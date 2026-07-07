<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\UserBankAccount;

class UserController extends Controller
{
    private function adminOnly(): void
    {
        if (! Auth::user() || Auth::user()->role !== 'admin') {
            abort(403);
        }
    }

    private function generateWalletId(): string
    {
        do {
            $walletId = 'XYW' . strtoupper(Str::random(10));
        } while (User::where('wallet_id', $walletId)->exists());

        return $walletId;
    }

    public function index(Request $request)
    {
        $this->adminOnly();

        $type = $request->get('type', 'client');

        if (! in_array($type, ['client', 'merchant'], true)) {
            $type = 'client';
        }

        $users = User::query()
            ->with(['bankAccounts' => function ($q) {
                $q->orderByDesc('is_default')->latest();
            }])
            ->where('role', $type)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('wallet_id', 'like', "%{$search}%")
                        ->orWhere('aadhaar', 'like', "%{$search}%")
                        ->orWhere('upi_id', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhereHas('bankAccounts', function ($bankQuery) use ($search) {
                            $bankQuery->where('bank_name', 'like', "%{$search}%")
                                ->orWhere('branch', 'like', "%{$search}%")
                                ->orWhere('account_number', 'like', "%{$search}%")
                                ->orWhere('account_type', 'like', "%{$search}%")
                                ->orWhere('ifsc', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('online'), fn ($q) => $q->where('is_online', (bool) $request->online))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'clients'   => User::where('role', 'client')->count(),
            'merchants' => User::where('role', 'merchant')->count(),
            'active'    => User::whereIn('role', ['client', 'merchant'])->where('status', 'active')->count(),
            'online'    => User::whereIn('role', ['client', 'merchant'])->where('is_online', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'type', 'stats'));
    }

    public function create()
    {
        $this->adminOnly();

        return view('admin.users.form', [
            'user' => new User(),
        ]);
    }



    public function destroy(User $user)
    {
        $this->adminOnly();

        if ($user->role === 'admin') {
            return back()->withErrors('Admin user cannot be deleted.');
        }

        $user->delete();

        return back()->with('success');
    }

    public function toggleStatus(User $user)
    {
        $this->adminOnly();

        if ($user->role === 'admin') {
            return back()->withErrors('Admin user cannot be blocked.');
        }

        $newStatus = $user->status === 'active' ? 'blocked' : 'active';

        $user->update([
            'status'    => $newStatus,
            'is_active' => $newStatus === 'active',
        ]);

        return back()->with('success', $newStatus === 'active' ? 'User activated.' : 'User blocked.');
    }

    public function toggleVerification(User $user)
    {
        $this->adminOnly();

        if ($user->role === 'admin') {
            return back()->withErrors('Admin verification cannot be changed.');
        }

        $user->update([
            'is_verified' => ! $user->is_verified,
        ]);

        return back()->with(
            'success',
            $user->is_verified ? 'User verified successfully.' : 'User marked as unverified.'
        );
    }

    private function validateUser(Request $request, ?int $userId = null): array
    {
        return $request->validate([
            'name'           => 'required|string|max:255',
            'original_name'  => 'nullable|string|max:255',
            'email'          => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'role'           => 'required|in:client,merchant',
            'phone'          => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($userId)],

            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',

            'nic'         => 'nullable|string|max:50',
            'aadhaar'     => 'nullable|string|max:50',
            'card_number' => 'nullable|string|max:100',

            'bank_name'      => 'nullable|string|max:100',
            'branch'         => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_type'   => 'nullable|string|max:100',
            'ifsc'           => 'nullable|string|max:100',

            'upi_name' => 'nullable|string|max:100',
            'upi_id'   => 'nullable|string|max:100',

            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'aadhaar_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'upi_qr'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',

            'balance'  => 'required|numeric|min:0',
            'password' => $userId ? 'nullable|min:6' : 'required|min:6',
            'status'   => 'nullable|in:active,blocked',
        ]);
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $data = $this->validateUser($request);

        $imageFolders = [
            'photo'         => 'users/photos',
            'aadhaar_photo' => 'users/aadhaar',
            'upi_qr'        => 'users/upi_qr',
        ];

        foreach ($imageFolders as $field => $folder) {
            $data[$field] = $request->hasFile($field)
                ? $request->file($field)->store($folder, 'public')
                : null;
        }

        $data['password'] = Hash::make($data['password']);
        $data['status'] = $request->input('status', 'active');
        $data['is_active'] = $data['status'] === 'active';
        $data['wallet_id'] = $this->generateWalletId();

        User::create($data);

        return redirect()
            ->route('admin.users.index', ['type' => $data['role']])
            ->with('success');
    }

    public function update(Request $request, User $user)
    {
        $this->adminOnly();

        $data = $this->validateUser($request, $user->id);

        $imageFolders = [
            'photo'         => 'users/photos',
            'aadhaar_photo' => 'users/aadhaar',
            'upi_qr'        => 'users/upi_qr',
        ];

        foreach ($imageFolders as $field => $folder) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store($folder, 'public');
            } else {
                unset($data[$field]);
            }
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if (empty($user->wallet_id)) {
            $data['wallet_id'] = $this->generateWalletId();
        }

        $data['status'] = $request->input('status', $user->status ?? 'active');
        $data['is_active'] = $data['status'] === 'active';

        $user->update($data);

        return redirect()
            ->route('admin.users.index', ['type' => $data['role']])
            ->with('success');
    }


    public function storeBank(Request $request, User $user)
{
    $this->adminOnly();

    $data = $request->validate([
        'bank_name' => 'required|string|max:255',
        'branch' => 'nullable|string|max:255',
        'account_number' => 'required|string|max:255',
        'account_type' => 'nullable|string|max:255',
        'ifsc' => 'nullable|string|max:255',
        'is_default' => 'nullable|boolean',
    ]);

    $data['user_id'] = $user->id;

    if ($request->boolean('is_default') || $user->bankAccounts()->count() === 0) {
        $user->bankAccounts()->update(['is_default' => false]);
        $data['is_default'] = true;
    } else {
        $data['is_default'] = false;
    }

    UserBankAccount::create($data);

    return back()->with('success');
}

public function updateBank(Request $request, User $user, UserBankAccount $bankAccount)
{
    $this->adminOnly();

    abort_unless((int) $bankAccount->user_id === (int) $user->id, 403);

    $data = $request->validate([
        'bank_name' => 'required|string|max:255',
        'branch' => 'nullable|string|max:255',
        'account_number' => 'required|string|max:255',
        'account_type' => 'nullable|string|max:255',
        'ifsc' => 'nullable|string|max:255',
    ]);

    $bankAccount->update($data);

    return back()->with('success');
}

public function deleteBank(User $user, UserBankAccount $bankAccount)
{
    $this->adminOnly();

    abort_unless((int) $bankAccount->user_id === (int) $user->id, 403);

    $wasDefault = (bool) $bankAccount->is_default;

    $bankAccount->delete();

    if ($wasDefault) {
        $nextBank = $user->bankAccounts()->oldest()->first();

        if ($nextBank) {
            $nextBank->update(['is_default' => true]);
        }
    }

    return back()->with('success');
}

public function setDefaultBank(User $user, UserBankAccount $bankAccount)
{
    $this->adminOnly();

    abort_unless((int) $bankAccount->user_id === (int) $user->id, 403);

    $user->bankAccounts()->update(['is_default' => false]);

    $bankAccount->update([
        'is_default' => true,
    ]);

    return back()->with('success');
}
public function edit(User $user)
{
    $this->adminOnly();

    $user->load(['bankAccounts' => function ($q) {
        $q->orderByDesc('is_default')->latest();
    }]);

    return view('admin.users.form', compact('user'));
}
}
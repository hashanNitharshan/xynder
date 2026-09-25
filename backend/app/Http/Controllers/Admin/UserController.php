<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Support\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const BANK_FIELDS = [
        'bank_name',
        'branch',
        'account_number',
        'account_type',
        'ifsc',
    ];

    private function adminOnly(): void
    {
        if (! Auth::user() || Auth::user()->role !== 'admin') {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->adminOnly();

        // Make sure inactive admins are shown as offline.
        Presence::sweep();

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
            ->orderByDesc('is_online')
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

        return back()->with('success', 'User deleted.');
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

    /**
     * Admin manual control: set a client or merchant Online / Offline.
     *
     * Offline: the user stays offline, even while using the app or web panel,
     * until an admin sets them online again. An offline merchant cannot
     * receive new P2P requests.
     *
     * Online: the user is shown as online right away.
     */
    public function toggleOnline(User $user)
    {
        $this->adminOnly();

        if ($user->role === 'admin') {
            return back()->withErrors('Admin online status cannot be changed from this page.');
        }

        if ($user->is_online) {
            $user->forceFill([
                'appear_offline' => true,
                'is_online'      => false,
            ])->save();

            return back()->with('success', $user->name . ' is now offline.');
        }

        $user->forceFill([
            'appear_offline' => false,
            'is_online'      => true,
            'last_seen_at'   => now(),
        ])->save();

        return back()->with('success', $user->name . ' is now online.');
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

    /**
     * Take the bank fields out of the user data.
     *
     * Bank details are saved in the user_bank_accounts table, not on the
     * users table. Returns the bank data only when a bank name or account
     * number was entered.
     */
    private function pullBankData(array &$data): ?array
    {
        $bank = [];

        foreach (self::BANK_FIELDS as $field) {
            $bank[$field] = isset($data[$field]) ? trim((string) $data[$field]) : null;
            unset($data[$field]);
        }

        if (empty($bank['bank_name']) && empty($bank['account_number'])) {
            return null;
        }

        return $bank;
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $data = $this->validateUser($request);

        $bankData = $this->pullBankData($data);

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

        $user = User::create($data);

        // First bank account entered on the create form becomes the default.
        if ($bankData) {
            UserBankAccount::create($bankData + [
                'user_id'    => $user->id,
                'is_default' => true,
            ]);
        }

        return redirect()
            ->route('admin.users.index', ['type' => $data['role']])
            ->with('success', ucfirst($data['role']) . ' created successfully.');
    }

    public function edit(User $user)
    {
        $this->adminOnly();

        // The user form only supports clients and merchants.
        if ($user->role === 'admin') {
            return redirect()
                ->route('admin.users.index', ['type' => 'client'])
                ->withErrors('Admin accounts cannot be edited from this page.');
        }

        $user->load(['bankAccounts' => function ($q) {
            $q->orderByDesc('is_default')->latest();
        }]);

        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->adminOnly();

        if ($user->role === 'admin') {
            return redirect()
                ->route('admin.users.index', ['type' => 'client'])
                ->withErrors('Admin accounts cannot be edited from this page.');
        }

        $data = $this->validateUser($request, $user->id);

        // Bank accounts are managed in the "All Bank Details" section.
        $this->pullBankData($data);

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

        $data['status'] = $request->input('status', $user->status ?? 'active');
        $data['is_active'] = $data['status'] === 'active';

        $user->update($data);

        return redirect()
            ->route('admin.users.index', ['type' => $data['role']])
            ->with('success', 'User updated successfully.');
    }

    // -------------------------------------------------------------------------
    // BANK ACCOUNTS (admin adds / edits bank details for clients and merchants)
    // -------------------------------------------------------------------------

    private function bankOwnerAllowed(User $user): bool
    {
        return in_array($user->role, ['client', 'merchant'], true);
    }

    public function storeBank(Request $request, User $user)
    {
        $this->adminOnly();

        if (! $this->bankOwnerAllowed($user)) {
            return back()->withErrors('Bank details can only be added for clients and merchants.');
        }

        $data = $request->validate([
            'bank_name'      => 'required|string|max:255',
            'branch'         => 'nullable|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_type'   => 'nullable|string|max:255',
            'ifsc'           => 'nullable|string|max:255',
            'is_default'     => 'nullable|boolean',
        ]);

        $data['user_id'] = $user->id;

        if ($request->boolean('is_default') || $user->bankAccounts()->count() === 0) {
            $user->bankAccounts()->update(['is_default' => false]);
            $data['is_default'] = true;
        } else {
            $data['is_default'] = false;
        }

        UserBankAccount::create($data);

        return back()->with('success', 'Bank account added.');
    }

    public function updateBank(Request $request, User $user, UserBankAccount $bankAccount)
    {
        $this->adminOnly();

        abort_unless((int) $bankAccount->user_id === (int) $user->id, 403);

        $data = $request->validate([
            'bank_name'      => 'required|string|max:255',
            'branch'         => 'nullable|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_type'   => 'nullable|string|max:255',
            'ifsc'           => 'nullable|string|max:255',
        ]);

        $bankAccount->update($data);

        return back()->with('success', 'Bank account updated.');
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

        return back()->with('success', 'Bank account deleted.');
    }

    public function setDefaultBank(User $user, UserBankAccount $bankAccount)
    {
        $this->adminOnly();

        abort_unless((int) $bankAccount->user_id === (int) $user->id, 403);

        $user->bankAccounts()->update(['is_default' => false]);

        $bankAccount->update([
            'is_default' => true,
        ]);

        return back()->with('success', 'Default bank account updated.');
    }
}
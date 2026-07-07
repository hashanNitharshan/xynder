<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\UserBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class WebSettingsController extends Controller
{
    public function clientIndex(Request $request)
    {
        abort_unless($request->user()->role === 'client', 403);
        return $this->index($request, 'client.settings');
    }

    public function merchantIndex(Request $request)
    {
        abort_unless($request->user()->role === 'merchant', 403);
        return $this->index($request, 'merchant.settings');
    }

    private function index(Request $request, string $view)
    {
        $user = $request->user()
            ->fresh()
            ->load(['bankAccounts' => fn ($q) => $q->orderByDesc('is_default')->latest()]);

        $tickets = SupportTicket::where('user_id', $user->id)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view($view, compact('user', 'tickets'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        return redirect()->route(
            $user->role === 'merchant' ? 'merchant.profile' : 'client.profile'
        );
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success');
    }

    public function storeSupport(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        SupportTicket::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        return back()->with('success');
    }

    /**
     * Shared guard: block any write action once payment details are locked.
     */
    private function abortIfPaymentLocked($user)
    {
        if (! empty($user->payment_details_locked_at)) {
            return back()->withErrors([
                'payment' => 'Payment details already updated once. You can view only.',
            ]);
        }

        return null;
    }

    /**
     * Shared guard: legacy bank records (imported from the old profile) can never
     * be edited, deleted, or set as default directly — the UI hides those actions,
     * but this stops someone from hitting the route directly.
     */
    private function abortIfLegacyBank(UserBankAccount $bankAccount)
    {
        if (! empty($bankAccount->old_user_bank)) {
            return back()->withErrors([
                'payment' => 'Legacy bank records cannot be edited or removed. Add a new bank account instead.',
            ]);
        }

        return null;
    }

    public function deleteBank(Request $request, UserBankAccount $bankAccount)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);
        abort_unless((int) $bankAccount->user_id === (int) $user->id, 403);

        if ($locked = $this->abortIfPaymentLocked($user)) {
            return $locked;
        }

        if ($legacy = $this->abortIfLegacyBank($bankAccount)) {
            return $legacy;
        }

        $wasDefault = (bool) $bankAccount->is_default;

        $bankAccount->delete();

        if ($wasDefault) {
            $nextBank = $user->bankAccounts()->oldest()->first();

            if ($nextBank) {
                $nextBank->update(['is_default' => true]);
            }
        }

        $user->update([
            'payment_details_locked_at' => now(),
        ]);

        return back()->with('success', 'Bank account deleted successfully. Payment details are now locked.');
    }

    public function storeBank(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        if ($locked = $this->abortIfPaymentLocked($user)) {
            return $locked;
        }

        $data = $request->validate([
            'bank_name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_type' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $user->bankAccounts()->update(['is_default' => false]);
        $data['is_default'] = true;

        $user->bankAccounts()->create($data);

        $user->update([
            'payment_details_locked_at' => now(),
        ]);

        return back()->with('success');
    }

    public function updateBank(Request $request, UserBankAccount $bankAccount)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);
        abort_unless((int) $bankAccount->user_id === (int) $user->id, 403);

        if ($locked = $this->abortIfPaymentLocked($user)) {
            return $locked;
        }

        if ($legacy = $this->abortIfLegacyBank($bankAccount)) {
            return $legacy;
        }

        $data = $request->validate([
            'bank_name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_type' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:255',
        ]);

        $bankAccount->update($data);

        $user->update([
            'payment_details_locked_at' => now(),
        ]);

        return back()->with('success', 'Bank account updated successfully. Payment details are now locked.');
    }

    public function setDefaultBank(Request $request, UserBankAccount $bankAccount)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);
        abort_unless((int) $bankAccount->user_id === (int) $user->id, 403);

        if ($locked = $this->abortIfPaymentLocked($user)) {
            return $locked;
        }

        if ($legacy = $this->abortIfLegacyBank($bankAccount)) {
            return $legacy;
        }

        $user->bankAccounts()->update(['is_default' => false]);

        $bankAccount->update([
            'is_default' => true,
        ]);

        $user->update([
            'payment_details_locked_at' => now(),
        ]);

        return back()->with('success', 'Default bank account updated successfully. Payment details are now locked.');
    }

    public function updateUpi(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        if ($locked = $this->abortIfPaymentLocked($user)) {
            return $locked;
        }

        $data = $request->validate([
            'upi_name' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:255',
            'upi_qr' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        if ($request->hasFile('upi_qr')) {
            if ($user->upi_qr && Storage::disk('public')->exists($user->upi_qr)) {
                Storage::disk('public')->delete($user->upi_qr);
            }

            $data['upi_qr'] = $request->file('upi_qr')->store('users/upi_qr', 'public');
        } else {
            unset($data['upi_qr']);
        }

        $data['payment_details_locked_at'] = now();

        $user->update($data);

        return back()->with('success');
    }
}
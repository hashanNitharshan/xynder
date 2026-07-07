<?php

namespace App\Http\Controllers;

use App\Models\UserBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebPaymentDetailsController extends Controller
{
    public function clientIndex(Request $request)
    {
        abort_unless($request->user()->role === 'client', 403);

        return $this->index($request, 'client.payment-details');
    }

    public function merchantIndex(Request $request)
    {
        abort_unless($request->user()->role === 'merchant', 403);

        return $this->index($request, 'merchant.payment-details');
    }

    private function index(Request $request, string $view)
    {
        $user = $request->user()->fresh();

        $bankAccounts = UserBankAccount::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        // Legacy fallback: pre-migration users had a single set of bank
        // fields directly on the users table. If they haven't added a row
        // to user_bank_accounts yet, surface those as a read-only entry
        // so the page doesn't look empty.
        if ($bankAccounts->isEmpty() && (
            $user->bank_name || $user->branch || $user->account_number || $user->account_type || $user->ifsc
        )) {
            $bankAccounts = collect([
                (object) [
                    'id'              => null,
                    'bank_name'       => $user->bank_name,
                    'branch'          => $user->branch,
                    'account_number'  => $user->account_number,
                    'account_type'    => $user->account_type,
                    'ifsc'            => $user->ifsc,
                    'is_default'      => true,
                    'old_user_bank'   => true,
                ],
            ]);
        }

        return view($view, compact('user', 'bankAccounts'));
    }

    public function storeBank(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        $data = $request->validate([
            'bank_name'      => 'nullable|string|max:100',
            'branch'         => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_type'   => 'nullable|string|max:100',
            'ifsc'           => 'nullable|string|max:100',
            'is_default'     => 'nullable|boolean',
        ]);

        $data['user_id'] = $user->id;

        $isFirstAccount = ! UserBankAccount::where('user_id', $user->id)->exists();

        if ($isFirstAccount || ($data['is_default'] ?? false) == true) {
            UserBankAccount::where('user_id', $user->id)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        UserBankAccount::create($data);

        return back()->with('success', 'Bank account added successfully.');
    }

    public function updateBank(Request $request, UserBankAccount $bankAccount)
    {
        $user = $request->user();

        if ($bankAccount->user_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'bank_name'      => 'nullable|string|max:100',
            'branch'         => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_type'   => 'nullable|string|max:100',
            'ifsc'           => 'nullable|string|max:100',
            'is_default'     => 'nullable|boolean',
        ]);

        if (($data['is_default'] ?? false) == true) {
            UserBankAccount::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $bankAccount->update($data);

        return back()->with('success', 'Bank account updated successfully.');
    }

    public function deleteBank(Request $request, UserBankAccount $bankAccount)
    {
        $user = $request->user();

        if ($bankAccount->user_id !== $user->id) {
            abort(403);
        }

        $wasDefault = $bankAccount->is_default;

        $bankAccount->delete();

        // Keep exactly one default when accounts remain after a delete.
        if ($wasDefault) {
            UserBankAccount::where('user_id', $user->id)
                ->latest()
                ->first()
                ?->update(['is_default' => true]);
        }

        return back()->with('success', 'Bank account deleted successfully.');
    }

    public function setDefaultBank(Request $request, UserBankAccount $bankAccount)
    {
        $user = $request->user();

        if ($bankAccount->user_id !== $user->id) {
            abort(403);
        }

        UserBankAccount::where('user_id', $user->id)->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);

        return back()->with('success', 'Default bank account updated.');
    }

    public function updateUpi(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        $data = $request->validate([
            'upi_name' => 'nullable|string|max:100',
            'upi_id'   => 'nullable|string|max:100',
            'upi_qr'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        if ($request->hasFile('upi_qr')) {
            if (! empty($user->upi_qr)) {
                Storage::disk('public')->delete($user->upi_qr);
            }

            $data['upi_qr'] = $request->file('upi_qr')->store('users/upi_qr', 'public');
        }

        $user->update($data);

        return back()->with('success', 'UPI details updated successfully.');
    }
}
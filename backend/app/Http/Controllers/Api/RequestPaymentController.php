<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\WalletRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestPaymentController extends Controller
{
    /**
     * Payment details for one Buy / Sell request.
     *
     * Buy USD  (deposit):    client sends INR  -> merchant bank / UPI
     * Sell USD (withdrawal): merchant sends INR -> client bank / UPI
     *
     * Only the client who owns the request, the assigned merchant,
     * or an admin can see these details.
     */
    public function show(Request $request, WalletRequest $walletRequest): JsonResponse
    {
        $user = $request->user();

        $isClientOwner    = (int) $walletRequest->user_id === (int) $user->id;
        $isMerchantOwner  = (int) $walletRequest->merchant_id === (int) $user->id;
        $isAdmin          = $user->role === 'admin';

        if (! $isClientOwner && ! $isMerchantOwner && ! $isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot view this transaction.',
            ], 403);
        }

        $type = strtolower(trim((string) $walletRequest->type));

        $isWithdrawal = in_array($type, ['withdrawal', 'sell', 'selling'], true);

        $payeeId = $isWithdrawal
            ? $walletRequest->user_id
            : $walletRequest->merchant_id;

        $payee = User::with(['bankAccounts' => function ($q) {
            $q->orderByDesc('is_default')->latest();
        }])->find($payeeId);

        if (! $payee) {
            return response()->json([
                'success'       => true,
                'request_id'    => $walletRequest->id,
                'payee_role'    => $isWithdrawal ? 'client' : 'merchant',
                'payee_is_me'   => false,
                'payee'         => null,
                'default_bank'  => null,
                'bank_accounts' => [],
            ]);
        }

        $bankAccounts = $payee->bankAccounts
            ->map(function (UserBankAccount $bank) {
                return [
                    'id'             => $bank->id,
                    'bank_name'      => $bank->bank_name,
                    'branch'         => $bank->branch,
                    'account_number' => $bank->account_number,
                    'account_type'   => $bank->account_type,
                    'ifsc'           => $bank->ifsc,
                    'is_default'     => (bool) $bank->is_default,
                ];
            })
            ->values();

        return response()->json([
            'success'      => true,
            'request_id'   => $walletRequest->id,
            'payee_role'   => $isWithdrawal ? 'client' : 'merchant',
            'payee_is_me'  => (int) $payee->id === (int) $user->id,
            'payee'        => [
                'id'            => $payee->id,
                'name'          => $payee->name,
                'original_name' => $payee->original_name,
                'wallet_id'     => $payee->wallet_id,
                'role'          => $payee->role,
                'upi_name'      => $payee->upi_name,
                'upi_id'        => $payee->upi_id,
                'upi_qr_url'    => $payee->upi_qr_url,
            ],
            'default_bank'  => $payee->default_bank,
            'bank_accounts' => $bankAccounts,
        ]);
    }
}
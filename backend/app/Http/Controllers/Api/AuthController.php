<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletRequest;
use App\Models\SystemConfig;
use App\Models\WalletTransfer;
use App\Models\UserBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Conversation;

class AuthController extends Controller
{
    private function ensureVerified(User $user)
    {
        if (! $user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified yet. Transactions are disabled until admin verification.',
            ], 403);
        }

        return null;
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        if ($user->status !== 'active' || ! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked. Please contact admin.',
            ], 403);
        }

        $user->update([
            'is_online'    => true,
            'last_seen_at' => now(),
        ]);

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user->fresh(),
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user()->fresh(),
        ]);
    }

    public function ping(Request $request)
    {
        $request->user()->update([
            'is_online'    => true,
            'last_seen_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function logout(Request $request)
    {
        $request->user()->update([
            'is_online'    => false,
            'last_seen_at' => now(),
        ]);

        $request->user()->currentAccessToken()?->delete();

        return response()->json(['success' => true]);
    }

    public function merchants(Request $request)
    {
        $merchants = User::where('role', 'merchant')
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('is_verified', true)
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'photo',
                'upi_qr',
                'is_online',
                'last_seen_at',
                'bank_name',
                'branch',
                'account_number',
                'account_type',
                'ifsc',
                'upi_name',
                'upi_id',
            ])
            ->orderByDesc('is_online')
            ->latest()
            ->get();

        return response()->json([
            'success'   => true,
            'merchants' => $merchants,
        ]);
    }

public function createRequest(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'client') {
        return response()->json([
            'success' => false,
            'message' => 'Only clients can create wallet requests.',
        ], 403);
    }

    if (! $user->is_active || $user->status !== 'active') {
        return response()->json([
            'success' => false,
            'message' => 'Your account has been blocked.',
        ], 403);
    }

    if ($response = $this->ensureVerified($user)) {
        return $response;
    }

    $data = $request->validate([
        'type'        => 'required|in:deposit,withdrawal',
        'amount'      => 'required|numeric|min:1',
        'merchant_id' => 'required|exists:users,id',
        'note'        => 'nullable|string|max:500',
    ]);

    $merchant = User::where('id', $data['merchant_id'])
        ->where('role', 'merchant')
        ->where('status', 'active')
        ->where('is_active', true)
        ->where('is_verified', true)
        ->where('is_online', true)
        ->first();

    if (! $merchant) {
        return response()->json([
            'success' => false,
            'message' => 'Selected merchant is offline or not available.',
        ], 422);
    }

    $amount = round((float) $data['amount'], 2);

    if ($data['type'] === 'withdrawal' && (float) $user->balance < $amount) {
        return response()->json([
            'success' => false,
            'message' => 'Insufficient USD balance.',
        ], 422);
    }

    $config = SystemConfig::current();

    $convertedAmount = round($amount * (float) $config->inr_rate, 2);
    $xynderFee = round((float) $config->xynder_fee, 2);
    $networkFee = round((float) $config->network_fee, 2);
    $fee = round($xynderFee + $networkFee, 2);

    $totalAmount = $data['type'] === 'deposit'
        ? round($convertedAmount + $fee, 2)
        : round($convertedAmount - $fee, 2);

    if ($data['type'] === 'withdrawal' && $totalAmount <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Amount is too small after fees.',
        ], 422);
    }

    $walletRequest = DB::transaction(function () use (
        $user,
        $merchant,
        $data,
        $amount,
        $config,
        $xynderFee,
        $networkFee,
        $convertedAmount,
        $fee,
        $totalAmount
    ) {
        $walletRequest = WalletRequest::create([
            'user_id'          => $user->id,
            'merchant_id'      => $merchant->id,
            'type'             => $data['type'],
            'amount'           => $amount,
            'usd_rate'         => $config->usd_rate,
            'inr_rate'         => $config->inr_rate,
            'xynder_fee'       => $xynderFee,
            'network_fee'      => $networkFee,
            'converted_amount' => $convertedAmount,
            'fee'              => $fee,
            'total_amount'     => $totalAmount,
            'note'             => $data['note'] ?? null,
            'payment_slip'     => null,
            'status'           => 'pending',
        ]);

        Conversation::firstOrCreate(
            ['wallet_request_id' => $walletRequest->id],
            [
                'user_one_id' => $user->id,
                'user_two_id' => $merchant->id,
            ]
        );

        return $walletRequest;
    });

    return response()->json([
        'success'    => true,
        'message'    => $data['type'] === 'withdrawal'
            ? 'Sell USD request submitted successfully.'
            : 'Buy USD request submitted successfully.',
        'request'    => $walletRequest->fresh(['merchant', 'conversation']),
        'merchant'   => $merchant,
        'request_id' => $walletRequest->id,
    ]);
}

    public function walletLookup(Request $request)
    {
        $currentUser = $request->user();

        if ($response = $this->ensureVerified($currentUser)) {
            return $response;
        }

        $data = $request->validate([
            'wallet_id' => 'required|string|max:50',
        ]);

        $user = User::where('wallet_id', $data['wallet_id'])
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('is_verified', true)
            ->select('id', 'name', 'email', 'wallet_id')
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet address not found or user is not verified.',
            ], 404);
        }

        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot transfer to your own wallet.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'user'    => $user,
        ]);
    }

    public function walletTransfer(Request $request)
    {
        $data = $request->validate([
            'receiver_wallet_id' => 'required|string|max:50',
            'amount'             => 'required|numeric|min:1',
            'note'               => 'nullable|string|max:500',
        ]);

        $sender = $request->user();

        if (! $sender->is_active || $sender->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked.',
            ], 403);
        }

        if ($response = $this->ensureVerified($sender)) {
            return $response;
        }

        $amount = round((float) $data['amount'], 2);

        try {
            $transfer = DB::transaction(function () use ($sender, $data, $amount) {
                $lockedSender = User::where('id', $sender->id)->lockForUpdate()->first();

                $receiver = User::where('wallet_id', $data['receiver_wallet_id'])
                    ->where('status', 'active')
                    ->where('is_active', true)
                    ->where('is_verified', true)
                    ->lockForUpdate()
                    ->first();

                if (! $receiver) {
                    abort(404, 'Receiver wallet address not found or receiver is not verified.');
                }

                if ($receiver->id === $lockedSender->id) {
                    abort(422, 'You cannot transfer to your own wallet.');
                }

                if ((float) $lockedSender->balance < $amount) {
                    abort(422, 'Insufficient wallet balance.');
                }

                $lockedSender->balance = round((float) $lockedSender->balance - $amount, 2);
                $receiver->balance     = round((float) $receiver->balance + $amount, 2);

                $lockedSender->save();
                $receiver->save();

                return WalletTransfer::create([
                    'sender_id'          => $lockedSender->id,
                    'receiver_id'        => $receiver->id,
                    'receiver_wallet_id' => $receiver->wallet_id,
                    'amount'             => $amount,
                    'note'               => $data['note'] ?? null,
                ]);
            });

            return response()->json([
                'success'  => true,
                'message'  => 'Wallet transfer completed successfully.',
                'transfer' => $transfer->fresh(['receiver']),
                'user'     => $request->user()->fresh(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Transfer failed.',
            ], 422);
        }
    }

    public function walletTransfers(Request $request)
    {
        $userId = $request->user()->id;

        $transfers = WalletTransfer::with([
            'sender:id,name,email,wallet_id',
            'receiver:id,name,email,wallet_id',
        ])
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->latest()
            ->get();

        return response()->json([
            'success'   => true,
            'transfers' => $transfers,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (! $user->is_active || $user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked.',
            ], 403);
        }

        $data = $this->validateProfile($request, $user->id, true);
        $data = $this->cleanImageFields($data);

        foreach (['photo', 'aadhaar_photo', 'upi_qr'] as $field) {
            if ($request->hasFile($field)) {
                if ($user->{$field} && $user->{$field} !== '0') {
                    Storage::disk('public')->delete($user->{$field});
                }

                $folder = match ($field) {
                    'photo'         => 'users/photos',
                    'aadhaar_photo' => 'users/aadhaar',
                    'upi_qr'        => 'users/upi_qr',
                };

                $data[$field] = $request->file($field)->store($folder, 'public');
            }
            // If no file uploaded, the field was already unset by cleanImageFields()
            // (handles null, '0', 0, ''), so the existing DB value is preserved.
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'user'    => $user->fresh(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    public function register(Request $request)
    {
        $data = $this->validateProfile($request);
        $data = $this->cleanImageFields($data);

        $data['role']        = 'client';
        $data['password']    = Hash::make($request->password);
        $data['balance']     = 0;
        $data['status']      = 'active';
        $data['is_active']   = true;
        $data['is_verified'] = false;
        $data['is_online']   = true;
        $data['last_seen_at'] = now();

        foreach (['photo', 'aadhaar_photo', 'upi_qr'] as $field) {
            if ($request->hasFile($field)) {
                $folder = match ($field) {
                    'photo'         => 'users/photos',
                    'aadhaar_photo' => 'users/aadhaar',
                    'upi_qr'        => 'users/upi_qr',
                };

                $data[$field] = $request->file($field)->store($folder, 'public');
            }
        }

        $user  = User::create($data);
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user->fresh(),
        ]);
    }

    private function validateProfile(Request $request, ?int $userId = null, bool $update = false): array
    {
        return $request->validate([
            'name'          => [$update ? 'sometimes' : 'required', 'string', 'max:255'],
            'original_name' => 'nullable|string|max:255',

            'email' => [
                $update ? 'sometimes' : 'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'phone')->ignore($userId),
            ],

            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',

            'aadhaar'       => 'nullable|string|max:50',
            'aadhaar_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',

            'bank_name'      => 'nullable|string|max:100',
            'branch'         => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_type'   => 'nullable|string|max:100',
            'ifsc'           => 'nullable|string|max:100',

            'upi_name' => 'nullable|string|max:100',
            'upi_id'   => 'nullable|string|max:100',

            'photo'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'upi_qr' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',

            'password' => $update ? 'nullable|min:6' : 'required|min:6',
        ]);
    }

    public function myRequests(Request $request)
    {
        $user = $request->user();

        $query = WalletRequest::with(['user', 'merchant']);

        if ($user->role === 'merchant') {
            $query->where('merchant_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'success'  => true,
            'requests' => $query->latest()->get(),
        ]);
    }

    public function merchantApproveRequest(Request $request, WalletRequest $walletRequest)
    {
        $merchantUser = $request->user();

        if ($merchantUser->role !== 'merchant') {
            return response()->json([
                'success' => false,
                'message' => 'Only merchant can approve this request.',
            ], 403);
        }

        if ($walletRequest->merchant_id != $merchantUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized request.',
            ], 403);
        }

        if ($walletRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request is already processed.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($walletRequest, $merchantUser) {
                $lockedRequest = WalletRequest::where('id', $walletRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedRequest->status !== 'pending') {
                    abort(422, 'This request is already processed.');
                }

                $client   = User::where('id', $lockedRequest->user_id)->lockForUpdate()->firstOrFail();
                $merchant = User::where('id', $merchantUser->id)->lockForUpdate()->firstOrFail();

                $amount = round((float) $lockedRequest->amount, 2);

                if ($lockedRequest->type === 'deposit') {
                    if ((float) $merchant->balance < $amount) {
                        abort(422, 'Merchant has insufficient USD balance.');
                    }

                    $client->balance   = round((float) $client->balance + $amount, 2);
                    $merchant->balance = round((float) $merchant->balance - $amount, 2);
                } elseif ($lockedRequest->type === 'withdrawal') {
                    if ((float) $client->balance < $amount) {
                        abort(422, 'Client has insufficient USD balance.');
                    }

                    $client->balance   = round((float) $client->balance - $amount, 2);
                    $merchant->balance = round((float) $merchant->balance + $amount, 2);
                } else {
                    abort(422, 'Invalid request type.');
                }

                $client->save();
                $merchant->save();

                $lockedRequest->update([
                    'status'      => 'approved',
                    'approved_by' => $merchant->id,
                    'approved_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Request approved successfully.',
                'request' => $walletRequest->fresh(['user', 'merchant']),
                'user'    => $request->user()->fresh(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Request approval failed.',
            ], 422);
        }
    }

    public function merchantRejectRequest(Request $request, WalletRequest $walletRequest)
    {
        $merchant = $request->user();

        if ($merchant->role !== 'merchant') {
            return response()->json([
                'success' => false,
                'message' => 'Only merchant can reject this request.',
            ], 403);
        }

        if ($walletRequest->merchant_id != $merchant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized request.',
            ], 403);
        }

        if ($walletRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request is already processed.',
            ], 422);
        }

        $walletRequest->update([
            'status'      => 'rejected',
            'approved_by' => $merchant->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request rejected successfully.',
            'request' => $walletRequest->fresh(['user', 'merchant']),
        ]);
    }

    /**
     * Close a pending P2P request.
     * Allowed for: the client who owns the request, OR the assigned merchant.
     * Not allowed once the request is approved / rejected / already closed.
     */
    public function merchantCloseRequest(Request $request, WalletRequest $walletRequest)
    {
        $currentUser = $request->user();

        if ($walletRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only a pending request can be closed.',
            ], 422);
        }

        $isClientOwner = $currentUser->role === 'client'
            && (int) $walletRequest->user_id === (int) $currentUser->id;

        $isAssignedMerchant = $currentUser->role === 'merchant'
            && (int) $walletRequest->merchant_id === (int) $currentUser->id;

        if (! $isClientOwner && ! $isAssignedMerchant) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to close this request.',
            ], 403);
        }

        try {
            DB::transaction(function () use ($walletRequest, $currentUser) {
                $lockedRequest = WalletRequest::where('id', $walletRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedRequest->status !== 'pending') {
                    abort(422, 'Only a pending request can be closed.');
                }

                $lockedRequest->update([
                    'status' => 'closed',
                    'approved_by' => $currentUser->role === 'merchant'
                        ? $currentUser->id
                        : null,
                    'approved_at' => now(),
                ]);

                $conversation = Conversation::where(
                    'wallet_request_id',
                    $lockedRequest->id
                )->first();

                if ($conversation) {
                    $conversation->update([
                        'locked_at' => now(),
                        'lock_reason' => 'Transaction request closed.',
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'The transaction request is now closed.',
                'request' => $walletRequest->fresh(['user', 'merchant']),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unable to close this request.',
            ], 422);
        }
    }
    // -----------------------------------------------------------------------
    // FIX: Also unset null values — not just '0', 0, ''.
    //
    // When a Flutter profile-update request omits an image field entirely,
    // Laravel validation returns null for that nullable field.  The previous
    // version did not strip null, so $user->update() would overwrite an
    // existing storage path with NULL.
    // -----------------------------------------------------------------------
    private function cleanImageFields(array $data): array
    {
        foreach (['photo', 'aadhaar_photo', 'upi_qr'] as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if ($value === null || $value === '0' || $value === 0 || $value === '') {
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }



public function storeBankAccount(Request $request)
{
    $user = $request->user();

    $data = $request->validate([
        'bank_name' => 'nullable|string|max:100',
        'branch' => 'nullable|string|max:100',
        'account_number' => 'nullable|string|max:100',
        'account_type' => 'nullable|string|max:100',
        'ifsc' => 'nullable|string|max:100',
        'is_default' => 'nullable|boolean',
    ]);

    $data['user_id'] = $user->id;

    if (($data['is_default'] ?? false) == true) {
        UserBankAccount::where('user_id', $user->id)->update(['is_default' => false]);
    }

    $bank = UserBankAccount::create($data);

    return response()->json([
        'success' => true,
        'message' => 'Bank account added successfully',
        'bank_account' => $bank,
    ]);
}

public function updateBankAccount(Request $request, UserBankAccount $bankAccount)
{
    $user = $request->user();

    if ($bankAccount->user_id !== $user->id) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $data = $request->validate([
        'bank_name' => 'nullable|string|max:100',
        'branch' => 'nullable|string|max:100',
        'account_number' => 'nullable|string|max:100',
        'account_type' => 'nullable|string|max:100',
        'ifsc' => 'nullable|string|max:100',
        'is_default' => 'nullable|boolean',
    ]);

    if (($data['is_default'] ?? false) == true) {
        UserBankAccount::where('user_id', $user->id)->update(['is_default' => false]);
    }

    $bankAccount->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Bank account updated successfully',
        'bank_account' => $bankAccount->fresh(),
    ]);
}

public function deleteBankAccount(Request $request, UserBankAccount $bankAccount)
{
    if ($bankAccount->user_id !== $request->user()->id) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $bankAccount->delete();

    return response()->json([
        'success' => true,
        'message' => 'Bank account deleted successfully',
    ]);
}

public function updateUpi(Request $request)
{
    $user = $request->user();

    $data = $request->validate([
        'upi_name' => 'nullable|string|max:100',
        'upi_id' => 'nullable|string|max:100',
        'upi_qr' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
    ]);

    if ($request->hasFile('upi_qr')) {
        if ($user->upi_qr && $user->upi_qr !== '0') {
            Storage::disk('public')->delete($user->upi_qr);
        }

        $data['upi_qr'] = $request->file('upi_qr')->store('users/upi_qr', 'public');
    }

    $user->update($data);

    return response()->json([
        'success' => true,
        'message' => 'UPI details updated successfully',
        'user' => $user->fresh(),
    ]);
}

public function paymentMethods(Request $request)
{
    $user = $request->user()->fresh();

    $banks = UserBankAccount::where('user_id', $user->id)
        ->latest()
        ->get();

    if ($banks->isEmpty() && (
        $user->bank_name ||
        $user->branch ||
        $user->account_number ||
        $user->account_type ||
        $user->ifsc
    )) {
        $banks = collect([
            [
                'id' => 'old',
                'bank_name' => $user->bank_name,
                'branch' => $user->branch,
                'account_number' => $user->account_number,
                'account_type' => $user->account_type,
                'ifsc' => $user->ifsc,
                'is_default' => true,
                'old_user_bank' => true,
            ]
        ]);
    }

    return response()->json([
        'success' => true,
        'user' => $user,
        'bank_accounts' => $banks,
    ]);
}
}
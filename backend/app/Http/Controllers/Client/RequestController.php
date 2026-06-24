<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use App\Models\User;
use App\Models\WalletRequest;
use Illuminate\Http\Request;
use App\Models\Conversation;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'client', 403);

        $config = SystemConfig::current();

        $merchants = User::where('role', 'merchant')
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('is_verified', true)
            ->latest()
            ->get();

        $requests = WalletRequest::with('merchant')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('client.requests', compact('requests', 'config', 'merchants'));
    }

   public function store(Request $request)
{
    $user = $request->user();

    abort_unless($user->role === 'client', 403);

    if (! $user->is_active || $user->status !== 'active') {
        return back()->withErrors(['amount' => 'Your account has been blocked.'])->withInput();
    }

    if (! $user->is_verified) {
        return back()->withErrors(['amount' => 'Your account is not verified yet.'])->withInput();
    }

    $data = $request->validate([
        'type' => 'required|in:deposit,withdrawal',
        'amount' => 'required|numeric|min:1',
        'merchant_id' => 'required|exists:users,id',
        'note' => 'nullable|string|max:500',
    ]);

    $merchant = User::where('id', $data['merchant_id'])
        ->where('role', 'merchant')
        ->where('status', 'active')
        ->where('is_active', true)
        ->where('is_verified', true)
        ->first();

    if (! $merchant) {
        return back()->withErrors(['merchant_id' => 'Selected merchant is not available.'])->withInput();
    }

    if (! $merchant->is_online) {
        return back()->withErrors(['merchant_id' => 'Selected merchant is offline. Please choose online merchant.'])->withInput();
    }

    $amount = round((float) $data['amount'], 2);

    if ($data['type'] === 'withdrawal' && (float) $user->balance < $amount) {
        return back()->withErrors(['amount' => 'Insufficient USD balance.'])->withInput();
    }

    $config = SystemConfig::current();

    $convertedAmount = round($amount * (float) $config->inr_rate, 2);
    $fee = round((float) $config->xynder_fee + (float) $config->network_fee, 2);
    $totalAmount = round($convertedAmount + $fee, 2);

    $walletRequest = WalletRequest::create([
        'user_id' => $user->id,
        'merchant_id' => $merchant->id,
        'type' => $data['type'],
        'amount' => $amount,
        'usd_rate' => $config->usd_rate,
        'inr_rate' => $config->inr_rate,
        'xynder_fee' => $config->xynder_fee,
        'network_fee' => $config->network_fee,
        'converted_amount' => $convertedAmount,
        'fee' => $fee,
        'total_amount' => $totalAmount,
        'note' => $data['note'] ?? null,
        'payment_slip' => null,
        'status' => 'pending',
    ]);

    $conversation = Conversation::firstOrCreate(
        ['wallet_request_id' => $walletRequest->id],
        [
            'user_one_id' => $user->id,
            'user_two_id' => $merchant->id,
        ]
    );

    return redirect()
        ->route('client.dashboard')
        ->with('success', $data['type'] === 'withdrawal'
            ? 'Sell USD request submitted successfully.'
            : 'Buy USD request submitted successfully.')
        ->with('popup_transaction', [
            'title' => $data['type'] === 'withdrawal' ? 'Sell USD Request Created' : 'Buy USD Request Created',
            'no' => $walletRequest->transaction_no,
            'amount' => number_format($amount, 2),
            'status' => 'PENDING',
            'chat_url' => route('client.chats.show', $conversation),
        ]);
}
}
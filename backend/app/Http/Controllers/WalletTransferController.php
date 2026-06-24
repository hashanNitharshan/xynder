<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Models\WalletTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletTransferController extends Controller
{
    public function clientIndex(Request $request)
    {
        abort_unless($request->user()->role === 'client', 403);

        return $this->index($request, 'client.transfers');
    }

    public function merchantIndex(Request $request)
    {
        abort_unless($request->user()->role === 'merchant', 403);

        return $this->index($request, 'merchant.transfers');
    }

    private function index(Request $request, string $view)
    {
        $user = User::findOrFail($request->user()->id);

        $transfers = WalletTransfer::with(['sender', 'receiver'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->paginate(20);

        $receiver = null;

        if (session('lookup_wallet_id')) {
            $receiver = User::where('wallet_id', session('lookup_wallet_id'))
                ->where('status', 'active')
                ->where('is_active', true)
                ->where('is_verified', true)
                ->first();
        }

        return view($view, compact('user', 'transfers', 'receiver'));
    }

    public function lookup(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant']), 403);

        $data = $request->validate([
            'wallet_id' => 'required|string|max:50',
        ]);

        if (! $user->is_verified) {
            return back()->withErrors(['wallet_id' => 'Your account is not verified yet.'])->withInput();
        }

        $receiver = User::where('wallet_id', $data['wallet_id'])
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('is_verified', true)
            ->first();

        if (! $receiver) {
            return back()->withErrors(['wallet_id' => 'Wallet address not found or user not verified.'])->withInput();
        }

        if ((int) $receiver->id === (int) $user->id) {
            return back()->withErrors(['wallet_id' => 'You cannot transfer to your own wallet.'])->withInput();
        }

        return back()
            ->withInput()
            ->with('lookup_wallet_id', $data['wallet_id'])
            ->with('lookup_success', 'Receiver found successfully.');
    }

    public function store(Request $request)
    {
        $sender = $request->user();

        abort_unless(in_array($sender->role, ['client', 'merchant']), 403);

        if (! $sender->is_active || $sender->status !== 'active') {
            return back()->withErrors(['amount' => 'Your account has been blocked.'])->withInput();
        }

        if (! $sender->is_verified) {
            return back()->withErrors(['amount' => 'Your account is not verified yet.'])->withInput();
        }

        $data = $request->validate([
            'receiver_wallet_id' => 'required|string|max:50',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $amount = round((float) $data['amount'], 2);

        try {
            $transfer = DB::transaction(function () use ($sender, $data, $amount) {
                $lockedSender = User::where('id', $sender->id)->lockForUpdate()->firstOrFail();

                $receiver = User::where('wallet_id', $data['receiver_wallet_id'])
                    ->where('status', 'active')
                    ->where('is_active', true)
                    ->where('is_verified', true)
                    ->lockForUpdate()
                    ->first();

                if (! $receiver) {
                    throw new \Exception('Receiver wallet address not found or receiver is not verified.');
                }

                if ((int) $receiver->id === (int) $lockedSender->id) {
                    throw new \Exception('You cannot transfer to your own wallet.');
                }

                if ((float) $lockedSender->balance < $amount) {
                    throw new \Exception('Insufficient wallet balance.');
                }

                $lockedSender->balance = round((float) $lockedSender->balance - $amount, 2);
                $receiver->balance = round((float) $receiver->balance + $amount, 2);

                $lockedSender->save();
                $receiver->save();

                return WalletTransfer::create([
                    'sender_id' => $lockedSender->id,
                    'receiver_id' => $receiver->id,
                    'receiver_wallet_id' => $receiver->wallet_id,
                    'amount' => $amount,
                    'note' => $data['note'] ?? null,
                ]);
            });

            $conversation = Conversation::firstOrCreate(
                ['wallet_transfer_id' => $transfer->id],
                [
                    'user_one_id' => $transfer->sender_id,
                    'user_two_id' => $transfer->receiver_id,
                ]
            );

            $dashboardRoute = $sender->role === 'merchant'
                ? 'merchant.dashboard'
                : 'client.dashboard';

            $chatRoute = $sender->role === 'merchant'
                ? 'merchant.chats.show'
                : 'client.chats.show';

            return redirect()
                ->route($dashboardRoute)
                ->with('success', 'Transfer sent successfully.')
                ->with('popup_transaction', [
                    'title' => 'Wallet Transfer Completed',
                    'no' => $transfer->transaction_no,
                    'amount' => number_format((float) $transfer->amount, 2),
                    'status' => 'COMPLETED',
                    'chat_url' => route($chatRoute, $conversation),
                ]);
        } catch (\Throwable $e) {
            return back()->withErrors(['amount' => $e->getMessage() ?: 'Transfer failed.'])->withInput();
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Models\WalletTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class WalletTransferController extends Controller
{
    public function clientIndex(Request $request): View
    {
        abort_unless($request->user()->role === 'client', 403);

        return $this->index($request, 'client.transfers');
    }

    public function merchantIndex(Request $request): View
    {
        abort_unless($request->user()->role === 'merchant', 403);

        return $this->index($request, 'merchant.transfers');
    }

    private function index(Request $request, string $view): View
    {
        $user = User::findOrFail($request->user()->id);

        $filterQ = trim((string) $request->query('q', ''));
        $filterType = $request->query('type', 'all');

        $transfers = WalletTransfer::with(['sender', 'receiver'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->when($filterType === 'sent', function ($query) use ($user) {
                $query->where('sender_id', $user->id);
            })
            ->when($filterType === 'received', function ($query) use ($user) {
                $query->where('receiver_id', $user->id);
            })
            ->when($filterQ !== '', function ($query) use ($filterQ) {
                $query->where(function ($subQuery) use ($filterQ) {
                    $subQuery->where('transaction_no', 'like', '%' . $filterQ . '%')
                        ->orWhere('receiver_wallet_id', 'like', '%' . $filterQ . '%')
                        ->orWhere('transfer_type', 'like', '%' . $filterQ . '%')
                        ->orWhere('note', 'like', '%' . $filterQ . '%')
                        ->orWhereHas('sender', function ($senderQuery) use ($filterQ) {
                            $senderQuery->where('name', 'like', '%' . $filterQ . '%')
                                ->orWhere('email', 'like', '%' . $filterQ . '%')
                                ->orWhere('wallet_id', 'like', '%' . $filterQ . '%');
                        })
                        ->orWhereHas('receiver', function ($receiverQuery) use ($filterQ) {
                            $receiverQuery->where('name', 'like', '%' . $filterQ . '%')
                                ->orWhere('email', 'like', '%' . $filterQ . '%')
                                ->orWhere('wallet_id', 'like', '%' . $filterQ . '%');
                        });
                });
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

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

    public function lookup(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        $data = $request->validate([
            'wallet_id' => ['required', 'string', 'max:50'],
        ]);

        if (! $user->is_active || $user->status !== 'active') {
            return back()
                ->withErrors(['wallet_id' => 'Your account has been blocked.'])
                ->withInput();
        }

        if (! $user->is_verified) {
            return back()
                ->withErrors(['wallet_id' => 'Your account is not verified yet.'])
                ->withInput();
        }

        $receiver = User::where('wallet_id', trim($data['wallet_id']))
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('is_verified', true)
            ->first();

        if (! $receiver) {
            return back()
                ->withErrors([
                    'wallet_id' => 'Wallet address not found or user not verified.',
                ])
                ->withInput();
        }

        if ((int) $receiver->id === (int) $user->id) {
            return back()
                ->withErrors([
                    'wallet_id' => 'You cannot transfer to your own wallet.',
                ])
                ->withInput();
        }

        return back()
            ->withInput()
            ->with('lookup_wallet_id', $receiver->wallet_id)
            ->with('lookup_success', 'Receiver found successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $sender = $request->user();

        abort_unless(in_array($sender->role, ['client', 'merchant'], true), 403);

        if (! $sender->is_active || $sender->status !== 'active') {
            return back()
                ->withErrors(['amount' => 'Your account has been blocked.'])
                ->withInput();
        }

        if (! $sender->is_verified) {
            return back()
                ->withErrors(['amount' => 'Your account is not verified yet.'])
                ->withInput();
        }

        $data = $request->validate([
            'transfer_type' => [
                'required',
                Rule::in([
                    WalletTransfer::TYPE_INTERNAL,
                    WalletTransfer::TYPE_EXTERNAL,
                ]),
            ],
            'receiver_wallet_id' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'receiver_wallet_id.regex' => 'Wallet address may contain only letters, numbers, hyphens and underscores.',
        ]);

        $transferType = $data['transfer_type'];
        $walletAddress = trim($data['receiver_wallet_id']);
        $amount = round((float) $data['amount'], 2);

        try {
            $transfer = DB::transaction(function () use (
                $sender,
                $data,
                $transferType,
                $walletAddress,
                $amount
            ) {
                $lockedSender = User::whereKey($sender->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedSender->is_active || $lockedSender->status !== 'active') {
                    throw new \RuntimeException('Your account has been blocked.');
                }

                if (! $lockedSender->is_verified) {
                    throw new \RuntimeException('Your account is not verified yet.');
                }

                if ((float) $lockedSender->balance < $amount) {
                    throw new \RuntimeException('Insufficient wallet balance.');
                }

                $receiver = null;

                if ($transferType === WalletTransfer::TYPE_INTERNAL) {
                    $receiver = User::where('wallet_id', $walletAddress)
                        ->where('status', 'active')
                        ->where('is_active', true)
                        ->where('is_verified', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $receiver) {
                        throw new \RuntimeException(
                            'Receiver wallet address not found or receiver is not verified.'
                        );
                    }

                    if ((int) $receiver->id === (int) $lockedSender->id) {
                        throw new \RuntimeException(
                            'You cannot transfer to your own wallet.'
                        );
                    }
                }

                $lockedSender->balance = round(
                    (float) $lockedSender->balance - $amount,
                    2
                );
                $lockedSender->save();

                if ($receiver) {
                    $receiver->balance = round(
                        (float) $receiver->balance + $amount,
                        2
                    );
                    $receiver->save();
                }

                return WalletTransfer::create([
                    'sender_id' => $lockedSender->id,
                    'receiver_id' => $receiver?->id,
                    'receiver_wallet_id' => $receiver?->wallet_id ?? $walletAddress,
                    'transfer_type' => $transferType,
                    'amount' => $amount,
                    'note' => $data['note'] ?? null,
                ]);
            });

            $transferRoute = $sender->role === 'merchant'
                ? 'merchant.transfers'
                : 'client.transfers';

            $transferNo = $transfer->transaction_no
                ?? 'TRA' . str_pad((string) $transfer->id, 9, '0', STR_PAD_LEFT);

            if ($transfer->isExternal()) {
                return redirect()
                    ->route($transferRoute)
                    ->with('success', 'External wallet transfer sent successfully.')
                    ->with('last_transfer_no', $transferNo)
                    ->with('popup_transaction', [
                        'title' => 'External Wallet Transfer Completed',
                        'no' => $transferNo,
                        'amount' => number_format((float) $transfer->amount, 2),
                        'status' => 'COMPLETED',
                        'chat_url' => null,
                    ]);
            }

            Conversation::firstOrCreate(
                ['wallet_transfer_id' => $transfer->id],
                [
                    'user_one_id' => $transfer->sender_id,
                    'user_two_id' => $transfer->receiver_id,
                ]
            );

            $chatRoute = $sender->role === 'merchant'
                ? 'merchant.chats.transfer'
                : 'client.chats.transfer';

            $chatUrl = route($chatRoute, $transfer);

            return redirect()
                ->route($transferRoute)
                ->with('success', 'Transfer sent successfully.')
                ->with('last_transfer_no', $transferNo)
                ->with('chat_url', $chatUrl)
                ->with('popup_transaction', [
                    'title' => 'Wallet Transfer Completed',
                    'no' => $transferNo,
                    'amount' => number_format((float) $transfer->amount, 2),
                    'status' => 'COMPLETED',
                    'chat_url' => $chatUrl,
                ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors([
                    'amount' => $exception->getMessage() ?: 'Transfer failed.',
                ])
                ->withInput();
        }
    }
}

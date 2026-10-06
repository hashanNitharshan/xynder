<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use App\Models\WalletTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class WalletTransferController extends Controller
{
    private function adminOnly(): void
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'admin') {
            abort(403);
        }
    }

    public function index(Request $request): View
    {
        $this->adminOnly();

        $perPage = (int) $request->integer('per_page', 20);

        if (! in_array($perPage, [10, 20, 30, 50, 100], true)) {
            $perPage = 20;
        }

        $transferType = strtolower(
            trim((string) $request->query('transfer_type', 'all'))
        );

        if (! in_array($transferType, ['all', 'internal', 'external'], true)) {
            $transferType = 'all';
        }

        $status = strtolower(
            trim((string) $request->query('status', 'all'))
        );

        if (! in_array($status, ['all', WalletTransfer::STATUS_COMPLETED, WalletTransfer::STATUS_REVERSED], true)) {
            $status = 'all';
        }

        $query = WalletTransfer::query()
            ->with([
                'sender:id,name,email,phone,wallet_id',
                'receiver:id,name,email,phone,wallet_id',
                'reversedBy:id,name',
            ]);

        if ($transferType !== 'all') {
            $query->where('transfer_type', $transferType);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('transaction_no', 'like', "%{$search}%")
                    ->orWhere('receiver_wallet_id', 'like', "%{$search}%")
                    ->orWhere('transfer_type', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('sender', function ($senderQuery) use ($search) {
                        $senderQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('wallet_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('receiver', function ($receiverQuery) use ($search) {
                        $receiverQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('wallet_id', 'like', "%{$search}%");
                    });
            });
        }

        $transfers = $query
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        // Volumes count only completed transfers. Reversed money went back.
        $completed = fn () => WalletTransfer::where('status', WalletTransfer::STATUS_COMPLETED);

        $stats = [
            'total_count'  => WalletTransfer::count(),
            'total_volume' => (float) $completed()->sum('amount'),

            'internal_count' => WalletTransfer::where(
                'transfer_type',
                WalletTransfer::TYPE_INTERNAL
            )->count(),

            'internal_volume' => (float) $completed()
                ->where('transfer_type', WalletTransfer::TYPE_INTERNAL)
                ->sum('amount'),

            'external_count' => WalletTransfer::where(
                'transfer_type',
                WalletTransfer::TYPE_EXTERNAL
            )->count(),

            'external_volume' => (float) $completed()
                ->where('transfer_type', WalletTransfer::TYPE_EXTERNAL)
                ->sum('amount'),

            'reversed_count' => WalletTransfer::where(
                'status',
                WalletTransfer::STATUS_REVERSED
            )->count(),

            'reversed_volume' => (float) WalletTransfer::where(
                'status',
                WalletTransfer::STATUS_REVERSED
            )->sum('amount'),

            'today_count' => WalletTransfer::whereDate(
                'created_at',
                today()
            )->count(),

            'today_volume' => (float) $completed()
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];

        return view(
            'admin.wallet_transfers.index',
            compact('transfers', 'stats')
        );
    }

    public function show(
        Request $request,
        WalletTransfer $walletTransfer
    ): View {
        $this->adminOnly();

        $walletTransfer->load([
            'sender:id,name,email,phone,wallet_id,photo,balance',
            'receiver:id,name,email,phone,wallet_id,photo,balance',
            'reversedBy:id,name,email',
        ]);

        return view('admin.wallet_transfers.show', [
            'transfer' => $walletTransfer,
            'user' => $request->user(),
        ]);
    }

    /**
     * Reverse (refund) a wallet transfer.
     *
     * Internal transfer: the amount is taken back from the receiver
     * and added to the sender's wallet.
     *
     * External transfer: the amount is added back to the sender's wallet.
     *
     * A transfer can be reversed only once. The original record is kept
     * and marked as "reversed" for the audit trail.
     */
    public function reverse(
        Request $request,
        WalletTransfer $walletTransfer
    ): RedirectResponse {
        $this->adminOnly();

        $data = $request->validate([
            'reverse_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = $request->user();
        $reason = trim((string) ($data['reverse_reason'] ?? '')) ?: null;

        try {
            $transfer = DB::transaction(function () use ($walletTransfer, $admin, $reason) {
                $locked = WalletTransfer::whereKey($walletTransfer->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $locked->canBeReversed()) {
                    throw new \RuntimeException('This transfer is already reversed.');
                }

                $amount = round((float) $locked->amount, 2);

                if ($amount <= 0) {
                    throw new \RuntimeException('Invalid transfer amount.');
                }

                // Lock both users in ID order to avoid deadlocks.
                $userIds = array_values(array_filter([
                    $locked->sender_id,
                    $locked->isInternal() ? $locked->receiver_id : null,
                ]));

                $users = User::whereIn('id', $userIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $sender = $users->get($locked->sender_id);

                if (! $sender) {
                    throw new \RuntimeException('Sender account not found. Cannot refund this transfer.');
                }

                if ($locked->isInternal()) {
                    $receiver = $locked->receiver_id
                        ? $users->get($locked->receiver_id)
                        : null;

                    if (! $receiver) {
                        throw new \RuntimeException('Receiver account not found. Cannot take back the amount.');
                    }

                    if ((float) $receiver->balance < $amount) {
                        throw new \RuntimeException(
                            'Receiver balance is only $' . number_format((float) $receiver->balance, 2)
                            . '. Reverse needs $' . number_format($amount, 2) . '.'
                        );
                    }

                    $receiver->balance = round((float) $receiver->balance - $amount, 2);
                    $receiver->save();
                }

                $sender->balance = round((float) $sender->balance + $amount, 2);
                $sender->save();

                $locked->forceFill([
                    'status'         => WalletTransfer::STATUS_REVERSED,
                    'reversed_at'    => now(),
                    'reversed_by'    => $admin->id,
                    'reverse_reason' => $reason,
                ])->save();

                // Close the chat of an internal transfer.
                if ($locked->isInternal()) {
                    $conversation = Conversation::where('wallet_transfer_id', $locked->id)->first();

                    if ($conversation && empty($conversation->locked_at)) {
                        $conversation->forceFill([
                            'locked_at'   => now(),
                            'lock_reason' => 'Transfer reversed by admin.',
                        ])->save();
                    }
                }

                return $locked;
            });

            $transferNo = $transfer->transaction_no
                ?? 'TRA' . str_pad((string) $transfer->id, 9, '0', STR_PAD_LEFT);

            return back()->with(
                'success',
                'Transfer ' . $transferNo . ' reversed. $'
                . number_format((float) $transfer->amount, 2)
                . ' refunded to the sender wallet.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'reverse' => $exception->getMessage() ?: 'Reverse failed.',
            ]);
        }
    }
}
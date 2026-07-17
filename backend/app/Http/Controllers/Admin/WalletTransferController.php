<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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

        $query = WalletTransfer::query()
            ->with([
                'sender:id,name,email,phone,wallet_id',
                'receiver:id,name,email,phone,wallet_id',
            ]);

        if ($transferType !== 'all') {
            $query->where('transfer_type', $transferType);
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

        $stats = [
            'total_count' => WalletTransfer::count(),
            'total_volume' => (float) WalletTransfer::sum('amount'),

            'internal_count' => WalletTransfer::where(
                'transfer_type',
                WalletTransfer::TYPE_INTERNAL
            )->count(),

            'internal_volume' => (float) WalletTransfer::where(
                'transfer_type',
                WalletTransfer::TYPE_INTERNAL
            )->sum('amount'),

            'external_count' => WalletTransfer::where(
                'transfer_type',
                WalletTransfer::TYPE_EXTERNAL
            )->count(),

            'external_volume' => (float) WalletTransfer::where(
                'transfer_type',
                WalletTransfer::TYPE_EXTERNAL
            )->sum('amount'),

            'today_count' => WalletTransfer::whereDate(
                'created_at',
                today()
            )->count(),

            'today_volume' => (float) WalletTransfer::whereDate(
                'created_at',
                today()
            )->sum('amount'),
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
            'sender:id,name,email,phone,wallet_id,photo',
            'receiver:id,name,email,phone,wallet_id,photo',
        ]);

        return view('admin.wallet_transfers.show', [
            'transfer' => $walletTransfer,
            'user' => $request->user(),
        ]);
    }
}
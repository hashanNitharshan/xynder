<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletTransferController extends Controller
{
    private function adminOnly(): void
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'admin') {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->adminOnly();

        $query = WalletTransfer::with([
            'sender:id,name,email,phone,wallet_id',
            'receiver:id,name,email,phone,wallet_id',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('receiver_wallet_id', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('sender', function ($senderQuery) use ($search) {
                        $senderQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('wallet_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('receiver', function ($receiverQuery) use ($search) {
                        $receiverQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('wallet_id', 'like', "%{$search}%");
                    });
            });
        }

        $transfers = $query->latest()->paginate(30)->withQueryString();

        $stats = [
            'total_count' => WalletTransfer::count(),
            'total_volume' => WalletTransfer::sum('amount'),
            'today_count' => WalletTransfer::whereDate('created_at', today())->count(),
            'today_volume' => WalletTransfer::whereDate('created_at', today())->sum('amount'),
        ];

        return view('admin.wallet_transfers.index', compact('transfers', 'stats'));
    }
}
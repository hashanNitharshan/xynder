<?php

namespace App\Http\Controllers;

use App\Models\WalletRequest;
use App\Models\WalletTransfer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HistoryController extends Controller
{
    public function clientIndex(Request $request)
    {
        abort_unless($request->user()->role === 'client', 403);

        return $this->index($request, 'client.history');
    }

    public function merchantIndex(Request $request)
    {
        abort_unless($request->user()->role === 'merchant', 403);

        return $this->index($request, 'merchant.history');
    }

    private function index(Request $request, string $view)
    {
        $user = $request->user()->fresh();

        $requests = WalletRequest::with(['user', 'merchant'])
            ->where(function ($q) use ($user) {
                if ($user->role === 'client') {
                    $q->where('user_id', $user->id);
                } else {
                    $q->where('merchant_id', $user->id);
                }
            })
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'source_type' => 'request',
                    'created_at' => $item->created_at,
                    'data' => $item,
                ];
            });

        $transfers = WalletTransfer::with(['sender', 'receiver'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'source_type' => 'transfer',
                    'created_at' => $item->created_at,
                    'data' => $item,
                ];
            });

        $items = $requests
            ->merge($transfers)
            ->sortByDesc('created_at')
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $history = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view($view, compact('user', 'history'));
    }
}
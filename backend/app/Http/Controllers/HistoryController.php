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

    public function clientShow(Request $request, string $sourceType, int $id)
    {
        abort_unless($request->user()->role === 'client', 403);
        return $this->show($request, $sourceType, $id);
    }

    public function merchantShow(Request $request, string $sourceType, int $id)
    {
        abort_unless($request->user()->role === 'merchant', 403);
        return $this->show($request, $sourceType, $id);
    }

    private function index(Request $request, string $view)
    {
        $user = $request->user()->fresh();

        $filterQ = trim((string) $request->query('q', ''));
        $filterType = $request->query('type', 'all');
        $filterStatus = $request->query('status', 'all');
        $filterDirection = $request->query('direction', 'all');

        $requestRows = WalletRequest::with(['user', 'merchant'])
            ->where(function ($q) use ($user) {
                if ($user->role === 'client') {
                    $q->where('user_id', $user->id);
                } else {
                    $q->where('merchant_id', $user->id);
                }
            })
            ->when($filterQ !== '', function ($q) use ($filterQ) {
                $q->where(function ($qq) use ($filterQ) {
                    $qq->where('transaction_no', 'like', '%' . $filterQ . '%')
                        ->orWhere('id', 'like', '%' . $filterQ . '%')
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $filterQ . '%'))
                        ->orWhereHas('merchant', fn ($m) => $m->where('name', 'like', '%' . $filterQ . '%'));
                });
            })
            ->when($filterType === 'request', fn ($q) => $q)
            ->when($filterStatus !== 'all', fn ($q) => $q->where('status', $filterStatus))
            ->when($filterDirection === 'buy', fn ($q) => $q->where('type', 'deposit'))
            ->when($filterDirection === 'sell', fn ($q) => $q->where('type', 'withdrawal'))
            ->latest('created_at')
            ->get()
            ->map(fn ($item) => [
                'source_type' => 'request',
                'created_at' => $item->created_at,
                'data' => $item,
            ]);

        $transferRows = WalletTransfer::with(['sender', 'receiver'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->when($filterQ !== '', function ($q) use ($filterQ) {
                $q->where(function ($qq) use ($filterQ) {
                    $qq->where('transaction_no', 'like', '%' . $filterQ . '%')
                        ->orWhere('receiver_wallet_id', 'like', '%' . $filterQ . '%')
                        ->orWhere('id', 'like', '%' . $filterQ . '%')
                        ->orWhereHas('sender', fn ($s) => $s->where('name', 'like', '%' . $filterQ . '%'))
                        ->orWhereHas('receiver', fn ($r) => $r->where('name', 'like', '%' . $filterQ . '%'));
                });
            })
            ->latest('created_at')
            ->get()
            ->map(fn ($item) => [
                'source_type' => 'transfer',
                'created_at' => $item->created_at,
                'data' => $item,
            ]);

        if ($filterType === 'request') {
            $items = $requestRows;
        } elseif ($filterType === 'transfer') {
            $items = $transferRows;
        } else {
            $items = $requestRows->merge($transferRows);
        }

        if ($filterStatus !== 'all') {
            $items = $items->filter(function ($row) use ($filterStatus) {
                if (($row['source_type'] ?? null) === 'transfer') {
                    return $filterStatus === 'completed';
                }
                return strtolower((string) ($row['data']->status ?? 'pending')) === strtolower($filterStatus);
            });
        }

        if (in_array($filterDirection, ['sent', 'received'], true)) {
            $items = $items->filter(function ($row) use ($user, $filterDirection) {
                if (($row['source_type'] ?? null) !== 'transfer') {
                    return false;
                }
                $item = $row['data'];
                return $filterDirection === 'sent'
                    ? (int) $item->sender_id === (int) $user->id
                    : (int) $item->receiver_id === (int) $user->id;
            });
        }

        $items = $items->sortByDesc('created_at')->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $history = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $allItems = $requestRows->merge($transferRows);
        $stats = [
            'total' => $allItems->count(),
            'requests' => $requestRows->count(),
            'transfers' => $transferRows->count(),
            'usd' => $allItems->sum(fn ($row) => (float) ($row['data']->amount ?? 0)),
        ];

        return view($view, compact('user', 'history', 'stats'));
    }

    private function show(Request $request, string $sourceType, int $id)
    {
        $user = $request->user()->fresh();

        abort_unless(in_array($sourceType, ['request', 'transfer'], true), 404);

        if ($sourceType === 'transfer') {
            $item = WalletTransfer::with(['sender', 'receiver'])->findOrFail($id);

            abort_unless(
                (int) $item->sender_id === (int) $user->id ||
                (int) $item->receiver_id === (int) $user->id,
                403
            );
        } else {
            $item = WalletRequest::with(['user', 'merchant'])->findOrFail($id);

            abort_unless(
                (int) $item->user_id === (int) $user->id ||
                (int) $item->merchant_id === (int) $user->id,
                403
            );
        }

        return view('shared.transaction_detail', compact('user', 'item', 'sourceType'));
    }
}

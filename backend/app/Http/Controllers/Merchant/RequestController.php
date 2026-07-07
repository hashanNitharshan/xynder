<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'merchant', 403);

        $filterQ = $request->query('q');
        $filterType = $request->query('type', 'all');
        $filterStatus = $request->query('status', 'all');

        $allRequestsQuery = WalletRequest::where('merchant_id', $request->user()->id);

        $stats = [
            'total' => (clone $allRequestsQuery)->count(),
            'pending' => (clone $allRequestsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $allRequestsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $allRequestsQuery)->where('status', 'rejected')->count(),
            'closed' => (clone $allRequestsQuery)->where('status', 'closed')->count(),
        ];

        $requests = WalletRequest::with('user')
            ->where('merchant_id', $request->user()->id)
            ->when($filterQ, function ($q) use ($filterQ) {
                $q->where(function ($qq) use ($filterQ) {
                    $qq->where('transaction_no', 'like', '%' . $filterQ . '%')
                        ->orWhere('id', 'like', '%' . $filterQ . '%')
                        ->orWhereHas('user', function ($uq) use ($filterQ) {
                            $uq->where('name', 'like', '%' . $filterQ . '%')
                               ->orWhere('email', 'like', '%' . $filterQ . '%')
                               ->orWhere('wallet_id', 'like', '%' . $filterQ . '%');
                        });
                });
            })
            ->when($filterType !== 'all', function ($q) use ($filterType) {
                $q->where('type', $filterType);
            })
            ->when($filterStatus !== 'all', function ($q) use ($filterStatus) {
                $q->where('status', $filterStatus);
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('merchant.requests', compact(
            'requests',
            'stats',
            'filterQ',
            'filterType',
            'filterStatus'
        ));
    }

    public function approve(Request $request, WalletRequest $walletRequest)
    {
        $merchantUser = $request->user();

        abort_unless($merchantUser->role === 'merchant', 403);
        abort_unless((int) $walletRequest->merchant_id === (int) $merchantUser->id, 403);

        if ($walletRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request is already processed.']);
        }

        try {
            DB::transaction(function () use ($walletRequest, $merchantUser) {
                $lockedRequest = WalletRequest::where('id', $walletRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedRequest->status !== 'pending') {
                    throw new \Exception('This request is already processed.');
                }

                $client = User::where('id', $lockedRequest->user_id)->lockForUpdate()->firstOrFail();
                $merchant = User::where('id', $merchantUser->id)->lockForUpdate()->firstOrFail();

                $amount = round((float) $lockedRequest->amount, 2);

                if ($lockedRequest->type === 'deposit') {
                    if ((float) $merchant->balance < $amount) {
                        throw new \Exception('Merchant has insufficient USD balance.');
                    }

                    $client->balance = round((float) $client->balance + $amount, 2);
                    $merchant->balance = round((float) $merchant->balance - $amount, 2);
                } elseif ($lockedRequest->type === 'withdrawal') {
                    if ((float) $client->balance < $amount) {
                        throw new \Exception('Client has insufficient USD balance.');
                    }

                    $client->balance = round((float) $client->balance - $amount, 2);
                    $merchant->balance = round((float) $merchant->balance + $amount, 2);
                } else {
                    throw new \Exception('Invalid request type.');
                }

                $client->save();
                $merchant->save();

                $lockedRequest->update([
                    'status' => 'approved',
                    'approved_by' => $merchant->id,
                    'approved_at' => now(),
                ]);
            });

            return back()->with('success');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, WalletRequest $walletRequest)
    {
        $merchant = $request->user();

        abort_unless($merchant->role === 'merchant', 403);
        abort_unless((int) $walletRequest->merchant_id === (int) $merchant->id, 403);

        if ($walletRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request is already processed.']);
        }

        $walletRequest->update([
            'status' => 'rejected',
            'approved_by' => $merchant->id,
            'approved_at' => now(),
        ]);

        return back()->with('success');
    }


 public function close(Request $request, WalletRequest $walletRequest)
{
    $merchant = $request->user();

    abort_unless($merchant->role === 'merchant', 403);
    abort_unless((int) $walletRequest->merchant_id === (int) $merchant->id, 403);

    if (! in_array($walletRequest->status, ['pending', 'approved'], true)) {
        return back()->withErrors([
            'error' => 'Only pending or approved requests can be closed.',
        ]);
    }

    $walletRequest->update([
        'status' => 'closed',
        'approved_by' => $merchant->id,
        'approved_at' => now(),
    ]);

    return back()->with('success');
}

private function lockRequestChat(WalletRequest $walletRequest, string $reason): void
{
    $conversation = $walletRequest->conversation;

    if ($conversation && ! $conversation->locked_at) {
        $conversation->update([
            'locked_at' => now(),
            'lock_reason' => $reason,
        ]);
    }
}


}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletRequestController extends Controller
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

        $query = WalletRequest::with(['user', 'merchant']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('wallet_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('merchant', function ($mq) use ($search) {
                        $mq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('wallet_id', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->latest()->paginate(30)->withQueryString();

        $stats = [
            'pending' => WalletRequest::where('status', 'pending')->count(),
            'approved' => WalletRequest::where('status', 'approved')->count(),
            'rejected' => WalletRequest::where('status', 'rejected')->count(),
            'closed' => WalletRequest::where('status', 'closed')->count(),
            'total_volume' => WalletRequest::where('status', 'approved')->sum('total_amount'),
        ];

        return view('admin.wallet_requests.index', compact('requests', 'stats'));
    }

    public function approve(WalletRequest $walletRequest)
    {
        $this->adminOnly();

        if ($walletRequest->status !== 'pending') {
            return back()->withErrors('This request is already processed.');
        }

        DB::transaction(function () use ($walletRequest) {
            $request = WalletRequest::where('id', $walletRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== 'pending') {
                abort(422, 'This request is already processed.');
            }

            $client = User::where('id', $request->user_id)->lockForUpdate()->first();
            $merchant = User::where('id', $request->merchant_id)->lockForUpdate()->first();

            if (! $client) {
                abort(404, 'Client not found.');
            }

            if (! $merchant) {
                abort(404, 'Merchant not found.');
            }

            $amount = (float) $request->amount;

            if ($request->type === 'deposit') {
                if ((float) $merchant->balance < $amount) {
                    abort(422, 'Merchant has insufficient USD balance.');
                }

                $client->balance = (float) $client->balance + $amount;
                $merchant->balance = (float) $merchant->balance - $amount;
            } elseif ($request->type === 'withdrawal') {
                if ((float) $client->balance < $amount) {
                    abort(422, 'Client has insufficient USD balance.');
                }

                $client->balance = (float) $client->balance - $amount;
                $merchant->balance = (float) $merchant->balance + $amount;
            } else {
                abort(422, 'Invalid request type.');
            }

            $client->save();
            $merchant->save();

            // NOTE: no need to lock the chat here explicitly. As soon as the
            // status becomes 'approved', Conversation::lockIfExpired() will
            // lock it automatically the next time the chat is fetched
            // (chat list or messages endpoint), because it reads this status
            // live. See app/Models/Conversation.php.
            $request->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success');
    }

    public function reject(WalletRequest $walletRequest)
    {
        $this->adminOnly();

        if ($walletRequest->status !== 'pending') {
            return back()->withErrors('This request is already processed.');
        }

        $walletRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success');
    }

    public function close(WalletRequest $walletRequest)
    {
        $this->adminOnly();

        if ($walletRequest->status !== 'pending') {
            return back()->withErrors('Only pending requests can be closed.');
        }

        $walletRequest->update([
            'status' => 'closed',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success');
    }
}
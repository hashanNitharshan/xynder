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

        $requests = WalletRequest::with('user')
            ->where('merchant_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('merchant.requests', compact('requests'));
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

            return back()->with('success', 'Request approved successfully.');
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

        return back()->with('success', 'Request rejected successfully.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletRequest;
use App\Models\WalletTransfer;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function redirectByRole()
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'merchant' => redirect()->route('merchant.dashboard'),
            'client' => redirect()->route('client.dashboard'),
            default => redirect()->route('client.dashboard'),
        };
    }


    public function client()
    {
        abort_unless(Auth::user()->role === 'client', 403);

        return $this->userDashboard('client.dashboard');
    }

    public function merchant()
    {
        abort_unless(Auth::user()->role === 'merchant', 403);

        return $this->userDashboard('merchant.dashboard');
    }

    private function userDashboard(string $view)
    {
        $user = User::findOrFail(Auth::id());

        $latestRequests = WalletRequest::with(['user', 'merchant', 'conversation'])
            ->where($user->role === 'client' ? 'user_id' : 'merchant_id', $user->id)
            ->latest()
            ->take(20)
            ->get();

        $latestTransfers = WalletTransfer::with(['sender', 'receiver', 'conversation'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->take(20)
            ->get();

        $latestTransactions = collect();

        foreach ($latestRequests as $request) {
            $latestTransactions->push([
                'source_type' => 'request',
                'created_at' => $request->created_at,
                'data' => $request,
            ]);
        }

        foreach ($latestTransfers as $transfer) {
            $latestTransactions->push([
                'source_type' => 'transfer',
                'created_at' => $transfer->created_at,
                'data' => $transfer,
            ]);
        }

        $latestTransactions = $latestTransactions
            ->sortByDesc('created_at')
            ->take(20)
            ->values();

        return view($view, compact(
            'user',
            'latestRequests',
            'latestTransfers',
            'latestTransactions'
        ));
    }






























    public function admin()
{
    abort_unless(Auth::user()->role === 'admin', 403);

    $stats = [
        'clients' => User::where('role', 'client')->count(),
        'merchants' => User::where('role', 'merchant')->count(),
        'online' => User::whereIn('role', ['client', 'merchant'])->where('is_online', true)->count(),
        'pending' => WalletRequest::where('status', 'pending')->count(),
        'approved' => WalletRequest::where('status', 'approved')->count(),
        'rejected' => WalletRequest::where('status', 'rejected')->count(),
        'total_volume' => WalletRequest::where('status', 'approved')->sum('total_amount'),
    ];

    $latestRequests = WalletRequest::with(['user', 'merchant'])
        ->latest()
        ->take(8)
        ->get();

    $latestUsers = User::whereIn('role', ['client', 'merchant'])
        ->latest()
        ->take(8)
        ->get();

    $requestLabels = [];
    $buyUsdData = [];
    $sellUsdData = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);

        $requestLabels[] = $date->format('D');

        $buyUsdData[] = (float) WalletRequest::whereDate('created_at', $date)
            ->where('type', 'deposit')
            ->sum('amount');

        $sellUsdData[] = (float) WalletRequest::whereDate('created_at', $date)
            ->where('type', 'withdrawal')
            ->sum('amount');
    }

    $statusLabels = ['Pending', 'Approved', 'Rejected'];
    $statusData = [
        $stats['pending'],
        $stats['approved'],
        $stats['rejected'],
    ];

    return view('dashboards.admin', compact(
        'stats',
        'latestRequests',
        'latestUsers',
        'requestLabels',
        'buyUsdData',
        'sellUsdData',
        'statusLabels',
        'statusData'
    ));
}
}
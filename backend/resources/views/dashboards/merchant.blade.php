@extends('layouts.admin', ['title' => 'Merchant Dashboard'])

@section('content')
<div class="grid">
    <div class="kpi">
        <h3>Wallet Balance</h3>
        <p>LKR {{ number_format((float)$user->balance, 2) }}</p>
    </div>

    <div class="kpi">
        <h3>Wallet ID</h3>
        <p style="font-size:18px;">{{ $user->wallet_id ?? 'N/A' }}</p>
    </div>

    <div class="kpi">
        <h3>Verification</h3>
        <p style="font-size:18px;">
            {{ $user->is_verified ? 'Verified' : 'Not Verified' }}
        </p>
    </div>
</div>

<div class="card">
    <h2>Welcome, {{ $user->name }}</h2>
    <p style="color:#9ca3af;">This is your merchant web dashboard.</p>
</div>

<div class="card">
    <h2>Latest Customer Requests</h2>

    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($latestRequests as $request)
                <tr>
                    <td>{{ $request->user->name ?? '-' }}</td>
                    <td>{{ ucfirst($request->type ?? '-') }}</td>
                    <td>{{ number_format((float)($request->amount ?? 0), 2) }}</td>
                    <td>LKR {{ number_format((float)($request->total_amount ?? 0), 2) }}</td>
                    <td>
                        <span class="badge {{ $request->status }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No customer requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Latest Wallet Transfers</h2>

    <table>
        <thead>
            <tr>
                <th>Transfer No</th>
                <th>Amount</th>
                <th>Note</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($latestTransfers as $transfer)
                <tr>
                    <td>#{{ $transfer->id }}</td>
                    <td>LKR {{ number_format((float)($transfer->amount ?? 0), 2) }}</td>
                    <td>{{ $transfer->note ?? '-' }}</td>
                    <td>{{ $transfer->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No wallet transfers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
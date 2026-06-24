@extends('layouts.admin', ['title' => $title ?? 'Wallet Transfer'])

@push('styles')
<style>
.xyn-page{display:flex;flex-direction:column;gap:22px}
.xyn-hero-card,.xyn-transfer-card,.xyn-table-card{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:28px;
    padding:24px;
    position:relative;
    overflow:hidden;
}
.xyn-hero-card::before{
    content:'';
    position:absolute;
    right:-90px;
    top:-90px;
    width:320px;
    height:320px;
    background:radial-gradient(circle,rgba(124,92,252,.22),transparent 70%);
}
.xyn-hero-card::after{
    content:'';
    position:absolute;
    right:150px;
    bottom:-80px;
    width:220px;
    height:220px;
    background:radial-gradient(circle,rgba(6,182,212,.12),transparent 70%);
}
.xyn-hero-title{
    font-size:28px;
    font-weight:900;
    margin-bottom:6px;
}
.xyn-hero-title span{
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}
.xyn-hero-sub{
    color:#9ca3af;
    font-size:14px;
}
.xyn-wallet-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
}
.xyn-wallet-stat{
    background:linear-gradient(145deg,#131929,#0e1220);
    border:1px solid rgba(255,255,255,.07);
    border-radius:22px;
    padding:20px;
    position:relative;
    overflow:hidden;
}
.xyn-wallet-stat::before{
    content:'';
    position:absolute;
    right:-35px;
    top:-35px;
    width:110px;
    height:110px;
    border-radius:50%;
    background:rgba(124,92,252,.14);
}
.xyn-wallet-stat small{
    display:block;
    color:#9ca3af;
    font-weight:900;
    text-transform:uppercase;
    font-size:11px;
    letter-spacing:.08em;
    margin-bottom:8px;
}
.xyn-wallet-stat b{
    display:block;
    font-size:24px;
    font-weight:900;
}
.xyn-grid{
    display:grid;
    grid-template-columns:.9fr 1.1fr;
    gap:20px;
}
.xyn-section-title{
    font-size:18px;
    font-weight:900;
    margin-bottom:18px;
}
.xyn-field label{
    font-size:12px;
    font-weight:900;
    color:#9ca3af;
    text-transform:uppercase;
    letter-spacing:.08em;
}
.xyn-field input{
    width:100%;
    margin-top:8px;
    background:#131929;
    border:1px solid rgba(255,255,255,.08);
    color:#fff;
    border-radius:14px;
    padding:13px 14px;
    outline:none;
}
.xyn-field input:focus{
    border-color:rgba(124,92,252,.55);
    box-shadow:0 0 0 3px rgba(124,92,252,.12);
}
.xyn-lookup-row{
    display:flex;
    gap:10px;
    align-items:flex-end;
}
.xyn-btn-main{
    border:0;
    border-radius:14px;
    padding:13px 18px;
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    color:#fff;
    font-weight:900;
    cursor:pointer;
    white-space:nowrap;
}
.xyn-btn-main:hover{opacity:.92}
.xyn-transfer-form{
    background:linear-gradient(145deg,#1a1060,#071228);
    border:1px solid rgba(124,92,252,.25);
    border-radius:24px;
    padding:22px;
}
.xyn-transfer-inner{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:16px;
}
.xyn-submit{
    margin-top:18px;
    width:100%;
    border:0;
    border-radius:16px;
    padding:14px 20px;
    background:linear-gradient(90deg,#10b981,#06b6d4);
    color:#fff;
    font-weight:900;
    cursor:pointer;
}
.xyn-receiver-card{
    margin-top:18px;
    background:rgba(16,185,129,.10);
    border:1px solid rgba(16,185,129,.22);
    border-radius:20px;
    padding:18px;
}
.xyn-receiver-head{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}
.xyn-avatar{
    width:46px;
    height:46px;
    border-radius:15px;
    background:linear-gradient(135deg,#10b981,#06b6d4);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    color:#fff;
    font-size:18px;
}
.xyn-receiver-name{
    font-weight:900;
    font-size:16px;
}
.xyn-receiver-email{
    color:#9ca3af;
    font-size:13px;
    margin-top:2px;
}
.xyn-receiver-row{
    display:flex;
    justify-content:space-between;
    gap:15px;
    padding:10px 0;
    border-top:1px solid rgba(255,255,255,.08);
    color:#9ca3af;
}
.xyn-receiver-row strong{
    color:#fff;
    word-break:break-all;
}
.xyn-alert-success,.xyn-alert-info,.xyn-alert-error{
    padding:14px 16px;
    border-radius:16px;
    margin-bottom:18px;
    font-weight:800;
}
.xyn-alert-success{
    background:rgba(16,185,129,.12);
    border:1px solid rgba(16,185,129,.22);
    color:#d1fae5;
}
.xyn-alert-info{
    background:rgba(59,130,246,.12);
    border:1px solid rgba(59,130,246,.22);
    color:#bfdbfe;
}
.xyn-alert-error{
    background:rgba(239,68,68,.12);
    border:1px solid rgba(239,68,68,.22);
    color:#fee2e2;
}
.xyn-table-wrap{overflow-x:auto}
.xyn-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 7px;
    font-size:13px;
}
.xyn-table th{
    color:#6b7280;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.08em;
    text-align:left;
    padding:0 12px 8px;
}
.xyn-table td{
    background:#131929;
    padding:14px 12px;
    border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
}
.xyn-table td:first-child{
    border-left:1px solid rgba(255,255,255,.07);
    border-radius:15px 0 0 15px;
}
.xyn-table td:last-child{
    border-right:1px solid rgba(255,255,255,.07);
    border-radius:0 15px 15px 0;
}
.xyn-ref{
    font-family:monospace;
    color:#9ca3af;
    font-size:12px;
}
.xyn-type{
    display:inline-flex;
    align-items:center;
    gap:7px;
    font-weight:900;
}
.xyn-dot{
    width:30px;
    height:30px;
    border-radius:10px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
}
.xyn-sent{
    background:rgba(239,68,68,.13);
    color:#f87171;
}
.xyn-received{
    background:rgba(16,185,129,.13);
    color:#34d399;
}
.xyn-name-chip{
    display:flex;
    align-items:center;
    gap:9px;
}
.xyn-mini-avatar{
    width:30px;
    height:30px;
    border-radius:10px;
    background:rgba(124,92,252,.18);
    color:#a78bfa;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    font-weight:900;
}
.xyn-note{
    max-width:230px;
    color:#9ca3af;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.xyn-empty{
    text-align:center;
    color:#6b7280;
    padding:35px!important;
}
@media(max-width:1050px){
    .xyn-grid{grid-template-columns:1fr}
    .xyn-wallet-grid{grid-template-columns:1fr}
}
@media(max-width:700px){
    .xyn-lookup-row{flex-direction:column;align-items:stretch}
    .xyn-transfer-inner{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')
<div class="xyn-page">

   

    @if(session('success'))
        <div class="xyn-alert-success">
            {{ session('success') }}
            @if(session('last_transfer_no'))
                <br><strong>{{ session('last_transfer_no') }}</strong>
            @endif
        </div>
    @endif

    @if(session('lookup_success'))
        <div class="xyn-alert-info">
            {{ session('lookup_success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="xyn-alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="xyn-wallet-grid">
        <div class="xyn-wallet-stat">
            <small>Available USD Balance</small>
            <b style="color:#34d399;">${{ number_format((float)$user->balance, 2) }}</b>
        </div>

        <div class="xyn-wallet-stat">
            <small>My Wallet ID</small>
            <b style="font-size:17px;color:#a78bfa;">{{ $user->wallet_id ?? '-' }}</b>
        </div>

        <div class="xyn-wallet-stat">
            <small>Account Status</small>
            @if($user->is_verified)
                <b style="color:#34d399;">VERIFIED</b>
            @else
                <b style="color:#fbbf24;">UNVERIFIED</b>
            @endif
        </div>
    </div>

    <div class="xyn-grid">
        <div class="xyn-transfer-card">
            <h2 class="xyn-section-title">Check Receiver</h2>

            <form method="POST" action="{{ $user->role === 'merchant' ? route('merchant.transfers.lookup') : route('client.transfers.lookup') }}">
                @csrf

                <div class="xyn-lookup-row">
                    <div class="xyn-field" style="flex:1;">
                        <label>Receiver Wallet Address</label>
                        <input type="text" name="wallet_id" value="{{ old('wallet_id', session('lookup_wallet_id')) }}" required>
                    </div>

                    <button type="submit" class="xyn-btn-main">
                        Check Receiver
                    </button>
                </div>
            </form>

            @if($receiver)
                <div class="xyn-receiver-card">
                    <div class="xyn-receiver-head">
                        <div class="xyn-avatar">{{ strtoupper(substr($receiver->name ?? 'R', 0, 1)) }}</div>
                        <div>
                            <div class="xyn-receiver-name">{{ $receiver->name }}</div>
                            <div class="xyn-receiver-email">{{ $receiver->email }}</div>
                        </div>
                    </div>

                    <div class="xyn-receiver-row">
                        <span>Wallet ID</span>
                        <strong>{{ $receiver->wallet_id }}</strong>
                    </div>

                    <div class="xyn-receiver-row">
                        <span>Status</span>
                        <strong style="color:#34d399;">Receiver Found</strong>
                    </div>
                </div>
            @endif
        </div>

        <div class="xyn-transfer-form">
            <h2 class="xyn-section-title">Transfer Amount</h2>

            <form method="POST" action="{{ $user->role === 'merchant' ? route('merchant.transfers.store') : route('client.transfers.store') }}">
                @csrf

                <input type="hidden" name="receiver_wallet_id" value="{{ old('wallet_id', session('lookup_wallet_id')) }}">

                <div class="xyn-transfer-inner">
                    <div class="xyn-field">
                        <label>USD Amount</label>
                        <input type="number" step="0.01" min="1" name="amount" value="{{ old('amount') }}" required>
                    </div>

                    <div class="xyn-field">
                        <label>Note</label>
                        <input type="text" name="note" value="{{ old('note') }}" placeholder="Optional note">
                    </div>
                </div>

                <button type="submit" class="xyn-submit">
                    Transfer Now
                </button>
            </form>
        </div>
    </div>

    <div class="xyn-table-card">
        <h2 class="xyn-section-title">Transfer History</h2>

        <div class="xyn-table-wrap">
            <table class="xyn-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Type</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Receiver Wallet</th>
                        <th>USD</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transfers as $t)
                        @php
                            $isSent = $t->sender_id === $user->id;
                            $senderName = $t->sender->name ?? '-';
                            $receiverName = $t->receiver->name ?? '-';
                        @endphp

                        <tr>
                            <td class="xyn-ref">
                                {{ $t->transaction_no ?? 'TRA'.str_pad($t->id, 9, '0', STR_PAD_LEFT) }}
                            </td>

                            <td>
                                @if($isSent)
                                    <span class="xyn-type">
                                        <span class="xyn-dot xyn-sent">↑</span>
                                        SENT
                                    </span>
                                @else
                                    <span class="xyn-type">
                                        <span class="xyn-dot xyn-received">↓</span>
                                        RECEIVED
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="xyn-name-chip">
                                    <div class="xyn-mini-avatar">{{ strtoupper(substr($senderName, 0, 1)) }}</div>
                                    <span>{{ $senderName }}</span>
                                </div>
                            </td>

                            <td>
                                <div class="xyn-name-chip">
                                    <div class="xyn-mini-avatar">{{ strtoupper(substr($receiverName, 0, 1)) }}</div>
                                    <span>{{ $receiverName }}</span>
                                </div>
                            </td>

                            <td class="xyn-ref">{{ $t->receiver_wallet_id }}</td>

                            <td style="font-weight:900;{{ $isSent ? 'color:#f87171;' : 'color:#34d399;' }}">
                                {{ $isSent ? '-' : '+' }}${{ number_format((float)$t->amount, 2) }}
                            </td>

                            <td>
                                <div class="xyn-note">{{ $t->note ?? '-' }}</div>
                            </td>

                            <td style="color:#9ca3af;">
                                {{ $t->created_at?->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="xyn-empty">No transfers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $transfers->links() }}
        </div>
    </div>
</div>
@endsection
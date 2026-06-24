@extends('layouts.admin', ['title' => 'Transaction History'])

@push('styles')
<style>
.xyn-history-page{
    display:flex;
    flex-direction:column;
    gap:22px;
}

.xyn-history-hero{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:28px;
    padding:26px;
    position:relative;
    overflow:hidden;
}

.xyn-history-hero::before{
    content:'';
    position:absolute;
    right:-90px;
    top:-90px;
    width:330px;
    height:330px;
    background:radial-gradient(circle,rgba(124,92,252,.22),transparent 70%);
}

.xyn-history-hero::after{
    content:'';
    position:absolute;
    left:40%;
    bottom:-90px;
    width:240px;
    height:240px;
    background:radial-gradient(circle,rgba(6,182,212,.10),transparent 70%);
}

.xyn-history-title{
    font-size:30px;
    font-weight:900;
    margin-bottom:6px;
    position:relative;
    z-index:1;
}

.xyn-history-title span{
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}

.xyn-history-sub{
    color:#9ca3af;
    font-size:14px;
    position:relative;
    z-index:1;
}

.xyn-kpi-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:14px;
}

.xyn-kpi{
    background:linear-gradient(145deg,#131929,#0e1220);
    border:1px solid rgba(255,255,255,.07);
    border-radius:22px;
    padding:20px;
    position:relative;
    overflow:hidden;
}

.xyn-kpi::before{
    content:'';
    position:absolute;
    right:-35px;
    top:-35px;
    width:110px;
    height:110px;
    border-radius:50%;
    background:rgba(124,92,252,.13);
}

.xyn-kpi small{
    display:block;
    color:#9ca3af;
    font-weight:900;
    text-transform:uppercase;
    font-size:11px;
    letter-spacing:.08em;
    margin-bottom:8px;
}

.xyn-kpi b{
    display:block;
    font-size:25px;
    font-weight:900;
}

.xyn-history-list{
    display:flex;
    flex-direction:column;
    gap:14px;
}

.xyn-history-card{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:24px;
    padding:20px;
    position:relative;
    overflow:hidden;
    transition:.15s;
}

.xyn-history-card:hover{
    transform:translateY(-2px);
    border-color:rgba(124,92,252,.25);
    background:#131929;
}

.xyn-history-card::before{
    content:'';
    position:absolute;
    right:-60px;
    top:-60px;
    width:160px;
    height:160px;
    border-radius:50%;
    background:rgba(124,92,252,.08);
}

.xyn-history-inner{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:20px;
    position:relative;
    z-index:1;
}

.xyn-left{
    display:flex;
    gap:14px;
    min-width:0;
}

.xyn-icon{
    width:52px;
    height:52px;
    border-radius:17px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    font-weight:900;
    flex-shrink:0;
}

.xyn-icon.transfer{
    background:rgba(124,92,252,.16);
    color:#a78bfa;
}

.xyn-icon.buy{
    background:rgba(239,68,68,.14);
    color:#f87171;
}

.xyn-icon.sell{
    background:rgba(16,185,129,.14);
    color:#34d399;
}

.xyn-info{
    min-width:0;
}

.xyn-row-title{
    display:flex;
    align-items:center;
    gap:9px;
    flex-wrap:wrap;
    margin-bottom:5px;
}

.xyn-row-title h2{
    font-size:18px;
    font-weight:900;
    color:#fff;
    margin:0;
}

.xyn-type-pill{
    font-size:10px;
    font-weight:900;
    padding:4px 8px;
    border-radius:999px;
    letter-spacing:.06em;
}

.xyn-type-pill.transfer{
    background:rgba(124,92,252,.16);
    color:#a78bfa;
}

.xyn-type-pill.request{
    background:rgba(245,158,11,.14);
    color:#fbbf24;
}

.xyn-date{
    color:#6b7280;
    font-size:12px;
    margin-bottom:13px;
}

.xyn-detail-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:8px 20px;
    font-size:13px;
    color:#9ca3af;
}

.xyn-detail-grid span{
    color:#6b7280;
    font-weight:800;
}

.xyn-detail-grid strong{
    color:#e5e7eb;
    font-weight:800;
    word-break:break-word;
}

.xyn-note{
    grid-column:1 / -1;
    padding-top:4px;
}

.xyn-right{
    text-align:right;
    flex-shrink:0;
}

.xyn-amount{
    font-size:24px;
    font-weight:900;
    color:#fff;
    margin-bottom:9px;
}

.xyn-status{
    display:inline-block;
    padding:6px 11px;
    border-radius:999px;
    font-size:11px;
    font-weight:900;
    border:1px solid transparent;
}

.xyn-status.approved,
.xyn-status.completed{
    background:rgba(16,185,129,.12);
    color:#34d399;
    border-color:rgba(16,185,129,.24);
}

.xyn-status.rejected{
    background:rgba(239,68,68,.12);
    color:#f87171;
    border-color:rgba(239,68,68,.24);
}

.xyn-status.pending{
    background:rgba(245,158,11,.13);
    color:#fbbf24;
    border-color:rgba(245,158,11,.24);
}

.xyn-status.default{
    background:rgba(107,114,128,.16);
    color:#d1d5db;
    border-color:rgba(107,114,128,.24);
}

.xyn-empty{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:28px;
    padding:50px 20px;
    text-align:center;
    color:#6b7280;
}

.xyn-empty-icon{
    width:70px;
    height:70px;
    margin:0 auto 14px;
    border-radius:24px;
    background:rgba(124,92,252,.12);
    color:#a78bfa;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
}

.xyn-pager{
    margin-top:8px;
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:20px;
    padding:14px;
}

@media(max-width:1000px){
    .xyn-kpi-grid{
        grid-template-columns:repeat(2,1fr);
    }

    .xyn-detail-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:700px){
    .xyn-kpi-grid{
        grid-template-columns:1fr;
    }

    .xyn-history-inner{
        flex-direction:column;
    }

    .xyn-right{
        width:100%;
        text-align:left;
        padding-left:66px;
    }

    .xyn-history-title{
        font-size:25px;
    }
}
</style>
@endpush

@section('content')
@php
    $totalCount = $history->count();

    $transferCount = $history->filter(function($row){
        return ($row['source_type'] ?? null) === 'transfer';
    })->count();

    $requestCount = $history->filter(function($row){
        return ($row['source_type'] ?? null) !== 'transfer';
    })->count();

    $totalUsd = $history->sum(function($row){
        return (float) (($row['data']->amount ?? 0));
    });

    $completedCount = $history->filter(function($row){
        $type = $row['source_type'] ?? null;
        $item = $row['data'];

        $status = $type === 'transfer'
            ? 'completed'
            : ($item->status ?? 'pending');

        return in_array(strtolower($status), ['approved', 'completed']);
    })->count();
@endphp

<div class="xyn-history-page">

  

    <div class="xyn-kpi-grid">
        <div class="xyn-kpi">
            <small>Total Records</small>
            <b>{{ $totalCount }}</b>
        </div>

        <div class="xyn-kpi">
            <small>Requests</small>
            <b style="color:#fbbf24;">{{ $requestCount }}</b>
        </div>

        <div class="xyn-kpi">
            <small>Transfers</small>
            <b style="color:#a78bfa;">{{ $transferCount }}</b>
        </div>

        <div class="xyn-kpi">
            <small>Total USD Volume</small>
            <b style="color:#34d399;">${{ number_format((float)$totalUsd, 2) }}</b>
        </div>
    </div>

    @if($history->count() === 0)
        <div class="xyn-empty">
            <div class="xyn-empty-icon">◷</div>
            <h2 style="font-size:20px;font-weight:900;color:#fff;margin-bottom:6px;">No History Found</h2>
            <p>No wallet requests or wallet transfers available yet.</p>
        </div>
    @else
        <div class="xyn-history-list">
            @foreach($history as $row)
                @php
                    $type = $row['source_type'];
                    $item = $row['data'];

                    $isTransfer = $type === 'transfer';

                    $title = $isTransfer
                        ? 'Wallet Transfer'
                        : (($item->type ?? '') === 'withdrawal' ? 'Sell USD' : 'Buy USD');

                    $status = $isTransfer ? 'completed' : ($item->status ?? 'pending');

                    $statusClass = match(strtolower($status)) {
                        'approved', 'completed' => 'approved',
                        'rejected' => 'rejected',
                        'pending' => 'pending',
                        default => 'default',
                    };

                    $amount = number_format((float)($item->amount ?? 0), 2);
                    $transactionNo = $item->transaction_no ?? '—';

                    $iconClass = $isTransfer
                        ? 'transfer'
                        : (($item->type ?? '') === 'withdrawal' ? 'sell' : 'buy');

                    $icon = $isTransfer
                        ? '⇄'
                        : (($item->type ?? '') === 'withdrawal' ? '↑' : '↓');
                @endphp

                <div class="xyn-history-card">
                    <div class="xyn-history-inner">
                        <div class="xyn-left">
                            <div class="xyn-icon {{ $iconClass }}">
                                {{ $icon }}
                            </div>

                            <div class="xyn-info">
                                <div class="xyn-row-title">
                                    <h2>{{ $title }}</h2>
                                    <span class="xyn-type-pill {{ $isTransfer ? 'transfer' : 'request' }}">
                                        {{ $isTransfer ? 'TRANSFER' : 'REQUEST' }}
                                    </span>
                                </div>

                                <div class="xyn-date">
                                    {{ $item->created_at?->format('d M Y, h:i A') }}
                                </div>

                                <div class="xyn-detail-grid">
                                    <div>
                                        <span>Transaction No:</span>
                                        <strong>{{ $transactionNo }}</strong>
                                    </div>

                                    @if($isTransfer)
                                        <div>
                                            <span>Sender:</span>
                                            <strong>{{ $item->sender?->name ?? '—' }}</strong>
                                        </div>

                                        <div>
                                            <span>Receiver:</span>
                                            <strong>{{ $item->receiver?->name ?? '—' }}</strong>
                                        </div>

                                        <div>
                                            <span>Receiver Wallet:</span>
                                            <strong>{{ $item->receiver_wallet_id ?? '—' }}</strong>
                                        </div>
                                    @else
                                        <div>
                                            <span>Client:</span>
                                            <strong>{{ $item->user?->name ?? '—' }}</strong>
                                        </div>

                                        <div>
                                            <span>Merchant:</span>
                                            <strong>{{ $item->merchant?->name ?? '—' }}</strong>
                                        </div>

                                        <div>
                                            <span>Total INR:</span>
                                            <strong>₹{{ number_format((float)($item->total_amount ?? 0), 2) }}</strong>
                                        </div>
                                    @endif

                                    @if(!empty($item->note))
                                        <div class="xyn-note">
                                            <span>Note:</span>
                                            <strong>{{ $item->note }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="xyn-right">
                            <div class="xyn-amount">
                                ${{ $amount }}
                            </div>

                            <span class="xyn-status {{ $statusClass }}">
                                {{ strtoupper($status) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="xyn-pager">
            {{ $history->links() }}
        </div>
    @endif

</div>
@endsection
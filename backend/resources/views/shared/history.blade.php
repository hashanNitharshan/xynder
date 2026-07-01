@extends('layouts.admin', ['title' => 'Transaction History'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --dark:#101518;
    --hero:#2b2f32;
    --box:#2b2f32;
    --panel:#24292d;
    --input:#1f2428;
    --line:#3b4248;
    --red:#e8192c;
    --red2:#c91022;
    --green:#0ecb81;
    --gold:#ffc933;
    --text:#fff;
    --muted:#aeb4ba;
    --muted2:#747b82;
}

*{box-sizing:border-box}

.hist-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.hist-hero{
    position:relative;
    min-height:330px;
    padding:65px 85px 120px;
    background:var(--hero);
    overflow:hidden;
}

.hist-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.08;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.hist-hero-content{
    position:relative;
    z-index:2;
    max-width:650px;
}

.hist-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.hist-title{
    font-size:48px;
    line-height:1.15;
    font-weight:900;
    margin:0 0 20px;
}

.hist-title span{color:var(--red)}

.hist-sub{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
}

.hist-kpis{
    position:relative;
    z-index:5;
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin:-65px 85px 0;
}

.hist-kpi{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:22px;
    min-height:95px;
    display:flex;
    align-items:center;
    gap:15px;
    box-shadow:0 18px 40px rgba(0,0,0,.25);
}

.hist-kpi-icon{
    width:46px;
    height:46px;
    border-radius:50%;
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    flex-shrink:0;
}

.hist-kpi small{
    display:block;
    color:#9fa5aa;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:5px;
}

.hist-kpi b{
    display:block;
    font-size:22px;
    font-weight:900;
}

.hist-list{
    margin:34px 85px 0;
    display:flex;
    flex-direction:column;
    gap:16px;
}

.hist-card{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    overflow:hidden;
    transition:.15s;
}

.hist-card:hover{
    border-color:var(--red);
    background:#30363a;
}

.hist-card-inner{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:22px;
    padding:22px;
}

.hist-left{
    display:flex;
    gap:15px;
    min-width:0;
}

.hist-icon{
    width:54px;
    height:54px;
    border-radius:50%;
    flex:0 0 54px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:900;
}

.hist-icon.transfer{
    background:#3a1018;
    color:#ff6b7b;
}

.hist-icon.buy{
    background:#0d2b1e;
    color:var(--green);
}

.hist-icon.sell{
    background:#3a1018;
    color:#ff6b7b;
}

.hist-info{min-width:0}

.hist-row-title{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:6px;
}

.hist-row-title h2{
    margin:0;
    color:#fff;
    font-size:19px;
    font-weight:900;
}

.hist-pill{
    padding:4px 9px;
    border-radius:20px;
    font-size:10px;
    font-weight:900;
    letter-spacing:.05em;
}

.hist-pill.transfer{
    background:#3a1018;
    color:#ff6b7b;
}

.hist-pill.request{
    background:#3b2a09;
    color:var(--gold);
}

.hist-date{
    color:#747b82;
    font-size:12px;
    font-weight:800;
    margin-bottom:14px;
}

.hist-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:9px 22px;
}

.hist-grid div{
    color:#9fa5aa;
    font-size:13px;
    font-weight:800;
}

.hist-grid span{
    color:#747b82;
    font-weight:900;
}

.hist-grid strong{
    color:#e5e7eb;
    font-weight:900;
    word-break:break-word;
}

.hist-note{
    grid-column:1 / -1;
}

.hist-right{
    text-align:right;
    flex-shrink:0;
}

.hist-amount{
    font-size:26px;
    font-weight:900;
    color:#fff;
    margin-bottom:10px;
}

.hist-status{
    display:inline-block;
    padding:6px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
}

.hist-status.approved,
.hist-status.completed{
    background:#0d2b1e;
    color:var(--green);
}

.hist-status.rejected{
    background:#3a1018;
    color:#ff6b7b;
}

.hist-status.pending{
    background:#3b2a09;
    color:var(--gold);
}

.hist-status.default{
    background:#1f2428;
    color:#c9ced3;
}

.hist-empty{
    margin:34px 85px 0;
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    text-align:center;
    padding:55px 25px;
    color:#9fa5aa;
    font-weight:800;
}

.hist-empty-icon{
    width:76px;
    height:76px;
    border-radius:50%;
    margin:0 auto 16px;
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:38px;
}

.hist-empty h2{
    margin:0 0 8px;
    color:#fff;
    font-size:22px;
    font-weight:900;
}

.hist-pager{
    margin:22px 85px 0;
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:16px 20px;
}

.hist-pager nav{
    display:flex;
    justify-content:center;
}

.hist-pager svg{
    width:18px;
    height:18px;
}

@media(max-width:1100px){
    .hist-kpis{
        grid-template-columns:repeat(2,1fr);
    }

    .hist-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:760px){
    .hist-page{margin:-16px}

    .hist-hero{
        padding:45px 24px 110px;
    }

    .hist-title{
        font-size:36px;
    }

    .hist-kpis,
    .hist-list,
    .hist-empty,
    .hist-pager{
        margin-left:20px;
        margin-right:20px;
    }

    .hist-kpis{
        grid-template-columns:1fr;
    }

    .hist-card-inner{
        flex-direction:column;
    }

    .hist-right{
        width:100%;
        text-align:left;
        padding-left:69px;
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

<div class="hist-page">

    <section class="hist-hero">
        <div class="hist-hero-content">
           

            <h1 class="hist-title">
                Transaction
                <span>History</span>
            </h1>

          
        </div>
    </section>

    <section class="hist-kpis">
        <div class="hist-kpi">
            <div class="hist-kpi-icon">
                <i class="ti ti-list-details"></i>
            </div>
            <div>
                <small>Total Records</small>
                <b>{{ $totalCount }}</b>
            </div>
        </div>

        <div class="hist-kpi">
            <div class="hist-kpi-icon">
                <i class="ti ti-receipt"></i>
            </div>
            <div>
                <small>Requests</small>
                <b style="color:var(--gold)">{{ $requestCount }}</b>
            </div>
        </div>

        <div class="hist-kpi">
            <div class="hist-kpi-icon">
                <i class="ti ti-arrows-transfer-up"></i>
            </div>
            <div>
                <small>Transfers</small>
                <b style="color:#ff6b7b">{{ $transferCount }}</b>
            </div>
        </div>

        <div class="hist-kpi">
            <div class="hist-kpi-icon">
                <i class="ti ti-currency-dollar"></i>
            </div>
            <div>
                <small>Total USD Volume</small>
                <b style="color:var(--green)">${{ number_format((float)$totalUsd, 2) }}</b>
            </div>
        </div>
    </section>

    @if($history->count() === 0)
        <section class="hist-empty">
            <div class="hist-empty-icon">
                <i class="ti ti-history-off"></i>
            </div>
            <h2>No History Found</h2>
            <p>No wallet requests or wallet transfers available yet.</p>
        </section>
    @else
        <section class="hist-list">
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
                        ? 'ti-arrows-transfer-up'
                        : (($item->type ?? '') === 'withdrawal' ? 'ti-trending-down' : 'ti-trending-up');
                @endphp

                <div class="hist-card">
                    <div class="hist-card-inner">

                        <div class="hist-left">
                            <div class="hist-icon {{ $iconClass }}">
                                <i class="ti {{ $icon }}"></i>
                            </div>

                            <div class="hist-info">
                                <div class="hist-row-title">
                                    <h2>{{ $title }}</h2>

                                    <span class="hist-pill {{ $isTransfer ? 'transfer' : 'request' }}">
                                        {{ $isTransfer ? 'TRANSFER' : 'REQUEST' }}
                                    </span>
                                </div>

                                <div class="hist-date">
                                    <i class="ti ti-calendar"></i>
                                    {{ $item->created_at?->format('d M Y, h:i A') }}
                                </div>

                                <div class="hist-grid">
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
                                        <div class="hist-note">
                                            <span>Note:</span>
                                            <strong>{{ $item->note }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="hist-right">
                            <div class="hist-amount">
                                ${{ $amount }}
                            </div>

                            <span class="hist-status {{ $statusClass }}">
                                {{ strtoupper($status) }}
                            </span>
                        </div>

                    </div>
                </div>
            @endforeach
        </section>

        @if($history->hasPages())
            <section class="hist-pager">
                {{ $history->links() }}
            </section>
        @endif
    @endif

</div>
@endsection
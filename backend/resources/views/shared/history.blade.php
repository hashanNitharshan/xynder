@extends('layouts.admin', ['title' => 'Transaction History'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#0B0E11;
    --surface:#181A20;
    --surface-alt:#1E2329;
    --border:#2B3139;

    --yellow:#F0B90B;
    --orange:#F0B90B;
    --amber:#C99400;
    --yellow-dark:#C99400;
    --gold:#FFD45A;

    --red:#ef4444;
    --green:#0ecb81;

    --text:#fff;
    --muted:#848E9C;
    --muted2:#5e6673;

    --shadow:0 18px 45px rgba(0,0,0,.35);
}

*{box-sizing:border-box}

.hist-page{
    margin:-28px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding:24px 36px 48px;
}

/* Top Header */
.hist-hero{
    position:relative;
    min-height:auto;
    padding:18px 20px;
    background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);
    border:1px solid var(--border);
    border-radius:16px;
    box-shadow:var(--shadow);
    overflow:hidden;
    margin-bottom:20px;
}
.hist-hero::after{display:none}
.hist-hero-inner{position:relative;z-index:2;max-width:760px}
.hist-eyebrow{
    color:var(--yellow);
    font-size:11px;
    font-weight:800;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:5px;
}
.hist-title{
    margin:0;
    font-size:19px;
    line-height:1.25;
    font-weight:800;
    letter-spacing:-.2px;
    color:#fff;
}
.hist-title span{color:var(--yellow)}
.hist-sub{
    margin-top:4px;
    color:var(--muted);
    font-size:12px;
    line-height:1.55;
    font-weight:600;
    max-width:640px;
}

/* KPIs */
.hist-kpis{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin:0 0 20px;
    position:relative;
    z-index:5;
}
.hist-kpi{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    padding:16px 18px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:var(--shadow);
}
.hist-kpi:hover{border-color:rgba(240,185,11,.42)}
.hist-kpi-icon{
    width:40px;
    height:40px;
    border-radius:10px;
    background:rgba(240,185,11,.12);
    border:1px solid rgba(240,185,11,.35);
    color:var(--yellow);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:19px;
    flex-shrink:0;
}
.hist-kpi small{
    display:block;
    color:var(--muted);
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    margin-bottom:5px;
    letter-spacing:.05em;
}
.hist-kpi b{
    display:block;
    font-size:18px;
    font-weight:800;
    color:#fff;
}
.hist-kpi b.green{color:var(--green)}
.hist-kpi b.gold{color:var(--yellow)}

/* Panel */
.hist-panel{
    margin:0;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    box-shadow:var(--shadow);
    overflow:hidden;
}
.hist-panel:hover{border-color:rgba(240,185,11,.42)}
.hist-panel-head{
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
    font-size:15px;
    font-weight:800;
}
.hist-panel-title{display:flex;align-items:center;gap:9px}
.hist-panel-title i{color:var(--yellow);font-size:18px}

/* Filters */
.hist-filters{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    padding:16px 20px;
    background:var(--surface-alt);
    border-bottom:1px solid var(--border);
}
.hist-filters input,
.hist-filters select{
    background:var(--surface);
    border:1px solid var(--border);
    color:#fff;
    border-radius:9px;
    padding:10px 12px;
    font-size:13.5px;
    font-weight:500;
    outline:0;
    min-width:155px;
    font-family:inherit;
}
.hist-filters input{min-width:230px;flex:1}
.hist-filters input:focus,
.hist-filters select:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(240,185,11,.12);
}
.hist-filters input::placeholder{color:var(--muted2)}
.hist-reset{
    background:var(--surface);
    border:1px solid var(--border);
    color:var(--muted);
    border-radius:9px;
    padding:10px 14px;
    font-size:13px;
    font-weight:700;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:6px;
}
.hist-reset:hover{color:#fff;border-color:var(--yellow)}

/* History List */
.hist-list{display:flex;flex-direction:column}
.hist-card{
    display:block;
    text-decoration:none;
    color:inherit;
    cursor:pointer;
    background:var(--surface);
    border-bottom:1px solid var(--border);
    transition:.15s;
}
.hist-card:hover{background:var(--surface-alt);color:inherit;text-decoration:none}
.hist-card-inner{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:22px;
    padding:18px 20px;
}
.hist-left{display:flex;gap:14px;min-width:0}
.hist-icon{
    width:46px;
    height:46px;
    border-radius:12px;
    flex:0 0 46px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:21px;
}
.hist-icon.transfer,
.hist-icon.sell{
    background:rgba(239,68,68,.12);
    color:#ff9b9b;
    border:1px solid rgba(239,68,68,.35);
}
.hist-icon.buy{
    background:rgba(14,203,129,.1);
    color:var(--green);
    border:1px solid rgba(14,203,129,.3);
}
.hist-row-title{
    display:flex;
    align-items:center;
    gap:9px;
    flex-wrap:wrap;
    margin-bottom:5px;
}
.hist-row-title h2{
    margin:0;
    color:#fff;
    font-size:15px;
    font-weight:800;
}
.hist-pill{
    padding:4px 9px;
    border-radius:999px;
    font-size:10.5px;
    font-weight:800;
}
.hist-pill.transfer{background:rgba(239,68,68,.12);color:#ff9b9b}
.hist-pill.request{background:rgba(240,185,11,.12);color:var(--yellow)}
.hist-date{
    color:var(--muted2);
    font-size:12px;
    font-weight:700;
    margin-bottom:12px;
}
.hist-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:8px 20px;
}
.hist-grid div{
    color:var(--muted);
    font-size:13px;
    font-weight:600;
}
.hist-grid span{color:var(--muted2);font-weight:800}
.hist-grid strong{color:#e5e7eb;font-weight:700;word-break:break-word}
.hist-note{grid-column:1 / -1}
.hist-right{text-align:right;flex-shrink:0}
.hist-amount{
    font-size:22px;
    font-weight:800;
    color:#fff;
    margin-bottom:10px;
}
.hist-amount.sent,
.hist-amount.sell{color:#ff9b9b}
.hist-amount.received,
.hist-amount.buy{color:var(--green)}
.hist-status{
    display:inline-block;
    padding:6px 11px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
}
.hist-status.approved,
.hist-status.completed{background:rgba(14,203,129,.1);color:var(--green);border:1px solid rgba(14,203,129,.3)}
.hist-status.rejected{background:rgba(239,68,68,.1);color:#ff9b9b;border:1px solid rgba(239,68,68,.35)}
.hist-status.pending{background:rgba(240,185,11,.12);color:var(--yellow);border:1px solid rgba(240,185,11,.35)}
.hist-status.closed,
.hist-status.default{background:var(--surface-alt);color:var(--muted);border:1px solid var(--border)}

/* Empty */
.hist-empty{
    text-align:center;
    padding:55px 25px;
    color:var(--muted);
    font-size:13px;
    font-weight:600;
}
.hist-empty i{display:block;font-size:38px;color:var(--muted2);margin-bottom:12px}
.hist-empty h2{color:#fff;margin:0 0 8px;font-size:17px;font-weight:800}
.hist-empty p{margin:0}

/* Pager */
.hist-pager{
    padding:16px 20px;
    border-top:1px solid var(--border);
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:12px;
    color:var(--muted);
    font-size:13px;
    font-weight:600;
}
.hist-page-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:8px 13px;
    background:var(--surface-alt);
    border:1px solid var(--border);
    color:#fff;
    text-decoration:none;
    border-radius:8px;
    font-size:12.5px;
    font-weight:700;
    margin-left:6px;
}
.hist-page-btn:hover{border-color:var(--yellow)}
.hist-page-btn.disabled{opacity:.4;pointer-events:none}

@media(max-width:1100px){
    .hist-kpis{grid-template-columns:repeat(2,1fr)}
    .hist-grid{grid-template-columns:1fr}
}

@media(max-width:760px){
    .hist-page{margin:-18px;padding:16px 14px 30px}
    .hist-hero{border-radius:14px}
    .hist-title{font-size:19px}
    .hist-sub{font-size:12px}
    .hist-kpis{grid-template-columns:1fr;gap:14px}
    .hist-filters{flex-direction:column}
    .hist-filters input,
    .hist-filters select,
    .hist-reset{width:100%;min-width:0}
    .hist-card-inner{flex-direction:column}
    .hist-left{width:100%}
    .hist-right{width:100%;text-align:left;padding-left:60px}
    .hist-pager{flex-direction:column;align-items:flex-start}
    .hist-grid{gap:8px}
}
</style>
@endpush

@section('content')
@php
    $filterQ = request('q', '');
    $filterType = request('type', 'all');
    $filterStatus = request('status', 'all');
    $filterDirection = request('direction', 'all');

    $totalCount = $stats['total'] ?? $history->total();
    $requestCount = $stats['requests'] ?? 0;
    $transferCount = $stats['transfers'] ?? 0;
    $totalUsd = $stats['usd'] ?? 0;
@endphp

<div class="hist-page">


    <section class="hist-kpis">
        <div class="hist-kpi"><div class="hist-kpi-icon"><i class="ti ti-list-details"></i></div><div><small>Total Records</small><b>{{ $totalCount }}</b></div></div>
        <div class="hist-kpi"><div class="hist-kpi-icon"><i class="ti ti-receipt"></i></div><div><small>Requests</small><b class="gold">{{ $requestCount }}</b></div></div>
        <div class="hist-kpi"><div class="hist-kpi-icon"><i class="ti ti-arrows-transfer-up"></i></div><div><small>Transfers</small><b>{{ $transferCount }}</b></div></div>
        <div class="hist-kpi"><div class="hist-kpi-icon"><i class="ti ti-currency-dollar"></i></div><div><small>Total USD Volume</small><b class="green">${{ number_format((float)$totalUsd, 2) }}</b></div></div>
    </section>

    <section class="hist-panel">
        <div class="hist-panel-head">
            <div class="hist-panel-title"><i class="ti ti-list-details"></i> Transaction Records</div>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="hist-filters" id="historyFilterForm">
            <input type="text" name="q" id="historySearch" value="{{ $filterQ }}" placeholder="Search ref no, name, wallet..." autocomplete="off">

            <select name="type" class="history-auto-filter">
                <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>All Types</option>
                <option value="request" {{ $filterType === 'request' ? 'selected' : '' }}>Requests Only</option>
                <option value="transfer" {{ $filterType === 'transfer' ? 'selected' : '' }}>Transfers Only</option>
            </select>

            <select name="status" class="history-auto-filter">
                <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $filterStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="completed" {{ $filterStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ $filterStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="closed" {{ $filterStatus === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <select name="direction" class="history-auto-filter">
                <option value="all" {{ $filterDirection === 'all' ? 'selected' : '' }}>All Direction</option>
                <option value="buy" {{ $filterDirection === 'buy' ? 'selected' : '' }}>Buy USD</option>
                <option value="sell" {{ $filterDirection === 'sell' ? 'selected' : '' }}>Sell USD</option>
                <option value="sent" {{ $filterDirection === 'sent' ? 'selected' : '' }}>Transfer Sent</option>
                <option value="received" {{ $filterDirection === 'received' ? 'selected' : '' }}>Transfer Received</option>
            </select>

            @if($filterQ || $filterType !== 'all' || $filterStatus !== 'all' || $filterDirection !== 'all')
                <a href="{{ url()->current() }}" class="hist-reset"><i class="ti ti-x"></i> Reset</a>
            @endif
        </form>

        @if($history->count() === 0)
            <div class="hist-empty">
                <i class="ti ti-receipt-off"></i>
                <h2>No History Found</h2>
                <p>No wallet requests or wallet transfers match your filters.</p>
            </div>
        @else
            <div class="hist-list">
                @foreach($history as $row)
                    @php
                        $type = $row['source_type'];
                        $item = $row['data'];
                        $isTransfer = $type === 'transfer';
                        $isSent = $isTransfer && (int)$item->sender_id === (int)$user->id;

                        $title = $isTransfer ? 'Wallet Transfer' : (($item->type ?? '') === 'withdrawal' ? 'Sell USD' : 'Buy USD');
                        $status = $isTransfer ? 'completed' : ($item->status ?? 'pending');
                        $statusClass = match(strtolower($status)) {
                            'approved', 'completed' => 'approved',
                            'rejected' => 'rejected',
                            'pending' => 'pending',
                            'closed' => 'closed',
                            default => 'default',
                        };

                        $amount = number_format((float)($item->amount ?? 0), 2);
                        $transactionNo = $item->transaction_no ?? ($isTransfer ? 'TRA'.str_pad($item->id, 9, '0', STR_PAD_LEFT) : 'TNS'.str_pad($item->id, 9, '0', STR_PAD_LEFT));
                        $iconClass = $isTransfer ? 'transfer' : (($item->type ?? '') === 'withdrawal' ? 'sell' : 'buy');
                        $icon = $isTransfer ? 'ti-arrows-transfer-up' : (($item->type ?? '') === 'withdrawal' ? 'ti-trending-down' : 'ti-trending-up');
                        $amountClass = $isTransfer ? ($isSent ? 'sent' : 'received') : (($item->type ?? '') === 'withdrawal' ? 'sell' : 'buy');
                        $amountPrefix = $isTransfer ? ($isSent ? '−' : '+') : (($item->type ?? '') === 'withdrawal' ? '−' : '+');

                        $detailRoute = auth()->user()->role === 'merchant'
                            ? route('merchant.history.show', [$type, $item->id])
                            : route('client.history.show', [$type, $item->id]);
                    @endphp

                    <a class="hist-card" href="{{ $detailRoute }}">
                        <div class="hist-card-inner">
                            <div class="hist-left">
                                <div class="hist-icon {{ $iconClass }}"><i class="ti {{ $icon }}"></i></div>

                                <div class="hist-info">
                                    <div class="hist-row-title">
                                        <h2>{{ $title }}</h2>
                                        <span class="hist-pill {{ $isTransfer ? 'transfer' : 'request' }}">{{ $isTransfer ? 'TRANSFER' : 'REQUEST' }}</span>
                                    </div>

                                    <div class="hist-date"><i class="ti ti-calendar"></i> {{ $item->created_at?->format('d M Y, h:i A') }}</div>

                                    <div class="hist-grid">
                                        <div><span>Transaction No:</span> <strong>{{ $transactionNo }}</strong></div>

                                        @if($isTransfer)
                                            <div><span>Sender:</span> <strong>{{ $item->sender?->name ?? '—' }}</strong></div>
                                            <div><span>Receiver:</span> <strong>{{ $item->receiver?->name ?? '—' }}</strong></div>
                                            <div><span>Receiver Wallet:</span> <strong>{{ $item->receiver_wallet_id ?? '—' }}</strong></div>
                                        @else
                                            <div><span>Client:</span> <strong>{{ $item->user?->name ?? '—' }}</strong></div>
                                            <div><span>Merchant:</span> <strong>{{ $item->merchant?->name ?? '—' }}</strong></div>
                                            <div><span>Total INR:</span> <strong>₹{{ number_format((float)($item->total_amount ?? 0), 2) }}</strong></div>
                                        @endif

                                        @if(!empty($item->note))
                                            <div class="hist-note"><span>Note:</span> <strong>{{ $item->note }}</strong></div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="hist-right">
                                <div class="hist-amount {{ $amountClass }}">{{ $amountPrefix }}${{ $amount }}</div>
                                <span class="hist-status {{ $statusClass }}">{{ strtoupper($status) }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($history->hasPages())
                <div class="hist-pager">
                    <div>
                        Page {{ $history->currentPage() }} of {{ $history->lastPage() }}
                        @if($history->total())
                            · {{ $history->firstItem() }}–{{ $history->lastItem() }} of {{ $history->total() }}
                        @endif
                    </div>

                    <div>
                        @if($history->onFirstPage())
                            <span class="hist-page-btn disabled"><i class="ti ti-chevron-left"></i> Previous</span>
                        @else
                            <a class="hist-page-btn" href="{{ $history->previousPageUrl() }}"><i class="ti ti-chevron-left"></i> Previous</a>
                        @endif

                        @if($history->hasMorePages())
                            <a class="hist-page-btn" href="{{ $history->nextPageUrl() }}">Next <i class="ti ti-chevron-right"></i></a>
                        @else
                            <span class="hist-page-btn disabled">Next <i class="ti ti-chevron-right"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </section>
</div>

<script>
(function(){
    const form = document.getElementById('historyFilterForm');
    const search = document.getElementById('historySearch');

    if(form){
        document.querySelectorAll('.history-auto-filter').forEach(function(el){
            el.addEventListener('change', function(){
                form.submit();
            });
        });

        let timer = null;
        if(search){
            search.addEventListener('input', function(){
                clearTimeout(timer);
                timer = setTimeout(function(){
                    form.submit();
                }, 600);
            });
        }
    }
})();
</script>
@endsection

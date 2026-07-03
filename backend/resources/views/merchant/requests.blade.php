@extends('layouts.admin', ['title' => 'Merchant Requests'])

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
    --yellow-dark:#C99400;
    --gold:#FFD45A;
    --green:#0ecb81;
    --red:#ef4444;
    --text:#ffffff;
    --muted:#848E9C;
    --muted2:#5e6673;
    --shadow:0 18px 45px rgba(0,0,0,.35);
}
*{box-sizing:border-box}
.mreq-page{
    margin:-28px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding:24px 36px 48px;
}
.mreq-hero{
    position:relative;
    background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);
    border:1px solid var(--border);
    border-radius:16px;
    padding:18px 20px;
    box-shadow:var(--shadow);
    margin-bottom:20px;
    overflow:hidden;
}
.mreq-hero::after{display:none}
.mreq-content{position:relative;z-index:2;max-width:650px}
.mreq-eyebrow{
    color:var(--yellow);
    font-size:11px;
    font-weight:800;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:5px;
}
.mreq-title{
    font-size:19px;
    line-height:1.25;
    font-weight:800;
    margin:0;
    color:#fff;
    letter-spacing:-.2px;
}
.mreq-title span{display:inline;color:var(--yellow)}
.mreq-sub{
    color:var(--muted);
    font-size:12px;
    line-height:1.6;
    font-weight:600;
    max-width:560px;
    margin-top:4px;
}
.mreq-kpis{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:14px;
    margin:0 0 20px;
}
.mreq-kpi{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    padding:16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    min-height:82px;
    box-shadow:var(--shadow);
}
.mreq-kpi:hover{border-color:rgba(240,185,11,.42)}
.mreq-kpi-icon{
    width:40px;height:40px;border-radius:10px;
    background:rgba(240,185,11,.12);
    border:1px solid rgba(240,185,11,.35);
    color:var(--yellow);
    display:flex;align-items:center;justify-content:center;
    font-size:19px;flex-shrink:0;
}
.mreq-kpi small{
    display:block;color:var(--muted);
    font-size:11px;font-weight:800;text-transform:uppercase;
    letter-spacing:.05em;margin-bottom:6px;
}
.mreq-kpi b{display:block;font-size:18px;font-weight:800;color:#fff}
.mreq-alerts{margin:0 0 20px}
.mreq-alert{
    border-radius:10px;padding:13px 15px;margin-bottom:10px;
    font-size:13px;font-weight:700;display:flex;gap:10px;align-items:flex-start;
}
.mreq-alert.ok{background:rgba(14,203,129,.12);color:var(--green);border:1px solid rgba(14,203,129,.35)}
.mreq-alert.err{background:rgba(239,68,68,.12);color:#ff9b9b;border:1px solid rgba(239,68,68,.35)}
.mreq-alert i{font-size:17px}
.mreq-table-card{
    margin:0;background:var(--surface);border:1px solid var(--border);
    border-radius:14px;overflow:hidden;box-shadow:var(--shadow);
}
.mreq-table-card:hover{border-color:rgba(240,185,11,.42)}
.mreq-table-head{
    padding:16px 20px;border-bottom:1px solid var(--border);
    background:var(--surface);display:flex;justify-content:space-between;
    align-items:center;gap:14px;flex-wrap:wrap;
}
.mreq-table-title{display:flex;align-items:center;gap:9px;font-size:15px;font-weight:800}
.mreq-table-title i{color:var(--yellow);font-size:18px}
.mreq-filters{
    display:flex;gap:10px;flex-wrap:wrap;padding:16px 20px;
    border-bottom:1px solid var(--border);background:var(--surface-alt);
}
.mreq-filters input,.mreq-filters select{
    background:var(--bg);border:1px solid var(--border);color:#fff;
    border-radius:9px;padding:10px 12px;font-size:13px;font-weight:600;
    outline:0;min-width:160px;font-family:inherit;
}
.mreq-filters input{min-width:230px}
.mreq-filters input:focus,.mreq-filters select:focus{border-color:var(--yellow);box-shadow:0 0 0 3px rgba(240,185,11,.12)}
.mreq-filters input::placeholder{color:var(--muted2)}
.mreq-reset{
    display:inline-flex;align-items:center;gap:6px;background:var(--surface);
    border:1px solid var(--border);color:var(--muted);border-radius:9px;
    padding:10px 14px;font-size:13px;font-weight:700;text-decoration:none;
}
.mreq-reset:hover{color:#fff;border-color:var(--yellow)}
.mreq-table-wrap{overflow-x:auto}
.mreq-table{width:100%;min-width:1160px;border-collapse:collapse}
.mreq-table th{
    color:var(--muted);font-size:11px;text-transform:uppercase;text-align:left;
    padding:13px 16px;background:var(--surface-alt);font-weight:800;letter-spacing:.04em;
}
.mreq-table td{
    padding:15px 16px;border-top:1px solid var(--border);color:#d5dade;
    font-size:13px;font-weight:600;white-space:nowrap;vertical-align:middle;
}
.mreq-table tr:hover td{background:var(--surface-alt)}
.mreq-ref{font-family:monospace;color:var(--yellow);font-weight:800}
.mreq-date{color:var(--muted);font-size:12px;font-weight:700}
.mreq-client{display:flex;align-items:center;gap:10px}
.mreq-avatar{width:34px;height:34px;border-radius:50%;background:rgba(240,185,11,.12);border:1px solid rgba(240,185,11,.35);color:var(--yellow);display:flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0;font-size:13px}
.mreq-client strong{color:#fff;font-weight:800;font-size:13px}
.mreq-client small{color:var(--muted);font-size:12px}
.mreq-type{display:inline-flex;align-items:center;gap:8px;font-weight:800;font-size:13px}
.mreq-type.buy{color:var(--green)}
.mreq-type.sell{color:#ff9b9b}
.mreq-type-icon{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:15px}
.mreq-type-icon.buy{background:rgba(14,203,129,.12);color:var(--green)}
.mreq-type-icon.sell{background:rgba(239,68,68,.12);color:#ff9b9b}
.badge{display:inline-block;padding:6px 11px;border-radius:999px;font-size:11px;font-weight:800;text-transform:uppercase}
.badge.pending{background:rgba(240,185,11,.12);color:var(--yellow);border:1px solid rgba(240,185,11,.35)}
.badge.approved,.badge.completed{background:rgba(14,203,129,.12);color:var(--green);border:1px solid rgba(14,203,129,.35)}
.badge.rejected{background:rgba(239,68,68,.12);color:#ff9b9b;border:1px solid rgba(239,68,68,.35)}
.badge.closed{background:var(--surface-alt);color:var(--muted);border:1px solid var(--border)}
.mreq-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.mreq-icon-btn{width:34px;height:34px;border:0;border-radius:9px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:17px;font-weight:800}
.mreq-icon-btn.accept{background:var(--green);color:#06140e}
.mreq-icon-btn.reject{background:var(--red);color:#fff}
.mreq-icon-btn.close{background:#111827;color:#fff;border:1px solid #2B3139}
.mreq-icon-btn:hover{filter:brightness(1.12)}
.mreq-empty{text-align:center;color:var(--muted)!important;padding:42px!important;font-weight:700!important}
.mreq-pager{padding:16px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;color:var(--muted);font-size:13px;font-weight:600}
.mreq-page-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 13px;background:var(--surface-alt);border:1px solid var(--border);color:#fff;text-decoration:none;border-radius:8px;font-size:12.5px;font-weight:700;margin-left:6px}
.mreq-page-btn:hover{border-color:var(--yellow)}
.mreq-page-btn.disabled{opacity:.4;pointer-events:none}
@media(max-width:1200px){.mreq-kpis{grid-template-columns:repeat(2,1fr)}}
@media(max-width:760px){
    .mreq-page{margin:-18px;padding:16px 14px 30px}
    .mreq-kpis{grid-template-columns:1fr}
    .mreq-filters{flex-direction:column}.mreq-filters input,.mreq-filters select,.mreq-reset{width:100%;min-width:0}
    .mreq-table{min-width:0}.mreq-table thead{display:none}.mreq-table,.mreq-table tbody,.mreq-table tr,.mreq-table td{display:block;width:100%}
    .mreq-table tr{border-top:1px solid var(--border);padding:14px 16px}
    .mreq-table td{border-top:0;padding:7px 0;display:flex;justify-content:space-between;gap:12px;white-space:normal;align-items:center}
    .mreq-table td::before{content:attr(data-label);color:var(--muted2);font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;flex-shrink:0}
    .mreq-pager{align-items:flex-start;flex-direction:column}
}
</style>
@endpush

@section('content')
@php
    $total = $stats['total'] ?? $requests->total();
    $pending = $stats['pending'] ?? 0;
    $approved = $stats['approved'] ?? 0;
    $rejected = $stats['rejected'] ?? 0;
    $closed = $stats['closed'] ?? 0;

    $filterQ = $filterQ ?? request('q');
    $filterType = $filterType ?? request('type', 'all');
    $filterStatus = $filterStatus ?? request('status', 'all');
@endphp

<div class="mreq-page">

    <section class="mreq-hero">
        <div class="mreq-content">
            <div class="mreq-eyebrow">BITXNOW WALLET</div>

            <h1 class="mreq-title">
                Merchant
                <span>Requests</span>
            </h1>

            <div class="mreq-sub">
                Review client buy and sell USD requests, approve valid requests, reject incorrect requests, or keep closed records for audit.
            </div>
        </div>
    </section>

    <section class="mreq-kpis">
        <div class="mreq-kpi"><div><small>Total Requests</small><b>{{ $total }}</b></div><div class="mreq-kpi-icon"><i class="ti ti-list-details"></i></div></div>
        <div class="mreq-kpi"><div><small>Pending</small><b style="color:var(--yellow)">{{ $pending }}</b></div><div class="mreq-kpi-icon"><i class="ti ti-clock"></i></div></div>
        <div class="mreq-kpi"><div><small>Approved</small><b style="color:var(--green)">{{ $approved }}</b></div><div class="mreq-kpi-icon"><i class="ti ti-circle-check"></i></div></div>
        <div class="mreq-kpi"><div><small>Rejected</small><b style="color:#ff9b9b">{{ $rejected }}</b></div><div class="mreq-kpi-icon"><i class="ti ti-circle-x"></i></div></div>
        <div class="mreq-kpi"><div><small>Closed</small><b style="color:var(--muted)">{{ $closed }}</b></div><div class="mreq-kpi-icon"><i class="ti ti-lock"></i></div></div>
    </section>

    <div class="mreq-alerts">
        @if(session('success'))
            <div class="mreq-alert ok">
                <i class="ti ti-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mreq-alert err">
                <i class="ti ti-alert-triangle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <section class="mreq-table-card">
        <div class="mreq-table-head">
            <div class="mreq-table-title">
                <i class="ti ti-receipt"></i>
                Incoming Requests
            </div>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="mreq-filters" id="merchantRequestFilterForm">
            <input type="text"
                   name="q"
                   id="filterQ"
                   value="{{ $filterQ }}"
                   placeholder="Search ref no, client, email..."
                   autocomplete="off">

            <select name="type" class="auto-filter">
                <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>All Types</option>
                <option value="deposit" {{ $filterType === 'deposit' ? 'selected' : '' }}>Buy USD</option>
                <option value="withdrawal" {{ $filterType === 'withdrawal' ? 'selected' : '' }}>Sell USD</option>
            </select>

            <select name="status" class="auto-filter">
                <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $filterStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $filterStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="closed" {{ $filterStatus === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            @if($filterQ || $filterType !== 'all' || $filterStatus !== 'all')
                <a href="{{ url()->current() }}" class="mreq-reset">
                    <i class="ti ti-x"></i> Reset
                </a>
            @endif
        </form>

        <div class="mreq-table-wrap">
            <table class="mreq-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Trans No</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>USD</th>
                        <th>Total INR</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $r)
                        @php
                            $clientName = $r->user->name ?? '-';
                            $isBuy = $r->type === 'deposit';
                            $refNo = $r->transaction_no ?? 'TNS'.str_pad($r->id, 9, '0', STR_PAD_LEFT);
                        @endphp

                        <tr>
                            <td data-label="Date">
                                <span class="mreq-date">{{ $r->created_at?->format('d M Y, H:i') }}</span>
                            </td>

                            <td data-label="Trans No">
                                <span class="mreq-ref">{{ $refNo }}</span>
                            </td>

                            <td data-label="Client">
                                <div class="mreq-client">
                                    <div class="mreq-avatar">
                                        {{ strtoupper(substr($clientName, 0, 1)) }}
                                    </div>

                                    <div>
                                        <strong>{{ $clientName }}</strong><br>
                                        <small>{{ $r->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Type">
                                <span class="mreq-type {{ $isBuy ? 'buy' : 'sell' }}">
                                    <span class="mreq-type-icon {{ $isBuy ? 'buy' : 'sell' }}">
                                        <i class="ti {{ $isBuy ? 'ti-trending-up' : 'ti-trending-down' }}"></i>
                                    </span>
                                    {{ $isBuy ? 'BUY USD' : 'SELL USD' }}
                                </span>
                            </td>

                            <td data-label="USD" style="font-weight:900;color:#fff;">
                                ${{ number_format((float)$r->amount, 2) }}
                            </td>

                            <td data-label="Total INR" style="font-weight:900;color:var(--green);">
                                ₹{{ number_format((float)$r->total_amount, 2) }}
                            </td>

                            <td data-label="Status">
                                <span class="badge {{ $r->status }}">
                                    {{ strtoupper($r->status) }}
                                </span>
                            </td>

 <td data-label="Action">
    <div class="mreq-actions">

        @if($r->status === 'pending')
            <form method="POST" action="{{ route('merchant.requests.approve', $r) }}">
                @csrf
                <button class="mreq-icon-btn accept"
                        title="Approve"
                        onclick="return confirm('Approve this request?')">
                    <i class="ti ti-check"></i>
                </button>
            </form>

            <form method="POST" action="{{ route('merchant.requests.reject', $r) }}">
                @csrf
                <button class="mreq-icon-btn reject"
                        title="Reject"
                        onclick="return confirm('Reject this request?')">
                    <i class="ti ti-x"></i>
                </button>
            </form>

            <form method="POST" action="{{ route('merchant.requests.close', $r) }}">
                @csrf
                <button class="mreq-icon-btn close"
                        title="Close"
                        onclick="return confirm('Close this request?')">
                    <i class="ti ti-lock"></i>
                </button>
            </form>

        @elseif($r->status === 'approved')
            <form method="POST" action="{{ route('merchant.requests.close', $r) }}">
                @csrf
                <button class="mreq-icon-btn close"
                        title="Close"
                        onclick="return confirm('Close this request?')">
                    <i class="ti ti-lock"></i>
                </button>
            </form>

        @else
            <span style="color:#848E9C;">—</span>
        @endif

    </div>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="mreq-empty">
                                No requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="mreq-pager">
                <div>
                    Page {{ $requests->currentPage() }} of {{ $requests->lastPage() }}
                    @if($requests->total())
                        · {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }}
                    @endif
                </div>

                <div>
                    @if($requests->onFirstPage())
                        <span class="mreq-page-btn disabled">
                            <i class="ti ti-chevron-left"></i> Previous
                        </span>
                    @else
                        <a class="mreq-page-btn" href="{{ $requests->previousPageUrl() }}">
                            <i class="ti ti-chevron-left"></i> Previous
                        </a>
                    @endif

                    @if($requests->hasMorePages())
                        <a class="mreq-page-btn" href="{{ $requests->nextPageUrl() }}">
                            Next <i class="ti ti-chevron-right"></i>
                        </a>
                    @else
                        <span class="mreq-page-btn disabled">
                            Next <i class="ti ti-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </section>

</div>
@endsection

@push('scripts')
<script>
(function(){
    const filterForm = document.getElementById('merchantRequestFilterForm');
    const filterQ = document.getElementById('filterQ');

    if(filterForm){
        document.querySelectorAll('.auto-filter').forEach(function(el){
            el.addEventListener('change', function(){
                filterForm.submit();
            });
        });

        let filterTimer = null;
        if(filterQ){
            filterQ.addEventListener('input', function(){
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function(){
                    filterForm.submit();
                }, 600);
            });
        }
    }
})();
</script>
@endpush

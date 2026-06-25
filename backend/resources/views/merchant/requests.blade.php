@extends('layouts.admin', ['title' => 'Merchant Requests'])

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

.mreq-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.mreq-hero{
    position:relative;
    min-height:320px;
    padding:65px 85px 120px;
    background:var(--hero);
    overflow:hidden;
}

.mreq-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.08;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.mreq-content{
    position:relative;
    z-index:2;
    max-width:650px;
}

.mreq-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.mreq-title{
    font-size:48px;
    line-height:1.15;
    font-weight:900;
    margin:0 0 20px;
}

.mreq-title span{color:var(--red)}

.mreq-sub{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
}

.mreq-kpis{
    position:relative;
    z-index:5;
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin:-65px 85px 0;
}

.mreq-kpi{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:22px;
    display:flex;
    align-items:center;
    gap:15px;
    min-height:96px;
    box-shadow:0 18px 40px rgba(0,0,0,.25);
}

.mreq-kpi-icon{
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

.mreq-kpi small{
    display:block;
    color:#9fa5aa;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:5px;
}

.mreq-kpi b{
    display:block;
    font-size:24px;
    font-weight:900;
}

.mreq-table-card{
    margin:34px 85px 0;
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    overflow:hidden;
}

.mreq-table-head{
    padding:20px 24px;
    border-bottom:1px solid var(--line);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:14px;
    flex-wrap:wrap;
}

.mreq-table-title{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:18px;
    font-weight:900;
}

.mreq-table-title i{color:var(--red)}

.mreq-table-wrap{overflow-x:auto}

.mreq-table{
    width:100%;
    min-width:1050px;
    border-collapse:collapse;
}

.mreq-table th{
    color:#9fa5aa;
    font-size:11px;
    text-transform:uppercase;
    text-align:left;
    padding:14px 16px;
    background:#24292d;
}

.mreq-table td{
    padding:16px;
    border-top:1px solid #3a4147;
    color:#c9ced3;
    font-size:13px;
    font-weight:700;
    white-space:nowrap;
    vertical-align:middle;
}

.mreq-table tr:hover td{
    background:#30363a;
}

.mreq-ref{
    font-family:monospace;
    color:#9fa5aa;
}

.mreq-client{
    display:flex;
    align-items:center;
    gap:10px;
}

.mreq-avatar{
    width:38px;
    height:38px;
    border-radius:50%;
    background:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    color:#fff;
    flex-shrink:0;
}

.mreq-client strong{
    color:#fff;
    font-weight:900;
}

.mreq-client small{
    color:#9fa5aa;
    font-size:12px;
}

.mreq-type{
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-weight:900;
}

.mreq-type.buy{color:var(--green)}
.mreq-type.sell{color:#ff6b7b}

.mreq-type-icon{
    width:32px;
    height:32px;
    border-radius:50%;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:15px;
}

.mreq-type-icon.buy{
    background:#0d2b1e;
    color:var(--green);
}

.mreq-type-icon.sell{
    background:#3a1018;
    color:#ff6b7b;
}

.badge{
    display:inline-block;
    padding:6px 11px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
}

.badge.pending{
    background:#3b2a09;
    color:var(--gold);
}

.badge.approved,
.badge.completed{
    background:#0d2b1e;
    color:var(--green);
}

.badge.rejected{
    background:#3a1018;
    color:#ff6b7b;
}

.mreq-view{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#1f2428;
    border:1px solid var(--line);
    color:#fff;
    border-radius:4px;
    padding:9px 12px;
    font-weight:900;
    font-size:12px;
}

.mreq-view:hover{
    background:var(--red);
    border-color:var(--red);
}

.mreq-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.mreq-btn{
    border:0;
    border-radius:4px;
    padding:9px 13px;
    color:#fff;
    font-weight:900;
    cursor:pointer;
    font-size:12px;
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.mreq-btn.accept{
    background:var(--green);
}

.mreq-btn.reject{
    background:var(--red);
}

.mreq-btn.accept:hover{
    background:#0aac6c;
}

.mreq-btn.reject:hover{
    background:var(--red2);
}

.mreq-empty{
    text-align:center;
    color:#9fa5aa!important;
    padding:45px!important;
    font-weight:800!important;
}

.mreq-pager{
    padding:18px 22px;
    border-top:1px solid var(--line);
}

.mreq-pager nav{
    display:flex;
    justify-content:center;
}

.mreq-pager svg{
    width:18px;
    height:18px;
}

@media(max-width:1100px){
    .mreq-kpis{
        grid-template-columns:repeat(2,1fr);
    }
}

@media(max-width:760px){
    .mreq-page{margin:-16px}

    .mreq-hero{
        padding:45px 24px 110px;
    }

    .mreq-title{
        font-size:36px;
    }

    .mreq-kpis,
    .mreq-table-card{
        margin-left:20px;
        margin-right:20px;
    }

    .mreq-kpis{
        grid-template-columns:1fr;
    }
}
</style>
@endpush

@section('content')
@php
    $total = $requests->count();
    $pending = $requests->where('status', 'pending')->count();
    $approved = $requests->where('status', 'approved')->count();
    $rejected = $requests->where('status', 'rejected')->count();
@endphp

<div class="mreq-page">

    <section class="mreq-hero">
        <div class="mreq-content">
            <div class="mreq-eyebrow">Xynder Wallet</div>

            <h1 class="mreq-title">
                Merchant
                <span>Requests</span>
            </h1>

            <div class="mreq-sub">
                Review incoming client buy and sell USD requests. Approve valid requests,
                reject incorrect requests, and view uploaded payment slips.
            </div>
        </div>
    </section>

    <section class="mreq-kpis">
        <div class="mreq-kpi">
            <div class="mreq-kpi-icon">
                <i class="ti ti-list-details"></i>
            </div>
            <div>
                <small>Total Requests</small>
                <b>{{ $total }}</b>
            </div>
        </div>

        <div class="mreq-kpi">
            <div class="mreq-kpi-icon">
                <i class="ti ti-clock"></i>
            </div>
            <div>
                <small>Pending</small>
                <b style="color:var(--gold)">{{ $pending }}</b>
            </div>
        </div>

        <div class="mreq-kpi">
            <div class="mreq-kpi-icon">
                <i class="ti ti-circle-check"></i>
            </div>
            <div>
                <small>Approved</small>
                <b style="color:var(--green)">{{ $approved }}</b>
            </div>
        </div>

        <div class="mreq-kpi">
            <div class="mreq-kpi-icon">
                <i class="ti ti-circle-x"></i>
            </div>
            <div>
                <small>Rejected</small>
                <b style="color:#ff6b7b">{{ $rejected }}</b>
            </div>
        </div>
    </section>

    <section class="mreq-table-card">
        <div class="mreq-table-head">
            <div class="mreq-table-title">
                <i class="ti ti-receipt"></i>
                Incoming Requests
            </div>
        </div>

        <div class="mreq-table-wrap">
            <table class="mreq-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>USD</th>
                        <th>Total INR</th>
                        <th>Status</th>
                        <th>Slip</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $r)
                        @php
                            $clientName = $r->user->name ?? '-';
                            $isBuy = $r->type === 'deposit';
                        @endphp

                        <tr>
                            <td>
                                <span class="mreq-ref">
                                    {{ $r->transaction_no ?? 'TNS'.str_pad($r->id, 9, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <td>
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

                            <td>
                                <span class="mreq-type {{ $isBuy ? 'buy' : 'sell' }}">
                                    <span class="mreq-type-icon {{ $isBuy ? 'buy' : 'sell' }}">
                                        <i class="ti {{ $isBuy ? 'ti-trending-up' : 'ti-trending-down' }}"></i>
                                    </span>
                                    {{ $isBuy ? 'BUY USD' : 'SELL USD' }}
                                </span>
                            </td>

                            <td style="font-weight:900;color:#fff;">
                                ${{ number_format((float)$r->amount, 2) }}
                            </td>

                            <td style="font-weight:900;color:var(--green);">
                                ₹{{ number_format((float)$r->total_amount, 2) }}
                            </td>

                            <td>
                                <span class="badge {{ $r->status }}">
                                    {{ strtoupper($r->status) }}
                                </span>
                            </td>

                            <td>
                                @if($r->payment_slip_url)
                                    <a class="mreq-view" href="{{ $r->payment_slip_url }}" target="_blank">
                                        <i class="ti ti-eye"></i>
                                        View
                                    </a>
                                @else
                                    <span style="color:#747b82;">—</span>
                                @endif
                            </td>

                            <td>
                                @if($r->status === 'pending')
                                    <div class="mreq-actions">
                                        <form method="POST" action="{{ route('merchant.requests.approve', $r) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="mreq-btn accept"
                                                    onclick="return confirm('Approve this request?')">
                                                <i class="ti ti-check"></i>
                                                Accept
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('merchant.requests.reject', $r) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="mreq-btn reject"
                                                    onclick="return confirm('Reject this request?')">
                                                <i class="ti ti-x"></i>
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span style="color:#747b82;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="mreq-empty">
                                No requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="mreq-pager">
                {{ $requests->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
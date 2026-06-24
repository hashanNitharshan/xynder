@extends('layouts.admin', ['title' => 'Merchant Requests'])

@push('styles')
<style>
.xyn-page{display:flex;flex-direction:column;gap:22px}
.xyn-hero-card,.xyn-table-card{
    background:#0e1220;border:1px solid rgba(255,255,255,.07);
    border-radius:28px;padding:24px;position:relative;overflow:hidden;
}
.xyn-hero-card::before{
    content:'';position:absolute;right:-90px;top:-90px;width:300px;height:300px;
    background:radial-gradient(circle,rgba(124,92,252,.20),transparent 70%);
}
.xyn-hero-title{font-size:28px;font-weight:900;margin-bottom:6px}
.xyn-hero-title span{
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
}
.xyn-hero-sub{color:#9ca3af;font-size:14px}
.xyn-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.xyn-kpi{
    background:linear-gradient(145deg,#131929,#0e1220);
    border:1px solid rgba(255,255,255,.07);border-radius:22px;padding:18px;
}
.xyn-kpi small{color:#9ca3af;font-weight:900;text-transform:uppercase;font-size:11px;letter-spacing:.08em}
.xyn-kpi b{display:block;font-size:26px;margin-top:8px}
.xyn-table-wrap{overflow-x:auto}
.xyn-table{width:100%;border-collapse:separate;border-spacing:0 7px;font-size:13px}
.xyn-table th{
    color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.08em;
    text-align:left;padding:0 12px 8px;
}
.xyn-table td{
    background:#131929;padding:14px 12px;border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
}
.xyn-table td:first-child{border-left:1px solid rgba(255,255,255,.07);border-radius:15px 0 0 15px}
.xyn-table td:last-child{border-right:1px solid rgba(255,255,255,.07);border-radius:0 15px 15px 0}
.xyn-ref{font-family:monospace;color:#9ca3af;font-size:12px}
.xyn-client{display:flex;align-items:center;gap:10px}
.xyn-avatar{
    width:36px;height:36px;border-radius:12px;
    background:linear-gradient(135deg,#7c5cfc,#06b6d4);
    display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;
}
.xyn-client small{color:#9ca3af}
.xyn-type{display:inline-flex;align-items:center;gap:7px;font-weight:900}
.xyn-dot{width:28px;height:28px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center}
.xyn-buy{background:rgba(239,68,68,.13);color:#f87171}
.xyn-sell{background:rgba(16,185,129,.13);color:#34d399}
.xyn-view{
    background:#2563eb;color:#fff;border-radius:11px;padding:8px 12px;font-weight:900;font-size:12px;
}
.xyn-actions{display:flex;gap:8px;flex-wrap:wrap}
.xyn-accept,.xyn-reject{
    border:0;border-radius:12px;padding:9px 13px;color:#fff;font-weight:900;cursor:pointer;
}
.xyn-accept{background:#16a34a}
.xyn-reject{background:#dc2626}
.xyn-empty{text-align:center;color:#6b7280;padding:35px!important}
@media(max-width:1000px){.xyn-kpi-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:650px){.xyn-kpi-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $total = $requests->count();
    $pending = $requests->where('status','pending')->count();
    $approved = $requests->where('status','approved')->count();
    $rejected = $requests->where('status','rejected')->count();
@endphp

<div class="xyn-page">

   

    <div class="xyn-kpi-grid">
        <div class="xyn-kpi">
            <small>Total Requests</small>
            <b>{{ $total }}</b>
        </div>
        <div class="xyn-kpi">
            <small>Pending</small>
            <b style="color:#fbbf24">{{ $pending }}</b>
        </div>
        <div class="xyn-kpi">
            <small>Approved</small>
            <b style="color:#34d399">{{ $approved }}</b>
        </div>
        <div class="xyn-kpi">
            <small>Rejected</small>
            <b style="color:#f87171">{{ $rejected }}</b>
        </div>
    </div>

    <div class="xyn-table-card">
        <h2 style="font-size:18px;font-weight:900;margin-bottom:18px;">Incoming Requests</h2>

        <div class="xyn-table-wrap">
            <table class="xyn-table">
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
                        @endphp
                        <tr>
                            <td class="xyn-ref">{{ $r->transaction_no ?? 'TNS'.str_pad($r->id, 9, '0', STR_PAD_LEFT) }}</td>

                            <td>
                                <div class="xyn-client">
                                    <div class="xyn-avatar">{{ strtoupper(substr($clientName, 0, 1)) }}</div>
                                    <div>
                                        <strong>{{ $clientName }}</strong><br>
                                        <small>{{ $r->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="xyn-type">
                                    <span class="xyn-dot {{ $r->type === 'deposit' ? 'xyn-buy' : 'xyn-sell' }}">
                                        {{ $r->type === 'deposit' ? 'B' : 'S' }}
                                    </span>
                                    {{ $r->type === 'deposit' ? 'BUY USD' : 'SELL USD' }}
                                </span>
                            </td>

                            <td style="font-weight:900;">${{ number_format((float)$r->amount, 2) }}</td>
                            <td style="font-weight:900;color:#34d399;">₹{{ number_format((float)$r->total_amount, 2) }}</td>
                            <td><span class="badge {{ $r->status }}">{{ strtoupper($r->status) }}</span></td>

                            <td>
                                @if($r->payment_slip_url)
                                    <a class="xyn-view" href="{{ $r->payment_slip_url }}" target="_blank">View</a>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if($r->status === 'pending')
                                    <div class="xyn-actions">
                                        <form method="POST" action="{{ route('merchant.requests.approve', $r) }}">
                                            @csrf
                                            <button class="xyn-accept" onclick="return confirm('Approve this request?')">Accept</button>
                                        </form>

                                        <form method="POST" action="{{ route('merchant.requests.reject', $r) }}">
                                            @csrf
                                            <button class="xyn-reject" onclick="return confirm('Reject this request?')">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="xyn-empty">No requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
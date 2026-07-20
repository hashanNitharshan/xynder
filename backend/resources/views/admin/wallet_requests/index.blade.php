@extends('layouts.admin', ['title' => 'P2P Requests'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{--dark:#0B0E11;--hero:#181A20;--box:#181A20;--panel:#1E2329;--input:#0B0E11;--line:#2B3139;--yellow:#F0B90B;--yellow2:#C99400;--green:#0ecb81;--sell:#ef4444;--gold:#FFD45A;--text:#fff;--muted:#848E9C;--muted2:#5e6673;}
*{box-sizing:border-box}
.ad-page{margin:-24px;min-height:100vh;background:var(--dark);color:var(--text);font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;padding-bottom:60px;}
.ad-hero{background:var(--hero);padding:65px 85px 120px;position:relative;overflow:hidden;}
.ad-hero::after{content:"";position:absolute;inset:0;opacity:.07;background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);background-size:260px 260px;}
.ad-hero-inner{position:relative;z-index:2;max-width:680px;}
.ad-eyebrow{color:var(--yellow);font-size:12px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;margin-bottom:14px;}
.ad-title-main{font-size:34px;line-height:1.15;font-weight:800;margin:0 0 18px;}
.ad-title-main span{color:var(--yellow);display:block;}
.ad-subtitle{color:#848E9C;font-size:15px;line-height:1.7;font-weight:700;max-width:580px;}
.ad-wrap{position:relative;z-index:5;margin:-82px 45px 0;}
.ad-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:28px;}
.ad-kpi{background:var(--box);border:1px solid var(--line);border-radius:6px;padding:20px;min-height:118px;position:relative;overflow:hidden;}
.ad-kpi::after{content:"";position:absolute;right:-38px;top:-38px;width:115px;height:115px;border-radius:50%;background:rgba(240,185,11,.12);}
.ad-kpi-icon{width:42px;height:42px;border-radius:50%;background:rgba(240,185,11,.12);color:var(--yellow);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:14px;}
.ad-kpi-label{color:var(--muted);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;}
.ad-kpi-value{font-size:22px;font-weight:800;margin-top:6px;}
.ad-card{background:var(--box);border:1.5px solid var(--yellow);border-radius:7px;overflow:hidden;box-shadow:0 18px 40px rgba(0,0,0,.28);}
.ad-card-head{background:var(--panel);border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:18px 22px;}
.ad-title{font-size:15px;font-weight:800;margin:0;display:flex;align-items:center;gap:9px;}
.ad-title i{color:var(--yellow);}
.ad-filters{display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:10px;padding:16px 22px;border-bottom:1px solid var(--line);background:var(--box);}
.ad-search-wrap{position:relative;}
.ad-search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:16px;}
.ad-input,.ad-select{width:100%;height:42px;border:1px solid var(--line);border-radius:4px;padding:0 13px;font-size:13px;background:var(--input);color:var(--text);outline:0;font-weight:700;}
.ad-search-wrap .ad-input{padding-left:36px;}
.ad-input:focus,.ad-select:focus{border-color:var(--yellow);box-shadow:0 0 0 3px rgba(240,185,11,.12);}
.ad-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 13px;border-radius:4px;font-size:12px;font-weight:900;border:0;cursor:pointer;text-decoration:none;white-space:nowrap;}
.ad-btn-red{background:var(--yellow);color:#111;}
.ad-btn-green{background:var(--green);color:#052e16;}
.ad-btn-yellow{background:var(--gold);color:#111;}
.ad-btn-ghost{background:var(--input);color:#fff;border:1px solid var(--line);}
.ad-btn-ghost:hover{border-color:var(--yellow);color:#fff;}
.ad-table-wrap{width:100%;overflow-x:auto!important;}
.ad-table{width:100%!important;min-width:0!important;border-collapse:collapse;table-layout:fixed;font-size:13px;}
.ad-table th{background:var(--panel);color:var(--muted2);font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.05em;padding:14px 8px;text-align:left;}
.ad-table td{padding:15px 8px;border-top:1px solid var(--line);color:#c9ced3;vertical-align:middle;font-weight:700;word-break:break-word;white-space:normal;}
.ad-click-row{cursor:pointer;}
.ad-click-row:hover td{background:#1E2329;}
.ad-table th:nth-child(1),.ad-table td:nth-child(1){width:10%;}
.ad-table th:nth-child(2),.ad-table td:nth-child(2){width:13%;}
.ad-table th:nth-child(3),.ad-table td:nth-child(3){width:13%;}
.ad-table th:nth-child(4),.ad-table td:nth-child(4){width:9%;}
.ad-table th:nth-child(5),.ad-table td:nth-child(5){width:10%;}
.ad-table th:nth-child(6),.ad-table td:nth-child(6){width:11%;}
.ad-table th:nth-child(7),.ad-table td:nth-child(7){width:7%;}
.ad-table th:nth-child(8),.ad-table td:nth-child(8){width:8%;}
.ad-table th:nth-child(9),.ad-table td:nth-child(9){width:8%;}
.ad-table th:nth-child(10),.ad-table td:nth-child(10){width:11%;}
.ad-name{font-weight:900;color:#fff;}
.ad-small{font-size:11px;color:var(--muted);margin-top:3px;}
.ad-ref{color:var(--yellow);font-family:monospace;font-weight:900;font-size:12px;}
.ad-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:900;white-space:nowrap;text-transform:uppercase;}
.ad-badge-green{background:#0d2b1e;color:var(--green);}
.ad-badge-red{background:rgba(239,68,68,.13);color:#ff9b9b;}
.ad-badge-yellow{background:#3b2a09;color:var(--gold);}
.ad-badge-gray{background:var(--input);color:#d5dade;border:1px solid var(--line);}
.ad-actions{display:flex;gap:6px;flex-wrap:wrap;align-items:center;}
.ad-actions form{margin:0;}
.ad-icon-btn{width:36px;height:36px;border-radius:4px;border:1px solid var(--line);background:var(--input);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;text-decoration:none;font-size:17px;}
.ad-icon-btn:hover{border-color:var(--yellow);transform:translateY(-1px);}
.ad-icon-view{color:var(--gold);}
.ad-icon-approve{color:var(--green);}
.ad-icon-reject{color:#ff9b9b;}
.ad-slip{width:44px;height:44px;object-fit:cover;border-radius:5px;border:1px solid var(--line);cursor:pointer;background:var(--input);}
.ad-pagination{padding:16px 22px;border-top:1px solid var(--line);background:var(--panel);display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;}
.ad-pagination-info{color:var(--muted);font-size:12px;font-weight:800;}

.ad-pagination nav{width:auto;max-width:100%;}
.ad-pagination nav > div{display:flex!important;align-items:center!important;gap:8px!important;flex-wrap:wrap!important;}
.ad-pagination nav p{margin:0!important;color:var(--muted)!important;font-size:12px!important;font-weight:800!important;}
.ad-pagination nav a,
.ad-pagination nav span{display:inline-flex!important;align-items:center!important;justify-content:center!important;min-width:34px!important;height:34px!important;padding:0 10px!important;border-radius:5px!important;border:1px solid var(--line)!important;background:var(--input)!important;color:#fff!important;text-decoration:none!important;font-size:12px!important;font-weight:900!important;line-height:1!important;box-shadow:none!important;}
.ad-pagination nav span[aria-current="page"] span,
.ad-pagination nav span[aria-current="page"]{background:var(--yellow)!important;color:#111!important;border-color:var(--yellow)!important;}
.ad-pagination nav a:hover{border-color:var(--yellow)!important;color:var(--yellow)!important;}
.ad-pagination nav svg{width:16px!important;height:16px!important;max-width:16px!important;max-height:16px!important;display:block!important;stroke-width:3!important;}
.ad-pagination nav .hidden{display:none!important;}
.ad-pagination nav div:first-child{display:none!important;}
.ad-pagination nav div:last-child{display:flex!important;}

.ad-empty{text-align:center;padding:45px;color:var(--muted);font-weight:800;}
.ad-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.78);z-index:9999;align-items:center;justify-content:center;padding:18px;}
.ad-modal-overlay.open{display:flex;}
.ad-modal{background:var(--box);border:1.5px solid var(--yellow);border-radius:7px;width:870px;max-width:100%;max-height:92vh;overflow-y:auto;box-shadow:0 18px 50px rgba(0,0,0,.5);}
.ad-modal-hdr{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:17px 22px;border-bottom:1px solid var(--line);position:sticky;top:0;background:var(--panel);z-index:5;}
.ad-modal-title{margin:0;font-size:15px;font-weight:800;color:#fff;display:flex;align-items:center;gap:8px;}
.ad-modal-title i{color:var(--yellow);}
.ad-modal-close{width:34px;height:34px;border:1px solid var(--line);background:var(--input);color:#fff;border-radius:4px;cursor:pointer;}
.ad-modal-close:hover{background:var(--yellow);border-color:var(--yellow);}
.ad-modal-body{padding:22px;}
.ad-detail-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.ad-detail-item{border:1px solid var(--line);background:var(--panel);border-radius:5px;padding:12px;}
.ad-detail-label{font-size:11px;color:var(--muted);font-weight:900;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;}
.ad-detail-value{font-weight:800;color:#fff;font-size:13px;word-break:break-word;}
.ad-slip-preview{margin-top:18px;border:1px solid var(--line);background:var(--panel);border-radius:6px;padding:14px;}
.ad-slip-preview img{max-width:280px;width:100%;border-radius:6px;border:1px solid var(--line);cursor:pointer;}
.ad-modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px;border-top:1px solid var(--line);background:var(--panel);flex-wrap:wrap;}
.ad-lightbox{display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:10001;align-items:center;justify-content:center;flex-direction:column;}
.ad-lightbox.open{display:flex;}
.ad-lightbox img{max-width:90vw;max-height:80vh;border-radius:8px;border:1px solid var(--line);}
.ad-lightbox-close{position:absolute;top:18px;right:24px;background:var(--yellow);border:0;color:#fff;font-size:22px;width:38px;height:38px;border-radius:4px;cursor:pointer;}
@media(max-width:1300px){.ad-wrap{margin:-82px 24px 0;}.ad-table th{font-size:9px;padding:12px 6px;}.ad-table td{font-size:12px;padding:13px 6px;}.ad-btn{padding:8px 9px;font-size:11px;}}
@media(max-width:1100px){.ad-kpis{grid-template-columns:repeat(2,1fr);}.ad-filters{grid-template-columns:1fr 1fr;}.ad-table,.ad-table thead,.ad-table tbody,.ad-table th,.ad-table td,.ad-table tr{display:block;width:100%!important;}.ad-table thead{display:none;}.ad-table tr{background:var(--panel);border:1px solid var(--line);border-radius:7px;margin:12px;padding:14px;}.ad-table td{border-top:0;padding:9px 0;}.ad-table td::before{content:attr(data-label);display:block;color:var(--muted2);font-size:10px;font-weight:900;text-transform:uppercase;margin-bottom:5px;}.ad-table td:first-child::before{display:none;}}
@media(max-width:768px){.ad-page{margin:-16px;}.ad-hero{padding:45px 24px 110px;}.ad-title-main{font-size:34px;}.ad-wrap{margin:-76px 18px 0;}.ad-kpis{grid-template-columns:1fr;}.ad-filters{grid-template-columns:1fr;}.ad-detail-grid{grid-template-columns:1fr;}.ad-pagination{flex-direction:column;align-items:flex-start;}}
</style>
@endpush

@section('content')
<div class="ad-page">
    <section class="ad-hero">
       
    </section>

    <main class="ad-wrap">
        <section class="ad-kpis">
            <div class="ad-kpi"><div class="ad-kpi-icon"><i class="ti ti-clock"></i></div><div class="ad-kpi-label">Pending</div><div class="ad-kpi-value">{{ $stats['pending'] ?? 0 }}</div></div>
            <div class="ad-kpi"><div class="ad-kpi-icon"><i class="ti ti-circle-check"></i></div><div class="ad-kpi-label">Approved</div><div class="ad-kpi-value">{{ $stats['approved'] ?? 0 }}</div></div>
            <div class="ad-kpi"><div class="ad-kpi-icon"><i class="ti ti-circle-x"></i></div><div class="ad-kpi-label">Rejected</div><div class="ad-kpi-value">{{ $stats['rejected'] ?? 0 }}</div></div>
            <div class="ad-kpi"><div class="ad-kpi-icon"><i class="ti ti-cash"></i></div><div class="ad-kpi-label">Approved INR Volume</div><div class="ad-kpi-value">{{ number_format((float)($stats['total_volume'] ?? 0), 2) }}</div></div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h2 class="ad-title"><i class="ti ti-list-details"></i> P2P Requests</h2>
            </div>

            <form id="adFilterForm" method="GET" action="{{ route('admin.wallet-requests.index') }}" class="ad-filters">
                <div class="ad-search-wrap">
                    <i class="ti ti-search"></i>
                    <input id="adSearch" type="text" name="search" value="{{ request('search') }}" class="ad-input" placeholder="Search client, merchant, note...">
                </div>

                <select name="status" class="ad-select ad-auto-filter">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <select name="type" class="ad-select ad-auto-filter">
                    <option value="">All Type</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Buy USD</option>
                    <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Sell USD</option>
                </select>

                <select name="per_page" class="ad-select ad-auto-filter">
                    <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 / page</option>
                    <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100 / page</option>
                </select>

                <a href="{{ route('admin.wallet-requests.index') }}" class="ad-btn ad-btn-ghost"><i class="ti ti-refresh"></i> Reset</a>
            </form>

            <div class="ad-table-wrap">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Transaction No</th><th>Client</th><th>Merchant</th><th>Type</th><th>USD</th><th>INR Total</th><th>Status</th><th>Date</th><th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($requests as $request)
                        <tr class="ad-click-row" onclick="window.location='{{ route('admin.wallet-requests.chat', ['walletRequest' => $request->id]) }}'">
                            <td data-label="Transaction No"><span class="ad-ref">{{ $request->transaction_no ?? ('TNS' . str_pad($request->id, 9, '0', STR_PAD_LEFT)) }}</span></td>

                            <td data-label="Client">
                                <div class="ad-name">{{ $request->user->name ?? 'Deleted User' }}</div>
                                <div class="ad-small">{{ $request->user->email ?? '-' }}</div>
                                <div class="ad-small">{{ ucfirst($request->user->role ?? '-') }}</div>
                            </td>

                            <td data-label="Merchant">
                                @if($request->merchant)
                                    <div class="ad-name">{{ $request->merchant->name }}</div>
                                    <div class="ad-small">{{ $request->merchant->email }}</div>
                                    <div class="ad-small">{{ $request->merchant->phone ?? '-' }}</div>
                                @else
                                    <span class="ad-small">—</span>
                                @endif
                            </td>

                            <td data-label="Type">
                                @if($request->type === 'deposit')
                                    <span class="ad-badge ad-badge-green"><i class="ti ti-plus"></i> Buy USD</span>
                                @elseif($request->type === 'withdrawal')
                                    <span class="ad-badge ad-badge-red"><i class="ti ti-minus"></i> Sell USD</span>
                                @else
                                    <span class="ad-badge ad-badge-gray">{{ ucwords(str_replace('_', ' ', $request->type)) }}</span>
                                @endif
                            </td>

                            <td data-label="USD">
                                <strong style="color:#fff;">$ {{ number_format((float)$request->amount, 2) }}</strong>
                                <div class="ad-small">USD: {{ number_format((float)($request->usd_rate ?? 0), 2) }}</div>
                                <div class="ad-small">INR: {{ number_format((float)($request->inr_rate ?? 0), 2) }}</div>
                            </td>

                            <td data-label="INR Total">
                                <strong style="color:var(--gold);">INR {{ number_format((float)($request->total_amount ?? 0), 2) }}</strong>
                                <div class="ad-small">Fee: INR {{ number_format((float)($request->fee ?? 0), 2) }}</div>
                            </td>

                          

                            <td data-label="Status">
                                @if($request->status === 'pending')
    <span class="ad-badge ad-badge-yellow">Pending</span>
@elseif($request->status === 'approved')
    <span class="ad-badge ad-badge-green">Approved</span>
@elseif($request->status === 'closed')
    <span class="ad-badge ad-badge-gray">Closed</span>
@else
    <span class="ad-badge ad-badge-red">Rejected</span>
@endif
                            </td>

                            <td data-label="Date">
                                <div class="ad-small">{{ $request->created_at?->format('Y-m-d') }}</div>
                                <div class="ad-small">{{ $request->created_at?->format('h:i A') }}</div>
                            </td>

                            <td data-label="Actions" onclick="event.stopPropagation();">
                                <div class="ad-actions">
                                    <button type="button" class="ad-icon-btn ad-icon-view" title="View" onclick="adOpenModal('adView{{ $request->id }}')">
                                        <i class="ti ti-eye"></i>
                                    </button>

                                    @if($request->status === 'pending')
                                        <form method="POST" action="{{ route('admin.wallet-requests.approve', $request) }}">
                                            @csrf
                                            <button type="submit" class="ad-icon-btn ad-icon-approve" title="Approve" onclick="return confirm('Approve this request?')">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.wallet-requests.reject', $request) }}">
                                            @csrf
                                            <button type="submit" class="ad-icon-btn ad-icon-reject" title="Reject" onclick="return confirm('Reject this request?')">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.wallet-requests.close', $request) }}">
    @csrf
    <button type="submit" class="ad-icon-btn ad-icon-view" title="Close" onclick="return confirm('Close this pending request?')">
        <i class="ti ti-lock"></i>
    </button>
</form>
                                    @elseif($request->status === 'approved')
                                        <form method="POST" action="{{ route('admin.wallet-requests.close', $request) }}">
                                            @csrf
                                            <button type="submit" class="ad-icon-btn ad-icon-view" title="Close" onclick="return confirm('Close this approved request?')">
                                                <i class="ti ti-lock"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="ad-small">Processed</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><div class="ad-empty">No P2P requests found.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ad-pagination">
                <div class="ad-pagination-info">Showing {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }}</div>
                {{ $requests->appends(request()->query())->links() }}
            </div>
        </section>

        @foreach($requests as $request)
            <div class="ad-modal-overlay" id="adView{{ $request->id }}">
                <div class="ad-modal">
                    <div class="ad-modal-hdr">
                        <h2 class="ad-modal-title"><i class="ti ti-receipt"></i> P2P Request Details</h2>
                        <button class="ad-modal-close" onclick="adCloseModal('adView{{ $request->id }}')"><i class="ti ti-x"></i></button>
                    </div>

                    <div class="ad-modal-body">
                        <div class="ad-detail-grid">
                            <div class="ad-detail-item"><div class="ad-detail-label">Transaction No</div><div class="ad-detail-value">{{ $request->transaction_no ?? ('TNS' . str_pad($request->id, 9, '0', STR_PAD_LEFT)) }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">Client</div><div class="ad-detail-value">{{ $request->user->name ?? 'Deleted User' }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">Merchant</div><div class="ad-detail-value">{{ $request->merchant->name ?? '-' }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">Type</div><div class="ad-detail-value">{{ $request->type === 'deposit' ? 'Buy USD' : ($request->type === 'withdrawal' ? 'Sell USD' : ucwords(str_replace('_', ' ', $request->type))) }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">USD Amount</div><div class="ad-detail-value">$ {{ number_format((float)$request->amount, 2) }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">Total INR</div><div class="ad-detail-value" style="color:var(--gold);">INR {{ number_format((float)($request->total_amount ?? 0), 2) }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">Fee</div><div class="ad-detail-value">INR {{ number_format((float)($request->fee ?? 0), 2) }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">Status</div><div class="ad-detail-value">{{ ucfirst($request->status) }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">Date</div><div class="ad-detail-value">{{ $request->created_at?->format('Y-m-d h:i A') }}</div></div>
                            <div class="ad-detail-item"><div class="ad-detail-label">Note</div><div class="ad-detail-value">{{ $request->note ?? '-' }}</div></div>
                        </div>

                        @if($request->payment_slip)
                            <div class="ad-slip-preview">
                                <div class="ad-detail-label">Payment Slip</div>
                                <img src="{{ url('/api/storage/' . $request->payment_slip) }}" onclick="adOpenLightbox('{{ url('/api/storage/' . $request->payment_slip) }}')" alt="Payment slip">
                            </div>
                        @endif
                    </div>

                    <div class="ad-modal-footer">
                        <button class="ad-btn ad-btn-ghost" onclick="adCloseModal('adView{{ $request->id }}')">Close</button>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="ad-lightbox" id="adLightbox" onclick="adCloseLightbox()">
            <button class="ad-lightbox-close" onclick="adCloseLightbox()"><i class="ti ti-x"></i></button>
            <img id="adLightboxImg" src="" alt="">
        </div>
    </main>
</div>

<script>
function adOpenModal(id){var modal=document.getElementById(id);if(modal){modal.classList.add('open');document.body.style.overflow='hidden';}}
function adCloseModal(id){var modal=document.getElementById(id);if(modal){modal.classList.remove('open');document.body.style.overflow='';}}
document.querySelectorAll('.ad-modal-overlay').forEach(function(modal){modal.addEventListener('click',function(e){if(e.target===modal){modal.classList.remove('open');document.body.style.overflow='';}});});
function adOpenLightbox(src){var lightbox=document.getElementById('adLightbox');var img=document.getElementById('adLightboxImg');if(lightbox&&img){img.src=src;lightbox.classList.add('open');document.body.style.overflow='hidden';}}
function adCloseLightbox(){var lightbox=document.getElementById('adLightbox');if(lightbox){lightbox.classList.remove('open');document.body.style.overflow='';}}
document.querySelectorAll('.ad-auto-filter').forEach(function(el){el.addEventListener('change',function(){document.getElementById('adFilterForm').submit();});});
var adSearch=document.getElementById('adSearch');var adSearchTimer=null;
if(adSearch){adSearch.addEventListener('keyup',function(){clearTimeout(adSearchTimer);adSearchTimer=setTimeout(function(){document.getElementById('adFilterForm').submit();},480);});}
document.addEventListener('keydown',function(e){if(e.key==='Escape'){document.querySelectorAll('.ad-modal-overlay.open').forEach(function(modal){modal.classList.remove('open');});adCloseLightbox();document.body.style.overflow='';}});
</script>
@endsection
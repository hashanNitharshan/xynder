
@extends('layouts.admin', ['title' => 'Wallet Requests'])

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

.wr-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.wr-hero{
    background:var(--hero);
    padding:65px 85px 120px;
    position:relative;
    overflow:hidden;
}

.wr-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.wr-hero-inner{
    position:relative;
    z-index:2;
    max-width:680px;
}

.wr-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.wr-title-main{
    font-size:46px;
    line-height:1.12;
    font-weight:900;
    margin:0 0 18px;
}

.wr-title-main span{
    color:var(--red);
    display:block;
}

.wr-subtitle{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    max-width:580px;
}

.wr-wrap{
    position:relative;
    z-index:5;
    margin:-82px 85px 0;
}

.wr-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:26px;
}

.wr-stat{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:20px;
    display:flex;
    align-items:center;
    gap:14px;
    min-height:105px;
}

.wr-stat-icon{
    width:46px;
    height:46px;
    border-radius:50%;
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    flex-shrink:0;
}

.wr-stat-num{
    font-size:25px;
    font-weight:900;
    color:#fff;
    line-height:1;
}

.wr-stat-label{
    color:var(--muted);
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-top:5px;
}

.wr-card{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.wr-card-head{
    background:var(--panel);
    border-bottom:1px solid var(--line);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    padding:18px 22px;
}

.wr-title{
    font-size:18px;
    font-weight:900;
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
}

.wr-title i{color:var(--red)}

.wr-filters{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    padding:16px 22px;
    border-bottom:1px solid var(--line);
    background:var(--box);
}

.wr-input,
.wr-select{
    height:42px;
    border:1px solid var(--line);
    border-radius:4px;
    padding:0 13px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:0;
    font-weight:700;
}

.wr-input:focus,
.wr-select:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.wr-input::placeholder{color:var(--muted)}

.wr-input{width:310px}
.wr-select{width:150px}

.wr-search-wrap{
    position:relative;
}

.wr-search-wrap i{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:var(--muted);
    font-size:16px;
}

.wr-search-wrap .wr-input{
    padding-left:36px;
}

.wr-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    padding:10px 14px;
    border-radius:4px;
    font-size:13px;
    font-weight:900;
    border:0;
    cursor:pointer;
    text-decoration:none;
    white-space:nowrap;
}

.wr-btn-red{background:var(--red);color:#fff}
.wr-btn-red:hover{background:var(--red2);color:#fff}
.wr-btn-green{background:var(--green);color:#052e16}
.wr-btn-yellow{background:var(--gold);color:#111}
.wr-btn-ghost{background:var(--input);color:#fff;border:1px solid var(--line)}
.wr-btn-ghost:hover{border-color:var(--red);color:#fff}

.wr-table-wrap{overflow-x:auto}

.wr-table{
    width:100%;
    min-width:1180px;
    border-collapse:collapse;
    font-size:13px;
}

.wr-table th{
    background:var(--panel);
    color:var(--muted2);
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    padding:14px 16px;
    white-space:nowrap;
    text-align:left;
}

.wr-table td{
    padding:15px 16px;
    border-top:1px solid var(--line);
    color:#c9ced3;
    vertical-align:middle;
    font-weight:700;
}

.wr-table tr:hover td{
    background:#30363a;
}

.wr-name{
    font-weight:900;
    color:#fff;
}

.wr-small{
    font-size:11px;
    color:var(--muted);
    margin-top:3px;
}

.wr-ref{
    color:var(--red);
    font-family:monospace;
    font-weight:900;
}

.wr-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
    white-space:nowrap;
    text-transform:uppercase;
}

.wr-badge-green{background:#0d2b1e;color:var(--green)}
.wr-badge-red{background:#3a1018;color:#ff6b7b}
.wr-badge-yellow{background:#3b2a09;color:var(--gold)}
.wr-badge-gray{background:var(--input);color:#d5dade;border:1px solid var(--line)}

.wr-actions{
    display:flex;
    gap:7px;
    flex-wrap:wrap;
    align-items:center;
}

.wr-actions form{margin:0}

.wr-slip{
    width:44px;
    height:44px;
    object-fit:cover;
    border-radius:5px;
    border:1px solid var(--line);
    cursor:pointer;
    background:var(--input);
}

.wr-pagination{
    padding:16px 22px;
    border-top:1px solid var(--line);
    background:var(--panel);
}

.wr-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:800;
}

.wr-modal-overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.78);
    z-index:9999;
    align-items:center;
    justify-content:center;
    padding:18px;
}

.wr-modal-overlay.open{display:flex}

.wr-modal{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    width:870px;
    max-width:100%;
    max-height:92vh;
    overflow-y:auto;
    box-shadow:0 18px 50px rgba(0,0,0,.5);
}

.wr-modal-hdr{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding:17px 22px;
    border-bottom:1px solid var(--line);
    position:sticky;
    top:0;
    background:var(--panel);
    z-index:5;
}

.wr-modal-title{
    margin:0;
    font-size:18px;
    font-weight:900;
    color:#fff;
    display:flex;
    align-items:center;
    gap:8px;
}

.wr-modal-title i{color:var(--red)}

.wr-modal-close{
    width:34px;
    height:34px;
    border:1px solid var(--line);
    background:var(--input);
    color:#fff;
    border-radius:4px;
    cursor:pointer;
}

.wr-modal-close:hover{
    background:var(--red);
    border-color:var(--red);
}

.wr-modal-body{
    padding:22px;
}

.wr-detail-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
}

.wr-detail-item{
    border:1px solid var(--line);
    background:var(--panel);
    border-radius:5px;
    padding:12px;
}

.wr-detail-label{
    font-size:11px;
    color:var(--muted);
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:6px;
}

.wr-detail-value{
    font-weight:800;
    color:#fff;
    font-size:13px;
    word-break:break-word;
}

.wr-slip-preview{
    margin-top:18px;
    border:1px solid var(--line);
    background:var(--panel);
    border-radius:6px;
    padding:14px;
}

.wr-slip-preview img{
    max-width:280px;
    width:100%;
    border-radius:6px;
    border:1px solid var(--line);
    cursor:pointer;
}

.wr-modal-footer{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    padding:16px 22px;
    border-top:1px solid var(--line);
    background:var(--panel);
    flex-wrap:wrap;
}

.wr-lightbox{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.9);
    z-index:10001;
    align-items:center;
    justify-content:center;
    flex-direction:column;
}

.wr-lightbox.open{display:flex}

.wr-lightbox img{
    max-width:90vw;
    max-height:80vh;
    border-radius:8px;
    border:1px solid var(--line);
}

.wr-lightbox-close{
    position:absolute;
    top:18px;
    right:24px;
    background:var(--red);
    border:0;
    color:#fff;
    font-size:22px;
    width:38px;
    height:38px;
    border-radius:4px;
    cursor:pointer;
}

@media(max-width:1100px){
    .wr-stats{grid-template-columns:repeat(2,1fr)}
    .wr-wrap{margin:-82px 24px 0}
}

@media(max-width:768px){
    .wr-page{margin:-16px}
    .wr-hero{padding:45px 24px 110px}
    .wr-title-main{font-size:34px}
    .wr-wrap{margin:-76px 18px 0}
    .wr-stats{grid-template-columns:1fr}
    .wr-detail-grid{grid-template-columns:1fr}
    .wr-input,.wr-select{width:100%}
}
</style>
@endpush

@section('content')

<div class="wr-page">

    <section class="wr-hero">
        <div class="wr-hero-inner">
            <div class="wr-eyebrow">Xynder Wallet Admin</div>

            <h1 class="wr-title-main">
                Wallet
                <span>Requests</span>
            </h1>

            <div class="wr-subtitle">
                Review buy and sell USD requests, check payment slips,
                approve or reject pending requests, and open transaction chat.
            </div>
        </div>
    </section>

    <main class="wr-wrap">

        <section class="wr-stats">
            <div class="wr-stat">
                <div class="wr-stat-icon"><i class="ti ti-clock"></i></div>
                <div>
                    <div class="wr-stat-num">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="wr-stat-label">Pending</div>
                </div>
            </div>

            <div class="wr-stat">
                <div class="wr-stat-icon"><i class="ti ti-circle-check"></i></div>
                <div>
                    <div class="wr-stat-num">{{ $stats['approved'] ?? 0 }}</div>
                    <div class="wr-stat-label">Approved</div>
                </div>
            </div>

            <div class="wr-stat">
                <div class="wr-stat-icon"><i class="ti ti-circle-x"></i></div>
                <div>
                    <div class="wr-stat-num">{{ $stats['rejected'] ?? 0 }}</div>
                    <div class="wr-stat-label">Rejected</div>
                </div>
            </div>

            <div class="wr-stat">
                <div class="wr-stat-icon"><i class="ti ti-cash"></i></div>
                <div>
                    <div class="wr-stat-num">{{ number_format((float)($stats['total_volume'] ?? 0), 2) }}</div>
                    <div class="wr-stat-label">Approved INR Volume</div>
                </div>
            </div>
        </section>

        <section class="wr-card">
            <div class="wr-card-head">
                <h2 class="wr-title">
                    <i class="ti ti-list-details"></i>
                    Wallet Requests
                </h2>
            </div>

            <form id="wrFilterForm" method="GET" action="{{ route('admin.wallet-requests.index') }}" class="wr-filters">
                <div class="wr-search-wrap">
                    <i class="ti ti-search"></i>
                    <input id="wrSearch"
                           type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="wr-input"
                           placeholder="Search client, merchant, note...">
                </div>

                <select name="status" class="wr-select wr-auto-filter">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="type" class="wr-select wr-auto-filter">
                    <option value="">All Type</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Buy USD</option>
                    <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Sell USD</option>
                </select>

                <a href="{{ route('admin.wallet-requests.index') }}" class="wr-btn wr-btn-ghost">
                    <i class="ti ti-refresh"></i>
                    Reset
                </a>
            </form>

            <div class="wr-table-wrap">
                <table class="wr-table">
                    <thead>
                        <tr>
                            <th>Transaction No</th>
                            <th>Client</th>
                            <th>Merchant</th>
                            <th>Type</th>
                            <th>USD</th>
                            <th>INR Total</th>
                            <th>Slip</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td>
                                <span class="wr-ref">
                                    {{ $request->transaction_no ?? ('TNS' . str_pad($request->id, 9, '0', STR_PAD_LEFT)) }}
                                </span>
                            </td>

                            <td>
                                <div class="wr-name">{{ $request->user->name ?? 'Deleted User' }}</div>
                                <div class="wr-small">{{ $request->user->email ?? '-' }}</div>
                                <div class="wr-small">{{ ucfirst($request->user->role ?? '-') }}</div>
                            </td>

                            <td>
                                @if($request->merchant)
                                    <div class="wr-name">{{ $request->merchant->name }}</div>
                                    <div class="wr-small">{{ $request->merchant->email }}</div>
                                    <div class="wr-small">{{ $request->merchant->phone ?? '-' }}</div>
                                @else
                                    <span class="wr-small">—</span>
                                @endif
                            </td>

                            <td>
                                @if($request->type === 'deposit')
                                    <span class="wr-badge wr-badge-green">
                                        <i class="ti ti-trending-up"></i>
                                        Buy USD
                                    </span>
                                @elseif($request->type === 'withdrawal')
                                    <span class="wr-badge wr-badge-red">
                                        <i class="ti ti-trending-down"></i>
                                        Sell USD
                                    </span>
                                @else
                                    <span class="wr-badge wr-badge-gray">
                                        {{ ucwords(str_replace('_', ' ', $request->type)) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <strong style="color:#fff;">$ {{ number_format((float)$request->amount, 2) }}</strong>
                                <div class="wr-small">USD: {{ number_format((float)($request->usd_rate ?? 0), 2) }}</div>
                                <div class="wr-small">INR: {{ number_format((float)($request->inr_rate ?? 0), 2) }}</div>
                            </td>

                            <td>
                                <strong style="color:var(--gold);">INR {{ number_format((float)($request->total_amount ?? 0), 2) }}</strong>
                                <div class="wr-small">Fee: INR {{ number_format((float)($request->fee ?? 0), 2) }}</div>
                            </td>

                            <td>
                                @if($request->payment_slip)
                                    <img src="{{ url('/api/storage/' . $request->payment_slip) }}"
                                         class="wr-slip"
                                         onclick="wrOpenLightbox('{{ url('/api/storage/' . $request->payment_slip) }}')"
                                         alt="Payment slip">
                                @else
                                    <span class="wr-small">No slip</span>
                                @endif
                            </td>

                            <td>
                                @if($request->status === 'pending')
                                    <span class="wr-badge wr-badge-yellow">Pending</span>
                                @elseif($request->status === 'approved')
                                    <span class="wr-badge wr-badge-green">Approved</span>
                                @else
                                    <span class="wr-badge wr-badge-red">Rejected</span>
                                @endif
                            </td>

                            <td>
                                <div class="wr-small">{{ $request->created_at?->format('Y-m-d') }}</div>
                                <div class="wr-small">{{ $request->created_at?->format('h:i A') }}</div>
                            </td>

                            <td>
                                <div class="wr-actions">
                                    <button type="button"
                                            class="wr-btn wr-btn-yellow"
                                            onclick="wrOpenModal('wrView{{ $request->id }}')">
                                        <i class="ti ti-eye"></i>
                                        View
                                    </button>

                                    <a class="wr-btn wr-btn-ghost"
                                       href="{{ route('admin.wallet-requests.chat', $request) }}">
                                        <i class="ti ti-message-circle"></i>
                                        Chat
                                    </a>

                                    @if($request->status === 'pending')
                                        <form method="POST" action="{{ route('admin.wallet-requests.approve', $request) }}">
                                            @csrf
                                            <button class="wr-btn wr-btn-green" onclick="return confirm('Approve this request?')">
                                                <i class="ti ti-check"></i>
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.wallet-requests.reject', $request) }}">
                                            @csrf
                                            <button class="wr-btn wr-btn-red" onclick="return confirm('Reject this request?')">
                                                <i class="ti ti-x"></i>
                                                Reject
                                            </button>
                                        </form>
                                    @else
                                        <span class="wr-small">Processed</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="wr-empty">No wallet requests found.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="wr-pagination">
                {{ $requests->appends(request()->query())->links() }}
            </div>
        </section>

        @foreach($requests as $request)
            <div class="wr-modal-overlay" id="wrView{{ $request->id }}">
                <div class="wr-modal">
                    <div class="wr-modal-hdr">
                        <h2 class="wr-modal-title">
                            <i class="ti ti-receipt"></i>
                            Wallet Request Details
                        </h2>

                        <button class="wr-modal-close" onclick="wrCloseModal('wrView{{ $request->id }}')">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <div class="wr-modal-body">
                        <div class="wr-detail-grid">
                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Transaction No</div>
                                <div class="wr-detail-value">{{ $request->transaction_no ?? ('TNS' . str_pad($request->id, 9, '0', STR_PAD_LEFT)) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Client</div>
                                <div class="wr-detail-value">{{ $request->user->name ?? 'Deleted User' }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Client Email</div>
                                <div class="wr-detail-value">{{ $request->user->email ?? '-' }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Merchant</div>
                                <div class="wr-detail-value">{{ $request->merchant->name ?? '-' }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Merchant Email</div>
                                <div class="wr-detail-value">{{ $request->merchant->email ?? '-' }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Type</div>
                                <div class="wr-detail-value">{{ $request->type === 'deposit' ? 'Buy USD' : ($request->type === 'withdrawal' ? 'Sell USD' : ucwords(str_replace('_', ' ', $request->type))) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">USD Amount</div>
                                <div class="wr-detail-value">$ {{ number_format((float)$request->amount, 2) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">USD Rate</div>
                                <div class="wr-detail-value">{{ number_format((float)($request->usd_rate ?? 0), 2) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">INR Rate</div>
                                <div class="wr-detail-value">{{ number_format((float)($request->inr_rate ?? 0), 2) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Converted INR</div>
                                <div class="wr-detail-value">INR {{ number_format((float)($request->converted_amount ?? 0), 2) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Xynder Fee</div>
                                <div class="wr-detail-value">INR {{ number_format((float)($request->xynder_fee ?? 0), 2) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Network Fee</div>
                                <div class="wr-detail-value">INR {{ number_format((float)($request->network_fee ?? 0), 2) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Total Fee</div>
                                <div class="wr-detail-value">INR {{ number_format((float)($request->fee ?? 0), 2) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Total INR</div>
                                <div class="wr-detail-value" style="color:var(--gold);">
                                    INR {{ number_format((float)($request->total_amount ?? 0), 2) }}
                                </div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Status</div>
                                <div class="wr-detail-value">{{ ucfirst($request->status) }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Date</div>
                                <div class="wr-detail-value">{{ $request->created_at?->format('Y-m-d h:i A') }}</div>
                            </div>

                            <div class="wr-detail-item">
                                <div class="wr-detail-label">Note</div>
                                <div class="wr-detail-value">{{ $request->note ?? '-' }}</div>
                            </div>
                        </div>

                        @if($request->payment_slip)
                            <div class="wr-slip-preview">
                                <div class="wr-detail-label">Payment Slip</div>
                                <img src="{{ url('/api/storage/' . $request->payment_slip) }}"
                                     onclick="wrOpenLightbox('{{ url('/api/storage/' . $request->payment_slip) }}')"
                                     alt="Payment slip">
                            </div>
                        @endif
                    </div>

                    <div class="wr-modal-footer">
                        <button class="wr-btn wr-btn-ghost" onclick="wrCloseModal('wrView{{ $request->id }}')">
                            Close
                        </button>

                        <a class="wr-btn wr-btn-ghost"
                           href="{{ route('admin.wallet-requests.chat', $request) }}">
                            <i class="ti ti-message-circle"></i>
                            Chat
                        </a>

                        @if($request->status === 'pending')
                            <form method="POST" action="{{ route('admin.wallet-requests.approve', $request) }}">
                                @csrf
                                <button class="wr-btn wr-btn-green" onclick="return confirm('Approve this request?')">
                                    <i class="ti ti-check"></i>
                                    Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.wallet-requests.reject', $request) }}">
                                @csrf
                                <button class="wr-btn wr-btn-red" onclick="return confirm('Reject this request?')">
                                    <i class="ti ti-x"></i>
                                    Reject
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <div class="wr-lightbox" id="wrLightbox" onclick="wrCloseLightbox()">
            <button class="wr-lightbox-close" onclick="wrCloseLightbox()">
                <i class="ti ti-x"></i>
            </button>
            <img id="wrLightboxImg" src="" alt="">
        </div>

    </main>
</div>

<script>
function wrOpenModal(id) {
    var modal = document.getElementById(id);

    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function wrCloseModal(id) {
    var modal = document.getElementById(id);

    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.querySelectorAll('.wr-modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
});

function wrOpenLightbox(src) {
    var lightbox = document.getElementById('wrLightbox');
    var img = document.getElementById('wrLightboxImg');

    if (lightbox && img) {
        img.src = src;
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function wrCloseLightbox() {
    var lightbox = document.getElementById('wrLightbox');

    if (lightbox) {
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.querySelectorAll('.wr-auto-filter').forEach(function(el) {
    el.addEventListener('change', function() {
        document.getElementById('wrFilterForm').submit();
    });
});

var wrSearch = document.getElementById('wrSearch');
var wrSearchTimer = null;

if (wrSearch) {
    wrSearch.addEventListener('keyup', function() {
        clearTimeout(wrSearchTimer);

        wrSearchTimer = setTimeout(function() {
            document.getElementById('wrFilterForm').submit();
        }, 480);
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.wr-modal-overlay.open').forEach(function(modal) {
            modal.classList.remove('open');
        });

        wrCloseLightbox();
        document.body.style.overflow = '';
    }
});
</script>

@endsection


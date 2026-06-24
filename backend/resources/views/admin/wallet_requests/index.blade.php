@extends('layouts.admin', ['title' => 'Wallet Requests'])

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
    --yellow:#facc15;
    --blue:#3b82f6;
    --red:#ef4444;
    --green:#22c55e;
    --page:#020617;
    --card:#0f172a;
    --soft:#111827;
    --border:#1e293b;
    --input:#111827;
    --text:#f8fafc;
    --muted:#94a3b8;
    --muted2:#cbd5e1;
    --radius:10px;
    --shadow:0 1px 3px rgba(0,0,0,.45);
    --shadow-lg:0 20px 60px rgba(0,0,0,.75);
}

.wr * {
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

.wr-page {
    background:var(--page);
    padding:20px;
    min-height:100vh;
    color:var(--text);
}

.wr-stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:18px;
}

.wr-stat {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:14px 16px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:var(--shadow);
}

.wr-stat-icon {
    width:42px;
    height:42px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}

.wr-stat-icon.yellow { background:rgba(250,204,21,.16); }
.wr-stat-icon.green { background:rgba(34,197,94,.16); }
.wr-stat-icon.red { background:rgba(239,68,68,.16); }
.wr-stat-icon.blue { background:rgba(59,130,246,.18); }

.wr-stat-num {
    font-size:22px;
    font-weight:800;
    color:var(--text);
    line-height:1;
}

.wr-stat-label {
    font-size:12px;
    color:var(--muted);
    margin-top:3px;
}

.wr-card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    overflow:hidden;
}

.wr-card-head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    padding:14px 16px;
    border-bottom:1px solid var(--border);
}

.wr-title {
    font-size:16px;
    font-weight:800;
    margin:0;
}

.wr-filters {
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
    padding:10px 16px;
    border-bottom:1px solid var(--border);
    background:#020617;
}

.wr-input,
.wr-select {
    height:34px;
    border:1px solid #334155;
    border-radius:8px;
    padding:0 10px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:none;
}

.wr-input::placeholder {
    color:var(--muted);
}

.wr-input {
    width:260px;
}

.wr-select {
    width:135px;
}

.wr-search-wrap {
    position:relative;
}

.wr-search-wrap svg {
    position:absolute;
    left:9px;
    top:50%;
    transform:translateY(-50%);
    color:var(--muted);
}

.wr-search-wrap .wr-input {
    padding-left:30px;
}

.wr-btn {
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:7px 13px;
    border-radius:8px;
    font-size:12px;
    font-weight:700;
    border:none;
    cursor:pointer;
    text-decoration:none;
    white-space:nowrap;
}

.wr-btn:hover {
    opacity:.88;
}

.wr-btn-yellow { background:var(--yellow); color:#111827; }
.wr-btn-blue { background:var(--blue); color:#fff; }
.wr-btn-red { background:var(--red); color:#fff; }
.wr-btn-green { background:var(--green); color:#052e16; }
.wr-btn-ghost { background:#020617; color:var(--muted2); border:1px solid var(--border); }

.wr-table-wrap {
    overflow-x:auto;
}

.wr-table {
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

.wr-table th {
    background:#020617;
    color:var(--muted);
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    padding:10px 14px;
    border-bottom:1px solid var(--border);
    white-space:nowrap;
    text-align:left;
}

.wr-table td {
    padding:11px 14px;
    border-bottom:1px solid var(--border);
    color:var(--muted2);
    vertical-align:middle;
}

.wr-table tr:hover td {
    background:#111827;
}

.wr-name {
    font-weight:800;
    color:var(--text);
}

.wr-small {
    font-size:11px;
    color:var(--muted);
    margin-top:2px;
}

.wr-badge {
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:3px 9px;
    border-radius:20px;
    font-size:11px;
    font-weight:700;
    white-space:nowrap;
}

.wr-badge::before {
    content:'';
    width:6px;
    height:6px;
    border-radius:50%;
}

.wr-badge-green { background:rgba(34,197,94,.16); color:#86efac; }
.wr-badge-green::before { background:#22c55e; }

.wr-badge-red { background:rgba(239,68,68,.16); color:#fca5a5; }
.wr-badge-red::before { background:#ef4444; }

.wr-badge-yellow { background:rgba(250,204,21,.16); color:#fde68a; }
.wr-badge-yellow::before { background:#facc15; }

.wr-badge-blue { background:rgba(59,130,246,.18); color:#93c5fd; }
.wr-badge-blue::before { background:#3b82f6; }

.wr-actions {
    display:flex;
    gap:5px;
    flex-wrap:wrap;
    align-items:center;
}

.wr-slip {
    width:42px;
    height:42px;
    object-fit:cover;
    border-radius:8px;
    border:1px solid var(--border);
    cursor:pointer;
}

.wr-pagination {
    padding:12px 16px;
    border-top:1px solid var(--border);
}

.wr-modal-overlay {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.72);
    backdrop-filter:blur(4px);
    z-index:9999;
    align-items:center;
    justify-content:center;
    padding:16px;
}

.wr-modal-overlay.open {
    display:flex;
}

.wr-modal {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:14px;
    width:850px;
    max-width:100%;
    max-height:92vh;
    overflow-y:auto;
    box-shadow:var(--shadow-lg);
}

.wr-modal-hdr {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    position:sticky;
    top:0;
    background:var(--card);
    z-index:1;
}

.wr-modal-title {
    margin:0;
    font-size:16px;
    font-weight:800;
    color:var(--text);
}

.wr-modal-close {
    width:30px;
    height:30px;
    border:none;
    background:#1e293b;
    color:var(--muted);
    border-radius:8px;
    cursor:pointer;
}

.wr-modal-close:hover {
    background:var(--red);
    color:#fff;
}

.wr-modal-body {
    padding:20px;
}

.wr-detail-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
}

.wr-detail-item {
    border:1px solid var(--border);
    background:#020617;
    border-radius:8px;
    padding:10px 12px;
}

.wr-detail-label {
    font-size:11px;
    color:var(--muted);
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:4px;
}

.wr-detail-value {
    font-weight:700;
    color:var(--text);
    font-size:13px;
    word-break:break-word;
}

.wr-slip-preview {
    margin-top:16px;
    border:1px solid var(--border);
    background:#020617;
    border-radius:12px;
    padding:12px;
}

.wr-slip-preview img {
    max-width:260px;
    width:100%;
    border-radius:10px;
    border:1px solid var(--border);
    cursor:pointer;
}

.wr-modal-footer {
    display:flex;
    justify-content:flex-end;
    gap:8px;
    padding:14px 20px;
    border-top:1px solid var(--border);
    background:#020617;
}

.wr-lightbox {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.9);
    z-index:10001;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    gap:12px;
}

.wr-lightbox.open {
    display:flex;
}

.wr-lightbox img {
    max-width:90vw;
    max-height:80vh;
    border-radius:10px;
}

.wr-lightbox-close {
    position:absolute;
    top:16px;
    right:20px;
    background:none;
    border:none;
    color:#fff;
    font-size:28px;
    cursor:pointer;
}

@media(max-width:768px) {
    .wr-stats { grid-template-columns:repeat(2,1fr); }
    .wr-detail-grid { grid-template-columns:repeat(2,1fr); }
    .wr-input,
    .wr-select {
        width:100%;
    }
}

@media(max-width:480px) {
    .wr-stats { grid-template-columns:1fr 1fr; }
    .wr-detail-grid { grid-template-columns:1fr; }
}
</style>

<div class="wr">
<div class="wr-page">

    <div class="wr-stats">
        <div class="wr-stat">
            <div class="wr-stat-icon yellow">⏳</div>
            <div>
                <div class="wr-stat-num">{{ $stats['pending'] ?? 0 }}</div>
                <div class="wr-stat-label">Pending</div>
            </div>
        </div>

        <div class="wr-stat">
            <div class="wr-stat-icon green">✅</div>
            <div>
                <div class="wr-stat-num">{{ $stats['approved'] ?? 0 }}</div>
                <div class="wr-stat-label">Approved</div>
            </div>
        </div>

        <div class="wr-stat">
            <div class="wr-stat-icon red">❌</div>
            <div>
                <div class="wr-stat-num">{{ $stats['rejected'] ?? 0 }}</div>
                <div class="wr-stat-label">Rejected</div>
            </div>
        </div>

        <div class="wr-stat">
            <div class="wr-stat-icon blue">💰</div>
            <div>
                <div class="wr-stat-num">{{ number_format($stats['total_volume'] ?? 0, 2) }}</div>
                <div class="wr-stat-label">Approved INR Volume</div>
            </div>
        </div>
    </div>

    <div class="wr-card">
        <div class="wr-card-head">
            <h2 class="wr-title">Wallet Requests</h2>
        </div>

        <form id="wrFilterForm" method="GET" action="{{ route('admin.wallet-requests.index') }}" class="wr-filters">
            <div class="wr-search-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input id="wrSearch" type="text" name="search" value="{{ request('search') }}" class="wr-input" placeholder="Search client, merchant, note...">
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

            <a href="{{ route('admin.wallet-requests.index') }}" class="wr-btn wr-btn-ghost">↺ Reset</a>
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
    <strong style="color:var(--yellow);">
        {{ $request->transaction_no ?? ('TNS' . str_pad($request->id, 9, '0', STR_PAD_LEFT)) }}
    </strong>
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
                                <span class="wr-small">-</span>
                            @endif
                        </td>

                        <td>
                            @if($request->type === 'deposit')
                                <span class="wr-badge wr-badge-green">Buy USD</span>
                            @elseif($request->type === 'withdrawal')
                                <span class="wr-badge wr-badge-red">Sell USD</span>
                            @else
                                <span class="wr-badge wr-badge-blue">{{ ucwords(str_replace('_', ' ', $request->type)) }}</span>
                            @endif
                        </td>

                        <td>
                            <strong style="color:var(--text);">$ {{ number_format($request->amount, 2) }}</strong>
                            <div class="wr-small">USD: {{ number_format($request->usd_rate ?? 0, 2) }}</div>
                            <div class="wr-small">INR: {{ number_format($request->inr_rate ?? 0, 2) }}</div>
                        </td>

                        <td>
                            <strong style="color:var(--yellow);">INR {{ number_format($request->total_amount ?? 0, 2) }}</strong>
                            <div class="wr-small">Fee: INR {{ number_format($request->fee ?? 0, 2) }}</div>
                        </td>

                        <td>
                            @if($request->payment_slip)
                                <img src="{{ url('/api/storage/' . $request->payment_slip) }}"
                                     class="wr-slip"
                                     onclick="wrOpenLightbox('{{ url('/api/storage/' . $request->payment_slip) }}')">
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
                            <div class="wr-small">{{ $request->created_at->format('Y-m-d') }}</div>
                            <div class="wr-small">{{ $request->created_at->format('h:i A') }}</div>
                        </td>

                        <td>
                           <div class="wr-actions">

    <button type="button"
        class="wr-btn wr-btn-yellow"
        onclick="wrOpenModal('wrView{{ $request->id }}')">
        👁 View
    </button>

    <a class="wr-btn wr-btn-blue"
       href="{{ route('admin.wallet-requests.chat', $request) }}">
        💬 Chat
    </a>

</div>

                                @if($request->status === 'pending')
                                    <form method="POST" action="{{ route('admin.wallet-requests.approve', $request) }}" style="margin:0;">
                                        @csrf
                                        <button class="wr-btn wr-btn-green" onclick="return confirm('Approve this request?')">Approve</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.wallet-requests.reject', $request) }}" style="margin:0;">
                                        @csrf
                                        <button class="wr-btn wr-btn-red" onclick="return confirm('Reject this request?')">Reject</button>
                                    </form>
                                @else
                                    <span class="wr-small">Processed</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:var(--muted);">
                            No wallet requests found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="wr-pagination">
            {{ $requests->appends(request()->query())->links() }}
        </div>
    </div>

    @foreach($requests as $request)
        <div class="wr-modal-overlay" id="wrView{{ $request->id }}">
            <div class="wr-modal">
                <div class="wr-modal-hdr">
                    <h2 class="wr-modal-title">Wallet Request Details</h2>
                    <button class="wr-modal-close" onclick="wrCloseModal('wrView{{ $request->id }}')">✕</button>
                </div>

                <div class="wr-modal-body">
                    <div class="wr-detail-grid">
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
                            <div class="wr-detail-label">Type</div>
                            <div class="wr-detail-value">{{ $request->type === 'deposit' ? 'Buy USD' : 'Sell USD' }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">USD Amount</div>
                            <div class="wr-detail-value">$ {{ number_format($request->amount, 2) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">USD Rate</div>
                            <div class="wr-detail-value">{{ number_format($request->usd_rate ?? 0, 2) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">INR Rate</div>
                            <div class="wr-detail-value">{{ number_format($request->inr_rate ?? 0, 2) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">Converted INR</div>
                            <div class="wr-detail-value">INR {{ number_format($request->converted_amount ?? 0, 2) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">Xynder Fee</div>
                            <div class="wr-detail-value">INR {{ number_format($request->xynder_fee ?? 0, 2) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">Network Fee</div>
                            <div class="wr-detail-value">INR {{ number_format($request->network_fee ?? 0, 2) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">Total Fee</div>
                            <div class="wr-detail-value">INR {{ number_format($request->fee ?? 0, 2) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">Total INR</div>
                            <div class="wr-detail-value" style="color:var(--yellow);">INR {{ number_format($request->total_amount ?? 0, 2) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">Status</div>
                            <div class="wr-detail-value">{{ ucfirst($request->status) }}</div>
                        </div>

                        <div class="wr-detail-item">
                            <div class="wr-detail-label">Date</div>
                            <div class="wr-detail-value">{{ $request->created_at->format('Y-m-d h:i A') }}</div>
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
                                 onclick="wrOpenLightbox('{{ url('/api/storage/' . $request->payment_slip) }}')">
                        </div>
                    @endif
                </div>

                <div class="wr-modal-footer">
                    <button class="wr-btn wr-btn-ghost" onclick="wrCloseModal('wrView{{ $request->id }}')">Close</button>

                    @if($request->status === 'pending')
                        <form method="POST" action="{{ route('admin.wallet-requests.approve', $request) }}" style="margin:0;">
                            @csrf
                            <button class="wr-btn wr-btn-green" onclick="return confirm('Approve this request?')">Approve</button>
                        </form>

                        <form method="POST" action="{{ route('admin.wallet-requests.reject', $request) }}" style="margin:0;">
                            @csrf
                            <button class="wr-btn wr-btn-red" onclick="return confirm('Reject this request?')">Reject</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <div class="wr-lightbox" id="wrLightbox" onclick="wrCloseLightbox()">
        <button class="wr-lightbox-close" onclick="wrCloseLightbox()">✕</button>
        <img id="wrLightboxImg" src="">
    </div>

</div>
</div>

<script>
function wrOpenModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function wrCloseModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
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
    document.getElementById('wrLightboxImg').src = src;
    document.getElementById('wrLightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function wrCloseLightbox() {
    document.getElementById('wrLightbox').classList.remove('open');
    document.body.style.overflow = '';
}

document.querySelectorAll('.wr-auto-filter').forEach(function(el) {
    el.addEventListener('change', function() {
        document.getElementById('wrFilterForm').submit();
    });
});

let wrSearchTimer;
document.getElementById('wrSearch').addEventListener('keyup', function() {
    clearTimeout(wrSearchTimer);
    wrSearchTimer = setTimeout(function() {
        document.getElementById('wrFilterForm').submit();
    }, 480);
});

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
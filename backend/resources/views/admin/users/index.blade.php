@extends('layouts.admin', ['title' => 'Clients & Merchants'])

@section('content')

<style>
:root {
    --yellow:#facc15;
    --blue:#3b82f6;
    --red:#ef4444;
    --green:#22c55e;
    --page:#020617;
    --card:#0f172a;
    --border:#1e293b;
    --input:#111827;
    --text:#f8fafc;
    --muted:#94a3b8;
    --muted2:#cbd5e1;
}

.um * {
    box-sizing:border-box;
}

.um-page {
    background:var(--page);
    padding:20px;
    min-height:100vh;
    color:var(--text);
}

.um-stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:18px;
}

.um-stat {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:14px;
    padding:15px;
    display:flex;
    align-items:center;
    gap:12px;
}

.um-stat-icon {
    width:42px;
    height:42px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    background:#111827;
}

.um-stat-num {
    font-size:22px;
    font-weight:800;
}

.um-stat-label {
    font-size:12px;
    color:var(--muted);
}

.um-card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
    overflow:hidden;
}

.um-card-head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    padding:15px;
    border-bottom:1px solid var(--border);
    flex-wrap:wrap;
}

.um-tabs {
    display:flex;
    gap:6px;
    background:#020617;
    border:1px solid var(--border);
    border-radius:12px;
    padding:5px;
}

.um-tab {
    padding:8px 18px;
    border-radius:9px;
    font-size:13px;
    font-weight:700;
    color:var(--muted2);
}

.um-tab.active,
.um-tab:hover {
    background:var(--yellow);
    color:#111827;
}

.um-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    border:none;
    border-radius:10px;
    padding:9px 13px;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    text-decoration:none;
}

.um-btn-yellow { background:var(--yellow); color:#111827; }
.um-btn-blue { background:var(--blue); color:#fff; }
.um-btn-red { background:var(--red); color:#fff; }
.um-btn-green { background:var(--green); color:#052e16; }
.um-btn-ghost { background:#020617; color:var(--muted2); border:1px solid var(--border); }

.um-icon-btn {
    width:36px;
    height:36px;
    border-radius:10px;
    border:1px solid var(--border);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    font-size:16px;
}

.um-icon-view { background:rgba(250,204,21,.15); color:#fde047; }
.um-icon-edit { background:rgba(59,130,246,.15); color:#60a5fa; }
.um-icon-delete { background:rgba(239,68,68,.15); color:#f87171; }
.um-icon-verify { background:rgba(34,197,94,.15); color:#86efac; }
.um-icon-block { background:rgba(239,68,68,.15); color:#fca5a5; }

.um-icon-btn:hover {
    transform:translateY(-1px);
    opacity:.9;
}

.um-filters {
    display:flex;
    gap:8px;
    padding:12px 15px;
    background:#020617;
    border-bottom:1px solid var(--border);
    flex-wrap:wrap;
}

.um-input,
.um-select {
    height:38px;
    background:var(--input);
    border:1px solid #334155;
    color:var(--text);
    border-radius:10px;
    padding:0 12px;
}

.um-input {
    width:280px;
}

.um-select {
    width:140px;
}

.um-table-wrap {
    overflow-x:auto;
}

.um-table {
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

.um-table th {
    background:#020617;
    color:var(--muted);
    padding:12px;
    font-size:11px;
    text-transform:uppercase;
    text-align:left;
    border-bottom:1px solid var(--border);
}

.um-table td {
    padding:13px 12px;
    border-bottom:1px solid var(--border);
    color:var(--muted2);
    vertical-align:middle;
}

.um-table tr:hover td {
    background:#111827;
}

.um-user-cell {
    display:flex;
    align-items:center;
    gap:12px;
    min-width:280px;
}

.um-avatar,
.um-avatar-placeholder {
    width:42px;
    height:42px;
    border-radius:50%;
    flex-shrink:0;
}

.um-avatar {
    object-fit:cover;
    border:2px solid #334155;
    cursor:pointer;
}

.um-avatar-placeholder {
    background:#1e293b;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    color:#facc15;
}

.um-user-name {
    font-weight:800;
    color:var(--text);
    margin-bottom:4px;
}

.um-user-email {
    font-size:12px;
    color:#93c5fd;
    word-break:break-all;
}

.um-wallet-pill {
    display:inline-flex;
    align-items:center;
    gap:5px;
    margin-top:5px;
    padding:4px 8px;
    border-radius:999px;
    background:rgba(250,204,21,.13);
    color:#fde68a;
    font-size:11px;
    font-weight:800;
}

.um-badge {
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:5px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    white-space:nowrap;
}

.um-badge-green { background:rgba(34,197,94,.15); color:#86efac; }
.um-badge-red { background:rgba(239,68,68,.15); color:#fca5a5; }
.um-badge-yellow { background:rgba(250,204,21,.15); color:#fde68a; }
.um-badge-gray { background:#1e293b; color:#cbd5e1; }

.um-actions {
    display:flex;
    gap:6px;
    align-items:center;
    flex-wrap:nowrap;
}

.um-actions form {
    margin:0;
}

.um-pagination {
    padding:13px 15px;
    border-top:1px solid var(--border);
}

.um-modal-overlay {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.75);
    z-index:9999;
    align-items:center;
    justify-content:center;
    padding:15px;
}

.um-modal-overlay.open {
    display:flex;
}

.um-modal {
    width:850px;
    max-width:100%;
    max-height:92vh;
    overflow:auto;
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
}

.um-modal-hdr {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    position:sticky;
    top:0;
    background:var(--card);
    z-index:5;
}

.um-modal-title {
    margin:0;
    font-size:17px;
    font-weight:900;
}

.um-modal-close {
    width:34px;
    height:34px;
    border:none;
    border-radius:10px;
    background:#1e293b;
    color:#fff;
    cursor:pointer;
}

.um-modal-body {
    padding:20px;
}

.um-view-hero {
    display:flex;
    gap:15px;
    align-items:center;
    background:#020617;
    border:1px solid var(--border);
    border-radius:14px;
    padding:16px;
    margin-bottom:16px;
}

.um-view-hero img,
.um-view-hero-placeholder {
    width:72px;
    height:72px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid var(--yellow);
}

.um-view-hero-placeholder {
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    font-weight:900;
    color:#facc15;
    background:#1e293b;
}

.um-view-hero-name {
    font-size:19px;
    font-weight:900;
}

.um-view-hero-meta {
    color:#93c5fd;
    font-size:13px;
    margin-top:4px;
}

.um-detail-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
}

.um-detail-item {
    background:#020617;
    border:1px solid var(--border);
    border-radius:10px;
    padding:11px;
}

.um-detail-lbl {
    font-size:11px;
    color:var(--muted);
    text-transform:uppercase;
    font-weight:800;
    margin-bottom:5px;
}

.um-detail-val {
    font-size:13px;
    color:var(--text);
    font-weight:700;
    word-break:break-all;
}

.um-form-grid {
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:12px;
}

.um-form-section {
    grid-column:1 / -1;
    color:var(--muted);
    text-transform:uppercase;
    font-size:11px;
    font-weight:900;
    border-bottom:1px solid var(--border);
    padding-bottom:6px;
}

.um-form-group {
    display:flex;
    flex-direction:column;
    gap:6px;
}

.um-form-group.full {
    grid-column:1 / -1;
}

.um-form-label {
    font-size:12px;
    font-weight:800;
    color:var(--muted2);
}

.um-form-control {
    height:38px;
    border-radius:10px;
    border:1px solid #334155;
    background:var(--input);
    color:#fff;
    padding:0 12px;
}

.um-form-footer {
    display:flex;
    justify-content:flex-end;
    gap:8px;
    padding:15px 20px;
    border-top:1px solid var(--border);
    background:#020617;
}

.um-lightbox {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.9);
    z-index:10001;
    align-items:center;
    justify-content:center;
    flex-direction:column;
}

.um-lightbox.open {
    display:flex;
}

.um-lightbox img {
    max-width:90vw;
    max-height:80vh;
    border-radius:12px;
}

.um-lightbox-close {
    position:absolute;
    top:18px;
    right:24px;
    background:none;
    border:none;
    color:white;
    font-size:30px;
    cursor:pointer;
}

@media(max-width:768px) {
    .um-stats { grid-template-columns:repeat(2,1fr); }
    .um-detail-grid { grid-template-columns:1fr; }
    .um-form-grid { grid-template-columns:1fr; }
    .um-input, .um-select { width:100%; }
}
</style>

<div class="um">
<div class="um-page">

    <div class="um-stats">
        <div class="um-stat">
            <div class="um-stat-icon">👤</div>
            <div>
                <div class="um-stat-num">{{ $stats['clients'] ?? 0 }}</div>
                <div class="um-stat-label">Total Clients</div>
            </div>
        </div>

        <div class="um-stat">
            <div class="um-stat-icon">🏪</div>
            <div>
                <div class="um-stat-num">{{ $stats['merchants'] ?? 0 }}</div>
                <div class="um-stat-label">Total Merchants</div>
            </div>
        </div>

        <div class="um-stat">
            <div class="um-stat-icon">✅</div>
            <div>
                <div class="um-stat-num">{{ $stats['active'] ?? 0 }}</div>
                <div class="um-stat-label">Active Users</div>
            </div>
        </div>

        <div class="um-stat">
            <div class="um-stat-icon">🟢</div>
            <div>
                <div class="um-stat-num">{{ $stats['online'] ?? 0 }}</div>
                <div class="um-stat-label">Online Now</div>
            </div>
        </div>
    </div>

    <div class="um-card">
        <div class="um-card-head">
            <div class="um-tabs">
                <a href="{{ route('admin.users.index', ['type' => 'client']) }}"
                   class="um-tab {{ $type === 'client' ? 'active' : '' }}">
                    👤 Clients
                </a>

                <a href="{{ route('admin.users.index', ['type' => 'merchant']) }}"
                   class="um-tab {{ $type === 'merchant' ? 'active' : '' }}">
                    🏪 Merchants
                </a>
            </div>

            <a href="{{ route('admin.users.create') }}" class="um-btn um-btn-yellow">
                ＋ Add User
            </a>
        </div>

        <form id="umFilterForm" method="GET" action="{{ route('admin.users.index') }}" class="um-filters">
            <input type="hidden" name="type" value="{{ $type }}">

            <input id="umSearch"
                   type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="um-input"
                   placeholder="Search name, Gmail, phone, wallet ID">

            <select name="status" class="um-select um-auto-filter">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
            </select>

            <select name="online" class="um-select um-auto-filter">
                <option value="">All Online</option>
                <option value="1" {{ request('online') === '1' ? 'selected' : '' }}>Online</option>
                <option value="0" {{ request('online') === '0' ? 'selected' : '' }}>Offline</option>
            </select>

            <a href="{{ route('admin.users.index', ['type' => $type]) }}" class="um-btn um-btn-ghost">
                ↺ Reset
            </a>
        </form>

        <div class="um-table-wrap">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>User / Gmail / Wallet</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Bank / UPI</th>
                        <th>Online</th>
                        <th>Verification</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="um-user-cell">
                                @if($user->photo)
                                    <img src="{{ asset('storage/'.$user->photo) }}"
                                         class="um-avatar"
                                         onclick="umOpenLightbox('{{ asset('storage/'.$user->photo) }}')">
                                @else
                                    <div class="um-avatar-placeholder">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <div class="um-user-name">{{ $user->name }}</div>

                                    <div class="um-user-email">
                                         {{ $user->email }}
                                    </div>

                                    <div class="um-wallet-pill">
                                         Wallet ID: {{ $user->wallet_id ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($user->role === 'merchant')
                                <span class="um-badge um-badge-yellow">🏪 Merchant</span>
                            @else
                                <span class="um-badge um-badge-gray">👤 Client</span>
                            @endif
                        </td>

                        <td>{{ $user->phone ?? '—' }}</td>

                        <td>
                            <strong>LKR {{ number_format($user->balance ?? 0, 2) }}</strong>
                        </td>

                        <td>
                            @if($user->status === 'active')
                                <span class="um-badge um-badge-green">Active</span>
                            @else
                                <span class="um-badge um-badge-red">Blocked</span>
                            @endif
                        </td>

                        <td>
                            <div style="font-size:12px; line-height:1.7;">
                                <strong>{{ $user->bank_name ?? '—' }}</strong>

                                @if($user->account_number)
                                    <div style="color:var(--muted);">Acc: {{ $user->account_number }}</div>
                                @endif

                                @if($user->upi_id)
                                    <div style="color:var(--muted);">UPI: {{ $user->upi_id }}</div>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($user->is_online)
                                <span class="um-badge um-badge-green">🟢 Online</span>
                            @else
                                <span class="um-badge um-badge-gray">⚫ Offline</span>
                            @endif

                            @if($user->last_seen_at)
                                <div style="font-size:10px;color:var(--muted);margin-top:4px;">
                                    {{ $user->last_seen_at->format('d M, h:i A') }}
                                </div>
                            @endif
                        </td>

                        <td>
                            @if($user->is_verified)
                                <span class="um-badge um-badge-green">Verified</span>
                            @else
                                <span class="um-badge um-badge-yellow">Not Verified</span>
                            @endif
                        </td>

                        <td>
                            <div class="um-actions">
                                <button type="button"
                                        class="um-icon-btn um-icon-view"
                                        title="View"
                                        onclick="umOpenModal('umView{{ $user->id }}')">
                                    👁
                                </button>

                                <button type="button"
                                        class="um-icon-btn um-icon-edit"
                                        title="Edit"
                                        onclick="umOpenModal('umEdit{{ $user->id }}')">
                                    ✏️
                                </button>

                                <form method="POST" action="{{ route('admin.users.toggle-verification', $user) }}">
                                    @csrf
                                    <button type="submit"
                                            class="um-icon-btn {{ $user->is_verified ? 'um-icon-block' : 'um-icon-verify' }}"
                                            title="{{ $user->is_verified ? 'Unverify' : 'Verify' }}"
                                            onclick="return confirm('{{ $user->is_verified ? 'Mark as unverified?' : 'Verify this user?' }}')">
                                        {{ $user->is_verified ? '❌' : '✅' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf
                                    <button type="submit"
                                            class="um-icon-btn {{ $user->status === 'active' ? 'um-icon-block' : 'um-icon-verify' }}"
                                            title="{{ $user->status === 'active' ? 'Block' : 'Activate' }}"
                                            onclick="return confirm('{{ $user->status === 'active' ? 'Block this user?' : 'Activate this user?' }}')">
                                        {{ $user->status === 'active' ? '🚫' : '🟢' }}
                                    </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="um-icon-btn um-icon-delete"
                                            title="Delete">
                                        🗑
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:var(--muted);">
                            No {{ $type }}s found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="um-pagination">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>

    @foreach($users as $user)
        <div class="um-modal-overlay" id="umView{{ $user->id }}">
            <div class="um-modal">
                <div class="um-modal-hdr">
                    <h2 class="um-modal-title">👁 User Details</h2>
                    <button type="button" class="um-modal-close" onclick="umCloseModal('umView{{ $user->id }}')">✕</button>
                </div>

                <div class="um-modal-body">
                    <div class="um-view-hero">
                        @if($user->photo)
                            <img src="{{ asset('storage/'.$user->photo) }}"
                                 onclick="umOpenLightbox('{{ asset('storage/'.$user->photo) }}')">
                        @else
                            <div class="um-view-hero-placeholder">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <div class="um-view-hero-name">{{ $user->name }}</div>
                            <div class="um-view-hero-meta">✉️ {{ $user->email }}</div>
                            <div class="um-wallet-pill">💳 Wallet ID: {{ $user->wallet_id ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="um-detail-grid">
                        <div class="um-detail-item"><div class="um-detail-lbl">Name</div><div class="um-detail-val">{{ $user->name }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Gmail</div><div class="um-detail-val">{{ $user->email }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Wallet ID</div><div class="um-detail-val">{{ $user->wallet_id ?? 'N/A' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Role</div><div class="um-detail-val">{{ ucfirst($user->role) }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Phone</div><div class="um-detail-val">{{ $user->phone ?? '—' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Balance</div><div class="um-detail-val">LKR {{ number_format($user->balance ?? 0, 2) }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Status</div><div class="um-detail-val">{{ ucfirst($user->status ?? '—') }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Verification</div><div class="um-detail-val">{{ $user->is_verified ? 'Verified' : 'Not Verified' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Online</div><div class="um-detail-val">{{ $user->is_online ? 'Online' : 'Offline' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Country</div><div class="um-detail-val">{{ $user->country ?? '—' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">State</div><div class="um-detail-val">{{ $user->state ?? '—' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Address</div><div class="um-detail-val">{{ $user->address ?? '—' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Bank</div><div class="um-detail-val">{{ $user->bank_name ?? '—' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">Account Number</div><div class="um-detail-val">{{ $user->account_number ?? '—' }}</div></div>
                        <div class="um-detail-item"><div class="um-detail-lbl">UPI ID</div><div class="um-detail-val">{{ $user->upi_id ?? '—' }}</div></div>
                    </div>
                </div>

                <div class="um-form-footer">
                    <button type="button" class="um-btn um-btn-ghost" onclick="umCloseModal('umView{{ $user->id }}')">Close</button>
                    <button type="button"
                            class="um-btn um-btn-blue"
                            onclick="umCloseModal('umView{{ $user->id }}'); umOpenModal('umEdit{{ $user->id }}')">
                        ✏️ Edit
                    </button>
                </div>
            </div>
        </div>

        <div class="um-modal-overlay" id="umEdit{{ $user->id }}">
            <div class="um-modal">
                <div class="um-modal-hdr">
                    <h2 class="um-modal-title">✏️ Edit User</h2>
                    <button type="button" class="um-modal-close" onclick="umCloseModal('umEdit{{ $user->id }}')">✕</button>
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="um-modal-body">
                        <div class="um-form-grid">
                            <div class="um-form-section">Basic Information</div>

                            <div class="um-form-group">
                                <label class="um-form-label">Name *</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="um-form-control" required>
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Gmail *</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="um-form-control" required>
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Wallet ID</label>
                                <input type="text" value="{{ $user->wallet_id }}" class="um-form-control" readonly>
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="um-form-control">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Password</label>
                                <input type="password" name="password" class="um-form-control" placeholder="Leave blank to keep old password">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Role</label>
                                <select name="role" class="um-form-control">
                                    <option value="client" {{ $user->role === 'client' ? 'selected' : '' }}>Client</option>
                                    <option value="merchant" {{ $user->role === 'merchant' ? 'selected' : '' }}>Merchant</option>
                                </select>
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Status</label>
                                <select name="status" class="um-form-control">
                                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="blocked" {{ $user->status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                </select>
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Balance</label>
                                <input type="number" step="0.01" name="balance" value="{{ old('balance', $user->balance) }}" class="um-form-control">
                            </div>

                            <div class="um-form-section">Address</div>

                            <div class="um-form-group">
                                <label class="um-form-label">Country</label>
                                <input type="text" name="country" value="{{ old('country', $user->country) }}" class="um-form-control">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">State</label>
                                <input type="text" name="state" value="{{ old('state', $user->state) }}" class="um-form-control">
                            </div>

                            <div class="um-form-group full">
                                <label class="um-form-label">Address</label>
                                <input type="text" name="address" value="{{ old('address', $user->address) }}" class="um-form-control">
                            </div>

                            <div class="um-form-section">Bank / UPI</div>

                            <div class="um-form-group">
                                <label class="um-form-label">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}" class="um-form-control">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Branch</label>
                                <input type="text" name="branch" value="{{ old('branch', $user->branch) }}" class="um-form-control">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Account Number</label>
                                <input type="text" name="account_number" value="{{ old('account_number', $user->account_number) }}" class="um-form-control">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Account Type</label>
                                <input type="text" name="account_type" value="{{ old('account_type', $user->account_type) }}" class="um-form-control">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">IFSC</label>
                                <input type="text" name="ifsc" value="{{ old('ifsc', $user->ifsc) }}" class="um-form-control">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">UPI ID</label>
                                <input type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}" class="um-form-control">
                            </div>

                            <div class="um-form-section">Photos</div>

                            <div class="um-form-group">
                                <label class="um-form-label">Profile Photo</label>
                                <input type="file" name="photo" class="um-form-control" accept="image/*" style="height:auto;padding:8px;">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">Aadhaar Photo</label>
                                <input type="file" name="aadhaar_photo" class="um-form-control" accept="image/*" style="height:auto;padding:8px;">
                            </div>

                            <div class="um-form-group">
                                <label class="um-form-label">UPI QR</label>
                                <input type="file" name="upi_qr" class="um-form-control" accept="image/*" style="height:auto;padding:8px;">
                            </div>
                        </div>
                    </div>

                    <div class="um-form-footer">
                        <button type="button" class="um-btn um-btn-ghost" onclick="umCloseModal('umEdit{{ $user->id }}')">Cancel</button>
                        <button type="submit" class="um-btn um-btn-yellow">💾 Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <div class="um-lightbox" id="umLightbox" onclick="umCloseLightbox()">
        <button type="button" class="um-lightbox-close" onclick="umCloseLightbox()">✕</button>
        <img id="umLightboxImg" src="" alt="">
    </div>

</div>
</div>

<script>
function umOpenModal(id) {
    document.querySelectorAll('.um-modal-overlay.open').forEach(function(modal) {
        modal.classList.remove('open');
    });

    var modal = document.getElementById(id);

    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function umCloseModal(id) {
    var modal = document.getElementById(id);

    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.querySelectorAll('.um-modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
});

function umOpenLightbox(src) {
    var lightbox = document.getElementById('umLightbox');
    var img = document.getElementById('umLightboxImg');

    if (lightbox && img) {
        img.src = src;
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function umCloseLightbox() {
    var lightbox = document.getElementById('umLightbox');

    if (lightbox) {
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.um-modal-overlay.open').forEach(function(modal) {
            modal.classList.remove('open');
        });

        umCloseLightbox();
        document.body.style.overflow = '';
    }
});

document.querySelectorAll('.um-auto-filter').forEach(function(el) {
    el.addEventListener('change', function() {
        document.getElementById('umFilterForm').submit();
    });
});

var umSearchInput = document.getElementById('umSearch');
var umSearchTimer = null;

if (umSearchInput) {
    umSearchInput.addEventListener('keyup', function() {
        clearTimeout(umSearchTimer);

        umSearchTimer = setTimeout(function() {
            document.getElementById('umFilterForm').submit();
        }, 500);
    });
}
</script>

@endsection

@extends('layouts.admin', ['title' => 'Clients & Merchants'])

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

.um-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.um-hero{
    background:var(--hero);
    padding:65px 85px 120px;
    position:relative;
    overflow:hidden;
}

.um-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.um-hero-inner{
    position:relative;
    z-index:2;
    max-width:680px;
}

.um-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.um-title{
    font-size:46px;
    line-height:1.12;
    font-weight:900;
    margin:0 0 18px;
}

.um-title span{
    color:var(--red);
    display:block;
}

.um-subtitle{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    max-width:570px;
}

.um-wrap{
    position:relative;
    z-index:5;
    margin:-82px 85px 0;
}

.um-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:26px;
}

.um-stat{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:20px;
    display:flex;
    align-items:center;
    gap:14px;
    min-height:105px;
}

.um-stat-icon{
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

.um-stat-num{
    font-size:26px;
    font-weight:900;
}

.um-stat-label{
    color:var(--muted);
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-top:3px;
}

.um-card{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.um-card-head{
    background:var(--panel);
    border-bottom:1px solid var(--line);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding:18px 22px;
    flex-wrap:wrap;
}

.um-tabs{
    display:flex;
    gap:0;
    background:var(--input);
    border:1px solid var(--line);
    border-radius:5px;
    overflow:hidden;
}

.um-tab{
    padding:11px 22px;
    font-size:13px;
    font-weight:900;
    color:var(--muted);
    text-decoration:none;
    border-right:1px solid var(--line);
}

.um-tab:last-child{border-right:0}

.um-tab.active,
.um-tab:hover{
    background:rgba(232,25,44,.14);
    color:var(--red);
    box-shadow:inset 0 -3px 0 var(--red);
}

.um-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    border:0;
    border-radius:4px;
    padding:10px 14px;
    font-size:13px;
    font-weight:900;
    cursor:pointer;
    text-decoration:none;
    white-space:nowrap;
}

.um-btn-red{background:var(--red);color:#fff}
.um-btn-red:hover{background:var(--red2);color:#fff}
.um-btn-green{background:var(--green);color:#052e16}
.um-btn-ghost{background:var(--input);color:#fff;border:1px solid var(--line)}
.um-btn-ghost:hover{border-color:var(--red);color:#fff}

.um-filters{
    display:flex;
    gap:10px;
    padding:16px 22px;
    background:var(--box);
    border-bottom:1px solid var(--line);
    flex-wrap:wrap;
}

.um-input,
.um-select{
    height:42px;
    background:var(--input);
    border:1px solid var(--line);
    color:var(--text);
    border-radius:4px;
    padding:0 13px;
    outline:0;
    font-weight:700;
}

.um-input:focus,
.um-select:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.um-input{width:310px}
.um-select{width:150px}

.um-table-wrap{overflow-x:auto}

.um-table{
    width:100%;
    min-width:1180px;
    border-collapse:collapse;
}

.um-table th{
    background:var(--panel);
    color:var(--muted2);
    padding:14px 16px;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    text-align:left;
}

.um-table td{
    padding:15px 16px;
    border-top:1px solid var(--line);
    color:#c9ced3;
    font-size:13px;
    font-weight:700;
    vertical-align:middle;
}

.um-table tr:hover td{background:#30363a}

.um-user-cell{
    display:flex;
    align-items:center;
    gap:12px;
    min-width:285px;
}

.um-avatar,
.um-avatar-placeholder{
    width:44px;
    height:44px;
    border-radius:50%;
    flex-shrink:0;
}

.um-avatar{
    object-fit:cover;
    border:2px solid var(--line);
    cursor:pointer;
}

.um-avatar-placeholder{
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    font-size:18px;
}

.um-user-name{
    color:#fff;
    font-weight:900;
    margin-bottom:4px;
}

.um-user-email{
    font-size:12px;
    color:var(--muted);
    word-break:break-all;
}

.um-wallet-pill{
    display:inline-flex;
    align-items:center;
    gap:5px;
    margin-top:6px;
    padding:4px 8px;
    border-radius:20px;
    background:#3a1018;
    color:#ff9aaa;
    font-size:11px;
    font-weight:900;
}

.um-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    white-space:nowrap;
}

.um-badge-green{background:#0d2b1e;color:var(--green)}
.um-badge-red{background:#3a1018;color:#ff6b7b}
.um-badge-yellow{background:#3b2a09;color:var(--gold)}
.um-badge-gray{background:var(--input);color:#d5dade;border:1px solid var(--line)}

.um-actions{
    display:flex;
    gap:7px;
    align-items:center;
    flex-wrap:nowrap;
}

.um-actions form{margin:0}

.um-icon-btn{
    width:36px;
    height:36px;
    border-radius:4px;
    border:1px solid var(--line);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    color:#fff;
    background:var(--input);
    font-size:16px;
}

.um-icon-view{color:var(--gold)}
.um-icon-edit{color:#fff}
.um-icon-delete{color:#ff6b7b}
.um-icon-verify{color:var(--green)}
.um-icon-block{color:#ff6b7b}

.um-icon-btn:hover{
    border-color:var(--red);
    transform:translateY(-1px);
}

.um-pagination{
    padding:16px 22px;
    border-top:1px solid var(--line);
    background:var(--panel);
}

.um-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:800;
}

.um-modal-overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.78);
    z-index:9999;
    align-items:center;
    justify-content:center;
    padding:18px;
}

.um-modal-overlay.open{display:flex}

.um-modal{
    width:870px;
    max-width:100%;
    max-height:92vh;
    overflow:auto;
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
}

.um-modal-hdr{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:17px 22px;
    border-bottom:1px solid var(--line);
    position:sticky;
    top:0;
    background:var(--panel);
    z-index:5;
}

.um-modal-title{
    margin:0;
    font-size:18px;
    font-weight:900;
    display:flex;
    align-items:center;
    gap:8px;
}

.um-modal-title i{color:var(--red)}

.um-modal-close{
    width:34px;
    height:34px;
    border:1px solid var(--line);
    border-radius:4px;
    background:var(--input);
    color:#fff;
    cursor:pointer;
}

.um-modal-body{padding:22px}

.um-view-hero{
    display:flex;
    gap:15px;
    align-items:center;
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:6px;
    padding:16px;
    margin-bottom:16px;
}

.um-view-hero img,
.um-view-hero-placeholder{
    width:76px;
    height:76px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid var(--red);
}

.um-view-hero-placeholder{
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    font-weight:900;
    color:var(--red);
    background:#3a1018;
}

.um-view-hero-name{
    font-size:20px;
    font-weight:900;
}

.um-view-hero-meta{
    color:var(--muted);
    font-size:13px;
    margin-top:4px;
}

.um-detail-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
}

.um-detail-item{
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:5px;
    padding:12px;
}

.um-detail-lbl{
    font-size:11px;
    color:var(--muted);
    text-transform:uppercase;
    font-weight:900;
    margin-bottom:6px;
}

.um-detail-val{
    font-size:13px;
    color:#fff;
    font-weight:800;
    word-break:break-all;
}

.um-form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:14px;
}

.um-form-section{
    grid-column:1/-1;
    color:var(--red);
    text-transform:uppercase;
    font-size:12px;
    font-weight:900;
    letter-spacing:.08em;
    border-bottom:1px solid var(--line);
    padding:10px 0 8px;
}

.um-form-group{
    display:flex;
    flex-direction:column;
    gap:7px;
}

.um-form-group.full{grid-column:1/-1}

.um-form-label{
    font-size:12px;
    font-weight:900;
    color:#c3c6ca;
}

.um-form-control{
    height:42px;
    border-radius:4px;
    border:1px solid var(--line);
    background:var(--input);
    color:#fff;
    padding:0 13px;
    outline:0;
    font-weight:700;
}

.um-form-control:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.um-form-footer{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    padding:16px 22px;
    border-top:1px solid var(--line);
    background:var(--panel);
}

.um-lightbox{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.9);
    z-index:10001;
    align-items:center;
    justify-content:center;
    flex-direction:column;
}

.um-lightbox.open{display:flex}

.um-lightbox img{
    max-width:90vw;
    max-height:80vh;
    border-radius:8px;
    border:1px solid var(--line);
}

.um-lightbox-close{
    position:absolute;
    top:18px;
    right:24px;
    background:var(--red);
    border:0;
    color:white;
    font-size:22px;
    width:38px;
    height:38px;
    border-radius:4px;
    cursor:pointer;
}

@media(max-width:1100px){
    .um-stats{grid-template-columns:repeat(2,1fr)}
    .um-wrap{margin:-82px 24px 0}
}

@media(max-width:768px){
    .um-page{margin:-16px}
    .um-hero{padding:45px 24px 110px}
    .um-title{font-size:34px}
    .um-wrap{margin:-76px 18px 0}
    .um-stats{grid-template-columns:1fr}
    .um-detail-grid,.um-form-grid{grid-template-columns:1fr}
    .um-input,.um-select{width:100%}
    .um-card-head{align-items:flex-start}
}
</style>
@endpush

@section('content')

<div class="um-page">

    <section class="um-hero">
        <div class="um-hero-inner">
            <div class="um-eyebrow">Xynder Wallet Admin</div>

            <h1 class="um-title">
                Clients &
                <span>Merchants</span>
            </h1>

            <div class="um-subtitle">
                Manage clients and merchants, wallet balances, KYC verification,
                online status, banking details, and account access from one panel.
            </div>
        </div>
    </section>

    <main class="um-wrap">

        <section class="um-stats">
            <div class="um-stat">
                <div class="um-stat-icon"><i class="ti ti-user"></i></div>
                <div>
                    <div class="um-stat-num">{{ $stats['clients'] ?? 0 }}</div>
                    <div class="um-stat-label">Total Clients</div>
                </div>
            </div>

            <div class="um-stat">
                <div class="um-stat-icon"><i class="ti ti-building-store"></i></div>
                <div>
                    <div class="um-stat-num">{{ $stats['merchants'] ?? 0 }}</div>
                    <div class="um-stat-label">Total Merchants</div>
                </div>
            </div>

            <div class="um-stat">
                <div class="um-stat-icon"><i class="ti ti-circle-check"></i></div>
                <div>
                    <div class="um-stat-num">{{ $stats['active'] ?? 0 }}</div>
                    <div class="um-stat-label">Active Users</div>
                </div>
            </div>

            <div class="um-stat">
                <div class="um-stat-icon"><i class="ti ti-wifi"></i></div>
                <div>
                    <div class="um-stat-num">{{ $stats['online'] ?? 0 }}</div>
                    <div class="um-stat-label">Online Now</div>
                </div>
            </div>
        </section>

        <section class="um-card">
            <div class="um-card-head">
                <div class="um-tabs">
                    <a href="{{ route('admin.users.index', ['type' => 'client']) }}"
                       class="um-tab {{ $type === 'client' ? 'active' : '' }}">
                        <i class="ti ti-user"></i> Clients
                    </a>

                    <a href="{{ route('admin.users.index', ['type' => 'merchant']) }}"
                       class="um-tab {{ $type === 'merchant' ? 'active' : '' }}">
                        <i class="ti ti-building-store"></i> Merchants
                    </a>
                </div>

                <a href="{{ route('admin.users.create') }}" class="um-btn um-btn-red">
                    <i class="ti ti-user-plus"></i>
                    Add User
                </a>
            </div>

            <form id="umFilterForm" method="GET" action="{{ route('admin.users.index') }}" class="um-filters">
                <input type="hidden" name="type" value="{{ $type }}">

                <input id="umSearch"
                       type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="um-input"
                       placeholder="Search name, email, phone, wallet ID">

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
                    <i class="ti ti-refresh"></i>
                    Reset
                </a>
            </form>

            <div class="um-table-wrap">
                <table class="um-table">
                    <thead>
                        <tr>
                            <th>User / Email / Wallet</th>
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
                                             onclick="umOpenLightbox('{{ asset('storage/'.$user->photo) }}')"
                                             alt="{{ $user->name }}">
                                    @else
                                        <div class="um-avatar-placeholder">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="um-user-name">{{ $user->name }}</div>
                                        <div class="um-user-email">{{ $user->email }}</div>

                                        <div class="um-wallet-pill">
                                            <i class="ti ti-wallet"></i>
                                            Wallet ID: {{ $user->wallet_id ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($user->role === 'merchant')
                                    <span class="um-badge um-badge-yellow">
                                        <i class="ti ti-building-store"></i> Merchant
                                    </span>
                                @else
                                    <span class="um-badge um-badge-gray">
                                        <i class="ti ti-user"></i> Client
                                    </span>
                                @endif
                            </td>

                            <td>{{ $user->phone ?? '—' }}</td>

                            <td>
                                <strong>LKR {{ number_format((float)($user->balance ?? 0), 2) }}</strong>
                            </td>

                            <td>
                                @if(($user->status ?? 'active') === 'active')
                                    <span class="um-badge um-badge-green">Active</span>
                                @else
                                    <span class="um-badge um-badge-red">Blocked</span>
                                @endif
                            </td>

                            <td>
                                <div style="font-size:12px;line-height:1.7;">
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
                                    <span class="um-badge um-badge-green">
                                        <i class="ti ti-circle-filled"></i> Online
                                    </span>
                                @else
                                    <span class="um-badge um-badge-gray">
                                        <i class="ti ti-circle"></i> Offline
                                    </span>
                                @endif

                                @if($user->last_seen_at)
                                    <div style="font-size:10px;color:var(--muted);margin-top:5px;">
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
                                        <i class="ti ti-eye"></i>
                                    </button>

                                    <button type="button"
                                            class="um-icon-btn um-icon-edit"
                                            title="Edit"
                                            onclick="umOpenModal('umEdit{{ $user->id }}')">
                                        <i class="ti ti-edit"></i>
                                    </button>

                                    <form method="POST" action="{{ route('admin.users.toggle-verification', $user) }}">
                                        @csrf
                                        <button type="submit"
                                                class="um-icon-btn {{ $user->is_verified ? 'um-icon-block' : 'um-icon-verify' }}"
                                                title="{{ $user->is_verified ? 'Unverify' : 'Verify' }}"
                                                onclick="return confirm('{{ $user->is_verified ? 'Mark as unverified?' : 'Verify this user?' }}')">
                                            <i class="ti {{ $user->is_verified ? 'ti-x' : 'ti-check' }}"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                        @csrf
                                        <button type="submit"
                                                class="um-icon-btn {{ ($user->status ?? 'active') === 'active' ? 'um-icon-block' : 'um-icon-verify' }}"
                                                title="{{ ($user->status ?? 'active') === 'active' ? 'Block' : 'Activate' }}"
                                                onclick="return confirm('{{ ($user->status ?? 'active') === 'active' ? 'Block this user?' : 'Activate this user?' }}')">
                                            <i class="ti {{ ($user->status ?? 'active') === 'active' ? 'ti-ban' : 'ti-circle-check' }}"></i>
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
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="um-empty">No {{ $type }}s found.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="um-pagination">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </section>

        @foreach($users as $user)
            <div class="um-modal-overlay" id="umView{{ $user->id }}">
                <div class="um-modal">
                    <div class="um-modal-hdr">
                        <h2 class="um-modal-title">
                            <i class="ti ti-eye"></i>
                            User Details
                        </h2>

                        <button type="button" class="um-modal-close" onclick="umCloseModal('umView{{ $user->id }}')">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <div class="um-modal-body">
                        <div class="um-view-hero">
                            @if($user->photo)
                                <img src="{{ asset('storage/'.$user->photo) }}"
                                     onclick="umOpenLightbox('{{ asset('storage/'.$user->photo) }}')"
                                     alt="{{ $user->name }}">
                            @else
                                <div class="um-view-hero-placeholder">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <div class="um-view-hero-name">{{ $user->name }}</div>
                                <div class="um-view-hero-meta">{{ $user->email }}</div>

                                <div class="um-wallet-pill">
                                    <i class="ti ti-wallet"></i>
                                    Wallet ID: {{ $user->wallet_id ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="um-detail-grid">
                            <div class="um-detail-item"><div class="um-detail-lbl">Name</div><div class="um-detail-val">{{ $user->name }}</div></div>
                            <div class="um-detail-item"><div class="um-detail-lbl">Email</div><div class="um-detail-val">{{ $user->email }}</div></div>
                            <div class="um-detail-item"><div class="um-detail-lbl">Wallet ID</div><div class="um-detail-val">{{ $user->wallet_id ?? 'N/A' }}</div></div>
                            <div class="um-detail-item"><div class="um-detail-lbl">Role</div><div class="um-detail-val">{{ ucfirst($user->role) }}</div></div>
                            <div class="um-detail-item"><div class="um-detail-lbl">Phone</div><div class="um-detail-val">{{ $user->phone ?? '—' }}</div></div>
                            <div class="um-detail-item"><div class="um-detail-lbl">Balance</div><div class="um-detail-val">LKR {{ number_format((float)($user->balance ?? 0), 2) }}</div></div>
                            <div class="um-detail-item"><div class="um-detail-lbl">Status</div><div class="um-detail-val">{{ ucfirst($user->status ?? 'active') }}</div></div>
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
                        <button type="button" class="um-btn um-btn-ghost" onclick="umCloseModal('umView{{ $user->id }}')">
                            Close
                        </button>

                        <button type="button"
                                class="um-btn um-btn-red"
                                onclick="umCloseModal('umView{{ $user->id }}'); umOpenModal('umEdit{{ $user->id }}')">
                            <i class="ti ti-edit"></i>
                            Edit
                        </button>
                    </div>
                </div>
            </div>

            <div class="um-modal-overlay" id="umEdit{{ $user->id }}">
                <div class="um-modal">
                    <div class="um-modal-hdr">
                        <h2 class="um-modal-title">
                            <i class="ti ti-user-edit"></i>
                            Edit User
                        </h2>

                        <button type="button" class="um-modal-close" onclick="umCloseModal('umEdit{{ $user->id }}')">
                            <i class="ti ti-x"></i>
                        </button>
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
                                    <label class="um-form-label">Email *</label>
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
                                        <option value="active" {{ ($user->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="blocked" {{ ($user->status ?? 'active') === 'blocked' ? 'selected' : '' }}>Blocked</option>
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
                                    <input type="file" name="photo" class="um-form-control" accept="image/*" style="height:auto;padding:9px;">
                                </div>

                                <div class="um-form-group">
                                    <label class="um-form-label">Aadhaar Photo</label>
                                    <input type="file" name="aadhaar_photo" class="um-form-control" accept="image/*" style="height:auto;padding:9px;">
                                </div>

                                <div class="um-form-group">
                                    <label class="um-form-label">UPI QR</label>
                                    <input type="file" name="upi_qr" class="um-form-control" accept="image/*" style="height:auto;padding:9px;">
                                </div>
                            </div>
                        </div>

                        <div class="um-form-footer">
                            <button type="button" class="um-btn um-btn-ghost" onclick="umCloseModal('umEdit{{ $user->id }}')">
                                Cancel
                            </button>

                            <button type="submit" class="um-btn um-btn-red">
                                <i class="ti ti-device-floppy"></i>
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        <div class="um-lightbox" id="umLightbox" onclick="umCloseLightbox()">
            <button type="button" class="um-lightbox-close" onclick="umCloseLightbox()">
                <i class="ti ti-x"></i>
            </button>
            <img id="umLightboxImg" src="" alt="">
        </div>

    </main>
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


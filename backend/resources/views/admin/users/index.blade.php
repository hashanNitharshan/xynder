@extends('layouts.admin', ['title' => 'Clients & Merchants'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --dark:#101518;
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

.ad-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding:24px;
}

.ad-kpis{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:14px;
    margin-bottom:18px;
}

.ad-kpi{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:20px;
    min-height:118px;
    position:relative;
    overflow:hidden;
}

.ad-kpi::after{
    content:"";
    position:absolute;
    right:-38px;
    top:-38px;
    width:115px;
    height:115px;
    border-radius:50%;
    background:rgba(232,25,44,.12);
}

.ad-kpi-icon{
    width:42px;
    height:42px;
    border-radius:50%;
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    margin-bottom:14px;
}

.ad-kpi-label{
    color:var(--muted);
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.06em;
}

.ad-kpi-value{
    font-size:28px;
    font-weight:900;
    margin-top:6px;
}

.ad-kpi-note{
    color:var(--muted2);
    font-size:12px;
    margin-top:4px;
    font-weight:700;
}

.ad-card{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    overflow:hidden;
    width:100%;
}

.ad-head{
    background:var(--panel);
    border-bottom:1px solid var(--line);
    padding:16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
}

.ad-title{
    margin:0;
    font-size:22px;
    font-weight:900;
}

.ad-tabs{
    display:flex;
    background:var(--input);
    border:1px solid var(--line);
    border-radius:5px;
    overflow:hidden;
}

.ad-tab{
    padding:11px 18px;
    color:var(--muted);
    font-size:13px;
    font-weight:900;
    text-decoration:none;
    border-right:1px solid var(--line);
}

.ad-tab:last-child{border-right:0}

.ad-tab.active,
.ad-tab:hover{
    background:rgba(232,25,44,.14);
    color:var(--red);
    box-shadow:inset 0 -3px 0 var(--red);
}

.ad-btn{
    border:0;
    border-radius:4px;
    padding:10px 14px;
    font-size:13px;
    font-weight:900;
    text-decoration:none;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    white-space:nowrap;
}

.ad-btn-red{background:var(--red);color:#fff}
.ad-btn-red:hover{background:var(--red2);color:#fff}
.ad-btn-dark{background:var(--input);color:#fff;border:1px solid var(--line)}
.ad-btn-dark:hover{border-color:var(--red);color:#fff}

.ad-filters{
    display:grid;
    grid-template-columns:2fr 1fr 1fr auto;
    gap:10px;
    padding:16px;
    border-bottom:1px solid var(--line);
}

.ad-input,
.ad-select{
    height:42px;
    width:100%;
    background:var(--input);
    border:1px solid var(--line);
    color:var(--text);
    border-radius:4px;
    padding:0 13px;
    font-weight:800;
    outline:0;
}

.ad-input:focus,
.ad-select:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.ad-table-wrap{
    width:100%;
    overflow-x:hidden;
}

.ad-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.ad-table th{
    background:var(--panel);
    color:var(--muted2);
    padding:13px 10px;
    font-size:10px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.04em;
    text-align:left;
}

.ad-table td{
    padding:16px 10px;
    border-top:1px solid var(--line);
    color:#c9ced3;
    font-size:13px;
    font-weight:800;
    vertical-align:middle;
    white-space:normal;
    word-break:break-word;
}

.ad-table tr:hover td{
    background:#30363a;
}

/* 7 columns now: User, Balance, Status, Bank/UPI, Online, Verify, Actions */
.ad-table th:nth-child(1),.ad-table td:nth-child(1){width:30%}
.ad-table th:nth-child(2),.ad-table td:nth-child(2){width:12%}
.ad-table th:nth-child(3),.ad-table td:nth-child(3){width:10%}
.ad-table th:nth-child(4),.ad-table td:nth-child(4){width:16%}
.ad-table th:nth-child(5),.ad-table td:nth-child(5){width:12%}
.ad-table th:nth-child(6),.ad-table td:nth-child(6){width:10%}
.ad-table th:nth-child(7),.ad-table td:nth-child(7){width:10%}

.ad-user{
    display:flex;
    align-items:center;
    gap:12px;
    min-width:0;
}

.ad-avatar,
.ad-avatar-empty{
    width:46px;
    height:46px;
    border-radius:50%;
    flex-shrink:0;
}

.ad-avatar{
    object-fit:cover;
    border:2px solid var(--line);
}

.ad-avatar-empty{
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    font-size:18px;
}

.ad-name{
    color:#fff;
    font-weight:900;
    margin-bottom:4px;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.ad-email{
    font-size:12px;
    color:var(--muted);
    font-weight:700;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.ad-phone{
    font-size:11px;
    color:var(--muted);
    font-weight:700;
    margin-top:3px;
    display:flex;
    align-items:center;
    gap:4px;
}

.ad-wallet{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:6px;
    background:#3a1018;
    color:#ff9aaa;
    border-radius:20px;
    padding:3px 6px 3px 9px;
    font-size:10px;
    font-weight:800;
    max-width:100%;
}

.ad-wallet span{
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.ad-copy-btn{
    background:rgba(255,255,255,.08);
    border:0;
    border-radius:50%;
    width:20px;
    height:20px;
    flex-shrink:0;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color:#ff9aaa;
    cursor:pointer;
    font-size:11px;
    padding:0;
}

.ad-copy-btn:hover{background:rgba(255,255,255,.18)}
.ad-copy-btn.ad-copied{color:var(--green)}

.ad-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:5px;
    padding:5px 9px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    white-space:normal;
}

.ad-green{background:#0d2b1e;color:var(--green)}
.ad-red{background:#3a1018;color:#ff6b7b}
.ad-yellow{background:#3b2a09;color:var(--gold)}
.ad-gray{background:var(--input);color:#d5dade;border:1px solid var(--line)}

.ad-actions{
    display:flex;
    gap:5px;
    flex-wrap:wrap;
}

.ad-actions form{margin:0}

.ad-icon{
    width:32px;
    height:32px;
    border-radius:4px;
    border:1px solid var(--line);
    background:var(--input);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    cursor:pointer;
    text-decoration:none;
}

.ad-icon:hover{border-color:var(--red)}
.ad-edit{color:#fff}
.ad-good{color:var(--green)}
.ad-bad{color:#ff6b7b}

.ad-empty{
    text-align:center;
    padding:40px;
    color:var(--muted);
    font-weight:900;
}

.ad-pagination{
    padding:16px;
    background:var(--panel);
    border-top:1px solid var(--line);
}

@media(max-width:1200px){
    .ad-table,
    .ad-table thead,
    .ad-table tbody,
    .ad-table th,
    .ad-table td,
    .ad-table tr{
        display:block;
        width:100% !important;
    }

    .ad-table thead{display:none}

    .ad-table tr{
        background:var(--panel);
        border:1px solid var(--line);
        border-radius:7px;
        margin:12px;
        padding:14px;
    }

    .ad-table td{
        border-top:0;
        padding:9px 0;
    }

    .ad-table td::before{
        content:attr(data-label);
        display:block;
        color:var(--muted2);
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
        margin-bottom:5px;
    }

    .ad-table td:first-child::before{display:none}
}

@media(max-width:768px){
    .ad-page{margin:-16px;padding:14px}
    .ad-kpis{grid-template-columns:1fr}
    .ad-filters{grid-template-columns:1fr}
    .ad-head{align-items:flex-start}
    .ad-tabs{width:100%}
    .ad-tab{flex:1;text-align:center}
}
</style>
@endpush

@section('content')

<div class="ad-page">

    <div class="ad-kpis">
        <div class="ad-kpi">
            <div class="ad-kpi-icon"><i class="ti ti-user"></i></div>
            <div class="ad-kpi-label">Total Clients</div>
            <div class="ad-kpi-value">{{ $stats['clients'] ?? 0 }}</div>
            <div class="ad-kpi-note">Registered clients</div>
        </div>

        <div class="ad-kpi">
            <div class="ad-kpi-icon"><i class="ti ti-building-store"></i></div>
            <div class="ad-kpi-label">Total Merchants</div>
            <div class="ad-kpi-value">{{ $stats['merchants'] ?? 0 }}</div>
            <div class="ad-kpi-note">Registered merchants</div>
        </div>

        <div class="ad-kpi">
            <div class="ad-kpi-icon"><i class="ti ti-circle-check"></i></div>
            <div class="ad-kpi-label">Active Users</div>
            <div class="ad-kpi-value">{{ $stats['active'] ?? 0 }}</div>
            <div class="ad-kpi-note">Active accounts</div>
        </div>

        <div class="ad-kpi">
            <div class="ad-kpi-icon"><i class="ti ti-wifi"></i></div>
            <div class="ad-kpi-label">Online Now</div>
            <div class="ad-kpi-value">{{ $stats['online'] ?? 0 }}</div>
            <div class="ad-kpi-note">Live users</div>
        </div>
    </div>

    <section class="ad-card">
        <div class="ad-head">
            <h2 class="ad-title">Clients & Merchants</h2>

            <div class="ad-tabs">
                <a href="{{ route('admin.users.index', ['type' => 'client']) }}"
                   class="ad-tab {{ $type === 'client' ? 'active' : '' }}">
                    Clients
                </a>

                <a href="{{ route('admin.users.index', ['type' => 'merchant']) }}"
                   class="ad-tab {{ $type === 'merchant' ? 'active' : '' }}">
                    Merchants
                </a>
            </div>

            <a href="{{ route('admin.users.create') }}" class="ad-btn ad-btn-red">
                <i class="ti ti-user-plus"></i>
                Add User
            </a>
        </div>

        <form id="adFilterForm" method="GET" action="{{ route('admin.users.index') }}" class="ad-filters">
            <input type="hidden" name="type" value="{{ $type }}">

            <input id="adSearch"
                   class="ad-input"
                   type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search name, email, phone, wallet ID">

            <select name="status" class="ad-select ad-auto-filter">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
            </select>

            <select name="online" class="ad-select ad-auto-filter">
                <option value="">All Online</option>
                <option value="1" {{ request('online') === '1' ? 'selected' : '' }}>Online</option>
                <option value="0" {{ request('online') === '0' ? 'selected' : '' }}>Offline</option>
            </select>

            <a href="{{ route('admin.users.index', ['type' => $type]) }}" class="ad-btn ad-btn-dark">
                <i class="ti ti-refresh"></i>
                Reset
            </a>
        </form>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Bank / UPI</th>
                        <th>Online</th>
                        <th>Verify</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td data-label="User">
                            <div class="ad-user">
                                @if($user->photo)
                                    <img src="{{ asset('storage/'.$user->photo) }}"
                                         class="ad-avatar"
                                         alt="{{ $user->name }}">
                                @else
                                    <div class="ad-avatar-empty">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div style="min-width:0;width:100%;">
                                    <div class="ad-name">{{ $user->name }}</div>
                                    <div class="ad-email">{{ $user->email }}</div>

                                    <div class="ad-wallet">
                                        <span>Wallet ID: {{ $user->wallet_id ?? 'N/A' }}</span>
                                        @if($user->wallet_id)
                                            <button type="button"
                                                    class="ad-copy-btn"
                                                    title="Copy wallet ID"
                                                    data-copy="{{ $user->wallet_id }}">
                                                <i class="ti ti-copy"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="ad-phone">
                                        <i class="ti ti-phone"></i>
                                        {{ $user->phone ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td data-label="Balance">
                            LKR {{ number_format((float)($user->balance ?? 0), 2) }}
                        </td>

                        <td data-label="Status">
                            @if(($user->status ?? 'active') === 'active')
                                <span class="ad-badge ad-green">Active</span>
                            @else
                                <span class="ad-badge ad-red">Blocked</span>
                            @endif
                        </td>

                        <td data-label="Bank / UPI">
                            <strong>{{ $user->bank_name ?? '—' }}</strong>

                            @if($user->account_number)
                                <br>
                                <span style="color:var(--muted);">
                                    Acc: {{ $user->account_number }}
                                </span>
                            @endif

                            @if($user->upi_id)
                                <br>
                                <span style="color:var(--muted);">
                                    UPI: {{ $user->upi_id }}
                                </span>
                            @endif
                        </td>

                        <td data-label="Online">
                            @if($user->is_online)
                                <span class="ad-badge ad-green">
                                    <i class="ti ti-circle-filled"></i>
                                    Online
                                </span>
                            @else
                                <span class="ad-badge ad-gray">
                                    <i class="ti ti-circle"></i>
                                    Offline
                                </span>
                            @endif

                            @if($user->last_seen_at)
                                <div style="font-size:10px;color:var(--muted);margin-top:5px;">
                                    {{ $user->last_seen_at->format('d M, h:i A') }}
                                </div>
                            @endif
                        </td>

                        <td data-label="Verify">
                            @if($user->is_verified)
                                <span class="ad-badge ad-green">Verified</span>
                            @else
                                <span class="ad-badge ad-yellow">Not Verified</span>
                            @endif
                        </td>

                        <td data-label="Actions">
                            <div class="ad-actions">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="ad-icon ad-edit"
                                   title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>

                                <form method="POST" action="{{ route('admin.users.toggle-verification', $user) }}">
                                    @csrf
                                    <button type="submit"
                                            class="ad-icon {{ $user->is_verified ? 'ad-bad' : 'ad-good' }}"
                                            title="{{ $user->is_verified ? 'Unverify' : 'Verify' }}"
                                            onclick="return confirm('{{ $user->is_verified ? 'Mark as unverified?' : 'Verify this user?' }}')">
                                        <i class="ti {{ $user->is_verified ? 'ti-x' : 'ti-check' }}"></i>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf
                                    <button type="submit"
                                            class="ad-icon {{ ($user->status ?? 'active') === 'active' ? 'ad-bad' : 'ad-good' }}"
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

                                    <button type="submit" class="ad-icon ad-bad" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="ad-empty">No {{ $type }}s found.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="ad-pagination">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </section>
</div>

<script>
document.querySelectorAll('.ad-auto-filter').forEach(function(el){
    el.addEventListener('change',function(){
        document.getElementById('adFilterForm').submit();
    });
});

let adSearchTimer=null;
const adSearch=document.getElementById('adSearch');

if(adSearch){
    adSearch.addEventListener('keyup',function(){
        clearTimeout(adSearchTimer);

        adSearchTimer=setTimeout(function(){
            document.getElementById('adFilterForm').submit();
        },500);
    });
}

document.querySelectorAll('.ad-copy-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
        const value = btn.getAttribute('data-copy');

        navigator.clipboard.writeText(value).then(function(){
            const icon = btn.querySelector('i');
            btn.classList.add('ad-copied');
            icon.className = 'ti ti-check';

            setTimeout(function(){
                btn.classList.remove('ad-copied');
                icon.className = 'ti ti-copy';
            }, 1200);
        });
    });
});
</script>

@endsection
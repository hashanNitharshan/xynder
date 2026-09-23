@extends('layouts.admin', ['title' => 'Clients & Merchants'])

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
    --green:#0ECB81;
    --red:#EF4444;
    --text:#FFFFFF;
    --muted:#848E9C;
    --muted2:#5E6673;
}

*{
    box-sizing:border-box;
}

.ad-page{
    margin:-24px;
    min-height:100vh;
    padding-bottom:60px;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
}

.ad-hero{
    min-height:185px;
    position:relative;
    overflow:hidden;
    background:
        radial-gradient(circle at 88% 15%,rgba(240,185,11,.12),transparent 31%),
        var(--surface);
}

.ad-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:
        linear-gradient(
            120deg,
            transparent 20%,
            rgba(255,255,255,.18) 21%,
            transparent 22%
        );
    background-size:260px 260px;
}

.ad-wrap{
    position:relative;
    z-index:5;
    margin:-82px 45px 0;
}

.ad-kpis{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:18px;
    margin-bottom:28px;
}

.ad-kpi{
    min-height:128px;
    padding:20px;
    position:relative;
    overflow:hidden;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:6px;
}

.ad-kpi::after{
    content:"";
    position:absolute;
    right:-38px;
    top:-38px;
    width:115px;
    height:115px;
    border-radius:50%;
    background:rgba(240,185,11,.10);
}

.ad-kpi-icon{
    width:42px;
    height:42px;
    margin-bottom:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    color:var(--yellow);
    background:rgba(240,185,11,.12);
    font-size:22px;
}

.ad-kpi-label{
    color:var(--muted);
    font-size:11px;
    font-weight:900;
    letter-spacing:.06em;
    text-transform:uppercase;
}

.ad-kpi-value{
    margin-top:6px;
    color:#FFFFFF;
    font-size:22px;
    font-weight:900;
}

.ad-kpi-note{
    margin-top:4px;
    color:var(--muted2);
    font-size:11px;
    font-weight:700;
}

.ad-kpi-note strong{
    color:var(--green);
}

.ad-card{
    width:100%;
    overflow:hidden;
    background:var(--surface);
    border:1.5px solid var(--yellow);
    border-radius:7px;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.ad-head{
    padding:18px 22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
    background:var(--surface-alt);
    border-bottom:1px solid var(--border);
}

.ad-title{
    margin:0;
    color:#FFFFFF;
    font-size:15px;
    font-weight:800;
}

.ad-tabs{
    display:flex;
    overflow:hidden;
    background:var(--bg);
    border:1px solid var(--border);
    border-radius:4px;
}

.ad-tab{
    min-width:105px;
    padding:10px 16px;
    text-align:center;
    color:var(--muted);
    border-right:1px solid var(--border);
    font-size:12px;
    font-weight:900;
    text-decoration:none;
}

.ad-tab:last-child{
    border-right:0;
}

.ad-tab.active,
.ad-tab:hover{
    color:#111111;
    background:var(--yellow);
}

.ad-btn{
    min-height:40px;
    padding:9px 14px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    border-radius:4px;
    font-size:12px;
    font-weight:900;
    text-decoration:none;
    cursor:pointer;
    white-space:nowrap;
}

.ad-btn-primary{
    color:#111111;
    background:var(--yellow);
    border:1px solid var(--yellow);
}

.ad-btn-primary:hover{
    color:#111111;
    background:var(--yellow-dark);
    border-color:var(--yellow-dark);
}

.ad-btn-dark{
    color:#FFFFFF;
    background:var(--surface-alt);
    border:1px solid var(--border);
}

.ad-btn-dark:hover{
    color:var(--yellow);
    border-color:var(--yellow);
}

.ad-filters{
    padding:16px 20px;
    display:grid;
    grid-template-columns:2fr 1fr 1fr auto;
    gap:10px;
    background:var(--surface);
    border-bottom:1px solid var(--border);
}

.ad-input,
.ad-select{
    width:100%;
    height:42px;
    padding:0 13px;
    outline:0;
    color:var(--text);
    background:var(--bg);
    border:1px solid var(--border);
    border-radius:4px;
    font-family:inherit;
    font-size:12px;
    font-weight:800;
}

.ad-input:focus,
.ad-select:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(240,185,11,.12);
}

.ad-table-wrap{
    width:100%;
    overflow-x:auto;
}

.ad-table{
    width:100%;
    min-width:1180px;
    border-collapse:collapse;
    table-layout:fixed;
}

.ad-table th{
    padding:14px 12px;
    text-align:left;
    color:var(--muted2);
    background:var(--surface-alt);
    font-size:10px;
    font-weight:900;
    letter-spacing:.05em;
    text-transform:uppercase;
}

.ad-table td{
    padding:15px 12px;
    vertical-align:middle;
    color:#C9CED3;
    border-top:1px solid var(--border);
    font-size:12px;
    font-weight:700;
    word-break:break-word;
}

.ad-table tr:hover td{
    background:var(--surface-alt);
}

.ad-table th:nth-child(1),
.ad-table td:nth-child(1){
    width:26%;
}

.ad-table th:nth-child(2),
.ad-table td:nth-child(2){
    width:10%;
}

.ad-table th:nth-child(3),
.ad-table td:nth-child(3){
    width:10%;
}

.ad-table th:nth-child(4),
.ad-table td:nth-child(4){
    width:18%;
}

.ad-table th:nth-child(5),
.ad-table td:nth-child(5){
    width:11%;
}

.ad-table th:nth-child(6),
.ad-table td:nth-child(6){
    width:11%;
}

.ad-table th:nth-child(7),
.ad-table td:nth-child(7){
    width:14%;
}

.ad-user{
    display:flex;
    align-items:center;
    gap:12px;
    min-width:0;
}

.ad-avatar-box{
    position:relative;
    flex-shrink:0;
}

.ad-avatar,
.ad-avatar-empty{
    width:44px;
    height:44px;
    flex-shrink:0;
    border-radius:50%;
}

.ad-avatar{
    object-fit:cover;
    border:2px solid var(--border);
}

.ad-avatar-empty{
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--yellow);
    background:rgba(240,185,11,.12);
    border:1px solid rgba(240,185,11,.35);
    font-size:17px;
    font-weight:900;
}

.ad-avatar-dot{
    position:absolute;
    right:0;
    bottom:0;
    width:12px;
    height:12px;
    border-radius:50%;
    background:var(--muted2);
    border:2px solid var(--surface);
}

.ad-avatar-dot.on{
    background:var(--green);
}

.ad-name{
    margin-bottom:4px;
    display:flex;
    align-items:center;
    gap:6px;
    overflow:hidden;
    color:#FFFFFF;
    font-weight:900;
    white-space:nowrap;
}

.ad-name-text{
    overflow:hidden;
    text-overflow:ellipsis;
}

.ad-email{
    overflow:hidden;
    color:var(--muted);
    font-size:11px;
    font-weight:700;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.ad-phone{
    margin-top:4px;
    display:flex;
    align-items:center;
    gap:4px;
    color:var(--muted);
    font-size:10px;
    font-weight:700;
}

.ad-wallet{
    max-width:100%;
    margin-top:6px;
    padding:3px 5px 3px 8px;
    display:inline-flex;
    align-items:center;
    gap:6px;
    color:var(--yellow);
    background:rgba(240,185,11,.10);
    border:1px solid rgba(240,185,11,.25);
    border-radius:20px;
    font-size:10px;
    font-weight:800;
}

.ad-wallet span{
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.ad-copy-btn{
    width:20px;
    height:20px;
    padding:0;
    flex-shrink:0;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color:var(--yellow);
    background:rgba(255,255,255,.08);
    border:0;
    border-radius:50%;
    cursor:pointer;
}

.ad-copy-btn:hover{
    background:rgba(255,255,255,.18);
}

.ad-copy-btn.ad-copied{
    color:var(--green);
}

.ad-badge{
    padding:5px 8px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:5px;
    border-radius:20px;
    font-size:10px;
    font-weight:900;
    line-height:1.2;
    text-transform:uppercase;
}

.ad-green{
    color:var(--green);
    background:#0D2B1E;
}

.ad-red{
    color:#FF9B9B;
    background:rgba(239,68,68,.12);
}

.ad-yellow{
    color:var(--gold);
    background:#3B2A09;
}

.ad-gray{
    color:#D5DADE;
    background:var(--surface-alt);
    border:1px solid var(--border);
}

.ad-bank-line{
    margin-top:4px;
    color:var(--muted);
    font-size:11px;
}

.ad-seen{
    margin-top:5px;
    color:var(--muted);
    font-size:10px;
}

.ad-dash{
    color:var(--muted2);
}

.ad-actions{
    display:flex;
    align-items:center;
    gap:5px;
    flex-wrap:wrap;
}

.ad-actions form{
    margin:0;
}

.ad-icon{
    width:31px;
    height:31px;
    padding:0;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color:#FFFFFF;
    background:var(--surface-alt);
    border:1px solid var(--border);
    border-radius:4px;
    cursor:pointer;
    text-decoration:none;
}

.ad-icon:hover{
    border-color:var(--yellow);
}

.ad-good{
    color:var(--green);
}

.ad-bad{
    color:#FF9B9B;
}

.ad-empty{
    padding:45px;
    text-align:center;
    color:var(--muted);
    font-weight:900;
}

.ad-pagination{
    padding:16px 22px;
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:14px;
    flex-wrap:wrap;
    background:var(--surface-alt);
    border-top:1px solid var(--border);
}

.ad-pagination nav{
    width:auto;
    max-width:100%;
}

.ad-pagination nav > div{
    display:flex!important;
    align-items:center!important;
    gap:8px!important;
    flex-wrap:wrap!important;
}

.ad-pagination nav p{
    margin:0!important;
    color:var(--muted)!important;
    font-size:12px!important;
    font-weight:800!important;
}

.ad-pagination nav a,
.ad-pagination nav span{
    min-width:34px!important;
    height:34px!important;
    padding:0 10px!important;
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    color:#FFFFFF!important;
    background:var(--bg)!important;
    border:1px solid var(--border)!important;
    border-radius:5px!important;
    box-shadow:none!important;
    font-size:12px!important;
    font-weight:900!important;
    line-height:1!important;
    text-decoration:none!important;
}

.ad-pagination nav span[aria-current="page"],
.ad-pagination nav span[aria-current="page"] span{
    color:#111111!important;
    background:var(--yellow)!important;
    border-color:var(--yellow)!important;
}

.ad-pagination nav a:hover{
    color:var(--yellow)!important;
    border-color:var(--yellow)!important;
}

.ad-pagination nav svg{
    width:16px!important;
    height:16px!important;
    max-width:16px!important;
    max-height:16px!important;
    display:block!important;
    stroke-width:3!important;
}

.ad-pagination nav .hidden{
    display:none!important;
}

.ad-pagination nav div:first-child{
    display:none!important;
}

.ad-pagination nav div:last-child{
    display:flex!important;
}

@media(max-width:1250px){
    .ad-wrap{
        margin:-82px 24px 0;
    }
}

@media(max-width:1000px){
    .ad-kpis{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }

    .ad-filters{
        grid-template-columns:1fr 1fr;
    }
}

@media(max-width:768px){
    .ad-page{
        margin:-16px;
    }

    .ad-hero{
        min-height:160px;
    }

    .ad-wrap{
        margin:-70px 18px 0;
    }

    .ad-kpis,
    .ad-filters{
        grid-template-columns:1fr;
    }

    .ad-head{
        align-items:flex-start;
    }

    .ad-tabs{
        width:100%;
    }

    .ad-tab{
        flex:1;
        min-width:0;
    }

    .ad-table,
    .ad-table thead,
    .ad-table tbody,
    .ad-table th,
    .ad-table td,
    .ad-table tr{
        display:block;
        width:100%!important;
    }

    .ad-table{
        min-width:0;
    }

    .ad-table thead{
        display:none;
    }

    .ad-table tr{
        margin:12px;
        padding:14px;
        background:var(--surface-alt);
        border:1px solid var(--border);
        border-radius:7px;
    }

    .ad-table td{
        padding:9px 0;
        border-top:0;
    }

    .ad-table td::before{
        content:attr(data-label);
        display:block;
        margin-bottom:5px;
        color:var(--muted2);
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
    }

    .ad-table td:first-child::before{
        display:none;
    }

    .ad-pagination{
        justify-content:flex-start;
    }
}
</style>
@endpush

@section('content')
<div class="ad-page">
    <section class="ad-hero"></section>

    <main class="ad-wrap">
        <section class="ad-kpis">
            <div class="ad-kpi">
                <div class="ad-kpi-icon">
                    <i class="ti ti-user"></i>
                </div>

                <div class="ad-kpi-label">
                    Total Clients
                </div>

                <div class="ad-kpi-value">
                    {{ $stats['clients'] ?? 0 }}
                </div>

                <div class="ad-kpi-note">
                    Registered clients
                </div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon">
                    <i class="ti ti-building-store"></i>
                </div>

                <div class="ad-kpi-label">
                    Total Merchants
                </div>

                <div class="ad-kpi-value">
                    {{ $stats['merchants'] ?? 0 }}
                </div>

                <div class="ad-kpi-note">
                    Registered merchants
                </div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon">
                    <i class="ti ti-circle-check"></i>
                </div>

                <div class="ad-kpi-label">
                    Active Users
                </div>

                <div class="ad-kpi-value">
                    {{ $stats['active'] ?? 0 }}
                </div>

                <div class="ad-kpi-note">
                    Active accounts
                </div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon">
                    <i class="ti ti-wifi"></i>
                </div>

                <div class="ad-kpi-label">
                    Online Now
                </div>

                <div class="ad-kpi-value">
                    {{ $stats['online'] ?? 0 }}
                </div>

                <div class="ad-kpi-note">
                    Across clients &amp; merchants
                </div>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-head">
                <h2 class="ad-title">
                    Clients & Merchants
                </h2>

                <div class="ad-tabs">
                    <a
                        href="{{ route('admin.users.index', ['type' => 'client']) }}"
                        class="ad-tab {{ $type === 'client' ? 'active' : '' }}"
                    >
                        Clients
                    </a>

                    <a
                        href="{{ route('admin.users.index', ['type' => 'merchant']) }}"
                        class="ad-tab {{ $type === 'merchant' ? 'active' : '' }}"
                    >
                        Merchants
                    </a>
                </div>

                <a
                    href="{{ route('admin.users.create') }}"
                    class="ad-btn ad-btn-primary"
                >
                    <i class="ti ti-user-plus"></i>
                    Add User
                </a>
            </div>

            <form
                id="adFilterForm"
                method="GET"
                action="{{ route('admin.users.index') }}"
                class="ad-filters"
            >
                <input
                    type="hidden"
                    name="type"
                    value="{{ $type }}"
                >

                <input
                    id="adSearch"
                    class="ad-input"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name, email, phone, wallet ID, bank, UPI"
                >

                <select
                    name="status"
                    class="ad-select ad-auto-filter"
                >
                    <option value="">
                        All Status
                    </option>

                    <option
                        value="active"
                        {{ request('status') === 'active' ? 'selected' : '' }}
                    >
                        Active
                    </option>

                    <option
                        value="blocked"
                        {{ request('status') === 'blocked' ? 'selected' : '' }}
                    >
                        Blocked
                    </option>
                </select>

                <select
                    name="online"
                    class="ad-select ad-auto-filter"
                >
                    <option value="">
                        All Online
                    </option>

                    <option
                        value="1"
                        {{ request('online') === '1' ? 'selected' : '' }}
                    >
                        Online
                    </option>

                    <option
                        value="0"
                        {{ request('online') === '0' ? 'selected' : '' }}
                    >
                        Offline
                    </option>
                </select>

                <a
                    href="{{ route('admin.users.index', ['type' => $type]) }}"
                    class="ad-btn ad-btn-dark"
                >
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
                            <th>Default Bank / UPI</th>
                            <th>Online</th>
                            <th>Verify</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($users as $user)
                        @php
                            $defaultBank = $user->bankAccounts
                                ? $user->bankAccounts->firstWhere('is_default', true)
                                : null;

                            $fallbackBank = $user->bankAccounts && $user->bankAccounts->count()
                                ? $user->bankAccounts->first()
                                : null;

                            $bank = $defaultBank ?: $fallbackBank;
                        @endphp

                        <tr>
                            <td data-label="User">
                                <div class="ad-user">
                                    <div class="ad-avatar-box">
                                        @if($user->photo)
                                            <img
                                                src="{{ asset('storage/'.$user->photo) }}"
                                                class="ad-avatar"
                                                alt="{{ $user->name }}"
                                            >
                                        @else
                                            <div class="ad-avatar-empty">
                                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif

                                        <span class="ad-avatar-dot {{ $user->is_online ? 'on' : '' }}"></span>
                                    </div>

                                    <div style="min-width:0;width:100%;">
                                        <div class="ad-name">
                                            <span class="ad-name-text">{{ $user->name }}</span>
                                        </div>

                                        <div class="ad-email">
                                            {{ $user->email }}
                                        </div>

                                        <div class="ad-wallet">
                                            <span>
                                                Wallet ID: {{ $user->wallet_id ?? 'N/A' }}
                                            </span>

                                            @if($user->wallet_id)
                                                <button
                                                    type="button"
                                                    class="ad-copy-btn"
                                                    title="Copy wallet ID"
                                                    data-copy="{{ $user->wallet_id }}"
                                                >
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
                                <span style="color:#FFFFFF;font-weight:900;">
                                    ${{ number_format((float)($user->balance ?? 0), 2) }}
                                </span>
                            </td>

                            <td data-label="Status">
                                @if(($user->status ?? 'active') === 'active')
                                    <span class="ad-badge ad-green">
                                        Active
                                    </span>
                                @else
                                    <span class="ad-badge ad-red">
                                        Blocked
                                    </span>
                                @endif
                            </td>

                            <td data-label="Default Bank / UPI">
                                @if($bank)
                                    <strong style="color:#FFFFFF;">
                                        {{ $bank->bank_name ?? '—' }}
                                    </strong>

                                    @if($bank->is_default)
                                        <br>

                                        <span
                                            class="ad-badge ad-yellow"
                                            style="margin-top:5px;"
                                        >
                                            Default
                                        </span>
                                    @endif

                                    @if($bank->account_number)
                                        <div class="ad-bank-line">
                                            Acc: {{ $bank->account_number }}
                                        </div>
                                    @endif

                                    @if($bank->branch)
                                        <div class="ad-bank-line">
                                            Branch: {{ $bank->branch }}
                                        </div>
                                    @endif

                                    @if($bank->account_type)
                                        <div class="ad-bank-line">
                                            Type: {{ $bank->account_type }}
                                        </div>
                                    @endif

                                    @if($bank->ifsc)
                                        <div class="ad-bank-line">
                                            IFSC: {{ $bank->ifsc }}
                                        </div>
                                    @endif
                                @elseif($user->bank_name || $user->account_number)
                                    <strong style="color:#FFFFFF;">
                                        {{ $user->bank_name ?? '—' }}
                                    </strong>

                                    <br>

                                    <span
                                        class="ad-badge ad-gray"
                                        style="margin-top:5px;"
                                    >
                                        Legacy
                                    </span>

                                    @if($user->account_number)
                                        <div class="ad-bank-line">
                                            Acc: {{ $user->account_number }}
                                        </div>
                                    @endif

                                    @if($user->branch)
                                        <div class="ad-bank-line">
                                            Branch: {{ $user->branch }}
                                        </div>
                                    @endif

                                    @if($user->ifsc)
                                        <div class="ad-bank-line">
                                            IFSC: {{ $user->ifsc }}
                                        </div>
                                    @endif
                                @else
                                    <span style="color:var(--muted);">
                                        No bank added
                                    </span>
                                @endif

                                @if($user->upi_id)
                                    <div class="ad-bank-line">
                                        UPI: {{ $user->upi_id }}
                                    </div>
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
                                    <div class="ad-seen">
                                        {{ $user->last_seen_at->format('d M, h:i A') }}
                                    </div>
                                @endif
                            </td>

                            <td data-label="Verify">
                                @if($user->is_verified)
                                    <span class="ad-badge ad-green">
                                        Verified
                                    </span>
                                @else
                                    <span class="ad-badge ad-yellow">
                                        Not Verified
                                    </span>
                                @endif
                            </td>

                            <td data-label="Actions">
                                <div class="ad-actions">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="ad-icon"
                                        title="Edit"
                                    >
                                        <i class="ti ti-edit"></i>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.toggle-online', $user) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="ad-icon {{ $user->is_online ? 'ad-bad' : 'ad-good' }}"
                                            title="{{ $user->is_online ? 'Set offline' : 'Set online' }}"
                                            aria-label="{{ $user->is_online ? 'Set offline' : 'Set online' }}"
                                            onclick="return confirm('{{ $user->is_online ? 'Set this user offline?' : 'Set this user online?' }}')"
                                        >
                                            <i class="ti {{ $user->is_online ? 'ti-wifi-off' : 'ti-wifi' }}"></i>
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.toggle-verification', $user) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="ad-icon {{ $user->is_verified ? 'ad-bad' : 'ad-good' }}"
                                            title="{{ $user->is_verified ? 'Unverify' : 'Verify' }}"
                                            onclick="return confirm('{{ $user->is_verified ? 'Mark as unverified?' : 'Verify this user?' }}')"
                                        >
                                            <i class="ti {{ $user->is_verified ? 'ti-x' : 'ti-check' }}"></i>
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.toggle-status', $user) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="ad-icon {{ ($user->status ?? 'active') === 'active' ? 'ad-bad' : 'ad-good' }}"
                                            title="{{ ($user->status ?? 'active') === 'active' ? 'Block' : 'Activate' }}"
                                            onclick="return confirm('{{ ($user->status ?? 'active') === 'active' ? 'Block this user?' : 'Activate this user?' }}')"
                                        >
                                            <i class="ti {{ ($user->status ?? 'active') === 'active' ? 'ti-ban' : 'ti-circle-check' }}"></i>
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.destroy', $user) }}"
                                        onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="ad-icon ad-bad"
                                            title="Delete"
                                        >
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="ad-empty">
                                    No {{ $type }}s found.
                                </div>
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
    </main>
</div>

<script>
document.querySelectorAll('.ad-auto-filter').forEach(function(element){
    element.addEventListener('change', function(){
        document.getElementById('adFilterForm').submit();
    });
});

let adSearchTimer = null;
const adSearch = document.getElementById('adSearch');

if(adSearch){
    adSearch.addEventListener('keyup', function(){
        clearTimeout(adSearchTimer);

        adSearchTimer = setTimeout(function(){
            document.getElementById('adFilterForm').submit();
        }, 500);
    });
}

document.querySelectorAll('.ad-copy-btn').forEach(function(button){
    button.addEventListener('click', function(){
        const value = button.getAttribute('data-copy');

        if(!navigator.clipboard){
            return;
        }

        navigator.clipboard.writeText(value).then(function(){
            const icon = button.querySelector('i');

            button.classList.add('ad-copied');
            icon.className = 'ti ti-check';

            setTimeout(function(){
                button.classList.remove('ad-copied');
                icon.className = 'ti ti-copy';
            }, 1200);
        });
    });
});
</script>
@endsection
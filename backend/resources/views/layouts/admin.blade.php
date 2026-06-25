<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Xynder Wallet' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

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
            --shadow:0 18px 40px rgba(0,0,0,.28);
        }

        html,body{height:100%}

        body{
            background:var(--dark);
            color:var(--text);
            font-family:Inter,'Segoe UI',Arial,sans-serif;
            font-size:15px;
            overflow-x:hidden;
        }

        a{text-decoration:none;color:inherit}
        button,input,select,textarea{font-family:inherit}

        .shell{
            display:grid;
            grid-template-columns:280px 1fr;
            min-height:100vh;
        }

        .sidebar{
            height:100vh;
            position:sticky;
            top:0;
            background:#161b1f;
            border-right:1px solid var(--line);
            display:flex;
            flex-direction:column;
            overflow-y:auto;
            overflow-x:hidden;
            scrollbar-width:none;
            -ms-overflow-style:none;
        }

        .sidebar::-webkit-scrollbar{display:none}

        .brand{
            min-height:76px;
            display:flex;
            align-items:center;
            gap:12px;
            padding:0 22px;
            border-bottom:1px solid var(--line);
            background:#1f2428;
            flex-shrink:0;
        }

        .brand-icon{
            width:42px;
            height:42px;
            border-radius:50%;
            background:var(--red);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:22px;
            flex-shrink:0;
            box-shadow:0 0 0 5px rgba(232,25,44,.12);
        }

        .brand-name{
            font-size:19px;
            font-weight:900;
            letter-spacing:-.3px;
            color:#fff;
            line-height:1.1;
        }

        .brand-name span{color:var(--red)}

        .brand-sub{
            font-size:11px;
            color:#9fa5aa;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.08em;
            margin-top:3px;
        }

        .nav{
            padding:16px 12px 24px;
            flex:1;
        }

        .nav-section{
            margin-top:24px;
        }

        .nav-label{
            font-size:11px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:.08em;
            color:#747b82;
            padding:0 12px;
            margin-bottom:8px;
            display:block;
        }

        .nav-item{
            display:flex;
            align-items:center;
            gap:12px;
            width:100%;
            min-height:46px;
            padding:12px 13px;
            border-radius:6px;
            color:#aeb4ba;
            font-size:14px;
            font-weight:800;
            margin-bottom:5px;
            cursor:pointer;
            border:1px solid transparent;
            background:transparent;
            text-align:left;
            transition:.15s;
        }

        .nav-item i{
            width:22px;
            text-align:center;
            font-size:20px;
            flex-shrink:0;
            color:#747b82;
            transition:.15s;
        }

        .nav-item:hover{
            background:#24292d;
            color:#fff;
            border-color:#3b4248;
        }

        .nav-item:hover i{
            color:var(--red);
        }

        .nav-item.active{
            background:#3a1018;
            color:#fff;
            border-color:rgba(232,25,44,.55);
            box-shadow:inset 3px 0 0 var(--red);
        }

        .nav-item.active i{
            color:var(--red);
        }

        .nav-logout:hover{
            background:#3a1018;
            color:#ff6b7b;
            border-color:rgba(232,25,44,.45);
        }

        .sidebar-footer{
            padding:16px 18px;
            border-top:1px solid var(--line);
            background:#1f2428;
            display:flex;
            align-items:center;
            gap:12px;
            flex-shrink:0;
        }

        .sf-avatar{
            width:42px;
            height:42px;
            border-radius:50%;
            background:var(--red);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:15px;
            font-weight:900;
            color:#fff;
            flex-shrink:0;
            overflow:hidden;
        }

        .sf-avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .sf-name{
            font-size:14px;
            font-weight:900;
            color:#fff;
            line-height:1.2;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .sf-role{
            font-size:11px;
            color:#9fa5aa;
            margin-top:3px;
            font-weight:800;
            text-transform:uppercase;
        }

        .main{
            min-width:0;
            display:flex;
            flex-direction:column;
        }

        .topbar{
            height:76px;
            background:#1f2428;
            border-bottom:1px solid var(--line);
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 24px;
            position:sticky;
            top:0;
            z-index:20;
        }

        .topbar-left{
            display:flex;
            align-items:center;
            gap:14px;
            flex:1;
            min-width:0;
        }

        .mobile-toggle{
            display:none;
            width:42px;
            height:42px;
            align-items:center;
            justify-content:center;
            background:#24292d;
            border:1px solid var(--line);
            border-radius:6px;
            color:#fff;
            cursor:pointer;
            font-size:22px;
        }

        .search-box{
            display:flex;
            align-items:center;
            gap:10px;
            height:46px;
            background:#101518;
            border:1px solid var(--line);
            border-radius:6px;
            padding:0 15px;
            width:360px;
            max-width:100%;
            transition:.15s;
        }

        .search-box:focus-within{
            border-color:var(--red);
            box-shadow:0 0 0 3px rgba(232,25,44,.12);
        }

        .search-box i{
            color:#747b82;
            font-size:18px;
            flex-shrink:0;
        }

        .search-box input{
            background:transparent;
            border:0;
            outline:0;
            color:#fff;
            font-size:14px;
            font-weight:700;
            width:100%;
        }

        .search-box input::placeholder{color:#747b82}

        .topbar-right{
            display:flex;
            align-items:center;
            gap:7px;
        }

        .tb-icon{
            position:relative;
            width:42px;
            height:42px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:6px;
            color:#aeb4ba;
            font-size:21px;
            cursor:pointer;
            border:1px solid transparent;
            transition:.15s;
        }

        .tb-icon:hover{
            background:#24292d;
            border-color:var(--line);
            color:#fff;
        }

        .tb-badge{
            position:absolute;
            top:3px;
            right:3px;
            min-width:18px;
            height:18px;
            background:var(--red);
            color:#fff;
            font-size:10px;
            font-weight:900;
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:0 5px;
            border:2px solid #1f2428;
        }

        .tb-divider{
            width:1px;
            height:32px;
            background:var(--line);
            margin:0 7px;
        }

        .profile-wrap{
            position:relative;
        }

        .profile-btn{
            display:flex;
            align-items:center;
            gap:10px;
            min-height:48px;
            padding:0 13px 0 10px;
            background:#24292d;
            border:1px solid var(--line);
            border-radius:6px;
            color:#fff;
            cursor:pointer;
            transition:.15s;
        }

        .profile-btn:hover{
            border-color:rgba(232,25,44,.5);
            background:#30363a;
        }

        .p-avatar{
            width:34px;
            height:34px;
            border-radius:50%;
            background:var(--red);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:13px;
            font-weight:900;
            color:#fff;
            overflow:hidden;
            flex-shrink:0;
        }

        .p-avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .p-name{
            font-size:13px;
            font-weight:900;
            color:#fff;
            line-height:1.2;
            max-width:130px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .p-role{
            font-size:11px;
            color:#9fa5aa;
            font-weight:800;
            text-transform:uppercase;
        }

        .p-chevron{
            font-size:16px;
            color:#9fa5aa;
            margin-left:2px;
        }

        .profile-menu{
            position:absolute;
            right:0;
            top:58px;
            width:270px;
            background:#24292d;
            border:1px solid #3b4248;
            border-radius:7px;
            box-shadow:0 20px 50px rgba(0,0,0,.55);
            display:none;
            overflow:hidden;
            z-index:99;
        }

        .profile-menu.show{display:block}

        .pm-head{
            padding:16px;
            border-bottom:1px solid var(--line);
            display:flex;
            gap:12px;
            align-items:center;
            background:#1f2428;
        }

        .pm-head-avatar{
            width:44px;
            height:44px;
            border-radius:50%;
            background:var(--red);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:16px;
            font-weight:900;
            color:#fff;
            overflow:hidden;
            flex-shrink:0;
        }

        .pm-head-avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .pm-hname{
            font-weight:900;
            color:#fff;
            font-size:14px;
        }

        .pm-hemail{
            font-size:12px;
            color:#9fa5aa;
            margin-top:3px;
            word-break:break-all;
            font-weight:700;
        }

        .pm-items{
            padding:7px;
        }

        .pm-link,
        .pm-logout{
            width:100%;
            display:flex;
            align-items:center;
            gap:10px;
            padding:11px 12px;
            background:transparent;
            border:0;
            color:#aeb4ba;
            font-size:13px;
            font-weight:800;
            text-align:left;
            cursor:pointer;
            border-radius:5px;
            transition:.13s;
        }

        .pm-link:hover{
            background:#30363a;
            color:#fff;
        }

        .pm-link i,
        .pm-logout i{
            font-size:17px;
            color:var(--red);
        }

        .pm-sep{
            height:1px;
            background:var(--line);
            margin:5px 6px;
        }

        .pm-logout:hover{
            background:#3a1018;
            color:#ff6b7b;
        }

        .content{
            flex:1;
            padding:28px;
            padding-bottom:68px;
            min-width:0;
        }

        .alert{
            display:flex;
            align-items:flex-start;
            gap:10px;
            padding:14px 16px;
            border-radius:4px;
            font-size:14px;
            font-weight:800;
            margin-bottom:16px;
        }

        .alert i{
            font-size:18px;
            flex-shrink:0;
            margin-top:1px;
        }

        .alert-success{
            background:#0d2b1e;
            border:1px solid #1a4a35;
            color:var(--green);
        }

        .alert-error{
            background:#3a1018;
            border:1px solid #71313a;
            color:#ff6b7b;
        }

        .footer{
            position:fixed;
            left:280px;
            right:0;
            bottom:0;
            height:42px;
            background:#1f2428;
            border-top:1px solid var(--line);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#9fa5aa;
            font-size:12px;
            font-weight:700;
            z-index:30;
        }

        @media(max-width:920px){
            .shell{display:block}

            .sidebar{
                position:fixed;
                left:-290px;
                width:280px;
                z-index:100;
                transition:left .22s ease;
                height:100%;
            }

            .sidebar.open{left:0}

            .mobile-toggle{display:flex}

            .search-box{
                width:100%;
                max-width:260px;
            }

            .tb-icon.hide-mobile{
                display:none;
            }

            .p-name,
            .p-role,
            .p-chevron{
                display:none;
            }

            .profile-btn{
                padding:0 8px;
            }

            .footer{
                left:0;
            }

            .content{
                padding:18px;
                padding-bottom:62px;
            }
        }

        @media(max-width:560px){
            .topbar{
                padding:0 14px;
                height:68px;
            }

            .search-box{
                display:none;
            }

            .tb-divider{
                display:none;
            }

            .content{
                padding:16px;
                padding-bottom:62px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
@php
    $authUser = auth()->user();
    $role = $authUser->role ?? 'admin';
    $photo = $authUser?->photo ? url('/api/storage/'.$authUser->photo) : null;

    if ($role === 'merchant') {
        $dashboardRoute = route('merchant.dashboard');
        $requestRoute   = route('merchant.requests');
        $transferRoute  = route('merchant.transfers');
        $chatRoute      = route('merchant.chats');
        $historyRoute   = route('merchant.history');
        $settingsRoute  = route('merchant.settings');
        $profileRoute   = route('merchant.profile');
    } elseif ($role === 'client') {
        $dashboardRoute = route('client.dashboard');
        $requestRoute   = route('client.requests');
        $transferRoute  = route('client.transfers');
        $chatRoute      = route('client.chats');
        $historyRoute   = route('client.history');
        $settingsRoute  = route('client.settings');
        $profileRoute   = route('client.profile');
    } else {
        $dashboardRoute = route('admin.dashboard');
        $requestRoute   = route('admin.wallet-requests.index');
        $transferRoute  = route('admin.wallet-transfers.index');
        $chatRoute      = route('admin.chats.index');
        $historyRoute   = route('admin.wallet-transfers.index');
        $settingsRoute  = route('admin.config.index');
        $profileRoute   = route('admin.dashboard');
    }
@endphp

<div class="shell">

    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <div class="brand-icon">
                <i class="ti ti-infinity"></i>
            </div>

            <div>
                <div class="brand-name">Xynder <span>Wallet</span></div>
                <div class="brand-sub">{{ ucfirst($role) }} Panel</div>
            </div>
        </div>

        <nav class="nav">
            <a href="{{ $dashboardRoute }}"
               class="nav-item {{ request()->url() === $dashboardRoute ? 'active' : '' }}">
                <i class="ti ti-layout-dashboard"></i>
                Dashboard
            </a>

            @if($role === 'admin')
                <a href="{{ route('admin.users.index') }}"
                   class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="ti ti-users"></i>
                    Users
                </a>

                <div class="nav-section">
                    <span class="nav-label">Wallet</span>

                    <a href="{{ route('admin.wallet-requests.index') }}"
                       class="nav-item {{ request()->routeIs('admin.wallet-requests.*') ? 'active' : '' }}">
                        <i class="ti ti-credit-card"></i>
                        Wallet Requests
                    </a>

                    <a href="{{ route('admin.wallet-transfers.index') }}"
                       class="nav-item {{ request()->routeIs('admin.wallet-transfers.*') ? 'active' : '' }}">
                        <i class="ti ti-arrows-transfer-up"></i>
                        Transfers
                    </a>

                    <a href="{{ route('admin.chats.index') }}"
                       class="nav-item {{ request()->routeIs('admin.chats.*') ? 'active' : '' }}">
                        <i class="ti ti-message-2"></i>
                        Transaction Chats
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-label">System</span>

                    <a href="{{ route('admin.config.index') }}"
                       class="nav-item {{ request()->routeIs('admin.config.*') ? 'active' : '' }}">
                        <i class="ti ti-settings-2"></i>
                        Configuration
                    </a>

                    <a href="{{ route('admin.support-tickets.index') }}"
                       class="nav-item {{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}">
                        <i class="ti ti-headset"></i>
                        Support Tickets
                    </a>
                </div>
            @else
                <div class="nav-section">
                    <span class="nav-label">Wallet</span>

                    <a href="{{ $requestRoute }}"
                       class="nav-item {{ request()->url() === $requestRoute ? 'active' : '' }}">
                        <i class="ti ti-credit-card"></i>
                        Requests
                    </a>

                    <a href="{{ $transferRoute }}"
                       class="nav-item {{ request()->url() === $transferRoute ? 'active' : '' }}">
                        <i class="ti ti-arrows-transfer-up"></i>
                        Transfer
                    </a>

                    <a href="{{ $chatRoute }}"
                       class="nav-item {{ request()->url() === $chatRoute ? 'active' : '' }}">
                        <i class="ti ti-message-2"></i>
                        Chat
                    </a>

                    <a href="{{ $historyRoute }}"
                       class="nav-item {{ request()->url() === $historyRoute ? 'active' : '' }}">
                        <i class="ti ti-history"></i>
                        History
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-label">Account</span>

                    <a href="{{ $profileRoute }}"
                       class="nav-item {{ request()->url() === $profileRoute ? 'active' : '' }}">
                        <i class="ti ti-user-circle"></i>
                        Profile
                    </a>

                    <a href="{{ $settingsRoute }}"
                       class="nav-item {{ request()->url() === $settingsRoute ? 'active' : '' }}">
                        <i class="ti ti-settings-2"></i>
                        Settings
                    </a>
                </div>
            @endif

            <div style="margin-top:8px">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="nav-item nav-logout">
                        <i class="ti ti-logout"></i>
                        Logout
                    </button>
                </form>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sf-avatar">
                @if($photo)
                    <img src="{{ $photo }}" alt="{{ $authUser->name ?? 'User' }}">
                @else
                    {{ strtoupper(substr($authUser->name ?? 'A', 0, 1)) }}
                @endif
            </div>

            <div style="min-width:0">
                <div class="sf-name">{{ $authUser->name ?? 'User' }}</div>
                <div class="sf-role">{{ ucfirst($role) }}</div>
            </div>
        </div>

    </aside>

    <main class="main">

        <div class="topbar">
            <div class="topbar-left">
                <button class="mobile-toggle"
                        type="button"
                        onclick="document.getElementById('sidebar').classList.toggle('open')"
                        aria-label="Toggle sidebar">
                    <i class="ti ti-menu-2"></i>
                </button>

                <div class="search-box">
                    <i class="ti ti-search"></i>
                    <input type="text" placeholder="Search anything..." aria-label="Search">
                </div>
            </div>

            <div class="topbar-right">
                <div class="tb-icon hide-mobile" title="Language">
                    <i class="ti ti-world"></i>
                </div>

                <div class="tb-icon hide-mobile" title="Theme">
                    <i class="ti ti-moon"></i>
                </div>

                <div class="tb-icon hide-mobile" title="Apps">
                    <i class="ti ti-grid-dots"></i>
                </div>

                <div class="tb-icon" title="Notifications">
                    <i class="ti ti-bell"></i>
                    @if(($stats['pending'] ?? 0) > 0)
                        <span class="tb-badge">{{ $stats['pending'] }}</span>
                    @endif
                </div>

                <div class="tb-icon" title="Transactions">
                    <i class="ti ti-receipt"></i>
                    @if(($stats['rejected'] ?? 0) > 0)
                        <span class="tb-badge">{{ $stats['rejected'] }}</span>
                    @endif
                </div>

                <div class="tb-divider"></div>

                <div class="profile-wrap">
                    <button type="button"
                            class="profile-btn"
                            onclick="toggleProfileMenu()"
                            aria-haspopup="true"
                            aria-expanded="false"
                            id="profileToggle">
                        <div class="p-avatar">
                            @if($photo)
                                <img src="{{ $photo }}" alt="{{ $authUser->name ?? 'User' }}">
                            @else
                                {{ strtoupper(substr($authUser->name ?? 'A', 0, 1)) }}
                            @endif
                        </div>

                        <div>
                            <div class="p-name">{{ $authUser->name ?? 'User' }}</div>
                            <div class="p-role">{{ ucfirst($role) }}</div>
                        </div>

                        <i class="ti ti-chevron-down p-chevron"></i>
                    </button>

                    <div class="profile-menu" id="profileMenu" role="menu">
                        <div class="pm-head">
                            <div class="pm-head-avatar">
                                @if($photo)
                                    <img src="{{ $photo }}" alt="{{ $authUser->name ?? 'User' }}">
                                @else
                                    {{ strtoupper(substr($authUser->name ?? 'A', 0, 1)) }}
                                @endif
                            </div>

                            <div>
                                <div class="pm-hname">{{ $authUser->name ?? 'User' }}</div>
                                <div class="pm-hemail">{{ $authUser->email ?? '' }}</div>
                            </div>
                        </div>

                        <div class="pm-items">
                            <a class="pm-link" href="{{ $profileRoute }}" role="menuitem">
                                <i class="ti ti-user-circle"></i>
                                My Profile
                            </a>

                            <a class="pm-link" href="{{ $settingsRoute }}" role="menuitem">
                                <i class="ti ti-settings-2"></i>
                                Settings
                            </a>

                            <div class="pm-sep"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button class="pm-logout" type="submit" role="menuitem">
                                    <i class="ti ti-logout"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="content">

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="ti ti-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <i class="ti ti-alert-circle"></i>

                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')

        </div>
    </main>
</div>

<div class="footer">
    Copyright &copy; {{ date('Y') }} Xynder Wallet. All rights reserved.
</div>

<script>
    function toggleProfileMenu() {
        const menu = document.getElementById('profileMenu');
        const btn = document.getElementById('profileToggle');

        if (!menu || !btn) return;

        const open = menu.classList.toggle('show');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-wrap')) {
            const menu = document.getElementById('profileMenu');
            const btn = document.getElementById('profileToggle');

            if (menu) menu.classList.remove('show');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }

        if (
            window.innerWidth <= 920 &&
            !e.target.closest('.sidebar') &&
            !e.target.closest('.mobile-toggle')
        ) {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) sidebar.classList.remove('open');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const menu = document.getElementById('profileMenu');
            const btn = document.getElementById('profileToggle');
            const sidebar = document.getElementById('sidebar');

            if (menu) menu.classList.remove('show');
            if (btn) btn.setAttribute('aria-expanded', 'false');
            if (sidebar) sidebar.classList.remove('open');
        }
    });
</script>

@stack('scripts')
</body>
</html>
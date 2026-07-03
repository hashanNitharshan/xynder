<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>{{ $title ?? 'BITXNOW' }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/bitxnow_logo.jpeg') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/bitxnow_logo.jpeg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/bitxnow_logo.jpeg') }}">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

        :root{
            --bg:#0B0E11;
            --surface:#181A20;
            --surface-alt:#1E2329;
            --border:#2B3139;
            --border-faint:#202020;
            --orange:#F0B90B;
            --amber:#C99400;
            --gold:#FFD45A;

            --red:#ef4444;
            --green:#0ecb81;

            --text-primary:#ffffff;
            --text-secondary:#848E9C;
            --muted:#5e6673;

            --shadow:0 18px 40px rgba(0,0,0,.35);
        }

        html,body{height:100%}

        body{
            background:var(--bg);
            color:var(--text-primary);
            font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            font-size:14px;
            overflow-x:hidden;
        }

        a{text-decoration:none;color:inherit}
        button,input,select,textarea{font-family:inherit}

        .shell{
            display:grid;
            grid-template-columns:270px 1fr;
            min-height:100vh;
        }

        .sidebar{
            height:100vh;
            position:sticky;
            top:0;
            background:var(--surface);
            border-right:1px solid var(--border);
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
            border-bottom:1px solid var(--border);
            background:var(--surface-alt);
            flex-shrink:0;
        }

        .brand-logo{
            width:42px;
            height:42px;
            border-radius:50%;
            object-fit:cover;
            border:1px solid var(--border);
            outline:none;
            box-shadow:none;
            background:transparent;
            flex-shrink:0;
        }

        .brand-logo-fallback{
            display:flex;
            align-items:center;
            justify-content:center;
            width:42px;
            height:42px;
            border-radius:50%;
            background:var(--surface);
            border:1px solid var(--border);
            color:var(--orange);
            font-weight:900;
            font-size:17px;
        }

        .brand-name{
            font-size:18px;
            font-weight:800;
            color:#fff;
            line-height:1.1;
        }

        .brand-name span{color:var(--orange)}

        .brand-sub{
            font-size:11px;
            color:var(--text-secondary);
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.08em;
            margin-top:3px;
        }

        .nav{
            padding:16px 12px 24px;
            flex:1;
        }

        .nav-section{margin-top:24px}

        .nav-label{
            font-size:11px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:.08em;
            color:var(--text-secondary);
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
            color:var(--text-secondary);
            font-size:13px;
            font-weight:700;
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
            color:var(--text-secondary);
            transition:.15s;
        }

        .nav-item:hover{
            background:var(--surface-alt);
            color:#fff;
            border-color:var(--border);
        }

        .nav-item:hover i{color:var(--orange)}

        .nav-item.active{
            background:rgba(240,185,11,.12);
            color:#fff;
            border-color:rgba(240,185,11,.55);
            box-shadow:inset 3px 0 0 var(--orange);
        }

        .nav-item.active i{color:var(--orange)}

        .nav-logout:hover{
            background:rgba(239,68,68,.12);
            color:#ff9b9b;
            border-color:rgba(239,68,68,.45);
        }

        .sidebar-footer{display:none}

        .p-avatar,.pm-head-avatar{
            border-radius:50%;
            background:transparent;
            border:1px solid var(--border);
            color:var(--orange);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:900;
            overflow:hidden;
            flex-shrink:0;
        }

        .p-avatar{width:34px;height:34px;font-size:13px}
        .pm-head-avatar{width:44px;height:44px;font-size:16px}

        .p-avatar img,
        .pm-head-avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
            background:transparent;
        }

        .main{
            min-width:0;
            display:flex;
            flex-direction:column;
        }

        .topbar{
            height:76px;
            background:var(--surface-alt);
            border-bottom:1px solid var(--border);
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
            background:var(--surface);
            border:1px solid var(--border);
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
            background:var(--bg);
            border:1px solid var(--border);
            border-radius:6px;
            padding:0 15px;
            width:360px;
            max-width:100%;
            transition:.15s;
        }

        .search-box:focus-within{
            border-color:var(--orange);
            box-shadow:0 0 0 3px rgba(240,185,11,.12);
        }

        .search-box i{
            color:var(--text-secondary);
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

        .search-box input::placeholder{color:var(--muted)}

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
            color:var(--text-secondary);
            font-size:21px;
            cursor:pointer;
            border:1px solid transparent;
            transition:.15s;
        }

        .tb-icon:hover{
            background:var(--surface);
            border-color:var(--border);
            color:#fff;
        }

        .tb-badge{
            position:absolute;
            top:3px;
            right:3px;
            min-width:18px;
            height:18px;
            background:var(--orange);
            color:#0B0E11;
            font-size:10px;
            font-weight:900;
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:0 5px;
            border:2px solid var(--surface-alt);
        }

        .tb-divider{
            width:1px;
            height:32px;
            background:var(--border);
            margin:0 7px;
        }

        .profile-wrap{position:relative}

        .profile-btn{
            display:flex;
            align-items:center;
            gap:10px;
            min-height:48px;
            padding:0 13px 0 10px;
            background:var(--surface);
            border:1px solid var(--border);
            border-radius:6px;
            color:#fff;
            cursor:pointer;
            transition:.15s;
        }

        .profile-btn:hover{
            border-color:rgba(240,185,11,.5);
            background:var(--surface-alt);
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
            color:var(--text-secondary);
            font-weight:800;
            text-transform:uppercase;
        }

        .p-chevron{
            font-size:16px;
            color:var(--text-secondary);
            margin-left:2px;
        }

        .profile-menu{
            position:absolute;
            right:0;
            top:58px;
            width:270px;
            background:var(--surface);
            border:1px solid var(--border);
            border-radius:7px;
            box-shadow:0 20px 50px rgba(0,0,0,.55);
            display:none;
            overflow:hidden;
            z-index:99;
        }

        .profile-menu.show{display:block}

        .pm-head{
            padding:16px;
            border-bottom:1px solid var(--border);
            display:flex;
            gap:12px;
            align-items:center;
            background:var(--surface-alt);
        }

        .pm-hname{
            font-weight:900;
            color:#fff;
            font-size:14px;
        }

        .pm-hemail{
            font-size:12px;
            color:var(--text-secondary);
            margin-top:3px;
            word-break:break-all;
            font-weight:700;
        }

        .pm-items{padding:7px}

        .pm-link,.pm-logout{
            width:100%;
            display:flex;
            align-items:center;
            gap:10px;
            padding:11px 12px;
            background:transparent;
            border:0;
            color:var(--text-secondary);
            font-size:13px;
            font-weight:800;
            text-align:left;
            cursor:pointer;
            border-radius:5px;
            transition:.13s;
        }

        .pm-link:hover{
            background:var(--surface-alt);
            color:#fff;
        }

        .pm-link i{font-size:17px;color:var(--orange)}
        .pm-logout i{font-size:17px;color:var(--red)}

        .pm-sep{
            height:1px;
            background:var(--border);
            margin:5px 6px;
        }

        .pm-logout:hover{
            background:rgba(239,68,68,.12);
            color:#ff9b9b;
        }

        .content{
            flex:1;
            padding:28px;
            padding-bottom:68px;
            min-width:0;
        }

        .page-head{
            display:flex;
            align-items:center;
            gap:14px;
            margin-bottom:22px;
            padding-bottom:18px;
            border-bottom:1px solid var(--border);
        }

        .page-head-icon{
            width:48px;
            height:48px;
            border-radius:10px;
            background:rgba(240,185,11,.12);
            border:1px solid rgba(240,185,11,.4);
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            overflow:hidden;
        }

        .page-head-icon i{
            font-size:24px;
            color:var(--orange);
        }

        .page-head-icon img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .page-title{
            font-size:19px;
            font-weight:800;
            color:#fff;
            letter-spacing:-.3px;
            line-height:1.2;
        }

        .page-subtitle{
            font-size:13px;
            color:var(--text-secondary);
            font-weight:700;
            margin-top:3px;
        }

        .alert{
            display:flex;
            align-items:flex-start;
            gap:10px;
            padding:14px 16px;
            border-radius:4px;
            font-size:13px;
            font-weight:700;
            margin-bottom:16px;
        }

        .alert i{
            font-size:18px;
            flex-shrink:0;
            margin-top:1px;
        }

        .alert-success{
            background:rgba(14,203,129,.12);
            border:1px solid rgba(14,203,129,.35);
            color:var(--green);
        }

        .alert-error{
            background:rgba(239,68,68,.12);
            border:1px solid rgba(239,68,68,.35);
            color:#ff9b9b;
        }

        .footer{
            position:fixed;
            left:270px;
            right:0;
            bottom:0;
            height:42px;
            background:var(--surface-alt);
            border-top:1px solid var(--border);
            display:flex;
            align-items:center;
            justify-content:center;
            color:var(--text-secondary);
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

            .tb-icon.hide-mobile{display:none}

            .p-name,.p-role,.p-chevron{display:none}

            .profile-btn{padding:0 8px}
            .footer{left:0}

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

            .search-box,.tb-divider{display:none}

            .content{
                padding:16px;
                padding-bottom:62px;
            }

            .page-head{
                gap:10px;
                margin-bottom:16px;
                padding-bottom:14px;
            }

            .page-head-icon{
                width:40px;
                height:40px;
                border-radius:8px;
            }

            .page-head-icon i{font-size:20px}
            .page-title{font-size:18px}
        }
    </style>

    @stack('styles')
</head>

<body>
@php
    $authUser = auth()->user();
    $role = $authUser->role ?? 'admin';
    $bitxnowLogo = asset('images/bitxnow_logo.jpeg');
    $userPhoto = $authUser?->photo ? url('/api/storage/'.$authUser->photo) : null;
    $photo = $role === 'admin' ? $bitxnowLogo : ($userPhoto ?: $bitxnowLogo);

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
            <img src="{{ asset('images/bitxnow_logo.jpeg') }}"
                 class="brand-logo"
                 alt="BitXNow"
                 onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('div'),{className:'brand-logo brand-logo-fallback',innerText:'B'}));">

            <div>
                <div class="brand-name">BITX<span>NOW</span></div>
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
                       P2P Requests
                    </a>

                    <a href="{{ route('admin.wallet-transfers.index') }}"
                       class="nav-item {{ request()->routeIs('admin.wallet-transfers.*') ? 'active' : '' }}">
                        <i class="ti ti-arrows-transfer-up"></i>
                        Transfers
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
                         P2P Requests
                    </a>

                    <a href="{{ $transferRoute }}"
                       class="nav-item {{ request()->url() === $transferRoute ? 'active' : '' }}">
                        <i class="ti ti-arrows-transfer-up"></i>
                        Transfer
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

                <div class="tb-icon" title="Notifications">
                    <i class="ti ti-bell"></i>
                    @if(($stats['pending'] ?? 0) > 0)
                        <span class="tb-badge">{{ $stats['pending'] }}</span>
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
                            <img src="{{ $photo }}" alt="BITXNOW">
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
                                <img src="{{ $photo }}" alt="BITXNOW">
                            </div>

                            <div>
                                <div class="pm-hname">{{ $authUser->name ?? 'User' }}</div>
                                <div class="pm-hemail">{{ $authUser->email ?? '' }}</div>
                            </div>
                        </div>

                        <div class="pm-items">
                            

                           

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
    Copyright &copy; {{ date('Y') }} BITXNOW. All rights reserved.
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
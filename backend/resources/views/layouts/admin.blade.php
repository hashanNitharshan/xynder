<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Xynder Wallet' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#0b1014;--panel:#11191d;--line:rgba(255,255,255,.08);
            --txt:#eef5f8;--muted:#9aa6ad;--blue:#0d8cff;--red:#ff3158;
            --green:#16e044;--cyan:#00c8ff;
        }
        body{background:var(--bg);color:var(--txt);font-family:Arial,Helvetica,sans-serif;overflow-x:hidden}
        a{text-decoration:none;color:inherit} button,input{font-family:inherit}

        .rocker{min-height:100vh;display:grid;grid-template-columns:270px 1fr;background:var(--bg)}
        .sidebar{height:100vh;position:sticky;top:0;background:#10181c;border-right:1px solid var(--line);overflow-y:auto}
        .brand{height:64px;display:flex;align-items:center;gap:12px;padding:0 16px;border-bottom:1px solid var(--line);font-size:24px;font-weight:900}
        .brand-mark{width:38px;height:22px;border:4px solid #fff;border-right:0;border-radius:50%;transform:skewX(-22deg)}
        .nav{padding:16px 12px 80px}
        .nav-title{color:var(--muted);font-size:12px;text-transform:uppercase;margin:22px 4px 10px;letter-spacing:.04em}
        .nav a,.logout-btn{width:100%;min-height:39px;display:flex;align-items:center;gap:13px;padding:10px 16px;color:#aab4ba;border-radius:4px;margin-bottom:8px;font-size:16px;font-weight:600;background:transparent;border:0;cursor:pointer;text-align:left}
        .nav a:hover,.nav a.active,.logout-btn:hover{background:#303639;color:#fff}
        .nav-ico{width:22px;text-align:center;font-size:19px}
        .main{min-width:0}

        .topbar{height:64px;background:#10181c;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 18px 0 25px;position:sticky;top:0;z-index:20}
        .search{width:395px;height:38px;background:#252c30;border:1px solid var(--line);border-radius:6px;display:flex;align-items:center;gap:10px;padding:0 14px;color:#cfd8dc;font-size:18px}
        .search input{width:100%;background:transparent;border:0;outline:0;color:#fff;font-size:16px;font-weight:700}
        .top-right{display:flex;align-items:center;gap:20px}
        .top-icon{position:relative;font-size:20px;color:#e5edf0}
        .count{position:absolute;top:-10px;right:-12px;min-width:19px;height:19px;border-radius:50%;background:#ef2b25;color:white;font-size:11px;display:flex;align-items:center;justify-content:center;font-weight:900}

        .profile-wrap{position:relative}
        .profile-btn{display:flex;align-items:center;gap:12px;padding-left:16px;border-left:1px solid var(--line);background:transparent;color:#fff;border-top:0;border-right:0;border-bottom:0;cursor:pointer}
        .avatar{width:44px;height:44px;border-radius:50%;object-fit:cover;background:linear-gradient(135deg,var(--blue),var(--cyan));display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff}
        .profile-name{font-size:16px;font-weight:900;text-align:left}
        .profile-role{font-size:13px;color:#aeb8be;margin-top:3px;text-align:left}

        .profile-menu{position:absolute;right:0;top:56px;width:245px;background:#10181c;border:1px solid rgba(255,255,255,.14);border-radius:10px;box-shadow:0 18px 50px rgba(0,0,0,.55);display:none;overflow:hidden;z-index:99}
        .profile-menu.show{display:block}
        .pm-head{padding:16px;border-bottom:1px solid var(--line);display:flex;gap:12px;align-items:center}
        .pm-name{font-weight:900;color:#fff}
        .pm-email{font-size:12px;color:#9aa6ad;margin-top:3px;word-break:break-all}
        .pm-link,.pm-logout{width:100%;display:flex;align-items:center;gap:10px;padding:13px 16px;background:transparent;border:0;color:#cbd5da;font-size:14px;font-weight:800;text-align:left;cursor:pointer}
        .pm-link:hover{background:#303639;color:#fff}
        .pm-logout:hover{background:rgba(255,49,88,.12);color:#ff8ba1}

        .content{padding:26px;padding-bottom:70px}
        .footer{position:fixed;left:270px;right:0;bottom:0;height:40px;background:#10181c;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:#a9b4bb;z-index:30}
        .mobile-menu{display:none}

        @media(max-width:900px){
            .rocker{display:block}
            .sidebar{position:fixed;left:-280px;width:270px;z-index:100;transition:.25s}
            .sidebar.open{left:0}
            .mobile-menu{display:inline-flex;width:40px;height:38px;align-items:center;justify-content:center;background:#252c30;color:#fff;border:1px solid var(--line);border-radius:6px;margin-right:10px;cursor:pointer}
            .search{width:100%;max-width:340px}
            .top-right .top-icon{display:none}
            .profile-name,.profile-role{display:none}
            .profile-menu{right:0;width:230px}
            .footer{left:0}
            .content{padding:16px;padding-bottom:70px}
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
        $requestRoute = route('merchant.requests');
        $transferRoute = route('merchant.transfers');
        $chatRoute = route('merchant.chats');
        $historyRoute = route('merchant.history');
        $settingsRoute = route('merchant.settings');
        $profileRoute = route('merchant.profile');
    } elseif ($role === 'client') {
        $dashboardRoute = route('client.dashboard');
        $requestRoute = route('client.requests');
        $transferRoute = route('client.transfers');
        $chatRoute = route('client.chats');
        $historyRoute = route('client.history');
        $settingsRoute = route('client.settings');
        $profileRoute = route('client.profile');
    } else {
        $dashboardRoute = route('admin.dashboard');
        $requestRoute = route('admin.wallet-requests.index');
        $transferRoute = route('admin.wallet-transfers.index');
        $chatRoute = route('admin.chats.index');
        $historyRoute = route('admin.wallet-transfers.index');
        $settingsRoute = route('admin.config.index');
        $profileRoute = route('admin.dashboard');
    }
@endphp

<div class="rocker">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark"></div>
            Xynder
        </div>

        <nav class="nav">
            <a class="{{ request()->url() === $dashboardRoute ? 'active' : '' }}" href="{{ $dashboardRoute }}">
                <span class="nav-ico">⌂</span> Dashboard
            </a>

            @if($role === 'admin')
                <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <span class="nav-ico">👥</span> Users
                </a>

                <div class="nav-title">Wallet</div>

                <a class="{{ request()->routeIs('admin.wallet-requests.*') ? 'active' : '' }}" href="{{ route('admin.wallet-requests.index') }}">
                    <span class="nav-ico">▣</span> Wallet Requests
                </a>

                <a class="{{ request()->routeIs('admin.wallet-transfers.*') ? 'active' : '' }}" href="{{ route('admin.wallet-transfers.index') }}">
                    <span class="nav-ico">⇄</span> Wallet Transfers
                </a>

                <a class="{{ request()->routeIs('admin.chats.*') ? 'active' : '' }}" href="{{ route('admin.chats.index') }}">
                    <span class="nav-ico">✉</span> Transaction Chats
                </a>

                <div class="nav-title">System</div>

                <a class="{{ request()->routeIs('admin.config.*') ? 'active' : '' }}" href="{{ route('admin.config.index') }}">
                    <span class="nav-ico">⚙</span> Config
                </a>

                <a class="{{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}" href="{{ route('admin.support-tickets.index') }}">
                    <span class="nav-ico">?</span> Support
                </a>
            @else
                <div class="nav-title">Wallet</div>

                <a class="{{ request()->url() === $requestRoute ? 'active' : '' }}" href="{{ $requestRoute }}">
                    <span class="nav-ico">▣</span> Requests
                </a>
                <a class="{{ request()->url() === $transferRoute ? 'active' : '' }}" href="{{ $transferRoute }}">
                    <span class="nav-ico">⇄</span> Transfer
                </a>
                <a class="{{ request()->url() === $chatRoute ? 'active' : '' }}" href="{{ $chatRoute }}">
                    <span class="nav-ico">✉</span> Chat
                </a>
                <a class="{{ request()->url() === $historyRoute ? 'active' : '' }}" href="{{ $historyRoute }}">
                    <span class="nav-ico">◷</span> History
                </a>

                <div class="nav-title">System</div>

                <a class="{{ request()->url() === $profileRoute ? 'active' : '' }}" href="{{ $profileRoute }}">
                    <span class="nav-ico">👤</span> Profile
                </a>

                <a class="{{ request()->url() === $settingsRoute ? 'active' : '' }}" href="{{ $settingsRoute }}">
                    <span class="nav-ico">⚙</span> Settings
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-btn" type="submit">
                    <span class="nav-ico">↩</span> Logout
                </button>
            </form>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:10px;flex:1;">
                <button class="mobile-menu" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                <div class="search">
                    🔍
                    <input type="text" placeholder="Search">
                </div>
            </div>

            <div class="top-right">
                <div class="top-icon">🌐</div>
                <div class="top-icon">☀</div>
                <div class="top-icon">▦</div>
                <div class="top-icon">🔔 <span class="count">{{ $stats['pending'] ?? 0 }}</span></div>
                <div class="top-icon">🧾 <span class="count">{{ $stats['rejected'] ?? 0 }}</span></div>

                <div class="profile-wrap">
                    <button type="button" class="profile-btn" onclick="toggleProfileMenu()">
                        @if($photo)
                            <img src="{{ $photo }}" class="avatar" alt="Profile">
                        @else
                            <span class="avatar">{{ strtoupper(substr($authUser->name ?? 'A', 0, 1)) }}</span>
                        @endif

                        <div>
                            <div class="profile-name">{{ $authUser->name ?? 'User' }}</div>
                            <div class="profile-role">{{ ucfirst($role) }}</div>
                        </div>
                    </button>

                    <div class="profile-menu" id="profileMenu">
                        <div class="pm-head">
                            @if($photo)
                                <img src="{{ $photo }}" class="avatar" alt="Profile">
                            @else
                                <span class="avatar">{{ strtoupper(substr($authUser->name ?? 'A', 0, 1)) }}</span>
                            @endif
                            <div>
                                <div class="pm-name">{{ $authUser->name ?? 'User' }}</div>
                                <div class="pm-email">{{ $authUser->email ?? '' }}</div>
                            </div>
                        </div>

                        <a class="pm-link" href="{{ $profileRoute }}">👤 My Profile</a>
                        <a class="pm-link" href="{{ $settingsRoute }}">⚙ Settings</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="pm-logout" type="submit">↩ Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            @if(session('success'))
                <div style="background:rgba(22,224,68,.13);border:1px solid rgba(22,224,68,.25);color:#9cffad;padding:12px 14px;border-radius:8px;margin-bottom:16px;font-weight:800;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:rgba(255,49,88,.13);border:1px solid rgba(255,49,88,.25);color:#ffb4c2;padding:12px 14px;border-radius:8px;margin-bottom:16px;font-weight:800;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<div class="footer">Copyright © {{ date('Y') }}. All right reserved.</div>

<script>
    function toggleProfileMenu() {
        document.getElementById('profileMenu').classList.toggle('show');
    }

    document.addEventListener('click', function(e) {
        const wrap = e.target.closest('.profile-wrap');
        if (!wrap) {
            const menu = document.getElementById('profileMenu');
            if (menu) menu.classList.remove('show');
        }
    });
</script>

@stack('scripts')
</body>
</html>
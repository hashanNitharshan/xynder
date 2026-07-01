@extends('layouts.admin', ['title' => 'Settings'])

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

.settings-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.settings-hero{
    position:relative;
    min-height:320px;
    padding:65px 85px 120px;
    background:var(--hero);
    overflow:hidden;
}

.settings-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.08;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.settings-hero-content{
    position:relative;
    z-index:2;
    max-width:650px;
}

.settings-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.settings-title{
    font-size:48px;
    line-height:1.15;
    font-weight:900;
    margin:0 0 20px;
}

.settings-title span{color:var(--red)}

.settings-sub{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
}

.settings-wrap{
    position:relative;
    z-index:5;
    max-width:780px;
    margin:-70px auto 0;
    padding:0 20px;
    display:flex;
    flex-direction:column;
    gap:18px;
}

.settings-alert{
    padding:14px 16px;
    border-radius:4px;
    font-weight:800;
}

.settings-alert.ok{
    background:#0d2b1e;
    color:var(--green);
    border:1px solid #1a4a35;
}

.settings-alert.err{
    background:#3a1018;
    color:#ff6b7b;
    border:1px solid #71313a;
}

.settings-profile{
    display:flex;
    align-items:center;
    gap:18px;
    padding:24px;
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    text-decoration:none;
    color:#fff;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.settings-avatar{
    width:84px;
    height:84px;
    border-radius:50%;
    background:var(--red);
    padding:4px;
    flex-shrink:0;
}

.settings-avatar img,
.settings-avatar-inner{
    width:100%;
    height:100%;
    border-radius:50%;
    object-fit:cover;
}

.settings-avatar-inner{
    background:#1f2428;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    font-weight:900;
    color:#fff;
}

.settings-user{
    flex:1;
    min-width:0;
}

.settings-user h3{
    margin:0 0 6px;
    font-size:23px;
    font-weight:900;
    color:#fff;
}

.settings-user p{
    margin:4px 0;
    color:#aeb4ba;
    font-size:14px;
    font-weight:700;
    word-break:break-word;
}

.settings-verified{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:9px;
    padding:6px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
}

.settings-verified.ok{
    background:#0d2b1e;
    color:var(--green);
}

.settings-verified.no{
    background:#3b2a09;
    color:var(--gold);
}

.settings-arrow{
    color:#9fa5aa;
    font-size:28px;
    flex-shrink:0;
}

.settings-item{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    overflow:hidden;
}

.settings-item:hover{
    border-color:rgba(232,25,44,.55);
}

.settings-link,
.settings-summary{
    display:flex;
    align-items:center;
    gap:15px;
    padding:18px;
    cursor:pointer;
    text-decoration:none;
    color:#fff;
}

.settings-link:hover,
.settings-summary:hover{
    background:#30363a;
}

.settings-icon{
    width:50px;
    height:50px;
    border-radius:50%;
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
    font-weight:900;
    flex-shrink:0;
}

.settings-text{
    flex:1;
    min-width:0;
}

.settings-text h4{
    margin:0;
    font-size:16px;
    font-weight:900;
    color:#fff;
}

.settings-text p{
    margin:5px 0 0;
    color:#9fa5aa;
    font-size:13px;
    font-weight:700;
}

details summary{
    list-style:none;
}

details summary::-webkit-details-marker{
    display:none;
}

details[open] .settings-arrow{
    transform:rotate(90deg);
}

.settings-body{
    padding:18px;
    border-top:1px solid var(--line);
    background:#24292d;
}

.settings-field{
    margin-bottom:14px;
}

.settings-field label{
    display:block;
    color:#9fa5aa;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:8px;
}

.settings-field input,
.settings-field textarea{
    width:100%;
    background:#1f2428;
    border:1px solid var(--line);
    color:#fff;
    border-radius:4px;
    padding:13px 14px;
    outline:none;
    font-weight:700;
}

.settings-field input:focus,
.settings-field textarea:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.settings-field textarea{
    resize:vertical;
    min-height:110px;
}

.settings-btn{
    border:0;
    border-radius:4px;
    padding:13px 18px;
    font-weight:900;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
}

.settings-btn.primary{
    background:var(--red);
    color:#fff;
}

.settings-btn.primary:hover{
    background:var(--red2);
}

.settings-ticket-title{
    font-size:17px;
    font-weight:900;
    color:#fff;
    margin:24px 0 14px;
    display:flex;
    align-items:center;
    gap:8px;
}

.settings-ticket-title i{
    color:var(--red);
}

.settings-ticket-wrap{
    overflow-x:auto;
}

.settings-table{
    width:100%;
    min-width:640px;
    border-collapse:collapse;
}

.settings-table th{
    color:#9fa5aa;
    font-size:11px;
    text-transform:uppercase;
    text-align:left;
    padding:14px 16px;
    background:#1f2428;
}

.settings-table td{
    padding:15px 16px;
    border-top:1px solid #3a4147;
    color:#c9ced3;
    font-size:13px;
    font-weight:700;
    vertical-align:top;
}

.settings-table tr:hover td{
    background:#30363a;
}

.settings-status{
    display:inline-block;
    padding:6px 11px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
}

.settings-status.pending,
.settings-status.open{
    background:#3b2a09;
    color:var(--gold);
}

.settings-status.approved,
.settings-status.resolved,
.settings-status.closed{
    background:#0d2b1e;
    color:var(--green);
}

.settings-status.rejected{
    background:#3a1018;
    color:#ff6b7b;
}

.settings-empty{
    text-align:center;
    color:#9fa5aa!important;
    padding:34px!important;
    font-weight:800!important;
}

.settings-logout{
    background:#3a1018;
    border:1px solid rgba(232,25,44,.45);
    border-radius:6px;
    padding:20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
}

.settings-logout h3{
    margin:0 0 5px;
    font-size:18px;
    font-weight:900;
}

.settings-logout p{
    margin:0;
    color:#ffb4bc;
    font-size:13px;
    font-weight:700;
}

.settings-btn.danger{
    background:var(--red);
    color:#fff;
}

.settings-btn.danger:hover{
    background:var(--red2);
}

@media(max-width:760px){
    .settings-page{margin:-16px}

    .settings-hero{
        padding:45px 24px 110px;
    }

    .settings-title{
        font-size:36px;
    }

    .settings-wrap{
        margin-top:-70px;
        padding:0 20px;
    }

    .settings-profile{
        align-items:flex-start;
    }

    .settings-avatar{
        width:68px;
        height:68px;
    }

    .settings-user h3{
        font-size:20px;
    }

    .settings-logout{
        flex-direction:column;
        align-items:flex-start;
    }
}
</style>
@endpush

@section('content')
@php
    $profileRoute = $user->role === 'merchant'
        ? route('merchant.profile')
        : route('client.profile');

    $passwordRoute = $user->role === 'merchant'
        ? route('merchant.settings.password')
        : route('client.settings.password');

    $supportRoute = $user->role === 'merchant'
        ? route('merchant.settings.support')
        : route('client.settings.support');

    $roleLabel = $user->role === 'merchant' ? 'Merchant' : 'Client';
    $photo = $user->photo ? url('/api/storage/'.$user->photo) : null;
@endphp

<div class="settings-page">

    <section class="settings-hero">
        <div class="settings-hero-content">
            <div class="settings-eyebrow">Xynder Wallet</div>

            <h1 class="settings-title">
                Account
                <span>Settings</span>
            </h1>

          
        </div>
    </section>

    <div class="settings-wrap">

        @if(session('success'))
            <div class="settings-alert ok">
                <i class="ti ti-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="settings-alert err">
                <i class="ti ti-alert-triangle"></i>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <a href="{{ $profileRoute }}" class="settings-profile">
            <div class="settings-avatar">
                @if($photo)
                    <img src="{{ $photo }}" alt="Profile">
                @else
                    <div class="settings-avatar-inner">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="settings-user">
                <h3>{{ $user->name }}</h3>
                <p><i class="ti ti-phone"></i> {{ $user->phone ?? '-' }}</p>
                <p><i class="ti ti-mail"></i> {{ $user->email }}</p>

                @if($user->is_verified)
                    <div class="settings-verified ok">
                        <i class="ti ti-circle-check"></i>
                        VERIFIED {{ strtoupper($roleLabel) }}
                    </div>
                @else
                    <div class="settings-verified no">
                        <i class="ti ti-alert-circle"></i>
                        UNVERIFIED {{ strtoupper($roleLabel) }}
                    </div>
                @endif
            </div>

            <div class="settings-arrow">
                <i class="ti ti-chevron-right"></i>
            </div>
        </a>

        <a href="{{ $profileRoute }}" class="settings-item settings-link">
            <div class="settings-icon">
                <i class="ti ti-credit-card"></i>
            </div>

            <div class="settings-text">
                <h4>Payment Methods</h4>
                <p>Bank, UPI and account details</p>
            </div>

            <div class="settings-arrow">
                <i class="ti ti-chevron-right"></i>
            </div>
        </a>

        <details class="settings-item">
            <summary class="settings-summary">
                <div class="settings-icon">
                    <i class="ti ti-lock-password"></i>
                </div>

                <div class="settings-text">
                    <h4>Change Password</h4>
                    <p>Update your {{ strtolower($roleLabel) }} account password securely</p>
                </div>

                <div class="settings-arrow">
                    <i class="ti ti-chevron-right"></i>
                </div>
            </summary>

            <div class="settings-body">
                <form method="POST" action="{{ $passwordRoute }}">
                    @csrf

                    <div class="settings-field">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>

                    <div class="settings-field">
                        <label>New Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="settings-field">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="settings-btn primary">
                        <i class="ti ti-shield-check"></i>
                        Update Password
                    </button>
                </form>
            </div>
        </details>

        <details class="settings-item">
            <summary class="settings-summary">
                <div class="settings-icon">
                    <i class="ti ti-headset"></i>
                </div>

                <div class="settings-text">
                    <h4>Help & Support</h4>
                    <p>Raise a {{ strtolower($roleLabel) }} support ticket</p>
                </div>

                <div class="settings-arrow">
                    <i class="ti ti-chevron-right"></i>
                </div>
            </summary>

            <div class="settings-body">
                <form method="POST" action="{{ $supportRoute }}">
                    @csrf

                    <div class="settings-field">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="settings-field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="settings-field">
                        <label>Message</label>
                        <textarea name="message" rows="4" required>{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="settings-btn primary">
                        <i class="ti ti-send"></i>
                        Submit Ticket
                    </button>
                </form>

                <div class="settings-ticket-title">
                    <i class="ti ti-ticket"></i>
                    Your Tickets
                </div>

                <div class="settings-ticket-wrap">
                    <table class="settings-table">
                        <thead>
                            <tr>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->message }}</td>

                                    <td>
                                        <span class="settings-status {{ strtolower($ticket->status) }}">
                                            {{ strtoupper($ticket->status) }}
                                        </span>
                                    </td>

                                    <td style="color:#9fa5aa;">
                                        {{ $ticket->created_at?->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="settings-empty">
                                        No support tickets yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </details>

        <div class="settings-logout">
            <div>
                <h3>
                    <i class="ti ti-logout"></i>
                    Logout Account
                </h3>
                <p>End your current session safely.</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="settings-btn danger">
                    <i class="ti ti-power"></i>
                    Logout
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
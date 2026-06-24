@extends('layouts.admin', ['title' => 'Settings'])

@push('styles')
<style>
.xyn-settings-wrap{
    max-width:720px;
    margin:0 auto;
    display:flex;
    flex-direction:column;
    gap:18px;
}

.xyn-settings-hero{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:28px;
    padding:26px;
    position:relative;
    overflow:hidden;
}

.xyn-settings-hero::before{
    content:'';
    position:absolute;
    right:-90px;
    top:-90px;
    width:300px;
    height:300px;
    background:radial-gradient(circle,rgba(124,92,252,.24),transparent 70%);
}

.xyn-settings-title{
    font-size:30px;
    font-weight:900;
    margin:0 0 6px;
    position:relative;
    z-index:1;
}

.xyn-settings-title span{
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.xyn-settings-sub{
    color:#9ca3af;
    font-size:14px;
    position:relative;
    z-index:1;
}

.xyn-profile-card{
    display:flex;
    align-items:center;
    gap:16px;
    padding:22px;
    background:linear-gradient(135deg,#1a1060,#071228);
    border:1px solid rgba(124,92,252,.28);
    border-radius:28px;
    position:relative;
    overflow:hidden;
    text-decoration:none;
}

.xyn-profile-card::before{
    content:'';
    position:absolute;
    right:-70px;
    top:-70px;
    width:220px;
    height:220px;
    background:radial-gradient(circle,rgba(6,182,212,.18),transparent 70%);
}

.xyn-avatar-ring{
    width:76px;
    height:76px;
    border-radius:50%;
    background:linear-gradient(135deg,#7c5cfc,#06b6d4);
    padding:3px;
    flex-shrink:0;
    position:relative;
    z-index:1;
}

.xyn-avatar-ring img,
.xyn-avatar-inner{
    width:100%;
    height:100%;
    border-radius:50%;
    object-fit:cover;
}

.xyn-avatar-inner{
    background:#131929;
    color:#a78bfa;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
    font-weight:900;
}

.xyn-user-info{
    flex:1;
    min-width:0;
    position:relative;
    z-index:1;
}

.xyn-user-info h3{
    margin:0;
    font-size:20px;
    font-weight:900;
    color:#fff;
}

.xyn-user-info p{
    margin:4px 0;
    color:#9ca3af;
    font-size:14px;
    word-break:break-word;
}

.xyn-verified{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:6px;
    padding:6px 12px;
    border-radius:999px;
    font-size:11px;
    font-weight:900;
}

.xyn-verified.ok{
    background:rgba(16,185,129,.12);
    color:#34d399;
    border:1px solid rgba(16,185,129,.25);
}

.xyn-verified.no{
    background:rgba(245,158,11,.13);
    color:#fbbf24;
    border:1px solid rgba(245,158,11,.25);
}

.xyn-arrow{
    color:#9ca3af;
    font-size:32px;
    position:relative;
    z-index:1;
}

.xyn-setting-item{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:24px;
    overflow:hidden;
    position:relative;
}

.xyn-setting-link,
.xyn-setting-summary{
    display:flex;
    align-items:center;
    gap:15px;
    padding:18px;
    cursor:pointer;
    text-decoration:none;
}

.xyn-setting-link:hover,
.xyn-setting-summary:hover{
    background:#131929;
}

.xyn-setting-icon{
    width:50px;
    height:50px;
    border-radius:16px;
    background:linear-gradient(135deg,rgba(124,92,252,.18),rgba(6,182,212,.10));
    color:#a78bfa;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:21px;
    font-weight:900;
    flex-shrink:0;
}

.xyn-setting-text{
    flex:1;
    min-width:0;
}

.xyn-setting-text h4{
    margin:0;
    font-size:16px;
    font-weight:900;
    color:#fff;
}

.xyn-setting-text p{
    margin:5px 0 0;
    color:#9ca3af;
    font-size:13px;
}

details summary{
    list-style:none;
}

details summary::-webkit-details-marker{
    display:none;
}

details[open] .xyn-arrow{
    transform:rotate(90deg);
}

.xyn-setting-body{
    padding:18px;
    border-top:1px solid rgba(255,255,255,.07);
    background:#0b0f1a;
}

.xyn-field{
    margin-bottom:14px;
}

.xyn-field label{
    display:block;
    color:#9ca3af;
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:8px;
}

.xyn-field input,
.xyn-field textarea{
    width:100%;
    background:#131929;
    border:1px solid rgba(255,255,255,.08);
    color:#fff;
    border-radius:14px;
    padding:13px 14px;
    outline:none;
}

.xyn-field input:focus,
.xyn-field textarea:focus{
    border-color:rgba(124,92,252,.55);
    box-shadow:0 0 0 3px rgba(124,92,252,.12);
}

.xyn-btn{
    border:0;
    border-radius:15px;
    padding:13px 18px;
    font-weight:900;
    cursor:pointer;
}

.xyn-btn.primary{
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    color:#fff;
}

.xyn-ticket-title{
    font-size:17px;
    font-weight:900;
    color:#fff;
    margin:22px 0 14px;
}

.xyn-ticket-wrap{
    overflow-x:auto;
}

.xyn-ticket-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 7px;
    font-size:13px;
}

.xyn-ticket-table th{
    color:#6b7280;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.08em;
    text-align:left;
    padding:0 12px 8px;
}

.xyn-ticket-table td{
    background:#131929;
    padding:13px 12px;
    border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
    vertical-align:top;
}

.xyn-ticket-table td:first-child{
    border-left:1px solid rgba(255,255,255,.07);
    border-radius:14px 0 0 14px;
}

.xyn-ticket-table td:last-child{
    border-right:1px solid rgba(255,255,255,.07);
    border-radius:0 14px 14px 0;
}

.xyn-status{
    display:inline-block;
    padding:6px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:900;
}

.xyn-status.pending,
.xyn-status.open{
    background:rgba(245,158,11,.13);
    color:#fbbf24;
}

.xyn-status.approved,
.xyn-status.resolved,
.xyn-status.closed{
    background:rgba(16,185,129,.12);
    color:#34d399;
}

.xyn-status.rejected{
    background:rgba(239,68,68,.12);
    color:#f87171;
}

.xyn-empty{
    text-align:center;
    color:#6b7280;
    padding:30px!important;
}

.xyn-alert-success,
.xyn-alert-error{
    border-radius:16px;
    padding:14px 16px;
    font-weight:800;
}

.xyn-alert-success{
    background:rgba(16,185,129,.12);
    border:1px solid rgba(16,185,129,.22);
    color:#d1fae5;
}

.xyn-alert-error{
    background:rgba(239,68,68,.12);
    border:1px solid rgba(239,68,68,.22);
    color:#fee2e2;
}

@media(max-width:650px){
    .xyn-settings-wrap{
        max-width:100%;
    }

    .xyn-profile-card{
        align-items:flex-start;
    }

    .xyn-avatar-ring{
        width:64px;
        height:64px;
    }

    .xyn-settings-title{
        font-size:25px;
    }

    .xyn-setting-icon{
        width:44px;
        height:44px;
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

    $photo = $user->photo ? url('/api/storage/'.$user->photo) : null;
@endphp

<div class="xyn-settings-wrap">

    

    @if(session('success'))
        <div class="xyn-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="xyn-alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <a href="{{ $profileRoute }}" class="xyn-profile-card">
        <div class="xyn-avatar-ring">
            @if($photo)
                <img src="{{ $photo }}" alt="Profile">
            @else
                <div class="xyn-avatar-inner">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="xyn-user-info">
            <h3>{{ $user->name }}</h3>
            <p>{{ $user->phone ?? '-' }}</p>
            <p>{{ $user->email }}</p>

            @if($user->is_verified)
                <div class="xyn-verified ok">✽ Verified Merchant</div>
            @else
                <div class="xyn-verified no">✽ Unverified Merchant</div>
            @endif
        </div>

        <div class="xyn-arrow">›</div>
    </a>

    <a href="{{ $profileRoute }}" class="xyn-setting-item xyn-setting-link">
        <div class="xyn-setting-icon">▣</div>
        <div class="xyn-setting-text">
            <h4>Payment Methods</h4>
            <p>Bank, UPI and account details</p>
        </div>
        <div class="xyn-arrow">›</div>
    </a>

    <details class="xyn-setting-item">
        <summary class="xyn-setting-summary">
            <div class="xyn-setting-icon">⟲</div>
            <div class="xyn-setting-text">
                <h4>Change Password</h4>
                <p>Update your merchant account password</p>
            </div>
            <div class="xyn-arrow">›</div>
        </summary>

        <div class="xyn-setting-body">
            <form method="POST" action="{{ $passwordRoute }}">
                @csrf

                <div class="xyn-field">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="xyn-field">
                    <label>New Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="xyn-field">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" required>
                </div>

                <button type="submit" class="xyn-btn primary">
                    Update Password
                </button>
            </form>
        </div>
    </details>

    <details class="xyn-setting-item">
        <summary class="xyn-setting-summary">
            <div class="xyn-setting-icon">☏</div>
            <div class="xyn-setting-text">
                <h4>Help & Support</h4>
                <p>Raise a merchant support ticket</p>
            </div>
            <div class="xyn-arrow">›</div>
        </summary>

        <div class="xyn-setting-body">
            <form method="POST" action="{{ $supportRoute }}">
                @csrf

                <div class="xyn-field">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="xyn-field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="xyn-field">
                    <label>Message</label>
                    <textarea name="message" rows="4" required>{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="xyn-btn primary">
                    Submit Ticket
                </button>
            </form>

            <div class="xyn-ticket-title">Your Tickets</div>

            <div class="xyn-ticket-wrap">
                <table class="xyn-ticket-table">
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
                                    <span class="xyn-status {{ strtolower($ticket->status) }}">
                                        {{ strtoupper($ticket->status) }}
                                    </span>
                                </td>
                                <td style="color:#9ca3af;">
                                    {{ $ticket->created_at?->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="xyn-empty">
                                    No support tickets yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </details>

</div>
@endsection
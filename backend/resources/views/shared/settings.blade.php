@extends('layouts.admin', ['title' => $title ?? 'Settings'])

@push('styles')
<style>
.xyn-settings-page{
    display:flex;
    flex-direction:column;
    gap:22px;
}

.xyn-hero{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:28px;
    padding:26px;
    position:relative;
    overflow:hidden;
}

.xyn-hero::before{
    content:'';
    position:absolute;
    right:-90px;
    top:-90px;
    width:330px;
    height:330px;
    background:radial-gradient(circle,rgba(124,92,252,.22),transparent 70%);
}

.xyn-hero::after{
    content:'';
    position:absolute;
    left:40%;
    bottom:-90px;
    width:240px;
    height:240px;
    background:radial-gradient(circle,rgba(6,182,212,.10),transparent 70%);
}

.xyn-hero-title{
    font-size:30px;
    font-weight:900;
    margin-bottom:6px;
    position:relative;
    z-index:1;
}

.xyn-hero-title span{
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}

.xyn-hero-sub{
    color:#9ca3af;
    font-size:14px;
    position:relative;
    z-index:1;
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

.xyn-profile-card{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:28px;
    padding:24px;
    display:flex;
    align-items:center;
    gap:18px;
    position:relative;
    overflow:hidden;
}

.xyn-profile-card::before{
    content:'';
    position:absolute;
    right:-70px;
    top:-70px;
    width:230px;
    height:230px;
    background:radial-gradient(circle,rgba(124,92,252,.18),transparent 70%);
}

.xyn-avatar-ring{
    width:82px;
    height:82px;
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
    font-size:32px;
    font-weight:900;
}

.xyn-profile-info{
    position:relative;
    z-index:1;
}

.xyn-profile-info h2{
    font-size:22px;
    font-weight:900;
    margin:0 0 5px;
}

.xyn-profile-info p{
    color:#9ca3af;
    margin:3px 0;
    font-size:14px;
}

.xyn-badge{
    display:inline-block;
    margin-top:8px;
    padding:6px 12px;
    border-radius:999px;
    font-size:11px;
    font-weight:900;
}

.xyn-badge.verified{
    background:rgba(16,185,129,.12);
    color:#34d399;
    border:1px solid rgba(16,185,129,.25);
}

.xyn-badge.unverified{
    background:rgba(245,158,11,.13);
    color:#fbbf24;
    border:1px solid rgba(245,158,11,.25);
}

.xyn-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:22px;
}

.xyn-section{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:28px;
    padding:24px;
    position:relative;
    overflow:hidden;
}

.xyn-section::before{
    content:'';
    position:absolute;
    right:-60px;
    top:-60px;
    width:160px;
    height:160px;
    border-radius:50%;
    background:rgba(124,92,252,.08);
}

.xyn-section-title{
    font-size:18px;
    font-weight:900;
    margin-bottom:18px;
    position:relative;
    z-index:1;
}

.xyn-pay-list{
    display:flex;
    flex-direction:column;
    gap:10px;
    position:relative;
    z-index:1;
}

.xyn-pay-row{
    display:flex;
    justify-content:space-between;
    gap:15px;
    background:#131929;
    border:1px solid rgba(255,255,255,.07);
    border-radius:15px;
    padding:13px 14px;
}

.xyn-pay-row span{
    color:#6b7280;
    font-size:13px;
    font-weight:900;
}

.xyn-pay-row strong{
    color:#e5e7eb;
    font-size:13px;
    text-align:right;
    word-break:break-word;
}

.xyn-form-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
    position:relative;
    z-index:1;
}

.xyn-form-grid.two{
    grid-template-columns:repeat(2,minmax(0,1fr));
}

.xyn-field label{
    font-size:12px;
    font-weight:900;
    color:#9ca3af;
    text-transform:uppercase;
    letter-spacing:.08em;
}

.xyn-field input,
.xyn-field textarea{
    width:100%;
    margin-top:8px;
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
    margin-top:16px;
}

.xyn-btn.primary{
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    color:#fff;
}

.xyn-btn.danger{
    background:#dc2626;
    color:#fff;
}

.xyn-ticket-table-wrap{
    overflow-x:auto;
    position:relative;
    z-index:1;
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
    padding:14px 12px;
    border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
    vertical-align:top;
}

.xyn-ticket-table td:first-child{
    border-left:1px solid rgba(255,255,255,.07);
    border-radius:15px 0 0 15px;
}

.xyn-ticket-table td:last-child{
    border-right:1px solid rgba(255,255,255,.07);
    border-radius:0 15px 15px 0;
}

.xyn-status{
    display:inline-block;
    padding:6px 11px;
    border-radius:999px;
    font-size:11px;
    font-weight:900;
}

.xyn-status.open,
.xyn-status.pending{
    background:rgba(245,158,11,.13);
    color:#fbbf24;
}

.xyn-status.closed,
.xyn-status.resolved,
.xyn-status.approved{
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
    padding:34px!important;
}

.xyn-logout-card{
    background:linear-gradient(135deg,rgba(239,68,68,.12),rgba(127,29,29,.14));
    border:1px solid rgba(239,68,68,.22);
    border-radius:28px;
    padding:22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
}

.xyn-logout-card h3{
    font-size:18px;
    font-weight:900;
    margin:0 0 4px;
}

.xyn-logout-card p{
    color:#fca5a5;
    font-size:13px;
    margin:0;
}

@media(max-width:1100px){
    .xyn-grid{
        grid-template-columns:1fr;
    }

    .xyn-form-grid,
    .xyn-form-grid.two{
        grid-template-columns:1fr;
    }
}

@media(max-width:650px){
    .xyn-profile-card{
        flex-direction:column;
        text-align:center;
    }

    .xyn-logout-card{
        flex-direction:column;
        align-items:flex-start;
    }

    .xyn-hero-title{
        font-size:25px;
    }
}
</style>
@endpush

@section('content')
@php
    $passwordRoute = $user->role === 'merchant'
        ? route('merchant.settings.password')
        : route('client.settings.password');

    $supportRoute = $user->role === 'merchant'
        ? route('merchant.settings.support')
        : route('client.settings.support');

    $photo = $user->photo ? url('/api/storage/'.$user->photo) : null;
@endphp

<div class="xyn-settings-page">

    <div class="xyn-hero">
        <div class="xyn-hero-title">
            Account <span>Settings</span>
        </div>
        <div class="xyn-hero-sub">
            Manage profile security, payment details and support tickets.
        </div>
    </div>

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

    <div class="xyn-profile-card">
        <div class="xyn-avatar-ring">
            @if($photo)
                <img src="{{ $photo }}" alt="Profile Photo">
            @else
                <div class="xyn-avatar-inner">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="xyn-profile-info">
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
            <p>{{ $user->phone ?? '-' }}</p>

            @if($user->is_verified)
                <span class="xyn-badge verified">VERIFIED ACCOUNT</span>
            @else
                <span class="xyn-badge unverified">UNVERIFIED ACCOUNT</span>
            @endif
        </div>
    </div>

    <div class="xyn-grid">

        <div class="xyn-section">
            <div class="xyn-section-title">Payment Methods</div>

            <div class="xyn-pay-list">
                <div class="xyn-pay-row">
                    <span>Bank</span>
                    <strong>{{ $user->bank_name ?? '-' }}</strong>
                </div>

                <div class="xyn-pay-row">
                    <span>Branch</span>
                    <strong>{{ $user->branch ?? '-' }}</strong>
                </div>

                <div class="xyn-pay-row">
                    <span>Account No</span>
                    <strong>{{ $user->account_number ?? '-' }}</strong>
                </div>

                <div class="xyn-pay-row">
                    <span>Account Type</span>
                    <strong>{{ $user->account_type ?? '-' }}</strong>
                </div>

                <div class="xyn-pay-row">
                    <span>IFSC</span>
                    <strong>{{ $user->ifsc ?? '-' }}</strong>
                </div>

                <div class="xyn-pay-row">
                    <span>UPI Name</span>
                    <strong>{{ $user->upi_name ?? '-' }}</strong>
                </div>

                <div class="xyn-pay-row">
                    <span>UPI ID</span>
                    <strong>{{ $user->upi_id ?? '-' }}</strong>
                </div>
            </div>
        </div>

        <div class="xyn-section">
            <div class="xyn-section-title">Change Password</div>

            <form method="POST" action="{{ $passwordRoute }}">
                @csrf

                <div class="xyn-form-grid">
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
                </div>

                <button type="submit" class="xyn-btn primary">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <div class="xyn-section">
        <div class="xyn-section-title">Help & Support</div>

        <form method="POST" action="{{ $supportRoute }}">
            @csrf

            <div class="xyn-form-grid two">
                <div class="xyn-field">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="xyn-field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
            </div>

            <div class="xyn-field" style="margin-top:14px;">
                <label>Message</label>
                <textarea name="message" rows="4" required>{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="xyn-btn primary">
                Submit Ticket
            </button>
        </form>
    </div>

    <div class="xyn-section">
        <div class="xyn-section-title">Your Tickets</div>

        <div class="xyn-ticket-table-wrap">
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

    <div class="xyn-logout-card">
        <div>
            <h3>Logout Account</h3>
            <p>End your current session safely.</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="xyn-btn danger" style="margin-top:0;">
                Logout
            </button>
        </form>
    </div>

</div>
@endsection
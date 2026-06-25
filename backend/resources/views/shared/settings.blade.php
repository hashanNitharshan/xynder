@extends('layouts.admin', ['title' => $title ?? 'Settings'])

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

.set-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.set-hero{
    position:relative;
    min-height:330px;
    padding:65px 85px 120px;
    background:var(--hero);
    overflow:hidden;
}

.set-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.08;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.set-content{
    position:relative;
    z-index:2;
    max-width:650px;
}

.set-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.set-title{
    font-size:48px;
    line-height:1.15;
    font-weight:900;
    margin:0 0 20px;
}

.set-title span{color:var(--red)}

.set-sub{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
}

.set-profile{
    position:relative;
    z-index:5;
    margin:-70px 85px 0;
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    padding:24px;
    display:flex;
    align-items:center;
    gap:20px;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.set-avatar{
    width:90px;
    height:90px;
    border-radius:50%;
    background:var(--red);
    padding:4px;
    flex-shrink:0;
}

.set-avatar img,
.set-avatar-inner{
    width:100%;
    height:100%;
    border-radius:50%;
    object-fit:cover;
}

.set-avatar-inner{
    background:#1f2428;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:34px;
    font-weight:900;
    color:#fff;
}

.set-profile-info h2{
    margin:0 0 6px;
    font-size:24px;
    font-weight:900;
}

.set-profile-info p{
    margin:4px 0;
    color:#aeb4ba;
    font-size:14px;
    font-weight:700;
}

.set-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:9px;
    padding:6px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
}

.set-badge.verified{
    background:#0d2b1e;
    color:var(--green);
}

.set-badge.unverified{
    background:#3b2a09;
    color:var(--gold);
}

.set-alerts{
    margin:24px 85px 0;
}

.set-alert{
    padding:14px 16px;
    border-radius:4px;
    font-weight:800;
    margin-bottom:12px;
}

.set-alert.ok{
    background:#0d2b1e;
    color:var(--green);
    border:1px solid #1a4a35;
}

.set-alert.err{
    background:#3a1018;
    color:#ff6b7b;
    border:1px solid #71313a;
}

.set-grid{
    margin:26px 85px 0;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:22px;
}

.set-section{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:24px;
    overflow:hidden;
}

.set-section:hover{
    border-color:rgba(232,25,44,.55);
}

.set-section-title{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:18px;
    font-weight:900;
    margin-bottom:20px;
}

.set-section-title i{
    color:var(--red);
    font-size:22px;
}

.set-pay-list{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.set-pay-row{
    display:flex;
    justify-content:space-between;
    gap:16px;
    background:#1f2428;
    border:1px solid var(--line);
    border-radius:4px;
    padding:13px 14px;
}

.set-pay-row span{
    color:#9fa5aa;
    font-size:13px;
    font-weight:900;
}

.set-pay-row strong{
    color:#fff;
    font-size:13px;
    text-align:right;
    word-break:break-word;
}

.set-form-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
}

.set-form-grid.two{
    grid-template-columns:repeat(2,minmax(0,1fr));
}

.set-field label{
    display:block;
    font-size:11px;
    color:#9fa5aa;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:8px;
}

.set-field input,
.set-field textarea{
    width:100%;
    background:#1f2428;
    border:1px solid var(--line);
    color:#fff;
    border-radius:4px;
    padding:13px 14px;
    outline:none;
    font-weight:700;
}

.set-field input:focus,
.set-field textarea:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.set-field textarea{
    resize:vertical;
    min-height:110px;
}

.set-btn{
    border:0;
    border-radius:4px;
    padding:13px 20px;
    font-weight:900;
    cursor:pointer;
    margin-top:16px;
    display:inline-flex;
    align-items:center;
    gap:8px;
}

.set-btn.primary{
    background:var(--red);
    color:#fff;
}

.set-btn.primary:hover{
    background:var(--red2);
}

.set-btn.danger{
    background:var(--red);
    color:#fff;
}

.set-btn.danger:hover{
    background:var(--red2);
}

.set-full{
    margin:22px 85px 0;
}

.set-table-wrap{
    overflow-x:auto;
}

.set-table{
    width:100%;
    min-width:760px;
    border-collapse:collapse;
}

.set-table th{
    color:#9fa5aa;
    font-size:11px;
    text-transform:uppercase;
    text-align:left;
    padding:14px 16px;
    background:#24292d;
}

.set-table td{
    padding:16px;
    border-top:1px solid #3a4147;
    color:#c9ced3;
    font-size:13px;
    font-weight:700;
    vertical-align:top;
}

.set-table tr:hover td{
    background:#30363a;
}

.set-status{
    display:inline-block;
    padding:6px 11px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
}

.set-status.open,
.set-status.pending{
    background:#3b2a09;
    color:var(--gold);
}

.set-status.closed,
.set-status.resolved,
.set-status.approved{
    background:#0d2b1e;
    color:var(--green);
}

.set-status.rejected{
    background:#3a1018;
    color:#ff6b7b;
}

.set-empty{
    text-align:center;
    padding:40px!important;
    color:#9fa5aa!important;
}

.set-logout{
    margin:22px 85px 0;
    background:#3a1018;
    border:1px solid rgba(232,25,44,.45);
    border-radius:6px;
    padding:22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
}

.set-logout h3{
    margin:0 0 5px;
    font-size:19px;
    font-weight:900;
}

.set-logout p{
    margin:0;
    color:#ffb4bc;
    font-size:13px;
    font-weight:700;
}

@media(max-width:1100px){
    .set-grid{
        grid-template-columns:1fr;
    }

    .set-form-grid,
    .set-form-grid.two{
        grid-template-columns:1fr;
    }
}

@media(max-width:760px){
    .set-page{margin:-16px}

    .set-hero{
        padding:45px 24px 110px;
    }

    .set-title{
        font-size:36px;
    }

    .set-profile,
    .set-alerts,
    .set-grid,
    .set-full,
    .set-logout{
        margin-left:20px;
        margin-right:20px;
    }

    .set-profile{
        flex-direction:column;
        text-align:center;
    }

    .set-logout{
        flex-direction:column;
        align-items:flex-start;
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

<div class="set-page">

    <section class="set-hero">
        <div class="set-content">
            <div class="set-eyebrow">Xynder Wallet</div>

            <h1 class="set-title">
                Account
                <span>Settings</span>
            </h1>

            <div class="set-sub">
                Manage your profile security, payment details, support tickets, and account session safely.
            </div>
        </div>
    </section>

    <section class="set-profile">
        <div class="set-avatar">
            @if($photo)
                <img src="{{ $photo }}" alt="Profile Photo">
            @else
                <div class="set-avatar-inner">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="set-profile-info">
            <h2>{{ $user->name }}</h2>
            <p><i class="ti ti-mail"></i> {{ $user->email }}</p>
            <p><i class="ti ti-phone"></i> {{ $user->phone ?? '-' }}</p>

            @if($user->is_verified)
                <span class="set-badge verified">
                    <i class="ti ti-circle-check"></i>
                    VERIFIED ACCOUNT
                </span>
            @else
                <span class="set-badge unverified">
                    <i class="ti ti-alert-circle"></i>
                    UNVERIFIED ACCOUNT
                </span>
            @endif
        </div>
    </section>

    <div class="set-alerts">
        @if(session('success'))
            <div class="set-alert ok">
                <i class="ti ti-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="set-alert err">
                <i class="ti ti-alert-triangle"></i>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
    </div>

    <section class="set-grid">

        <div class="set-section">
            <div class="set-section-title">
                <i class="ti ti-credit-card"></i>
                Payment Methods
            </div>

            <div class="set-pay-list">
                <div class="set-pay-row">
                    <span>Bank</span>
                    <strong>{{ $user->bank_name ?? '-' }}</strong>
                </div>

                <div class="set-pay-row">
                    <span>Branch</span>
                    <strong>{{ $user->branch ?? '-' }}</strong>
                </div>

                <div class="set-pay-row">
                    <span>Account No</span>
                    <strong>{{ $user->account_number ?? '-' }}</strong>
                </div>

                <div class="set-pay-row">
                    <span>Account Type</span>
                    <strong>{{ $user->account_type ?? '-' }}</strong>
                </div>

                <div class="set-pay-row">
                    <span>IFSC</span>
                    <strong>{{ $user->ifsc ?? '-' }}</strong>
                </div>

                <div class="set-pay-row">
                    <span>UPI Name</span>
                    <strong>{{ $user->upi_name ?? '-' }}</strong>
                </div>

                <div class="set-pay-row">
                    <span>UPI ID</span>
                    <strong>{{ $user->upi_id ?? '-' }}</strong>
                </div>
            </div>
        </div>

        <div class="set-section">
            <div class="set-section-title">
                <i class="ti ti-lock-password"></i>
                Change Password
            </div>

            <form method="POST" action="{{ $passwordRoute }}">
                @csrf

                <div class="set-form-grid">
                    <div class="set-field">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>

                    <div class="set-field">
                        <label>New Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="set-field">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" required>
                    </div>
                </div>

                <button type="submit" class="set-btn primary">
                    <i class="ti ti-shield-check"></i>
                    Update Password
                </button>
            </form>
        </div>

    </section>

    <section class="set-section set-full">
        <div class="set-section-title">
            <i class="ti ti-headset"></i>
            Help & Support
        </div>

        <form method="POST" action="{{ $supportRoute }}">
            @csrf

            <div class="set-form-grid two">
                <div class="set-field">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="set-field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
            </div>

            <div class="set-field" style="margin-top:14px;">
                <label>Message</label>
                <textarea name="message" rows="4" required>{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="set-btn primary">
                <i class="ti ti-send"></i>
                Submit Ticket
            </button>
        </form>
    </section>

    <section class="set-section set-full">
        <div class="set-section-title">
            <i class="ti ti-ticket"></i>
            Your Tickets
        </div>

        <div class="set-table-wrap">
            <table class="set-table">
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
                                <span class="set-status {{ strtolower($ticket->status) }}">
                                    {{ strtoupper($ticket->status) }}
                                </span>
                            </td>

                            <td style="color:#9fa5aa;">
                                {{ $ticket->created_at?->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="set-empty">
                                No support tickets yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="set-logout">
        <div>
            <h3>
                <i class="ti ti-logout"></i>
                Logout Account
            </h3>
            <p>End your current session safely.</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="set-btn danger" style="margin-top:0;">
                <i class="ti ti-power"></i>
                Logout
            </button>
        </form>
    </section>

</div>
@endsection
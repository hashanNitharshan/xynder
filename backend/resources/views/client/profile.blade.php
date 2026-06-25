@extends('layouts.admin', ['title' => 'My Profile'])

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

.profile-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.profile-hero{
    position:relative;
    min-height:330px;
    padding:65px 85px 120px;
    background:var(--hero);
    overflow:hidden;
}

.profile-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.08;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.profile-hero-content{
    position:relative;
    z-index:2;
    max-width:650px;
}

.profile-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.profile-title{
    font-size:48px;
    line-height:1.15;
    font-weight:900;
    margin:0 0 20px;
}

.profile-title span{color:var(--red)}

.profile-sub{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
}

.profile-wrap{
    position:relative;
    z-index:5;
    margin:-70px 85px 0;
    display:flex;
    flex-direction:column;
    gap:22px;
}

.profile-card{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    padding:24px;
    display:flex;
    align-items:center;
    gap:20px;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.profile-avatar{
    width:94px;
    height:94px;
    border-radius:50%;
    background:var(--red);
    padding:4px;
    flex-shrink:0;
}

.profile-avatar img,
.profile-avatar-inner{
    width:100%;
    height:100%;
    border-radius:50%;
    object-fit:cover;
}

.profile-avatar-inner{
    background:#1f2428;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:36px;
    font-weight:900;
    color:#fff;
}

.profile-info{
    min-width:0;
}

.profile-info h2{
    margin:0 0 6px;
    color:#fff;
    font-size:24px;
    font-weight:900;
}

.profile-info p{
    margin:4px 0;
    color:#aeb4ba;
    font-size:14px;
    font-weight:700;
    word-break:break-word;
}

.profile-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:9px;
    padding:6px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
}

.profile-badge.verified{
    background:#0d2b1e;
    color:var(--green);
}

.profile-badge.unverified{
    background:#3b2a09;
    color:var(--gold);
}

.profile-section{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:24px;
}

.profile-section:hover{
    border-color:rgba(232,25,44,.55);
}

.profile-section-title{
    display:flex;
    align-items:center;
    gap:10px;
    margin:0 0 20px;
    color:#fff;
    font-size:19px;
    font-weight:900;
}

.profile-section-title i{
    color:var(--red);
    font-size:22px;
}

.profile-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:15px;
}

.profile-grid.two{
    grid-template-columns:repeat(2,minmax(0,1fr));
}

.profile-field{
    margin-bottom:15px;
}

.profile-field label{
    display:block;
    color:#9fa5aa;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:8px;
}

.profile-field input,
.profile-field textarea{
    width:100%;
    background:#1f2428;
    border:1px solid var(--line);
    color:#fff;
    border-radius:4px;
    padding:13px 14px;
    outline:none;
    font-weight:700;
}

.profile-field input:focus,
.profile-field textarea:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.profile-field textarea{
    resize:vertical;
    min-height:100px;
}

.profile-file-preview{
    margin:10px 0 12px;
    background:#1f2428;
    border:1px solid var(--line);
    border-radius:6px;
    padding:12px;
    display:inline-block;
}

.profile-file-preview img{
    max-width:240px;
    max-height:240px;
    border-radius:6px;
    border:1px solid var(--line);
    display:block;
    object-fit:cover;
}

.profile-save{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    padding:22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    box-shadow:0 18px 40px rgba(0,0,0,.18);
}

.profile-save h3{
    margin:0 0 5px;
    font-size:19px;
    font-weight:900;
    color:#fff;
}

.profile-save p{
    margin:0;
    color:#aeb4ba;
    font-size:13px;
    font-weight:700;
}

.profile-btn{
    border:0;
    border-radius:4px;
    background:var(--red);
    color:#fff;
    padding:14px 24px;
    font-weight:900;
    cursor:pointer;
    white-space:nowrap;
    display:inline-flex;
    align-items:center;
    gap:8px;
}

.profile-btn:hover{
    background:var(--red2);
}

@media(max-width:1100px){
    .profile-grid,
    .profile-grid.two{
        grid-template-columns:1fr;
    }
}

@media(max-width:760px){
    .profile-page{margin:-16px}

    .profile-hero{
        padding:45px 24px 110px;
    }

    .profile-title{
        font-size:36px;
    }

    .profile-wrap{
        margin-left:20px;
        margin-right:20px;
    }

    .profile-card{
        flex-direction:column;
        text-align:center;
    }

    .profile-save{
        flex-direction:column;
        align-items:flex-start;
    }

    .profile-btn{
        width:100%;
        justify-content:center;
    }
}
</style>
@endpush

@section('content')
@php
    $profileRoute = $user->role === 'merchant'
        ? route('merchant.profile.update')
        : route('client.profile.update');

    $photo = $user->photo ? url('/api/storage/'.$user->photo) : null;
    $aadhaarPhoto = $user->aadhaar_photo ? url('/api/storage/'.$user->aadhaar_photo) : null;
    $upiQr = $user->upi_qr ? url('/api/storage/'.$user->upi_qr) : null;
@endphp

<form method="POST" action="{{ $profileRoute }}" enctype="multipart/form-data">
    @csrf

    <div class="profile-page">

        <section class="profile-hero">
            <div class="profile-hero-content">
                <div class="profile-eyebrow">Xynder Wallet</div>

                <h1 class="profile-title">
                    My
                    <span>Profile</span>
                </h1>

                <div class="profile-sub">
                    Update your personal details, KYC document, bank information, and UPI payment details.
                </div>
            </div>
        </section>

        <div class="profile-wrap">

            <div class="profile-card">
                <div class="profile-avatar">
                    @if($photo)
                        <img src="{{ $photo }}" alt="Profile Photo">
                    @else
                        <div class="profile-avatar-inner">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="profile-info">
                    <h2>{{ $user->name }}</h2>
                    <p><i class="ti ti-mail"></i> {{ $user->email }}</p>
                    <p><i class="ti ti-phone"></i> {{ $user->phone ?? '-' }}</p>

                    @if($user->is_verified)
                        <span class="profile-badge verified">
                            <i class="ti ti-circle-check"></i>
                            VERIFIED ACCOUNT
                        </span>
                    @else
                        <span class="profile-badge unverified">
                            <i class="ti ti-alert-circle"></i>
                            UNVERIFIED ACCOUNT
                        </span>
                    @endif
                </div>
            </div>

            <section class="profile-section">
                <h3 class="profile-section-title">
                    <i class="ti ti-user"></i>
                    Personal Details
                </h3>

                <div class="profile-grid">
                    <div class="profile-field">
                        <label>Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="profile-field">
                        <label>Original / Legal Name</label>
                        <input type="text" name="original_name" value="{{ old('original_name', $user->original_name) }}">
                    </div>

                    <div class="profile-field">
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="profile-field">
                        <label>Country</label>
                        <input type="text" name="country" value="{{ old('country', $user->country) }}">
                    </div>

                    <div class="profile-field">
                        <label>State</label>
                        <input type="text" name="state" value="{{ old('state', $user->state) }}">
                    </div>

                    <div class="profile-field">
                        <label>Profile Photo</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                </div>

                <div class="profile-field">
                    <label>Address</label>
                    <textarea name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                </div>

                <div class="profile-field">
                    <label>Aadhaar Card Photo</label>

                    @if($aadhaarPhoto)
                        <div class="profile-file-preview">
                            <img src="{{ $aadhaarPhoto }}" alt="Aadhaar Photo">
                        </div>
                    @endif

                    <input type="file" name="aadhaar_photo" accept="image/*">
                </div>
            </section>

            <section class="profile-section">
                <h3 class="profile-section-title">
                    <i class="ti ti-building-bank"></i>
                    Bank Details
                </h3>

                <div class="profile-grid">
                    <div class="profile-field">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
                    </div>

                    <div class="profile-field">
                        <label>Branch</label>
                        <input type="text" name="branch" value="{{ old('branch', $user->branch) }}">
                    </div>

                    <div class="profile-field">
                        <label>Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $user->account_number) }}">
                    </div>

                    <div class="profile-field">
                        <label>Account Type</label>
                        <input type="text" name="account_type" value="{{ old('account_type', $user->account_type) }}">
                    </div>

                    <div class="profile-field">
                        <label>IFSC</label>
                        <input type="text" name="ifsc" value="{{ old('ifsc', $user->ifsc) }}">
                    </div>
                </div>
            </section>

            <section class="profile-section">
                <h3 class="profile-section-title">
                    <i class="ti ti-qrcode"></i>
                    UPI Details
                </h3>

                <div class="profile-grid two">
                    <div class="profile-field">
                        <label>UPI Account Name</label>
                        <input type="text" name="upi_name" value="{{ old('upi_name', $user->upi_name) }}">
                    </div>

                    <div class="profile-field">
                        <label>UPI ID</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}">
                    </div>
                </div>

                <div class="profile-field">
                    <label>UPI QR</label>

                    @if($upiQr)
                        <div class="profile-file-preview">
                            <img src="{{ $upiQr }}" alt="UPI QR">
                        </div>
                    @endif

                    <input type="file" name="upi_qr" accept="image/*">
                </div>
            </section>

            <section class="profile-save">
                <div>
                    <h3>Save Profile Changes</h3>
                    <p>Update your latest profile, bank, KYC, and UPI details.</p>
                </div>

                <button type="submit" class="profile-btn">
                    <i class="ti ti-device-floppy"></i>
                    Save Profile
                </button>
            </section>

        </div>
    </div>
</form>
@endsection
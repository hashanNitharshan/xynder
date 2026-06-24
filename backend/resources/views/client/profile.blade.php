@extends('layouts.admin', ['title' => 'My Profile'])

@push('styles')
<style>
.xyn-profile-page{
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
    margin:0 0 6px;
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

.xyn-profile-card{
    background:linear-gradient(135deg,#1a1060,#071228);
    border:1px solid rgba(124,92,252,.28);
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
    background:radial-gradient(circle,rgba(6,182,212,.18),transparent 70%);
}

.xyn-avatar-ring{
    width:92px;
    height:92px;
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
    font-size:34px;
    font-weight:900;
}

.xyn-profile-info{
    position:relative;
    z-index:1;
    min-width:0;
}

.xyn-profile-info h2{
    font-size:23px;
    font-weight:900;
    margin:0 0 5px;
    color:#fff;
}

.xyn-profile-info p{
    color:#9ca3af;
    margin:4px 0;
    font-size:14px;
    word-break:break-word;
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
    font-size:19px;
    font-weight:900;
    margin:0 0 18px;
    position:relative;
    z-index:1;
    color:#fff;
}

.xyn-form-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:15px;
    position:relative;
    z-index:1;
}

.xyn-form-grid.two{
    grid-template-columns:repeat(2,minmax(0,1fr));
}

.xyn-field{
    position:relative;
    z-index:1;
    margin-bottom:15px;
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

.xyn-file-preview{
    margin:10px 0;
    background:#131929;
    border:1px solid rgba(255,255,255,.08);
    border-radius:18px;
    padding:12px;
    display:inline-block;
}

.xyn-file-preview img{
    max-width:230px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.08);
    display:block;
}

.xyn-save-bar{
    background:#0e1220;
    border:1px solid rgba(255,255,255,.07);
    border-radius:28px;
    padding:20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:18px;
}

.xyn-save-bar h3{
    margin:0 0 4px;
    font-size:18px;
    font-weight:900;
    color:#fff;
}

.xyn-save-bar p{
    margin:0;
    font-size:13px;
    color:#9ca3af;
}

.xyn-btn-save{
    border:0;
    border-radius:16px;
    padding:14px 22px;
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    color:#fff;
    font-weight:900;
    cursor:pointer;
    white-space:nowrap;
}

.xyn-btn-save:hover{
    opacity:.92;
}

@media(max-width:1100px){
    .xyn-form-grid,
    .xyn-form-grid.two{
        grid-template-columns:1fr;
    }
}

@media(max-width:700px){
    .xyn-profile-card{
        flex-direction:column;
        text-align:center;
    }

    .xyn-save-bar{
        flex-direction:column;
        align-items:flex-start;
    }

    .xyn-btn-save{
        width:100%;
    }

    .xyn-hero-title{
        font-size:25px;
    }
}
</style>
@endpush

@section('content')
@php
    $profileRoute = route('client.profile.update');

    $photo = $user->photo ? url('/api/storage/'.$user->photo) : null;
    $aadhaarPhoto = $user->aadhaar_photo ? url('/api/storage/'.$user->aadhaar_photo) : null;
    $upiQr = $user->upi_qr ? url('/api/storage/'.$user->upi_qr) : null;
@endphp

<form method="POST" action="{{ $profileRoute }}" enctype="multipart/form-data">
    @csrf

    <div class="xyn-profile-page">

      

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

        <div class="xyn-section">
            <h3 class="xyn-section-title">Personal Details</h3>

            <div class="xyn-form-grid">
                <div class="xyn-field">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="xyn-field">
                    <label>Original / Legal Name</label>
                    <input type="text" name="original_name" value="{{ old('original_name', $user->original_name) }}">
                </div>

                <div class="xyn-field">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                </div>

                <div class="xyn-field">
                    <label>Country</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}">
                </div>

                <div class="xyn-field">
                    <label>State</label>
                    <input type="text" name="state" value="{{ old('state', $user->state) }}">
                </div>

                <div class="xyn-field">
                    <label>Profile Photo</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
            </div>

            <div class="xyn-field">
                <label>Address</label>
                <textarea name="address" rows="3">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="xyn-field">
                <label>Aadhaar Card Photo</label>

                @if($aadhaarPhoto)
                    <div class="xyn-file-preview">
                        <img src="{{ $aadhaarPhoto }}" alt="Aadhaar Photo">
                    </div>
                @endif

                <input type="file" name="aadhaar_photo" accept="image/*">
            </div>
        </div>

        <div class="xyn-section">
            <h3 class="xyn-section-title">Bank Details</h3>

            <div class="xyn-form-grid">
                <div class="xyn-field">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
                </div>

                <div class="xyn-field">
                    <label>Branch</label>
                    <input type="text" name="branch" value="{{ old('branch', $user->branch) }}">
                </div>

                <div class="xyn-field">
                    <label>Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $user->account_number) }}">
                </div>

                <div class="xyn-field">
                    <label>Account Type</label>
                    <input type="text" name="account_type" value="{{ old('account_type', $user->account_type) }}">
                </div>

                <div class="xyn-field">
                    <label>IFSC</label>
                    <input type="text" name="ifsc" value="{{ old('ifsc', $user->ifsc) }}">
                </div>
            </div>
        </div>

        <div class="xyn-section">
            <h3 class="xyn-section-title">UPI Details</h3>

            <div class="xyn-form-grid two">
                <div class="xyn-field">
                    <label>UPI Account Name</label>
                    <input type="text" name="upi_name" value="{{ old('upi_name', $user->upi_name) }}">
                </div>

                <div class="xyn-field">
                    <label>UPI ID</label>
                    <input type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}">
                </div>
            </div>

            <div class="xyn-field">
                <label>UPI QR</label>

                @if($upiQr)
                    <div class="xyn-file-preview">
                        <img src="{{ $upiQr }}" alt="UPI QR">
                    </div>
                @endif

                <input type="file" name="upi_qr" accept="image/*">
            </div>
        </div>

        <div class="xyn-save-bar">
            <div>
                <h3>Save Profile Changes</h3>
                <p>Update your latest profile, bank and UPI details.</p>
            </div>

            <button type="submit" class="xyn-btn-save">
                Save Profile
            </button>
        </div>

    </div>
</form>
@endsection
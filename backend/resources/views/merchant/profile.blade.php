@extends('layouts.admin', ['title' => 'My Profile'])

@push('styles')
<style>
.xprof-page{margin:-24px;min-height:100vh;background:#101518;color:#fff;font-family:Inter,Arial,sans-serif;padding-bottom:60px}
.xprof-hero{position:relative;min-height:330px;padding:65px 85px 120px;background:#2b2f32;overflow:hidden}
.xprof-hero:after{content:"";position:absolute;inset:0;opacity:.08;background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);background-size:260px 260px}
.xprof-hero-content{position:relative;z-index:2;max-width:650px}
.xprof-eyebrow{color:#e8192c;font-size:12px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;margin-bottom:14px}
.xprof-title{font-size:48px;line-height:1.15;font-weight:900;margin:0 0 20px}
.xprof-title span{color:#e8192c}
.xprof-sub{color:#b8bdc2;font-size:15px;line-height:1.7;font-weight:700}

.xprof-wrap{position:relative;z-index:5;margin:-70px 85px 0;display:flex;flex-direction:column;gap:22px}
.xprof-card{background:#2b2f32;border:1.5px solid #e8192c;border-radius:7px;padding:24px;display:flex;align-items:center;gap:20px;box-shadow:0 18px 40px rgba(0,0,0,.28)}
.xprof-avatar{width:94px;height:94px;border-radius:50%;background:#e8192c;padding:4px;flex-shrink:0}
.xprof-avatar img,.xprof-avatar-inner{width:100%;height:100%;border-radius:50%;object-fit:cover}
.xprof-avatar-inner{background:#1f2428;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:900;color:#fff}
.xprof-info h2{margin:0 0 6px;color:#fff;font-size:24px;font-weight:900}
.xprof-info p{margin:4px 0;color:#aeb4ba;font-size:14px;font-weight:700;word-break:break-word}
.xprof-badge{display:inline-flex;align-items:center;gap:6px;margin-top:9px;padding:6px 12px;border-radius:20px;font-size:11px;font-weight:900}
.xprof-badge.verified{background:#0d2b1e;color:#0ecb81}
.xprof-badge.unverified{background:#3b2a09;color:#ffc933}

.xprof-section{background:#2b2f32;border:1px solid #3b4248;border-radius:6px;padding:24px}
.xprof-section:hover{border-color:rgba(232,25,44,.55)}
.xprof-section-title{display:flex;align-items:center;gap:10px;margin:0 0 20px;color:#fff;font-size:19px;font-weight:900}
.xprof-section-title i{color:#e8192c;font-size:22px}
.xprof-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}
.xprof-grid.two{grid-template-columns:repeat(2,minmax(0,1fr))}
.xprof-field{margin-bottom:15px}
.xprof-field label{display:block;color:#9fa5aa;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}
.xprof-field input,.xprof-field textarea{width:100%;background:#1f2428;border:1px solid #3b4248;color:#fff;border-radius:4px;padding:13px 14px;outline:none;font-weight:700}
.xprof-field input:focus,.xprof-field textarea:focus{border-color:#e8192c;box-shadow:0 0 0 3px rgba(232,25,44,.12)}
.xprof-field textarea{resize:vertical;min-height:100px}
.xprof-preview{margin:10px 0 12px;background:#1f2428;border:1px solid #3b4248;border-radius:6px;padding:12px;display:inline-block}
.xprof-preview img{max-width:240px;max-height:240px;border-radius:6px;border:1px solid #3b4248;display:block;object-fit:cover}

.xprof-save{background:#2b2f32;border:1.5px solid #e8192c;border-radius:7px;padding:22px;display:flex;align-items:center;justify-content:space-between;gap:20px;box-shadow:0 18px 40px rgba(0,0,0,.18)}
.xprof-save h3{margin:0 0 5px;font-size:19px;font-weight:900;color:#fff}
.xprof-save p{margin:0;color:#aeb4ba;font-size:13px;font-weight:700}
.xprof-save-btn{border:0;border-radius:4px;background:#e8192c;color:#fff;padding:14px 24px;font-weight:900;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;gap:8px}
.xprof-save-btn:hover{background:#c91022}

@media(max-width:1100px){
    .xprof-grid,.xprof-grid.two{grid-template-columns:1fr}
}
@media(max-width:760px){
    .xprof-page{margin:-16px}
    .xprof-hero{padding:45px 24px 110px}
    .xprof-title{font-size:36px}
    .xprof-wrap{margin-left:20px;margin-right:20px}
    .xprof-card{flex-direction:column;text-align:center}
    .xprof-save{flex-direction:column;align-items:flex-start}
    .xprof-save-btn{width:100%;justify-content:center}
}
</style>
@endpush

@section('content')
@php
    $profileRoute = $user->role === 'merchant'
        ? route('merchant.profile.update')
        : route('client.profile.update');
$photo = $user->photo_url;
$aadhaarPhoto = $user->aadhaar_photo_url;
$upiQr = $user->upi_qr_url;
@endphp

<form method="POST" action="{{ $profileRoute }}" enctype="multipart/form-data">
    @csrf

    <div class="xprof-page">
        <section class="xprof-hero">
            <div class="xprof-hero-content">
                <div class="xprof-eyebrow">Xynder Wallet</div>
                <h1 class="xprof-title">My <span>Profile</span></h1>
                <div class="xprof-sub">
                    Update your personal details, KYC document, bank information, and UPI payment details.
                </div>
            </div>
        </section>

        <div class="xprof-wrap">
            <div class="xprof-card">
                <div class="xprof-avatar">
                    @if($photo)
                        <img src="{{ $photo }}" alt="Profile Photo">
                    @else
                        <div class="xprof-avatar-inner">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
                    @endif
                </div>

                <div class="xprof-info">
                    <h2>{{ $user->name }}</h2>
                    <p><i class="ti ti-mail"></i> {{ $user->email }}</p>
                    <p><i class="ti ti-phone"></i> {{ $user->phone ?? '-' }}</p>

                    @if($user->is_verified)
                        <span class="xprof-badge verified"><i class="ti ti-circle-check"></i> VERIFIED ACCOUNT</span>
                    @else
                        <span class="xprof-badge unverified"><i class="ti ti-alert-circle"></i> UNVERIFIED ACCOUNT</span>
                    @endif
                </div>
            </div>

            <section class="xprof-section">
                <h3 class="xprof-section-title"><i class="ti ti-user"></i> Personal Details</h3>

                <div class="xprof-grid">
                    <div class="xprof-field">
                        <label>Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="xprof-field">
                        <label>Original / Legal Name</label>
                        <input type="text" name="original_name" value="{{ old('original_name', $user->original_name) }}">
                    </div>

                    <div class="xprof-field">
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="xprof-field">
                        <label>Country</label>
                        <input type="text" name="country" value="{{ old('country', $user->country) }}">
                    </div>

                    <div class="xprof-field">
                        <label>State</label>
                        <input type="text" name="state" value="{{ old('state', $user->state) }}">
                    </div>

                    <div class="xprof-field">
                        <label>Profile Photo</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                </div>

                <div class="xprof-field">
                    <label>Address</label>
                    <textarea name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                </div>

                <div class="xprof-field">
                    <label>Aadhaar Card Photo</label>
                    @if($aadhaarPhoto)
                        <div class="xprof-preview"><img src="{{ $aadhaarPhoto }}" alt="Aadhaar Photo"></div>
                    @endif
                    <input type="file" name="aadhaar_photo" accept="image/*">
                </div>
            </section>

            <section class="xprof-section">
                <h3 class="xprof-section-title"><i class="ti ti-building-bank"></i> Bank Details</h3>

                <div class="xprof-grid">
                    <div class="xprof-field">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
                    </div>

                    <div class="xprof-field">
                        <label>Branch</label>
                        <input type="text" name="branch" value="{{ old('branch', $user->branch) }}">
                    </div>

                    <div class="xprof-field">
                        <label>Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $user->account_number) }}">
                    </div>

                    <div class="xprof-field">
                        <label>Account Type</label>
                        <input type="text" name="account_type" value="{{ old('account_type', $user->account_type) }}">
                    </div>

                    <div class="xprof-field">
                        <label>IFSC</label>
                        <input type="text" name="ifsc" value="{{ old('ifsc', $user->ifsc) }}">
                    </div>
                </div>
            </section>

            <section class="xprof-section">
                <h3 class="xprof-section-title"><i class="ti ti-qrcode"></i> UPI Details</h3>

                <div class="xprof-grid two">
                    <div class="xprof-field">
                        <label>UPI Account Name</label>
                        <input type="text" name="upi_name" value="{{ old('upi_name', $user->upi_name) }}">
                    </div>

                    <div class="xprof-field">
                        <label>UPI ID</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}">
                    </div>
                </div>

                <div class="xprof-field">
                    <label>UPI QR</label>
                    @if($upiQr)
                        <div class="xprof-preview"><img src="{{ $upiQr }}" alt="UPI QR"></div>
                    @endif
                    <input type="file" name="upi_qr" accept="image/*">
                </div>
            </section>

            <section class="xprof-save">
                <div>
                    <h3>Save Profile Changes</h3>
                    <p>Update your latest profile, bank, KYC, and UPI details.</p>
                </div>

                <button type="submit" class="xprof-save-btn">
                    <i class="ti ti-device-floppy"></i>
                    Save Profile
                </button>
            </section>
        </div>
    </div>
</form>
@endsection
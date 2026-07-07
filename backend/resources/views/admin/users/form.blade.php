@extends('layouts.admin', ['title' => $user->exists ? 'Edit User' : 'Add User'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{--bg:#0B0E11;--surface:#181A20;--surface-alt:#1E2329;--border:#2B3139;--yellow:#F0B90B;--gold:#FFD45A;--green:#0ecb81;--red:#ef4444;--text:#fff;--muted:#848E9C;--shadow:0 18px 45px rgba(0,0,0,.35)}
*{box-sizing:border-box}
.uf-page{margin:-28px;min-height:100vh;background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;padding-bottom:60px}
.uf-hero{background:var(--surface);padding:65px 85px 120px;position:relative;overflow:hidden}
.uf-hero::after{content:"";position:absolute;inset:0;opacity:.07;background-image:linear-gradient(120deg,transparent 20%,rgba(240,185,11,.18) 21%,transparent 22%);background-size:260px 260px}
.uf-hero-inner{position:relative;z-index:2;max-width:680px}
.uf-eyebrow{color:var(--yellow);font-size:12px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;margin-bottom:14px}
.uf-title{font-size:32px;line-height:1.12;font-weight:900;margin:0 0 18px}
.uf-title span{color:var(--yellow);display:block}
.uf-subtitle{color:#b8bdc2;font-size:13px;line-height:1.7;font-weight:700;max-width:560px}
.uf-wrap{position:relative;z-index:5;max-width:1180px;margin:-82px auto 0;padding:0 24px}
.uf-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:0 18px 40px rgba(0,0,0,.28)}
.uf-head{background:var(--surface-alt);border-bottom:1px solid var(--border);padding:18px 22px;display:flex;justify-content:space-between;align-items:center;gap:15px}
.uf-head-title{margin:0;font-size:15px;font-weight:900;display:flex;align-items:center;gap:10px}
.uf-head-title i{color:var(--yellow)}
.uf-body{padding:32px}
.uf-error{background:rgba(240,185,11,.12);border:1px solid rgba(240,185,11,.35);color:#FFD45A;padding:14px 16px;border-radius:9px;margin-bottom:22px;font-weight:800}
.uf-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.uf-section{grid-column:1/-1;margin-top:12px;padding:14px 0 10px;border-bottom:1px solid var(--border);color:var(--yellow);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;display:flex;align-items:center;gap:8px}
.uf-group{display:flex;flex-direction:column;gap:8px}
.uf-group.full{grid-column:1/-1}
.uf-label{font-size:12px;font-weight:900;color:#c3c6ca}
.uf-input,.uf-select{width:100%;height:46px;background:var(--surface-alt);border:1px solid var(--border);color:var(--text);border-radius:9px;padding:0 14px;outline:0;font-weight:700}
.uf-input:focus,.uf-select:focus{border-color:var(--yellow);box-shadow:0 0 0 3px rgba(240,185,11,.12)}
.uf-file{height:auto;padding:11px 14px}
.uf-preview{margin-top:10px;width:140px;height:95px;border-radius:8px;object-fit:cover;border:1px solid var(--border);background:var(--surface-alt)}
.uf-preview.round{width:96px;height:96px;border-radius:50%}
.uf-bank-box{border:1px solid var(--border);background:var(--surface-alt);border-radius:12px;padding:16px;margin-bottom:14px}
.uf-badge{background:#3b2a09;color:var(--gold);padding:7px 12px;border-radius:20px;font-size:11px;font-weight:900;display:inline-flex}
.uf-badge.normal{background:var(--surface);color:var(--muted)}
.modal-foot-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.uf-actions{margin-top:28px;padding-top:22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap}
.uf-btn{border:0;border-radius:9px;padding:12px 18px;font-weight:900;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;font-size:13px}
.uf-btn-red{background:var(--yellow);color:#0B0E11}
.uf-btn-red:hover{background:var(--gold);color:#0B0E11}
.uf-btn-dark{background:var(--surface-alt);color:#fff;border:1px solid var(--border)}
.uf-btn-dark:hover{border-color:var(--yellow);color:#fff}
@media(max-width:768px){.uf-page{margin:-16px}.uf-hero{padding:45px 24px 110px}.uf-title{font-size:34px}.uf-wrap{margin:-76px auto 0;padding:0 18px}.uf-body{padding:24px 18px}.uf-grid{grid-template-columns:1fr}.uf-head{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="uf-page">

<section class="uf-hero">
    <div class="uf-hero-inner">
        <div class="uf-eyebrow">BITXNOW Admin</div>
        <h1 class="uf-title">
            {{ $user->exists ? 'Edit User' : 'Add User' }}
            <span>Client / Merchant</span>
        </h1>
        <div class="uf-subtitle">
            Manage profile photo, Aadhaar photo, KYC, wallet balance, bank details, UPI details, and account status.
        </div>
    </div>
</section>

<main class="uf-wrap">
<div class="uf-card">
<div class="uf-head">
    <h2 class="uf-head-title">
        <i class="ti {{ $user->exists ? 'ti-user-edit' : 'ti-user-plus' }}"></i>
        {{ $user->exists ? 'Edit Client / Merchant' : 'Add Client / Merchant' }}
    </h2>

    <a class="uf-btn uf-btn-dark" href="{{ route('admin.users.index') }}">
        <i class="ti ti-arrow-left"></i> Back
    </a>
</div>

<div class="uf-body">

@if($errors->any())
    <div class="uf-error">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" enctype="multipart/form-data" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
@csrf
@if($user->exists)
    @method('PUT')
@endif

<div class="uf-grid">

    <div class="uf-section"><i class="ti ti-id"></i> Basic Information</div>

    <div class="uf-group">
        <label class="uf-label">Full Name *</label>
        <input class="uf-input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
    </div>

    <div class="uf-group">
        <label class="uf-label">Original Name / Merchant Legal Name</label>
        <input class="uf-input" type="text" name="original_name" value="{{ old('original_name', $user->original_name) }}">
    </div>

    <div class="uf-group">
        <label class="uf-label">Email *</label>
        <input class="uf-input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
    </div>

    <div class="uf-group">
        <label class="uf-label">Role *</label>
        <select class="uf-select" name="role" required>
            <option value="client" @selected(old('role', $user->role) === 'client')>Client</option>
            <option value="merchant" @selected(old('role', $user->role) === 'merchant')>Merchant</option>
        </select>
    </div>

    <div class="uf-group">
        <label class="uf-label">Phone</label>
        <input class="uf-input" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
    </div>

    <div class="uf-group">
        <label class="uf-label">Balance *</label>
        <input class="uf-input" type="number" step="0.01" name="balance" value="{{ old('balance', $user->balance ?? 0) }}" required>
    </div>

    <div class="uf-group">
        <label class="uf-label">Password {{ $user->exists ? '(leave empty if no change)' : '*' }}</label>
        <input class="uf-input" type="password" name="password" {{ $user->exists ? '' : 'required' }}>
    </div>

    <div class="uf-group">
        <label class="uf-label">Account Status</label>
        <select class="uf-select" name="status">
            <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
            <option value="blocked" @selected(old('status', $user->status ?? 'active') === 'blocked')>Blocked</option>
        </select>
    </div>

    <div class="uf-section"><i class="ti ti-photo"></i> Photos / KYC</div>

    <div class="uf-group">
        <label class="uf-label">Profile Photo</label>
        <input class="uf-input uf-file" type="file" name="photo" accept="image/*">
        @if($user->photo)
            <img class="uf-preview round" src="{{ asset('storage/'.$user->photo) }}" alt="Profile photo">
        @endif
    </div>

    <div class="uf-group">
        <label class="uf-label">Aadhaar Photo</label>
        <input class="uf-input uf-file" type="file" name="aadhaar_photo" accept="image/*">
        @if($user->aadhaar_photo)
            <img class="uf-preview" src="{{ asset('storage/'.$user->aadhaar_photo) }}" alt="Aadhaar photo">
        @endif
    </div>

    <div class="uf-group">
        <label class="uf-label">NIC</label>
        <input class="uf-input" type="text" name="nic" value="{{ old('nic', $user->nic) }}">
    </div>

    <div class="uf-group">
        <label class="uf-label">Aadhaar Number</label>
        <input class="uf-input" type="text" name="aadhaar" value="{{ old('aadhaar', $user->aadhaar) }}">
    </div>

    <div class="uf-group full">
        <label class="uf-label">Card Number</label>
        <input class="uf-input" type="text" name="card_number" value="{{ old('card_number', $user->card_number) }}">
    </div>

    <div class="uf-section"><i class="ti ti-map-pin"></i> Address Details</div>

    <div class="uf-group">
        <label class="uf-label">Country</label>
        <input class="uf-input" type="text" name="country" value="{{ old('country', $user->country) }}">
    </div>

    <div class="uf-group">
        <label class="uf-label">State</label>
        <input class="uf-input" type="text" name="state" value="{{ old('state', $user->state) }}">
    </div>

    <div class="uf-group full">
        <label class="uf-label">Address</label>
        <input class="uf-input" type="text" name="address" value="{{ old('address', $user->address) }}">
    </div>

    @unless($user->exists)
        <div class="uf-section"><i class="ti ti-building-bank"></i> Bank Details</div>

        <div class="uf-group">
            <label class="uf-label">Bank Name</label>
            <input class="uf-input" type="text" name="bank_name" value="{{ old('bank_name') }}">
        </div>

        <div class="uf-group">
            <label class="uf-label">Branch</label>
            <input class="uf-input" type="text" name="branch" value="{{ old('branch') }}">
        </div>

        <div class="uf-group">
            <label class="uf-label">Account Number</label>
            <input class="uf-input" type="text" name="account_number" value="{{ old('account_number') }}">
        </div>

        <div class="uf-group">
            <label class="uf-label">Account Type</label>
            <input class="uf-input" type="text" name="account_type" value="{{ old('account_type') }}">
        </div>

        <div class="uf-group">
            <label class="uf-label">IFSC</label>
            <input class="uf-input" type="text" name="ifsc" value="{{ old('ifsc') }}">
        </div>
    @endunless

    <div class="uf-section"><i class="ti ti-brand-paypal"></i> UPI Details</div>

    <div class="uf-group">
        <label class="uf-label">UPI Account Name</label>
        <input class="uf-input" type="text" name="upi_name" value="{{ old('upi_name', $user->upi_name) }}">
    </div>

    <div class="uf-group">
        <label class="uf-label">UPI ID</label>
        <input class="uf-input" type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}">
    </div>

    <div class="uf-group full">
        <label class="uf-label">UPI QR Photo</label>
        <input class="uf-input uf-file" type="file" name="upi_qr" accept="image/*">
        @if($user->upi_qr)
            <img class="uf-preview" src="{{ asset('storage/'.$user->upi_qr) }}" alt="UPI QR photo">
        @endif
    </div>

</div>

<div class="uf-actions">
    <a class="uf-btn uf-btn-dark" href="{{ route('admin.users.index') }}">
        <i class="ti ti-x"></i> Cancel
    </a>

    <button class="uf-btn uf-btn-red" type="submit">
        <i class="ti {{ $user->exists ? 'ti-device-floppy' : 'ti-plus' }}"></i>
        {{ $user->exists ? 'Update User' : 'Create User' }}
    </button>
</div>

</form>

@if($user->exists)
    <div class="uf-section" style="margin-top:32px;"><i class="ti ti-building-bank"></i> All Bank Details</div>

    @forelse($user->bankAccounts as $bank)
        <div class="uf-bank-box">
            <form method="POST" action="{{ route('admin.users.bank.update', [$user, $bank]) }}">
                @csrf
                @method('PUT')

                <div class="uf-grid">
                    <div class="uf-group">
                        <label class="uf-label">Bank Name</label>
                        <input class="uf-input" type="text" name="bank_name" value="{{ old('bank_name', $bank->bank_name) }}" required>
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Branch</label>
                        <input class="uf-input" type="text" name="branch" value="{{ old('branch', $bank->branch) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Account Number</label>
                        <input class="uf-input" type="text" name="account_number" value="{{ old('account_number', $bank->account_number) }}" required>
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Account Type</label>
                        <input class="uf-input" type="text" name="account_type" value="{{ old('account_type', $bank->account_type) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">IFSC</label>
                        <input class="uf-input" type="text" name="ifsc" value="{{ old('ifsc', $bank->ifsc) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Default Status</label>
                        <div style="padding-top:12px;">
                            @if($bank->is_default)
                                <span class="uf-badge">DEFAULT</span>
                            @else
                                <span class="uf-badge normal">NORMAL</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-foot-actions" style="margin-top:14px;">
                    <button class="uf-btn uf-btn-red" type="submit">
                        <i class="ti ti-device-floppy"></i> Update Bank
                    </button>
            </form>

            @unless($bank->is_default)
                <form method="POST" action="{{ route('admin.users.bank.default', [$user, $bank]) }}">
                    @csrf
                    <button class="uf-btn uf-btn-dark" type="submit">
                        <i class="ti ti-star"></i> Make Default
                    </button>
                </form>
            @endunless

            <form method="POST" action="{{ route('admin.users.bank.destroy', [$user, $bank]) }}" onsubmit="return confirm('Delete this bank account?')">
                @csrf
                @method('DELETE')
                <button class="uf-btn uf-btn-dark" type="submit" style="color:#ff9b9b;">
                    <i class="ti ti-trash"></i> Delete
                </button>
            </form>
                </div>
        </div>
    @empty
        <div style="color:var(--muted);font-weight:800;margin-bottom:14px;">No bank accounts added yet.</div>
    @endforelse

    <div class="uf-section"><i class="ti ti-plus"></i> Add New Bank Account</div>

    <form method="POST" action="{{ route('admin.users.bank.store', $user) }}">
        @csrf

        <div class="uf-grid">
            <div class="uf-group">
                <label class="uf-label">Bank Name</label>
                <input class="uf-input" type="text" name="bank_name" required>
            </div>

            <div class="uf-group">
                <label class="uf-label">Branch</label>
                <input class="uf-input" type="text" name="branch">
            </div>

            <div class="uf-group">
                <label class="uf-label">Account Number</label>
                <input class="uf-input" type="text" name="account_number" required>
            </div>

            <div class="uf-group">
                <label class="uf-label">Account Type</label>
                <input class="uf-input" type="text" name="account_type">
            </div>

            <div class="uf-group">
                <label class="uf-label">IFSC</label>
                <input class="uf-input" type="text" name="ifsc">
            </div>

            <div class="uf-group">
                <label class="uf-label">Default</label>
                <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-weight:800;color:var(--muted);">
                    <input type="checkbox" name="is_default" value="1"> Set as default
                </label>
            </div>
        </div>

        <button class="uf-btn uf-btn-red" type="submit" style="margin-top:14px;">
            <i class="ti ti-plus"></i> Add Bank Account
        </button>
    </form>
@endif

</div>
</div>
</main>
</div>
@endsection
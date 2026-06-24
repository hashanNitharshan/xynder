@extends('layouts.admin', ['title' => $user->exists ? 'Edit User' : 'Add User'])

@section('content')

<style>
:root{
    --yellow:#facc15;
    --blue:#3b82f6;
    --red:#ef4444;
    --green:#22c55e;
    --page:#020617;
    --card:#0f172a;
    --border:#1e293b;
    --input:#111827;
    --text:#f8fafc;
    --muted:#94a3b8;
    --muted2:#cbd5e1;
}

.uf-page{
    background:var(--page);
    min-height:100vh;
    padding:20px;
    color:var(--text);
}

.uf-card{
    max-width:1100px;
    margin:auto;
    background:var(--card);
    border:1px solid var(--border);
    border-radius:18px;
    overflow:hidden;
}

.uf-head{
    padding:18px 22px;
    border-bottom:1px solid var(--border);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.uf-title{
    margin:0;
    font-size:22px;
    font-weight:900;
}

.uf-body{
    padding:22px;
}

.uf-error{
    background:rgba(239,68,68,.15);
    border:1px solid rgba(239,68,68,.35);
    color:#fca5a5;
    padding:12px;
    border-radius:12px;
    margin-bottom:18px;
}

.uf-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:14px;
}

.uf-section{
    grid-column:1/-1;
    margin-top:10px;
    padding-bottom:8px;
    border-bottom:1px solid var(--border);
    color:var(--yellow);
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
}

.uf-group{
    display:flex;
    flex-direction:column;
    gap:7px;
}

.uf-group.full{
    grid-column:1/-1;
}

.uf-label{
    font-size:12px;
    font-weight:800;
    color:var(--muted2);
}

.uf-input,
.uf-select{
    width:100%;
    height:42px;
    background:var(--input);
    border:1px solid #334155;
    color:var(--text);
    border-radius:12px;
    padding:0 13px;
    outline:none;
}

.uf-input:focus,
.uf-select:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(250,204,21,.12);
}

.uf-file{
    height:auto;
    padding:10px;
}

.uf-preview{
    margin-top:8px;
    width:130px;
    height:90px;
    border-radius:12px;
    object-fit:cover;
    border:1px solid var(--border);
}

.uf-actions{
    margin-top:22px;
    padding-top:18px;
    border-top:1px solid var(--border);
    display:flex;
    justify-content:flex-end;
    gap:10px;
}

.uf-btn{
    border:none;
    border-radius:12px;
    padding:11px 16px;
    font-weight:900;
    cursor:pointer;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.uf-btn-yellow{
    background:var(--yellow);
    color:#111827;
}

.uf-btn-gray{
    background:#1e293b;
    color:var(--text);
    border:1px solid #334155;
}

@media(max-width:768px){
    .uf-grid{grid-template-columns:1fr;}
    .uf-page{padding:12px;}
}
</style>

<div class="uf-page">
    <div class="uf-card">
        <div class="uf-head">
            <h2 class="uf-title">
                {{ $user->exists ? '✏️ Edit Client / Merchant' : '＋ Add Client / Merchant' }}
            </h2>

            <a class="uf-btn uf-btn-gray" href="{{ route('admin.users.index') }}">← Back</a>
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
                    <div class="uf-section">Basic Information</div>

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
                        <select class="uf-select" name="is_active">
                            <option value="1" @selected(old('is_active', $user->is_active ?? true) == true)>Active</option>
                            <option value="0" @selected(old('is_active', $user->is_active ?? true) == false)>Blocked</option>
                        </select>
                    </div>

                    <div class="uf-section">Address Details</div>

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

                    <div class="uf-section">KYC Details</div>

                    <div class="uf-group">
                        <label class="uf-label">Aadhaar Card Number</label>
                        <input class="uf-input" type="text" name="aadhaar" value="{{ old('aadhaar', $user->aadhaar) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Card Number</label>
                        <input class="uf-input" type="text" name="card_number" value="{{ old('card_number', $user->card_number) }}">
                    </div>

                    <div class="uf-section">Bank Details</div>

                    <div class="uf-group">
                        <label class="uf-label">Bank Name</label>
                        <input class="uf-input" type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Branch</label>
                        <input class="uf-input" type="text" name="branch" value="{{ old('branch', $user->branch) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Account Number</label>
                        <input class="uf-input" type="text" name="account_number" value="{{ old('account_number', $user->account_number) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Account Type</label>
                        <input class="uf-input" type="text" name="account_type" value="{{ old('account_type', $user->account_type) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">IFSC</label>
                        <input class="uf-input" type="text" name="ifsc" value="{{ old('ifsc', $user->ifsc) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">UPI Account Name</label>
                        <input class="uf-input" type="text" name="upi_name" value="{{ old('upi_name', $user->upi_name) }}">
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">UPI ID</label>
                        <input class="uf-input" type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}">
                    </div>

                    <div class="uf-section">Photos</div>

                    <div class="uf-group">
                        <label class="uf-label">Photo</label>
                        <input class="uf-input uf-file" type="file" name="photo" accept="image/*">

                        @if($user->photo)
                            <img class="uf-preview" src="{{ asset('storage/'.$user->photo) }}">
                        @endif
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">Aadhaar Card Photo</label>
                        <input class="uf-input uf-file" type="file" name="aadhaar_photo" accept="image/*">

                        @if($user->aadhaar_photo)
                            <img class="uf-preview" src="{{ asset('storage/'.$user->aadhaar_photo) }}">
                        @endif
                    </div>

                    <div class="uf-group">
                        <label class="uf-label">UPI QR Photo</label>
                        <input class="uf-input uf-file" type="file" name="upi_qr" accept="image/*">

                        @if($user->upi_qr)
                            <img class="uf-preview" src="{{ asset('storage/'.$user->upi_qr) }}">
                        @endif
                    </div>
                </div>

                <div class="uf-actions">
                    <a class="uf-btn uf-btn-gray" href="{{ route('admin.users.index') }}">Cancel</a>

                    <button class="uf-btn uf-btn-yellow" type="submit">
                        {{ $user->exists ? '💾 Update User' : '＋ Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
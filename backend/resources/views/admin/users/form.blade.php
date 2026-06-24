@extends('layouts.admin', ['title' => $user->exists ? 'Edit User' : 'Add User'])

@section('content')
<div class="card">
    <h2 style="margin-top:0;">{{ $user->exists ? 'Edit Client / Merchant' : 'Add Client / Merchant' }}</h2>

    @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:10px;margin-bottom:15px;">
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

        <div class="form-grid">
            <label>
                Full Name
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </label>

            <label>
                Original Name / Merchant Legal Name
                <input type="text" name="original_name" value="{{ old('original_name', $user->original_name) }}">
            </label>

            <label>
                Email
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </label>

            <label>
                Role
                <select name="role" required>
                    <option value="client" @selected(old('role', $user->role) === 'client')>Client</option>
                    <option value="merchant" @selected(old('role', $user->role) === 'merchant')>Merchant</option>
                </select>
            </label>

            <label>
                Phone
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
            </label>

            <label>
                Country
                <input type="text" name="country" value="{{ old('country', $user->country) }}">
            </label>

            <label>
                State
                <input type="text" name="state" value="{{ old('state', $user->state) }}">
            </label>

            <label>
                Address
                <input type="text" name="address" value="{{ old('address', $user->address) }}">
            </label>

        <label>
    Aadhaar Card Photo
    <input type="file" name="aadhaar_photo" accept="image/*">

    @if($user->aadhaar_photo)
        <div style="margin-top:8px;">
            <img src="{{ asset('storage/'.$user->aadhaar_photo) }}"
                 style="width:120px;height:80px;object-fit:cover;border-radius:10px;">
        </div>
    @endif
</label>

            <label>
                Aadhaar Card Number
                <input type="text" name="aadhaar" value="{{ old('aadhaar', $user->aadhaar) }}">
            </label>

            <label>
                Card Number
                <input type="text" name="card_number" value="{{ old('card_number', $user->card_number) }}">
            </label>

            <label>
                Photo
                <input type="file" name="photo" accept="image/*">
            </label>

            <label>
                Bank Name
                <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
            </label>

            <label>
                Branch
                <input type="text" name="branch" value="{{ old('branch', $user->branch) }}">
            </label>

            <label>
                Account Number
                <input type="text" name="account_number" value="{{ old('account_number', $user->account_number) }}">
            </label>

            <label>
                Account Type
                <input type="text" name="account_type" value="{{ old('account_type', $user->account_type) }}">
            </label>

            <label>
                IFSC
                <input type="text" name="ifsc" value="{{ old('ifsc', $user->ifsc) }}">
            </label>

            <label>
                UPI Account Name
                <input type="text" name="upi_name" value="{{ old('upi_name', $user->upi_name) }}">
            </label>

            <label>
                UPI ID
                <input type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}">
            </label>

            <label>
                UPI QR Photo
                <input type="file" name="upi_qr" accept="image/*">
            </label>

            <label>
                Balance
                <input type="number" step="0.01" name="balance" value="{{ old('balance', $user->balance ?? 0) }}" required>
            </label>

            <label>
                Password {{ $user->exists ? '(leave empty if no change)' : '' }}
                <input type="password" name="password" {{ $user->exists ? '' : 'required' }}>
            </label>

            <label>
                Account Status
                <select name="is_active">
                    <option value="1" @selected(old('is_active', $user->is_active ?? true) == true)>Active</option>
                    <option value="0" @selected(old('is_active', $user->is_active ?? true) == false)>Blocked</option>
                </select>
            </label>
        </div>

        <div class="actions" style="margin-top:16px;">
            <button class="btn btn-yellow" type="submit">
                {{ $user->exists ? 'Update User' : 'Create User' }}
            </button>

            <a class="btn btn-gray" href="{{ route('admin.users.index') }}">Back</a>
        </div>
    </form>
</div>
@endsection
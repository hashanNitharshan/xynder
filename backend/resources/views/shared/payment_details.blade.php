@extends('layouts.admin', ['title' => 'Payment Details'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#0B0E11;
    --surface:#181A20;
    --surface-alt:#1E2329;
    --border:#2B3139;
    --yellow:#F0B90B;
    --yellow-dark:#C99400;
    --gold:#FFD45A;
    --green:#0ecb81;
    --red:#ef4444;
    --text:#ffffff;
    --muted:#848E9C;
    --muted2:#5e6673;
    --shadow:0 18px 45px rgba(0,0,0,.35);
}

*{box-sizing:border-box}

.pd-page{
    margin:-28px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding:24px 36px 48px;
}

.pd-simple-head{
    background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);
    border:1px solid var(--border);
    border-radius:16px;
    padding:18px 20px;
    box-shadow:var(--shadow);
    margin-bottom:20px;
}

.pd-simple-kicker{color:var(--yellow);font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;margin-bottom:5px}
.pd-simple-head h1{margin:0;font-size:19px;font-weight:800;letter-spacing:-.2px;color:#fff}
.pd-simple-head p{margin:4px 0 0;color:var(--muted);font-size:12px;font-weight:600}

.pd-alerts{margin-bottom:20px}

.pd-alert{
    border-radius:10px;
    padding:13px 15px;
    margin-bottom:10px;
    font-size:13px;
    font-weight:700;
    display:flex;
    gap:10px;
    align-items:flex-start;
}

.pd-alert.ok{background:rgba(14,203,129,.12);color:var(--green);border:1px solid rgba(14,203,129,.35)}
.pd-alert.err{background:rgba(239,68,68,.12);color:#ff9b9b;border:1px solid rgba(239,68,68,.35)}
.pd-alert.info{background:rgba(240,185,11,.10);color:var(--gold);border:1px solid rgba(240,185,11,.35)}

.pd-card{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    box-shadow:var(--shadow);
    overflow:hidden;
    margin-bottom:20px;
}

.pd-card:hover{border-color:rgba(240,185,11,.42)}

.pd-card-head{
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
}

.pd-card-head h3{margin:0;font-size:15px;font-weight:800;display:flex;align-items:center;gap:9px}
.pd-card-head h3 i{color:var(--yellow);font-size:18px}
.pd-card-head p{margin:3px 0 0;font-size:12px;color:var(--muted);font-weight:600}

.pd-card-body{padding:20px}

.pd-bank-row{
    background:var(--surface-alt);
    border:1px solid var(--border);
    border-radius:10px;
    padding:16px;
    margin-bottom:14px;
}

.pd-bank-row:last-child{margin-bottom:0}

.pd-bank-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:12px;
}

.pd-bank-name{font-size:14.5px;font-weight:800;color:#fff;display:flex;align-items:center;gap:8px}

.pd-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:4px 10px;
    border-radius:20px;
    font-size:10.5px;
    font-weight:900;
    text-transform:uppercase;
    background:rgba(14,203,129,.12);
    color:var(--green);
    border:1px solid rgba(14,203,129,.35);
}

.pd-badge.locked{
    background:rgba(240,185,11,.12);
    color:var(--gold);
    border:1px solid rgba(240,185,11,.35);
}

.pd-bank-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
    font-size:12.5px;
}

.pd-bank-grid span{display:block;color:var(--muted);font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px}
.pd-bank-grid strong{color:#fff;font-weight:700;word-break:break-word}

.pd-bank-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.pd-bank-actions form{margin:0}

.pd-btn{
    border:0;
    border-radius:8px;
    padding:8px 14px;
    font-size:12px;
    font-weight:800;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:6px;
    text-decoration:none;
}

.pd-btn.primary{background:var(--yellow);color:#0B0E11}
.pd-btn.primary:hover{background:var(--yellow-dark)}
.pd-btn.dark{background:var(--surface);color:#fff;border:1px solid var(--border)}
.pd-btn.dark:hover{border-color:var(--yellow)}
.pd-btn.danger{background:rgba(239,68,68,.12);color:#ff9b9b;border:1px solid rgba(239,68,68,.35)}
.pd-btn.danger:hover{background:rgba(239,68,68,.2)}
.pd-btn[disabled]{opacity:.45;cursor:not-allowed;pointer-events:none}

.pd-legacy-note{
    font-size:12px;
    color:var(--muted);
    font-weight:600;
    font-style:italic;
}

.pd-edit-form{
    display:none;
    margin-top:14px;
    padding-top:14px;
    border-top:1px solid var(--border);
}

.pd-edit-form.open{display:block}

.pd-form-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    margin-bottom:14px;
}

.pd-field label{display:block;font-size:12px;font-weight:700;color:#c3c6ca;margin-bottom:7px}

.pd-field input{
    width:100%;
    height:42px;
    background:var(--surface);
    border:1px solid var(--border);
    color:var(--text);
    border-radius:9px;
    padding:0 12px;
    outline:0;
    font-weight:600;
    font-size:13px;
}

.pd-field input:focus{border-color:var(--yellow);box-shadow:0 0 0 3px rgba(240,185,11,.12)}

.pd-checkbox{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#c3c6ca;margin-bottom:14px}
.pd-checkbox input{width:16px;height:16px}

.pd-divider{height:1px;background:var(--border);margin:20px 0}

.pd-add-title{font-size:13px;font-weight:800;color:var(--yellow);text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px}

.pd-empty{text-align:center;padding:24px;color:var(--muted);font-weight:700;font-size:13px}

.pd-locked-box{
    text-align:center;
    padding:22px;
    color:var(--muted);
    font-weight:700;
    font-size:13px;
    background:var(--surface-alt);
    border:1px dashed var(--border);
    border-radius:10px;
}
.pd-locked-box i{color:var(--gold);font-size:22px;display:block;margin-bottom:8px}

.pd-qr-frame{
    width:120px;
    height:120px;
    border-radius:10px;
    overflow:hidden;
    border:1px solid var(--border);
    background:var(--surface-alt);
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--muted2);
    font-size:22px;
    margin-bottom:14px;
}

.pd-qr-frame img{width:100%;height:100%;object-fit:cover}

/* ===== View (read-only) popup ===== */
.pd-view-backdrop{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.72);
    z-index:9999;
    display:none;
    align-items:center;
    justify-content:center;
    padding:18px;
}
.pd-view-backdrop.show{display:flex}

.pd-view-modal{
    width:100%;
    max-width:440px;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    box-shadow:0 24px 70px rgba(0,0,0,.55);
    overflow:hidden;
}

.pd-view-head{
    padding:18px 20px;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}

.pd-view-title{
    display:flex;
    align-items:center;
    gap:9px;
    font-size:15px;
    font-weight:800;
    color:#fff;
}
.pd-view-title i{color:var(--yellow);font-size:18px}

.pd-view-close{
    width:32px;height:32px;border-radius:8px;
    border:1px solid var(--border);
    background:var(--surface-alt);
    color:var(--muted);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:16px;
}
.pd-view-close:hover{color:#fff;border-color:var(--yellow)}

.pd-view-body{padding:20px}

.pd-view-badges{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}

.pd-view-grid{display:flex;flex-direction:column;gap:14px}

.pd-view-row{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:14px;
    padding-bottom:12px;
    border-bottom:1px solid var(--border);
}
.pd-view-row:last-child{border-bottom:0;padding-bottom:0}

.pd-view-label{
    color:var(--muted);
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.04em;
    flex-shrink:0;
}

.pd-view-value{
    color:#fff;
    font-size:14px;
    font-weight:700;
    text-align:right;
    word-break:break-word;
}

.pd-view-footer{
    padding:14px 20px 18px;
}

.pd-view-footer .pd-btn{width:100%;justify-content:center}

@media(max-width:900px){
    .pd-bank-grid{grid-template-columns:1fr 1fr}
    .pd-form-grid{grid-template-columns:1fr 1fr}
}

@media(max-width:640px){
    .pd-page{margin:-18px;padding:16px 14px 30px}
    .pd-bank-grid{grid-template-columns:1fr}
    .pd-form-grid{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')

@php
    $isMerchant = $user->role === 'merchant';
    $isLocked   = !empty($user->payment_details_locked_at);

    $storeBankRoute   = $isMerchant ? route('merchant.payment-details.bank.store') : route('client.payment-details.bank.store');
    $upiRoute         = $isMerchant ? route('merchant.payment-details.upi') : route('client.payment-details.upi');

    $updateBankRoute  = fn($id) => $isMerchant ? route('merchant.payment-details.bank.update', $id) : route('client.payment-details.bank.update', $id);
    $deleteBankRoute  = fn($id) => $isMerchant ? route('merchant.payment-details.bank.destroy', $id) : route('client.payment-details.bank.destroy', $id);
    $defaultBankRoute = fn($id) => $isMerchant ? route('merchant.payment-details.bank.default', $id) : route('client.payment-details.bank.default', $id);

    $upiQr = $user->upi_qr_url ?? ($user->upi_qr ? url('/api/storage/'.$user->upi_qr) : null);
@endphp

<div class="pd-page">

    <section class="pd-simple-head">
        <div class="pd-simple-kicker">BITXNOW</div>
        <h1>Payment Details</h1>
        <p>Manage your bank accounts and UPI details used for deposits and withdrawals.</p>
    </section>

    <div class="pd-alerts">
        @if(session('success'))
            <div class="pd-alert ok"><i class="ti ti-circle-check"></i><div>{{ session('success') }}</div></div>
        @endif

        @if($errors->any())
            <div class="pd-alert err">
                <i class="ti ti-alert-triangle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($isLocked)
            <div class="pd-alert info">
                <i class="ti ti-lock"></i>
                <div>Your payment details have already been set once and are now locked for editing. Contact support if you need a change.</div>
            </div>
        @endif
    </div>

    {{-- Bank Accounts --}}
    <section class="pd-card">
        <div class="pd-card-head">
            <h3><i class="ti ti-building-bank"></i> Bank Accounts</h3>
            <p>Add one or more accounts. One is always marked default for receiving payments.</p>
        </div>

        <div class="pd-card-body">
            @forelse($bankAccounts as $bank)
                @php
                    $bankIsLegacy = !empty($bank->old_user_bank);
                @endphp

                <div class="pd-bank-row">
                    <div class="pd-bank-top">
                        <div class="pd-bank-name">
                            <i class="ti ti-building-bank"></i>
                            {{ $bank->bank_name ?? 'Unnamed Bank' }}
                            @if($bank->is_default)
                                <span class="pd-badge"><i class="ti ti-star-filled"></i> Default</span>
                            @endif
                            @if($isLocked)
                                <span class="pd-badge locked"><i class="ti ti-lock"></i> Locked</span>
                            @endif
                        </div>
                    </div>

                    <div class="pd-bank-grid">
                        <div><span>Branch</span><strong>{{ $bank->branch ?? '—' }}</strong></div>
                        <div><span>Account No</span><strong>{{ $bank->account_number ?? '—' }}</strong></div>
                        <div><span>Account Type</span><strong>{{ $bank->account_type ?? '—' }}</strong></div>
                        <div><span>IFSC</span><strong>{{ $bank->ifsc ?? '—' }}</strong></div>
                    </div>

                    <div class="pd-bank-actions">
                        <button type="button"
                                class="pd-btn dark"
                                onclick="pdViewBank(this)"
                                data-bank-name="{{ $bank->bank_name ?? 'Unnamed Bank' }}"
                                data-branch="{{ $bank->branch ?? '—' }}"
                                data-account-number="{{ $bank->account_number ?? '—' }}"
                                data-account-type="{{ $bank->account_type ?? '—' }}"
                                data-ifsc="{{ $bank->ifsc ?? '—' }}"
                                data-is-default="{{ $bank->is_default ? '1' : '0' }}"
                                data-is-locked="{{ $isLocked ? '1' : '0' }}">
                            <i class="ti ti-eye"></i> View
                        </button>

                        @if($bankIsLegacy)
                            <span class="pd-legacy-note">
                                <i class="ti ti-info-circle"></i>
                                @if($isLocked)
                                    Legacy record from your old profile. Payment details are locked, so this is view-only.
                                @else
                                    Legacy record from your old profile. Add a new bank account below — it will become your default.
                                @endif
                            </span>
                        @elseif(!$isLocked)
                            <button type="button" class="pd-btn dark" onclick="pdToggleEdit({{ $bank->id }})">
                                <i class="ti ti-edit"></i> Edit
                            </button>

                            @if(!$bank->is_default)
                                <form method="POST" action="{{ $defaultBankRoute($bank->id) }}">
                                    @csrf
                                    <button type="submit" class="pd-btn primary">
                                        <i class="ti ti-star"></i> Set Default
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ $deleteBankRoute($bank->id) }}"
                                  onsubmit="return confirm('Delete this bank account?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pd-btn danger">
                                    <i class="ti ti-trash"></i> Delete
                                </button>
                            </form>
                        @else
                            <span class="pd-legacy-note">
                                <i class="ti ti-lock"></i> View only — payment details are locked.
                            </span>
                        @endif
                    </div>

                    @if(!$bankIsLegacy && !$isLocked)
                        <div class="pd-edit-form" id="editForm{{ $bank->id }}">
                            <form method="POST" action="{{ $updateBankRoute($bank->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="pd-form-grid">
                                    <div class="pd-field">
                                        <label>Bank Name</label>
                                        <input type="text" name="bank_name" value="{{ $bank->bank_name }}">
                                    </div>
                                    <div class="pd-field">
                                        <label>Branch</label>
                                        <input type="text" name="branch" value="{{ $bank->branch }}">
                                    </div>
                                    <div class="pd-field">
                                        <label>Account Number</label>
                                        <input type="text" name="account_number" value="{{ $bank->account_number }}">
                                    </div>
                                    <div class="pd-field">
                                        <label>Account Type</label>
                                        <input type="text" name="account_type" value="{{ $bank->account_type }}">
                                    </div>
                                    <div class="pd-field">
                                        <label>IFSC</label>
                                        <input type="text" name="ifsc" value="{{ $bank->ifsc }}">
                                    </div>
                                </div>

                                <label class="pd-checkbox">
                                    <input type="checkbox" name="is_default" value="1" {{ $bank->is_default ? 'checked' : '' }}>
                                    Set as default account
                                </label>

                                <button type="submit" class="pd-btn primary">
                                    <i class="ti ti-device-floppy"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="pd-empty">No bank accounts added yet.</div>
            @endforelse

            @if(!$isLocked)
                <div class="pd-divider"></div>

                <div class="pd-add-title">Add New Bank Account</div>

                <form method="POST" action="{{ $storeBankRoute }}">
                    @csrf

                    <div class="pd-form-grid">
                        <div class="pd-field">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name">
                        </div>
                        <div class="pd-field">
                            <label>Branch</label>
                            <input type="text" name="branch">
                        </div>
                        <div class="pd-field">
                            <label>Account Number</label>
                            <input type="text" name="account_number">
                        </div>
                        <div class="pd-field">
                            <label>Account Type</label>
                            <input type="text" name="account_type">
                        </div>
                        <div class="pd-field">
                            <label>IFSC</label>
                            <input type="text" name="ifsc">
                        </div>
                    </div>

                    <label class="pd-checkbox">
                        <input type="checkbox" name="is_default" value="1">
                        Set as default account
                    </label>

                    <button type="submit" class="pd-btn primary">
                        <i class="ti ti-plus"></i> Add Bank Account
                    </button>
                </form>
            @else
                <div class="pd-divider"></div>
                <div class="pd-locked-box">
                    <i class="ti ti-lock"></i>
                    Payment details are locked. You can't add another bank account.
                </div>
            @endif
        </div>
    </section>

    {{-- UPI Details --}}
    <section class="pd-card">
        <div class="pd-card-head">
            <h3><i class="ti ti-brand-paypal"></i> UPI Details</h3>
            <p>Used for UPI-based deposits and withdrawals.</p>
        </div>

        <div class="pd-card-body">
            <div class="pd-qr-frame" id="upiQrPreview">
                @if($upiQr)
                    <img src="{{ $upiQr }}" alt="UPI QR">
                @else
                    <i class="ti ti-qrcode"></i>
                @endif
            </div>

            @if(!$isLocked)
                <form method="POST" action="{{ $upiRoute }}" enctype="multipart/form-data">
                    @csrf

                    <div class="pd-form-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="pd-field">
                            <label>UPI Account Name</label>
                            <input type="text" name="upi_name" value="{{ old('upi_name', $user->upi_name) }}">
                        </div>
                        <div class="pd-field">
                            <label>UPI ID</label>
                            <input type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}">
                        </div>
                    </div>

                    <div class="pd-field" style="margin-bottom:14px;">
                        <label>UPI QR Code</label>
                        <input type="file" name="upi_qr" accept="image/*" data-preview="upiQrPreview">
                    </div>

                    <button type="submit" class="pd-btn primary">
                        <i class="ti ti-device-floppy"></i> Save UPI Details
                    </button>
                </form>
            @else
                <div class="pd-bank-grid" style="grid-template-columns:1fr 1fr;margin-bottom:14px;">
                    <div><span>UPI Account Name</span><strong>{{ $user->upi_name ?? '—' }}</strong></div>
                    <div><span>UPI ID</span><strong>{{ $user->upi_id ?? '—' }}</strong></div>
                </div>
                <div class="pd-locked-box">
                    <i class="ti ti-lock"></i>
                    UPI details are locked and can't be edited.
                </div>
            @endif
        </div>
    </section>
</div>

{{-- Read-only View popup (shared for all bank rows) --}}
<div class="pd-view-backdrop" id="pdViewBackdrop">
    <div class="pd-view-modal">
        <div class="pd-view-head">
            <div class="pd-view-title">
                <i class="ti ti-building-bank"></i>
                <span id="pdViewBankName">Bank Details</span>
            </div>
            <button type="button" class="pd-view-close" id="pdViewCloseBtn">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <div class="pd-view-body">
            <div class="pd-view-badges" id="pdViewBadges"></div>

            <div class="pd-view-grid">
                <div class="pd-view-row">
                    <span class="pd-view-label">Branch</span>
                    <span class="pd-view-value" id="pdViewBranch">—</span>
                </div>
                <div class="pd-view-row">
                    <span class="pd-view-label">Account No</span>
                    <span class="pd-view-value" id="pdViewAccountNumber">—</span>
                </div>
                <div class="pd-view-row">
                    <span class="pd-view-label">Account Type</span>
                    <span class="pd-view-value" id="pdViewAccountType">—</span>
                </div>
                <div class="pd-view-row">
                    <span class="pd-view-label">IFSC</span>
                    <span class="pd-view-value" id="pdViewIfsc">—</span>
                </div>
            </div>
        </div>

        <div class="pd-view-footer">
            <button type="button" class="pd-btn dark" id="pdViewCloseBtn2">Close</button>
        </div>
    </div>
</div>

<script>
function pdToggleEdit(id) {
    const el = document.getElementById('editForm' + id);
    if (el) el.classList.toggle('open');
}

document.querySelectorAll('input[type="file"][data-preview]').forEach(function(input){
    input.addEventListener('change', function(){
        const file = input.files && input.files[0];
        const target = document.getElementById(input.dataset.preview);
        if (!file || !target) return;

        const reader = new FileReader();
        reader.onload = function(e){
            target.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(file);
    });
});

(function(){
    const backdrop = document.getElementById('pdViewBackdrop');
    const closeBtn1 = document.getElementById('pdViewCloseBtn');
    const closeBtn2 = document.getElementById('pdViewCloseBtn2');

    function closeModal(){
        backdrop.classList.remove('show');
    }

    closeBtn1.addEventListener('click', closeModal);
    closeBtn2.addEventListener('click', closeModal);

    backdrop.addEventListener('click', function(e){
        if (e.target === backdrop) closeModal();
    });

    window.pdViewBank = function(btn){
        document.getElementById('pdViewBankName').textContent = btn.dataset.bankName || 'Bank Details';
        document.getElementById('pdViewBranch').textContent = btn.dataset.branch || '—';
        document.getElementById('pdViewAccountNumber').textContent = btn.dataset.accountNumber || '—';
        document.getElementById('pdViewAccountType').textContent = btn.dataset.accountType || '—';
        document.getElementById('pdViewIfsc').textContent = btn.dataset.ifsc || '—';

        const badges = document.getElementById('pdViewBadges');
        badges.innerHTML = '';

        if (btn.dataset.isDefault === '1') {
            badges.insertAdjacentHTML('beforeend',
                '<span class="pd-badge"><i class="ti ti-star-filled"></i> Default</span>');
        }
        if (btn.dataset.isLocked === '1') {
            badges.insertAdjacentHTML('beforeend',
                '<span class="pd-badge locked"><i class="ti ti-lock"></i> Locked</span>');
        }

        backdrop.classList.add('show');
    };
})();
</script>

@endsection
@extends('layouts.admin', ['title' => 'Settings'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --bg:#0B0E11;--card:#181A20;--soft:#1E2329;--border:#2B3139;
    --yellow:#F0B90B;--yellow2:#C99400;--green:#0ECB81;--red:#EF4444;
    --text:#fff;--muted:#848E9C;--muted2:#5E6673;
}
.set-page{margin:-28px;min-height:100vh;background:var(--bg);color:var(--text);padding:24px 36px 48px;font-family:Inter,Arial,sans-serif}
.set-head{background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);border:1px solid var(--border);border-radius:16px;padding:18px 20px;margin-bottom:16px}
.set-head h1{margin:0;font-size:20px}.set-head p{margin:5px 0 0;color:var(--muted);font-size:13px}

.stat-row{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.stat-chip{flex:1;min-width:160px;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:12px}
.stat-chip .ic{width:36px;height:36px;border-radius:10px;background:rgba(240,185,11,.12);color:var(--yellow);display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.stat-chip .lbl{color:var(--muted);font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.03em}
.stat-chip .val{font-size:15px;font-weight:800;margin-top:2px}

.alert{padding:12px 14px;border-radius:10px;margin-bottom:12px;font-weight:700;font-size:13px}
.alert.ok{background:rgba(14,203,129,.12);color:var(--green);border:1px solid rgba(14,203,129,.35)}
.alert.err{background:rgba(239,68,68,.12);color:#ff9b9b;border:1px solid rgba(239,68,68,.35)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.full{margin-top:20px}
.card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);font-weight:800;display:flex;align-items:center;justify-content:space-between;gap:8px}
.card-head .ttl{display:flex;align-items:center;gap:8px}
.card-head i{color:var(--yellow)}
.card-body{padding:20px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.form-grid.three{grid-template-columns:repeat(3,1fr)}
.field{margin-bottom:14px}.field label{display:block;font-size:12px;font-weight:800;margin-bottom:7px;color:var(--muted)}
.field input,.field textarea{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:9px;color:#fff;padding:10px 12px;outline:none;font-family:inherit}
.field textarea{min-height:105px}
.field input:focus,.field textarea:focus{border-color:var(--yellow);box-shadow:0 0 0 3px rgba(240,185,11,.12)}
.field input[readonly]{opacity:.75}
.btn{border:0;border-radius:9px;padding:10px 16px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:7px;font-size:13px}
.btn.yellow{background:var(--yellow);color:#0B0E11}.btn.yellow:hover{background:var(--yellow2)}
.btn.dark{background:var(--soft);color:#fff;border:1px solid var(--border)}
.btn.red{background:rgba(239,68,68,.14);color:#ff9b9b;border:1px solid rgba(239,68,68,.35)}
.btn.sm{padding:8px 12px;font-size:12px}
.btn.block{width:100%;justify-content:center}

/* bank row (list) */
.bank-row{display:flex;align-items:center;gap:14px;background:var(--soft);border:1px solid var(--border);border-radius:12px;padding:13px 16px;margin-bottom:10px}
.bank-avatar{width:42px;height:42px;border-radius:10px;background:rgba(240,185,11,.12);color:var(--yellow);font-weight:900;font-size:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.bank-mid{flex:1;min-width:0}
.bank-mid .nm{font-weight:800;font-size:14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.bank-mid .sub{color:var(--muted);font-size:12px;margin-top:3px;display:flex;gap:10px;flex-wrap:wrap}
.bank-mid .sub .num{font-family:'Courier New',monospace;letter-spacing:.03em;color:#c9cdd3}
.badge{font-size:10px;font-weight:900;border-radius:999px;padding:4px 9px;background:rgba(240,185,11,.12);color:var(--yellow);border:1px solid rgba(240,185,11,.35);text-transform:uppercase;letter-spacing:.03em}
.badge.type{background:var(--bg);color:var(--muted);border:1px solid var(--border)}

.table{width:100%;border-collapse:collapse}.table th{background:var(--soft);color:var(--muted);font-size:11px;text-align:left;padding:12px}.table td{border-top:1px solid var(--border);padding:13px;font-size:13px}
.status{padding:6px 10px;border-radius:999px;font-size:11px;font-weight:900;background:rgba(240,185,11,.12);color:var(--yellow)}

/* modal */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);display:none;align-items:center;justify-content:center;z-index:1000;padding:20px}
.modal-overlay.active{display:flex}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:16px;width:100%;max-width:540px;max-height:90vh;overflow-y:auto}
.modal-head{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--card)}
.modal-head h3{margin:0;font-size:16px;display:flex;align-items:center;gap:8px}
.modal-head h3 i{color:var(--yellow)}
.modal-close{background:var(--soft);border:1px solid var(--border);color:var(--muted);width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center}
.modal-close:hover{color:#fff}
.modal-body{padding:22px}
.modal-foot-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
hr.sep{border:0;border-top:1px solid var(--border);margin:18px 0}

/* view-only badges row inside view modal */
.view-badges{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}

@media(max-width:1000px){
  .grid,.form-grid,.form-grid.three{grid-template-columns:1fr}
  .set-page{margin:-18px;padding:16px 14px 30px}
  .bank-row{flex-wrap:wrap}
}
</style>
@endpush
@section('content')
@php
    $role = $user->role === 'merchant' ? 'merchant' : 'client';

    $passwordRoute = route($role.'.settings.password');
    $supportRoute = route($role.'.settings.support');

    $bankStoreRoute = route($role.'.settings.bank.store');
    $upiRoute = route($role.'.settings.upi');

    $bankAccounts = $user->bankAccounts ?? collect();
    $defaultBank = $bankAccounts->firstWhere('is_default', true);
    $openTickets = $tickets->getCollection()->whereNotIn('status', ['closed', 'resolved'])->count();
    $paymentLocked = ! empty($user->payment_details_locked_at);
@endphp

<div class="set-page">
    <section class="set-head">
        <h1>Settings</h1>
        <p>Manage password, bank accounts, UPI details, and support tickets.</p>
    </section>

    <section class="stat-row">
        <div class="stat-chip">
            <div class="ic"><i class="ti ti-building-bank"></i></div>
            <div>
                <div class="lbl">Bank Accounts</div>
                <div class="val">{{ $bankAccounts->count() }}</div>
            </div>
        </div>

        <div class="stat-chip">
            <div class="ic"><i class="ti ti-star"></i></div>
            <div>
                <div class="lbl">Default Bank</div>
                <div class="val">{{ $defaultBank->bank_name ?? 'Not set' }}</div>
            </div>
        </div>

        <div class="stat-chip">
            <div class="ic"><i class="ti ti-ticket"></i></div>
            <div>
                <div class="lbl">Open Tickets</div>
                <div class="val">{{ $openTickets }}</div>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="alert ok">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert err">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if($paymentLocked)
        <div class="alert err">
            <i class="ti ti-lock"></i>
            Payment details are locked. You can view bank and UPI details only.
        </div>
    @endif

    <section class="grid">
        <div class="card">
            <div class="card-head">
                <span class="ttl"><i class="ti ti-lock"></i> Change Password</span>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ $passwordRoute }}">
                    @csrf

                    <div class="field">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>

                    <div class="field">
                        <label>New Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="field">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" required>
                    </div>

                    <button class="btn yellow" type="submit">
                        <i class="ti ti-shield-check"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <span class="ttl"><i class="ti ti-brand-paypal"></i> UPI Details</span>
                @if($paymentLocked)
                    <span class="badge"><i class="ti ti-lock"></i> Locked</span>
                @endif
            </div>

            <div class="card-body">
                <form method="POST" action="{{ $upiRoute }}" enctype="multipart/form-data">
                    @csrf

                    <div class="field">
                        <label>UPI Name</label>
                        <input type="text" name="upi_name" value="{{ old('upi_name', $user->upi_name) }}" {{ $paymentLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="field">
                        <label>UPI ID</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}" placeholder="example@upi" {{ $paymentLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="field">
                        <label>UPI QR Photo</label>
                        <input type="file" name="upi_qr" accept="image/*" {{ $paymentLocked ? 'disabled' : '' }}>

                        @if($user->upi_qr)
                            <img src="{{ asset('storage/'.$user->upi_qr) }}" style="width:130px;height:130px;object-fit:cover;border-radius:10px;margin-top:10px;border:1px solid var(--border);">
                        @endif
                    </div>

                    @if(! $paymentLocked)
                        <button class="btn yellow" type="submit">
                            <i class="ti ti-device-floppy"></i> Save UPI
                        </button>
                    @else
                        <span class="badge"><i class="ti ti-lock"></i> UPI Locked</span>
                    @endif
                </form>
            </div>
        </div>
    </section>

    <section class="card full">
        <div class="card-head">
            <span class="ttl"><i class="ti ti-building-bank"></i> Bank Accounts</span>

            @if(! $paymentLocked)
                <button type="button" class="btn yellow sm" onclick="openModal('add-bank-modal')">
                    <i class="ti ti-plus"></i> Add Bank Account
                </button>
            @else
                <span class="badge"><i class="ti ti-lock"></i> Payment Details Locked</span>
            @endif
        </div>

        <div class="card-body">
            @forelse($bankAccounts as $bank)
                @php
                    $masked = $bank->account_number ? '••••'.substr($bank->account_number, -4) : '—';
                    $initial = strtoupper(substr($bank->bank_name ?: 'B', 0, 1));
                @endphp

                <div class="bank-row">
                    <div class="bank-avatar">{{ $initial }}</div>

                    <div class="bank-mid">
                        <div class="nm">
                            {{ $bank->bank_name }}

                            @if($bank->is_default)
                                <span class="badge">Default</span>
                            @endif

                            @if($bank->account_type)
                                <span class="badge type">{{ $bank->account_type }}</span>
                            @endif
                        </div>

                        <div class="sub">
                            <span class="num">{{ $masked }}</span>
                            <span>{{ $bank->branch ?: 'No branch added' }}</span>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn dark sm"
                        onclick="openBankView(this)"
                        data-bank-name="{{ $bank->bank_name }}"
                        data-branch="{{ $bank->branch }}"
                        data-account-number="{{ $bank->account_number }}"
                        data-account-type="{{ $bank->account_type }}"
                        data-ifsc="{{ $bank->ifsc }}"
                        data-is-default="{{ $bank->is_default ? '1' : '0' }}"
                    >
                        <i class="ti ti-eye"></i> View
                    </button>
                </div>
            @empty
                <div style="color:var(--muted);font-weight:700;padding:20px 0;text-align:center">
                    No bank accounts added yet.
                    @if(! $paymentLocked)
                        Click "Add Bank Account" to add your first one.
                    @endif
                </div>
            @endforelse
        </div>
    </section>

    <section class="card full">
        <div class="card-head">
            <span class="ttl"><i class="ti ti-headset"></i> Help & Support</span>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ $supportRoute }}">
                @csrf

                <div class="form-grid">
                    <div class="field">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>

                <div class="field">
                    <label>Message</label>
                    <textarea name="message" required>{{ old('message') }}</textarea>
                </div>

                <button class="btn yellow" type="submit">
                    <i class="ti ti-send"></i> Submit Ticket
                </button>
            </form>
        </div>
    </section>

    <section class="card full">
        <div class="card-head">
            <span class="ttl"><i class="ti ti-ticket"></i> Your Support Tickets</span>
        </div>

        <div style="overflow-x:auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->created_at?->format('d M Y, H:i') }}</td>
                            <td>{{ $ticket->message }}</td>
                            <td><span class="status">{{ strtoupper($ticket->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center;color:var(--muted);padding:35px">
                                No support tickets yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($tickets, 'links'))
            <div style="padding:16px 20px;border-top:1px solid var(--border)">
                {{ $tickets->links() }}
            </div>
        @endif
    </section>
</div>

@if(! $paymentLocked)
<div class="modal-overlay" id="add-bank-modal" onclick="if(event.target===this) closeModal('add-bank-modal')">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="ti ti-plus"></i> Add Bank Account</h3>
            <button type="button" class="modal-close" onclick="closeModal('add-bank-modal')">&times;</button>
        </div>

        <div class="modal-body">
            <form method="POST" action="{{ $bankStoreRoute }}">
                @csrf

                <div class="form-grid three">
                    <div class="field">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" required>
                    </div>

                    <div class="field">
                        <label>Branch</label>
                        <input type="text" name="branch" value="{{ old('branch') }}">
                    </div>

                    <div class="field">
                        <label>Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" required>
                    </div>

                    <div class="field">
                        <label>Account Type</label>
                        <input type="text" name="account_type" value="{{ old('account_type') }}" placeholder="Savings / Current">
                    </div>

                    <div class="field">
                        <label>IFSC</label>
                        <input type="text" name="ifsc" value="{{ old('ifsc') }}">
                    </div>
                </div>

                <label style="display:flex;gap:8px;align-items:center;margin-bottom:16px;font-size:13px;font-weight:700">
                    <input type="checkbox" name="is_default" value="1">
                    Set as default bank account
                </label>

                <button class="btn yellow block" type="submit">
                    <i class="ti ti-plus"></i> Add Bank Account
                </button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Bank Account VIEW modal — always read-only, no editing, no default-switch here --}}
<div class="modal-overlay" id="bank-view-modal" onclick="if(event.target===this) closeModal('bank-view-modal')">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="ti ti-building-bank"></i> Bank Account Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('bank-view-modal')">&times;</button>
        </div>

        <div class="modal-body">
            <div class="view-badges" id="view-badges"></div>

            <div class="form-grid three">
                <div class="field">
                    <label>Bank Name</label>
                    <input type="text" id="view-bank-name" readonly>
                </div>

                <div class="field">
                    <label>Branch</label>
                    <input type="text" id="view-branch" readonly>
                </div>

                <div class="field">
                    <label>Account Number</label>
                    <input type="text" id="view-account-number" readonly>
                </div>

                <div class="field">
                    <label>Account Type</label>
                    <input type="text" id="view-account-type" readonly>
                </div>

                <div class="field">
                    <label>IFSC</label>
                    <input type="text" id="view-ifsc" readonly>
                </div>
            </div>

            <button type="button" class="btn dark block" onclick="closeModal('bank-view-modal')">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openModal(id){
    const modal = document.getElementById(id);
    if(modal) modal.classList.add('active');
}

function closeModal(id){
    const modal = document.getElementById(id);
    if(modal) modal.classList.remove('active');
}

function openBankView(btn){
    const d = btn.dataset;

    document.getElementById('view-bank-name').value = d.bankName || '';
    document.getElementById('view-branch').value = d.branch || '';
    document.getElementById('view-account-number').value = d.accountNumber || '';
    document.getElementById('view-account-type').value = d.accountType || '';
    document.getElementById('view-ifsc').value = d.ifsc || '';

    const badges = document.getElementById('view-badges');
    badges.innerHTML = '';

    if(d.isDefault === '1'){
        badges.insertAdjacentHTML('beforeend', '<span class="badge"><i class="ti ti-star-filled"></i> Default</span>');
    }

    openModal('bank-view-modal');
}

document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        closeModal('add-bank-modal');
        closeModal('bank-view-modal');
    }
});
</script>
@endsection
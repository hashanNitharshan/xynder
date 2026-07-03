@extends('layouts.admin', ['title' => 'Settings'])

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

    --text:#fff;
    --muted:#848E9C;
    --muted2:#5e6673;

    --shadow:0 18px 45px rgba(0,0,0,.35);
}

*{box-sizing:border-box}

.set-page{
    margin:-28px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding:24px 36px 48px;
}

/* Top Header */
.set-simple-head{
    background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);
    border:1px solid var(--border);
    border-radius:16px;
    padding:18px 20px;
    box-shadow:var(--shadow);
    margin-bottom:20px;
}

.set-simple-kicker{
    color:var(--yellow);
    font-size:11px;
    font-weight:800;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:5px;
}

.set-simple-head h1{
    margin:0;
    font-size:19px;
    font-weight:800;
    letter-spacing:-.2px;
    color:#fff;
}

.set-simple-head p{
    margin:4px 0 0;
    color:var(--muted);
    font-size:12px;
    font-weight:600;
}

/* Alerts */
.set-alerts{margin-bottom:20px}

.set-alert{
    border-radius:10px;
    padding:13px 15px;
    margin-bottom:10px;
    font-size:13px;
    font-weight:700;
    display:flex;
    gap:10px;
    align-items:flex-start;
}

.set-alert.ok{
    background:rgba(14,203,129,.12);
    color:var(--green);
    border:1px solid rgba(14,203,129,.35);
}

.set-alert.err{
    background:rgba(239,68,68,.12);
    color:#ff9b9b;
    border:1px solid rgba(239,68,68,.35);
}

.set-alert i{font-size:17px}

/* Sections */
.set-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.set-section{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    box-shadow:var(--shadow);
    overflow:hidden;
}

.set-section:hover{
    border-color:rgba(240,185,11,.42);
}

.set-section-head{
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    background:var(--surface);
    display:flex;
    align-items:center;
    gap:9px;
    font-size:15px;
    font-weight:800;
    color:#fff;
}

.set-section-head i{
    color:var(--yellow);
    font-size:18px;
}

.set-section-body{padding:20px}

.set-full{margin-top:20px}

/* Payment */
.set-pay-list{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
}

.set-pay-row{
    background:var(--surface-alt);
    border:1px solid var(--border);
    border-radius:9px;
    padding:12px;
}

.set-pay-row span{
    display:block;
    color:var(--muted);
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:6px;
}

.set-pay-row strong{
    color:#fff;
    font-size:13.5px;
    font-weight:700;
    word-break:break-word;
}

/* Forms */
.set-form-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
}

.set-form-grid.two{
    grid-template-columns:repeat(2,1fr);
}

.set-field{
    margin-bottom:14px;
}

.set-field label{
    display:block;
    color:#fff;
    font-size:12.5px;
    font-weight:700;
    margin-bottom:7px;
}

.set-field input,
.set-field textarea{
    width:100%;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:9px;
    color:#fff;
    padding:10px 12px;
    outline:none;
    font-size:13.5px;
    font-weight:500;
    font-family:inherit;
    transition:.15s;
}

.set-field textarea{
    min-height:110px;
    resize:vertical;
}

.set-field input:focus,
.set-field textarea:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(240,185,11,.12);
}

/* Buttons */
.set-btn{
    border:0;
    border-radius:9px;
    padding:10px 18px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    text-decoration:none;
    font-family:inherit;
}

.set-btn.primary{
    background:var(--yellow);
    color:#0B0E11;
    box-shadow:0 10px 24px rgba(240,185,11,.20);
}

.set-btn.primary:hover{
    background:var(--yellow-dark);
}

.set-btn.dark{
    background:var(--surface-alt);
    color:#fff;
    border:1px solid var(--border);
}

/* Table */
.set-table-wrap{overflow-x:auto}

.set-table{
    width:100%;
    min-width:780px;
    border-collapse:collapse;
}

.set-table th{
    color:var(--muted);
    background:var(--surface-alt);
    font-size:11px;
    text-align:left;
    text-transform:uppercase;
    letter-spacing:.05em;
    padding:13px 16px;
    font-weight:800;
}

.set-table td{
    border-top:1px solid var(--border);
    padding:15px 16px;
    color:#d5dade;
    font-size:13px;
    font-weight:600;
    vertical-align:top;
}

.set-table tr:hover td{
    background:var(--surface-alt);
}

.set-status{
    display:inline-flex;
    padding:6px 11px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
}

.set-status.pending,
.set-status.open{
    background:rgba(240,185,11,.12);
    color:var(--yellow);
    border:1px solid rgba(240,185,11,.35);
}

.set-status.closed,
.set-status.resolved,
.set-status.approved{
    background:rgba(14,203,129,.12);
    color:var(--green);
    border:1px solid rgba(14,203,129,.35);
}

.set-status.rejected{
    background:rgba(239,68,68,.12);
    color:#ff9b9b;
    border:1px solid rgba(239,68,68,.35);
}

.set-empty{
    text-align:center!important;
    padding:42px!important;
    color:var(--muted)!important;
}

/* Pager */
.set-pager{
    padding:16px 20px;
    border-top:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    color:var(--muted);
    font-size:13px;
    font-weight:600;
}

.set-page-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:8px 13px;
    background:var(--surface-alt);
    border:1px solid var(--border);
    color:#fff;
    text-decoration:none;
    border-radius:8px;
    font-size:12.5px;
    font-weight:700;
    margin-left:6px;
}

.set-page-btn:hover{
    border-color:var(--yellow);
}

.set-page-btn.disabled{
    opacity:.4;
    pointer-events:none;
}

@media(max-width:1100px){
    .set-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:760px){
    .set-page{
        margin:-18px;
        padding:16px 14px 30px;
    }

    .set-simple-head{
        border-radius:14px;
    }

    .set-pay-list,
    .set-form-grid,
    .set-form-grid.two{
        grid-template-columns:1fr;
    }

    .set-table{
        min-width:0;
    }

    .set-table thead{
        display:none;
    }

    .set-table,
    .set-table tbody,
    .set-table tr,
    .set-table td{
        display:block;
        width:100%;
    }

    .set-table tr{
        border-top:1px solid var(--border);
        padding:14px 16px;
    }

    .set-table td{
        border-top:0;
        padding:6px 0;
        display:flex;
        justify-content:space-between;
        gap:12px;
        white-space:normal;
    }

    .set-table td::before{
        content:attr(data-label);
        color:var(--muted2);
        font-size:11px;
        font-weight:800;
        text-transform:uppercase;
        flex-shrink:0;
    }

    .set-pager{
        align-items:flex-start;
        flex-direction:column;
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
@endphp

<div class="set-page">

    <section class="set-simple-head">
        <div class="set-simple-kicker">BITXNOW</div>
        <h1>Settings</h1>
        <p>Manage password, payment methods, and support tickets.</p>
    </section>

    <div class="set-alerts">
        @if(session('success'))
            <div class="set-alert ok">
                <i class="ti ti-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="set-alert err">
                <i class="ti ti-alert-triangle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <section class="set-grid">
        <div class="set-section">
            <div class="set-section-head">
                <i class="ti ti-credit-card"></i>
                Payment Methods
            </div>

            <div class="set-section-body">
                <div class="set-pay-list">
                    <div class="set-pay-row"><span>Bank</span><strong>{{ $user->bank_name ?? '-' }}</strong></div>
                    <div class="set-pay-row"><span>Branch</span><strong>{{ $user->branch ?? '-' }}</strong></div>
                    <div class="set-pay-row"><span>Account No</span><strong>{{ $user->account_number ?? '-' }}</strong></div>
                    <div class="set-pay-row"><span>Account Type</span><strong>{{ $user->account_type ?? '-' }}</strong></div>
                    <div class="set-pay-row"><span>IFSC</span><strong>{{ $user->ifsc ?? '-' }}</strong></div>
                    <div class="set-pay-row"><span>UPI Name</span><strong>{{ $user->upi_name ?? '-' }}</strong></div>
                    <div class="set-pay-row"><span>UPI ID</span><strong>{{ $user->upi_id ?? '-' }}</strong></div>
                    <div class="set-pay-row"><span>Phone</span><strong>{{ $user->phone ?? '-' }}</strong></div>
                </div>
            </div>
        </div>

        <div class="set-section">
            <div class="set-section-head">
                <i class="ti ti-lock-password"></i>
                Change Password
            </div>

            <div class="set-section-body">
                <form method="POST" action="{{ $passwordRoute }}">
                    @csrf

                    <div class="set-form-grid">
                        <div class="set-field">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required autocomplete="current-password">
                        </div>

                        <div class="set-field">
                            <label>New Password</label>
                            <input type="password" name="password" required autocomplete="new-password">
                        </div>

                        <div class="set-field">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="set-btn primary">
                        <i class="ti ti-shield-check"></i>
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="set-section set-full">
        <div class="set-section-head">
            <i class="ti ti-headset"></i>
            Help & Support
        </div>

        <div class="set-section-body">
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

                <div class="set-field">
                    <label>Message</label>
                    <textarea name="message" required placeholder="Tell us your issue...">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="set-btn primary">
                    <i class="ti ti-send"></i>
                    Submit Ticket
                </button>
            </form>
        </div>
    </section>

    <section class="set-section set-full">
        <div class="set-section-head">
            <i class="ti ti-ticket"></i>
            Your Support Tickets
        </div>

        <div class="set-table-wrap">
            <table class="set-table">
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
                            <td data-label="Date" style="color:var(--muted)">
                                {{ $ticket->created_at?->format('d M Y, H:i') }}
                            </td>

                            <td data-label="Message">
                                {{ $ticket->message }}
                            </td>

                            <td data-label="Status">
                                <span class="set-status {{ strtolower($ticket->status) }}">
                                    {{ strtoupper($ticket->status) }}
                                </span>
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

        @if(method_exists($tickets, 'hasPages') && $tickets->hasPages())
            <div class="set-pager">
                <div>
                    Page {{ $tickets->currentPage() }} of {{ $tickets->lastPage() }}
                    @if($tickets->total())
                        · {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} of {{ $tickets->total() }}
                    @endif
                </div>

                <div>
                    @if($tickets->onFirstPage())
                        <span class="set-page-btn disabled">
                            <i class="ti ti-chevron-left"></i> Previous
                        </span>
                    @else
                        <a class="set-page-btn" href="{{ $tickets->previousPageUrl() }}">
                            <i class="ti ti-chevron-left"></i> Previous
                        </a>
                    @endif

                    @if($tickets->hasMorePages())
                        <a class="set-page-btn" href="{{ $tickets->nextPageUrl() }}">
                            Next <i class="ti ti-chevron-right"></i>
                        </a>
                    @else
                        <span class="set-page-btn disabled">
                            Next <i class="ti ti-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
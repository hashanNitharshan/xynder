@extends('layouts.admin', ['title' => 'Client Requests'])

@push('styles')
<style>
.xyn-page{display:flex;flex-direction:column;gap:22px}
.xyn-hero-card,.xyn-form-card,.xyn-table-card{
    background:#0e1220;border:1px solid rgba(255,255,255,.07);
    border-radius:28px;padding:24px;position:relative;overflow:hidden;
}
.xyn-hero-card::before{
    content:'';position:absolute;right:-90px;top:-90px;width:300px;height:300px;
    background:radial-gradient(circle,rgba(124,92,252,.20),transparent 70%);
}
.xyn-hero-title{font-size:28px;font-weight:900;margin-bottom:6px}
.xyn-hero-title span{
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
}
.xyn-hero-sub{color:#9ca3af;font-size:14px}
.xyn-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:20px}
.xyn-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.xyn-field label{font-size:12px;font-weight:900;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em}
.xyn-field input,.xyn-field select,.xyn-field textarea{
    width:100%;margin-top:8px;background:#131929;border:1px solid rgba(255,255,255,.08);
    color:#fff;border-radius:14px;padding:13px 14px;outline:none;
}
.xyn-field input:focus,.xyn-field select:focus,.xyn-field textarea:focus{
    border-color:rgba(124,92,252,.55);box-shadow:0 0 0 3px rgba(124,92,252,.12);
}
.xyn-online{
    color:#34d399;
    font-weight:900;
}
.xyn-rate-card{
    background:linear-gradient(145deg,#1a1060,#071228);
    border:1px solid rgba(124,92,252,.25);border-radius:24px;padding:22px;
}
.xyn-rate-title{font-size:15px;font-weight:900;margin-bottom:16px}
.xyn-rate-row{display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.08);color:#9ca3af}
.xyn-rate-row strong{color:#fff}
.xyn-total-box{
    margin-top:16px;padding:16px;border-radius:18px;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);
}
.xyn-total-box .big{font-size:26px;font-weight:900;color:#34d399}
.xyn-submit{
    margin-top:18px;border:0;border-radius:16px;padding:14px 20px;
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);color:#fff;
    font-weight:900;cursor:pointer;
}
.xyn-submit:disabled{opacity:.55;cursor:not-allowed}
.xyn-table-wrap{overflow-x:auto}
.xyn-table{width:100%;border-collapse:separate;border-spacing:0 7px;font-size:13px}
.xyn-table th{
    color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.08em;
    text-align:left;padding:0 12px 8px;
}
.xyn-table td{
    background:#131929;padding:14px 12px;border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
}
.xyn-table td:first-child{border-left:1px solid rgba(255,255,255,.07);border-radius:15px 0 0 15px}
.xyn-table td:last-child{border-right:1px solid rgba(255,255,255,.07);border-radius:0 15px 15px 0}
.xyn-type{display:inline-flex;align-items:center;gap:7px;font-weight:900}
.xyn-dot{width:28px;height:28px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center}
.xyn-buy{background:rgba(239,68,68,.13);color:#f87171}
.xyn-sell{background:rgba(16,185,129,.13);color:#34d399}
.xyn-ref{font-family:monospace;color:#9ca3af;font-size:12px}
.xyn-empty{text-align:center;color:#6b7280;padding:35px!important}
.xyn-alert{
    padding:14px 16px;border-radius:16px;font-weight:800;
    background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.25);
    margin-bottom:16px;
}
.xyn-success{
    padding:14px 16px;border-radius:16px;font-weight:800;
    background:rgba(16,185,129,.12);color:#6ee7b7;border:1px solid rgba(16,185,129,.25);
    margin-bottom:16px;
}
@media(max-width:1000px){.xyn-grid{grid-template-columns:1fr}.xyn-form-grid{grid-template-columns:1fr}}
.xyn-pagination{
    margin-top:18px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
    padding:14px 16px;
    background:#131929;
    border:1px solid rgba(255,255,255,.07);
    border-radius:18px;
}

.xyn-page-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:40px;
    padding:0 16px;
    border-radius:13px;
    background:linear-gradient(90deg,#7c5cfc,#06b6d4);
    color:#fff!important;
    font-size:13px;
    font-weight:900;
    text-decoration:none!important;
}

.xyn-page-btn.disabled{
    background:#0e1220;
    color:#6b7280!important;
    border:1px solid rgba(255,255,255,.07);
    cursor:not-allowed;
}

.xyn-page-info{
    color:#9ca3af;
    font-size:13px;
    font-weight:800;
}
</style>
@endpush

@section('content')
@php
    $onlineMerchants = collect($merchants)->filter(function ($m) {
        return $m->is_online == true || $m->is_online == 1 || $m->is_online === '1';
    });
@endphp

<div class="xyn-page">

  
    <div class="xyn-grid">
        <div class="xyn-form-card">
            <h2 style="font-size:18px;font-weight:900;margin-bottom:18px;">Create New Request</h2>

            @if(session('success'))
                <div class="xyn-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="xyn-alert">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('client.requests.store') }}">
                @csrf

                <div class="xyn-form-grid">
                    <div class="xyn-field">
                        <label>Request Type</label>
                        <select name="type" id="type" required>
                            <option value="withdrawal" {{ old('type') === 'withdrawal' ? 'selected' : '' }}>SELL USD</option>
                            <option value="deposit" {{ old('type') === 'deposit' ? 'selected' : '' }}>BUY USD</option>
                        </select>
                    </div>

                    <div class="xyn-field">
                        <label>USD Amount</label>
                        <input type="number" step="0.01" min="1" name="amount" id="amount" value="{{ old('amount') }}" required>
                    </div>

                    <div class="xyn-field">
                        <label>Online Merchant Only</label>
                        <select name="merchant_id" required>
                            <option value="">Select Online Merchant</option>
                            @forelse($onlineMerchants as $m)
                                <option value="{{ $m->id }}" {{ old('merchant_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }} - ONLINE
                                </option>
                            @empty
                                <option value="" disabled>No online merchants available</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div class="xyn-field" style="margin-top:16px;">
                    <label>Note</label>
                    <textarea name="note" rows="3">{{ old('note') }}</textarea>
                </div>

                <button type="submit" class="xyn-submit" {{ $onlineMerchants->isEmpty() ? 'disabled' : '' }}>
                    Submit Request
                </button>
            </form>
        </div>

        <div class="xyn-rate-card">
            <div class="xyn-rate-title">Rate Summary</div>

            <div class="xyn-rate-row"><span>USD Rate</span><strong>${{ number_format((float)$config->usd_rate, 4) }}</strong></div>
            <div class="xyn-rate-row"><span>INR Rate</span><strong>₹{{ number_format((float)$config->inr_rate, 4) }}</strong></div>
            <div class="xyn-rate-row"><span>Xynder Fee</span><strong>₹{{ number_format((float)$config->xynder_fee, 2) }}</strong></div>
            <div class="xyn-rate-row"><span>Network Fee</span><strong>₹{{ number_format((float)$config->network_fee, 2) }}</strong></div>

            <div class="xyn-total-box">
                <div style="color:#9ca3af;font-size:12px;font-weight:900;text-transform:uppercase;">Converted INR</div>
                <div style="font-size:18px;font-weight:900;">₹<span id="converted">0.00</span></div>

                <div style="height:1px;background:rgba(255,255,255,.09);margin:14px 0;"></div>

                <div style="color:#9ca3af;font-size:12px;font-weight:900;text-transform:uppercase;">Total INR</div>
                <div class="big">₹<span id="total">0.00</span></div>
            </div>
        </div>
    </div>

    <div class="xyn-table-card">
        <h2 style="font-size:18px;font-weight:900;margin-bottom:18px;">My Wallet Requests</h2>

        <div class="xyn-table-wrap">
            <table class="xyn-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Type</th>
                        <th>Merchant</th>
                        <th>USD</th>
                        <th>INR Rate</th>
                        <th>Xynder Fee</th>
                        <th>Network Fee</th>
                        <th>Total INR</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                        <tr>
                            <td class="xyn-ref">{{ $r->transaction_no ?? 'TNS'.str_pad($r->id, 9, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <span class="xyn-type">
                                    <span class="xyn-dot {{ $r->type === 'deposit' ? 'xyn-buy' : 'xyn-sell' }}">
                                        {{ $r->type === 'deposit' ? 'B' : 'S' }}
                                    </span>
                                    {{ $r->type === 'deposit' ? 'BUY USD' : 'SELL USD' }}
                                </span>
                            </td>
                            <td>{{ $r->merchant->name ?? '-' }}</td>
                            <td>${{ number_format((float)$r->amount, 2) }}</td>
                            <td>₹{{ number_format((float)$r->inr_rate, 4) }}</td>
                            <td>₹{{ number_format((float)$r->xynder_fee, 2) }}</td>
                            <td>₹{{ number_format((float)$r->network_fee, 2) }}</td>
                            <td style="font-weight:900;color:#34d399;">₹{{ number_format((float)$r->total_amount, 2) }}</td>
                            <td><span class="badge {{ $r->status }}">{{ strtoupper($r->status) }}</span></td>
                            <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="xyn-empty">No requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

       @if ($requests->hasPages())
    <div class="xyn-pagination">
        @if ($requests->onFirstPage())
            <span class="xyn-page-btn disabled">‹ Previous</span>
        @else
            <a class="xyn-page-btn" href="{{ $requests->previousPageUrl() }}">‹ Previous</a>
        @endif

        <span class="xyn-page-info">
            Page {{ $requests->currentPage() }} of {{ $requests->lastPage() }}
            · Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }}
        </span>

        @if ($requests->hasMorePages())
            <a class="xyn-page-btn" href="{{ $requests->nextPageUrl() }}">Next ›</a>
        @else
            <span class="xyn-page-btn disabled">Next ›</span>
        @endif
    </div>
@endif
    </div>
</div>

<script>
const amountInput = document.getElementById('amount');

const inrRate = {{ (float)$config->inr_rate }};
const xynderFee = {{ (float)$config->xynder_fee }};
const networkFee = {{ (float)$config->network_fee }};

function calculateTotal() {
    const amount = parseFloat(amountInput.value || 0);
    const converted = amount * inrRate;
    const total = converted + xynderFee + networkFee;

    document.getElementById('converted').innerText = converted.toFixed(2);
    document.getElementById('total').innerText = total.toFixed(2);
}

amountInput.addEventListener('input', calculateTotal);
calculateTotal();
</script>
@endsection
@extends('layouts.admin', ['title' => 'Wallet Requests'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --bg:#11161a;--hero:#2b2f32;--box:#2b2f32;--dark:#101518;--red:#e8192c;--red2:#c91022;
    --green:#0ecb81;--green2:#0aac6c;--text:#fff;--muted:#aeb4ba;--muted2:#747b82;
    --input:#f4f4f4;--gold:#ffc933;--line:#3b4248;
}
*{box-sizing:border-box}
.rq-page{margin:-24px;background:var(--dark);min-height:100vh;color:var(--text);font-family:Inter,Arial,sans-serif;padding-bottom:60px}
.rq-hero{position:relative;min-height:500px;padding:70px 85px 120px;background:#2b2f32;overflow:hidden}
.rq-hero::after{content:"";position:absolute;inset:0;opacity:.08;background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);background-size:260px 260px}
.rq-content{position:relative;z-index:2;max-width:560px}
.rq-title{font-size:48px;line-height:1.15;font-weight:900;margin:0 0 22px}
.rq-title span{display:block;color:var(--red)}
.rq-art{position:absolute;right:90px;top:55px;width:540px;height:370px;z-index:1}
.phone{position:absolute;bottom:25px;width:150px;height:275px;border:7px solid #151b20;border-radius:28px;background:#22282c}
.phone.left{left:70px}.phone.right{right:70px}
.phone-icon{width:78px;height:78px;border:3px solid var(--red);border-radius:50%;margin:28px auto;display:flex;align-items:center;justify-content:center;font-size:42px;color:#fff}
.phone-row{width:112px;height:34px;background:var(--red);border-radius:8px;margin:12px auto;display:flex;align-items:center;gap:8px;padding:8px}
.phone-row:nth-child(3){background:#4b4be8}
.chip{width:24px;height:17px;background:var(--gold);border-radius:4px}
.line{height:7px;flex:1;background:#fff;border-radius:20px}
.down{position:absolute;top:38px;left:50%;transform:translateX(-50%);font-size:88px;color:var(--red)}
.coin-stack{position:absolute;bottom:52px;left:32px;width:110px}
.coin-stack span{display:block;height:10px;margin-bottom:4px;background:var(--gold);border-radius:20px}
.big-coin{position:absolute;bottom:38px;left:15px;width:78px;height:78px;border-radius:50%;background:#ffd247;border:6px solid #ffbf1d}
.arc{position:absolute;top:0;left:160px;width:245px;height:120px;border-top:4px dashed var(--red);border-radius:180px 180px 0 0}
.arc.blue{top:28px;left:190px;width:180px;height:85px;border-color:#4545ff}
.coin{position:absolute;width:44px;height:44px;background:var(--gold);border:4px solid #e8a000;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#e09100;font-weight:900}
.c1{top:0;left:170px}.c2{top:-22px;left:290px}.c3{top:0;right:65px}.c4{top:62px;left:118px}.c5{top:62px;right:5px}

.rq-box{position:relative;z-index:5;max-width:980px;margin:-88px 0 0 85px;background:var(--box);border:1.5px solid var(--red);border-radius:7px;overflow:hidden;box-shadow:0 18px 40px rgba(0,0,0,.28)}
.rq-tabs{display:flex;border-bottom:1px solid var(--line);background:#24292d}
.rq-tab{appearance:none;border:0;border-right:1px solid var(--line);background:transparent;color:#9fa5aa;cursor:pointer;padding:16px 24px;font-weight:900;display:flex;gap:8px;align-items:center}
.rq-tab.active.sell{background:rgba(232,25,44,.12);color:var(--red);box-shadow:inset 0 -3px 0 var(--red)}
.rq-tab.active.buy{background:rgba(14,203,129,.12);color:var(--green);box-shadow:inset 0 -3px 0 var(--green)}
.rq-box-inner{padding:40px}
.rq-form-row{display:grid;grid-template-columns:1fr 36px 1fr 220px;gap:16px;align-items:center}
.rq-input-box{height:56px;background:var(--input);color:#222;display:grid;grid-template-columns:1fr 92px;border-radius:3px;overflow:hidden}
.rq-input-left{padding:10px 16px}
.rq-small{color:#6b6b6b;font-size:11px;font-weight:800;margin-bottom:3px}
.rq-input-left input{width:100%;border:0;outline:0;background:transparent;color:#333;font-size:18px;font-weight:900}
.rq-value{color:#333;font-size:18px;font-weight:900}
.rq-currency{border-left:1px solid #d1d1d1;padding:9px 14px;display:flex;flex-direction:column;justify-content:center;color:#555}
.rq-currency strong{font-size:17px;color:#444}
.rq-swap{width:32px;height:32px;border-radius:50%;background:#161b1f;border:0;color:#747b82;font-size:18px;cursor:pointer}
.rq-submit{height:56px;border:0;border-radius:3px;background:var(--red);color:#fff;font-size:14px;font-weight:900;cursor:pointer}
.rq-submit.buy{background:var(--green)}
.rq-submit:hover{background:var(--red2)}
.rq-submit.buy:hover{background:var(--green2)}
.rq-submit:disabled{background:#1f2428;color:#6b7280;cursor:not-allowed}
.rq-extra{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:22px}
.rq-field label{display:flex;align-items:center;gap:8px;color:#c3c6ca;font-size:12px;font-weight:900;margin-bottom:8px}
.rq-online{color:var(--green);background:#0d2b1e;border:1px solid #1a4a35;border-radius:20px;padding:2px 8px;font-size:11px}
.rq-select,.rq-textarea{width:100%;background:#1f2428;color:#fff;border:1px solid #3b4248;border-radius:4px;padding:13px 14px;outline:0;font-weight:700}
.rq-select:focus,.rq-textarea:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(232,25,44,.12)}
.rq-textarea{min-height:48px;resize:vertical}
.rq-alert{margin-bottom:18px;padding:13px 16px;border-radius:4px;font-weight:800}
.rq-alert.ok{background:#0d2b1e;color:#0ecb81}
.rq-alert.err{background:#3a1018;color:#ff6b7b}

.rq-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin:100px 85px 0}
.rq-card{background:#2b2f32;border-radius:4px;padding:22px 20px;display:flex;align-items:center;justify-content:space-between;min-height:76px}
.rq-pair{display:flex;align-items:center;gap:12px;font-weight:900}
.rq-token{width:36px;height:36px;border-radius:50%;background:#e4a719;border:2px solid #ffc94d;display:flex;align-items:center;justify-content:center;color:#2b2f32;font-weight:900}
.rq-change{font-size:12px;color:#9fa5aa;line-height:1.5;text-align:right}
.rq-change b{color:var(--red)}
.rq-change.buy b{color:var(--green)}

.rq-history{margin:34px 85px 0;background:#2b2f32;border-radius:6px;overflow:hidden}
.rq-history-head{padding:18px 22px;border-bottom:1px solid #3b4248;font-weight:900;display:flex;gap:10px;align-items:center}
.rq-table-wrap{overflow-x:auto}
.rq-table{width:100%;min-width:980px;border-collapse:collapse}
.rq-table th{color:#9fa5aa;font-size:11px;text-transform:uppercase;text-align:left;padding:14px 16px;background:#24292d}
.rq-table td{padding:16px;border-top:1px solid #3a4147;color:#c9ced3;font-size:13px;font-weight:700;white-space:nowrap}
.rq-table tr:hover td{background:#30363a}
.rq-click-row{cursor:pointer;transition:.18s}
.rq-ref{font-family:monospace;color:#9fa5aa}
.rq-type{display:inline-flex;align-items:center;gap:8px;font-weight:900}
.rq-type.buy{color:var(--green)}
.rq-type.sell{color:var(--red)}
.rq-amount,.rq-total{color:#fff;font-weight:900}
.rq-closed-text{color:#d5dade;font-weight:900}
.rq-closed-muted{color:#747b82;font-weight:900}
.badge{padding:5px 10px;border-radius:20px;font-size:11px;font-weight:900;text-transform:uppercase}
.badge.pending{background:#3b2a09;color:#ffc933}
.badge.approved,.badge.completed{background:#0d2b1e;color:#0ecb81}
.badge.rejected{background:#3a1018;color:#ff6b7b}
.badge.closed{background:#1f2428;color:#d5dade;border:1px solid #3b4248}
.rq-empty{text-align:center;padding:45px;color:#9fa5aa}
.rq-pagination{padding:16px 22px;border-top:1px solid #3a4147;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.rq-page-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 14px;background:#1f2428;color:#fff;text-decoration:none;border-radius:4px;font-size:13px;font-weight:800;margin-left:6px}
.rq-page-btn.disabled{opacity:.4;pointer-events:none}

@media(max-width:1100px){.rq-art{opacity:.25;right:20px}.rq-form-row{grid-template-columns:1fr}.rq-swap{margin:auto}.rq-cards{grid-template-columns:1fr}}
@media(max-width:760px){.rq-page{margin:-16px}.rq-hero{padding:45px 24px 120px}.rq-title{font-size:36px}.rq-box{margin:-80px 20px 0}.rq-box-inner{padding:28px 20px}.rq-extra{grid-template-columns:1fr}.rq-cards,.rq-history{margin-left:20px;margin-right:20px}.rq-tabs{overflow-x:auto}.rq-tab{min-width:130px;justify-content:center}}
</style>
@endpush

@section('content')
@php
    $onlineMerchants = collect($merchants)->filter(fn($m) =>
        $m->is_online == true || $m->is_online == 1 || $m->is_online === '1'
    );

    $totalFees = (float)$config->xynder_fee + (float)$config->network_fee;
    $oldType = old('type', 'withdrawal');
@endphp

<div class="rq-page">

    <section class="rq-hero">
        <div class="rq-content">
            <h1 class="rq-title">
                Secure Wallet
                <span>Buy & Sell Request</span>
            </h1>
        </div>

        <div class="rq-art">
            <div class="arc"></div>
            <div class="arc blue"></div>

            <div class="coin c1">$</div>
            <div class="coin c2">$</div>
            <div class="coin c3">$</div>
            <div class="coin c4">$</div>
            <div class="coin c5">$</div>

            <div class="phone left">
                <div class="phone-icon"><i class="ti ti-user-filled"></i></div>
                <div class="phone-row"><div class="chip"></div><div class="line"></div></div>
                <div class="phone-row"><div class="chip"></div><div class="line"></div></div>
                <div class="phone-row"><div class="chip"></div><div class="line"></div></div>
            </div>

            <div class="phone right">
                <i class="ti ti-arrow-down down"></i>
                <div class="coin-stack">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
                <div class="big-coin"></div>
            </div>
        </div>
    </section>

    <section class="rq-box" id="requestBox">
        <div class="rq-tabs">
            <button class="rq-tab sell active" type="button" id="tabSell">
                <i class="ti ti-trending-down"></i> Sell USD
            </button>

            <button class="rq-tab buy" type="button" id="tabBuy">
                <i class="ti ti-trending-up"></i> Buy USD
            </button>
        </div>

        <div class="rq-box-inner">
            @if(session('success'))
                <div class="rq-alert ok">
                    <i class="ti ti-circle-check"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rq-alert err">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('client.requests.store') }}" id="reqForm">
                @csrf
                <input type="hidden" name="type" id="typeHidden" value="{{ $oldType }}">

                <div class="rq-form-row">
                    <div class="rq-input-box">
                        <div class="rq-input-left">
                            <div class="rq-small" id="sendLabel"></div>
                            <input type="number" step="0.01" min="1" name="amount" id="amount"
                                   value="{{ old('amount') }}" placeholder="45" required>
                        </div>
                        <div class="rq-currency">
                            <small>Currency</small>
                            <strong>USD</strong>
                        </div>
                    </div>

                    <button class="rq-swap" type="button" id="swapBtn" title="Switch Buy / Sell">
                        <i class="ti ti-arrows-exchange"></i>
                    </button>

                    <div class="rq-input-box">
                        <div class="rq-input-left">
                            <div class="rq-small" id="receiveLabel"></div>
                            <div class="rq-value">₹<span id="totalDisp">0.00</span></div>
                        </div>
                        <div class="rq-currency">
                            <small>Currency</small>
                            <strong>INR</strong>
                        </div>
                    </div>

                    <button type="submit" class="rq-submit" id="submitBtn" {{ $onlineMerchants->isEmpty() ? 'disabled' : '' }}>
                        <span id="btnLabel">Place Sell Order</span>
                    </button>
                </div>

                <div class="rq-extra">
                    <div class="rq-field">
                        <label for="merchant_id">
                            Select Merchant
                            <span class="rq-online">{{ $onlineMerchants->count() }} online</span>
                        </label>

                        <select class="rq-select" name="merchant_id" id="merchant_id" required>
                            <option value="">— Choose a merchant —</option>
                            @forelse($onlineMerchants as $m)
                                <option value="{{ $m->id }}" {{ old('merchant_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }}
                                </option>
                            @empty
                                <option value="" disabled>No merchants online right now</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="rq-field">
                        <label for="note">Note Optional</label>
                        <textarea class="rq-textarea" name="note" id="note" placeholder="Any instructions for the merchant...">{{ old('note') }}</textarea>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="rq-cards">
        <div class="rq-card">
            <div class="rq-pair">
                <span class="rq-token">$</span> USD <span>→</span>
                <span class="rq-token">₹</span> INR
            </div>
            <div class="rq-change">
                <b>{{ number_format((float)$config->inr_rate, 2) }}</b> INR Rate<br>
                Fee ₹{{ number_format($totalFees, 2) }}
            </div>
        </div>

        <div class="rq-card">
            <div class="rq-pair">
                <span class="rq-token"><i class="ti ti-users"></i></span> Merchants
            </div>
            <div class="rq-change buy">
                <b>{{ $onlineMerchants->count() }}</b> Online<br>
                Available now
            </div>
        </div>

        <div class="rq-card">
            <div class="rq-pair">
                <span class="rq-token"><i class="ti ti-receipt"></i></span> Requests
            </div>
            <div class="rq-change">
                <b>Buy / Sell</b><br>
                Full history below
            </div>
        </div>
    </section>

    <section class="rq-history">
        <div class="rq-history-head">
            <i class="ti ti-list-details"></i>
            My Wallet Requests
        </div>

        <div class="rq-table-wrap">
            <table class="rq-table">
                <thead>
                    <tr>
                        <th>Ref No</th>
                        <th>Type</th>
                        <th>Merchant</th>
                        <th>USD Amount</th>
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
                        @php
                            $isBuy = $r->type === 'deposit';
                            $isClosed = $r->status === 'closed';
                        @endphp

                        <tr class="rq-click-row" onclick="window.location='{{ route('client.chats.request', $r) }}'">
                            <td>
                                <span class="rq-ref">
                                    {{ $r->transaction_no ?? 'TNS'.str_pad($r->id, 9, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <td>
                                @if($isBuy)
                                    <span class="rq-type buy">
                                        <i class="ti ti-trending-up"></i> Buy USD
                                    </span>
                                @else
                                    <span class="rq-type sell">
                                        <i class="ti ti-trending-down"></i> Sell USD
                                    </span>
                                @endif
                            </td>

                            <td>{{ $r->merchant->name ?? '—' }}</td>

                            <td>
                                @if($isClosed)
                                    <span class="rq-closed-text">Transaction Closed</span>
                                @else
                                    <span class="rq-amount">${{ number_format((float)$r->amount, 2) }}</span>
                                @endif
                            </td>

                            <td>
                                @if($isClosed)
                                    <span class="rq-closed-muted">—</span>
                                @else
                                    ₹{{ number_format((float)$r->inr_rate, 4) }}
                                @endif
                            </td>

                            <td>
                                @if($isClosed)
                                    <span class="rq-closed-muted">—</span>
                                @else
                                    ₹{{ number_format((float)$r->xynder_fee, 2) }}
                                @endif
                            </td>

                            <td>
                                @if($isClosed)
                                    <span class="rq-closed-muted">—</span>
                                @else
                                    ₹{{ number_format((float)$r->network_fee, 2) }}
                                @endif
                            </td>

                            <td>
                                @if($isClosed)
                                    <span class="rq-closed-text">Closed</span>
                                @else
                                    <span class="rq-total">₹{{ number_format((float)$r->total_amount, 2) }}</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $r->status }}">
                                    {{ $isClosed ? 'closed' : $r->status }}
                                </span>
                            </td>

                            <td>{{ $r->created_at?->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="rq-empty">No requests yet. Create your first request above.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="rq-pagination">
                <div>
                    Page {{ $requests->currentPage() }} of {{ $requests->lastPage() }}
                    @if($requests->total())
                        · {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }}
                    @endif
                </div>

                <div>
                    @if($requests->onFirstPage())
                        <span class="rq-page-btn disabled"><i class="ti ti-chevron-left"></i> Previous</span>
                    @else
                        <a class="rq-page-btn" href="{{ $requests->previousPageUrl() }}">
                            <i class="ti ti-chevron-left"></i> Previous
                        </a>
                    @endif

                    @if($requests->hasMorePages())
                        <a class="rq-page-btn" href="{{ $requests->nextPageUrl() }}">
                            Next <i class="ti ti-chevron-right"></i>
                        </a>
                    @else
                        <span class="rq-page-btn disabled">
                            Next <i class="ti ti-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>

<script>
(function(){
    const INR_RATE = {{ (float)$config->inr_rate }};
    const TOTAL_FEES = {{ $totalFees }};

    const amount = document.getElementById('amount');
    const totalDisp = document.getElementById('totalDisp');
    const typeHidden = document.getElementById('typeHidden');

    const tabSell = document.getElementById('tabSell');
    const tabBuy = document.getElementById('tabBuy');
    const swapBtn = document.getElementById('swapBtn');

    const submitBtn = document.getElementById('submitBtn');
    const btnLabel = document.getElementById('btnLabel');
    const sendLabel = document.getElementById('sendLabel');
    const receiveLabel = document.getElementById('receiveLabel');

    function calc(){
        const usd = parseFloat(amount.value) || 0;
        const converted = usd * INR_RATE;
        let total = 0;

        if(typeHidden.value === 'deposit'){
            total = converted + TOTAL_FEES;
        }else{
            total = converted - TOTAL_FEES;
        }

        totalDisp.textContent = total.toFixed(2);
    }

    function setType(type){
        typeHidden.value = type;

        if(type === 'deposit'){
            tabBuy.classList.add('active');
            tabSell.classList.remove('active');
            submitBtn.classList.add('buy');
            btnLabel.textContent = 'Place Buy Order';
            sendLabel.textContent = 'You Buy';
            receiveLabel.textContent = 'You Pay';
        }else{
            tabSell.classList.add('active');
            tabBuy.classList.remove('active');
            submitBtn.classList.remove('buy');
            btnLabel.textContent = 'Place Sell Order';
            sendLabel.textContent = 'You Sell';
            receiveLabel.textContent = 'You Receive';
        }

        calc();
    }

    tabSell.addEventListener('click', function(){ setType('withdrawal'); });
    tabBuy.addEventListener('click', function(){ setType('deposit'); });
    swapBtn.addEventListener('click', function(){
        setType(typeHidden.value === 'deposit' ? 'withdrawal' : 'deposit');
    });

    amount.addEventListener('input', calc);

    setType('{{ $oldType }}');
    calc();
})();
</script>
@endsection
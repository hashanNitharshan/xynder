@extends('layouts.admin', ['title' => 'Wallet Requests'])

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
    --border-faint:#202020;

    --orange:#F0B90B;
    --amber:#C99400;
    --gold:#FFD45A;

    --red:#ef4444;
    --green:#0ecb81;
    --green2:#0aac6c;

    --text:#fff;
    --muted:#848E9C;
    --muted2:#5e6673;
    --shadow:0 18px 45px rgba(0,0,0,.35);
}
*{box-sizing:border-box}

.rq-page{
    margin:-28px;
    background:var(--bg);
    min-height:100vh;
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding:24px 36px 48px;
}

/* ===== Hero ===== */
.rq-hero{position:relative;min-height:260px;padding:24px 28px 84px;background:var(--surface);overflow:hidden;border-bottom:1px solid var(--border)}
.rq-hero::after{content:"";position:absolute;inset:0;opacity:.06;background-image:linear-gradient(120deg,transparent 20%,rgba(240,185,11,.5) 21%,transparent 22%);background-size:260px 260px}
.rq-content{position:relative;z-index:2;max-width:560px}
.rq-eyebrow{color:var(--orange);font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin-bottom:14px}
.rq-title{font-size:24px;line-height:1.18;font-weight:800;margin:0 0 18px;color:#fff}
.rq-title span{display:block;color:var(--orange)}
.rq-sub{color:var(--muted);font-size:12px;font-weight:700;line-height:1.7;max-width:520px}

.rq-art{position:absolute;right:45px;top:18px;width:420px;height:260px;z-index:1;opacity:.9}
.phone{position:absolute;bottom:25px;width:150px;height:275px;border:7px solid #0B0E11;border-radius:28px;background:var(--surface-alt)}
.phone.left{left:70px}.phone.right{right:70px}
.phone-icon{width:78px;height:78px;border:3px solid var(--orange);border-radius:50%;margin:28px auto;display:flex;align-items:center;justify-content:center;font-size:42px;color:var(--orange)}
.phone-row{width:112px;height:34px;background:var(--orange);border-radius:14px;margin:12px auto;display:flex;align-items:center;gap:8px;padding:8px}
.phone-row:nth-child(3){background:var(--amber)}
.chip{width:24px;height:17px;background:#0B0E11;border-radius:4px;opacity:.35}
.line{height:7px;flex:1;background:#0B0E11;border-radius:20px;opacity:.35}
.down{position:absolute;top:38px;left:50%;transform:translateX(-50%);font-size:88px;color:var(--orange)}
.coin-stack{position:absolute;bottom:52px;left:32px;width:110px}
.coin-stack span{display:block;height:10px;margin-bottom:4px;background:var(--gold);border-radius:20px}
.big-coin{position:absolute;bottom:38px;left:15px;width:78px;height:78px;border-radius:50%;background:var(--gold);border:6px solid var(--orange)}
.arc{position:absolute;top:0;left:160px;width:245px;height:120px;border-top:4px dashed var(--orange);border-radius:180px 180px 0 0}
.arc.blue{top:28px;left:190px;width:180px;height:85px;border-color:var(--amber)}
.coin{position:absolute;width:44px;height:44px;background:var(--gold);border:4px solid var(--orange);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#0B0E11;font-weight:800}
.c1{top:0;left:170px}.c2{top:-22px;left:290px}.c3{top:0;right:65px}.c4{top:62px;left:118px}.c5{top:62px;right:5px}

/* ===== Order box ===== */
.rq-box{position:relative;z-index:5;max-width:1180px;margin:-56px 0 0 0;background:var(--surface);border:1.5px solid var(--orange);border-radius:14px;overflow:hidden;box-shadow:var(--shadow)}
.rq-tabs{display:flex;border-bottom:1px solid var(--border);background:var(--surface-alt)}
.rq-tab{appearance:none;border:0;border-right:1px solid var(--border);background:transparent;color:var(--muted);cursor:pointer;padding:16px 24px;font-weight:800;display:flex;gap:8px;align-items:center;font-size:14px}
.rq-tab.active.sell{background:rgba(239,68,68,.1);color:var(--red);box-shadow:inset 0 -3px 0 var(--red)}
.rq-tab.active.buy{background:rgba(14,203,129,.1);color:var(--green);box-shadow:inset 0 -3px 0 var(--green)}
.rq-box-inner{padding:20px}
.rq-form-row{display:grid;grid-template-columns:1fr 36px 1fr 200px;gap:16px;align-items:center}
.rq-input-box{height:56px;background:var(--surface-alt);border:1px solid var(--border);color:#fff;display:grid;grid-template-columns:1fr 92px;border-radius:4px;overflow:hidden}
.rq-input-left{padding:10px 16px}
.rq-small{color:var(--muted);font-size:11px;font-weight:700;margin-bottom:3px;text-transform:uppercase;letter-spacing:.04em}
.rq-input-left input{width:100%;border:0;outline:0;background:transparent;color:#fff;font-size:18px;font-weight:800}
.rq-value{color:#fff;font-size:18px;font-weight:800}
.rq-currency{border-left:1px solid var(--border);padding:9px 14px;display:flex;flex-direction:column;justify-content:center;color:var(--muted)}
.rq-currency strong{font-size:16px;color:#fff;font-weight:800}
.rq-swap{width:32px;height:32px;border-radius:50%;background:var(--surface-alt);border:1px solid var(--border);color:var(--orange);font-size:18px;cursor:pointer}
.rq-submit{height:56px;border:0;border-radius:4px;background:var(--red);color:#fff;font-size:13px;font-weight:700;cursor:pointer;transition:.15s}
.rq-submit.buy{background:var(--green)}
.rq-submit:hover{filter:brightness(1.1)}
.rq-submit:disabled{background:var(--surface-alt);color:var(--muted2);cursor:not-allowed}

.rq-extra{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:22px}
.rq-field label{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:12px;font-weight:700;margin-bottom:8px;text-transform:uppercase;letter-spacing:.03em}
.rq-online{color:var(--green);background:rgba(14,203,129,.1);border:1px solid rgba(14,203,129,.3);border-radius:20px;padding:2px 8px;font-size:11px;font-weight:800}
.rq-select,.rq-textarea{width:100%;background:var(--surface-alt);color:#fff;border:1px solid var(--border);border-radius:4px;padding:13px 14px;outline:0;font-weight:500;font-size:13.5px}
.rq-select:focus,.rq-textarea:focus{border-color:var(--orange)}
.rq-textarea{min-height:48px;resize:vertical}
.rq-alert{margin-bottom:18px;padding:13px 16px;border-radius:4px;font-weight:700;font-size:14px}
.rq-alert.ok{background:rgba(14,203,129,.1);color:var(--green);border:1px solid rgba(14,203,129,.3)}
.rq-alert.err{background:rgba(239,68,68,.1);color:#ff9b9b;border:1px solid rgba(239,68,68,.3)}
.rq-popup-backdrop{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.72);
    z-index:9999;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:18px;
}

.rq-popup{
    width:100%;
    max-width:390px;
    background:#181A20;
    border:1px solid #F0B90B;
    border-radius:14px;
    padding:24px;
    color:#fff;
    text-align:center;
    box-shadow:0 18px 50px rgba(0,0,0,.55);
}

.rq-popup h3{
    margin:0 0 10px;
    color:#F0B90B;
    font-size:22px;
}

.rq-popup p{
    margin:8px 0;
    color:#848E9C;
    font-size:14px;
}

.rq-popup-actions{
    display:flex;
    gap:10px;
    margin-top:22px;
}

.rq-popup-chat,
.rq-popup-close{
    flex:1;
    border:0;
    border-radius:14px;
    padding:12px 14px;
    font-weight:800;
    cursor:pointer;
    text-decoration:none;
    font-size:14px;
}

.rq-popup-chat{
    background:#F0B90B;
    color:#0B0E11;
}

.rq-popup-close{
    background:#1E2329;
    color:#fff;
    border:1px solid #2B3139;
}
/* merchant reveal panel */
.rq-merchant-wrap{
    grid-column:1 / -1;
    overflow:hidden;
    max-height:0;
    opacity:0;
    transition:max-height .35s ease,opacity .3s ease,margin-top .35s ease;
    margin-top:0;
}
.rq-merchant-wrap.show{
    max-height:400px;
    opacity:1;
    margin-top:22px;
}
.rq-merchant-hint{
    display:flex;
    align-items:center;
    gap:8px;
    color:var(--muted2);
    font-size:13px;
    font-weight:400;
    padding:14px 0;
}
.rq-merchant-hint i{color:var(--orange);font-size:17px}

/* ===== Info cards ===== */
.rq-cards{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin:20px 0 0}
.rq-card{background:var(--surface);border:1px solid var(--border);border-radius:6px;padding:22px 20px;display:flex;align-items:center;justify-content:space-between;min-height:76px}
.rq-pair{display:flex;align-items:center;gap:12px;font-weight:800}
.rq-token{width:36px;height:36px;border-radius:50%;background:rgba(240,185,11,.12);border:1px solid rgba(240,185,11,.4);display:flex;align-items:center;justify-content:center;color:var(--orange);font-weight:800}
.rq-change{font-size:12px;color:var(--muted);line-height:1.5;text-align:right}
.rq-change b{color:var(--orange);font-size:14px}
.rq-change.buy b{color:var(--green)}

/* ===== History + filters ===== */
.rq-history{margin:20px 0 0;background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden}
.rq-history-head{padding:18px 22px;border-bottom:1px solid var(--border);font-weight:800;display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap}
.rq-history-title{display:flex;gap:10px;align-items:center}
.rq-history-title i{color:var(--orange)}

.rq-filters{display:flex;gap:10px;flex-wrap:wrap;padding:16px 22px;border-bottom:1px solid var(--border);background:var(--surface-alt)}
.rq-filters input,
.rq-filters select{
    background:var(--bg);
    border:1px solid var(--border);
    color:#fff;
    border-radius:4px;
    padding:9px 12px;
    font-size:13px;
    font-weight:400;
    outline:0;
    min-width:150px;
}
.rq-filters input:focus,
.rq-filters select:focus{border-color:var(--orange)}
.rq-filters input::placeholder{color:var(--muted2)}
.rq-filter-btn{
    background:var(--orange);
    border:0;
    color:#0B0E11;
    border-radius:4px;
    padding:9px 18px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
}
.rq-filter-btn:hover{background:var(--gold)}
.rq-filter-reset{
    background:transparent;
    border:1px solid var(--border);
    color:var(--muted);
    border-radius:4px;
    padding:9px 14px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
}
.rq-filter-reset:hover{color:#fff;border-color:var(--muted)}

.rq-table-wrap{overflow-x:auto}
.rq-table{width:100%;min-width:980px;border-collapse:collapse}
.rq-table th{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.04em;text-align:left;padding:14px 16px;background:var(--surface-alt);font-weight:800}
.rq-table td{padding:16px;border-top:1px solid var(--border);color:#d5dade;font-size:13px;font-weight:500;white-space:nowrap}
.rq-table tr:hover td{background:var(--surface-alt)}
.rq-click-row{cursor:pointer;transition:.18s}
.rq-ref{font-family:monospace;color:var(--muted)}
.rq-type{display:inline-flex;align-items:center;gap:8px;font-weight:800}
.rq-type.buy{color:var(--green)}
.rq-type.sell{color:var(--red)}
.rq-amount,.rq-total{color:#fff;font-weight:800}
.rq-closed-text{color:var(--muted);font-weight:800}
.rq-closed-muted{color:var(--muted2);font-weight:800}
.badge{padding:5px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase}
.badge.pending{background:rgba(240,185,11,.12);color:var(--orange)}
.badge.approved,.badge.completed{background:rgba(14,203,129,.1);color:var(--green)}
.badge.rejected{background:rgba(239,68,68,.1);color:#ff9b9b}
.badge.closed{background:var(--surface-alt);color:var(--muted);border:1px solid var(--border)}
.rq-empty{text-align:center;padding:45px;color:var(--muted)}
.rq-pagination{padding:16px 22px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;color:var(--muted);font-size:13px}
.rq-page-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 14px;background:var(--surface-alt);border:1px solid var(--border);color:#fff;text-decoration:none;border-radius:4px;font-size:13px;font-weight:700;margin-left:6px}
.rq-page-btn:hover{border-color:var(--orange)}
.rq-page-btn.disabled{opacity:.4;pointer-events:none}


/* ===== Success popup ===== */
.rq-modal-backdrop{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.72);
    z-index:9999;
    display:none;
    align-items:center;
    justify-content:center;
    padding:18px;
}
.rq-modal-backdrop.show{display:flex}
.rq-modal{
    width:100%;
    max-width:420px;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    box-shadow:0 24px 70px rgba(0,0,0,.55);
    overflow:hidden;
}
.rq-modal-head{
    padding:22px 22px 12px;
    text-align:center;
}
.rq-modal-icon{
    width:58px;
    height:58px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 14px;
    background:rgba(14,203,129,.12);
    color:var(--green);
    border:1px solid rgba(14,203,129,.35);
    font-size:30px;
}
.rq-modal-title{
    color:#fff;
    font-size:20px;
    font-weight:800;
    margin-bottom:8px;
}
.rq-modal-text{
    color:var(--muted);
    font-size:13px;
    line-height:1.6;
}
.rq-modal-actions{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
    padding:18px 22px 22px;
}
.rq-modal-btn{
    height:44px;
    border-radius:14px;
    border:1px solid var(--border);
    background:var(--surface-alt);
    color:#fff;
    font-size:13px;
    font-weight:800;
    cursor:pointer;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
}
.rq-modal-btn.primary{
    background:var(--orange);
    color:#0B0E11;
    border-color:var(--orange);
}
.rq-modal-btn:hover{filter:brightness(1.08)}

@media(max-width:480px){
    .rq-modal-actions{grid-template-columns:1fr}
}

/* ===== Responsive ===== */
@media(max-width:1100px){
    .rq-art{display:none}
    .rq-form-row{grid-template-columns:1fr}
    .rq-swap{margin:auto;transform:rotate(90deg)}
    .rq-cards{grid-template-columns:1fr}
}

@media(max-width:760px){
    .rq-page{margin:-18px;padding:16px 14px 30px}
    .rq-hero{min-height:385px;padding:38px 18px 82px}
    .rq-eyebrow{font-size:11px;margin-bottom:12px}
    .rq-title{font-size:29px;line-height:1.2}
    .rq-sub{font-size:13px;line-height:1.65;max-width:310px}
    .rq-box{margin:-52px 0 0 0;border-left:0;border-right:0;border-radius:0}
    .rq-box-inner{padding:18px 14px}
    .rq-extra{grid-template-columns:1fr}
    .rq-cards,.rq-history{margin-left:0;margin-right:0;border-left:0;border-right:0;border-radius:0}
    .rq-tabs{overflow-x:auto;height:52px}
    .rq-tab{min-width:50%;justify-content:center;padding:13px 10px;font-size:13px}
    .rq-filters{flex-direction:column;align-items:stretch}
    .rq-filters input,.rq-filters select,.rq-filter-reset{width:100%}

    /* table -> stacked cards */
    .rq-table thead{display:none}
    .rq-table,.rq-table tbody,.rq-table tr,.rq-table td{display:block;width:100%}
    .rq-table{min-width:0}
    .rq-table tr{
        border-top:1px solid var(--border);
        padding:14px 16px;
    }
    .rq-table tr:first-child{border-top:0}
    .rq-table td{
        border-top:0;
        padding:6px 0;
        white-space:normal;
        display:flex;
        justify-content:space-between;
        gap:12px;
        align-items:center;
    }
    .rq-table td::before{
        content:attr(data-label);
        color:var(--muted2);
        font-size:11px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.03em;
        flex-shrink:0;
    }
    .rq-pagination{flex-direction:column;align-items:flex-start}
}
</style>
@endpush

@section('content')
@php
    $onlineMerchants = collect($merchants)->filter(fn($m) =>
        $m->is_online == true || $m->is_online == 1 || $m->is_online === '1'
    );

    $totalFees = (float)$config->xynder_fee + (float)$config->network_fee;
    $oldType = old('type', 'withdrawal');

    $filterQ      = request('q');
    $filterType   = request('type', 'all');
    $filterStatus = request('status', 'all');

    /*
        For success popup chat button:
        In your controller redirect, pass one of these:
        ->with('chat_url', route('client.chats.request', $walletRequest))
        OR
        ->with('created_request_id', $walletRequest->id)
    */
    $chatUrl = session('chat_url');
    if (! $chatUrl && session('popup_transaction.chat_url')) {
        $chatUrl = session('popup_transaction.chat_url');
    }
    if (! $chatUrl && session('created_request_id')) {
        $chatUrl = route('client.chats.request', session('created_request_id'));
    }
@endphp

<div class="rq-page">

    <section class="rq-hero">
        <div class="rq-content">
            <div class="rq-eyebrow">BitXnow Wallet</div>

            <h1 class="rq-title">
                Secure Wallet
                <span>Buy & Sell Request</span>
            </h1>

            <div class="rq-sub">
                Enter an amount, pick an online merchant, and place your buy or sell request in seconds.
            </div>
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
                                   value="{{ old('amount') }}" placeholder="45" required autocomplete="off">
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

                    <button type="submit" class="rq-submit" id="submitBtn" disabled>
                        <span id="btnLabel">Place Sell Order</span>
                    </button>

                    <div class="rq-merchant-wrap" id="merchantWrap">
                        <div class="rq-field">
                            <label for="merchant_id">
                                Select Merchant
                            </label>

                            <select class="rq-select" name="merchant_id" id="merchant_id">
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

                        <div class="rq-merchant-hint" id="noMerchantHint" style="display:none">
                            <i class="ti ti-alert-triangle"></i>
                            No merchants are online right now — please check back shortly.
                        </div>

                        <div class="rq-extra" style="margin-top:16px">
                            <div class="rq-field" style="grid-column:1 / -1">
                                <label for="note">Note Optional</label>
                                <textarea class="rq-textarea" name="note" id="note" placeholder="Any instructions for the merchant...">{{ old('note') }}</textarea>
                            </div>
                        </div>
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
            <div class="rq-history-title">
                <i class="ti ti-list-details"></i>
                My Wallet Requests
            </div>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="rq-filters" id="filterForm">
            <input type="text"
                   name="q"
                   id="filterQ"
                   value="{{ $filterQ }}"
                   placeholder="Search by ref no..."
                   autocomplete="off">

            <select name="type" class="auto-filter">
                <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>All Types</option>
                <option value="deposit" {{ $filterType === 'deposit' ? 'selected' : '' }}>Buy USD</option>
                <option value="withdrawal" {{ $filterType === 'withdrawal' ? 'selected' : '' }}>Sell USD</option>
            </select>

            <select name="status" class="auto-filter">
                <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $filterStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="completed" {{ $filterStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ $filterStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="closed" {{ $filterStatus === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            @if($filterQ || $filterType !== 'all' || $filterStatus !== 'all')
                <a href="{{ url()->current() }}" class="rq-filter-reset">
                    <i class="ti ti-x"></i> Reset
                </a>
            @endif
        </form>

        <div class="rq-table-wrap">
            <table class="rq-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Ref No</th>
                        <th>Type</th>
                        <th>Merchant</th>
                        <th>USD Amount</th>
                        <th>INR Rate</th>
                        <th>Xynder Fee</th>
                        <th>Network Fee</th>
                        <th>Total INR</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $r)
                        @php
                            $isBuy = $r->type === 'deposit';
                            $isClosed = $r->status === 'closed';
                            $refNo = $r->transaction_no ?? 'TNS'.str_pad($r->id, 9, '0', STR_PAD_LEFT);
                            $historyUrl = route('client.history.show', ['request', $r->id]);
                        @endphp

                        <tr class="rq-click-row" onclick="window.location='{{ $historyUrl }}'">
                            <td data-label="Date">{{ $r->created_at?->format('d M Y, H:i') }}</td>

                            <td data-label="Ref No">
                                <span class="rq-ref">{{ $refNo }}</span>
                            </td>

                            <td data-label="Type">
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

                            <td data-label="Merchant">{{ $r->merchant->name ?? '—' }}</td>

                            <td data-label="USD Amount">
                                @if($isClosed)
                                    <span class="rq-closed-text">Transaction Closed</span>
                                @else
                                    <span class="rq-amount">${{ number_format((float)$r->amount, 2) }}</span>
                                @endif
                            </td>

                            <td data-label="INR Rate">
                                @if($isClosed)
                                    <span class="rq-closed-muted">—</span>
                                @else
                                    ₹{{ number_format((float)$r->inr_rate, 4) }}
                                @endif
                            </td>

                            <td data-label="Xynder Fee">
                                @if($isClosed)
                                    <span class="rq-closed-muted">—</span>
                                @else
                                    ₹{{ number_format((float)$r->xynder_fee, 2) }}
                                @endif
                            </td>

                            <td data-label="Network Fee">
                                @if($isClosed)
                                    <span class="rq-closed-muted">—</span>
                                @else
                                    ₹{{ number_format((float)$r->network_fee, 2) }}
                                @endif
                            </td>

                            <td data-label="Total INR">
                                @if($isClosed)
                                    <span class="rq-closed-text">Closed</span>
                                @else
                                    <span class="rq-total">₹{{ number_format((float)$r->total_amount, 2) }}</span>
                                @endif
                            </td>

                            <td data-label="Status">
                                <span class="badge {{ $r->status }}">
                                    {{ $isClosed ? 'closed' : $r->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="rq-empty">No requests match your filters yet.</div>
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
                        <a class="rq-page-btn" href="{{ $requests->appends(request()->query())->previousPageUrl() }}">
                            <i class="ti ti-chevron-left"></i> Previous
                        </a>
                    @endif

                    @if($requests->hasMorePages())
                        <a class="rq-page-btn" href="{{ $requests->appends(request()->query())->nextPageUrl() }}">
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

@if(session('success'))
    <div class="rq-modal-backdrop show" id="successModal">
        <div class="rq-modal">
            <div class="rq-modal-head">
                <div class="rq-modal-icon">
                    <i class="ti ti-circle-check"></i>
                </div>
                <div class="rq-modal-title">Request Created</div>
                <div class="rq-modal-text">
                    {{ session('success') }}<br>
                    You can open the transaction chat now.
                </div>
            </div>

            <div class="rq-modal-actions">
                @if($chatUrl)
                    <a href="{{ $chatUrl }}" class="rq-modal-btn primary">
                        <i class="ti ti-message-circle"></i> Open Chat
                    </a>
                @endif

                <button type="button" class="rq-modal-btn" id="closeSuccessModal">
                    Stay Here
                </button>
            </div>
        </div>
    </div>
@endif

<script>
(function(){
    const INR_RATE = {{ (float)$config->inr_rate }};
    const TOTAL_FEES = {{ $totalFees }};
    const HAS_ONLINE_MERCHANTS = {{ $onlineMerchants->isEmpty() ? 'false' : 'true' }};


    const successModal = document.getElementById('successModal');
    const closeSuccessModal = document.getElementById('closeSuccessModal');

    if(closeSuccessModal && successModal){
        closeSuccessModal.addEventListener('click', function(){
            successModal.classList.remove('show');
        });

        successModal.addEventListener('click', function(e){
            if(e.target === successModal){
                successModal.classList.remove('show');
            }
        });
    }

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

    const merchantWrap = document.getElementById('merchantWrap');
    const merchantSelect = document.getElementById('merchant_id');
    const noMerchantHint = document.getElementById('noMerchantHint');

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

    function refreshMerchantPanel(){
        const usd = parseFloat(amount.value) || 0;
        const shouldShow = usd > 0;

        merchantWrap.classList.toggle('show', shouldShow);
        merchantSelect.required = shouldShow;

        if(shouldShow && !HAS_ONLINE_MERCHANTS){
            noMerchantHint.style.display = 'flex';
        }else{
            noMerchantHint.style.display = 'none';
        }

        updateSubmitState();
    }

    function updateSubmitState(){
        const usd = parseFloat(amount.value) || 0;
        const merchantChosen = merchantSelect.value !== '';

        submitBtn.disabled = !(usd > 0 && HAS_ONLINE_MERCHANTS && merchantChosen);
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

    amount.addEventListener('input', function(){
        calc();
        refreshMerchantPanel();
    });

    merchantSelect.addEventListener('change', updateSubmitState);


    const filterForm = document.getElementById('filterForm');
    const filterQ = document.getElementById('filterQ');

    if(filterForm){
        document.querySelectorAll('.auto-filter').forEach(function(el){
            el.addEventListener('change', function(){
                filterForm.submit();
            });
        });

        let filterTimer = null;
        if(filterQ){
            filterQ.addEventListener('input', function(){
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function(){
                    filterForm.submit();
                }, 600);
            });
        }
    }

    setType('{{ $oldType }}');
    calc();
    refreshMerchantPanel();
})();
</script>
@endsection
@extends('layouts.admin', ['title' => 'Wallet Transfer'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --bg:#11161a;
    --hero:#2b2f32;
    --box:#2b2f32;
    --dark:#101518;
    --red:#e8192c;
    --red2:#c91022;
    --green:#0ecb81;
    --green2:#0aac6c;
    --text:#fff;
    --muted:#aeb4ba;
    --muted2:#747b82;
    --input:#f4f4f4;
    --gold:#ffc933;
    --line:#3b4248;
}

*{box-sizing:border-box}

.tr-page{
    margin:-24px;
    background:var(--dark);
    min-height:100vh;
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.tr-hero{
    position:relative;
    min-height:500px;
    padding:70px 85px 120px;
    background:linear-gradient(90deg,#2b2f32 0%,#2b2f32 100%);
    overflow:hidden;
}

.tr-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.08;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.tr-content{
    position:relative;
    z-index:2;
    max-width:560px;
}

.tr-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.tr-title{
    font-size:48px;
    line-height:1.15;
    font-weight:900;
    margin:0 0 22px;
}

.tr-title span{
    display:block;
    color:var(--red);
}

.tr-subtitle{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    margin-bottom:45px;
}

.tr-main-btn{
    background:var(--red);
    color:#fff;
    text-decoration:none;
    padding:16px 30px;
    border-radius:2px;
    font-weight:900;
    display:inline-flex;
    align-items:center;
    gap:8px;
}

.tr-main-btn:hover{background:var(--red2)}

.tr-art{
    position:absolute;
    right:90px;
    top:55px;
    width:540px;
    height:370px;
    z-index:1;
}

.phone{
    position:absolute;
    bottom:25px;
    width:150px;
    height:275px;
    border:7px solid #151b20;
    border-radius:28px;
    background:#22282c;
}

.phone.left{left:70px}
.phone.right{right:70px}

.phone-icon{
    width:78px;
    height:78px;
    border:3px solid var(--red);
    border-radius:50%;
    margin:28px auto;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:42px;
    color:#fff;
}

.phone-row{
    width:112px;
    height:34px;
    background:var(--red);
    border-radius:8px;
    margin:12px auto;
    display:flex;
    align-items:center;
    gap:8px;
    padding:8px;
}

.phone-row:nth-child(3){background:#4b4be8}

.chip{
    width:24px;
    height:17px;
    background:var(--gold);
    border-radius:4px;
}

.line{
    height:7px;
    flex:1;
    background:#fff;
    border-radius:20px;
}

.down{
    position:absolute;
    top:38px;
    left:50%;
    transform:translateX(-50%);
    font-size:88px;
    color:var(--red);
}

.coin-stack{
    position:absolute;
    bottom:52px;
    left:32px;
    width:110px;
}

.coin-stack span{
    display:block;
    height:10px;
    margin-bottom:4px;
    background:var(--gold);
    border-radius:20px;
}

.big-coin{
    position:absolute;
    bottom:38px;
    left:15px;
    width:78px;
    height:78px;
    border-radius:50%;
    background:#ffd247;
    border:6px solid #ffbf1d;
}

.arc{
    position:absolute;
    top:0;
    left:160px;
    width:245px;
    height:120px;
    border-top:4px dashed var(--red);
    border-radius:180px 180px 0 0;
}

.arc.blue{
    top:28px;
    left:190px;
    width:180px;
    height:85px;
    border-color:#4545ff;
}

.coin{
    position:absolute;
    width:44px;
    height:44px;
    background:var(--gold);
    border:4px solid #e8a000;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#e09100;
    font-weight:900;
}

.c1{top:0;left:170px}.c2{top:-22px;left:290px}.c3{top:0;right:65px}.c4{top:62px;left:118px}.c5{top:62px;right:5px}

.tr-alerts{
    position:relative;
    z-index:6;
    margin:-68px 85px 18px;
    max-width:980px;
}

.tr-alert{
    margin-bottom:12px;
    padding:13px 16px;
    border-radius:4px;
    font-weight:800;
    display:flex;
    gap:10px;
    align-items:flex-start;
}

.tr-alert.ok{background:#0d2b1e;color:#0ecb81}
.tr-alert.info{background:#152f3d;color:#9de8ff}
.tr-alert.err{background:#3a1018;color:#ff6b7b}
.tr-alert-txn{display:block;margin-top:4px;font-family:monospace;font-size:13px;opacity:.85}

.tr-transfer-box{
    position:relative;
    z-index:5;
    max-width:1180px;
    margin:0 85px 0;
    display:grid;
    grid-template-columns:380px 1fr;
    gap:22px;
    align-items:start;
}

.tr-box{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.tr-box-head{
    display:flex;
    align-items:center;
    gap:10px;
    padding:18px 22px;
    border-bottom:1px solid var(--line);
    background:#24292d;
    font-weight:900;
}

.tr-box-head i{color:var(--red);font-size:20px}
.tr-box-body{padding:26px}

.tr-field{margin-bottom:18px}
.tr-field label{
    display:flex;
    align-items:center;
    gap:8px;
    color:#c3c6ca;
    font-size:12px;
    font-weight:900;
    margin-bottom:8px;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.tr-input-wrap{position:relative}
.tr-input,.tr-textarea{
    width:100%;
    background:#1f2428;
    color:#fff;
    border:1px solid #3b4248;
    border-radius:4px;
    padding:14px 44px 14px 14px;
    outline:0;
    font-weight:800;
}
.tr-input{height:56px}
.tr-input:focus,.tr-textarea:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}
.tr-input::placeholder,.tr-textarea::placeholder{color:#747b82}
.tr-textarea{min-height:56px;resize:vertical;font-family:Inter,Arial,sans-serif;padding-right:14px}
.tr-input-icon{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    color:#747b82;
    font-size:18px;
}

.tr-btn{
    width:100%;
    height:56px;
    border:0;
    border-radius:3px;
    background:var(--red);
    color:#fff;
    font-size:14px;
    font-weight:900;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:9px;
}
.tr-btn:hover{background:var(--red2)}
.tr-btn.green{background:var(--green)}
.tr-btn.green:hover{background:var(--green2)}
.tr-btn:disabled{background:#1f2428;color:#6b7280;cursor:not-allowed}

.tr-receiver-found{
    margin-top:18px;
    background:#20262b;
    border:1px solid #1a4a35;
    border-radius:5px;
    padding:18px;
}

.tr-receiver-top{
    display:flex;
    align-items:center;
    gap:12px;
    padding-bottom:14px;
    margin-bottom:10px;
    border-bottom:1px solid #3b4248;
}

.tr-avatar{
    width:50px;
    height:50px;
    border-radius:50%;
    background:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-weight:900;
    font-size:20px;
    flex-shrink:0;
}

.tr-r-name{font-size:16px;font-weight:900;color:#fff}
.tr-r-email{font-size:12px;color:#aeb4ba;margin-top:3px}
.tr-verified{
    margin-left:auto;
    color:var(--green);
    background:#0d2b1e;
    border:1px solid #1a4a35;
    border-radius:20px;
    padding:4px 9px;
    font-size:11px;
    font-weight:900;
    display:flex;
    align-items:center;
    gap:5px;
}

.tr-r-row{
    display:flex;
    justify-content:space-between;
    gap:12px;
    color:#aeb4ba;
    font-size:13px;
    font-weight:700;
    padding:9px 0;
    border-bottom:1px solid #353b40;
}
.tr-r-row:last-child{border-bottom:0}
.tr-r-row strong{color:#fff;text-align:right;word-break:break-all}

.tr-placeholder{
    margin-top:18px;
    background:#20262b;
    border:1px dashed #3b4248;
    border-radius:5px;
    padding:28px 18px;
    text-align:center;
    color:#9fa5aa;
    font-size:13px;
    font-weight:800;
}
.tr-placeholder i{display:block;font-size:34px;margin-bottom:10px;color:#747b82}

.tr-wallet-display{
    background:#20262b;
    border:1px solid #3b4248;
    border-radius:4px;
    padding:13px 16px;
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:18px;
    color:#c3c6ca;
    font-weight:800;
}
.tr-wallet-display i{color:var(--red);font-size:18px}
.tr-wallet-display strong{font-family:monospace;color:#fff}

.tr-form-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
}

.tr-amount-wrap{position:relative}
.tr-prefix{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    color:var(--green);
    font-size:18px;
    font-weight:900;
    z-index:1;
}
.tr-input.amount{padding-left:36px;font-size:18px;color:#fff}

.tr-fee-box{
    background:#20262b;
    border:1px solid #3b4248;
    border-radius:4px;
    padding:14px 16px;
    margin-top:2px;
}
.tr-fee-row{
    display:flex;
    justify-content:space-between;
    color:#aeb4ba;
    font-size:13px;
    font-weight:800;
    padding:6px 0;
}
.tr-fee-row:last-child{
    margin-top:6px;
    padding-top:12px;
    border-top:1px solid #3b4248;
    color:#fff;
    font-size:15px;
}
.tr-fee-row strong{color:var(--green)}

.tr-submit-warn{
    text-align:center;
    color:#9fa5aa;
    font-size:12px;
    font-weight:800;
    margin-top:11px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
}

.tr-cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin:34px 85px 0;
}

.tr-card-stat{
    background:#2b2f32;
    border-radius:4px;
    padding:22px 20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    min-height:76px;
}

.tr-pair{
    display:flex;
    align-items:center;
    gap:12px;
    font-weight:900;
}

.tr-token{
    width:36px;
    height:36px;
    border-radius:50%;
    background:#e4a719;
    border:2px solid #ffc94d;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#2b2f32;
    font-weight:900;
    flex-shrink:0;
}

.tr-change{
    font-size:12px;
    color:#9fa5aa;
    line-height:1.5;
    text-align:right;
}
.tr-change b{color:var(--red);font-size:16px}
.tr-change.green b{color:var(--green)}

.tr-history{
    margin:34px 85px 0;
    background:#2b2f32;
    border-radius:6px;
    overflow:hidden;
}

.tr-history-head{
    padding:18px 22px;
    border-bottom:1px solid #3b4248;
    font-weight:900;
    display:flex;
    gap:10px;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
}

.tr-history-title{display:flex;align-items:center;gap:10px}
.tr-history-title i{color:var(--red)}

.tr-filter-tabs{
    display:flex;
    background:#20262b;
    border:1px solid #3b4248;
    border-radius:4px;
    overflow:hidden;
}
.tr-ftab{
    appearance:none;
    border:0;
    border-right:1px solid #3b4248;
    background:transparent;
    color:#9fa5aa;
    cursor:pointer;
    padding:9px 16px;
    font-size:12px;
    font-weight:900;
}
.tr-ftab:last-child{border-right:0}
.tr-ftab.active{background:rgba(232,25,44,.12);color:var(--red)}

.tr-table-wrap{overflow-x:auto}
.tr-table{width:100%;min-width:980px;border-collapse:collapse}
.tr-table th{
    color:#9fa5aa;
    font-size:11px;
    text-transform:uppercase;
    text-align:left;
    padding:14px 16px;
    background:#24292d;
}
.tr-table td{
    padding:16px;
    border-top:1px solid #3a4147;
    color:#c9ced3;
    font-size:13px;
    font-weight:700;
    white-space:nowrap;
}
.tr-table tr:hover td{background:#30363a}
.tr-ref{font-family:monospace;color:#9fa5aa}
.tr-type{
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-weight:900;
}
.tr-type.sent{color:#ff6b7b}
.tr-type.received{color:var(--green)}
.tr-name-chip{display:flex;align-items:center;gap:9px}
.tr-mini-av{
    width:30px;height:30px;border-radius:50%;
    background:var(--red);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;
}
.tr-mini-av.green{background:var(--green);color:#102018}
.tr-wallet-id{font-family:monospace;color:#9fa5aa;background:#20262b;padding:5px 8px;border-radius:4px;border:1px solid #3b4248}
.tr-amt{font-size:15px;font-weight:900}
.tr-amt.sent{color:#ff6b7b}
.tr-amt.received{color:var(--green)}
.tr-note{max-width:210px;color:#9fa5aa;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-style:italic}
.tr-date{color:#9fa5aa;font-size:12px}
.tr-empty{text-align:center;padding:45px;color:#9fa5aa}
.tr-empty i{display:block;font-size:38px;margin-bottom:12px;color:#747b82}

.tr-pagination{
    padding:16px 22px;
    border-top:1px solid #3a4147;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:12px;
}
.tr-page-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:9px 14px;
    background:#1f2428;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
    font-size:13px;
    font-weight:800;
    margin-left:6px;
}
.tr-page-btn.disabled{opacity:.4;pointer-events:none}

@media(max-width:1200px){
    .tr-art{opacity:.25;right:20px}
    .tr-transfer-box{grid-template-columns:1fr}
    .tr-cards{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:760px){
    .tr-page{margin:-16px}
    .tr-hero{padding:45px 24px 120px}
    .tr-title{font-size:36px}
    .tr-alerts,.tr-transfer-box,.tr-cards,.tr-history{margin-left:20px;margin-right:20px}
    .tr-form-row{grid-template-columns:1fr}
    .tr-cards{grid-template-columns:1fr}
    .tr-filter-tabs{width:100%;display:grid;grid-template-columns:repeat(3,1fr)}
    .tr-ftab{border-bottom:1px solid #3b4248}
}
</style>
@endpush

@section('content')
@php
    $isVerified = (bool) $user->is_verified;
    $balance = (float) $user->balance;
    $walletId = $user->wallet_id ?? '—';
    $totalSent = $transfers->filter(fn($t) => $t->sender_id === $user->id)->sum('amount');
    $totalReceived = $transfers->filter(fn($t) => $t->receiver_id === $user->id)->sum('amount');
    $isMerchant = $user->role === 'merchant';
    $lookupRoute = $isMerchant ? route('merchant.transfers.lookup') : route('client.transfers.lookup');
    $storeRoute  = $isMerchant ? route('merchant.transfers.store')  : route('client.transfers.store');
    $lookupWallet = old('wallet_id', session('lookup_wallet_id'));
@endphp

<div class="tr-page">

    <section class="tr-hero">
        <div class="tr-content">
            <div class="tr-eyebrow">Xynder Wallet</div>

            <h1 class="tr-title">
                Secure Wallet
                <span>USD Transfer</span>
            </h1>

            <div class="tr-subtitle">
                Send USD instantly to verified Xynder users using wallet ID lookup.
                Check receiver details first, confirm amount, and track every transfer in your history.
            </div>

            <a href="#transferBox" class="tr-main-btn">
                <i class="ti ti-send"></i>
                Send Transfer
            </a>
        </div>

        <div class="tr-art">
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

    <div class="tr-alerts">
        @if(session('success'))
            <div class="tr-alert ok">
                <i class="ti ti-circle-check"></i>
                <div>
                    {{ session('success') }}
                    @if(session('last_transfer_no'))
                        <span class="tr-alert-txn">Ref: {{ session('last_transfer_no') }}</span>
                    @endif
                </div>
            </div>
        @endif

        @if(session('lookup_success'))
            <div class="tr-alert info">
                <i class="ti ti-info-circle"></i>
                <div>{{ session('lookup_success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="tr-alert err">
                <i class="ti ti-alert-triangle"></i>
                <div>
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!$isVerified)
            <div class="tr-alert info">
                <i class="ti ti-lock"></i>
                <div>Your account is not yet verified. Transfer functionality may be restricted.</div>
            </div>
        @endif
    </div>

    <section class="tr-transfer-box" id="transferBox">
        <div class="tr-box">
            <div class="tr-box-head">
                <i class="ti ti-search"></i>
                Check Receiver
            </div>

            <div class="tr-box-body">
                <form method="POST" action="{{ $lookupRoute }}">
                    @csrf

                    <div class="tr-field">
                        <label for="wallet_id">Receiver Wallet Address</label>
                        <div class="tr-input-wrap">
                            <input
                                class="tr-input"
                                type="text"
                                name="wallet_id"
                                id="wallet_id"
                                value="{{ old('wallet_id', session('lookup_wallet_id')) }}"
                                placeholder="e.g. XYN-A1B2C3D4"
                                required
                                autocomplete="off"
                            >
                            <i class="ti ti-id-badge tr-input-icon"></i>
                        </div>
                    </div>

                    <button type="submit" class="tr-btn">
                        <i class="ti ti-search"></i>
                        Verify Receiver
                    </button>
                </form>

                @if($receiver)
                    <div class="tr-receiver-found">
                        <div class="tr-receiver-top">
                            <div class="tr-avatar">
                                {{ strtoupper(substr($receiver->name ?? 'R', 0, 1)) }}
                            </div>

                            <div>
                                <div class="tr-r-name">{{ $receiver->name }}</div>
                                <div class="tr-r-email">{{ $receiver->email }}</div>
                            </div>

                            <div class="tr-verified">
                                <i class="ti ti-circle-check"></i>
                                Verified
                            </div>
                        </div>

                        <div class="tr-r-row">
                            <span>Wallet ID</span>
                            <strong>{{ $receiver->wallet_id }}</strong>
                        </div>
                        <div class="tr-r-row">
                            <span>Account Status</span>
                            <strong style="color:{{ $receiver->is_verified ? '#0ecb81' : '#ffc933' }}">
                                {{ $receiver->is_verified ? 'Verified' : 'Unverified' }}
                            </strong>
                        </div>
                        <div class="tr-r-row">
                            <span>Role</span>
                            <strong>{{ ucfirst($receiver->role ?? 'user') }}</strong>
                        </div>
                    </div>
                @else
                    <div class="tr-placeholder">
                        <i class="ti ti-user-search"></i>
                        Enter receiver wallet ID and click <strong>Verify Receiver</strong> before sending USD.
                    </div>
                @endif
            </div>
        </div>

        <div class="tr-box">
            <div class="tr-box-head">
                <i class="ti ti-send"></i>
                Transfer Amount
            </div>

            <div class="tr-box-body">
                @if($lookupWallet)
                    <div class="tr-wallet-display">
                        <i class="ti ti-arrow-right-circle"></i>
                        <span>Sending to:</span>
                        <strong>{{ $lookupWallet }}</strong>
                    </div>
                @endif

                <form method="POST" action="{{ $storeRoute }}">
                    @csrf
                    <input type="hidden" name="receiver_wallet_id" value="{{ $lookupWallet }}">

                    <div class="tr-form-row">
                        <div class="tr-field">
                            <label for="transferAmount">USD Amount</label>
                            <div class="tr-amount-wrap">
                                <span class="tr-prefix">$</span>
                                <input
                                    class="tr-input amount"
                                    type="number"
                                    step="0.01"
                                    min="1"
                                    max="{{ $balance }}"
                                    name="amount"
                                    id="transferAmount"
                                    value="{{ old('amount') }}"
                                    placeholder="0.00"
                                    required
                                >
                            </div>
                        </div>

                        <div class="tr-field">
                            <label for="note">Note Optional</label>
                            <input
                                class="tr-input"
                                type="text"
                                name="note"
                                id="note"
                                value="{{ old('note') }}"
                                placeholder="Payment for..."
                            >
                        </div>
                    </div>

                    <div class="tr-fee-box">
                        <div class="tr-fee-row">
                            <span>Transfer Amount</span>
                            <span>$<span id="feeAmt">0.00</span></span>
                        </div>
                        <div class="tr-fee-row">
                            <span>Network Fee</span>
                            <span>$0.00</span>
                        </div>
                        <div class="tr-fee-row">
                            <span>You Send</span>
                            <strong>$<span id="feeTotal">0.00</span></strong>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="tr-btn green"
                        id="transferSubmit"
                        {{ (!$receiver || $balance <= 0) ? 'disabled' : '' }}
                    >
                        <i class="ti ti-send"></i>
                        Confirm Transfer
                    </button>
                </form>

                <div class="tr-submit-warn">
                    <i class="ti ti-shield-lock" style="color:var(--green)"></i>
                    Transfers are irreversible. Verify the wallet address before confirming.
                </div>
            </div>
        </div>
    </section>

    <section class="tr-cards">
        <div class="tr-card-stat">
            <div class="tr-pair">
                <span class="tr-token"><i class="ti ti-currency-dollar"></i></span>
                Balance
            </div>
            <div class="tr-change green">
                <b>${{ number_format($balance, 2) }}</b><br>
                Available USD
            </div>
        </div>

        <div class="tr-card-stat">
            <div class="tr-pair">
                <span class="tr-token"><i class="ti ti-wallet"></i></span>
                Wallet ID
            </div>
            <div class="tr-change">
                <b style="font-family:monospace;font-size:13px">{{ $walletId }}</b><br>
                My address
            </div>
        </div>

        <div class="tr-card-stat">
            <div class="tr-pair">
                <span class="tr-token"><i class="ti ti-trending-up"></i></span>
                Received
            </div>
            <div class="tr-change green">
                <b>+${{ number_format($totalReceived, 2) }}</b><br>
                Total received
            </div>
        </div>

        <div class="tr-card-stat">
            <div class="tr-pair">
                <span class="tr-token"><i class="ti ti-trending-down"></i></span>
                Sent
            </div>
            <div class="tr-change">
                <b>−${{ number_format($totalSent, 2) }}</b><br>
                Total sent
            </div>
        </div>
    </section>

    <section class="tr-history" id="historyBox">
        <div class="tr-history-head">
            <div class="tr-history-title">
                <i class="ti ti-list-details"></i>
                Transfer History
            </div>

            <div class="tr-filter-tabs">
                <button class="tr-ftab active" type="button" onclick="filterTable('all',this)">All</button>
                <button class="tr-ftab" type="button" onclick="filterTable('sent',this)">Sent</button>
                <button class="tr-ftab" type="button" onclick="filterTable('received',this)">Received</button>
            </div>
        </div>

        <div class="tr-table-wrap">
            <table class="tr-table" id="txTable">
                <thead>
                    <tr>
                        <th>Ref No</th>
                        <th>Type</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Wallet ID</th>
                        <th>Amount</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transfers as $t)
                        @php
                            $isSent = $t->sender_id === $user->id;
                            $senderName = $t->sender->name ?? '—';
                            $receiverName = $t->receiver->name ?? '—';
                        @endphp

                        <tr data-type="{{ $isSent ? 'sent' : 'received' }}">
                            <td>
                                <span class="tr-ref">
                                    {{ $t->transaction_no ?? 'TRA'.str_pad($t->id, 9, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <td>
                                @if($isSent)
                                    <span class="tr-type sent">
                                        <i class="ti ti-arrow-up"></i>
                                        SENT
                                    </span>
                                @else
                                    <span class="tr-type received">
                                        <i class="ti ti-arrow-down"></i>
                                        RECEIVED
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="tr-name-chip">
                                    <div class="tr-mini-av">{{ strtoupper(substr($senderName, 0, 1)) }}</div>
                                    <span>{{ $senderName }}</span>
                                </div>
                            </td>

                            <td>
                                <div class="tr-name-chip">
                                    <div class="tr-mini-av green">{{ strtoupper(substr($receiverName, 0, 1)) }}</div>
                                    <span>{{ $receiverName }}</span>
                                </div>
                            </td>

                            <td><span class="tr-wallet-id">{{ $t->receiver_wallet_id }}</span></td>

                            <td>
                                <span class="tr-amt {{ $isSent ? 'sent' : 'received' }}">
                                    {{ $isSent ? '−' : '+' }}${{ number_format((float)$t->amount, 2) }}
                                </span>
                            </td>

                            <td><div class="tr-note">{{ $t->note ?: '—' }}</div></td>
                            <td><span class="tr-date">{{ $t->created_at?->format('d M Y, H:i') }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="tr-empty">
                                    <i class="ti ti-receipt-off"></i>
                                    No transfers found. Send your first transfer above.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="tr-pagination">
                <div>
                    Page {{ $transfers->currentPage() }} of {{ $transfers->lastPage() }}
                    @if($transfers->total())
                        · {{ $transfers->firstItem() }}–{{ $transfers->lastItem() }} of {{ $transfers->total() }}
                    @endif
                </div>

                <div>
                    @if($transfers->onFirstPage())
                        <span class="tr-page-btn disabled"><i class="ti ti-chevron-left"></i> Previous</span>
                    @else
                        <a class="tr-page-btn" href="{{ $transfers->previousPageUrl() }}">
                            <i class="ti ti-chevron-left"></i> Previous
                        </a>
                    @endif

                    @if($transfers->hasMorePages())
                        <a class="tr-page-btn" href="{{ $transfers->nextPageUrl() }}">
                            Next <i class="ti ti-chevron-right"></i>
                        </a>
                    @else
                        <span class="tr-page-btn disabled">
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
    const amtInput = document.getElementById('transferAmount');
    const feeAmt = document.getElementById('feeAmt');
    const feeTotal = document.getElementById('feeTotal');

    function calcFee(){
        const v = parseFloat(amtInput?.value) || 0;
        if(feeAmt) feeAmt.textContent = v.toFixed(2);
        if(feeTotal) feeTotal.textContent = v.toFixed(2);
    }

    if(amtInput){
        amtInput.addEventListener('input', calcFee);
        calcFee();
    }

    window.filterTable = function(type, btn){
        document.querySelectorAll('.tr-ftab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('#txTable tbody tr[data-type]').forEach(row => {
            row.style.display = (type === 'all' || row.dataset.type === type) ? '' : 'none';
        });
    };
})();
</script>
@endsection

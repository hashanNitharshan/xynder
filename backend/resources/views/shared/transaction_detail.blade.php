@extends('layouts.admin', ['title' => 'Transaction Details'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
.tx-page{margin:-24px;min-height:100vh;background:#000;color:#fff;font-family:Inter,Arial,sans-serif;padding:32px 20px 70px}
.tx-wrap{max-width:820px;margin:0 auto}
.tx-top{display:flex;align-items:center;margin-bottom:70px}
.tx-back{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:28px}
.tx-title{flex:1;text-align:center;font-size:25px;font-weight:900;margin-right:42px}
.tx-center{text-align:center;margin-bottom:32px}
.tx-label{color:#6f6f76;font-size:21px;margin-bottom:10px}
.tx-qty{font-size:34px;font-weight:800;margin-bottom:18px}
.tx-status{display:flex;justify-content:center;align-items:center;gap:9px;font-size:21px;font-weight:700}
.tx-status.green{color:#00C076}.tx-status.red{color:#ef4444}.tx-status.amber{color:#FFB800}.tx-status.grey{color:#8E8E93}

.tx-chat-card{
    display:flex;
    align-items:center;
    gap:14px;
    max-width:760px;
    margin:0 auto 45px;
    background:#111316;
    border:1px solid #23262b;
    border-radius:14px;
    padding:14px 16px;
    text-decoration:none;
    color:inherit;
    transition:.15s;
}
.tx-chat-card:hover{border-color:#F0B90B;background:#161A1F}
.tx-chat-avatar{
    width:46px;height:46px;border-radius:50%;
    background:rgba(240,185,11,.12);
    border:1px solid rgba(240,185,11,.35);
    color:#F0B90B;
    display:flex;align-items:center;justify-content:center;
    font-weight:800;font-size:18px;flex-shrink:0;
}
.tx-chat-info{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.tx-chat-label{color:#6f6f76;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.tx-chat-name{color:#fff;font-size:16px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tx-chat-icon{color:#F0B90B;font-size:22px;flex-shrink:0}

.tx-box{max-width:760px;margin:0 auto}
.tx-row{display:grid;grid-template-columns:1fr 1.4fr;gap:18px;margin-bottom:25px;align-items:start}
.tx-left{color:#6f6f76;font-size:18px;font-weight:600}
.tx-right{text-align:right;color:#fff;font-size:18px;font-weight:600;line-height:1.35;word-break:break-word}
.tx-copy{cursor:pointer;display:inline-flex;align-items:center;gap:7px;justify-content:flex-end}
.tx-copy i{font-size:16px;color:#bbb}
.tx-toast{position:fixed;left:50%;bottom:30px;transform:translateX(-50%);background:#00C076;color:#fff;padding:11px 18px;border-radius:8px;font-weight:800;display:none}
@media(max-width:760px){.tx-page{margin:-16px;padding:25px 18px 60px}.tx-title{font-size:22px}.tx-row{grid-template-columns:1fr;gap:7px;margin-bottom:23px}.tx-right{text-align:left}.tx-chat-card{margin-left:0;margin-right:0}}
</style>
@endpush

@section('content')
@php
    $isTransfer = $sourceType === 'transfer';
    $statusRaw = strtolower((string)($item->status ?? 'pending'));
    $isClosed = !$isTransfer && $statusRaw === 'closed';

    $title = $isTransfer ? 'Transfer Details' : ($isClosed ? 'Transaction Closed' : (strtolower((string)$item->type) === 'withdrawal' ? 'Withdrawal Details' : 'Deposit Details'));

    $amount = (float)($item->amount ?? 0);
    $quantity = $isClosed ? 'Transaction Closed' : rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.') . ' USDT';

    if ($isTransfer) {
        $statusText = 'Transfer Completed';
        $statusColor = 'green';
        $statusIcon = 'ti-circle-check-filled';
    } elseif ($isClosed) {
        $statusText = 'Transaction Closed';
        $statusColor = 'grey';
        $statusIcon = 'ti-lock-filled';
    } elseif ($statusRaw === 'approved') {
        $statusText = strtolower((string)$item->type) === 'withdrawal' ? 'Withdrawal Completed' : 'Deposit Completed';
        $statusColor = 'green';
        $statusIcon = 'ti-circle-check-filled';
    } elseif ($statusRaw === 'rejected') {
        $statusText = 'Request Rejected';
        $statusColor = 'red';
        $statusIcon = 'ti-circle-x-filled';
    } else {
        $statusText = 'Request Pending';
        $statusColor = 'amber';
        $statusIcon = 'ti-clock-filled';
    }

    $hash = $item->transaction_no ?? ($isTransfer ? 'TRA'.str_pad($item->id, 9, '0', STR_PAD_LEFT) : 'TNS'.str_pad($item->id, 9, '0', STR_PAD_LEFT));

    if ($isTransfer) {
        $accountTitle = 'Transfer Account';
        $account = (int)$item->sender_id === (int)$user->id ? 'Sent Transfer' : 'Received Transfer';
        $fees = '0';
        $chainType = 'Internal Transfer';
        $addressTitle = 'Wallet Address';
        $address = (int)$item->sender_id === (int)$user->id
            ? ($item->receiver?->wallet_id ?? $item->receiver_wallet_id ?? '—')
            : ($item->sender?->wallet_id ?? $item->sender_id ?? '—');
    } else {
        $accountTitle = 'Request Account';
        $account = $isClosed ? 'Transaction Closed' : (strtolower((string)$item->type) === 'withdrawal' ? 'Funding Account' : 'Wallet Account');
        $feeTotal = (float)($item->xynder_fee ?? 0) + (float)($item->network_fee ?? 0);
        if ($feeTotal <= 0) $feeTotal = (float)($item->fee ?? 0);
        $fees = $isClosed ? '—' : ($feeTotal == 0 ? '0' : number_format($feeTotal, 2));
        $chainType = $isClosed ? 'Closed Request' : 'Merchant Request';
        $addressTitle = 'Merchant Address';
        $address = $isClosed ? '—' : ($item->merchant?->wallet_id ?? $item->merchant_id ?? '—');
    }

    // Counterparty (client/merchant or sender/receiver) — clickable, opens the chat for this exact request/transfer
    if ($isTransfer) {
        $isSenderMe = (int)$item->sender_id === (int)$user->id;
        $counterpartyLabel = $isSenderMe ? 'Receiver' : 'Sender';
        $counterpartyUser = $isSenderMe ? $item->receiver : $item->sender;
        $chatRoute = $user->role === 'merchant'
            ? route('merchant.chats.transfer', $item->id)
            : route('client.chats.transfer', $item->id);
    } else {
        if ($user->role === 'merchant') {
            $counterpartyLabel = 'Client';
            $counterpartyUser = $item->user;
        } else {
            $counterpartyLabel = 'Merchant';
            $counterpartyUser = $item->merchant;
        }
        $chatRoute = $user->role === 'merchant'
            ? route('merchant.chats.request', $item->id)
            : route('client.chats.request', $item->id);
    }

    $counterpartyName = $counterpartyUser?->name ?? null;

    $time = $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '—';

    $backRoute = $user->role === 'merchant' ? route('merchant.history') : route('client.history');

    $rows = [
        [$accountTitle, $account, false],
        ['Fees', $fees, false],
        ['Chain Type', $chainType, false],
        ['Time', $time, false],
        [$addressTitle, $address, !$isClosed],
        ['Transaction No', $hash, true],
        ['Reference ID', $hash, true],
    ];
@endphp

<div class="tx-page">
    <div class="tx-wrap">
        <div class="tx-top">
            <a href="{{ $backRoute }}" class="tx-back"><i class="ti ti-arrow-left"></i></a>
            <div class="tx-title">{{ $title }}</div>
        </div>

        <div class="tx-center">
            <div class="tx-label">{{ $isClosed ? 'Status' : 'Quantity' }}</div>
            <div class="tx-qty" style="color:{{ $isClosed ? '#8E8E93' : '#fff' }}">{{ $quantity }}</div>

            <div class="tx-status {{ $statusColor }}">
                <i class="ti {{ $statusIcon }}"></i>
                <span>{{ $statusText }}</span>
            </div>
        </div>

        @if($counterpartyName)
            <a href="{{ $chatRoute }}" class="tx-chat-card">
                <div class="tx-chat-avatar">{{ strtoupper(substr($counterpartyName, 0, 1)) }}</div>
                <div class="tx-chat-info">
                    <span class="tx-chat-label">{{ $counterpartyLabel }}</span>
                    <span class="tx-chat-name">{{ $counterpartyName }}</span>
                </div>
                <i class="ti ti-message-circle tx-chat-icon"></i>
            </a>
        @endif

        <div class="tx-box">
            @foreach($rows as [$left, $right, $copyable])
                <div class="tx-row">
                    <div class="tx-left">{{ $left }}</div>
                    <div class="tx-right">
                        @if($copyable && $right !== '—')
                            <span class="tx-copy" data-copy="{{ $right }}">
                                {{ $right }} <i class="ti ti-copy"></i>
                            </span>
                        @else
                            {{ $right }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="tx-toast" id="txToast">Copied</div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.tx-copy').forEach(function(el){
    el.addEventListener('click', function(){
        const text = el.getAttribute('data-copy');

        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        }

        const toast = document.getElementById('txToast');
        toast.style.display = 'block';

        setTimeout(function(){
            toast.style.display = 'none';
        }, 1200);
    });
});
</script>
@endpush
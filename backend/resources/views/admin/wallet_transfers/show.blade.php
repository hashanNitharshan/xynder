@extends('layouts.admin', ['title' => 'Transfer Details'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --bg:#0B0E11;
    --surface:#181A20;
    --surface2:#1E2329;
    --border:#2B3139;
    --yellow:#F0B90B;
    --green:#0ECB81;
    --blue:#60A5FA;
    --text:#FFFFFF;
    --muted:#848E9C;
}

*{box-sizing:border-box}

.td-page{
    margin:-28px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    padding:28px 32px 60px;
    font-family:Inter,Arial,sans-serif;
}

.td-wrap{
    max-width:820px;
    margin:0 auto;
}

.td-top{
    display:flex;
    align-items:center;
    margin-bottom:42px;
}

.td-back{
    width:42px;
    height:42px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    text-decoration:none;
    border:1px solid var(--border);
    background:var(--surface);
}

.td-title{
    flex:1;
    text-align:center;
    font-size:24px;
    font-weight:900;
    margin-right:42px;
}

.td-summary{
    text-align:center;
    margin-bottom:36px;
}

.td-icon{
    width:62px;
    height:62px;
    margin:0 auto 16px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:rgba(14,203,129,.12);
    color:var(--green);
    border:1px solid rgba(14,203,129,.35);
    font-size:30px;
}

.td-amount{
    font-size:34px;
    font-weight:900;
    margin-bottom:10px;
}

.td-status{
    color:var(--green);
    font-size:16px;
    font-weight:800;
}

.td-card{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    overflow:hidden;
}

.td-head{
    padding:17px 20px;
    background:var(--surface2);
    border-bottom:1px solid var(--border);
    font-size:16px;
    font-weight:900;
}

.td-body{
    padding:20px;
}

.td-row{
    display:grid;
    grid-template-columns:1fr 1.5fr;
    gap:18px;
    padding:13px 0;
    border-bottom:1px solid var(--border);
}

.td-row:last-child{
    border-bottom:0;
}

.td-label{
    color:var(--muted);
    font-size:13px;
    font-weight:700;
}

.td-value{
    text-align:right;
    color:#fff;
    font-size:14px;
    font-weight:700;
    word-break:break-word;
}

.td-yellow{
    color:var(--yellow);
    font-family:monospace;
}

.td-green{
    color:var(--green);
}

.td-blue{
    color:var(--blue);
}

.td-copy{
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:flex-end;
    gap:7px;
}

.td-icon.reversed{
    background:rgba(239,68,68,.12);
    color:#FF9B9B;
    border-color:rgba(239,68,68,.35);
}

.td-status.reversed{
    color:#FF9B9B;
}

.td-amount.reversed{
    color:var(--muted);
    text-decoration:line-through;
}

.td-red{
    color:#FF9B9B;
}

.td-alert{
    margin-bottom:18px;
    padding:13px 16px;
    border-radius:10px;
    display:flex;
    align-items:center;
    gap:9px;
    font-size:13px;
    font-weight:800;
}

.td-alert-ok{
    color:var(--green);
    background:rgba(14,203,129,.10);
    border:1px solid rgba(14,203,129,.35);
}

.td-alert-err{
    color:#FF9B9B;
    background:rgba(239,68,68,.10);
    border:1px solid rgba(239,68,68,.35);
}

.td-reverse-card{
    margin-top:20px;
    background:var(--surface);
    border:1px solid rgba(239,68,68,.35);
    border-radius:14px;
    overflow:hidden;
}

.td-reverse-card .td-head{
    color:#FF9B9B;
}

.td-reverse-text{
    margin:0 0 14px;
    color:var(--muted);
    font-size:13px;
    font-weight:700;
    line-height:1.6;
}

.td-label-sm{
    display:block;
    margin-bottom:7px;
    color:#C3C6CA;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
}

.td-textarea{
    width:100%;
    min-height:80px;
    padding:10px 12px;
    resize:vertical;
    outline:0;
    color:#fff;
    background:var(--bg);
    border:1px solid var(--border);
    border-radius:8px;
    font-family:inherit;
    font-size:13px;
    font-weight:600;
}

.td-textarea:focus{
    border-color:var(--yellow);
}

.td-reverse-btn{
    margin-top:14px;
    width:100%;
    height:46px;
    border:0;
    border-radius:8px;
    background:#EF4444;
    color:#fff;
    font-size:14px;
    font-weight:900;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
}

.td-reverse-btn:disabled{
    opacity:.6;
    cursor:not-allowed;
}

.td-toast{
    position:fixed;
    left:50%;
    bottom:30px;
    transform:translateX(-50%);
    display:none;
    padding:10px 16px;
    border-radius:8px;
    background:var(--green);
    color:#fff;
    font-weight:800;
}

@media(max-width:700px){
    .td-page{
        margin:-16px;
        padding:20px 16px 45px;
    }

    .td-row{
        grid-template-columns:1fr;
        gap:6px;
    }

    .td-value{
        text-align:left;
    }
}
</style>
@endpush

@section('content')
@php
    $isExternal =
        $transfer->transfer_type
        === \App\Models\WalletTransfer::TYPE_EXTERNAL;

    $transactionNo =
        $transfer->transaction_no
        ?? 'TRA'.str_pad(
            $transfer->id,
            9,
            '0',
            STR_PAD_LEFT
        );

    $senderName = $transfer->sender->name ?? 'Deleted User';
    $senderEmail = $transfer->sender->email ?? '—';
    $senderWallet = $transfer->sender->wallet_id ?? '—';

    $receiverName = $isExternal
        ? 'External Wallet'
        : ($transfer->receiver->name ?? 'Deleted User');

    $receiverEmail = $isExternal
        ? 'No registered receiver'
        : ($transfer->receiver->email ?? '—');

    $receiverWallet =
        $transfer->receiver_wallet_id
        ?? $transfer->receiver->wallet_id
        ?? '—';

    $createdAt = $transfer->created_at
        ? $transfer->created_at->format('Y-m-d H:i:s')
        : '—';

    $isReversed = $transfer->isReversed();

    $reversedAt = $transfer->reversed_at
        ? $transfer->reversed_at->format('Y-m-d H:i:s')
        : '—';

    $reversedByName = $transfer->reversedBy->name ?? 'Admin';

    $receiverBalance = (! $isExternal && $transfer->receiver)
        ? (float) $transfer->receiver->balance
        : null;

    $receiverCanPay = $isExternal
        || ($receiverBalance !== null && $receiverBalance >= (float) $transfer->amount);
@endphp

<div class="td-page">
    <div class="td-wrap">

        <div class="td-top">
            <a
                href="{{ route('admin.wallet-transfers.index') }}"
                class="td-back"
            >
                <i class="ti ti-arrow-left"></i>
            </a>

            <div class="td-title">
                Transfer Details
            </div>
        </div>

        @if(session('success'))
            <div class="td-alert td-alert-ok">
                <i class="ti ti-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="td-alert td-alert-err">
                <i class="ti ti-alert-triangle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="td-summary">
            <div class="td-icon {{ $isReversed ? 'reversed' : '' }}">
                <i class="ti {{ $isReversed ? 'ti-arrow-back-up' : 'ti-circle-check-filled' }}"></i>
            </div>

            <div class="td-amount {{ $isReversed ? 'reversed' : '' }}">
                ${{ number_format((float)$transfer->amount, 2) }}
            </div>

            <div class="td-status {{ $isReversed ? 'reversed' : '' }}">
                {{ $isReversed ? 'Transfer Reversed (Refunded)' : 'Transfer Completed' }}
            </div>
        </div>

        <div class="td-card">
            <div class="td-head">
                <i class="ti ti-receipt"></i>
                Transaction Information
            </div>

            <div class="td-body">

                <div class="td-row">
                    <div class="td-label">Transaction No</div>
                    <div class="td-value">
                        <span
                            class="td-copy td-yellow"
                            data-copy="{{ $transactionNo }}"
                        >
                            {{ $transactionNo }}
                            <i class="ti ti-copy"></i>
                        </span>
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Transfer Type</div>
                    <div class="td-value {{ $isExternal ? 'td-blue' : 'td-green' }}">
                        {{ $isExternal ? 'EXTERNAL' : 'INTERNAL' }}
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Sender</div>
                    <div class="td-value">
                        {{ $senderName }}<br>
                        <span style="color:var(--muted)">
                            {{ $senderEmail }}
                        </span>
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Sender Wallet</div>
                    <div class="td-value">
                        <span
                            class="td-copy td-yellow"
                            data-copy="{{ $senderWallet }}"
                        >
                            {{ $senderWallet }}
                            <i class="ti ti-copy"></i>
                        </span>
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Receiver</div>
                    <div class="td-value">
                        {{ $receiverName }}<br>
                        <span style="color:var(--muted)">
                            {{ $receiverEmail }}
                        </span>
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Receiver Wallet</div>
                    <div class="td-value">
                        <span
                            class="td-copy td-yellow"
                            data-copy="{{ $receiverWallet }}"
                        >
                            {{ $receiverWallet }}
                            <i class="ti ti-copy"></i>
                        </span>
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Amount</div>
                    <div class="td-value td-green">
                        ${{ number_format((float)$transfer->amount, 2) }}
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Note</div>
                    <div class="td-value">
                        {{ $transfer->note ?: '—' }}
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Transfer Time</div>
                    <div class="td-value">
                        {{ $createdAt }}
                    </div>
                </div>

                <div class="td-row">
                    <div class="td-label">Status</div>
                    <div class="td-value {{ $isReversed ? 'td-red' : 'td-green' }}">
                        {{ $isReversed ? 'REVERSED' : 'COMPLETED' }}
                    </div>
                </div>

                @if($isReversed)
                    <div class="td-row">
                        <div class="td-label">Reversed Time</div>
                        <div class="td-value">
                            {{ $reversedAt }}
                        </div>
                    </div>

                    <div class="td-row">
                        <div class="td-label">Reversed By</div>
                        <div class="td-value">
                            {{ $reversedByName }}
                        </div>
                    </div>

                    <div class="td-row">
                        <div class="td-label">Reverse Reason</div>
                        <div class="td-value">
                            {{ $transfer->reverse_reason ?: '—' }}
                        </div>
                    </div>
                @endif

            </div>
        </div>

        @unless($isReversed)
            <div class="td-reverse-card">
                <div class="td-head">
                    <i class="ti ti-arrow-back-up"></i>
                    Reverse This Transfer
                </div>

                <div class="td-body">
                    <p class="td-reverse-text">
                        @if($isExternal)
                            ${{ number_format((float)$transfer->amount, 2) }} will be added back to
                            <strong style="color:#fff">{{ $senderName }}</strong>'s wallet.
                        @else
                            ${{ number_format((float)$transfer->amount, 2) }} will be taken from
                            <strong style="color:#fff">{{ $receiverName }}</strong>
                            (balance: ${{ number_format((float)($receiverBalance ?? 0), 2) }})
                            and refunded to
                            <strong style="color:#fff">{{ $senderName }}</strong>'s wallet.
                        @endif
                        This cannot be undone.
                    </p>

                    @unless($receiverCanPay)
                        <div class="td-alert td-alert-err" style="margin-bottom:14px;">
                            <i class="ti ti-alert-triangle"></i>
                            The receiver does not have enough balance. Reverse is not possible now.
                        </div>
                    @endunless

                    <form
                        method="POST"
                        action="{{ route('admin.wallet-transfers.reverse', ['walletTransfer' => $transfer->id]) }}"
                        id="tdReverseForm"
                    >
                        @csrf

                        <label class="td-label-sm" for="tdReverseReason">Reason (optional)</label>
                        <textarea
                            class="td-textarea"
                            name="reverse_reason"
                            id="tdReverseReason"
                            maxlength="500"
                            placeholder="Why is this transfer reversed?"
                        >{{ old('reverse_reason') }}</textarea>

                        <button
                            type="submit"
                            class="td-reverse-btn"
                            id="tdReverseBtn"
                            {{ $receiverCanPay ? '' : 'disabled' }}
                        >
                            <i class="ti ti-arrow-back-up"></i>
                            Reverse and Refund ${{ number_format((float)$transfer->amount, 2) }}
                        </button>
                    </form>
                </div>
            </div>
        @endunless
    </div>

    <div class="td-toast" id="tdToast">
        Copied
    </div>
</div>

@unless($isReversed)
<script>
(function () {
    const form = document.getElementById('tdReverseForm');
    const button = document.getElementById('tdReverseBtn');

    if (!form || !button) return;

    form.addEventListener('submit', function (event) {
        const transferNo = @json($transactionNo);
        const amount = @json(number_format((float) $transfer->amount, 2));
        const sender = @json($senderName);

        const ok = confirm(
            'Reverse transfer ' + transferNo + '?\n\n'
            + '$' + amount + ' will be refunded to ' + sender + '.\n'
            + 'This cannot be undone.'
        );

        if (!ok) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        button.innerHTML = '<i class="ti ti-loader-2"></i> Reversing...';
    });
})();
</script>
@endunless

<script>
document.querySelectorAll('.td-copy').forEach(function(element) {
    element.addEventListener('click', function() {
        const text = element.getAttribute('data-copy');

        if (navigator.clipboard && text) {
            navigator.clipboard.writeText(text);
        }

        const toast = document.getElementById('tdToast');

        if (!toast) {
            return;
        }

        toast.style.display = 'block';

        setTimeout(function() {
            toast.style.display = 'none';
        }, 1200);
    });
});
</script>
@endsection
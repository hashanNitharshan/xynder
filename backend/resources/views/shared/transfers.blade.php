@php
    use App\Models\WalletTransfer;

    $isVerified = (bool) $user->is_verified;
    $balance = (float) $user->balance;
    $walletId = $user->wallet_id ?? '—';
    $totalSent = $transfers->filter(fn ($transfer) => (int) $transfer->sender_id === (int) $user->id)->sum('amount');
    $totalReceived = $transfers->filter(fn ($transfer) => (int) $transfer->receiver_id === (int) $user->id)->sum('amount');
    $isMerchant = $user->role === 'merchant';
    $lookupRoute = $isMerchant ? route('merchant.transfers.lookup') : route('client.transfers.lookup');
    $storeRoute = $isMerchant ? route('merchant.transfers.store') : route('client.transfers.store');
    $lookupWallet = old('wallet_id', session('lookup_wallet_id'));
    $oldTransferType = old('transfer_type', 'internal');
    $filterQ = request('q', '');
    $filterType = request('type', 'all');
    $chatUrl = session('chat_url') ?: session('popup_transaction.chat_url');
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--bg:#0B0E11;--box:#181A20;--panel:#1E2329;--line:#2B3139;--yellow:#F0B90B;--yellow2:#C99400;--green:#0ecb81;--red:#ef4444;--text:#fff;--muted:#848E9C}
*{box-sizing:border-box}.tr-page{margin:-28px;min-height:100vh;background:var(--bg);color:var(--text);font-family:Inter,sans-serif;padding:24px 36px 48px}
.tr-hero{border:1px solid var(--line);border-radius:16px;background:radial-gradient(circle at 90% 0,rgba(240,185,11,.18),transparent 35%),var(--box);padding:26px;margin-bottom:20px}
.tr-title{font-size:27px;margin:0 0 16px}.tr-title span{display:block;color:var(--yellow)}.tr-main-btn{display:inline-flex;gap:8px;align-items:center;background:var(--yellow);color:#111;text-decoration:none;padding:10px 17px;border-radius:8px;font-weight:900}
.tr-alert{padding:13px 16px;border-radius:9px;margin-bottom:12px;font-weight:700}.tr-alert.ok{background:#0d2b1e;color:var(--green)}.tr-alert.info{background:#152f3d;color:#9de8ff}.tr-alert.err{background:#3a1018;color:#ff8090}
.tr-mode-tabs{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}.tr-mode{border:1px solid var(--line);background:var(--panel);color:var(--muted);padding:13px;border-radius:9px;font-weight:900;cursor:pointer}.tr-mode.active{border-color:var(--yellow);color:var(--yellow);background:rgba(240,185,11,.08)}
.tr-transfer-box{display:grid;grid-template-columns:380px 1fr;gap:22px}.tr-box{background:var(--box);border:1px solid var(--line);border-radius:14px;overflow:hidden}.tr-box-head{padding:16px 20px;background:var(--panel);border-bottom:1px solid var(--line);font-weight:900}.tr-box-head i{color:var(--yellow)}.tr-box-body{padding:20px}
.tr-field{margin-bottom:17px}.tr-field label{display:block;color:#c3c6ca;font-size:12px;font-weight:900;text-transform:uppercase;margin-bottom:8px}.tr-input{width:100%;height:44px;background:var(--panel);border:1px solid var(--line);border-radius:9px;color:#fff;padding:10px 12px;font-weight:700;outline:0}.tr-input:focus{border-color:var(--yellow)}
.tr-btn{width:100%;height:44px;border:0;border-radius:6px;background:var(--yellow);color:#111;font-weight:900;cursor:pointer}.tr-btn.green{background:var(--green)}.tr-btn:disabled{background:#30363d;color:#777;cursor:not-allowed}
.tr-placeholder,.tr-receiver-found,.tr-wallet-display,.tr-fee-box{background:var(--panel);border:1px solid var(--line);border-radius:9px;padding:16px;margin-top:16px}.tr-placeholder{text-align:center;color:var(--muted)}.tr-receiver-found strong{color:#fff}.tr-r-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)}.tr-r-row:last-child{border-bottom:0}.tr-wallet-display{margin:0 0 18px}.tr-wallet-display strong{color:var(--yellow);word-break:break-all}
.tr-form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}.tr-fee-row{display:flex;justify-content:space-between;padding:6px 0;color:var(--muted)}.tr-fee-row:last-child{border-top:1px solid var(--line);margin-top:6px;padding-top:12px;color:#fff}.tr-fee-row strong{color:var(--green)}
.tr-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:20px}.tr-card-stat{background:var(--box);border:1px solid var(--line);border-radius:10px;padding:16px}.tr-card-stat b{display:block;color:var(--yellow);margin-top:6px}
.tr-history{margin-top:20px;background:var(--box);border:1px solid var(--line);border-radius:14px;overflow:hidden}.tr-history-head{padding:16px 20px;background:var(--panel);display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}.tr-filter-form{display:flex;gap:8px}.tr-filter-form input,.tr-filter-form select{height:38px;background:var(--bg);border:1px solid var(--line);color:#fff;border-radius:8px;padding:8px 10px}
.tr-table-wrap{overflow:auto}.tr-table{width:100%;min-width:1000px;border-collapse:collapse}.tr-table th,.tr-table td{padding:13px 15px;border-top:1px solid var(--line);text-align:left;font-size:12px}.tr-table th{color:var(--muted);background:var(--panel);text-transform:uppercase}.tr-ref,.tr-wallet-id{font-family:monospace}.tr-type.sent{color:var(--yellow)}.tr-type.received{color:var(--green)}.tr-type.external{color:#60a5fa}.tr-amt.sent{color:var(--yellow)}.tr-amt.received{color:var(--green)}.tr-note{max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.tr-empty{text-align:center;color:var(--muted);padding:35px}
.tr-pagination{padding:15px 20px;border-top:1px solid var(--line)}.tr-modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:9999;display:none;align-items:center;justify-content:center;padding:18px}.tr-modal-backdrop.show{display:flex}.tr-modal{max-width:420px;width:100%;background:var(--box);border:1px solid var(--line);border-radius:14px;padding:22px;text-align:center}.tr-modal-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px}.tr-modal-btn{height:44px;border:1px solid var(--line);background:var(--panel);color:#fff;border-radius:8px;text-decoration:none;display:flex;align-items:center;justify-content:center;font-weight:900}.tr-modal-btn.primary{background:var(--green);color:#07130d}
.hidden{display:none!important}@media(max-width:1000px){.tr-transfer-box{grid-template-columns:1fr}.tr-cards{grid-template-columns:1fr 1fr}}@media(max-width:760px){.tr-page{margin:-16px;padding:16px}.tr-form-row,.tr-mode-tabs,.tr-cards{grid-template-columns:1fr}.tr-filter-form{width:100%;display:grid}.tr-modal-actions{grid-template-columns:1fr}}
</style>
@endpush

<div class="tr-page">
  

    @if(session('success'))
        <div class="tr-alert ok">{{ session('success') }} @if(session('last_transfer_no')) — {{ session('last_transfer_no') }} @endif</div>
    @endif
    @if(session('lookup_success'))<div class="tr-alert info">{{ session('lookup_success') }}</div>@endif
    @if($errors->any())<div class="tr-alert err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    @if(!$isVerified)<div class="tr-alert info">Your account is not verified. Transfer functionality is restricted.</div>@endif

    <div class="tr-mode-tabs">
        <button type="button" class="tr-mode {{ $oldTransferType === 'internal' ? 'active' : '' }}" data-mode="internal">
            <i class="ti ti-users"></i> Internal Wallet Transfer
        </button>
        <button type="button" class="tr-mode {{ $oldTransferType === 'external' ? 'active' : '' }}" data-mode="external">
            <i class="ti ti-world"></i> External Wallet Transfer
        </button>
    </div>

    <section class="tr-transfer-box" id="transferBox">
        <div class="tr-box" id="internalLookupBox">
            <div class="tr-box-head"><i class="ti ti-search"></i> Check Internal Receiver</div>
            <div class="tr-box-body">
                <form method="POST" action="{{ $lookupRoute }}">
                    @csrf
                    <div class="tr-field">
                        <label for="wallet_id">Receiver BitxNow Wallet Address</label>
                        <input class="tr-input" type="text" name="wallet_id" id="wallet_id"
                               value="{{ old('wallet_id', session('lookup_wallet_id')) }}"
                               placeholder="e.g. XYN-A1B2C3D4" required autocomplete="off">
                    </div>
                    <button type="submit" class="tr-btn"><i class="ti ti-search"></i> Verify Receiver</button>
                </form>

                @if($receiver)
                    <div class="tr-receiver-found">
                        <strong>{{ $receiver->name }}</strong>
                        <div class="tr-r-row"><span>Email</span><strong>{{ $receiver->email }}</strong></div>
                        <div class="tr-r-row"><span>Wallet ID</span><strong>{{ $receiver->wallet_id }}</strong></div>
                        <div class="tr-r-row"><span>Status</span><strong style="color:#0ecb81">Verified</strong></div>
                    </div>
                @else
                    <div class="tr-placeholder">Enter a registered BitxNow wallet ID and verify the receiver.</div>
                @endif
            </div>
        </div>

        <div class="tr-box">
            <div class="tr-box-head"><i class="ti ti-send"></i> Transfer Amount</div>
            <div class="tr-box-body">
                <form method="POST" action="{{ $storeRoute }}" id="transferForm">
                    @csrf
                    <input type="hidden" name="transfer_type" id="transferType" value="{{ $oldTransferType }}">

                    <div id="internalWalletSection">
                        <div class="tr-wallet-display">
                            <span>Sending to:</span>
                            <strong>{{ $lookupWallet ?: 'Verify an internal wallet first' }}</strong>
                        </div>
                        <input type="hidden" name="receiver_wallet_id" id="internalWalletInput" value="{{ $lookupWallet }}">
                    </div>

                    <div class="tr-field hidden" id="externalWalletSection">
                        <label for="external_wallet_address">External Wallet Address</label>
                        <input class="tr-input" type="text" id="external_wallet_address"
                               value="{{ old('transfer_type') === 'external' ? old('receiver_wallet_id') : '' }}"
                               placeholder="Enter letters and numbers" autocomplete="off" maxlength="255">
                        <small style="display:block;color:var(--muted);margin-top:7px">
                            No receiver verification. The amount will only be deducted from your balance.
                        </small>
                    </div>

                    <div class="tr-form-row">
                        <div class="tr-field">
                            <label for="transferAmount">USD Amount</label>
                            <input class="tr-input" type="number" step="0.01" min="1" max="{{ $balance }}"
                                   name="amount" id="transferAmount" value="{{ old('amount') }}"
                                   placeholder="0.00" required>
                        </div>
                        <div class="tr-field">
                            <label for="note">Note Optional</label>
                            <input class="tr-input" type="text" name="note" id="note"
                                   value="{{ old('note') }}" placeholder="Payment for..." maxlength="500">
                        </div>
                    </div>

                    <div class="tr-fee-box">
                        <div class="tr-fee-row"><span>Transfer Amount</span><span>$<span id="feeAmt">0.00</span></span></div>
                        <div class="tr-fee-row"><span>Network Fee</span><span>$0.00</span></div>
                        <div class="tr-fee-row"><span>You Send</span><strong>$<span id="feeTotal">0.00</span></strong></div>
                    </div>

                    <button type="submit" class="tr-btn green" id="transferSubmit">
                        <i class="ti ti-send"></i> Confirm Transfer
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="tr-cards">
        <div class="tr-card-stat">Balance<b>${{ number_format($balance, 2) }}</b></div>
        <div class="tr-card-stat">Wallet ID<b>{{ $walletId }}</b></div>
        <div class="tr-card-stat">Received<b>+${{ number_format($totalReceived, 2) }}</b></div>
        <div class="tr-card-stat">Sent<b>−${{ number_format($totalSent, 2) }}</b></div>
    </section>

    <section class="tr-history">
        <div class="tr-history-head">
            <strong><i class="ti ti-list-details"></i> Transfer History</strong>
            <form method="GET" action="{{ url()->current() }}" class="tr-filter-form" id="transferFilterForm">
                <input type="text" name="q" id="transferSearch" value="{{ $filterQ }}" placeholder="Search reference / name / wallet">
                <select name="type" id="transferTypeFilter">
                    <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>All</option>
                    <option value="sent" {{ $filterType === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="received" {{ $filterType === 'received' ? 'selected' : '' }}>Received</option>
                </select>
            </form>
        </div>

        <div class="tr-table-wrap">
            <table class="tr-table">
                <thead><tr><th>Date</th><th>Ref No</th><th>Type</th><th>Sender</th><th>Receiver</th><th>Wallet ID</th><th>Amount</th><th>Note</th></tr></thead>
                <tbody>
                @forelse($transfers as $transfer)
                    @php
                        $isSent = (int) $transfer->sender_id === (int) $user->id;
                        $isExternal = $transfer->transfer_type === WalletTransfer::TYPE_EXTERNAL;
                    @endphp
                    <tr>
                        <td>{{ $transfer->created_at?->format('d M Y, H:i') }}</td>
                        <td><span class="tr-ref">{{ $transfer->transaction_no ?? 'TRA'.str_pad($transfer->id, 9, '0', STR_PAD_LEFT) }}</span></td>
                        <td>
                            @if($isExternal)
                                <span class="tr-type external"><i class="ti ti-world"></i> EXTERNAL</span>
                            @elseif($isSent)
                                <span class="tr-type sent"><i class="ti ti-arrow-up"></i> SENT</span>
                            @else
                                <span class="tr-type received"><i class="ti ti-arrow-down"></i> RECEIVED</span>
                            @endif
                        </td>
                        <td>{{ $transfer->sender->name ?? 'Deleted User' }}</td>
                        <td>{{ $isExternal ? 'External Wallet' : ($transfer->receiver->name ?? 'Deleted User') }}</td>
                        <td><span class="tr-wallet-id">{{ $transfer->receiver_wallet_id }}</span></td>
                        <td><span class="tr-amt {{ $isSent ? 'sent' : 'received' }}">{{ $isSent ? '−' : '+' }}${{ number_format((float) $transfer->amount, 2) }}</span></td>
                        <td><div class="tr-note">{{ $transfer->note ?: '—' }}</div></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="tr-empty">No transfers found.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())<div class="tr-pagination">{{ $transfers->links() }}</div>@endif
    </section>
</div>

@if(session('popup_transaction'))
<div class="tr-modal-backdrop show" id="transferSuccessModal">
    <div class="tr-modal">
        <h3>{{ session('popup_transaction.title', 'Transfer Completed') }}</h3>
        <p>
            {{ session('success') }}<br>
            Ref: {{ session('popup_transaction.no', session('last_transfer_no')) }}
        </p>
        <div class="tr-modal-actions">
            @if($chatUrl)
                <a href="{{ $chatUrl }}" class="tr-modal-btn primary"><i class="ti ti-message-circle"></i> Open Chat</a>
            @endif
            <button type="button" class="tr-modal-btn" id="closeTransferSuccessModal">Stay Here</button>
        </div>
    </div>
</div>
@endif

<script>
(function () {
    const typeInput = document.getElementById('transferType');
    const modes = document.querySelectorAll('.tr-mode');
    const lookupBox = document.getElementById('internalLookupBox');
    const internalSection = document.getElementById('internalWalletSection');
    const externalSection = document.getElementById('externalWalletSection');
    const internalInput = document.getElementById('internalWalletInput');
    const externalInput = document.getElementById('external_wallet_address');
    const submitButton = document.getElementById('transferSubmit');
    const balance = Number(@json($balance));
    const internalVerified = @json((bool) $receiver);

    function setMode(mode) {
        typeInput.value = mode;
        modes.forEach(button => button.classList.toggle('active', button.dataset.mode === mode));

        const external = mode === 'external';
        lookupBox.classList.toggle('hidden', external);
        internalSection.classList.toggle('hidden', external);
        externalSection.classList.toggle('hidden', !external);

        if (external) {
            internalInput.removeAttribute('name');
            externalInput.setAttribute('name', 'receiver_wallet_id');
            externalInput.required = true;
            submitButton.disabled = balance <= 0;
        } else {
            externalInput.removeAttribute('name');
            externalInput.required = false;
            internalInput.setAttribute('name', 'receiver_wallet_id');
            submitButton.disabled = !internalVerified || balance <= 0;
        }
    }

    modes.forEach(button => button.addEventListener('click', () => setMode(button.dataset.mode)));
    setMode(typeInput.value);

    const amount = document.getElementById('transferAmount');
    const feeAmt = document.getElementById('feeAmt');
    const feeTotal = document.getElementById('feeTotal');
    function calculate() {
        const value = Number(amount.value || 0);
        feeAmt.textContent = value.toFixed(2);
        feeTotal.textContent = value.toFixed(2);
    }
    amount.addEventListener('input', calculate);
    calculate();

    const modal = document.getElementById('transferSuccessModal');
    const closeModal = document.getElementById('closeTransferSuccessModal');
    if (modal && closeModal) {
        closeModal.addEventListener('click', () => modal.classList.remove('show'));
        modal.addEventListener('click', event => {
            if (event.target === modal) modal.classList.remove('show');
        });
    }

    const filterForm = document.getElementById('transferFilterForm');
    const search = document.getElementById('transferSearch');
    const filter = document.getElementById('transferTypeFilter');
    let timer;
    filter?.addEventListener('change', () => filterForm.submit());
    search?.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => filterForm.submit(), 600);
    });
})();
</script>

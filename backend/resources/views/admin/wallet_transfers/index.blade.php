@extends('layouts.admin', ['title' => 'Wallet Transfer History'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
    --dark:#0B0E11;
    --box:#181A20;
    --panel:#1E2329;
    --line:#2B3139;
    --yellow:#F0B90B;
    --green:#0ecb81;
    --blue:#60a5fa;
    --text:#fff;
    --muted:#848E9C;
}

*{box-sizing:border-box}

.ad-page{
    margin:-28px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,sans-serif;
    padding:24px 36px 48px;
}

.ad-hero{
    background:
        radial-gradient(
            circle at 92% 0,
            rgba(240,185,11,.2),
            transparent 38%
        ),
        var(--box);
    padding:18px 20px;
    border:1px solid var(--line);
    border-radius:16px;
    margin-bottom:20px;
}

.ad-title-main{
    font-size:20px;
    margin:0;
}

.ad-title-main span{
    color:var(--yellow);
}

/* =========================
   KPI CARDS
========================= */

.ad-kpis{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:24px;
}

.ad-kpi{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:10px;
    padding:18px;
}

.ad-kpi.internal{
    border-color:rgba(14,203,129,.35);
}

.ad-kpi.external{
    border-color:rgba(96,165,250,.35);
}

.ad-kpi-label{
    color:var(--muted);
    font-size:11px;
    text-transform:uppercase;
    font-weight:900;
}

.ad-kpi-value{
    font-size:21px;
    font-weight:900;
    margin-top:8px;
}

.ad-kpi-value.internal{
    color:var(--green);
}

.ad-kpi-value.external{
    color:var(--blue);
}

.ad-kpi-sub{
    color:var(--muted);
    font-size:11px;
    margin-top:5px;
    font-weight:600;
}

/* =========================
   HISTORY CARD
========================= */

.ad-card{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:14px;
    overflow:hidden;
}

.ad-card-head{
    background:var(--panel);
    padding:17px 20px;
    font-weight:900;
}

.ad-filters{
    display:grid;
    grid-template-columns:2fr 1fr 1fr auto;
    gap:10px;
    padding:16px 20px;
    border-bottom:1px solid var(--line);
}

.ad-input,
.ad-select{
    height:42px;
    background:var(--dark);
    border:1px solid var(--line);
    color:#fff;
    border-radius:8px;
    padding:9px 12px;
    outline:none;
}

.ad-input:focus,
.ad-select:focus{
    border-color:var(--yellow);
}

.ad-btn{
    height:42px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    border:1px solid var(--line);
    border-radius:8px;
    color:#fff;
    text-decoration:none;
    padding:0 14px;
    font-size:13px;
    font-weight:700;
}

.ad-btn:hover{
    border-color:var(--yellow);
    color:var(--yellow);
}

/* =========================
   TABLE
========================= */

.ad-table-wrap{
    overflow:auto;
}

.ad-table{
    width:100%;
    min-width:1060px;
    border-collapse:collapse;
}

.ad-table th,
.ad-table td{
    padding:13px 10px;
    border-top:1px solid var(--line);
    font-size:12px;
    text-align:left;
}

.ad-table th{
    background:var(--panel);
    color:var(--muted);
    text-transform:uppercase;
}

.ad-row{
    cursor:pointer;
    transition:.15s;
}

.ad-row:hover td{
    background:#20242a;
}

.ad-ref{
    color:var(--yellow);
    font-family:monospace;
    font-weight:900;
}

.ad-name{
    font-weight:900;
}

.ad-small{
    font-size:11px;
    color:var(--muted);
    margin-top:3px;
}

.ad-wallet{
    font-family:monospace;
    color:var(--yellow);
}

.ad-amount{
    color:var(--green);
    font-weight:900;
}

.ad-type{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:5px 9px;
    border-radius:20px;
    font-weight:900;
}

.ad-type.internal{
    background:#0d2b1e;
    color:var(--green);
}

.ad-type.external{
    background:#102441;
    color:var(--blue);
}

.ad-external{
    color:var(--blue);
    font-weight:900;
}

.ad-view{
    color:var(--yellow);
    font-size:18px;
}

.ad-pagination{
    padding:16px 20px;
    border-top:1px solid var(--line);
}

.ad-empty{
    text-align:center;
    padding:35px;
    color:var(--muted);
}

@media(max-width:1100px){
    .ad-kpis{
        grid-template-columns:1fr 1fr;
    }
}

@media(max-width:760px){
    .ad-page{
        margin:-16px;
        padding:16px;
    }

    .ad-kpis,
    .ad-filters{
        grid-template-columns:1fr;
    }
}
</style>
@endpush

@section('content')
@php
    $selectedType = request('transfer_type', 'all');

    $internalCount = $stats['internal_count']
        ?? $stats['total_internal_count']
        ?? 0;

    $externalCount = $stats['external_count']
        ?? $stats['total_external_count']
        ?? 0;

    $internalVolume = $stats['internal_volume']
        ?? $stats['total_internal_volume']
        ?? 0;

    $externalVolume = $stats['external_volume']
        ?? $stats['total_external_volume']
        ?? 0;
@endphp

<div class="ad-page">

    <section class="ad-hero">
        <h1 class="ad-title-main">
            Wallet Transfer <span>History</span>
        </h1>
    </section>

    <section class="ad-kpis">

        <div class="ad-kpi">
            <div class="ad-kpi-label">
                Total Transfers
            </div>

            <div class="ad-kpi-value">
                {{ $stats['total_count'] ?? 0 }}
            </div>

            <div class="ad-kpi-sub">
                All wallet transfers
            </div>
        </div>

        <div class="ad-kpi">
            <div class="ad-kpi-label">
                Total Volume
            </div>

            <div class="ad-kpi-value">
                ${{ number_format(
                    (float)($stats['total_volume'] ?? 0),
                    2
                ) }}
            </div>

            <div class="ad-kpi-sub">
                Internal and external
            </div>
        </div>

        <div class="ad-kpi internal">
            <div class="ad-kpi-label">
                Internal Transfers
            </div>

            <div class="ad-kpi-value internal">
                {{ $internalCount }}
            </div>

            <div class="ad-kpi-sub">
                Volume:
                ${{ number_format((float)$internalVolume, 2) }}
            </div>
        </div>

        <div class="ad-kpi external">
            <div class="ad-kpi-label">
                External Transfers
            </div>

            <div class="ad-kpi-value external">
                {{ $externalCount }}
            </div>

            <div class="ad-kpi-sub">
                Volume:
                ${{ number_format((float)$externalVolume, 2) }}
            </div>
        </div>

    </section>

    <section class="ad-card">

        <div class="ad-card-head">
            <i class="ti ti-list-details"></i>
            Wallet Transfer History
        </div>

        <form
            id="adFilterForm"
            method="GET"
            action="{{ route('admin.wallet-transfers.index') }}"
            class="ad-filters"
        >
            <input
                id="adSearch"
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="ad-input"
                placeholder="Search reference, sender, receiver or wallet"
                autocomplete="off"
            >

            <select
                name="transfer_type"
                class="ad-select ad-auto-filter"
            >
                <option
                    value="all"
                    {{ $selectedType === 'all' ? 'selected' : '' }}
                >
                    All Transfer Types
                </option>

                <option
                    value="internal"
                    {{ $selectedType === 'internal' ? 'selected' : '' }}
                >
                    Internal Transfers
                </option>

                <option
                    value="external"
                    {{ $selectedType === 'external' ? 'selected' : '' }}
                >
                    External Transfers
                </option>
            </select>

            <select
                name="per_page"
                class="ad-select ad-auto-filter"
            >
                @foreach([10, 20, 30, 50, 100] as $size)
                    <option
                        value="{{ $size }}"
                        {{
                            (int)request('per_page', 20) === $size
                                ? 'selected'
                                : ''
                        }}
                    >
                        {{ $size }} / page
                    </option>
                @endforeach
            </select>

            <a
                href="{{ route('admin.wallet-transfers.index') }}"
                class="ad-btn"
            >
                <i class="ti ti-refresh"></i>
                Reset
            </a>
        </form>

        <div class="ad-table-wrap">
            <table class="ad-table">

                <thead>
                    <tr>
                        <th>Transaction No</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Receiver Wallet</th>
                        <th>Amount</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                @forelse($transfers as $transfer)
                    @php
                        $isExternal =
                            $transfer->transfer_type
                            === \App\Models\WalletTransfer::TYPE_EXTERNAL;

                        /*
                         * Change only this route name if your existing
                         * transaction details route uses a different name.
                         */
                        $detailsUrl = route(
                            'admin.wallet-transfers.show',
                            ['walletTransfer' => $transfer->id]
                        );
                    @endphp

                    <tr
                        class="ad-row"
                        tabindex="0"
                        role="link"
                        data-url="{{ $detailsUrl }}"
                    >
                        <td>
                            <span class="ad-ref">
                                {{
                                    $transfer->transaction_no
                                    ?? 'TRA'.str_pad(
                                        $transfer->id,
                                        9,
                                        '0',
                                        STR_PAD_LEFT
                                    )
                                }}
                            </span>
                        </td>

                        <td>
                            <div>
                                {{
                                    $transfer->created_at
                                        ?->format('d M Y')
                                }}
                            </div>

                            <div class="ad-small">
                                {{
                                    $transfer->created_at
                                        ?->format('h:i A')
                                }}
                            </div>
                        </td>

                        <td>
                            <span
                                class="ad-type {{
                                    $isExternal
                                        ? 'external'
                                        : 'internal'
                                }}"
                            >
                                <i class="ti {{
                                    $isExternal
                                        ? 'ti-world'
                                        : 'ti-arrows-exchange'
                                }}"></i>

                                {{
                                    $isExternal
                                        ? 'EXTERNAL'
                                        : 'INTERNAL'
                                }}
                            </span>
                        </td>

                        <td>
                            <div class="ad-name">
                                {{
                                    $transfer->sender->name
                                    ?? 'Deleted User'
                                }}
                            </div>

                            <div class="ad-small">
                                {{
                                    $transfer->sender->email
                                    ?? '-'
                                }}
                            </div>

                            <div class="ad-wallet">
                                {{
                                    $transfer->sender->wallet_id
                                    ?? '-'
                                }}
                            </div>
                        </td>

                        <td>
                            @if($isExternal)
                                <div class="ad-external">
                                    <i class="ti ti-world"></i>
                                    External Wallet
                                </div>

                                <div class="ad-small">
                                    No registered receiver
                                </div>
                            @else
                                <div class="ad-name">
                                    {{
                                        $transfer->receiver->name
                                        ?? 'Deleted User'
                                    }}
                                </div>

                                <div class="ad-small">
                                    {{
                                        $transfer->receiver->email
                                        ?? '-'
                                    }}
                                </div>

                                <div class="ad-wallet">
                                    {{
                                        $transfer->receiver->wallet_id
                                        ?? '-'
                                    }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <span class="ad-wallet">
                                {{ $transfer->receiver_wallet_id }}
                            </span>
                        </td>

                        <td>
                            <span class="ad-amount">
                                ${{
                                    number_format(
                                        (float)$transfer->amount,
                                        2
                                    )
                                }}
                            </span>
                        </td>

                        <td>
                            {{ $transfer->note ?: '-' }}
                        </td>

                        <td>
                            <i class="ti ti-chevron-right ad-view"></i>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="9">
                            <div class="ad-empty">
                                No wallet transfers found.
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        <div class="ad-pagination">
            Showing
            {{ $transfers->firstItem() ?? 0 }}
            –
            {{ $transfers->lastItem() ?? 0 }}
            of
            {{ $transfers->total() }}

            {{ $transfers->appends(request()->query())->links() }}
        </div>

    </section>
</div>

<script>
document.querySelectorAll('.ad-auto-filter').forEach(function(element) {
    element.addEventListener('change', function() {
        document.getElementById('adFilterForm').submit();
    });
});

let searchTimer = null;

document.getElementById('adSearch')?.addEventListener('input', function() {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(function() {
        document.getElementById('adFilterForm').submit();
    }, 500);
});

document.querySelectorAll('.ad-row').forEach(function(row) {
    function openDetails() {
        const url = row.getAttribute('data-url');

        if (url) {
            window.location.href = url;
        }
    }

    row.addEventListener('click', openDetails);

    row.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openDetails();
        }
    });
});
</script>
@endsection
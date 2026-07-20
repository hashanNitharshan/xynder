@extends('layouts.admin', ['title' => 'Wallet Transfer History'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
    --dark:#0B0E11;
    --hero:#181A20;
    --box:#181A20;
    --panel:#1E2329;
    --input:#0B0E11;
    --line:#2B3139;
    --yellow:#F0B90B;
    --yellow-dark:#C99400;
    --green:#0ECB81;
    --blue:#60A5FA;
    --red:#EF4444;
    --gold:#FFD45A;
    --text:#FFFFFF;
    --muted:#848E9C;
    --muted-dark:#5E6673;
}

*{
    box-sizing:border-box;
}

.ad-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;
    padding-bottom:60px;
}

.ad-hero{
    min-height:185px;
    background:
        radial-gradient(circle at 88% 15%,rgba(240,185,11,.12),transparent 31%),
        var(--hero);
    position:relative;
    overflow:hidden;
}

.ad-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:
        linear-gradient(
            120deg,
            transparent 20%,
            rgba(255,255,255,.18) 21%,
            transparent 22%
        );
    background-size:260px 260px;
}

.ad-wrap{
    position:relative;
    z-index:5;
    margin:-82px 45px 0;
}

.ad-kpis{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:18px;
    margin-bottom:28px;
}

.ad-kpi{
    min-height:128px;
    padding:20px;
    position:relative;
    overflow:hidden;
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
}

.ad-kpi::after{
    content:"";
    position:absolute;
    right:-38px;
    top:-38px;
    width:115px;
    height:115px;
    border-radius:50%;
    background:rgba(240,185,11,.10);
}

.ad-kpi-internal::after{
    background:rgba(14,203,129,.10);
}

.ad-kpi-external::after{
    background:rgba(96,165,250,.10);
}

.ad-kpi-icon{
    width:42px;
    height:42px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:14px;
    font-size:22px;
    color:var(--yellow);
    background:rgba(240,185,11,.12);
}

.ad-kpi-internal .ad-kpi-icon{
    color:var(--green);
    background:rgba(14,203,129,.12);
}

.ad-kpi-external .ad-kpi-icon{
    color:var(--blue);
    background:rgba(96,165,250,.12);
}

.ad-kpi-label{
    color:var(--muted);
    font-size:11px;
    font-weight:900;
    letter-spacing:.06em;
    text-transform:uppercase;
}

.ad-kpi-value{
    margin-top:6px;
    color:#FFFFFF;
    font-size:22px;
    font-weight:800;
    word-break:break-word;
}

.ad-kpi-internal .ad-kpi-value{
    color:var(--green);
}

.ad-kpi-external .ad-kpi-value{
    color:var(--blue);
}

.ad-kpi-sub{
    margin-top:5px;
    color:var(--muted);
    font-size:11px;
    font-weight:700;
}

.ad-card{
    overflow:hidden;
    background:var(--box);
    border:1.5px solid var(--yellow);
    border-radius:7px;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.ad-card-head{
    padding:18px 22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    background:var(--panel);
    border-bottom:1px solid var(--line);
}

.ad-title{
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
    color:#FFFFFF;
    font-size:15px;
    font-weight:800;
}

.ad-title i{
    color:var(--yellow);
}

.ad-filters{
    display:grid;
    grid-template-columns:minmax(220px,2fr) minmax(150px,1fr) minmax(120px,1fr) auto;
    gap:10px;
    padding:16px 22px;
    background:var(--box);
    border-bottom:1px solid var(--line);
}

.ad-search-wrap{
    position:relative;
}

.ad-search-wrap i{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:var(--muted);
    font-size:16px;
    pointer-events:none;
}

.ad-input,
.ad-select{
    width:100%;
    height:42px;
    padding:0 13px;
    outline:0;
    color:var(--text);
    background:var(--input);
    border:1px solid var(--line);
    border-radius:4px;
    font-size:13px;
    font-weight:700;
}

.ad-search-wrap .ad-input{
    padding-left:36px;
}

.ad-input:focus,
.ad-select:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(240,185,11,.12);
}

.ad-select option{
    background:var(--input);
    color:#FFFFFF;
}

.ad-btn{
    min-height:42px;
    padding:10px 13px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    border-radius:4px;
    border:1px solid var(--line);
    color:#FFFFFF;
    background:var(--input);
    font-size:12px;
    font-weight:900;
    text-decoration:none;
    white-space:nowrap;
    cursor:pointer;
}

.ad-btn:hover{
    color:var(--yellow);
    border-color:var(--yellow);
}

.ad-table-wrap{
    width:100%;
    overflow-x:auto;
}

.ad-table{
    width:100%;
    min-width:1080px;
    border-collapse:collapse;
    table-layout:fixed;
    font-size:13px;
}

.ad-table th{
    padding:14px 8px;
    text-align:left;
    color:var(--muted-dark);
    background:var(--panel);
    font-size:10px;
    font-weight:900;
    letter-spacing:.05em;
    text-transform:uppercase;
}

.ad-table td{
    padding:15px 8px;
    vertical-align:middle;
    color:#C9CED3;
    border-top:1px solid var(--line);
    font-weight:700;
    word-break:break-word;
}

.ad-table th:nth-child(1),
.ad-table td:nth-child(1){
    width:12%;
}

.ad-table th:nth-child(2),
.ad-table td:nth-child(2){
    width:9%;
}

.ad-table th:nth-child(3),
.ad-table td:nth-child(3){
    width:9%;
}

.ad-table th:nth-child(4),
.ad-table td:nth-child(4){
    width:15%;
}

.ad-table th:nth-child(5),
.ad-table td:nth-child(5){
    width:15%;
}

.ad-table th:nth-child(6),
.ad-table td:nth-child(6){
    width:15%;
}

.ad-table th:nth-child(7),
.ad-table td:nth-child(7){
    width:9%;
}

.ad-table th:nth-child(8),
.ad-table td:nth-child(8){
    width:11%;
}

.ad-table th:nth-child(9),
.ad-table td:nth-child(9){
    width:5%;
    text-align:center;
}

.ad-row{
    cursor:pointer;
    transition:background-color .15s ease;
}

.ad-row:hover td{
    background:#1E2329;
}

.ad-row:focus{
    outline:2px solid var(--yellow);
    outline-offset:-2px;
}

.ad-ref{
    color:var(--yellow);
    font-family:monospace;
    font-size:12px;
    font-weight:900;
}

.ad-name{
    color:#FFFFFF;
    font-weight:900;
}

.ad-small{
    margin-top:3px;
    color:var(--muted);
    font-size:11px;
    font-weight:700;
}

.ad-wallet{
    margin-top:3px;
    color:var(--yellow);
    font-family:monospace;
    font-size:11px;
    font-weight:900;
    word-break:break-all;
}

.ad-amount{
    color:var(--green);
    font-size:13px;
    font-weight:900;
    white-space:nowrap;
}

.ad-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:900;
    white-space:nowrap;
    text-transform:uppercase;
}

.ad-badge-internal{
    color:var(--green);
    background:#0D2B1E;
}

.ad-badge-external{
    color:var(--blue);
    background:#102441;
}

.ad-external{
    display:flex;
    align-items:center;
    gap:6px;
    color:var(--blue);
    font-weight:900;
}

.ad-icon-btn{
    width:36px;
    height:36px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color:var(--yellow);
    background:var(--input);
    border:1px solid var(--line);
    border-radius:4px;
    font-size:17px;
    text-decoration:none;
    cursor:pointer;
}

.ad-icon-btn:hover{
    color:var(--yellow);
    border-color:var(--yellow);
    transform:translateY(-1px);
}

.ad-note{
    color:#C9CED3;
    font-size:12px;
    line-height:1.5;
}

.ad-empty{
    padding:45px;
    text-align:center;
    color:var(--muted);
    font-weight:800;
}

.ad-pagination{
    padding:16px 22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
    background:var(--panel);
    border-top:1px solid var(--line);
}

.ad-pagination-info{
    color:var(--muted);
    font-size:12px;
    font-weight:800;
}

.ad-pagination nav{
    width:auto;
    max-width:100%;
}

.ad-pagination nav > div{
    display:flex!important;
    align-items:center!important;
    gap:8px!important;
    flex-wrap:wrap!important;
}

.ad-pagination nav p{
    margin:0!important;
    color:var(--muted)!important;
    font-size:12px!important;
    font-weight:800!important;
}

.ad-pagination nav a,
.ad-pagination nav span{
    min-width:34px!important;
    height:34px!important;
    padding:0 10px!important;
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    color:#FFFFFF!important;
    background:var(--input)!important;
    border:1px solid var(--line)!important;
    border-radius:5px!important;
    box-shadow:none!important;
    font-size:12px!important;
    font-weight:900!important;
    line-height:1!important;
    text-decoration:none!important;
}

.ad-pagination nav span[aria-current="page"],
.ad-pagination nav span[aria-current="page"] span{
    color:#111111!important;
    background:var(--yellow)!important;
    border-color:var(--yellow)!important;
}

.ad-pagination nav a:hover{
    color:var(--yellow)!important;
    border-color:var(--yellow)!important;
}

.ad-pagination nav svg{
    width:16px!important;
    height:16px!important;
    max-width:16px!important;
    max-height:16px!important;
    display:block!important;
    stroke-width:3!important;
}

.ad-pagination nav .hidden{
    display:none!important;
}

.ad-pagination nav div:first-child{
    display:none!important;
}

.ad-pagination nav div:last-child{
    display:flex!important;
}

@media(max-width:1300px){
    .ad-wrap{
        margin:-82px 24px 0;
    }

    .ad-table th{
        padding:12px 6px;
        font-size:9px;
    }

    .ad-table td{
        padding:13px 6px;
        font-size:12px;
    }
}

@media(max-width:1100px){
    .ad-kpis{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }

    .ad-filters{
        grid-template-columns:1fr 1fr;
    }

    .ad-table,
    .ad-table thead,
    .ad-table tbody,
    .ad-table th,
    .ad-table td,
    .ad-table tr{
        display:block;
        width:100%!important;
    }

    .ad-table{
        min-width:0;
    }

    .ad-table thead{
        display:none;
    }

    .ad-table tr{
        margin:12px;
        padding:14px;
        background:var(--panel);
        border:1px solid var(--line);
        border-radius:7px;
    }

    .ad-table td{
        padding:9px 0;
        border-top:0;
        text-align:left!important;
    }

    .ad-table td::before{
        content:attr(data-label);
        display:block;
        margin-bottom:5px;
        color:var(--muted-dark);
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
    }
}

@media(max-width:768px){
    .ad-page{
        margin:-16px;
    }

    .ad-hero{
        min-height:160px;
    }

    .ad-wrap{
        margin:-70px 18px 0;
    }

    .ad-kpis,
    .ad-filters{
        grid-template-columns:1fr;
    }

    .ad-pagination{
        flex-direction:column;
        align-items:flex-start;
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
    <section class="ad-hero"></section>

    <main class="ad-wrap">
        <section class="ad-kpis">
            <div class="ad-kpi">
                <div class="ad-kpi-icon">
                    <i class="ti ti-arrows-transfer-up"></i>
                </div>

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
                <div class="ad-kpi-icon">
                    <i class="ti ti-cash"></i>
                </div>

                <div class="ad-kpi-label">
                    Total Volume
                </div>

                <div class="ad-kpi-value">
                    ${{ number_format((float)($stats['total_volume'] ?? 0), 2) }}
                </div>

                <div class="ad-kpi-sub">
                    Internal and external
                </div>
            </div>

            <div class="ad-kpi ad-kpi-internal">
                <div class="ad-kpi-icon">
                    <i class="ti ti-arrows-exchange"></i>
                </div>

                <div class="ad-kpi-label">
                    Internal Transfers
                </div>

                <div class="ad-kpi-value">
                    {{ $internalCount }}
                </div>

                <div class="ad-kpi-sub">
                    Volume: ${{ number_format((float)$internalVolume, 2) }}
                </div>
            </div>

            <div class="ad-kpi ad-kpi-external">
                <div class="ad-kpi-icon">
                    <i class="ti ti-world"></i>
                </div>

                <div class="ad-kpi-label">
                    External Transfers
                </div>

                <div class="ad-kpi-value">
                    {{ $externalCount }}
                </div>

                <div class="ad-kpi-sub">
                    Volume: ${{ number_format((float)$externalVolume, 2) }}
                </div>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h2 class="ad-title">
                    <i class="ti ti-list-details"></i>
                    Wallet Transfer History
                </h2>
            </div>

            <form
                id="adFilterForm"
                method="GET"
                action="{{ route('admin.wallet-transfers.index') }}"
                class="ad-filters"
            >
                <div class="ad-search-wrap">
                    <i class="ti ti-search"></i>

                    <input
                        id="adSearch"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="ad-input"
                        placeholder="Search reference, sender, receiver or wallet..."
                        autocomplete="off"
                    >
                </div>

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
                            {{ (int)request('per_page', 20) === $size ? 'selected' : '' }}
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
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($transfers as $transfer)
                        @php
                            $isExternal =
                                $transfer->transfer_type
                                === \App\Models\WalletTransfer::TYPE_EXTERNAL;

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
                            <td data-label="Transaction No">
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

                            <td data-label="Date">
                                <div class="ad-name">
                                    {{ $transfer->created_at?->format('Y-m-d') }}
                                </div>

                                <div class="ad-small">
                                    {{ $transfer->created_at?->format('h:i A') }}
                                </div>
                            </td>

                            <td data-label="Type">
                                <span class="ad-badge {{ $isExternal ? 'ad-badge-external' : 'ad-badge-internal' }}">
                                    <i class="ti {{ $isExternal ? 'ti-world' : 'ti-arrows-exchange' }}"></i>

                                    {{ $isExternal ? 'External' : 'Internal' }}
                                </span>
                            </td>

                            <td data-label="Sender">
                                <div class="ad-name">
                                    {{ $transfer->sender->name ?? 'Deleted User' }}
                                </div>

                                <div class="ad-small">
                                    {{ $transfer->sender->email ?? '-' }}
                                </div>

                                <div class="ad-wallet">
                                    {{ $transfer->sender->wallet_id ?? '-' }}
                                </div>
                            </td>

                            <td data-label="Receiver">
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
                                        {{ $transfer->receiver->name ?? 'Deleted User' }}
                                    </div>

                                    <div class="ad-small">
                                        {{ $transfer->receiver->email ?? '-' }}
                                    </div>

                                    <div class="ad-wallet">
                                        {{ $transfer->receiver->wallet_id ?? '-' }}
                                    </div>
                                @endif
                            </td>

                            <td data-label="Receiver Wallet">
                                <div class="ad-wallet">
                                    {{ $transfer->receiver_wallet_id ?: '-' }}
                                </div>
                            </td>

                            <td data-label="Amount">
                                <span class="ad-amount">
                                    ${{ number_format((float)$transfer->amount, 2) }}
                                </span>
                            </td>

                            <td data-label="Note">
                                <div class="ad-note">
                                    {{ $transfer->note ?: '-' }}
                                </div>
                            </td>

                            <td data-label="Action" onclick="event.stopPropagation();">
                                <a
                                    href="{{ $detailsUrl }}"
                                    class="ad-icon-btn"
                                    title="View transaction details"
                                    aria-label="View transaction details"
                                >
                                    <i class="ti ti-eye"></i>
                                </a>
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
                <div class="ad-pagination-info">
                    Showing
                    {{ $transfers->firstItem() ?? 0 }}
                    –
                    {{ $transfers->lastItem() ?? 0 }}
                    of
                    {{ $transfers->total() }}
                </div>

                {{ $transfers->appends(request()->query())->links() }}
            </div>
        </section>
    </main>
</div>

<script>
document.querySelectorAll('.ad-auto-filter').forEach(function(element) {
    element.addEventListener('change', function() {
        document.getElementById('adFilterForm').submit();
    });
});

let searchTimer = null;
const searchInput = document.getElementById('adSearch');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(function() {
            document.getElementById('adFilterForm').submit();
        }, 480);
    });
}

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

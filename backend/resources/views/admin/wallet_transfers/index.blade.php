
@extends('layouts.admin', ['title' => 'Wallet Transfer History'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --dark:#101518;
    --hero:#2b2f32;
    --box:#2b2f32;
    --panel:#24292d;
    --input:#1f2428;
    --line:#3b4248;
    --red:#e8192c;
    --red2:#c91022;
    --green:#0ecb81;
    --gold:#ffc933;
    --text:#fff;
    --muted:#aeb4ba;
    --muted2:#747b82;
}

*{box-sizing:border-box}

.wt-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.wt-hero{
    background:var(--hero);
    padding:65px 85px 120px;
    position:relative;
    overflow:hidden;
}

.wt-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.wt-hero-inner{
    position:relative;
    z-index:2;
    max-width:680px;
}

.wt-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.wt-title-main{
    font-size:46px;
    line-height:1.12;
    font-weight:900;
    margin:0 0 18px;
}

.wt-title-main span{
    color:var(--red);
    display:block;
}

.wt-subtitle{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    max-width:580px;
}

.wt-wrap{
    position:relative;
    z-index:5;
    margin:-82px 85px 0;
}

.wt-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:26px;
}

.wt-stat{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:20px;
    display:flex;
    align-items:center;
    gap:14px;
    min-height:105px;
}

.wt-stat-icon{
    width:46px;
    height:46px;
    border-radius:50%;
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    flex-shrink:0;
}

.wt-stat-num{
    font-size:25px;
    font-weight:900;
    color:#fff;
    line-height:1;
}

.wt-stat-label{
    color:var(--muted);
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-top:5px;
}

.wt-card{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.wt-card-head{
    background:var(--panel);
    border-bottom:1px solid var(--line);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    padding:18px 22px;
}

.wt-title{
    font-size:18px;
    font-weight:900;
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
}

.wt-title i{color:var(--red)}

.wt-filters{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    padding:16px 22px;
    border-bottom:1px solid var(--line);
    background:var(--box);
}

.wt-search-wrap{
    position:relative;
}

.wt-search-wrap i{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:var(--muted);
    font-size:16px;
}

.wt-input{
    height:42px;
    width:360px;
    border:1px solid var(--line);
    border-radius:4px;
    padding:0 13px 0 36px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:0;
    font-weight:700;
}

.wt-input:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.wt-input::placeholder{color:var(--muted)}

.wt-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    padding:10px 14px;
    border-radius:4px;
    font-size:13px;
    font-weight:900;
    border:0;
    cursor:pointer;
    text-decoration:none;
    white-space:nowrap;
}

.wt-btn-red{background:var(--red);color:#fff}
.wt-btn-red:hover{background:var(--red2);color:#fff}
.wt-btn-yellow{background:var(--gold);color:#111}
.wt-btn-ghost{background:var(--input);color:#fff;border:1px solid var(--line)}
.wt-btn-ghost:hover{border-color:var(--red);color:#fff}

.wt-table-wrap{overflow-x:auto}

.wt-table{
    width:100%;
    min-width:1100px;
    border-collapse:collapse;
    font-size:13px;
}

.wt-table th{
    background:var(--panel);
    color:var(--muted2);
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    padding:14px 16px;
    white-space:nowrap;
    text-align:left;
}

.wt-table td{
    padding:15px 16px;
    border-top:1px solid var(--line);
    color:#c9ced3;
    vertical-align:middle;
    font-weight:700;
}

.wt-table tr:hover td{
    background:#30363a;
}

.wt-ref{
    color:var(--red);
    font-family:monospace;
    font-weight:900;
}

.wt-name{
    font-weight:900;
    color:#fff;
}

.wt-small{
    font-size:11px;
    color:var(--muted);
    margin-top:3px;
}

.wt-wallet{
    display:inline-flex;
    align-items:center;
    gap:5px;
    margin-top:5px;
    padding:4px 8px;
    border-radius:20px;
    background:#3a1018;
    color:#ff9aaa;
    font-size:11px;
    font-weight:900;
}

.wt-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
    white-space:nowrap;
    text-transform:uppercase;
}

.wt-badge-green{
    background:#0d2b1e;
    color:var(--green);
}

.wt-amount{
    color:var(--green);
    font-weight:900;
    font-size:14px;
}

.wt-note{
    max-width:260px;
    color:#c9ced3;
    white-space:normal;
    line-height:1.5;
}

.wt-pagination{
    padding:16px 22px;
    border-top:1px solid var(--line);
    background:var(--panel);
}

.wt-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:800;
}

@media(max-width:1100px){
    .wt-stats{grid-template-columns:repeat(2,1fr)}
    .wt-wrap{margin:-82px 24px 0}
}

@media(max-width:768px){
    .wt-page{margin:-16px}
    .wt-hero{padding:45px 24px 110px}
    .wt-title-main{font-size:34px}
    .wt-wrap{margin:-76px 18px 0}
    .wt-stats{grid-template-columns:1fr}
    .wt-input{width:100%}
    .wt-search-wrap{width:100%}
}
</style>
@endpush

@section('content')

<div class="wt-page">

    <section class="wt-hero">
        <div class="wt-hero-inner">
            <div class="wt-eyebrow">Xynder Wallet Admin</div>

            <h1 class="wt-title-main">
                Wallet Transfer
                <span>History</span>
            </h1>

            <div class="wt-subtitle">
                Track wallet-to-wallet transfers, sender and receiver details,
                wallet IDs, transfer volume, notes, and transaction chats.
            </div>
        </div>
    </section>

    <main class="wt-wrap">

        <section class="wt-stats">
            <div class="wt-stat">
                <div class="wt-stat-icon">
                    <i class="ti ti-arrows-exchange"></i>
                </div>
                <div>
                    <div class="wt-stat-num">{{ $stats['total_count'] ?? 0 }}</div>
                    <div class="wt-stat-label">Total Transfers</div>
                </div>
            </div>

            <div class="wt-stat">
                <div class="wt-stat-icon">
                    <i class="ti ti-cash"></i>
                </div>
                <div>
                    <div class="wt-stat-num">$ {{ number_format((float)($stats['total_volume'] ?? 0), 2) }}</div>
                    <div class="wt-stat-label">Total Transfer Volume</div>
                </div>
            </div>

            <div class="wt-stat">
                <div class="wt-stat-icon">
                    <i class="ti ti-calendar"></i>
                </div>
                <div>
                    <div class="wt-stat-num">{{ $stats['today_count'] ?? 0 }}</div>
                    <div class="wt-stat-label">Today Transfers</div>
                </div>
            </div>

            <div class="wt-stat">
                <div class="wt-stat-icon">
                    <i class="ti ti-bolt"></i>
                </div>
                <div>
                    <div class="wt-stat-num">$ {{ number_format((float)($stats['today_volume'] ?? 0), 2) }}</div>
                    <div class="wt-stat-label">Today Volume</div>
                </div>
            </div>
        </section>

        <section class="wt-card">
            <div class="wt-card-head">
                <h2 class="wt-title">
                    <i class="ti ti-list-details"></i>
                    Wallet Transfer History
                </h2>
            </div>

            <form id="wtFilterForm"
                  method="GET"
                  action="{{ route('admin.wallet-transfers.index') }}"
                  class="wt-filters">

                <div class="wt-search-wrap">
                    <i class="ti ti-search"></i>

                    <input id="wtSearch"
                           type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="wt-input"
                           placeholder="Search sender, receiver, wallet ID, note...">
                </div>

                <button class="wt-btn wt-btn-red" type="submit">
                    <i class="ti ti-search"></i>
                    Search
                </button>

                <a href="{{ route('admin.wallet-transfers.index') }}"
                   class="wt-btn wt-btn-ghost">
                    <i class="ti ti-refresh"></i>
                    Reset
                </a>
            </form>

            <div class="wt-table-wrap">
                <table class="wt-table">
                    <thead>
                        <tr>
                            <th>Transaction No</th>
                            <th>Date</th>
                            <th>Sender</th>
                            <th>Receiver</th>
                            <th>Receiver Wallet</th>
                            <th>Amount</th>
                            <th>Note</th>
                            <th>Chat</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td>
                                <span class="wt-ref">
                                    {{ $transfer->transaction_no ?? ('TRA' . str_pad($transfer->id, 9, '0', STR_PAD_LEFT)) }}
                                </span>
                            </td>

                            <td>
                                <div class="wt-small">{{ $transfer->created_at?->format('d M Y') }}</div>
                                <div class="wt-small">{{ $transfer->created_at?->format('h:i A') }}</div>
                            </td>

                            <td>
                                <div class="wt-name">
                                    {{ $transfer->sender->name ?? 'Deleted User' }}
                                </div>

                                <div class="wt-small">
                                    {{ $transfer->sender->email ?? '-' }}
                                </div>

                                <div class="wt-wallet">
                                    <i class="ti ti-wallet"></i>
                                    {{ $transfer->sender->wallet_id ?? '-' }}
                                </div>
                            </td>

                            <td>
                                <div class="wt-name">
                                    {{ $transfer->receiver->name ?? 'Deleted User' }}
                                </div>

                                <div class="wt-small">
                                    {{ $transfer->receiver->email ?? '-' }}
                                </div>

                                <div class="wt-wallet">
                                    <i class="ti ti-wallet"></i>
                                    {{ $transfer->receiver->wallet_id ?? '-' }}
                                </div>
                            </td>

                            <td>
                                <span class="wt-badge wt-badge-green">
                                    <i class="ti ti-wallet"></i>
                                    {{ $transfer->receiver_wallet_id }}
                                </span>
                            </td>

                            <td>
                                <span class="wt-amount">
                                    $ {{ number_format((float)$transfer->amount, 2) }}
                                </span>
                            </td>

                            <td>
                                <div class="wt-note">
                                    {{ $transfer->note ?: '-' }}
                                </div>
                            </td>

                            <td>
                                <a class="wt-btn wt-btn-yellow"
                                   href="{{ route('admin.wallet-transfers.chat', $transfer) }}">
                                    <i class="ti ti-message-circle"></i>
                                    Chat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="wt-empty">No wallet transfers found.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="wt-pagination">
                {{ $transfers->appends(request()->query())->links() }}
            </div>
        </section>

    </main>
</div>

<script>
var wtSearchTimer = null;
var wtSearch = document.getElementById('wtSearch');

if (wtSearch) {
    wtSearch.addEventListener('keyup', function() {
        clearTimeout(wtSearchTimer);

        wtSearchTimer = setTimeout(function() {
            document.getElementById('wtFilterForm').submit();
        }, 500);
    });
}
</script>

@endsection


@extends('layouts.admin', ['title' => 'Wallet Transfer History'])

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
    --yellow:#facc15;
    --blue:#3b82f6;
    --red:#ef4444;
    --green:#22c55e;
    --page:#020617;
    --card:#0f172a;
    --border:#1e293b;
    --input:#111827;
    --text:#f8fafc;
    --muted:#94a3b8;
    --muted2:#cbd5e1;
    --radius:10px;
    --shadow:0 1px 3px rgba(0,0,0,.45);
}

.wt * {
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

.wt-page {
    background:var(--page);
    padding:20px;
    min-height:100vh;
    color:var(--text);
}

.wt-stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:18px;
}

.wt-stat {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:14px 16px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:var(--shadow);
}

.wt-stat-icon {
    width:42px;
    height:42px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}

.wt-stat-icon.yellow { background:rgba(250,204,21,.16); }
.wt-stat-icon.green { background:rgba(34,197,94,.16); }
.wt-stat-icon.blue { background:rgba(59,130,246,.18); }
.wt-stat-icon.red { background:rgba(239,68,68,.16); }

.wt-stat-num {
    font-size:22px;
    font-weight:800;
    color:var(--text);
    line-height:1;
}

.wt-stat-label {
    font-size:12px;
    color:var(--muted);
    margin-top:3px;
}

.wt-card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    overflow:hidden;
    margin-bottom:18px;
}

.wt-card-head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    padding:14px 16px;
    border-bottom:1px solid var(--border);
}

.wt-title {
    font-size:16px;
    font-weight:800;
    margin:0;
}

.wt-filters {
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
    padding:10px 16px;
    border-bottom:1px solid var(--border);
    background:#020617;
}

.wt-input {
    height:34px;
    border:1px solid #334155;
    border-radius:8px;
    padding:0 10px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:none;
    width:320px;
}

.wt-input::placeholder {
    color:var(--muted);
}

.wt-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:5px;
    padding:7px 13px;
    border-radius:8px;
    font-size:12px;
    font-weight:700;
    border:none;
    cursor:pointer;
    text-decoration:none;
    white-space:nowrap;
}

.wt-btn:hover {
    opacity:.88;
}

.wt-btn-yellow {
    background:var(--yellow);
    color:#111827;
}

.wt-btn-ghost {
    background:#020617;
    color:var(--muted2);
    border:1px solid var(--border);
}

.wt-table-wrap {
    overflow-x:auto;
}

.wt-table {
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

.wt-table th {
    background:#020617;
    color:var(--muted);
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    padding:10px 14px;
    border-bottom:1px solid var(--border);
    white-space:nowrap;
    text-align:left;
}

.wt-table td {
    padding:11px 14px;
    border-bottom:1px solid var(--border);
    color:var(--muted2);
    vertical-align:middle;
}

.wt-table tr:hover td {
    background:#111827;
}

.wt-name {
    font-weight:800;
    color:var(--text);
}

.wt-small {
    font-size:11px;
    color:var(--muted);
    margin-top:2px;
}

.wt-wallet {
    color:var(--yellow);
    font-size:11px;
    font-weight:700;
    margin-top:2px;
}

.wt-badge {
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:3px 9px;
    border-radius:20px;
    font-size:11px;
    font-weight:700;
    white-space:nowrap;
}

.wt-badge::before {
    content:'';
    width:6px;
    height:6px;
    border-radius:50%;
    background:#22c55e;
}

.wt-badge-green {
    background:rgba(34,197,94,.16);
    color:#86efac;
}

.wt-amount {
    color:#34d399;
    font-weight:800;
}

.wt-pagination {
    padding:12px 16px;
    border-top:1px solid var(--border);
}

@media(max-width:768px) {
    .wt-stats {
        grid-template-columns:repeat(2,1fr);
    }

    .wt-input {
        width:100%;
    }
}

@media(max-width:480px) {
    .wt-stats {
        grid-template-columns:1fr;
    }
}
</style>

<div class="wt">
<div class="wt-page">

    <div class="wt-stats">

        <div class="wt-stat">
            <div class="wt-stat-icon yellow">🔁</div>
            <div>
                <div class="wt-stat-num">
                    {{ $stats['total_count'] ?? 0 }}
                </div>
                <div class="wt-stat-label">
                    Total Transfers
                </div>
            </div>
        </div>

        <div class="wt-stat">
            <div class="wt-stat-icon green">💰</div>
            <div>
                <div class="wt-stat-num">
                    $ {{ number_format($stats['total_volume'] ?? 0, 2) }}
                </div>
                <div class="wt-stat-label">
                    Total Transfer Volume
                </div>
            </div>
        </div>

        <div class="wt-stat">
            <div class="wt-stat-icon blue">📅</div>
            <div>
                <div class="wt-stat-num">
                    {{ $stats['today_count'] ?? 0 }}
                </div>
                <div class="wt-stat-label">
                    Today Transfers
                </div>
            </div>
        </div>

        <div class="wt-stat">
            <div class="wt-stat-icon red">⚡</div>
            <div>
                <div class="wt-stat-num">
                    $ {{ number_format($stats['today_volume'] ?? 0, 2) }}
                </div>
                <div class="wt-stat-label">
                    Today Volume
                </div>
            </div>
        </div>

    </div>

    <div class="wt-card">

        <div class="wt-card-head">
            <h2 class="wt-title">
                Wallet Transfer History
            </h2>
        </div>

        <form
            id="wtFilterForm"
            method="GET"
            action="{{ route('admin.wallet-transfers.index') }}"
            class="wt-filters"
        >

            <input
                id="wtSearch"
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="wt-input"
                placeholder="Search sender, receiver, wallet ID, note..."
            >

            <button class="wt-btn wt-btn-yellow" type="submit">
                Search
            </button>

            <a
                href="{{ route('admin.wallet-transfers.index') }}"
                class="wt-btn wt-btn-ghost"
            >
                ↺ Reset
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
    <strong style="color:var(--yellow);">
        {{ $transfer->transaction_no ?? ('TRA' . str_pad($transfer->id, 9, '0', STR_PAD_LEFT)) }}
    </strong>
</td>
                        <td>
                            <div class="wt-small">
                                {{ $transfer->created_at->format('d M Y') }}
                            </div>

                            <div class="wt-small">
                                {{ $transfer->created_at->format('h:i A') }}
                            </div>
                        </td>

                        <td>
                            <div class="wt-name">
                                {{ $transfer->sender->name ?? 'Deleted User' }}
                            </div>

                            <div class="wt-small">
                                {{ $transfer->sender->email ?? '-' }}
                            </div>

                            <div class="wt-wallet">
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
                                {{ $transfer->receiver->wallet_id ?? '-' }}
                            </div>
                        </td>

                        <td>
                            <span class="wt-badge wt-badge-green">
                                {{ $transfer->receiver_wallet_id }}
                            </span>
                        </td>

                        <td>
                            <span class="wt-amount">
                                $ {{ number_format($transfer->amount, 2) }}
                            </span>
                        </td>

                        <td>
    {{ $transfer->note ?: '-' }}
</td>

<td>
    <a class="wt-btn wt-btn-yellow"
       href="{{ route('admin.wallet-transfers.chat', $transfer) }}">
        💬 Chat
    </a>
</td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--muted);padding:40px;">
                            No wallet transfers found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="wt-pagination">
            {{ $transfers->appends(request()->query())->links() }}
        </div>

    </div>

</div>
</div>

<script>
let wtSearchTimer;
const wtSearch = document.getElementById('wtSearch');

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
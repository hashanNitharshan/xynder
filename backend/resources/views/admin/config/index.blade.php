@extends('layouts.admin', ['title' => 'System Config'])

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

.cfg * {
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

.cfg-page {
    background:var(--page);
    padding:20px;
    min-height:100vh;
    color:var(--text);
}

.cfg-stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:18px;
}

.cfg-stat {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:14px 16px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:var(--shadow);
}

.cfg-stat-icon {
    width:42px;
    height:42px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}

.cfg-stat-icon.yellow { background:rgba(250,204,21,.16); }
.cfg-stat-icon.blue { background:rgba(59,130,246,.18); }
.cfg-stat-icon.green { background:rgba(34,197,94,.16); }
.cfg-stat-icon.red { background:rgba(239,68,68,.16); }

.cfg-stat-num {
    font-size:22px;
    font-weight:800;
    color:var(--text);
    line-height:1;
}

.cfg-stat-label {
    font-size:12px;
    color:var(--muted);
    margin-top:3px;
}

.cfg-card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    overflow:hidden;
    margin-bottom:18px;
}

.cfg-head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    padding:14px 16px;
    border-bottom:1px solid var(--border);
}

.cfg-title {
    font-size:16px;
    font-weight:800;
    margin:0;
}

.cfg-form {
    padding:16px;
}

.cfg-grid {
    display:grid;
    grid-template-columns:repeat(5, 1fr);
    gap:12px;
    margin-bottom:14px;
}

.cfg-group label {
    display:block;
    font-size:12px;
    font-weight:700;
    color:var(--muted2);
    margin-bottom:6px;
}

.cfg-control {
    width:100%;
    height:38px;
    border:1px solid #334155;
    border-radius:8px;
    padding:0 10px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:none;
}

.cfg-control:focus {
    border-color:var(--blue);
    box-shadow:0 0 0 3px rgba(59,130,246,.16);
}

.cfg-btn {
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:9px 15px;
    border-radius:8px;
    font-size:13px;
    font-weight:800;
    border:none;
    cursor:pointer;
    text-decoration:none;
    background:var(--yellow);
    color:#111827;
}

.cfg-btn:hover {
    opacity:.9;
}

.cfg-table-wrap {
    overflow-x:auto;
}

.cfg-table {
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

.cfg-table th {
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

.cfg-table td {
    padding:12px 14px;
    border-bottom:1px solid var(--border);
    color:var(--muted2);
    vertical-align:middle;
}

.cfg-table tr:hover td {
    background:#111827;
}

.cfg-value {
    font-weight:800;
    color:var(--text);
}

.cfg-yellow {
    color:var(--yellow);
}

.cfg-pagination {
    padding:12px 16px;
    border-top:1px solid var(--border);
}

@media(max-width:1000px) {
    .cfg-grid {
        grid-template-columns:repeat(2,1fr);
    }

    .cfg-stats {
        grid-template-columns:repeat(2,1fr);
    }
}

@media(max-width:520px) {
    .cfg-grid,
    .cfg-stats {
        grid-template-columns:1fr;
    }
}
</style>

<div class="cfg">
<div class="cfg-page">

    <div class="cfg-stats">
        <div class="cfg-stat">
            <div class="cfg-stat-icon yellow">💵</div>
            <div>
                <div class="cfg-stat-num">{{ number_format($config->usd_rate ?? 0, 4) }}</div>
                <div class="cfg-stat-label">USD Rate</div>
            </div>
        </div>

        <div class="cfg-stat">
            <div class="cfg-stat-icon blue">₹</div>
            <div>
                <div class="cfg-stat-num">{{ number_format($config->inr_rate ?? 0, 4) }}</div>
                <div class="cfg-stat-label">INR Rate</div>
            </div>
        </div>

        <div class="cfg-stat">
            <div class="cfg-stat-icon green">⚡</div>
            <div>
                <div class="cfg-stat-num">{{ number_format($config->xynder_fee ?? 0, 2) }}</div>
                <div class="cfg-stat-label">Xynder Fee</div>
            </div>
        </div>

        <div class="cfg-stat">
            <div class="cfg-stat-icon red">🌐</div>
            <div>
                <div class="cfg-stat-num">{{ number_format($config->network_fee ?? 0, 2) }}</div>
                <div class="cfg-stat-label">Network Fee</div>
            </div>
        </div>
    </div>

    <div class="cfg-card">
        <div class="cfg-head">
            <h2 class="cfg-title">System Config</h2>
        </div>

        <form method="POST" action="{{ route('admin.config.store') }}" class="cfg-form">
            @csrf

            <div class="cfg-grid">
                <div class="cfg-group">
                    <label>USD Rate</label>
                    <input class="cfg-control" type="number" step="0.0001" name="usd_rate"
                           value="{{ old('usd_rate', $config->usd_rate) }}" required>
                </div>

                <div class="cfg-group">
                    <label>INR Rate</label>
                    <input class="cfg-control" type="number" step="0.0001" name="inr_rate"
                           value="{{ old('inr_rate', $config->inr_rate) }}" required>
                </div>

                <div class="cfg-group">
                    <label>Xynder Fee</label>
                    <input class="cfg-control" type="number" step="0.01" name="xynder_fee"
                           value="{{ old('xynder_fee', $config->xynder_fee) }}" required>
                </div>

                <div class="cfg-group">
                    <label>Network Fee</label>
                    <input class="cfg-control" type="number" step="0.01" name="network_fee"
                           value="{{ old('network_fee', $config->network_fee) }}" required>
                </div>

                <div class="cfg-group">
                    <label>Effective Date</label>
                    <input class="cfg-control" type="date" name="effective_date"
                           value="{{ old('effective_date', now()->toDateString()) }}" required>
                </div>
            </div>

            <button class="cfg-btn">💾 Save Config</button>
        </form>
    </div>

    <div class="cfg-card">
        <div class="cfg-head">
            <h2 class="cfg-title">Config History</h2>
        </div>

        <div class="cfg-table-wrap">
            <table class="cfg-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>USD Rate</th>
                        <th>INR Rate</th>
                        <th>Xynder Fee</th>
                        <th>Network Fee</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($configs as $row)
                    <tr>
                        <td>
                            <span class="cfg-value">
                                {{ $row->effective_date?->format('Y-m-d') ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="cfg-value cfg-yellow">
                                {{ number_format($row->usd_rate, 4) }}
                            </span>
                        </td>

                        <td>
                            <span class="cfg-value">
                                {{ number_format($row->inr_rate, 4) }}
                            </span>
                        </td>

                        <td>
                            <span class="cfg-value">
                                {{ number_format($row->xynder_fee, 2) }}
                            </span>
                        </td>

                        <td>
                            <span class="cfg-value">
                                {{ number_format($row->network_fee, 2) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:35px;color:var(--muted);">
                            No config history found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="cfg-pagination">
            {{ $configs->links() }}
        </div>
    </div>

</div>
</div>

@endsection
@extends('layouts.admin', ['title' => 'System Config'])

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

    --yellow:#F0B90B;
    --yellow-dark:#C99400;
    --gold:#FFD45A;

    --green:#0ecb81;
    --red:#ef4444;

    --text:#fff;
    --muted:#848E9C;
    --muted2:#5e6673;

    --shadow:0 18px 45px rgba(0,0,0,.35);
}

*{box-sizing:border-box}

.cfg-page{
    margin:-24px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding:24px 36px 48px;
}

.cfg-head{
    background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);
    border:1px solid var(--border);
    border-radius:16px;
    padding:18px 20px;
    box-shadow:var(--shadow);
    margin-bottom:20px;
}

.cfg-kicker{
    color:var(--yellow);
    font-size:11px;
    font-weight:800;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:5px;
}

.cfg-head h1{
    margin:0;
    font-size:19px;
    font-weight:800;
    letter-spacing:-.2px;
    color:#fff;
}

.cfg-head p{
    margin:4px 0 0;
    color:var(--muted);
    font-size:12px;
    font-weight:600;
}

.cfg-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:20px;
}

.cfg-stat{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    padding:18px;
    display:flex;
    align-items:center;
    gap:14px;
    min-height:95px;
    box-shadow:var(--shadow);
}

.cfg-stat-icon{
    width:44px;
    height:44px;
    border-radius:50%;
    background:rgba(240,185,11,.12);
    border:1px solid rgba(240,185,11,.35);
    color:var(--yellow);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    flex-shrink:0;
}

.cfg-stat-num{
    font-size:22px;
    font-weight:800;
    color:#fff;
    line-height:1;
}

.cfg-stat-label{
    color:var(--muted);
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-top:5px;
}

.cfg-card{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    box-shadow:var(--shadow);
    overflow:hidden;
    margin-bottom:20px;
}

.cfg-card:hover{
    border-color:rgba(240,185,11,.42);
}

.cfg-card-head{
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.cfg-title{
    font-size:15px;
    font-weight:800;
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
}

.cfg-title i{color:var(--yellow); font-size:18px;}

.cfg-subtitle{
    font-size:12px;
    color:var(--muted);
    margin-top:3px;
    font-weight:600;
}

.cfg-form{
    padding:20px;
}

.cfg-grid{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:16px;
    margin-bottom:20px;
}

.cfg-group label{
    display:block;
    font-size:11px;
    font-weight:800;
    color:#c3c6ca;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:8px;
}

.cfg-control{
    width:100%;
    height:42px;
    border:1px solid var(--border);
    border-radius:9px;
    padding:0 13px;
    font-size:13px;
    background:var(--bg);
    color:var(--text);
    outline:0;
    font-weight:600;
    font-family:inherit;
}

.cfg-control:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(240,185,11,.12);
}

.cfg-btn{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:11px 18px;
    border-radius:9px;
    font-size:13px;
    font-weight:800;
    border:0;
    cursor:pointer;
    text-decoration:none;
    font-family:inherit;
    background:var(--yellow);
    color:#0B0E11;
}

.cfg-btn:hover{
    background:var(--yellow-dark);
    color:#0B0E11;
}

.cfg-table-wrap{
    overflow-x:auto;
}

.cfg-table{
    width:100%;
    min-width:850px;
    border-collapse:collapse;
    font-size:13px;
}

.cfg-table th{
    background:var(--surface-alt);
    color:var(--muted);
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.05em;
    padding:13px 16px;
    border-bottom:1px solid var(--border);
    white-space:nowrap;
    text-align:left;
}

.cfg-table td{
    padding:15px 16px;
    border-top:1px solid var(--border);
    color:#d5dade;
    vertical-align:middle;
    font-weight:600;
}

.cfg-table tr:hover td{
    background:var(--surface-alt);
}

.cfg-value{
    font-weight:800;
    color:#fff;
}

.cfg-yellow{ color:var(--yellow); }
.cfg-gold{ color:var(--gold); }
.cfg-green{ color:var(--green); }

.cfg-pagination{
    padding:16px 20px;
    border-top:1px solid var(--border);
    background:var(--surface);
}

.cfg-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:700;
}

@media(max-width:1200px){
    .cfg-grid{grid-template-columns:repeat(2,1fr)}
    .cfg-stats{grid-template-columns:repeat(2,1fr)}
}

@media(max-width:768px){
    .cfg-page{
        margin:-18px;
        padding:16px 14px 30px;
    }
    .cfg-grid,
    .cfg-stats{grid-template-columns:1fr}
    .cfg-form{padding:16px}
    .cfg-table{min-width:0}
}
</style>
@endpush

@section('content')

<div class="cfg-page">

    <section class="cfg-head">
        <div class="cfg-kicker">BITXNOW ADMIN</div>
        <h1>System Config</h1>
        <p>Manage exchange rates and fees.</p>
    </section>

    <section class="cfg-stats">
        <div class="cfg-stat">
            <div class="cfg-stat-icon">
                <i class="ti ti-currency-dollar"></i>
            </div>
            <div>
                <div class="cfg-stat-num">{{ number_format((float)($config->usd_rate ?? 0), 4) }}</div>
                <div class="cfg-stat-label">USD Rate</div>
            </div>
        </div>

        <div class="cfg-stat">
            <div class="cfg-stat-icon">
                <i class="ti ti-currency-rupee"></i>
            </div>
            <div>
                <div class="cfg-stat-num">{{ number_format((float)($config->inr_rate ?? 0), 4) }}</div>
                <div class="cfg-stat-label">INR Rate</div>
            </div>
        </div>

        <div class="cfg-stat">
            <div class="cfg-stat-icon">
                <i class="ti ti-bolt"></i>
            </div>
            <div>
                <div class="cfg-stat-num">{{ number_format((float)($config->xynder_fee ?? 0), 2) }}</div>
                <div class="cfg-stat-label">Xynder Fee</div>
            </div>
        </div>

        <div class="cfg-stat">
            <div class="cfg-stat-icon">
                <i class="ti ti-world"></i>
            </div>
            <div>
                <div class="cfg-stat-num">{{ number_format((float)($config->network_fee ?? 0), 2) }}</div>
                <div class="cfg-stat-label">Network Fee</div>
            </div>
        </div>
    </section>

    <section class="cfg-card">
        <div class="cfg-card-head">
            <div>
                <h2 class="cfg-title">
                    <i class="ti ti-settings"></i>
                    Update System Config
                </h2>
                <div class="cfg-subtitle">Set new exchange rates and fees.</div>
            </div>
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

            <button class="cfg-btn" type="submit">
                <i class="ti ti-device-floppy"></i>
                Save Config
            </button>
        </form>
    </section>

    <section class="cfg-card">
        <div class="cfg-card-head">
            <div>
                <h2 class="cfg-title">
                    <i class="ti ti-history"></i>
                    Config History
                </h2>
                <div class="cfg-subtitle">Previous rate and fee changes.</div>
            </div>
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
                                {{ number_format((float)$row->usd_rate, 4) }}
                            </span>
                        </td>

                        <td>
                            <span class="cfg-value cfg-gold">
                                {{ number_format((float)$row->inr_rate, 4) }}
                            </span>
                        </td>

                        <td>
                            <span class="cfg-value cfg-green">
                                {{ number_format((float)$row->xynder_fee, 2) }}
                            </span>
                        </td>

                        <td>
                            <span class="cfg-value">
                                {{ number_format((float)$row->network_fee, 2) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="cfg-empty">No config history found.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="cfg-pagination">
            {{ $configs->links() }}
        </div>
    </section>

</div>

@endsection
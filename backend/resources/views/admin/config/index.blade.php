
@extends('layouts.admin', ['title' => 'System Config'])

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

.cfg-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.cfg-hero{
    background:var(--hero);
    padding:65px 85px 120px;
    position:relative;
    overflow:hidden;
}

.cfg-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.cfg-hero-inner{
    position:relative;
    z-index:2;
    max-width:680px;
}

.cfg-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.cfg-title-main{
    font-size:46px;
    line-height:1.12;
    font-weight:900;
    margin:0 0 18px;
}

.cfg-title-main span{
    color:var(--red);
    display:block;
}

.cfg-subtitle{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    max-width:580px;
}

.cfg-wrap{
    position:relative;
    z-index:5;
    margin:-82px 85px 0;
}

.cfg-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:26px;
}

.cfg-stat{
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
    padding:20px;
    display:flex;
    align-items:center;
    gap:14px;
    min-height:105px;
}

.cfg-stat-icon{
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

.cfg-stat-num{
    font-size:24px;
    font-weight:900;
    color:#fff;
    line-height:1;
}

.cfg-stat-label{
    color:var(--muted);
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-top:5px;
}

.cfg-card{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
    margin-bottom:24px;
}

.cfg-head{
    background:var(--panel);
    border-bottom:1px solid var(--line);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    padding:18px 22px;
}

.cfg-title{
    font-size:18px;
    font-weight:900;
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
}

.cfg-title i{color:var(--red)}

.cfg-form{
    padding:24px;
}

.cfg-grid{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:16px;
    margin-bottom:22px;
}

.cfg-group label{
    display:block;
    font-size:12px;
    font-weight:900;
    color:#c3c6ca;
    margin-bottom:8px;
}

.cfg-control{
    width:100%;
    height:44px;
    border:1px solid var(--line);
    border-radius:4px;
    padding:0 13px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:0;
    font-weight:700;
}

.cfg-control:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.cfg-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:12px 18px;
    border-radius:4px;
    font-size:13px;
    font-weight:900;
    border:0;
    cursor:pointer;
    text-decoration:none;
    background:var(--red);
    color:#fff;
}

.cfg-btn:hover{
    background:var(--red2);
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

.cfg-table td{
    padding:15px 16px;
    border-top:1px solid var(--line);
    color:#c9ced3;
    vertical-align:middle;
    font-weight:700;
}

.cfg-table tr:hover td{
    background:#30363a;
}

.cfg-value{
    font-weight:900;
    color:#fff;
}

.cfg-red{
    color:var(--red);
}

.cfg-gold{
    color:var(--gold);
}

.cfg-green{
    color:var(--green);
}

.cfg-pagination{
    padding:16px 22px;
    border-top:1px solid var(--line);
    background:var(--panel);
}

.cfg-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:800;
}

@media(max-width:1200px){
    .cfg-grid{grid-template-columns:repeat(2,1fr)}
    .cfg-stats{grid-template-columns:repeat(2,1fr)}
    .cfg-wrap{margin:-82px 24px 0}
}

@media(max-width:650px){
    .cfg-page{margin:-16px}
    .cfg-hero{padding:45px 24px 110px}
    .cfg-title-main{font-size:34px}
    .cfg-wrap{margin:-76px 18px 0}
    .cfg-grid,
    .cfg-stats{grid-template-columns:1fr}
    .cfg-form{padding:20px 18px}
}
</style>
@endpush

@section('content')

<div class="cfg-page">

    <section class="cfg-hero">
        <div class="cfg-hero-inner">
            <div class="cfg-eyebrow">Xynder Wallet Admin</div>

            <h1 class="cfg-title-main">
                System
                <span>Config</span>
            </h1>

            <div class="cfg-subtitle">
                Manage USD and INR exchange rates, Xynder transaction fee,
                network fee, and effective date history for wallet requests.
            </div>
        </div>
    </section>

    <main class="cfg-wrap">

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
            <div class="cfg-head">
                <h2 class="cfg-title">
                    <i class="ti ti-settings"></i>
                    Update System Config
                </h2>
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
            <div class="cfg-head">
                <h2 class="cfg-title">
                    <i class="ti ti-history"></i>
                    Config History
                </h2>
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
                                <span class="cfg-value cfg-red">
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

    </main>
</div>

@endsection


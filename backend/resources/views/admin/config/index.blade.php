@extends('layouts.admin', ['title' => 'System Config'])

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
    --red:#EF4444;
    --gold:#FFD45A;
    --text:#FFFFFF;
    --muted:#848E9C;
    --muted-dark:#5E6673;
}

*{
    box-sizing:border-box;
}

.cfg-page{
    margin:-24px;
    min-height:100vh;
    padding-bottom:60px;
    background:var(--dark);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;
}

.cfg-hero{
    min-height:185px;
    position:relative;
    overflow:hidden;
    background:
        radial-gradient(circle at 88% 15%,rgba(240,185,11,.12),transparent 31%),
        var(--hero);
}

.cfg-hero::after{
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

.cfg-wrap{
    position:relative;
    z-index:5;
    margin:-82px 45px 0;
}

.cfg-stats{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:18px;
    margin-bottom:28px;
}

.cfg-stat{
    min-height:128px;
    padding:20px;
    position:relative;
    overflow:hidden;
    background:var(--box);
    border:1px solid var(--line);
    border-radius:6px;
}

.cfg-stat::after{
    content:"";
    position:absolute;
    right:-38px;
    top:-38px;
    width:115px;
    height:115px;
    border-radius:50%;
    background:rgba(240,185,11,.10);
}

.cfg-stat-icon{
    width:42px;
    height:42px;
    margin-bottom:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    color:var(--yellow);
    background:rgba(240,185,11,.12);
    font-size:22px;
}

.cfg-stat-label{
    color:var(--muted);
    font-size:11px;
    font-weight:900;
    letter-spacing:.06em;
    text-transform:uppercase;
}

.cfg-stat-value{
    margin-top:6px;
    color:#FFFFFF;
    font-size:22px;
    font-weight:800;
    word-break:break-word;
}

.cfg-card{
    margin-bottom:28px;
    overflow:hidden;
    background:var(--box);
    border:1.5px solid var(--yellow);
    border-radius:7px;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.cfg-card-head{
    padding:18px 22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    background:var(--panel);
    border-bottom:1px solid var(--line);
}

.cfg-title-wrap{
    min-width:0;
}

.cfg-title{
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
    color:#FFFFFF;
    font-size:15px;
    font-weight:800;
}

.cfg-title i{
    color:var(--yellow);
}

.cfg-subtitle{
    margin-top:4px;
    color:var(--muted);
    font-size:11px;
    font-weight:700;
}

.cfg-form{
    padding:20px 22px 22px;
}

.cfg-grid{
    display:grid;
    grid-template-columns:repeat(5,minmax(0,1fr));
    gap:14px;
    margin-bottom:18px;
}

.cfg-group label{
    display:block;
    margin-bottom:7px;
    color:var(--muted);
    font-size:10px;
    font-weight:900;
    letter-spacing:.05em;
    text-transform:uppercase;
}

.cfg-control{
    width:100%;
    height:42px;
    padding:0 13px;
    outline:0;
    color:var(--text);
    background:var(--input);
    border:1px solid var(--line);
    border-radius:4px;
    font-family:inherit;
    font-size:13px;
    font-weight:700;
}

.cfg-control:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(240,185,11,.12);
}

.cfg-btn{
    min-height:42px;
    padding:10px 16px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    color:#111111;
    background:var(--yellow);
    border:0;
    border-radius:4px;
    font-family:inherit;
    font-size:12px;
    font-weight:900;
    cursor:pointer;
}

.cfg-btn:hover{
    background:var(--yellow-dark);
}

.cfg-table-wrap{
    width:100%;
    overflow-x:auto;
}

.cfg-table{
    width:100%;
    min-width:760px;
    border-collapse:collapse;
    table-layout:fixed;
    font-size:13px;
}

.cfg-table th{
    padding:14px 12px;
    text-align:left;
    color:var(--muted-dark);
    background:var(--panel);
    font-size:10px;
    font-weight:900;
    letter-spacing:.05em;
    text-transform:uppercase;
}

.cfg-table td{
    padding:15px 12px;
    vertical-align:middle;
    color:#C9CED3;
    border-top:1px solid var(--line);
    font-size:12px;
    font-weight:700;
    word-break:break-word;
}

.cfg-table tr:hover td{
    background:#1E2329;
}

.cfg-value{
    color:#FFFFFF;
    font-weight:900;
}

.cfg-yellow{
    color:var(--yellow);
}

.cfg-gold{
    color:var(--gold);
}

.cfg-green{
    color:var(--green);
}

.cfg-empty{
    padding:45px;
    text-align:center;
    color:var(--muted);
    font-weight:800;
}

.cfg-pagination{
    padding:16px 22px;
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:14px;
    flex-wrap:wrap;
    background:var(--panel);
    border-top:1px solid var(--line);
}

.cfg-pagination nav{
    width:auto;
    max-width:100%;
}

.cfg-pagination nav > div{
    display:flex!important;
    align-items:center!important;
    gap:8px!important;
    flex-wrap:wrap!important;
}

.cfg-pagination nav p{
    margin:0!important;
    color:var(--muted)!important;
    font-size:12px!important;
    font-weight:800!important;
}

.cfg-pagination nav a,
.cfg-pagination nav span{
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

.cfg-pagination nav span[aria-current="page"],
.cfg-pagination nav span[aria-current="page"] span{
    color:#111111!important;
    background:var(--yellow)!important;
    border-color:var(--yellow)!important;
}

.cfg-pagination nav a:hover{
    color:var(--yellow)!important;
    border-color:var(--yellow)!important;
}

.cfg-pagination nav svg{
    width:16px!important;
    height:16px!important;
    max-width:16px!important;
    max-height:16px!important;
    display:block!important;
    stroke-width:3!important;
}

.cfg-pagination nav .hidden{
    display:none!important;
}

.cfg-pagination nav div:first-child{
    display:none!important;
}

.cfg-pagination nav div:last-child{
    display:flex!important;
}

@media(max-width:1250px){
    .cfg-wrap{
        margin:-82px 24px 0;
    }

    .cfg-grid{
        grid-template-columns:repeat(3,minmax(0,1fr));
    }
}

@media(max-width:1000px){
    .cfg-stats{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }

    .cfg-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}

@media(max-width:768px){
    .cfg-page{
        margin:-16px;
    }

    .cfg-hero{
        min-height:160px;
    }

    .cfg-wrap{
        margin:-70px 18px 0;
    }

    .cfg-stats,
    .cfg-grid{
        grid-template-columns:1fr;
    }

    .cfg-form{
        padding:18px;
    }

    .cfg-table,
    .cfg-table thead,
    .cfg-table tbody,
    .cfg-table th,
    .cfg-table td,
    .cfg-table tr{
        display:block;
        width:100%!important;
    }

    .cfg-table{
        min-width:0;
    }

    .cfg-table thead{
        display:none;
    }

    .cfg-table tr{
        margin:12px;
        padding:14px;
        background:var(--panel);
        border:1px solid var(--line);
        border-radius:7px;
    }

    .cfg-table td{
        padding:9px 0;
        border-top:0;
    }

    .cfg-table td::before{
        content:attr(data-label);
        display:block;
        margin-bottom:5px;
        color:var(--muted-dark);
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
    }

    .cfg-pagination{
        justify-content:flex-start;
    }
}
</style>
@endpush

@section('content')
<div class="cfg-page">
    <section class="cfg-hero"></section>

    <main class="cfg-wrap">
        <section class="cfg-stats">
            <div class="cfg-stat">
                <div class="cfg-stat-icon">
                    <i class="ti ti-currency-dollar"></i>
                </div>

                <div class="cfg-stat-label">
                    USD Rate
                </div>

                <div class="cfg-stat-value">
                    {{ number_format((float)($config->usd_rate ?? 0), 4) }}
                </div>
            </div>

            <div class="cfg-stat">
                <div class="cfg-stat-icon">
                    <i class="ti ti-currency-rupee"></i>
                </div>

                <div class="cfg-stat-label">
                    INR Rate
                </div>

                <div class="cfg-stat-value">
                    {{ number_format((float)($config->inr_rate ?? 0), 4) }}
                </div>
            </div>

            <div class="cfg-stat">
                <div class="cfg-stat-icon">
                    <i class="ti ti-bolt"></i>
                </div>

                <div class="cfg-stat-label">
                    Xynder Fee
                </div>

                <div class="cfg-stat-value">
                    {{ number_format((float)($config->xynder_fee ?? 0), 2) }}
                </div>
            </div>

            <div class="cfg-stat">
                <div class="cfg-stat-icon">
                    <i class="ti ti-world"></i>
                </div>

                <div class="cfg-stat-label">
                    Network Fee
                </div>

                <div class="cfg-stat-value">
                    {{ number_format((float)($config->network_fee ?? 0), 2) }}
                </div>
            </div>
        </section>

        <section class="cfg-card">
            <div class="cfg-card-head">
                <div class="cfg-title-wrap">
                    <h2 class="cfg-title">
                        <i class="ti ti-settings"></i>
                        Update System Config
                    </h2>

                    <div class="cfg-subtitle">
                        Set new exchange rates and fees.
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.config.store') }}"
                class="cfg-form"
            >
                @csrf

                <div class="cfg-grid">
                    <div class="cfg-group">
                        <label for="usd_rate">
                            USD Rate
                        </label>

                        <input
                            id="usd_rate"
                            class="cfg-control"
                            type="number"
                            step="0.0001"
                            name="usd_rate"
                            value="{{ old('usd_rate', $config->usd_rate) }}"
                            required
                        >
                    </div>

                    <div class="cfg-group">
                        <label for="inr_rate">
                            INR Rate
                        </label>

                        <input
                            id="inr_rate"
                            class="cfg-control"
                            type="number"
                            step="0.0001"
                            name="inr_rate"
                            value="{{ old('inr_rate', $config->inr_rate) }}"
                            required
                        >
                    </div>

                    <div class="cfg-group">
                        <label for="xynder_fee">
                            Xynder Fee
                        </label>

                        <input
                            id="xynder_fee"
                            class="cfg-control"
                            type="number"
                            step="0.01"
                            name="xynder_fee"
                            value="{{ old('xynder_fee', $config->xynder_fee) }}"
                            required
                        >
                    </div>

                    <div class="cfg-group">
                        <label for="network_fee">
                            Network Fee
                        </label>

                        <input
                            id="network_fee"
                            class="cfg-control"
                            type="number"
                            step="0.01"
                            name="network_fee"
                            value="{{ old('network_fee', $config->network_fee) }}"
                            required
                        >
                    </div>

                    <div class="cfg-group">
                        <label for="effective_date">
                            Effective Date
                        </label>

                        <input
                            id="effective_date"
                            class="cfg-control"
                            type="date"
                            name="effective_date"
                            value="{{ old('effective_date', now()->toDateString()) }}"
                            required
                        >
                    </div>
                </div>

                <button
                    class="cfg-btn"
                    type="submit"
                >
                    <i class="ti ti-device-floppy"></i>
                    Save Config
                </button>
            </form>
        </section>

        <section class="cfg-card">
            <div class="cfg-card-head">
                <div class="cfg-title-wrap">
                    <h2 class="cfg-title">
                        <i class="ti ti-history"></i>
                        Config History
                    </h2>

                    <div class="cfg-subtitle">
                        Previous rate and fee changes.
                    </div>
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
                            <td data-label="Date">
                                <span class="cfg-value">
                                    {{ $row->effective_date?->format('Y-m-d') ?? '-' }}
                                </span>
                            </td>

                            <td data-label="USD Rate">
                                <span class="cfg-value cfg-yellow">
                                    {{ number_format((float)$row->usd_rate, 4) }}
                                </span>
                            </td>

                            <td data-label="INR Rate">
                                <span class="cfg-value cfg-gold">
                                    {{ number_format((float)$row->inr_rate, 4) }}
                                </span>
                            </td>

                            <td data-label="Xynder Fee">
                                <span class="cfg-value cfg-green">
                                    {{ number_format((float)$row->xynder_fee, 2) }}
                                </span>
                            </td>

                            <td data-label="Network Fee">
                                <span class="cfg-value">
                                    {{ number_format((float)$row->network_fee, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="cfg-empty">
                                    No config history found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="cfg-pagination">
                {{ $configs->appends(request()->query())->links() }}
            </div>
        </section>
    </main>
</div>
@endsection
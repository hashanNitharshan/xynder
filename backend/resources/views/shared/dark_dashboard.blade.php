@extends('layouts.admin', ['title' => ($mode ?? 'client') === 'merchant' ? 'Merchant Dashboard' : 'Client Dashboard'])

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
    --border-soft:#202630;
    --yellow:#F0B90B;
    --yellow-dark:#C99400;
    --gold:#FFD45A;
    --green:#0ecb81;
    --red:#ef4444;
    --text:#ffffff;
    --muted:#848E9C;
    --muted2:#5e6673;
}
*{box-sizing:border-box}

.dash-page{
    margin:-28px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding-bottom:60px;
}

.dash-hero{
    position:relative;
    min-height:360px;
    padding:62px 85px 125px;
    background:var(--surface);
    border-bottom:1px solid var(--border);
    overflow:hidden;
}
.dash-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.06;
    background-image:linear-gradient(120deg,transparent 20%,rgba(240,185,11,.55) 21%,transparent 22%);
    background-size:260px 260px;
}
.dash-hero-content{position:relative;z-index:2;max-width:720px}
.dash-eyebrow{color:var(--yellow);font-size:11.5px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;margin-bottom:14px}
.dash-title{font-size:28px;line-height:1.18;font-weight:800;margin:0 0 14px;color:#fff}
.dash-title span{display:block;color:var(--yellow)}
.dash-sub{color:var(--muted);font-size:14px;line-height:1.7;font-weight:700;max-width:520px}

.dash-hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:24px}
.dash-action{
    height:44px;
    padding:0 18px;
    border-radius:8px;
    border:1px solid var(--border);
    background:var(--surface-alt);
    color:#fff;
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    font-weight:900;
    text-decoration:none;
}
.dash-action.primary{background:var(--yellow);border-color:var(--yellow);color:#0B0E11}
.dash-action:hover{filter:brightness(1.08);text-decoration:none;color:inherit}

.dash-wrap{
    position:relative;
    z-index:5;
    margin:-72px 85px 0;
    display:grid;
    grid-template-columns:310px 1fr;
    gap:22px;
    align-items:start;
}
.dash-side,.dash-main{display:flex;flex-direction:column;gap:18px;min-width:0}

.dash-card{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.25);
}
.dash-profile{border:1.5px solid var(--yellow);padding:26px 22px;text-align:center}
.dash-avatar{width:86px;height:86px;margin:0 auto 14px;border-radius:50%;background:var(--yellow);padding:4px}
.dash-avatar img,.dash-avatar-inner{width:100%;height:100%;border-radius:50%;object-fit:cover}
.dash-avatar-inner{background:var(--surface-alt);display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:900;color:#fff}
.dash-profile h3{font-size:16px;font-weight:800;margin:0 0 5px;color:#fff}
.dash-role{color:var(--muted);font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
.dash-wallet{margin-top:16px;background:var(--surface-alt);border:1px solid var(--border);border-radius:8px;padding:12px;display:flex;align-items:center;justify-content:space-between;gap:10px;text-align:left}
.dash-wallet small{display:block;color:var(--muted2);font-size:10px;font-weight:900;text-transform:uppercase;margin-bottom:3px}
.dash-wallet span{font-family:monospace;color:#fff;font-size:13px;word-break:break-all}
.dash-copy{border:0;background:var(--yellow);color:#0B0E11;border-radius:7px;padding:8px 10px;cursor:pointer;font-weight:900;flex-shrink:0}
.dash-badge{display:inline-flex;align-items:center;gap:6px;margin-top:13px;padding:7px 12px;border-radius:999px;font-size:10.5px;font-weight:800}
.dash-badge.ok{background:rgba(14,203,129,.12);color:var(--green);border:1px solid rgba(14,203,129,.35)}
.dash-badge.pending{background:rgba(240,185,11,.12);color:var(--yellow);border:1px solid rgba(240,185,11,.35)}

.dash-section-title{padding:17px 20px;border-bottom:1px solid var(--border);font-size:14px;font-weight:800;display:flex;align-items:center;gap:9px}
.dash-section-title i{color:var(--yellow)}
.dash-bs{padding:8px 20px 18px}
.dash-bs-row{display:flex;justify-content:space-between;gap:14px;padding:13px 0;border-bottom:1px solid var(--border)}
.dash-bs-row:last-child{border-bottom:0}
.dash-bs-row span{color:var(--muted);font-size:13px;font-weight:800}
.dash-bs-row strong{color:#fff;font-size:12.5px;font-weight:800;text-align:right}

.dash-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.dash-kpi{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:20px;transition:.15s;min-width:0}
.dash-kpi:hover{border-color:var(--yellow);background:var(--surface-alt)}
.dash-kpi-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;gap:10px}
.dash-kpi-icon{width:44px;height:44px;border-radius:50%;background:rgba(240,185,11,.12);color:var(--yellow);border:1px solid rgba(240,185,11,.35);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.dash-trend{padding:5px 9px;border-radius:999px;font-size:10.5px;font-weight:800;white-space:nowrap}
.dash-trend.green{background:rgba(14,203,129,.12);color:var(--green)}
.dash-trend.gold{background:rgba(240,185,11,.12);color:var(--yellow)}
.dash-trend.gray{background:var(--surface-alt);color:var(--muted);border:1px solid var(--border)}
.dash-kpi small{color:var(--muted);font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
.dash-kpi b{display:block;margin-top:7px;font-size:21px;font-weight:800;color:#fff;word-break:break-word}
.dash-kpi p{margin:6px 0 0;color:var(--muted2);font-size:12px;font-weight:700}

.dash-wallet-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.dash-wcard{min-height:140px;border-radius:10px;padding:22px;background:var(--surface);border:1px solid var(--border);position:relative;overflow:hidden}
.dash-wcard::after{content:"";position:absolute;right:-50px;top:-50px;width:150px;height:150px;border-radius:50%;background:rgba(240,185,11,.12)}
.dash-wcard-top{position:relative;z-index:2;display:flex;justify-content:space-between;gap:10px;margin-bottom:18px}
.dash-wcard-type{color:var(--muted);font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
.dash-wcard-pill{background:rgba(240,185,11,.12);color:var(--yellow);border:1px solid rgba(240,185,11,.28);border-radius:999px;padding:4px 9px;font-size:10px;font-weight:900}
.dash-wcard-val{position:relative;z-index:2;font-size:20px;font-weight:800;color:#fff;word-break:break-word}
.dash-wcard-label{position:relative;z-index:2;color:var(--muted);margin-top:5px;font-size:12px;font-weight:700}

.dash-chart,.dash-donut,.dash-activity{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:22px;overflow:hidden}
.dash-chart-head,.dash-activity-head{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:18px;flex-wrap:wrap}
.dash-chart-head h2,.dash-activity-head h2{font-size:15px;font-weight:800;margin:0;color:#fff;display:flex;gap:9px;align-items:center}
.dash-chart-head h2 i,.dash-activity-head h2 i{color:var(--yellow)}
.dash-tabs{display:flex;background:var(--surface-alt);border:1px solid var(--border);border-radius:8px;padding:4px}
.dash-tab{border:0;background:transparent;color:var(--muted);padding:8px 15px;border-radius:6px;font-size:11.5px;font-weight:800;cursor:pointer}
.dash-tab.active{background:var(--yellow);color:#0B0E11}
.dash-chart-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px}
.dash-chart-stat{background:var(--surface-alt);border:1px solid var(--border);border-radius:8px;padding:14px}
.dash-chart-stat small{color:var(--muted);font-size:10.5px;font-weight:800;text-transform:uppercase}
.dash-chart-stat b{display:block;margin-top:6px;font-size:16px;font-weight:800;color:#fff}
.dash-chart-box{height:260px;position:relative}

.dash-donut-grid{display:grid;grid-template-columns:250px 1fr;gap:20px;align-items:center}
.dash-donut-wrap{position:relative;display:flex;justify-content:center;margin:14px 0}
.dash-donut-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center}
.dash-donut-center b{font-size:20px;font-weight:800;color:#fff}
.dash-donut-center small{display:block;color:var(--muted);font-size:10.5px;font-weight:800}
.dash-legend{display:flex;flex-direction:column;gap:12px}
.dash-legend-row{display:flex;justify-content:space-between;gap:10px;color:var(--muted);font-size:13px;font-weight:800;background:var(--surface-alt);border:1px solid var(--border);border-radius:8px;padding:12px 14px}
.dash-legend-key{display:flex;align-items:center;gap:8px}
.dash-dot{width:10px;height:10px;border-radius:50%}

.dash-table-wrap{overflow-x:auto}
.dash-table{width:100%;min-width:900px;border-collapse:collapse}
.dash-table th{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.05em;text-align:left;padding:14px 16px;background:var(--surface-alt);font-weight:900}
.dash-table td{padding:15px 16px;border-top:1px solid var(--border);color:#d5dade;font-size:13px;font-weight:700;white-space:nowrap}
.dash-table tr:hover td{background:var(--surface-alt)}
.dash-click{cursor:pointer}
.dash-ref{font-family:monospace;color:var(--muted)}
.dash-type{display:inline-flex;align-items:center;gap:7px;font-weight:900}
.dash-type.buy,.dash-type.received{color:var(--green)}
.dash-type.sell,.dash-type.sent{color:var(--red)}
.dash-badge-status{display:inline-flex;padding:5px 10px;border-radius:999px;font-size:10.5px;font-weight:800;text-transform:uppercase}
.dash-badge-status.pending{background:rgba(240,185,11,.12);color:var(--yellow)}
.dash-badge-status.approved,.dash-badge-status.completed{background:rgba(14,203,129,.12);color:var(--green)}
.dash-badge-status.rejected{background:rgba(239,68,68,.12);color:#ff9b9b}
.dash-empty{text-align:center;padding:34px;color:var(--muted);font-weight:800}

.dash-notice{background:rgba(240,185,11,.12);border:1px solid rgba(240,185,11,.35);border-radius:10px;color:#ffe7a3;padding:15px 18px;font-weight:800;display:flex;align-items:center;gap:10px}

@media(max-width:1200px){
    .dash-wrap{grid-template-columns:1fr}
    .dash-side{display:grid;grid-template-columns:repeat(2,1fr)}
    .dash-kpis{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:800px){
    .dash-page{margin:-18px}
    .dash-hero{min-height:320px;padding:45px 20px 105px}
    .dash-title{font-size:24px}
    .dash-sub{font-size:13px}
    .dash-wrap{margin:-58px 0 0;gap:16px}
    .dash-side{grid-template-columns:1fr;padding:0 14px}
    .dash-main{gap:16px}
    .dash-kpis,.dash-wallet-cards{grid-template-columns:1fr;padding:0 14px}
    .dash-chart,.dash-donut,.dash-activity,.dash-notice{border-left:0;border-right:0;border-radius:0}
    .dash-chart-head{align-items:flex-start}
    .dash-tabs{width:100%;display:grid;grid-template-columns:repeat(3,1fr)}
    .dash-chart-stats{grid-template-columns:1fr}
    .dash-donut-grid{grid-template-columns:1fr}
    .dash-table{min-width:0}
    .dash-table thead{display:none}
    .dash-table,.dash-table tbody,.dash-table tr,.dash-table td{display:block;width:100%}
    .dash-table tr{padding:14px 16px;border-top:1px solid var(--border)}
    .dash-table tr:first-child{border-top:0}
    .dash-table td{border-top:0;padding:6px 0;white-space:normal;display:flex;justify-content:space-between;gap:12px;align-items:center}
    .dash-table td::before{content:attr(data-label);color:var(--muted2);font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;flex-shrink:0}
}
</style>
@endpush

@section('content')
@php
    $isMerchant = ($mode ?? 'client') === 'merchant';
    $roleLabel = $isMerchant ? 'Merchant' : 'Client';
    $name = $user->name ?? $roleLabel;
    $photo = $user->photo ? url('/api/storage/'.$user->photo) : null;

    $totalUsd = $latestRequests->sum('amount');
    $totalInr = $latestRequests->sum('total_amount');

    $approvedCount = $latestRequests->where('status', 'approved')->count();
    $pendingCount = $latestRequests->where('status', 'pending')->count();
    $rejectedCount = $latestRequests->where('status', 'rejected')->count();
    $closedCount = $latestRequests->where('status', 'closed')->count();
    $totalCount = $latestRequests->count();

    $totalSent = $latestTransfers->where('sender_id', $user->id)->sum('amount');
    $totalReceived = $latestTransfers->where('receiver_id', $user->id)->sum('amount');

    $monthlyIncome = [];
    $monthlyExpense = [];
    $monthLabels = [];

    for ($i = 5; $i >= 0; $i--) {
        $m = now()->subMonths($i);
        $monthLabels[] = $m->format('M Y');

        $monthlyIncome[] = round((float)$latestRequests->where('type', 'withdrawal')
            ->filter(fn($r) => $r->created_at && $r->created_at->month == $m->month && $r->created_at->year == $m->year)
            ->sum('amount'), 2);

        $monthlyExpense[] = round((float)$latestRequests->where('type', 'deposit')
            ->filter(fn($r) => $r->created_at && $r->created_at->month == $m->month && $r->created_at->year == $m->year)
            ->sum('amount'), 2);
    }

    $weeklyIncome = [];
    $weeklyExpense = [];
    $weekLabels = [];

    for ($i = 3; $i >= 0; $i--) {
        $start = now()->startOfWeek()->subWeeks($i);
        $end = (clone $start)->endOfWeek();
        $weekLabels[] = 'Wk '.$start->weekOfYear;

        $weeklyIncome[] = round((float)$latestRequests->where('type', 'withdrawal')
            ->filter(fn($r) => $r->created_at && $r->created_at->between($start, $end))
            ->sum('amount'), 2);

        $weeklyExpense[] = round((float)$latestRequests->where('type', 'deposit')
            ->filter(fn($r) => $r->created_at && $r->created_at->between($start, $end))
            ->sum('amount'), 2);
    }

    $dailyIncome = [];
    $dailyExpense = [];
    $dayLabels = [];

    for ($i = 6; $i >= 0; $i--) {
        $d = now()->subDays($i);
        $dayLabels[] = $d->format('D');

        $dailyIncome[] = round((float)$latestRequests->where('type', 'withdrawal')
            ->filter(fn($r) => $r->created_at && $r->created_at->isSameDay($d))
            ->sum('amount'), 2);

        $dailyExpense[] = round((float)$latestRequests->where('type', 'deposit')
            ->filter(fn($r) => $r->created_at && $r->created_at->isSameDay($d))
            ->sum('amount'), 2);
    }

    $buyCount = $latestRequests->where('type', 'deposit')->count();
    $sellCount = $latestRequests->where('type', 'withdrawal')->count();

    $requestRoute = $isMerchant ? route('merchant.requests') : route('client.requests');
    $transferRoute = $isMerchant ? route('merchant.transfers') : route('client.transfers');
    $historyRoute = $isMerchant ? route('merchant.history') : route('client.history');
@endphp

<div class="dash-page">
    <section class="dash-hero">
        <div class="dash-hero-content">
            <div class="dash-eyebrow">BITXNOW Wallet</div>

            <h1 class="dash-title">
                Welcome Back,
                <span>{{ $name }}</span>
            </h1>

          

        </div>
    </section>

    <div class="dash-wrap">
        <aside class="dash-side">
            <div class="dash-card dash-profile">
                <div class="dash-avatar">
                    @if($photo)
                        <img src="{{ $photo }}" alt="{{ $name }}">
                    @else
                        <div class="dash-avatar-inner">{{ strtoupper(substr($name, 0, 1)) }}</div>
                    @endif
                </div>

                <h3>{{ $name }}</h3>
                <div class="dash-role">{{ $roleLabel }} Account</div>

                <div class="dash-wallet">
                    <div>
                        <small>Wallet ID</small>
                        <span>{{ $user->wallet_id ?? '—' }}</span>
                    </div>

                    <button class="dash-copy" type="button" onclick="navigator.clipboard.writeText('{{ $user->wallet_id ?? '' }}');this.innerHTML='✓'">
                        <i class="ti ti-copy"></i>
                    </button>
                </div>

                @if($user->is_verified ?? false)
                    <div class="dash-badge ok"><i class="ti ti-circle-check"></i> Verified Account</div>
                @else
                    <div class="dash-badge pending"><i class="ti ti-alert-circle"></i> Verification Pending</div>
                @endif
            </div>

            <div class="dash-card">
                <div class="dash-section-title"><i class="ti ti-wallet"></i> Balance Sheet</div>

                <div class="dash-bs">
                    <div class="dash-bs-row"><span>Wallet Balance</span><strong>${{ number_format((float)($user->balance ?? 0), 2) }}</strong></div>
                    <div class="dash-bs-row"><span>Total Requests</span><strong>{{ $totalCount }}</strong></div>
                    <div class="dash-bs-row"><span>USD Requests</span><strong>${{ number_format((float)$totalUsd, 2) }}</strong></div>
                    <div class="dash-bs-row"><span>INR Volume</span><strong>₹{{ number_format((float)$totalInr, 2) }}</strong></div>
                    <div class="dash-bs-row"><span>Transfer Sent</span><strong style="color:var(--red)">-${{ number_format((float)$totalSent, 2) }}</strong></div>
                    <div class="dash-bs-row"><span>Transfer Received</span><strong style="color:var(--green)">+${{ number_format((float)$totalReceived, 2) }}</strong></div>
                </div>
            </div>
        </aside>

        <main class="dash-main">
            <section class="dash-kpis">
                <div class="dash-kpi">
                    <div class="dash-kpi-top"><div class="dash-kpi-icon"><i class="ti ti-wallet"></i></div><div class="dash-trend gray">Wallet</div></div>
                    <small>Wallet Balance</small>
                    <b>${{ number_format((float)($user->balance ?? 0), 2) }}</b>
                    <p>Available USD</p>
                </div>

                <div class="dash-kpi">
                    <div class="dash-kpi-top"><div class="dash-kpi-icon"><i class="ti ti-receipt"></i></div><div class="dash-trend gold">{{ $pendingCount }} Pending</div></div>
                    <small>Total Requests</small>
                    <b>{{ $totalCount }}</b>
                    <p>{{ $approvedCount }} approved requests</p>
                </div>

                <div class="dash-kpi">
                    <div class="dash-kpi-top"><div class="dash-kpi-icon"><i class="ti ti-trending-up"></i></div><div class="dash-trend green">Received</div></div>
                    <small>Transfer Received</small>
                    <b>+${{ number_format((float)$totalReceived, 2) }}</b>
                    <p>Total incoming transfers</p>
                </div>

                <div class="dash-kpi">
                    <div class="dash-kpi-top"><div class="dash-kpi-icon"><i class="ti ti-trending-down"></i></div><div class="dash-trend gray">Sent</div></div>
                    <small>Transfer Sent</small>
                    <b>-${{ number_format((float)$totalSent, 2) }}</b>
                    <p>Total outgoing transfers</p>
                </div>
            </section>

            <section class="dash-wallet-cards">
                <div class="dash-wcard">
                    <div class="dash-wcard-top"><div class="dash-wcard-type">USD Requests</div><div class="dash-wcard-pill">Buy / Sell</div></div>
                    <div class="dash-wcard-val">${{ number_format((float)$totalUsd, 2) }}</div>
                    <div class="dash-wcard-label">Total USD request volume</div>
                </div>

                <div class="dash-wcard">
                    <div class="dash-wcard-top"><div class="dash-wcard-type">INR Volume</div><div class="dash-wcard-pill">{{ $roleLabel }}</div></div>
                    <div class="dash-wcard-val">₹{{ number_format((float)$totalInr, 2) }}</div>
                    <div class="dash-wcard-label">Total INR request volume</div>
                </div>

                <div class="dash-wcard">
                    <div class="dash-wcard-top"><div class="dash-wcard-type">Status</div><div class="dash-wcard-pill">Active</div></div>
                    <div class="dash-wcard-val">{{ ($user->is_verified ?? false) ? 'Verified' : 'Pending' }}</div>
                    <div class="dash-wcard-label">Account verification status</div>
                </div>
            </section>

            <section class="dash-chart">
                <div class="dash-chart-head">
                    <h2><i class="ti ti-chart-line"></i> Funds Overview</h2>
                    <div class="dash-tabs">
                        <button type="button" class="dash-tab active" data-period="monthly">Monthly</button>
                        <button type="button" class="dash-tab" data-period="weekly">Weekly</button>
                        <button type="button" class="dash-tab" data-period="daily">Daily</button>
                    </div>
                </div>

                <div class="dash-chart-stats">
                    <div class="dash-chart-stat"><small>Sell USD</small><b id="dashTotalIncome" style="color:var(--red)">${{ number_format(array_sum($monthlyIncome), 2) }}</b></div>
                    <div class="dash-chart-stat"><small>Buy USD</small><b id="dashTotalExpense" style="color:var(--green)">${{ number_format(array_sum($monthlyExpense), 2) }}</b></div>
                    <div class="dash-chart-stat"><small>Net</small><b id="dashTotalNet">${{ number_format(array_sum($monthlyIncome) - array_sum($monthlyExpense), 2) }}</b></div>
                </div>

                <div class="dash-chart-box"><canvas id="dashFundsChart"></canvas></div>
            </section>

            <section class="dash-donut">
                <div class="dash-section-title" style="padding:0 0 16px;border-bottom:0;"><i class="ti ti-chart-donut"></i> Request Breakdown</div>

                <div class="dash-donut-grid">
                    <div class="dash-donut-wrap">
                        <canvas id="dashDonut" width="190" height="190"></canvas>
                        <div class="dash-donut-center"><b>{{ $totalCount }}</b><small>Total</small></div>
                    </div>

                    <div class="dash-legend">
                        <div class="dash-legend-row"><div class="dash-legend-key"><span class="dash-dot" style="background:#0ecb81"></span>Approved</div><strong style="color:var(--green)">{{ $approvedCount }}</strong></div>
                        <div class="dash-legend-row"><div class="dash-legend-key"><span class="dash-dot" style="background:#F0B90B"></span>Pending</div><strong style="color:var(--yellow)">{{ $pendingCount }}</strong></div>
                        <div class="dash-legend-row"><div class="dash-legend-key"><span class="dash-dot" style="background:#ef4444"></span>Rejected</div><strong style="color:#ff9b9b">{{ $rejectedCount }}</strong></div>
                        <div class="dash-legend-row"><div class="dash-legend-key"><span class="dash-dot" style="background:#5e6673"></span>Closed</div><strong>{{ $closedCount }}</strong></div>
                        <div class="dash-legend-row"><div class="dash-legend-key"><span class="dash-dot" style="background:#0ecb81"></span>Buy USD</div><strong>{{ $buyCount }}</strong></div>
                        <div class="dash-legend-row"><div class="dash-legend-key"><span class="dash-dot" style="background:#ef4444"></span>Sell USD</div><strong>{{ $sellCount }}</strong></div>
                    </div>
                </div>
            </section>

            

            @if(!($user->is_verified ?? false))
                <div class="dash-notice"><i class="ti ti-alert-triangle"></i><span><b>Complete your verification</b> — submit KYC documents to unlock full transaction limits.</span></div>
            @endif
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
(function () {
    const datasets = {
        monthly: { labels: @json($monthLabels), income: @json($monthlyIncome), expense: @json($monthlyExpense) },
        weekly: { labels: @json($weekLabels), income: @json($weeklyIncome), expense: @json($weeklyExpense) },
        daily: { labels: @json($dayLabels), income: @json($dailyIncome), expense: @json($dailyExpense) },
    };

    const fundsCanvas = document.getElementById('dashFundsChart');

    if (fundsCanvas && window.Chart) {
        const fundsChart = new Chart(fundsCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: datasets.monthly.labels,
                datasets: [
                    { label: 'Sell USD', data: datasets.monthly.income, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.12)', fill: true, tension: .42, borderWidth: 3, pointRadius: 4 },
                    { label: 'Buy USD', data: datasets.monthly.expense, borderColor: '#0ecb81', backgroundColor: 'rgba(14,203,129,.12)', fill: true, tension: .42, borderWidth: 3, pointRadius: 4, borderDash: [6, 4] }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#848E9C' } },
                    tooltip: { backgroundColor: '#1E2329', borderColor: '#2B3139', borderWidth: 1, titleColor: '#fff', bodyColor: '#848E9C' }
                },
                scales: {
                    x: { ticks: { color: '#848E9C' }, grid: { color: 'rgba(255,255,255,.05)' } },
                    y: { ticks: { color: '#848E9C', callback: value => '$' + value }, grid: { color: 'rgba(255,255,255,.05)' } }
                }
            }
        });

        const totalIncome = document.getElementById('dashTotalIncome');
        const totalExpense = document.getElementById('dashTotalExpense');
        const totalNet = document.getElementById('dashTotalNet');

        function sum(arr) { return arr.reduce((a, b) => a + Number(b || 0), 0); }
        function money(n) { return '$' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

        document.querySelectorAll('.dash-tab').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.dash-tab').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const d = datasets[btn.dataset.period];
                fundsChart.data.labels = d.labels;
                fundsChart.data.datasets[0].data = d.income;
                fundsChart.data.datasets[1].data = d.expense;
                fundsChart.update();

                const inc = sum(d.income);
                const exp = sum(d.expense);
                totalIncome.textContent = money(inc);
                totalExpense.textContent = money(exp);
                totalNet.textContent = money(inc - exp);
            });
        });
    }

    const donutCanvas = document.getElementById('dashDonut');

    if (donutCanvas && window.Chart) {
        new Chart(donutCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected', 'Closed'],
                datasets: [{
                    data: [{{ $approvedCount }}, {{ $pendingCount }}, {{ $rejectedCount }}, {{ $closedCount }}],
                    backgroundColor: ['#0ecb81', '#F0B90B', '#ef4444', '#5e6673'],
                    borderColor: '#181A20',
                    borderWidth: 4,
                }]
            },
            options: {
                responsive: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#1E2329', borderColor: '#2B3139', borderWidth: 1, titleColor: '#fff', bodyColor: '#848E9C' }
                }
            }
        });
    }
})();
</script>
@endpush

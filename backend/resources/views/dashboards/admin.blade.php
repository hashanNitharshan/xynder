@extends('layouts.admin', ['title' => 'Admin Dashboard'])

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
    --border-faint:#202020;

    --yellow:#F0B90B;
    --yellow-dark:#C99400;
    --gold:#FFD45A;

    --green:#0ecb81;
    --red:#ef4444;

    --text:#ffffff;
    --muted:#848E9C;
    --muted2:#5e6673;

    --shadow:0 18px 45px rgba(0,0,0,.35);
}
*{box-sizing:border-box}

.ad-page{
    margin:-28px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding:24px 36px 48px;
}
.ad-hero{position:relative;background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);border:1px solid var(--border);border-radius:16px;padding:18px 20px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:20px;}
.ad-hero::after{display:none}
.ad-hero-content{position:relative;z-index:2;max-width:680px;}
.ad-eyebrow{color:var(--yellow);font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;margin-bottom:5px;}
.ad-title{font-size:19px;line-height:1.2;font-weight:800;margin:0;color:#fff;letter-spacing:-.2px;}
.ad-title span{color:var(--yellow);display:inline}
.ad-subtitle{color:var(--muted);font-size:11.5px;line-height:1.6;font-weight:600;max-width:620px;margin-top:4px;}

.ad-wrap{position:relative;z-index:5;margin:0;}
.ad-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:28px;}
.ad-kpi{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:20px;min-height:118px;position:relative;overflow:hidden;}
.ad-kpi::after{content:"";position:absolute;right:-38px;top:-38px;width:115px;height:115px;border-radius:50%;background:rgba(240,185,11,.12);}
.ad-kpi-icon{width:42px;height:42px;border-radius:50%;background:rgba(240,185,11,.12);color:var(--yellow);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:14px;}
.ad-kpi-label{color:var(--muted);font-size:11.5px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;}
.ad-kpi-value{font-size:22px;font-weight:900;margin-top:6px;}
.ad-kpi-note{color:var(--muted2);font-size:11.5px;font-weight:800;margin-top:8px;}

.ad-grid-top{display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px;}
.ad-card{background:var(--surface);border:1px solid var(--border);border-radius:10px;overflow:hidden;}
.ad-card-head{min-height:58px;background:var(--surface-alt);border-bottom:1px solid var(--border);padding:0 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
.ad-card-title{display:flex;align-items:center;gap:9px;font-size:12.5px;font-weight:900;}
.ad-card-title i{color:var(--yellow)}
.ad-card-body{padding:20px}
.ad-chart{width:100%;height:315px;}

.ad-mid{display:grid;grid-template-columns:390px 1fr;gap:24px;margin-bottom:24px;}
.ad-donut-wrap{height:315px;display:flex;align-items:center;justify-content:center;}
.ad-total-row{border-top:1px solid var(--border);padding:16px 20px;display:flex;justify-content:space-between;align-items:center;}

.ad-mini-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;}
.ad-mini{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:18px;min-height:115px;position:relative;overflow:hidden;}
.ad-mini-label{color:var(--muted);font-size:12.5px;font-weight:800;}
.ad-mini-value{color:#fff;font-size:22px;font-weight:900;margin-top:6px;}
.ad-mini-status{position:absolute;right:16px;bottom:14px;font-size:11.5px;font-weight:900;}

.good{color:var(--green)}
.warn{color:var(--gold)}
.danger{color:#ff9b9b}

.spark{position:absolute;left:14px;bottom:0;height:38px;display:flex;align-items:flex-end;gap:3px;color:var(--yellow);}
.spark span{width:3px;border-radius:3px 3px 0 0;background:currentColor;opacity:.9;}

.badge{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:900;text-transform:uppercase;}
.badge.rejected{background:rgba(240,185,11,.12);color:#ff9b9b}

@media(max-width:1200px){
    .ad-kpis{grid-template-columns:repeat(2,1fr)}
    .ad-grid-top,.ad-mid{grid-template-columns:1fr}
}

@media(max-width:760px){
    .ad-page{margin:-18px;padding:16px 14px 30px}
    .ad-hero{position:relative;background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);border:1px solid var(--border);border-radius:16px;padding:18px 20px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:20px;}
    .ad-title{font-size:36px}
    .ad-wrap{margin:0}
    .ad-kpis,.ad-mini-grid{grid-template-columns:1fr}
    .ad-chart{height:260px}
}
</style>
@endpush

@section('content')
@php
    $clients = (int)($stats['clients'] ?? 0);
    $merchants = (int)($stats['merchants'] ?? 0);
    $online = (int)($stats['online'] ?? 0);
    $pending = (int)($stats['pending'] ?? 0);
    $approved = (int)($stats['approved'] ?? 0);
    $rejected = (int)($stats['rejected'] ?? 0);
    $volume = (float)($stats['total_volume'] ?? 0);
    $totalUsers = $clients + $merchants;
    $bars = [9,7,14,10,8,13,11,6,12,9,15,8];
@endphp

<div class="ad-page">

    <section class="ad-hero">
        <div class="ad-hero-content">
            <div class="ad-eyebrow">Xynder Wallet Admin</div>

            <h1 class="ad-title">
                Admin Control
                <span>Dashboard</span>
            </h1>

            <div class="ad-subtitle">
                Manage users, merchants, wallet buy/sell requests, online activity,
                approval status, and total approved INR volume from one secure panel.
            </div>
        </div>
    </section>

    <main class="ad-wrap">

        <section class="ad-kpis">
            <div class="ad-kpi">
                <div class="ad-kpi-icon"><i class="ti ti-users"></i></div>
                <div class="ad-kpi-label">Total Users</div>
                <div class="ad-kpi-value">{{ $totalUsers }}</div>
                <div class="ad-kpi-note">Clients + merchants</div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon"><i class="ti ti-user-check"></i></div>
                <div class="ad-kpi-label">Online Users</div>
                <div class="ad-kpi-value">{{ $online }}</div>
                <div class="ad-kpi-note">Currently active</div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon"><i class="ti ti-clock"></i></div>
                <div class="ad-kpi-label">Pending Requests</div>
                <div class="ad-kpi-value">{{ $pending }}</div>
                <div class="ad-kpi-note">Need admin action</div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon"><i class="ti ti-cash"></i></div>
                <div class="ad-kpi-label">Approved Volume</div>
                <div class="ad-kpi-value">₹{{ number_format($volume, 0) }}</div>
                <div class="ad-kpi-note">Total approved INR</div>
            </div>
        </section>

        <section class="ad-grid-top">
            <div class="ad-card">
                <div class="ad-card-head">
                    <div class="ad-card-title">
                        <i class="ti ti-chart-line"></i>
                        Wallet Request Overview
                    </div>
                </div>
                <div class="ad-card-body">
                    <canvas id="walletOverview" class="ad-chart"></canvas>
                </div>
            </div>

            <div class="ad-card">
                <div class="ad-card-head">
                    <div class="ad-card-title">
                        <i class="ti ti-chart-bar"></i>
                        Request Status
                    </div>
                </div>
                <div class="ad-card-body">
                    <canvas id="requestStatus" class="ad-chart"></canvas>
                </div>
            </div>
        </section>

        <section class="ad-mid">
            <div class="ad-card">
                <div class="ad-card-head">
                    <div class="ad-card-title">
                        <i class="ti ti-chart-donut"></i>
                        User Split
                    </div>
                </div>

                <div class="ad-donut-wrap">
                    <canvas id="donutChart" width="300" height="300"></canvas>
                </div>

                <div class="ad-total-row">
                    <b>Total Users</b>
                    <span class="badge rejected">{{ $totalUsers }}</span>
                </div>
            </div>

            <div class="ad-mini-grid">
                <div class="ad-mini">
                    <div class="ad-mini-label">Total Clients</div>
                    <div class="ad-mini-value">{{ $clients }}</div>
                    <div class="spark">@foreach($bars as $h)<span style="height:{{ $h*3 }}px"></span>@endforeach</div>
                    <div class="ad-mini-status good">Active</div>
                </div>

                <div class="ad-mini">
                    <div class="ad-mini-label">Total Merchants</div>
                    <div class="ad-mini-value">{{ $merchants }}</div>
                    <div class="spark">@foreach($bars as $h)<span style="height:{{ ($h+2)*3 }}px"></span>@endforeach</div>
                    <div class="ad-mini-status good">Active</div>
                </div>

                <div class="ad-mini">
                    <div class="ad-mini-label">Online Users</div>
                    <div class="ad-mini-value">{{ $online }}</div>
                    <div class="spark">@foreach($bars as $h)<span style="height:{{ ($h+1)*3 }}px"></span>@endforeach</div>
                    <div class="ad-mini-status good">Live</div>
                </div>

                <div class="ad-mini">
                    <div class="ad-mini-label">Pending Requests</div>
                    <div class="ad-mini-value">{{ $pending }}</div>
                    <div class="spark">@foreach($bars as $h)<span style="height:{{ $h*3 }}px"></span>@endforeach</div>
                    <div class="ad-mini-status warn">Need Action</div>
                </div>

                <div class="ad-mini">
                    <div class="ad-mini-label">Approved Requests</div>
                    <div class="ad-mini-value">{{ $approved }}</div>
                    <div class="spark">@foreach($bars as $h)<span style="height:{{ ($h+3)*3 }}px"></span>@endforeach</div>
                    <div class="ad-mini-status good">Done</div>
                </div>

                <div class="ad-mini">
                    <div class="ad-mini-label">Rejected Requests</div>
                    <div class="ad-mini-value">{{ $rejected }}</div>
                    <div class="spark">@foreach($bars as $h)<span style="height:{{ ($h+1)*3 }}px"></span>@endforeach</div>
                    <div class="ad-mini-status danger">Rejected</div>
                </div>
            </div>
        </section>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const gridColor = 'rgba(255,255,255,.06)';
const textColor = '#aeb4ba';

new Chart(document.getElementById('walletOverview'), {
    type: 'line',
    data: {
        labels: @json($requestLabels ?? []),
        datasets: [
            {
                label: 'Buy USD',
                data: @json($buyUsdData ?? []),
                borderColor: '#0ecb81',
                backgroundColor: 'rgba(14,203,129,.15)',
                fill: true,
                tension: .42,
                borderWidth: 3,
                pointRadius: 0
            },
            {
                label: 'Sell USD',
                data: @json($sellUsdData ?? []),
                borderColor: '#F0B90B',
                backgroundColor: 'rgba(240,185,11,.18)',
                fill: true,
                tension: .42,
                borderWidth: 3,
                pointRadius: 0
            }
        ]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{labels:{color:textColor,font:{weight:'bold'}}}
        },
        scales:{
            x:{grid:{color:gridColor},ticks:{color:textColor}},
            y:{grid:{color:gridColor},ticks:{color:textColor},beginAtZero:true}
        }
    }
});

new Chart(document.getElementById('requestStatus'), {
    type: 'bar',
    data: {
        labels: @json($statusLabels ?? ['Pending','Approved','Rejected']),
        datasets: [{
            data: @json($statusData ?? [$pending,$approved,$rejected]),
            backgroundColor: ['#ffc933','#0ecb81','#F0B90B'],
            borderColor: '#2b2f32',
            borderWidth: 2,
            borderRadius: 6,
            barThickness: 34
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{legend:{display:false}},
        scales:{
            x:{grid:{color:gridColor},ticks:{color:textColor}},
            y:{grid:{color:gridColor},ticks:{color:textColor},beginAtZero:true}
        }
    }
});

new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: ['Clients','Merchants','Online'],
        datasets: [{
            data: [{{ $clients }}, {{ $merchants }}, {{ $online }}],
            backgroundColor: ['#F0B90B','#FFD45A','#0ecb81'],
            borderColor: '#2b2f32',
            borderWidth: 4,
            cutout: '64%'
        }]
    },
    options: {
        plugins:{
            legend:{
                position:'bottom',
                labels:{color:textColor,font:{weight:'bold'}}
            }
        }
    },
    plugins: [{
        id:'centerText',
        afterDraw(chart){
            const {ctx, chartArea:{left,right,top,bottom}} = chart;
            ctx.save();
            ctx.fillStyle = '#fff';
            ctx.font = '900 28px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Users', (left+right)/2, (top+bottom)/2 - 8);
            ctx.fillStyle = '#F0B90B';
            ctx.font = '900 24px Arial';
            ctx.fillText('{{ $totalUsers }}', (left+right)/2, (top+bottom)/2 + 24);
            ctx.restore();
        }
    }]
});
</script>
@endsection
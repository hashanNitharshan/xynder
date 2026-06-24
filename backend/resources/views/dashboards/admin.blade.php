@extends('layouts.admin', ['title' => 'Admin Dashboard'])

@section('content')
<style>
    .rd-grid-top{display:grid;grid-template-columns:2fr 1fr;gap:26px;margin-bottom:26px}
    .rd-card{background:#10181c;border:1px solid rgba(255,255,255,.08);border-radius:10px;overflow:hidden}
    .rd-head{height:54px;display:flex;align-items:center;justify-content:space-between;padding:0 18px;border-bottom:1px solid rgba(255,255,255,.08);font-size:18px;font-weight:900}
    .rd-body{padding:18px}
    .rd-chart{width:100%;height:330px}
    .rd-mid{display:grid;grid-template-columns:420px 1fr;gap:26px;margin-bottom:26px}
    .rd-donut-wrap{display:flex;align-items:center;justify-content:center;height:300px}
    .rd-metrics{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
    .rd-mini{min-height:118px;background:#10181c;border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:18px;position:relative;overflow:hidden}
    .rd-mini-title{color:#b3bec4;font-size:15px;margin-bottom:4px}
    .rd-mini-value{font-size:24px;font-weight:900;color:#fff}
    .rd-mini-change{position:absolute;right:16px;bottom:16px;font-size:15px;font-weight:900}
    .up{color:#16e044}.down{color:#ff3158}
    .spark{position:absolute;left:14px;bottom:0;height:42px;display:flex;align-items:flex-end;gap:3px}
    .spark span{width:3px;border-radius:2px 2px 0 0;background:currentColor;opacity:.95}
    .blue{color:#0d8cff}.red{color:#ff3158}.green{color:#16e044}.yellow{color:#ffd21f}.orange{color:#ff9800}.cyan{color:#00c8ff}
    .rd-bottom{display:grid;grid-template-columns:1fr 1fr;gap:26px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:13px 14px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left;font-size:13px}
    th{color:#8c989f;text-transform:uppercase;font-size:11px;letter-spacing:.05em}
    td{color:#cbd5da}
    .name{font-weight:900;color:#fff}.small{font-size:11px;color:#87939a;margin-top:2px}
    .badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:900}
    .b-green{background:rgba(22,224,68,.12);color:#75ff8e}
    .b-red{background:rgba(255,49,88,.12);color:#ff8ba1}
    .b-yellow{background:rgba(255,210,31,.12);color:#ffe16d}
    .b-blue{background:rgba(13,140,255,.13);color:#6ab8ff}
    .b-gray{background:rgba(255,255,255,.08);color:#cbd5da}
    .action-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:26px}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:6px;font-weight:900;font-size:13px;color:#fff}
    .btn-blue{background:#0d8cff}.btn-green{background:#16a34a}.btn-red{background:#ef3158}.btn-yellow{background:#f59e0b;color:#111}
    @media(max-width:1200px){.rd-grid-top,.rd-mid,.rd-bottom{grid-template-columns:1fr}.rd-metrics{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:650px){.rd-metrics{grid-template-columns:1fr}.rd-chart{height:260px}}
</style>

@php
    $clients = (int)($stats['clients'] ?? 0);
    $merchants = (int)($stats['merchants'] ?? 0);
    $online = (int)($stats['online'] ?? 0);
    $pending = (int)($stats['pending'] ?? 0);
    $approved = (int)($stats['approved'] ?? 0);
    $rejected = (int)($stats['rejected'] ?? 0);
    $volume = (float)($stats['total_volume'] ?? 0);
    $bars = [9,7,14,10,8,13,11,6,12,9,15,8];
@endphp

<div class="rd-grid-top">
    <div class="rd-card">
        <div class="rd-head">
            <span>Wallet Request Overview</span>
            <span>•••</span>
        </div>
        <div class="rd-body">
            <canvas id="walletOverview" class="rd-chart"></canvas>
        </div>
    </div>

    <div class="rd-card">
        <div class="rd-head">
            <span>Request Status</span>
            <span>•••</span>
        </div>
        <div class="rd-body">
            <canvas id="requestStatus" class="rd-chart"></canvas>
        </div>
    </div>
</div>

<div class="action-row">
    <a class="btn btn-yellow" href="{{ route('admin.users.create') }}">＋ Add User</a>
    <a class="btn btn-blue" href="{{ route('admin.users.index') }}">👥 Manage Users</a>
    <a class="btn btn-green" href="{{ route('admin.wallet-requests.index') }}">✅ Requests</a>
    <a class="btn btn-red" href="{{ route('admin.config.index') }}">⚙ Config</a>
</div>

<div class="rd-mid">
    <div class="rd-card">
        <div class="rd-donut-wrap">
            <canvas id="donutChart" width="310" height="310"></canvas>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,.08);padding:14px 18px;display:flex;justify-content:space-between;">
            <b>Total Users</b>
            <span class="badge b-red">{{ $clients + $merchants }}</span>
        </div>
    </div>

    <div class="rd-metrics">
        <div class="rd-mini">
            <div class="rd-mini-title">Total Clients</div>
            <div class="rd-mini-value">{{ $clients }}</div>
            <div class="spark blue">@foreach($bars as $h)<span style="height:{{ $h*3 }}px"></span>@endforeach</div>
            <div class="rd-mini-change up">Active</div>
        </div>

        <div class="rd-mini">
            <div class="rd-mini-title">Total Merchants</div>
            <div class="rd-mini-value">{{ $merchants }}</div>
            <div class="spark red">@foreach($bars as $h)<span style="height:{{ ($h+2)*3 }}px"></span>@endforeach</div>
            <div class="rd-mini-change up">Active</div>
        </div>

        <div class="rd-mini">
            <div class="rd-mini-title">Online Users</div>
            <div class="rd-mini-value">{{ $online }}</div>
            <div class="spark green">@foreach($bars as $h)<span style="height:{{ ($h+1)*3 }}px"></span>@endforeach</div>
            <div class="rd-mini-change up">Live</div>
        </div>

        <div class="rd-mini">
            <div class="rd-mini-title">Pending Requests</div>
            <div class="rd-mini-value">{{ $pending }}</div>
            <div class="spark orange">@foreach($bars as $h)<span style="height:{{ $h*3 }}px"></span>@endforeach</div>
            <div class="rd-mini-change down">Need Action</div>
        </div>

        <div class="rd-mini">
            <div class="rd-mini-title">Approved Requests</div>
            <div class="rd-mini-value">{{ $approved }}</div>
            <div class="spark cyan">@foreach($bars as $h)<span style="height:{{ ($h+3)*3 }}px"></span>@endforeach</div>
            <div class="rd-mini-change up">Done</div>
        </div>

        <div class="rd-mini">
            <div class="rd-mini-title">Approved INR Volume</div>
            <div class="rd-mini-value">₹{{ number_format($volume, 0) }}</div>
            <div class="spark yellow">@foreach($bars as $h)<span style="height:{{ ($h+1)*3 }}px"></span>@endforeach</div>
            <div class="rd-mini-change up">Volume</div>
        </div>
    </div>
</div>

<div class="rd-bottom">
    <div class="rd-card">
        <div class="rd-head">
            <span>Latest Wallet Requests</span>
            <a href="{{ route('admin.wallet-requests.index') }}" style="font-size:13px;color:#0d8cff;">View All</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($latestRequests as $request)
                <tr>
                    <td>
                        <div class="name">{{ $request->user->name ?? 'Deleted User' }}</div>
                        <div class="small">{{ $request->user->email ?? '-' }}</div>
                    </td>
                    <td>
                        @if($request->type === 'deposit')
                            <span class="badge b-green">Buy USD</span>
                        @elseif($request->type === 'withdrawal')
                            <span class="badge b-red">Sell USD</span>
                        @else
                            <span class="badge b-blue">{{ ucfirst($request->type) }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="name">$ {{ number_format($request->amount, 2) }}</div>
                        <div class="small">INR {{ number_format($request->total_amount ?? 0, 2) }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $request->status === 'approved' ? 'b-green' : ($request->status === 'pending' ? 'b-yellow' : 'b-red') }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#87939a;">No wallet requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="rd-card">
        <div class="rd-head">
            <span>Latest Users</span>
            <a href="{{ route('admin.users.index') }}" style="font-size:13px;color:#0d8cff;">View All</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($latestUsers as $user)
                <tr>
                    <td>
                        <div class="name">{{ $user->name }}</div>
                        <div class="small">{{ $user->email }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $user->role === 'merchant' ? 'b-yellow' : 'b-gray' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ ($user->status ?? 'active') === 'active' ? 'b-green' : 'b-red' }}">
                            {{ ($user->status ?? 'active') === 'active' ? 'Active' : 'Blocked' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:#87939a;">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const gridColor = 'rgba(255,255,255,.035)';
const textColor = '#6f7b82';

new Chart(document.getElementById('walletOverview'), {
    type: 'line',
    data: {
        labels: @json($requestLabels),
        datasets: [
            {
                label: 'Buy USD',
                data: @json($buyUsdData),
                borderColor: '#0d8cff',
                backgroundColor: 'rgba(13,140,255,.26)',
                fill: true,
                tension: .45,
                borderWidth: 3,
                pointRadius: 0
            },
            {
                label: 'Sell USD',
                data: @json($sellUsdData),
                borderColor: '#ff3158',
                backgroundColor: 'rgba(255,49,88,.18)',
                fill: true,
                tension: .45,
                borderWidth: 3,
                pointRadius: 0
            }
        ]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{legend:{labels:{color:textColor}}},
        scales:{
            x:{grid:{color:gridColor},ticks:{color:textColor}},
            y:{grid:{color:gridColor},ticks:{color:textColor}}
        }
    }
});

new Chart(document.getElementById('requestStatus'), {
    type: 'bar',
    data: {
        labels: @json($statusLabels),
        datasets: [{
            data: @json($statusData),
            backgroundColor: ['#ffd21f','#16e044','#ff3158'],
            borderColor: '#ffffff',
            borderWidth: 2,
            borderRadius: 8,
            barThickness: 32
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
            backgroundColor: ['#16e044','#0d8cff','#ff3158'],
            borderColor: '#ffffff',
            borderWidth: 3,
            cutout: '63%'
        }]
    },
    options: {
        plugins:{legend:{display:false}}
    },
    plugins: [{
        id:'centerText',
        afterDraw(chart){
            const {ctx, chartArea:{left,right,top,bottom}} = chart;
            ctx.save();
            ctx.fillStyle = '#000';
            ctx.font = '900 30px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Users', (left+right)/2, (top+bottom)/2 - 5);
            ctx.font = '400 22px Arial';
            ctx.fillText('{{ $clients + $merchants }}', (left+right)/2, (top+bottom)/2 + 27);
            ctx.restore();
        }
    }]
});
</script>
@endsection
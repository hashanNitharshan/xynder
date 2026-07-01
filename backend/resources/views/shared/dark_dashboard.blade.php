@php
    $isMerchant = ($mode ?? 'client') === 'merchant';
    $roleLabel = $isMerchant ? 'Merchant' : 'Client';
    $name = $user->name ?? $roleLabel;

    $totalUsd = $latestRequests->sum('amount');
    $totalInr = $latestRequests->sum('total_amount');

    $approvedCount = $latestRequests->where('status', 'approved')->count();
    $pendingCount = $latestRequests->where('status', 'pending')->count();
    $rejectedCount = $latestRequests->where('status', 'rejected')->count();
    $totalCount = $latestRequests->count();

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
@endphp

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

.dash-page{margin:-24px;min-height:100vh;background:var(--dark);color:var(--text);font-family:Inter,Arial,sans-serif;padding-bottom:60px}

.dash-hero{position:relative;min-height:300px;padding:58px 85px 105px;background:var(--hero);overflow:hidden}
.dash-hero::after{content:"";position:absolute;inset:0;opacity:.08;background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);background-size:260px 260px}
.dash-hero-content{position:relative;z-index:2;max-width:680px}
.dash-eyebrow{color:var(--red);font-size:12px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;margin-bottom:14px}
.dash-title{font-size:46px;line-height:1.15;font-weight:900;margin:0 0 16px}
.dash-title span{color:var(--red)}
.dash-sub{color:#b8bdc2;font-size:15px;line-height:1.7;font-weight:700}

.dash-wrap{position:relative;z-index:5;margin:-65px 85px 0;display:grid;grid-template-columns:300px 1fr;gap:22px;align-items:start}
.dash-side{display:flex;flex-direction:column;gap:18px}
.dash-main{min-width:0;display:flex;flex-direction:column;gap:22px}

.dash-card{background:var(--box);border:1px solid var(--line);border-radius:6px;overflow:hidden}
.dash-profile{border:1.5px solid var(--red);box-shadow:0 18px 40px rgba(0,0,0,.28);padding:26px 22px;text-align:center}

.dash-avatar{width:82px;height:82px;margin:0 auto 14px;border-radius:50%;background:var(--red);padding:4px}
.dash-avatar img,.dash-avatar-inner{width:100%;height:100%;border-radius:50%;object-fit:cover}
.dash-avatar-inner{background:#1f2428;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:900;color:#fff}

.dash-profile h3{font-size:20px;font-weight:900;margin:0 0 5px;color:#fff}
.dash-role{color:#9fa5aa;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}

.dash-wallet{margin-top:15px;background:#1f2428;border:1px solid var(--line);border-radius:4px;padding:12px;display:flex;align-items:center;justify-content:space-between;gap:10px}
.dash-wallet small{display:block;color:#747b82;font-size:10px;font-weight:900;text-transform:uppercase;margin-bottom:3px}
.dash-wallet span{font-family:monospace;color:#fff;font-size:13px}
.dash-copy{border:0;background:var(--red);color:#fff;border-radius:4px;padding:7px 10px;cursor:pointer;font-weight:900}

.dash-badge{display:inline-flex;align-items:center;gap:6px;margin-top:13px;padding:6px 12px;border-radius:20px;font-size:11px;font-weight:900}
.dash-badge.ok{background:#0d2b1e;color:var(--green)}
.dash-badge.pending{background:#3b2a09;color:var(--gold)}

.dash-section-title{padding:18px 20px;border-bottom:1px solid var(--line);font-size:15px;font-weight:900;display:flex;align-items:center;gap:9px}
.dash-section-title i{color:var(--red)}

.dash-bs{padding:8px 20px 18px}
.dash-bs-row{display:flex;justify-content:space-between;gap:14px;padding:13px 0;border-bottom:1px solid var(--line)}
.dash-bs-row:last-child{border-bottom:0}
.dash-bs-row span{color:#9fa5aa;font-size:13px;font-weight:800}
.dash-bs-row strong{color:#fff;font-size:13px;font-weight:900;text-align:right}

.dash-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.dash-kpi{background:var(--box);border:1px solid var(--line);border-radius:6px;padding:20px;transition:.15s}
.dash-kpi:hover{border-color:var(--red);background:#30363a}
.dash-kpi-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px}
.dash-kpi-icon{width:44px;height:44px;border-radius:50%;background:#3a1018;color:var(--red);display:flex;align-items:center;justify-content:center;font-size:22px}
.dash-trend{padding:5px 9px;border-radius:20px;font-size:11px;font-weight:900}
.dash-trend.green{background:#0d2b1e;color:var(--green)}
.dash-trend.gold{background:#3b2a09;color:var(--gold)}
.dash-trend.gray{background:#1f2428;color:#9fa5aa}
.dash-kpi small{color:#9fa5aa;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}
.dash-kpi b{display:block;margin-top:7px;font-size:27px;font-weight:900}
.dash-kpi p{margin-top:6px;color:#747b82;font-size:12px;font-weight:700}

.dash-wallet-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.dash-wcard{min-height:140px;border-radius:6px;padding:22px;background:var(--box);border:1px solid var(--line);position:relative;overflow:hidden}
.dash-wcard::after{content:"";position:absolute;right:-50px;top:-50px;width:150px;height:150px;border-radius:50%;background:rgba(232,25,44,.14)}
.dash-wcard-top{position:relative;z-index:2;display:flex;justify-content:space-between;gap:10px;margin-bottom:18px}
.dash-wcard-type{color:#9fa5aa;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}
.dash-wcard-pill{background:#3a1018;color:#ff6b7b;border-radius:20px;padding:4px 9px;font-size:10px;font-weight:900}
.dash-wcard-val{position:relative;z-index:2;font-size:25px;font-weight:900;color:#fff}
.dash-wcard-label{position:relative;z-index:2;color:#aeb4ba;margin-top:5px;font-size:12px;font-weight:700}

.dash-chart{background:var(--box);border:1px solid var(--line);border-radius:6px;padding:24px}
.dash-chart-head{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:18px}
.dash-chart-head h2{font-size:18px;font-weight:900;margin:0}
.dash-tabs{display:flex;background:#1f2428;border:1px solid var(--line);border-radius:4px;padding:4px}
.dash-tab{border:0;background:transparent;color:#9fa5aa;padding:8px 15px;border-radius:3px;font-size:12px;font-weight:900;cursor:pointer}
.dash-tab.active{background:var(--red);color:#fff}

.dash-chart-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px}
.dash-chart-stat{background:#1f2428;border:1px solid var(--line);border-radius:4px;padding:14px}
.dash-chart-stat small{color:#9fa5aa;font-size:11px;font-weight:900;text-transform:uppercase}
.dash-chart-stat b{display:block;margin-top:6px;font-size:20px;font-weight:900}
.dash-chart-box{height:250px;position:relative}

.dash-bottom{display:grid;grid-template-columns:1fr;gap:18px}
.dash-donut{background:var(--box);border:1px solid var(--line);border-radius:6px;padding:22px}
.dash-donut-grid{display:grid;grid-template-columns:260px 1fr;gap:20px;align-items:center}
.dash-donut-wrap{position:relative;display:flex;justify-content:center;margin:14px 0}
.dash-donut-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center}
.dash-donut-center b{font-size:24px;font-weight:900}
.dash-donut-center small{display:block;color:#9fa5aa;font-size:11px;font-weight:900}
.dash-legend{display:flex;flex-direction:column;gap:12px}
.dash-legend-row{display:flex;justify-content:space-between;gap:10px;color:#aeb4ba;font-size:13px;font-weight:800;background:#1f2428;border:1px solid var(--line);border-radius:4px;padding:12px 14px}
.dash-legend-key{display:flex;align-items:center;gap:8px}
.dash-dot{width:10px;height:10px;border-radius:50%}

.dash-notice{background:#3b2a09;border:1px solid rgba(255,201,51,.35);border-radius:6px;color:#ffe7a3;padding:15px 18px;font-weight:800;display:flex;align-items:center;gap:10px}

@media(max-width:1200px){
    .dash-wrap{grid-template-columns:1fr}
    .dash-side{display:grid;grid-template-columns:repeat(2,1fr)}
    .dash-kpis{grid-template-columns:repeat(2,1fr)}
}

@media(max-width:800px){
    .dash-page{margin:-16px}
    .dash-hero{padding:45px 24px 110px}
    .dash-title{font-size:36px}
    .dash-wrap{margin-left:20px;margin-right:20px}
    .dash-side{grid-template-columns:1fr}
    .dash-kpis{grid-template-columns:1fr}
    .dash-wallet-cards{grid-template-columns:1fr}
    .dash-chart-head{flex-direction:column;align-items:flex-start}
    .dash-chart-stats{grid-template-columns:1fr}
    .dash-donut-grid{grid-template-columns:1fr}
}
</style>

<div class="dash-page">

    <section class="dash-hero">
        <div class="dash-hero-content">
            <div class="dash-eyebrow">Xynder Wallet</div>

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
                    <div class="dash-avatar-inner">{{ strtoupper(substr($name, 0, 1)) }}</div>
                </div>

                <h3>{{ $name }}</h3>
                <div class="dash-role">{{ $roleLabel }} Account</div>

                <div class="dash-wallet">
                    <div>
                        <small>Wallet ID</small>
                        <span>{{ $user->wallet_id ?? 'WXXX-XXXX-XXXX' }}</span>
                    </div>

                    <button class="dash-copy"
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $user->wallet_id ?? '' }}');this.innerHTML='✓'">
                        <i class="ti ti-copy"></i>
                    </button>
                </div>

                @if($user->is_verified ?? false)
                    <div class="dash-badge ok">
                        <i class="ti ti-circle-check"></i>
                        Verified Account
                    </div>
                @else
                    <div class="dash-badge pending">
                        <i class="ti ti-alert-circle"></i>
                        Verification Pending
                    </div>
                @endif
            </div>

            <div class="dash-card">
                <div class="dash-section-title">
                    <i class="ti ti-wallet"></i>
                    Balance Sheet
                </div>

                <div class="dash-bs">
                    <div class="dash-bs-row">
                        <span>Wallet Balance</span>
                        <strong>₹{{ number_format((float)($user->balance ?? 0), 2) }}</strong>
                    </div>

                    <div class="dash-bs-row">
                        <span>Total Requests</span>
                        <strong>{{ $totalCount }}</strong>
                    </div>

                    <div class="dash-bs-row">
                        <span>USD Account</span>
                        <strong>${{ number_format((float)$totalUsd, 2) }}</strong>
                    </div>

                    <div class="dash-bs-row">
                        <span>INR Volume</span>
                        <strong>₹{{ number_format((float)$totalInr, 2) }}</strong>
                    </div>

                    <div class="dash-bs-row">
                        <span>Approved</span>
                        <strong style="color:var(--green)">{{ $approvedCount }}</strong>
                    </div>

                    <div class="dash-bs-row">
                        <span>Pending</span>
                        <strong style="color:var(--gold)">{{ $pendingCount }}</strong>
                    </div>
                </div>
            </div>

        </aside>

        <main class="dash-main">

            <section class="dash-kpis">
                <div class="dash-kpi">
                    <div class="dash-kpi-top">
                        <div class="dash-kpi-icon"><i class="ti ti-wallet"></i></div>
                        <div class="dash-trend gray">Wallet</div>
                    </div>
                    <small>Wallet Balance</small>
                    <b>₹{{ number_format((float)($user->balance ?? 0), 0) }}</b>
                    <p>Available funds</p>
                </div>

                <div class="dash-kpi">
                    <div class="dash-kpi-top">
                        <div class="dash-kpi-icon"><i class="ti ti-receipt"></i></div>
                        <div class="dash-trend gray">All Time</div>
                    </div>
                    <small>Total Requests</small>
                    <b>{{ $totalCount }}</b>
                    <p>{{ $approvedCount }} approved requests</p>
                </div>

                <div class="dash-kpi">
                    <div class="dash-kpi-top">
                        <div class="dash-kpi-icon"><i class="ti ti-currency-dollar"></i></div>
                        <div class="dash-trend green">USD</div>
                    </div>
                    <small>USD Account</small>
                    <b>${{ number_format((float)$totalUsd, 2) }}</b>
                    <p>Total USD transacted</p>
                </div>

                <div class="dash-kpi">
                    <div class="dash-kpi-top">
                        <div class="dash-kpi-icon"><i class="ti ti-currency-rupee"></i></div>
                        <div class="dash-trend {{ $pendingCount > 0 ? 'gold' : 'green' }}">
                            {{ $pendingCount > 0 ? $pendingCount.' Pending' : 'Clear' }}
                        </div>
                    </div>
                    <small>INR Volume</small>
                    <b>₹{{ number_format((float)$totalInr, 0) }}</b>
                    <p>Total INR volume</p>
                </div>
            </section>

            <section class="dash-wallet-cards">
                <div class="dash-wcard">
                    <div class="dash-wcard-top">
                        <div class="dash-wcard-type">Wallet Balance</div>
                        <div class="dash-wcard-pill">Active</div>
                    </div>
                    <div class="dash-wcard-val">₹{{ number_format((float)($user->balance ?? 0), 2) }}</div>
                    <div class="dash-wcard-label">Available balance</div>
                </div>

                <div class="dash-wcard">
                    <div class="dash-wcard-top">
                        <div class="dash-wcard-type">USD Account</div>
                        <div class="dash-wcard-pill">{{ ($user->is_verified ?? false) ? 'Verified' : 'Pending' }}</div>
                    </div>
                    <div class="dash-wcard-val">${{ number_format((float)$totalUsd, 2) }}</div>
                    <div class="dash-wcard-label">Total USD transacted</div>
                </div>

                <div class="dash-wcard">
                    <div class="dash-wcard-top">
                        <div class="dash-wcard-type">INR Volume</div>
                        <div class="dash-wcard-pill">{{ $roleLabel }}</div>
                    </div>
                    <div class="dash-wcard-val">₹{{ number_format((float)$totalInr, 2) }}</div>
                    <div class="dash-wcard-label">{{ $approvedCount }} approved requests</div>
                </div>
            </section>

            <section class="dash-chart">
                <div class="dash-chart-head">
                    <h2>Funds Overview</h2>

                    <div class="dash-tabs">
                        <button type="button" class="dash-tab active" data-period="monthly">Monthly</button>
                        <button type="button" class="dash-tab" data-period="weekly">Weekly</button>
                        <button type="button" class="dash-tab" data-period="daily">Daily</button>
                    </div>
                </div>

                <div class="dash-chart-stats">
                    <div class="dash-chart-stat">
                        <small>Sell USD Income</small>
                        <b id="dashTotalIncome" style="color:var(--green)">
                            ${{ number_format(array_sum($monthlyIncome), 2) }}
                        </b>
                    </div>

                    <div class="dash-chart-stat">
                        <small>Buy USD Expense</small>
                        <b id="dashTotalExpense" style="color:#ff6b7b">
                            ${{ number_format(array_sum($monthlyExpense), 2) }}
                        </b>
                    </div>

                    <div class="dash-chart-stat">
                        <small>Net</small>
                        <b id="dashTotalNet">
                            ${{ number_format(array_sum($monthlyIncome) - array_sum($monthlyExpense), 2) }}
                        </b>
                    </div>
                </div>

                <div class="dash-chart-box">
                    <canvas id="dashFundsChart"></canvas>
                </div>
            </section>

            <section class="dash-bottom">
                <div class="dash-donut">
                    <div class="dash-section-title" style="padding:0 0 16px;border-bottom:0;">
                        <i class="ti ti-chart-donut"></i>
                        Request Breakdown
                    </div>

                    <div class="dash-donut-grid">
                        <div class="dash-donut-wrap">
                            <canvas id="dashDonut" width="190" height="190"></canvas>

                            <div class="dash-donut-center">
                                <b>{{ $totalCount }}</b>
                                <small>Total</small>
                            </div>
                        </div>

                        <div class="dash-legend">
                            <div class="dash-legend-row">
                                <div class="dash-legend-key"><span class="dash-dot" style="background:#0ecb81"></span>Approved</div>
                                <strong style="color:var(--green)">{{ $approvedCount }}</strong>
                            </div>

                            <div class="dash-legend-row">
                                <div class="dash-legend-key"><span class="dash-dot" style="background:#ffc933"></span>Pending</div>
                                <strong style="color:var(--gold)">{{ $pendingCount }}</strong>
                            </div>

                            <div class="dash-legend-row">
                                <div class="dash-legend-key"><span class="dash-dot" style="background:#ff6b7b"></span>Rejected</div>
                                <strong style="color:#ff6b7b">{{ $rejectedCount }}</strong>
                            </div>

                            <div class="dash-legend-row">
                                <div class="dash-legend-key"><span class="dash-dot" style="background:#34d399"></span>Buy USD</div>
                                <strong>{{ $buyCount }}</strong>
                            </div>

                            <div class="dash-legend-row">
                                <div class="dash-legend-key"><span class="dash-dot" style="background:#e8192c"></span>Sell USD</div>
                                <strong>{{ $sellCount }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @if(!($user->is_verified ?? false))
                <div class="dash-notice">
                    <i class="ti ti-alert-triangle"></i>
                    <span><b>Complete your verification</b> — submit KYC documents to unlock full transaction limits.</span>
                </div>
            @endif

        </main>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

<script>
(function () {
    const datasets = {
        monthly: {
            labels: @json($monthLabels),
            income: @json($monthlyIncome),
            expense: @json($monthlyExpense),
        },
        weekly: {
            labels: @json($weekLabels),
            income: @json($weeklyIncome),
            expense: @json($weeklyExpense),
        },
        daily: {
            labels: @json($dayLabels),
            income: @json($dailyIncome),
            expense: @json($dailyExpense),
        },
    };

    const fundsCanvas = document.getElementById('dashFundsChart');

    if (fundsCanvas && window.Chart) {
        const ctx = fundsCanvas.getContext('2d');

        const fundsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: datasets.monthly.labels,
                datasets: [
                    {
                        label: 'Sell USD Income',
                        data: datasets.monthly.income,
                        borderColor: '#0ecb81',
                        backgroundColor: 'rgba(14,203,129,.12)',
                        fill: true,
                        tension: .42,
                        borderWidth: 3,
                        pointRadius: 4,
                    },
                    {
                        label: 'Buy USD Expense',
                        data: datasets.monthly.expense,
                        borderColor: '#e8192c',
                        backgroundColor: 'rgba(232,25,44,.12)',
                        fill: true,
                        tension: .42,
                        borderWidth: 3,
                        pointRadius: 4,
                        borderDash: [6, 4],
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#aeb4ba' } },
                    tooltip: {
                        backgroundColor: '#1f2428',
                        borderColor: '#3b4248',
                        borderWidth: 1,
                        titleColor: '#fff',
                        bodyColor: '#aeb4ba',
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#9fa5aa' },
                        grid: { color: 'rgba(255,255,255,.05)' }
                    },
                    y: {
                        ticks: {
                            color: '#9fa5aa',
                            callback: value => '$' + value
                        },
                        grid: { color: 'rgba(255,255,255,.05)' }
                    }
                }
            }
        });

        const totalIncome = document.getElementById('dashTotalIncome');
        const totalExpense = document.getElementById('dashTotalExpense');
        const totalNet = document.getElementById('dashTotalNet');

        function sum(arr) {
            return arr.reduce((a, b) => a + Number(b || 0), 0);
        }

        function money(n) {
            return '$' + Number(n || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        document.querySelectorAll('.dash-tab').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.dash-tab').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const period = btn.dataset.period;
                const d = datasets[period];

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
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [{{ $approvedCount }}, {{ $pendingCount }}, {{ $rejectedCount }}],
                    backgroundColor: ['#0ecb81', '#ffc933', '#e8192c'],
                    borderColor: '#2b2f32',
                    borderWidth: 4,
                }]
            },
            options: {
                responsive: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2428',
                        borderColor: '#3b4248',
                        borderWidth: 1,
                        titleColor: '#fff',
                        bodyColor: '#aeb4ba',
                    }
                }
            }
        });
    }
})();
</script>
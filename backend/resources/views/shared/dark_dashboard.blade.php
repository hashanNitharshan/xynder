@php
    $isMerchant = ($mode ?? 'client') === 'merchant';
    $roleLabel   = $isMerchant ? 'Merchant' : 'Client';
    $name        = $user->name ?? $roleLabel;

    $totalUsd    = $latestRequests->sum('amount');
    $totalInr    = $latestRequests->sum('total_amount');
    $approvedCount = $latestRequests->where('status','approved')->count();
    $pendingCount  = $latestRequests->where('status','pending')->count();
    $rejectedCount = $latestRequests->where('status','rejected')->count();
    $totalCount    = $latestRequests->count();

    /* ── 6-month data for chart ── */
    $monthlyIncome  = [];
    $monthlyExpense = [];
    $monthLabels    = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = now()->subMonths($i);
        $monthLabels[]    = $m->format('M Y');
        $monthlyIncome[]  = round((float)$latestRequests->where('type','withdraw')
            ->filter(fn($r) => $r->created_at && $r->created_at->month==$m->month && $r->created_at->year==$m->year)
            ->sum('amount'), 2);
        $monthlyExpense[] = round((float)$latestRequests->where('type','deposit')
            ->filter(fn($r) => $r->created_at && $r->created_at->month==$m->month && $r->created_at->year==$m->year)
            ->sum('amount'), 2);
    }

    /* ── 4-week data ── */
    $weeklyIncome  = [];
    $weeklyExpense = [];
    $weekLabels    = [];
    for ($i = 3; $i >= 0; $i--) {
        $start = now()->startOfWeek()->subWeeks($i);
        $end   = (clone $start)->endOfWeek();
        $weekLabels[]    = 'Wk '.($start->weekOfYear);
        $weeklyIncome[]  = round((float)$latestRequests->where('type','withdraw')
            ->filter(fn($r) => $r->created_at && $r->created_at->between($start,$end))->sum('amount'),2);
        $weeklyExpense[] = round((float)$latestRequests->where('type','deposit')
            ->filter(fn($r) => $r->created_at && $r->created_at->between($start,$end))->sum('amount'),2);
    }

    /* ── 7-day data ── */
    $dailyIncome  = [];
    $dailyExpense = [];
    $dayLabels    = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = now()->subDays($i);
        $dayLabels[]    = $d->format('D');
        $dailyIncome[]  = round((float)$latestRequests->where('type','withdraw')
            ->filter(fn($r) => $r->created_at && $r->created_at->isToday($d))->sum('amount'),2);
        $dailyExpense[] = round((float)$latestRequests->where('type','deposit')
            ->filter(fn($r) => $r->created_at && $r->created_at->isToday($d))->sum('amount'),2);
    }

    /* ── Donut chart data ── */
    $buyCount  = $latestRequests->where('type','deposit')->count();
    $sellCount = $latestRequests->where('type','withdraw')->count();

    /* ── Recent 5 for activity feed ── */
    $recent5 = $latestRequests->sortByDesc('created_at')->take(5);
@endphp
<!-------------------------------------------------------------------
  XYNDER – MASSIVE DARK FINTECH DASHBOARD
  Drop into: resources/views/shared/dark_dashboard.blade.php
-------------------------------------------------------------------->
<style>
/* ═══════════════════════════════════════════════════════
   RESET & TOKENS
═══════════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#080b12!important;color:#e8eaf0!important;font-family:'Inter',system-ui,sans-serif!important}
.card,.kpi{background:transparent!important;box-shadow:none!important;border:0!important}

:root{
  --bg:#080b12;
  --s1:#0e1220;   /* card surface */
  --s2:#131929;   /* nested surface */
  --s3:#1a2235;   /* hover state */
  --bdr:rgba(255,255,255,0.07);
  --bdr2:rgba(255,255,255,0.12);
  --txt:#e8eaf0;
  --muted:#6b7280;
  --muted2:#9ca3af;

  /* accent palette */
  --violet:#7c5cfc;
  --violet2:#5b3fd8;
  --green:#10b981;
  --green2:#059669;
  --amber:#f59e0b;
  --red:#ef4444;
  --blue:#3b82f6;
  --cyan:#06b6d4;
  --pink:#ec4899;

  --r-sm:12px;
  --r-md:16px;
  --r-lg:22px;
  --r-xl:28px;
}

/* ═══════════════════════════════════════════════════════
   LAYOUT GRID
═══════════════════════════════════════════════════════ */
.xyn-shell{
  display:grid;
  grid-template-columns:280px 1fr;
  gap:20px;
  padding:0 0 32px;
  align-items:start;
}

/* ═══════════════════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════════════════ */
.xyn-sidebar{
  display:flex;flex-direction:column;gap:16px;
  position:sticky;top:20px;
}

/* Profile Card */
.xyn-profile{
  background:var(--s1);
  border:1px solid var(--bdr);
  border-radius:var(--r-xl);
  padding:28px 22px;
  text-align:center;
  position:relative;
  overflow:hidden;
}
.xyn-profile::before{
  content:'';position:absolute;
  top:-60px;left:50%;transform:translateX(-50%);
  width:220px;height:220px;border-radius:50%;
  background:radial-gradient(circle,rgba(124,92,252,0.18) 0%,transparent 70%);
  pointer-events:none;
}
.xyn-av-ring{
  width:72px;height:72px;border-radius:50%;
  background:linear-gradient(135deg,var(--violet),var(--cyan));
  padding:2px;margin:0 auto 14px;
  position:relative;z-index:1;
}
.xyn-av-inner{
  width:100%;height:100%;border-radius:50%;
  background:var(--s1);
  display:flex;align-items:center;justify-content:center;
  font-size:26px;font-weight:900;color:var(--violet);
}
.xyn-profile h3{font-size:17px;font-weight:800;margin-bottom:4px;position:relative;z-index:1;}
.xyn-profile .xyn-role{
  font-size:11px;font-weight:700;
  color:var(--muted2);letter-spacing:.07em;text-transform:uppercase;
}
.xyn-wallet-id{
  margin:14px 0 0;padding:10px 14px;
  background:var(--s2);border:1px solid var(--bdr);border-radius:var(--r-sm);
  font-family:monospace;font-size:13px;
  display:flex;align-items:center;justify-content:space-between;gap:8px;
}
.xyn-wallet-id small{color:var(--muted);font-size:10px;display:block;margin-bottom:2px;font-family:inherit;}
.xyn-wallet-id span{color:var(--txt);}
.xyn-copy-btn{
  background:rgba(124,92,252,0.12);border:none;
  color:var(--violet);border-radius:7px;
  padding:5px 9px;cursor:pointer;font-size:13px;
}
.xyn-copy-btn:hover{background:rgba(124,92,252,0.25);}
.xyn-vbadge{
  display:inline-flex;align-items:center;gap:5px;
  margin-top:12px;padding:6px 14px;border-radius:999px;
  font-size:11px;font-weight:800;letter-spacing:.04em;
}
.xyn-vbadge.ok{background:rgba(16,185,129,0.12);color:#34d399;}
.xyn-vbadge.pend{background:rgba(245,158,11,0.12);color:#fbbf24;}
.xyn-vbadge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;}

/* Balance Sheet Panel */
.xyn-bs{
  background:var(--s1);border:1px solid var(--bdr);
  border-radius:var(--r-xl);padding:22px;
}
.xyn-panel-title{
  font-size:11px;font-weight:800;color:var(--muted);
  text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;
  display:flex;align-items:center;justify-content:space-between;
}
.xyn-panel-title a{font-size:10px;color:var(--violet);text-decoration:none;}
.xyn-bs-row{
  display:flex;justify-content:space-between;align-items:center;
  padding:11px 0;border-bottom:1px solid var(--bdr);
}
.xyn-bs-row:last-child{border-bottom:0;}
.xyn-bs-lbl{display:flex;align-items:center;gap:9px;font-size:13px;color:var(--muted2);}
.xyn-bs-dot{width:9px;height:9px;border-radius:3px;flex-shrink:0;}
.xyn-bs-val{font-size:14px;font-weight:800;color:var(--txt);}
.xyn-bs-divider{height:1px;background:var(--bdr2);margin:12px 0;}
.xyn-bs-total{display:flex;justify-content:space-between;align-items:baseline;}
.xyn-bs-total-lbl{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;}
.xyn-bs-total-val{font-size:24px;font-weight:900;
  background:linear-gradient(90deg,var(--violet),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

/* Status Donut */
.xyn-donut-panel{
  background:var(--s1);border:1px solid var(--bdr);
  border-radius:var(--r-xl);padding:22px;
}
.xyn-donut-wrap{
  display:flex;align-items:center;gap:20px;margin-top:14px;
}
.xyn-donut-svg{flex-shrink:0;}
.xyn-donut-legend{flex:1;display:flex;flex-direction:column;gap:10px;}
.xyn-dl-row{display:flex;justify-content:space-between;align-items:center;}
.xyn-dl-key{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted2);}
.xyn-dl-key span{width:10px;height:10px;border-radius:3px;}
.xyn-dl-val{font-size:14px;font-weight:800;}

/* Activity Feed */
.xyn-activity{
  background:var(--s1);border:1px solid var(--bdr);
  border-radius:var(--r-xl);padding:22px;
}
.xyn-act-list{display:flex;flex-direction:column;gap:0;}
.xyn-act-item{
  display:flex;align-items:center;gap:12px;
  padding:12px 0;border-bottom:1px solid var(--bdr);
}
.xyn-act-item:last-child{border-bottom:0;}
.xyn-act-icon{
  width:36px;height:36px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  font-size:16px;flex-shrink:0;
}
.xyn-act-icon.buy{background:rgba(239,68,68,0.12);color:#f87171;}
.xyn-act-icon.sell{background:rgba(16,185,129,0.12);color:#34d399;}
.xyn-act-body{flex:1;min-width:0;}
.xyn-act-body b{display:block;font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.xyn-act-body small{color:var(--muted);font-size:11px;}
.xyn-act-amt{font-size:13px;font-weight:800;text-align:right;white-space:nowrap;}

/* ═══════════════════════════════════════════════════════
   MAIN CONTENT
═══════════════════════════════════════════════════════ */
.xyn-main{display:flex;flex-direction:column;gap:20px;min-width:0;}

/* ── Top hero strip ── */
.xyn-hero{
  background:var(--s1);border:1px solid var(--bdr);
  border-radius:var(--r-xl);padding:26px 28px;
  display:flex;justify-content:space-between;align-items:center;
  position:relative;overflow:hidden;
}
.xyn-hero::before{
  content:'';position:absolute;
  right:-80px;top:-80px;
  width:320px;height:320px;border-radius:50%;
  background:radial-gradient(circle,rgba(124,92,252,0.12) 0%,transparent 65%);
  pointer-events:none;
}
.xyn-hero::after{
  content:'';position:absolute;
  right:120px;bottom:-60px;
  width:200px;height:200px;border-radius:50%;
  background:radial-gradient(circle,rgba(6,182,212,0.08) 0%,transparent 65%);
  pointer-events:none;
}
.xyn-hero-left h1{font-size:26px;font-weight:900;margin-bottom:5px;}
.xyn-hero-left h1 span{
  background:linear-gradient(90deg,var(--violet),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.xyn-hero-left p{font-size:14px;color:var(--muted2);}
.xyn-hero-right{
  display:flex;align-items:center;gap:12px;
  position:relative;z-index:1;
}
.xyn-hero-time{
  text-align:right;
}
.xyn-hero-time .ht-date{font-size:14px;font-weight:700;color:var(--txt);}
.xyn-hero-time .ht-greet{font-size:12px;color:var(--muted2);}

/* ── KPI Row ── */
.xyn-kpi-row{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:14px;
}
.xyn-kpi-card{
  border-radius:var(--r-lg);
  padding:20px;
  border:1px solid transparent;
  position:relative;overflow:hidden;
  transition:transform .15s;
}
.xyn-kpi-card:hover{transform:translateY(-2px);}
.xyn-kpi-card.k1{background:linear-gradient(140deg,#1a1060,#0e0a40);border-color:rgba(124,92,252,0.25);}
.xyn-kpi-card.k2{background:linear-gradient(140deg,#062a1e,#031a12);border-color:rgba(16,185,129,0.2);}
.xyn-kpi-card.k3{background:linear-gradient(140deg,#1c1106,#120b02);border-color:rgba(245,158,11,0.2);}
.xyn-kpi-card.k4{background:linear-gradient(140deg,#0d1f38,#071228);border-color:rgba(59,130,246,0.2);}

.xyn-kpi-card .kpi-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;}
.xyn-kpi-card .kpi-badge{
  width:42px;height:42px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  font-size:20px;
}
.k1 .kpi-badge{background:rgba(124,92,252,0.2);}
.k2 .kpi-badge{background:rgba(16,185,129,0.15);}
.k3 .kpi-badge{background:rgba(245,158,11,0.15);}
.k4 .kpi-badge{background:rgba(59,130,246,0.15);}

.xyn-kpi-card .kpi-trend{
  font-size:11px;font-weight:800;padding:4px 9px;border-radius:999px;
}
.kpi-trend.up{background:rgba(16,185,129,0.12);color:#34d399;}
.kpi-trend.down{background:rgba(239,68,68,0.12);color:#f87171;}
.kpi-trend.neutral{background:rgba(107,114,128,0.15);color:#9ca3af;}

.xyn-kpi-card .kpi-label{font-size:11px;font-weight:700;color:rgba(255,255,255,0.45);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;}
.xyn-kpi-card .kpi-val{font-size:28px;font-weight:900;color:#fff;line-height:1;}
.xyn-kpi-card .kpi-sub{font-size:12px;color:rgba(255,255,255,0.4);margin-top:6px;}
.xyn-kpi-card .kpi-bar{
  height:3px;border-radius:2px;margin-top:14px;
  background:rgba(255,255,255,0.08);overflow:hidden;
}
.xyn-kpi-card .kpi-bar-fill{height:100%;border-radius:2px;transition:width 1s ease;}
.k1 .kpi-bar-fill{background:linear-gradient(90deg,var(--violet),var(--cyan));}
.k2 .kpi-bar-fill{background:linear-gradient(90deg,var(--green),#34d399);}
.k3 .kpi-bar-fill{background:linear-gradient(90deg,var(--amber),#fcd34d);}
.k4 .kpi-bar-fill{background:linear-gradient(90deg,var(--blue),var(--cyan));}

/* ── Wallet Cards ── */
.xyn-wallet-row{
  display:grid;grid-template-columns:repeat(3,1fr);gap:14px;
}
.xyn-wcard{
  border-radius:var(--r-lg);padding:22px;
  position:relative;overflow:hidden;min-height:130px;
  transition:transform .15s;
}
.xyn-wcard:hover{transform:translateY(-3px);}
.xyn-wcard.wc1{background:linear-gradient(135deg,#4f35c8,#7c5cfc,#a78bfa);}
.xyn-wcard.wc2{background:linear-gradient(135deg,#9d174d,#db2777,#f472b6);}
.xyn-wcard.wc3{background:linear-gradient(135deg,#065f46,#059669,#34d399);}
.xyn-wcard-shine{
  position:absolute;top:-30%;right:-10%;
  width:180px;height:180px;border-radius:50%;
  background:rgba(255,255,255,0.06);pointer-events:none;
}
.xyn-wcard-shine2{
  position:absolute;bottom:-40%;left:-10%;
  width:130px;height:130px;border-radius:50%;
  background:rgba(255,255,255,0.04);pointer-events:none;
}
.xyn-wcard .wc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;}
.xyn-wcard .wc-type{font-size:11px;font-weight:800;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:.08em;}
.xyn-wcard .wc-status-pill{
  background:rgba(255,255,255,0.18);
  color:#fff;font-size:10px;font-weight:800;
  padding:3px 10px;border-radius:999px;
}
.xyn-wcard .wc-val{font-size:24px;font-weight:900;color:#fff;margin-bottom:4px;}
.xyn-wcard .wc-label{font-size:11px;color:rgba(255,255,255,0.55);}
.xyn-wcard .wc-chip{
  position:absolute;bottom:18px;right:18px;
  background:rgba(255,255,255,0.12);border-radius:8px;
  padding:5px 10px;font-size:12px;color:rgba(255,255,255,0.8);font-weight:700;
}

/* ── Chart Section ── */
.xyn-chart-section{
  background:var(--s1);border:1px solid var(--bdr);
  border-radius:var(--r-xl);padding:24px;
}
.xyn-chart-head{
  display:flex;justify-content:space-between;align-items:center;
  margin-bottom:6px;
}
.xyn-chart-head h2{font-size:17px;font-weight:800;}
.xyn-tab-group{display:flex;background:var(--s2);border-radius:var(--r-sm);padding:3px;gap:2px;}
.xyn-tab{
  padding:7px 16px;border-radius:9px;
  background:transparent;border:none;
  color:var(--muted);font-size:12px;font-weight:700;cursor:pointer;
  transition:all .15s;
}
.xyn-tab.active{background:var(--violet);color:#fff;}
.xyn-tab:hover:not(.active){color:var(--txt);}

.xyn-chart-meta{
  display:flex;align-items:center;gap:24px;
  margin-bottom:18px;padding:14px 16px;
  background:var(--s2);border-radius:var(--r-md);
}
.xyn-chart-stat{flex:1;}
.xyn-chart-stat .cs-lbl{font-size:11px;color:var(--muted);font-weight:600;margin-bottom:3px;}
.xyn-chart-stat .cs-val{font-size:20px;font-weight:900;}
.xyn-chart-stat .cs-val.income{color:#34d399;}
.xyn-chart-stat .cs-val.expense{color:#f87171;}
.xyn-chart-div{width:1px;height:36px;background:var(--bdr2);}

.xyn-chart-legend{display:flex;gap:18px;margin-bottom:14px;}
.xcl-item{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted2);}
.xcl-line{width:24px;height:3px;border-radius:2px;}
.xcl-line.dashed{background:repeating-linear-gradient(90deg,#f87171 0px,#f87171 5px,transparent 5px,transparent 9px);}

.xyn-chart-container{position:relative;width:100%;height:240px;}

/* ── Grid: table + donut ── */
.xyn-bottom-grid{
  display:grid;grid-template-columns:1fr 340px;gap:16px;
}

/* ── Transactions Table ── */
.xyn-tx{
  background:var(--s1);border:1px solid var(--bdr);
  border-radius:var(--r-xl);padding:24px;
}
.xyn-tx-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;}
.xyn-tx-head h2{font-size:17px;font-weight:800;}
.xyn-tx-search{
  display:flex;align-items:center;gap:8px;
  background:var(--s2);border:1px solid var(--bdr);border-radius:var(--r-sm);
  padding:8px 12px;font-size:12px;color:var(--muted);
}
.xyn-table-wrap{overflow-x:auto;}
.xyn-tbl{
  width:100%;border-collapse:separate;border-spacing:0 5px;
  font-size:13px;
}
.xyn-tbl thead th{
  color:var(--muted);font-size:10px;font-weight:700;
  text-transform:uppercase;letter-spacing:.08em;
  padding:0 12px 8px;text-align:left;
}
.xyn-tbl tbody tr{cursor:default;transition:opacity .1s;}
.xyn-tbl tbody tr:hover td{background:var(--s3);}
.xyn-tbl tbody td{
  background:var(--s2);padding:13px 12px;
  border-top:1px solid var(--bdr);
  border-bottom:1px solid var(--bdr);
}
.xyn-tbl tbody td:first-child{border-left:1px solid var(--bdr);border-radius:13px 0 0 13px;}
.xyn-tbl tbody td:last-child{border-right:1px solid var(--bdr);border-radius:0 13px 13px 0;}
.xyn-tbl-badge{
  padding:4px 11px;border-radius:999px;
  font-size:10px;font-weight:800;letter-spacing:.04em;display:inline-block;
}
.xyn-tbl-badge.approved{background:rgba(16,185,129,0.12);color:#34d399;}
.xyn-tbl-badge.pending{background:rgba(245,158,11,0.12);color:#fbbf24;}
.xyn-tbl-badge.rejected{background:rgba(239,68,68,0.12);color:#f87171;}

.xyn-tx-icon{
  width:30px;height:30px;border-radius:9px;
  display:inline-flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:900;vertical-align:middle;margin-right:7px;
}
.xyn-tx-icon.buy{background:rgba(239,68,68,0.12);color:#f87171;}
.xyn-tx-icon.sell{background:rgba(16,185,129,0.12);color:#34d399;}
.xyn-person-chip{display:flex;align-items:center;gap:8px;}
.xyn-person-av{
  width:28px;height:28px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:800;
  background:rgba(124,92,252,0.15);color:#a78bfa;
}
.xyn-amt-up{color:#34d399;font-weight:800;}
.xyn-amt-dn{color:#f87171;font-weight:800;}
.xyn-mono{font-family:monospace;font-size:11px;color:var(--muted);}

/* ── Right column: donut + stats ── */
.xyn-right-col{display:flex;flex-direction:column;gap:16px;}

.xyn-donut-card{
  background:var(--s1);border:1px solid var(--bdr);
  border-radius:var(--r-xl);padding:22px;
}
.xyn-donut-center-wrap{
  position:relative;display:flex;justify-content:center;margin:20px 0 16px;
}
.xyn-donut-center{
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  text-align:center;
}
.xyn-donut-center .dc-val{font-size:24px;font-weight:900;}
.xyn-donut-center .dc-lbl{font-size:11px;color:var(--muted);font-weight:700;}

.xyn-do-legend{display:flex;flex-direction:column;gap:10px;}
.xyn-do-row{display:flex;justify-content:space-between;align-items:center;}
.xyn-do-key{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted2);}
.xyn-do-key span{width:10px;height:10px;border-radius:3px;}
.xyn-do-pct{font-size:14px;font-weight:800;}

/* Quick Actions */
.xyn-quick{
  background:var(--s1);border:1px solid var(--bdr);
  border-radius:var(--r-xl);padding:22px;
}
.xyn-qa-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px;}
.xyn-qa-btn{
  background:var(--s2);border:1px solid var(--bdr);
  border-radius:var(--r-md);padding:14px 12px;
  display:flex;flex-direction:column;align-items:center;gap:7px;
  text-decoration:none;color:var(--txt);cursor:pointer;
  transition:all .15s;text-align:center;
}
.xyn-qa-btn:hover{background:var(--s3);border-color:var(--bdr2);transform:translateY(-1px);}
.xyn-qa-ico{
  width:38px;height:38px;border-radius:11px;
  display:flex;align-items:center;justify-content:center;font-size:18px;
}
.xyn-qa-btn:nth-child(1) .xyn-qa-ico{background:rgba(124,92,252,0.15);}
.xyn-qa-btn:nth-child(2) .xyn-qa-ico{background:rgba(16,185,129,0.12);}
.xyn-qa-btn:nth-child(3) .xyn-qa-ico{background:rgba(245,158,11,0.12);}
.xyn-qa-btn:nth-child(4) .xyn-qa-ico{background:rgba(6,182,212,0.12);}
.xyn-qa-btn span{font-size:12px;font-weight:700;color:var(--muted2);}

/* Tooltip notice */
.xyn-notice{
  background:linear-gradient(90deg,rgba(124,92,252,0.12),rgba(6,182,212,0.08));
  border:1px solid rgba(124,92,252,0.2);
  border-radius:var(--r-md);padding:14px 18px;
  display:flex;align-items:center;gap:12px;
  font-size:13px;color:var(--muted2);
}
.xyn-notice b{color:var(--txt);}

/* ── Responsive ── */
@media(max-width:1200px){
  .xyn-kpi-row{grid-template-columns:repeat(2,1fr);}
  .xyn-bottom-grid{grid-template-columns:1fr;}
  .xyn-right-col{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
}
@media(max-width:1050px){
  .xyn-shell{grid-template-columns:1fr;}
  .xyn-sidebar{position:static;}
  .xyn-wallet-row{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:680px){
  .xyn-kpi-row{grid-template-columns:1fr;}
  .xyn-wallet-row{grid-template-columns:1fr;}
  .xyn-hero{flex-direction:column;gap:14px;align-items:flex-start;}
  .xyn-right-col{grid-template-columns:1fr;}
}
</style>

<div class="xyn-shell">

  {{-- ════════════════════════ SIDEBAR ════════════════════════ --}}
  <aside class="xyn-sidebar">

    {{-- Profile --}}
    <div class="xyn-profile">
      <div class="xyn-av-ring">
        <div class="xyn-av-inner">{{ strtoupper(substr($name,0,1)) }}</div>
      </div>
      <h3>{{ $name }}</h3>
      <div class="xyn-role">{{ $roleLabel }} Account</div>
      <div class="xyn-wallet-id">
        <div>
          <small>Wallet ID</small>
          <span>{{ $user->wallet_id ?? 'WXXX-XXXX-XXXX' }}</span>
        </div>
        <button class="xyn-copy-btn" onclick="navigator.clipboard.writeText('{{ $user->wallet_id ?? '' }}');this.textContent='✓'">⎘</button>
      </div>
      @if($user->is_verified ?? false)
        <div class="xyn-vbadge ok">Verified Account</div>
      @else
        <div class="xyn-vbadge pend">Verification Pending</div>
      @endif
    </div>

    {{-- Balance Sheet --}}
    <div class="xyn-bs">
      <div class="xyn-panel-title">Balance Sheet</div>
      <div class="xyn-bs-row">
        <div class="xyn-bs-lbl"><span class="xyn-bs-dot" style="background:#7c5cfc"></span>Wallet Balance</div>
        <div class="xyn-bs-val">₹{{ number_format((float)($user->balance ?? 0),2) }}</div>
      </div>
      <div class="xyn-bs-row">
        <div class="xyn-bs-lbl"><span class="xyn-bs-dot" style="background:#10b981"></span>USD Volume</div>
        <div class="xyn-bs-val">${{ number_format((float)$totalUsd,2) }}</div>
      </div>
      <div class="xyn-bs-row">
        <div class="xyn-bs-lbl"><span class="xyn-bs-dot" style="background:#f59e0b"></span>INR Volume</div>
        <div class="xyn-bs-val">₹{{ number_format((float)$totalInr,2) }}</div>
      </div>
      <div class="xyn-bs-row">
        <div class="xyn-bs-lbl"><span class="xyn-bs-dot" style="background:#3b82f6"></span>Approved</div>
        <div class="xyn-bs-val" style="color:#34d399">{{ $approvedCount }} txns</div>
      </div>
      <div class="xyn-bs-row">
        <div class="xyn-bs-lbl"><span class="xyn-bs-dot" style="background:#ef4444"></span>Pending</div>
        <div class="xyn-bs-val" style="color:#fbbf24">{{ $pendingCount }} txns</div>
      </div>
      <div class="xyn-bs-divider"></div>
      <div class="xyn-bs-total">
        <span class="xyn-bs-total-lbl">Net Balance</span>
        <span class="xyn-bs-total-val">₹{{ number_format((float)($user->balance ?? 0),2) }}</span>
      </div>
    </div>

    {{-- Activity Feed --}}
    <div class="xyn-activity">
      <div class="xyn-panel-title">Recent Activity</div>
      <div class="xyn-act-list">
        @forelse($recent5 as $act)
          @php $actPerson = $isMerchant ? ($act->user->name ?? 'Client') : ($act->merchant->name ?? 'Merchant'); @endphp
          <div class="xyn-act-item">
            <div class="xyn-act-icon {{ $act->type==='deposit' ? 'buy' : 'sell' }}">
              {{ $act->type==='deposit' ? '↓' : '↑' }}
            </div>
            <div class="xyn-act-body">
              <b>{{ $actPerson }}</b>
              <small>{{ $act->type==='deposit' ? 'Buy USD' : 'Sell USD' }} · {{ $act->created_at?->diffForHumans() }}</small>
            </div>
            <div class="xyn-act-amt {{ $act->type==='deposit' ? 'xyn-amt-dn' : 'xyn-amt-up' }}">
              {{ $act->type==='deposit' ? '-' : '+' }}${{ number_format((float)($act->amount??0),2) }}
            </div>
          </div>
        @empty
          <div style="text-align:center;padding:20px;color:var(--muted);font-size:13px;">No recent activity</div>
        @endforelse
      </div>
    </div>

  </aside>

  {{-- ════════════════════════ MAIN ════════════════════════ --}}
  <main class="xyn-main">

   

    {{-- KPI Cards --}}
    <div class="xyn-kpi-row">
      <div class="xyn-kpi-card k1">
        <div class="kpi-top">
          <div class="kpi-badge">💰</div>
          <div class="kpi-trend neutral">Wallet</div>
        </div>
        <div class="kpi-label">Wallet Balance</div>
        <div class="kpi-val">₹{{ number_format((float)($user->balance??0),0) }}</div>
        <div class="kpi-sub">Available funds</div>
        <div class="kpi-bar"><div class="kpi-bar-fill" style="width:72%"></div></div>
      </div>

      <div class="xyn-kpi-card k2">
        <div class="kpi-top">
          <div class="kpi-badge">💵</div>
          <div class="kpi-trend up">Active</div>
        </div>
        <div class="kpi-label">USD Volume</div>
        <div class="kpi-val">${{ number_format((float)$totalUsd,2) }}</div>
        <div class="kpi-sub">{{ $approvedCount }} approved</div>
        <div class="kpi-bar"><div class="kpi-bar-fill" style="width:{{ $totalCount > 0 ? round($approvedCount/$totalCount*100) : 0 }}%"></div></div>
      </div>

      <div class="xyn-kpi-card k3">
        <div class="kpi-top">
          <div class="kpi-badge">📈</div>
          <div class="kpi-trend {{ $pendingCount > 0 ? 'down' : 'up' }}">{{ $pendingCount > 0 ? $pendingCount.' Pending' : 'Clear' }}</div>
        </div>
        <div class="kpi-label">INR Volume</div>
        <div class="kpi-val">₹{{ number_format((float)$totalInr,0) }}</div>
        <div class="kpi-sub">Total transacted</div>
        <div class="kpi-bar"><div class="kpi-bar-fill" style="width:65%"></div></div>
      </div>

      <div class="xyn-kpi-card k4">
        <div class="kpi-top">
          <div class="kpi-badge">🎯</div>
          <div class="kpi-trend neutral">All time</div>
        </div>
        <div class="kpi-label">Total Requests</div>
        <div class="kpi-val">{{ $totalCount }}</div>
        <div class="kpi-sub">{{ $rejectedCount }} rejected</div>
        <div class="kpi-bar"><div class="kpi-bar-fill" style="width:{{ $totalCount > 0 ? round(($approvedCount+$pendingCount)/$totalCount*100) : 0 }}%"></div></div>
      </div>
    </div>

    {{-- Wallet Cards --}}
    <div class="xyn-wallet-row">
      <div class="xyn-wcard wc1">
        <div class="xyn-wcard-shine"></div><div class="xyn-wcard-shine2"></div>
        <div class="wc-top">
          <div class="wc-type">Wallet Balance</div>
          <div class="wc-status-pill">Active</div>
        </div>
        <div class="wc-val">₹{{ number_format((float)($user->balance??0),2) }}</div>
        <div class="wc-label">Available balance</div>
        <div class="wc-chip">XYNDER</div>
      </div>
      <div class="xyn-wcard wc2">
        <div class="xyn-wcard-shine"></div><div class="xyn-wcard-shine2"></div>
        <div class="wc-top">
          <div class="wc-type">USD Account</div>
          <div class="wc-status-pill">{{ $user->is_verified ? 'Verified' : 'Pending' }}</div>
        </div>
        <div class="wc-val">${{ number_format((float)$totalUsd,2) }}</div>
        <div class="wc-label">Total USD transacted</div>
        <div class="wc-chip">BUY / SELL</div>
      </div>
      <div class="xyn-wcard wc3">
        <div class="xyn-wcard-shine"></div><div class="xyn-wcard-shine2"></div>
        <div class="wc-top">
          <div class="wc-type">INR Volume</div>
          <div class="wc-status-pill">{{ $roleLabel }}</div>
        </div>
        <div class="wc-val">₹{{ number_format((float)$totalInr,2) }}</div>
        <div class="wc-label">Total INR transacted</div>
        <div class="wc-chip">{{ $approvedCount }} approved</div>
      </div>
    </div>

    {{-- Funds Overview Chart --}}
    <div class="xyn-chart-section">
      <div class="xyn-chart-head">
        <h2>Funds Overview</h2>
        <div class="xyn-tab-group">
          <button class="xyn-tab active" data-period="monthly">Monthly</button>
          <button class="xyn-tab" data-period="weekly">Weekly</button>
          <button class="xyn-tab" data-period="daily">Daily</button>
        </div>
      </div>

      <div class="xyn-chart-meta">
        <div class="xyn-chart-stat">
          <div class="cs-lbl">Sell USD (Income)</div>
          <div class="cs-val income" id="xyn-total-income">
            ${{ number_format(array_sum($monthlyIncome),2) }}
          </div>
        </div>
        <div class="xyn-chart-div"></div>
        <div class="xyn-chart-stat">
          <div class="cs-lbl">Buy USD (Expense)</div>
          <div class="cs-val expense" id="xyn-total-expense">
            ${{ number_format(array_sum($monthlyExpense),2) }}
          </div>
        </div>
        <div class="xyn-chart-div"></div>
        <div class="xyn-chart-stat">
          <div class="cs-lbl">Net</div>
          <div class="cs-val" id="xyn-total-net" style="color:#a78bfa">
            ${{ number_format(array_sum($monthlyIncome)-array_sum($monthlyExpense),2) }}
          </div>
        </div>
      </div>

      <div class="xyn-chart-legend">
        <div class="xcl-item"><div class="xcl-line" style="background:#7c5cfc"></div> Sell USD (Income)</div>
        <div class="xcl-item"><div class="xcl-line dashed"></div> Buy USD (Expense)</div>
      </div>

      <div class="xyn-chart-container">
        <canvas id="xynFundsChart" role="img" aria-label="Funds overview chart showing income and expense over time">Chart loading…</canvas>
      </div>
    </div>

    {{-- Bottom Grid --}}
    <div class="xyn-bottom-grid">

      {{-- Transactions Table --}}
      <div class="xyn-tx">
        <div class="xyn-tx-head">
          <h2>{{ $isMerchant ? 'Customer Requests' : 'Wallet Requests' }}</h2>
          <div class="xyn-tx-search">🔍 Search transactions…</div>
        </div>

        <div class="xyn-table-wrap">
          <table class="xyn-tbl">
            <thead>
              <tr>
                <th>Ref</th>
                <th>{{ $isMerchant ? 'Client' : 'Merchant' }}</th>
                <th>Type</th>
                <th>USD Amt</th>
                <th>INR Total</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($latestRequests as $req)
                @php $pName = $isMerchant ? ($req->user->name??'Client') : ($req->merchant->name??'Merchant'); @endphp
                <tr>
                  <td class="xyn-mono">{{ $req->transaction_no ?? 'TNS'.str_pad($req->id,7,'0',STR_PAD_LEFT) }}</td>
                  <td>
                    <div class="xyn-person-chip">
                      <div class="xyn-person-av">{{ strtoupper(substr($pName,0,1)) }}</div>
                      <span style="font-size:13px;">{{ $pName }}</span>
                    </div>
                  </td>
                  <td>
                    <div style="display:flex;align-items:center;gap:4px;">
                      <span class="xyn-tx-icon {{ $req->type==='deposit'?'buy':'sell' }}">
                        {{ $req->type==='deposit'?'B':'S' }}
                      </span>
                      <span style="font-size:12px;font-weight:700;">{{ $req->type==='deposit'?'BUY':'SELL' }}</span>
                    </div>
                  </td>
                  <td class="{{ $req->type==='deposit'?'xyn-amt-dn':'xyn-amt-up' }}">
                    ${{ number_format((float)($req->amount??0),2) }}
                  </td>
                  <td style="color:var(--muted2);font-weight:700;">₹{{ number_format((float)($req->total_amount??0),2) }}</td>
                  <td><span class="xyn-tbl-badge {{ $req->status }}">{{ strtoupper($req->status??'-') }}</span></td>
                  <td style="color:var(--muted);font-size:11px;">{{ $req->created_at?->format('d M, H:i') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" style="text-align:center;padding:32px;color:var(--muted);">
                    No transactions found
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Right column --}}
      <div class="xyn-right-col">

        {{-- Donut Chart --}}
        <div class="xyn-donut-card">
          <div class="xyn-panel-title">Request Breakdown</div>
          <div class="xyn-donut-center-wrap">
            <canvas id="xynDonut" width="160" height="160" role="img"
              aria-label="Request breakdown donut chart">Donut chart</canvas>
            <div class="xyn-donut-center">
              <div class="dc-val">{{ $totalCount }}</div>
              <div class="dc-lbl">Total</div>
            </div>
          </div>
          <div class="xyn-do-legend">
            <div class="xyn-do-row">
              <div class="xyn-do-key"><span style="background:#34d399"></span>Approved</div>
              <div class="xyn-do-pct" style="color:#34d399">{{ $approvedCount }}</div>
            </div>
            <div class="xyn-do-row">
              <div class="xyn-do-key"><span style="background:#fbbf24"></span>Pending</div>
              <div class="xyn-do-pct" style="color:#fbbf24">{{ $pendingCount }}</div>
            </div>
            <div class="xyn-do-row">
              <div class="xyn-do-key"><span style="background:#f87171"></span>Rejected</div>
              <div class="xyn-do-pct" style="color:#f87171">{{ $rejectedCount }}</div>
            </div>
            <div class="xyn-do-row">
              <div class="xyn-do-key"><span style="background:#818cf8"></span>Buy USD</div>
              <div class="xyn-do-pct" style="color:#818cf8">{{ $buyCount }}</div>
            </div>
            <div class="xyn-do-row">
              <div class="xyn-do-key"><span style="background:#06b6d4"></span>Sell USD</div>
              <div class="xyn-do-pct" style="color:#06b6d4">{{ $sellCount }}</div>
            </div>
          </div>
        </div>

       

      </div>
    </div>

    {{-- Notice bar --}}
    @if(!($user->is_verified ?? false))
    <div class="xyn-notice">
      <span style="font-size:20px;">⚠️</span>
      <span><b>Complete your verification</b> — Submit your KYC documents to unlock full transaction limits.</span>
    </div>
    @endif
@if(session('popup_transaction'))
@php $popup = session('popup_transaction'); @endphp

<div id="xynSuccessModal" style="position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:99999;display:flex;align-items:center;justify-content:center;">
    <div style="width:min(430px,92vw);background:#0e1220;border:1px solid rgba(255,255,255,.12);border-radius:26px;padding:26px;text-align:center;color:white;box-shadow:0 30px 80px rgba(0,0,0,.45);">
        <div style="width:70px;height:70px;border-radius:24px;margin:0 auto 16px;background:rgba(16,185,129,.14);color:#34d399;display:flex;align-items:center;justify-content:center;font-size:36px;">✓</div>

        <h2 style="font-size:22px;font-weight:900;margin-bottom:8px;">
            {{ $popup['title'] ?? 'Transaction Completed' }}
        </h2>

        <p style="color:#9ca3af;margin-bottom:18px;">
            Transaction created successfully. You can open chat for this transaction now.
        </p>

        <div style="background:#131929;border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:14px;margin-bottom:18px;text-align:left;">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="color:#6b7280;font-weight:800;">Transaction No</span>
                <b>{{ $popup['no'] ?? '—' }}</b>
            </div>

            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="color:#6b7280;font-weight:800;">Amount</span>
                <b>${{ $popup['amount'] ?? '0.00' }}</b>
            </div>

            <div style="display:flex;justify-content:space-between;">
                <span style="color:#6b7280;font-weight:800;">Status</span>
                <b style="color:#34d399;">{{ $popup['status'] ?? 'PENDING' }}</b>
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="button" onclick="document.getElementById('xynSuccessModal').remove()" style="flex:1;padding:13px;border-radius:14px;border:1px solid rgba(255,255,255,.12);background:#131929;color:white;font-weight:900;cursor:pointer;">
                Close
            </button>

            <a href="{{ $popup['chat_url'] ?? '#' }}" style="flex:1;padding:13px;border-radius:14px;background:linear-gradient(90deg,#7c5cfc,#06b6d4);color:white;font-weight:900;text-decoration:none;">
                Open Chat
            </a>
        </div>
    </div>
</div>
@endif
  </main>





</div>

{{-- ════════════════════════ SCRIPTS ════════════════════════ --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
(function () {
  /* ── Data sets from PHP ── */
  var datasets = {
    monthly: {
      labels:  @json($monthLabels),
      income:  @json($monthlyIncome),
      expense: @json($monthlyExpense),
    },
    weekly: {
      labels:  @json($weekLabels),
      income:  @json($weeklyIncome),
      expense: @json($weeklyExpense),
    },
    daily: {
      labels:  @json($dayLabels),
      income:  @json($dailyIncome),
      expense: @json($dailyExpense),
    },
  };

  /* ── Line Chart ── */
  var fundsCtx = document.getElementById('xynFundsChart').getContext('2d');

  var gradIncome = fundsCtx.createLinearGradient(0, 0, 0, 240);
  gradIncome.addColorStop(0,   'rgba(124,92,252,0.30)');
  gradIncome.addColorStop(1,   'rgba(124,92,252,0.00)');

  var gradExpense = fundsCtx.createLinearGradient(0, 0, 0, 240);
  gradExpense.addColorStop(0,  'rgba(239,68,68,0.20)');
  gradExpense.addColorStop(1,  'rgba(239,68,68,0.00)');

  function buildLineDatasets(d) {
    return [
      {
        label: 'Sell USD (Income)',
        data: d.income,
        borderColor: '#7c5cfc',
        backgroundColor: gradIncome,
        fill: true, tension: 0.42,
        borderWidth: 3,
        pointBackgroundColor: '#7c5cfc',
        pointRadius: 5, pointHoverRadius: 7,
        borderDash: [],
      },
      {
        label: 'Buy USD (Expense)',
        data: d.expense,
        borderColor: '#f87171',
        backgroundColor: gradExpense,
        fill: true, tension: 0.42,
        borderWidth: 3,
        pointBackgroundColor: '#f87171',
        pointRadius: 5, pointHoverRadius: 7,
        borderDash: [6, 4],
      }
    ];
  }

  var fundsChart = new Chart(fundsCtx, {
    type: 'line',
    data: {
      labels: datasets.monthly.labels,
      datasets: buildLineDatasets(datasets.monthly),
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0e1220',
          borderColor: 'rgba(124,92,252,0.3)',
          borderWidth: 1,
          titleColor: '#e8eaf0',
          bodyColor: '#9ca3af',
          padding: 12,
          cornerRadius: 10,
          callbacks: {
            label: function(ctx) {
              return ' ' + ctx.dataset.label + ': $' + ctx.parsed.y.toLocaleString('en-IN', {minimumFractionDigits:2});
            }
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
          ticks: { color: '#6b7280', font: { size: 11, weight: '600' } },
          border: { color: 'transparent' },
        },
        y: {
          grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
          ticks: {
            color: '#6b7280', font: { size: 11 },
            callback: function(v) { return '$' + v.toLocaleString('en-IN'); }
          },
          border: { color: 'transparent' },
        }
      },
      interaction: { mode: 'index', intersect: false },
      animation: { duration: 600, easing: 'easeInOutQuart' },
    }
  });

  /* ── Tab switcher ── */
  var totalIncome  = document.getElementById('xyn-total-income');
  var totalExpense = document.getElementById('xyn-total-expense');
  var totalNet     = document.getElementById('xyn-total-net');

  function sum(arr) { return arr.reduce(function(a,b){return a+b;},0); }
  function fmt(n)   { return '$'+n.toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }

  document.querySelectorAll('.xyn-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.xyn-tab').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');

      var period = btn.getAttribute('data-period');
      var d      = datasets[period];

      fundsChart.data.labels   = d.labels;
      fundsChart.data.datasets = buildLineDatasets(d);
      fundsChart.update();

      var inc = sum(d.income), exp = sum(d.expense);
      totalIncome.textContent  = fmt(inc);
      totalExpense.textContent = fmt(exp);
      totalNet.textContent     = fmt(inc - exp);
    });
  });

  /* ── Donut Chart ── */
  var donutCtx = document.getElementById('xynDonut').getContext('2d');
  new Chart(donutCtx, {
    type: 'doughnut',
    data: {
      labels: ['Approved', 'Pending', 'Rejected'],
      datasets: [{
        data: [
          {{ $approvedCount }},
          {{ $pendingCount }},
          {{ $rejectedCount > 0 ? $rejectedCount : 0 }},
        ],
        backgroundColor: ['#10b981','#f59e0b','#ef4444'],
        hoverBackgroundColor: ['#34d399','#fbbf24','#f87171'],
        borderWidth: 3,
        borderColor: '#0e1220',
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: false,
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0e1220',
          borderColor: 'rgba(255,255,255,0.08)',
          borderWidth: 1,
          titleColor: '#e8eaf0',
          bodyColor: '#9ca3af',
          padding: 10,
        }
      },
      animation: { duration: 800, easing: 'easeInOutQuart' },
    }
  });

})();
</script>
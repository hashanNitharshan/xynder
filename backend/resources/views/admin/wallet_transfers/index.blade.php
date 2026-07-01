@extends('layouts.admin', ['title' => 'Wallet Transfer History'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{--dark:#101518;--hero:#2b2f32;--box:#2b2f32;--panel:#24292d;--input:#1f2428;--line:#3b4248;--red:#e8192c;--red2:#c91022;--green:#0ecb81;--gold:#ffc933;--text:#fff;--muted:#aeb4ba;--muted2:#747b82;}
*{box-sizing:border-box}
.ad-page{margin:-24px;min-height:100vh;background:var(--dark);color:var(--text);font-family:Inter,Arial,sans-serif;padding-bottom:60px;}
.ad-hero{background:var(--hero);padding:65px 85px 120px;position:relative;overflow:hidden;}
.ad-hero::after{content:"";position:absolute;inset:0;opacity:.07;background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);background-size:260px 260px;}
.ad-hero-inner{position:relative;z-index:2;max-width:680px;}
.ad-eyebrow{color:var(--red);font-size:12px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;margin-bottom:14px;}
.ad-title-main{font-size:46px;line-height:1.12;font-weight:900;margin:0 0 18px;}
.ad-title-main span{color:var(--red);display:block;}
.ad-subtitle{color:#b8bdc2;font-size:15px;line-height:1.7;font-weight:700;max-width:580px;}
.ad-wrap{position:relative;z-index:5;margin:-82px 45px 0;}
.ad-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:28px;}
.ad-kpi{background:var(--box);border:1px solid var(--line);border-radius:6px;padding:20px;min-height:118px;position:relative;overflow:hidden;}
.ad-kpi::after{content:"";position:absolute;right:-38px;top:-38px;width:115px;height:115px;border-radius:50%;background:rgba(232,25,44,.12);}
.ad-kpi-icon{width:42px;height:42px;border-radius:50%;background:#3a1018;color:var(--red);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:14px;}
.ad-kpi-label{color:var(--muted);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;}
.ad-kpi-value{font-size:28px;font-weight:900;margin-top:6px;}
.ad-card{background:var(--box);border:1.5px solid var(--red);border-radius:7px;overflow:hidden;box-shadow:0 18px 40px rgba(0,0,0,.28);}
.ad-card-head{background:var(--panel);border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:18px 22px;}
.ad-title{font-size:18px;font-weight:900;margin:0;display:flex;align-items:center;gap:9px;}
.ad-title i{color:var(--red);}
.ad-filters{display:grid;grid-template-columns:2fr 1fr auto;gap:10px;padding:16px 22px;border-bottom:1px solid var(--line);background:var(--box);}
.ad-search-wrap{position:relative;}
.ad-search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:16px;}
.ad-input,.ad-select{width:100%;height:42px;border:1px solid var(--line);border-radius:4px;padding:0 13px;font-size:13px;background:var(--input);color:var(--text);outline:0;font-weight:700;}
.ad-search-wrap .ad-input{padding-left:36px;}
.ad-input:focus,.ad-select:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(232,25,44,.12);}
.ad-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 13px;border-radius:4px;font-size:12px;font-weight:900;border:0;cursor:pointer;text-decoration:none;white-space:nowrap;}
.ad-btn-ghost{background:var(--input);color:#fff;border:1px solid var(--line);}
.ad-btn-ghost:hover{border-color:var(--red);color:#fff;}
.ad-table-wrap{width:100%;overflow-x:hidden!important;}
.ad-table{width:100%!important;min-width:0!important;border-collapse:collapse;table-layout:fixed;font-size:13px;}
.ad-table th{background:var(--panel);color:var(--muted2);font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.05em;padding:14px 8px;text-align:left;}
.ad-table td{padding:15px 8px;border-top:1px solid var(--line);color:#c9ced3;vertical-align:middle;font-weight:700;word-break:break-word;white-space:normal;}
.ad-click-row{cursor:pointer;}
.ad-click-row:hover td{background:#30363a;}
.ad-table th:nth-child(1),.ad-table td:nth-child(1){width:12%;}
.ad-table th:nth-child(2),.ad-table td:nth-child(2){width:10%;}
.ad-table th:nth-child(3),.ad-table td:nth-child(3){width:18%;}
.ad-table th:nth-child(4),.ad-table td:nth-child(4){width:18%;}
.ad-table th:nth-child(5),.ad-table td:nth-child(5){width:14%;}
.ad-table th:nth-child(6),.ad-table td:nth-child(6){width:11%;}
.ad-table th:nth-child(7),.ad-table td:nth-child(7){width:17%;}
.ad-ref{color:var(--red);font-family:monospace;font-weight:900;font-size:12px;}
.ad-name{font-weight:900;color:#fff;}
.ad-small{font-size:11px;color:var(--muted);margin-top:3px;}
.ad-wallet{display:inline-flex;align-items:center;gap:5px;margin-top:5px;padding:4px 8px;border-radius:20px;background:#3a1018;color:#ff9aaa;font-size:11px;font-weight:900;}
.ad-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:900;white-space:normal;text-transform:uppercase;}
.ad-badge-green{background:#0d2b1e;color:var(--green);}
.ad-amount{color:var(--green);font-weight:900;font-size:14px;}
.ad-note{max-width:100%;color:#c9ced3;white-space:normal;line-height:1.5;}
.ad-pagination{padding:16px 22px;border-top:1px solid var(--line);background:var(--panel);display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;}
.ad-pagination-info{color:var(--muted);font-size:12px;font-weight:800;}
.ad-empty{text-align:center;padding:45px;color:var(--muted);font-weight:800;}
@media(max-width:1300px){.ad-wrap{margin:-82px 24px 0;}.ad-table th{font-size:9px;padding:12px 6px;}.ad-table td{font-size:12px;padding:13px 6px;}}
@media(max-width:1100px){.ad-kpis{grid-template-columns:repeat(2,1fr);}.ad-filters{grid-template-columns:1fr 1fr;}.ad-table,.ad-table thead,.ad-table tbody,.ad-table th,.ad-table td,.ad-table tr{display:block;width:100%!important;}.ad-table thead{display:none;}.ad-table tr{background:var(--panel);border:1px solid var(--line);border-radius:7px;margin:12px;padding:14px;}.ad-table td{border-top:0;padding:9px 0;}.ad-table td::before{content:attr(data-label);display:block;color:var(--muted2);font-size:10px;font-weight:900;text-transform:uppercase;margin-bottom:5px;}.ad-table td:first-child::before{display:none;}}
@media(max-width:768px){.ad-page{margin:-16px;}.ad-hero{padding:45px 24px 110px;}.ad-title-main{font-size:34px;}.ad-wrap{margin:-76px 18px 0;}.ad-kpis{grid-template-columns:1fr;}.ad-filters{grid-template-columns:1fr;}.ad-pagination{flex-direction:column;align-items:flex-start;}}
</style>
@endpush

@section('content')
<div class="ad-page">
    <section class="ad-hero">
        <div class="ad-hero-inner">
       
            <h1 class="ad-title-main">Wallet Transfer <span>History</span></h1>
         
        </div>
    </section>

    <main class="ad-wrap">
        <section class="ad-kpis">
            <div class="ad-kpi">
                <div class="ad-kpi-icon"><i class="ti ti-arrows-exchange"></i></div>
                <div class="ad-kpi-label">Total Transfers</div>
                <div class="ad-kpi-value">{{ $stats['total_count'] ?? 0 }}</div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon"><i class="ti ti-cash"></i></div>
                <div class="ad-kpi-label">Total Transfer Volume</div>
                <div class="ad-kpi-value">$ {{ number_format((float)($stats['total_volume'] ?? 0), 2) }}</div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon"><i class="ti ti-calendar"></i></div>
                <div class="ad-kpi-label">Today Transfers</div>
                <div class="ad-kpi-value">{{ $stats['today_count'] ?? 0 }}</div>
            </div>

            <div class="ad-kpi">
                <div class="ad-kpi-icon"><i class="ti ti-bolt"></i></div>
                <div class="ad-kpi-label">Today Volume</div>
                <div class="ad-kpi-value">$ {{ number_format((float)($stats['today_volume'] ?? 0), 2) }}</div>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h2 class="ad-title"><i class="ti ti-list-details"></i> Wallet Transfer History</h2>
            </div>

            <form id="adFilterForm" method="GET" action="{{ route('admin.wallet-transfers.index') }}" class="ad-filters">
                <div class="ad-search-wrap">
                    <i class="ti ti-search"></i>
                    <input id="adSearch" type="text" name="search" value="{{ request('search') }}" class="ad-input" placeholder="Search sender, receiver, wallet ID, note...">
                </div>

                <select name="per_page" class="ad-select ad-auto-filter">
                    <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 / page</option>
                    <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100 / page</option>
                </select>

                <a href="{{ route('admin.wallet-transfers.index') }}" class="ad-btn ad-btn-ghost"><i class="ti ti-refresh"></i> Reset</a>
            </form>

            <div class="ad-table-wrap">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Transaction No</th>
                            <th>Date</th>
                            <th>Sender</th>
                            <th>Receiver</th>
                            <th>Receiver Wallet</th>
                            <th>Amount</th>
                            <th>Note</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($transfers as $transfer)
                       <tr class="ad-click-row" onclick="window.location='{{ route('admin.wallet-transfers.chat', ['walletTransfer' => $transfer->id]) }}'">
                            <td data-label="Transaction No">
                                <span class="ad-ref">
                                    {{ $transfer->transaction_no ?? ('TRA' . str_pad($transfer->id, 9, '0', STR_PAD_LEFT)) }}
                                </span>
                            </td>

                            <td data-label="Date">
                                <div class="ad-small">{{ $transfer->created_at?->format('d M Y') }}</div>
                                <div class="ad-small">{{ $transfer->created_at?->format('h:i A') }}</div>
                            </td>

                            <td data-label="Sender">
                                <div class="ad-name">{{ $transfer->sender->name ?? 'Deleted User' }}</div>
                                <div class="ad-small">{{ $transfer->sender->email ?? '-' }}</div>
                                <div class="ad-wallet"><i class="ti ti-wallet"></i> {{ $transfer->sender->wallet_id ?? '-' }}</div>
                            </td>

                            <td data-label="Receiver">
                                <div class="ad-name">{{ $transfer->receiver->name ?? 'Deleted User' }}</div>
                                <div class="ad-small">{{ $transfer->receiver->email ?? '-' }}</div>
                                <div class="ad-wallet"><i class="ti ti-wallet"></i> {{ $transfer->receiver->wallet_id ?? '-' }}</div>
                            </td>

                            <td data-label="Receiver Wallet">
                                <span class="ad-badge ad-badge-green">
                                    <i class="ti ti-wallet"></i>
                                    {{ $transfer->receiver_wallet_id }}
                                </span>
                            </td>

                            <td data-label="Amount">
                                <span class="ad-amount">
                                    $ {{ number_format((float)$transfer->amount, 2) }}
                                </span>
                            </td>

                            <td data-label="Note">
                                <div class="ad-note">
                                    {{ $transfer->note ?: '-' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="ad-empty">No wallet transfers found.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ad-pagination">
                <div class="ad-pagination-info">
                    Showing {{ $transfers->firstItem() ?? 0 }}–{{ $transfers->lastItem() ?? 0 }} of {{ $transfers->total() }}
                </div>

                {{ $transfers->appends(request()->query())->links() }}
            </div>
        </section>
    </main>
</div>

<script>
document.querySelectorAll('.ad-auto-filter').forEach(function(el){
    el.addEventListener('change',function(){
        document.getElementById('adFilterForm').submit();
    });
});

var adSearchTimer=null;
var adSearch=document.getElementById('adSearch');

if(adSearch){
    adSearch.addEventListener('keyup',function(){
        clearTimeout(adSearchTimer);

        adSearchTimer=setTimeout(function(){
            document.getElementById('adFilterForm').submit();
        },500);
    });
}
</script>
@endsection
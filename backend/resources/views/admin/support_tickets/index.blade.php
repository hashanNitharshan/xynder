@extends('layouts.admin', ['title' => 'Help & Support Tickets'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#0B0E11;
    --hero:#181A20;
    --surface:#181A20;
    --surface-alt:#1E2329;
    --input:#0B0E11;
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

.hs-page{
    margin:-24px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding-bottom:60px;
}

.hs-hero{
    background:var(--hero);
    padding:65px 85px 120px;
    position:relative;
    overflow:hidden;
}

.hs-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.hs-hero-inner{
    position:relative;
    z-index:2;
    max-width:680px;
}

.hs-eyebrow{
    color:var(--yellow);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.hs-title-main{
    font-size:34px;
    line-height:1.15;
    font-weight:800;
    margin:0 0 18px;
    color:#fff;
}

.hs-title-main span{
    color:var(--yellow);
    display:block;
}

.hs-subtitle-main{
    color:var(--muted);
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    max-width:580px;
}

.hs-wrap{
    position:relative;
    z-index:5;
    margin:-82px 45px 0;
}

.hs-kpis{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:28px;
}

.hs-kpi{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:6px;
    padding:20px;
    min-height:118px;
    position:relative;
    overflow:hidden;
}

.hs-kpi::after{
    content:"";
    position:absolute;
    right:-38px;
    top:-38px;
    width:115px;
    height:115px;
    border-radius:50%;
    background:rgba(240,185,11,.12);
}

.hs-kpi-icon{
    width:42px;
    height:42px;
    border-radius:50%;
    background:rgba(240,185,11,.12);
    color:var(--yellow);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    margin-bottom:14px;
}

.hs-kpi-label{
    color:var(--muted);
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.06em;
}

.hs-kpi-value{
    font-size:22px;
    font-weight:800;
    margin-top:6px;
    color:#fff;
}

.hs-card{
    background:var(--surface);
    border:1.5px solid var(--yellow);
    border-radius:7px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.hs-card-head{
    background:var(--surface-alt);
    border-bottom:1px solid var(--border);
    padding:18px 22px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.hs-title{
    font-size:15px;
    font-weight:800;
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
    color:#fff;
}

.hs-title i{
    color:var(--yellow);
}

.hs-subtitle{
    font-size:12px;
    color:var(--muted);
    margin-top:5px;
    font-weight:700;
}

.hs-filters{
    display:grid;
    grid-template-columns:1fr 1fr 2fr 1fr auto;
    align-items:center;
    gap:10px;
    padding:16px 22px;
    border-bottom:1px solid var(--border);
    background:var(--surface);
}

.hs-search-wrap{
    position:relative;
}

.hs-search-wrap i{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:var(--muted);
    font-size:16px;
}

.hs-input,
.hs-select{
    width:100%;
    height:42px;
    border:1px solid var(--border);
    border-radius:4px;
    padding:0 13px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:0;
    font-weight:700;
    font-family:inherit;
}

.hs-input{
    padding-left:36px;
}

.hs-input:focus,
.hs-select:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(240,185,11,.12);
}

.hs-input::placeholder{
    color:var(--muted2);
}

.hs-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    padding:10px 14px;
    min-height:42px;
    border-radius:4px;
    font-size:12px;
    font-weight:900;
    border:0;
    cursor:pointer;
    text-decoration:none;
    white-space:nowrap;
    font-family:inherit;
}

.hs-btn-primary{
    background:var(--yellow);
    color:#0B0E11;
}

.hs-btn-primary:hover{
    background:var(--yellow-dark);
    color:#0B0E11;
}

.hs-btn-ghost{
    background:var(--input);
    color:#fff;
    border:1px solid var(--border);
}

.hs-btn-ghost:hover{
    border-color:var(--yellow);
    color:#fff;
}

.hs-table-wrap{
    width:100%;
    overflow-x:hidden!important;
}

.hs-table{
    width:100%!important;
    min-width:0!important;
    border-collapse:collapse;
    table-layout:fixed;
    font-size:13.5px;
}

.hs-table th{
    background:var(--surface-alt);
    color:var(--muted2);
    font-size:10px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    padding:16px 10px;
    border-bottom:1px solid var(--border);
    text-align:left;
}

.hs-table td{
    padding:20px 10px;
    border-top:1px solid var(--border);
    color:#c9ced3;
    vertical-align:middle;
    font-weight:700;
    word-break:break-word;
    white-space:normal;
    line-height:1.5;
}

.hs-table tr:hover td{
    background:var(--surface-alt);
}

.hs-table th:nth-child(1),.hs-table td:nth-child(1){width:6%;}
.hs-table th:nth-child(2),.hs-table td:nth-child(2){width:12%;}
.hs-table th:nth-child(3),.hs-table td:nth-child(3){width:15%;}
.hs-table th:nth-child(4),.hs-table td:nth-child(4){width:13%;}
.hs-table th:nth-child(5),.hs-table td:nth-child(5){width:17%;}
.hs-table th:nth-child(6),.hs-table td:nth-child(6){width:11%;}
.hs-table th:nth-child(7),.hs-table td:nth-child(7){width:16%;}
.hs-table th:nth-child(8),.hs-table td:nth-child(8){width:10%;}

.hs-ref{
    color:var(--yellow);
    font-family:monospace;
    font-weight:900;
    font-size:12px;
}

.hs-date{
    font-size:12.5px;
    font-weight:800;
    color:#fff;
}

.hs-date .time{
    display:block;
    font-size:11px;
    color:var(--muted);
    font-weight:700;
    margin-top:3px;
}

.hs-name{
    font-weight:900;
    color:#fff;
}

.hs-small{
    font-size:11px;
    color:var(--muted);
    margin-top:3px;
}

.hs-message{
    line-height:1.6;
    color:#d5dade;
    white-space:normal;
}

.hs-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
    white-space:normal;
    text-transform:uppercase;
}

.hs-badge-yellow{
    background:#3b2a09;
    color:var(--gold);
}

.hs-badge-green{
    background:#0d2b1e;
    color:var(--green);
}

.hs-badge-red{
    background:rgba(239,68,68,.12);
    color:#ff9b9b;
}

.hs-badge-gray{
    background:var(--input);
    color:#d5dade;
    border:1px solid var(--border);
}

.hs-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    align-items:center;
}

.hs-actions form{
    margin:0;
}

.hs-actions .hs-select{
    min-width:130px;
}

.hs-pagination{
    padding:16px 22px;
    border-top:1px solid var(--border);
    background:var(--surface-alt);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
}

.hs-pagination-info{
    color:var(--muted);
    font-size:12px;
    font-weight:800;
}

.hs-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:800;
}

@media(max-width:1300px){
    .hs-wrap{margin:-82px 24px 0;}
    .hs-table th{font-size:9px;padding:14px 8px;}
    .hs-table td{font-size:12.5px;padding:17px 8px;}
    .hs-btn{padding:8px 9px;font-size:11px;}
}

@media(max-width:1100px){
    .hs-kpis{grid-template-columns:repeat(2,1fr);}
    .hs-filters{grid-template-columns:1fr 1fr;}
    .hs-table,
    .hs-table thead,
    .hs-table tbody,
    .hs-table th,
    .hs-table td,
    .hs-table tr{
        display:block;
        width:100%!important;
    }

    .hs-table thead{
        display:none;
    }

    .hs-table tr{
        background:var(--surface-alt);
        border:1px solid var(--border);
        border-radius:7px;
        margin:12px;
        padding:16px;
    }

    .hs-table td{
        border-top:0;
        padding:11px 0;
    }

    .hs-table td::before{
        content:attr(data-label);
        display:block;
        color:var(--muted2);
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
        margin-bottom:5px;
    }

    .hs-table td:first-child::before{
        display:none;
    }
}

@media(max-width:768px){
    .hs-page{margin:-16px;}
    .hs-hero{padding:45px 24px 110px;}
    .hs-title-main{font-size:34px;}
    .hs-wrap{margin:-76px 18px 0;}
    .hs-kpis{grid-template-columns:1fr;}
    .hs-filters{grid-template-columns:1fr;}
    .hs-pagination{flex-direction:column;align-items:flex-start;}
}
</style>
@endpush

@section('content')
<div class="hs-page">
    <section class="hs-hero">
        <div class="hs-hero-inner">
           

           

         
        </div>
    </section>

    <main class="hs-wrap">
        <section class="hs-kpis">
            <div class="hs-kpi">
                <div class="hs-kpi-icon"><i class="ti ti-ticket"></i></div>
                <div class="hs-kpi-label">Total Tickets</div>
                <div class="hs-kpi-value">{{ method_exists($tickets, 'total') ? $tickets->total() : $tickets->count() }}</div>
            </div>

            <div class="hs-kpi">
                <div class="hs-kpi-icon"><i class="ti ti-clock"></i></div>
                <div class="hs-kpi-label">Pending</div>
                <div class="hs-kpi-value">{{ $stats['pending'] ?? $tickets->where('status', 'pending')->count() }}</div>
            </div>

            <div class="hs-kpi">
                <div class="hs-kpi-icon"><i class="ti ti-circle-check"></i></div>
                <div class="hs-kpi-label">Resolved</div>
                <div class="hs-kpi-value">{{ $stats['resolved'] ?? $tickets->where('status', 'resolved')->count() }}</div>
            </div>

            <div class="hs-kpi">
                <div class="hs-kpi-icon"><i class="ti ti-checklist"></i></div>
                <div class="hs-kpi-label">Done</div>
                <div class="hs-kpi-value">{{ $stats['done'] ?? $tickets->where('status', 'done')->count() }}</div>
            </div>
        </section>

        <section class="hs-card">
            <div class="hs-card-head">
                <div>
                    <h2 class="hs-title">
                        <i class="ti ti-headset"></i>
                        Support Tickets
                    </h2>
                    <div class="hs-subtitle">Search, filter, and update support ticket status.</div>
                </div>
            </div>

            <form id="hsFilterForm" method="GET" action="{{ route('admin.support-tickets.index') }}" class="hs-filters">
                <select name="per_page" class="hs-select hs-auto-filter">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>Show 10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>Show 25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>Show 50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>Show 100</option>
                </select>

                <select name="sort" class="hs-select hs-auto-filter">
                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Latest First</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                </select>

                <div class="hs-search-wrap">
                    <i class="ti ti-search"></i>
                    <input id="hsSearch"
                           type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="hs-input"
                           placeholder="Search user, email, phone, message...">
                </div>

                <select name="status" class="hs-select hs-auto-filter">
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                    <option value="done" @selected(request('status') === 'done')>Done</option>
                </select>

                <a href="{{ route('admin.support-tickets.index') }}" class="hs-btn hs-btn-ghost">
                    <i class="ti ti-refresh"></i>
                    Reset
                </a>
            </form>

            <div class="hs-table-wrap">
                <table class="hs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td data-label="#">
                                <span class="hs-ref">#{{ $ticket->id }}</span>
                            </td>

                            <td data-label="Date">
                                <div class="hs-date">
                                    {{ $ticket->created_at ? $ticket->created_at->format('d M, Y') : '—' }}
                                    <span class="time">{{ $ticket->created_at ? $ticket->created_at->format('h:i A') : '' }}</span>
                                </div>
                            </td>

                            <td data-label="User">
                                @if($ticket->user)
                                    <div class="hs-name">{{ $ticket->user->name }}</div>
                                    <div class="hs-small">{{ $ticket->user->phone ?? '-' }}</div>
                                    <div class="hs-small">{{ ucfirst($ticket->user->role) }}</div>
                                @else
                                    <span class="hs-small">—</span>
                                @endif
                            </td>

                            <td data-label="Name">
                                <div class="hs-name">{{ $ticket->name }}</div>
                            </td>

                            <td data-label="Email">{{ $ticket->email }}</td>

                            <td data-label="Status">
                                @if($ticket->status === 'pending')
                                    <span class="hs-badge hs-badge-yellow">
                                        <i class="ti ti-clock"></i>
                                        Pending
                                    </span>
                                @elseif($ticket->status === 'resolved')
                                    <span class="hs-badge hs-badge-green">
                                        <i class="ti ti-circle-check"></i>
                                        Resolved
                                    </span>
                                @elseif($ticket->status === 'done')
                                    <span class="hs-badge hs-badge-green">
                                        <i class="ti ti-check"></i>
                                        Done
                                    </span>
                                @else
                                    <span class="hs-badge hs-badge-gray">{{ ucfirst($ticket->status) }}</span>
                                @endif
                            </td>

                            <td data-label="Message">
                                <div class="hs-message">{{ $ticket->message }}</div>
                            </td>

                            <td data-label="Action">
                                <form method="POST" action="{{ route('admin.support-tickets.status', $ticket) }}">
                                    @csrf

                                    <div class="hs-actions">
                                        <select name="status" class="hs-select">
                                            <option value="pending" @selected($ticket->status === 'pending')>Pending</option>
                                            <option value="resolved" @selected($ticket->status === 'resolved')>Resolved</option>
                                            <option value="done" @selected($ticket->status === 'done')>Done</option>
                                        </select>

                                        <button class="hs-btn hs-btn-primary" type="submit">
                                            <i class="ti ti-device-floppy"></i>
                                            Update
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="hs-empty">No support tickets found.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="hs-pagination">
                <div class="hs-pagination-info">
                    Showing {{ $tickets->firstItem() ?? 0 }}–{{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() ?? $tickets->count() }}
                </div>
                {{ $tickets->appends(request()->query())->links() }}
            </div>
        </section>
    </main>
</div>

<script>
document.querySelectorAll('.hs-auto-filter').forEach(function(select) {
    select.addEventListener('change', function() {
        var form = document.getElementById('hsFilterForm');
        if (form) {
            form.submit();
        }
    });
});

var hsSearchTimer = null;
var hsSearch = document.getElementById('hsSearch');

if (hsSearch) {
    hsSearch.addEventListener('keyup', function() {
        clearTimeout(hsSearchTimer);

        hsSearchTimer = setTimeout(function() {
            var form = document.getElementById('hsFilterForm');
            if (form) {
                form.submit();
            }
        }, 500);
    });
}
</script>
@endsection
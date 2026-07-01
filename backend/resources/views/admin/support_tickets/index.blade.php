
@extends('layouts.admin', ['title' => 'Help & Support Tickets'])

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

.hs-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
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
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.hs-title-main{
    font-size:46px;
    line-height:1.12;
    font-weight:900;
    margin:0 0 18px;
}

.hs-title-main span{
    color:var(--red);
    display:block;
}

.hs-subtitle-main{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    max-width:580px;
}

.hs-wrap{
    position:relative;
    z-index:5;
    margin:-82px 85px 0;
}

.hs-card{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.hs-card-head{
    background:var(--panel);
    border-bottom:1px solid var(--line);
    padding:18px 22px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.hs-title{
    font-size:18px;
    font-weight:900;
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
}

.hs-title i{color:var(--red)}

.hs-subtitle{
    font-size:12px;
    color:var(--muted);
    margin-top:5px;
    font-weight:700;
}

.hs-filters{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    padding:16px 22px;
    border-bottom:1px solid var(--line);
    background:var(--box);
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
    height:42px;
    border:1px solid var(--line);
    border-radius:4px;
    padding:0 13px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:0;
    font-weight:700;
}

.hs-input{
    width:320px;
    padding-left:36px;
}

.hs-select{
    width:145px;
}

.hs-input:focus,
.hs-select:focus{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(232,25,44,.12);
}

.hs-input::placeholder{
    color:var(--muted);
}

.hs-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    padding:10px 14px;
    border-radius:4px;
    font-size:13px;
    font-weight:900;
    border:0;
    cursor:pointer;
    text-decoration:none;
    white-space:nowrap;
}

.hs-btn-red{
    background:var(--red);
    color:#fff;
}

.hs-btn-red:hover{
    background:var(--red2);
    color:#fff;
}

.hs-btn-ghost{
    background:var(--input);
    color:#fff;
    border:1px solid var(--line);
}

.hs-btn-ghost:hover{
    border-color:var(--red);
    color:#fff;
}

.hs-table-wrap{
    overflow-x:auto;
}

.hs-table{
    width:100%;
    min-width:1050px;
    border-collapse:collapse;
    font-size:13px;
}

.hs-table th{
    background:var(--panel);
    color:var(--muted2);
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    padding:14px 16px;
    border-bottom:1px solid var(--line);
    text-align:left;
    white-space:nowrap;
}

.hs-table td{
    padding:15px 16px;
    border-top:1px solid var(--line);
    color:#c9ced3;
    vertical-align:middle;
    font-weight:700;
}

.hs-table tr:hover td{
    background:#30363a;
}

.hs-ref{
    color:var(--red);
    font-family:monospace;
    font-weight:900;
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
    max-width:380px;
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
    white-space:nowrap;
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
    background:#3a1018;
    color:#ff6b7b;
}

.hs-badge-gray{
    background:var(--input);
    color:#d5dade;
    border:1px solid var(--line);
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
    width:135px;
}

.hs-pagination{
    padding:16px 22px;
    border-top:1px solid var(--line);
    background:var(--panel);
}

.hs-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:800;
}

@media(max-width:1100px){
    .hs-wrap{margin:-82px 24px 0}
}

@media(max-width:768px){
    .hs-page{margin:-16px}
    .hs-hero{padding:45px 24px 110px}
    .hs-title-main{font-size:34px}
    .hs-wrap{margin:-76px 18px 0}
    .hs-input,
    .hs-select,
    .hs-search-wrap{
        width:100%;
    }
    .hs-actions .hs-select{
        width:100%;
    }
}
</style>
@endpush

@section('content')

<div class="hs-page">

    <section class="hs-hero">
        <div class="hs-hero-inner">
          

            <h1 class="hs-title-main">
                Help &
                <span>Support Tickets</span>
            </h1>

        
        </div>
    </section>

    <main class="hs-wrap">

        <section class="hs-card">
            <div class="hs-card-head">
                
            </div>

            <form id="hsFilterForm" method="GET" action="{{ route('admin.support-tickets.index') }}" class="hs-filters">
                <select name="per_page" class="hs-select hs-auto-filter">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>Show 10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>Show 25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>Show 50</option>
                </select>

                <div class="hs-search-wrap">
                    <i class="ti ti-search"></i>
                    <input id="hsSearch"
                           type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="hs-input"
                           placeholder="Search user, email, message...">
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
                            <td>
                                <span class="hs-ref">#{{ $ticket->id }}</span>
                            </td>

                            <td>
                                @if($ticket->user)
                                    <div class="hs-name">{{ $ticket->user->name }}</div>
                                    <div class="hs-small">{{ $ticket->user->phone ?? '-' }}</div>
                                    <div class="hs-small">{{ ucfirst($ticket->user->role) }}</div>
                                @else
                                    <span class="hs-small">—</span>
                                @endif
                            </td>

                            <td>
                                <div class="hs-name">{{ $ticket->name }}</div>
                            </td>

                            <td>{{ $ticket->email }}</td>

                            <td>
                                @if($ticket->status === 'pending')
                                    <span class="hs-badge hs-badge-yellow">Pending</span>
                                @elseif($ticket->status === 'resolved')
                                    <span class="hs-badge hs-badge-green">Resolved</span>
                                @elseif($ticket->status === 'done')
                                    <span class="hs-badge hs-badge-green">Done</span>
                                @else
                                    <span class="hs-badge hs-badge-gray">{{ ucfirst($ticket->status) }}</span>
                                @endif
                            </td>

                            <td>
                                <div class="hs-message">{{ $ticket->message }}</div>
                            </td>

                            <td>
                                <form method="POST" action="{{ route('admin.support-tickets.status', $ticket) }}">
                                    @csrf

                                    <div class="hs-actions">
                                        <select name="status" class="hs-select">
                                            <option value="pending" @selected($ticket->status === 'pending')>Pending</option>
                                            <option value="resolved" @selected($ticket->status === 'resolved')>Resolved</option>
                                            <option value="done" @selected($ticket->status === 'done')>Done</option>
                                        </select>

                                        <button class="hs-btn hs-btn-red" type="submit">
                                            <i class="ti ti-device-floppy"></i>
                                            Update
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="hs-empty">No support tickets found.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="hs-pagination">
                {{ $tickets->appends(request()->query())->links() }}
            </div>
        </section>

    </main>
</div>

<script>
document.querySelectorAll('.hs-auto-filter').forEach(function(select) {
    select.addEventListener('change', function() {
        document.getElementById('hsFilterForm').submit();
    });
});

var hsSearchTimer = null;
var hsSearch = document.getElementById('hsSearch');

if (hsSearch) {
    hsSearch.addEventListener('keyup', function() {
        clearTimeout(hsSearchTimer);

        hsSearchTimer = setTimeout(function() {
            document.getElementById('hsFilterForm').submit();
        }, 500);
    });
}
</script>

@endsection


@extends('layouts.admin', ['title' => 'Help & Support Tickets'])

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
    --yellow:#facc15;
    --blue:#3b82f6;
    --red:#ef4444;
    --green:#22c55e;
    --page:#020617;
    --card:#0f172a;
    --border:#1e293b;
    --input:#111827;
    --text:#f8fafc;
    --muted:#94a3b8;
    --muted2:#cbd5e1;
    --radius:10px;
    --shadow:0 1px 3px rgba(0,0,0,.45);
}

.hs * {
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

.hs-page {
    background:var(--page);
    padding:20px;
    min-height:100vh;
    color:var(--text);
}

.hs-card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    overflow:hidden;
}

.hs-card-head {
    padding:14px 16px;
    border-bottom:1px solid var(--border);
}

.hs-title {
    font-size:16px;
    font-weight:800;
    margin:0;
}

.hs-subtitle {
    font-size:12px;
    color:var(--muted);
    margin-top:3px;
}

.hs-filters {
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
    padding:10px 16px;
    border-bottom:1px solid var(--border);
    background:#020617;
}

.hs-input,
.hs-select {
    height:34px;
    border:1px solid #334155;
    border-radius:8px;
    padding:0 10px;
    font-size:13px;
    background:var(--input);
    color:var(--text);
    outline:none;
}

.hs-input {
    width:280px;
}

.hs-select {
    width:135px;
}

.hs-input::placeholder {
    color:var(--muted);
}

.hs-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:5px;
    padding:7px 13px;
    border-radius:8px;
    font-size:12px;
    font-weight:700;
    border:none;
    cursor:pointer;
    text-decoration:none;
    white-space:nowrap;
}

.hs-btn:hover {
    opacity:.88;
}

.hs-btn-ghost {
    background:#020617;
    color:var(--muted2);
    border:1px solid var(--border);
}

.hs-table-wrap {
    overflow-x:auto;
}

.hs-table {
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

.hs-table th {
    background:#020617;
    color:var(--muted);
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    padding:10px 14px;
    border-bottom:1px solid var(--border);
    text-align:left;
    white-space:nowrap;
}

.hs-table td {
    padding:11px 14px;
    border-bottom:1px solid var(--border);
    color:var(--muted2);
    vertical-align:middle;
}

.hs-table tr:hover td {
    background:#111827;
}

.hs-name {
    font-weight:800;
    color:var(--text);
}

.hs-small {
    font-size:11px;
    color:var(--muted);
    margin-top:2px;
}

.hs-message {
    max-width:360px;
    line-height:1.5;
}

.hs-badge {
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:3px 9px;
    border-radius:20px;
    font-size:11px;
    font-weight:700;
    white-space:nowrap;
}

.hs-badge::before {
    content:'';
    width:6px;
    height:6px;
    border-radius:50%;
}

.hs-badge-yellow { background:rgba(250,204,21,.16); color:#fde68a; }
.hs-badge-yellow::before { background:#facc15; }

.hs-badge-green { background:rgba(34,197,94,.16); color:#86efac; }
.hs-badge-green::before { background:#22c55e; }

.hs-badge-blue { background:rgba(59,130,246,.18); color:#93c5fd; }
.hs-badge-blue::before { background:#3b82f6; }

.hs-actions {
    display:flex;
    gap:6px;
    flex-wrap:wrap;
    align-items:center;
}

.hs-update-btn {
    background:var(--yellow);
    color:#111827;
}

.hs-pagination {
    padding:12px 16px;
    border-top:1px solid var(--border);
}

@media(max-width:768px) {
    .hs-input,
    .hs-select {
        width:100%;
    }
}
</style>

<div class="hs">
<div class="hs-page">

    <div class="hs-card">
        <div class="hs-card-head">
            <h2 class="hs-title">Help & Support Messages</h2>
            <div class="hs-subtitle">Manage user support tickets and update status.</div>
        </div>

        <form id="hsFilterForm" method="GET" action="{{ route('admin.support-tickets.index') }}" class="hs-filters">
            <select name="per_page" class="hs-select hs-auto-filter">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>Show 10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>Show 25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>Show 50</option>
            </select>

            <input
                id="hsSearch"
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="hs-input"
                placeholder="Search user, email, message..."
            >

            <select name="status" class="hs-select hs-auto-filter">
                <option value="">All Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                <option value="done" @selected(request('status') === 'done')>Done</option>
            </select>

            <a href="{{ route('admin.support-tickets.index') }}" class="hs-btn hs-btn-ghost">
                ↺ Reset
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
                            <td>{{ $ticket->id }}</td>

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
                                @else
                                    <span class="hs-badge hs-badge-blue">Done</span>
                                @endif
                            </td>

                            <td class="hs-message">{{ $ticket->message }}</td>

                            <td>
                                <form method="POST" action="{{ route('admin.support-tickets.status', $ticket) }}" style="margin:0;">
                                    @csrf

                                    <div class="hs-actions">
                                        <select name="status" class="hs-select" style="width:130px;">
                                            <option value="pending" @selected($ticket->status === 'pending')>Pending</option>
                                            <option value="resolved" @selected($ticket->status === 'resolved')>Resolved</option>
                                            <option value="done" @selected($ticket->status === 'done')>Done</option>
                                        </select>

                                        <button class="hs-btn hs-update-btn" type="submit">
                                            Update
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:var(--muted);padding:40px;">
                                No support tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="hs-pagination">
            {{ $tickets->appends(request()->query())->links() }}
        </div>
    </div>

</div>
</div>

<script>
document.querySelectorAll('.hs-auto-filter').forEach(function(select) {
    select.addEventListener('change', function() {
        document.getElementById('hsFilterForm').submit();
    });
});

let hsSearchTimer;
const hsSearch = document.getElementById('hsSearch');

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
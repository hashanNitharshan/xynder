@extends('layouts.admin', ['title' => 'Settings'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#0B0E11;
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

.vs-page{
    margin:-24px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding-bottom:60px;
}

.vs-hero{
    background:var(--surface);
    padding:65px 85px 120px;
    position:relative;
    overflow:hidden;
}

.vs-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.vs-hero-inner{
    position:relative;
    z-index:2;
    max-width:720px;
}

.vs-eyebrow{
    color:var(--yellow);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.vs-title-main{
    font-size:34px;
    line-height:1.15;
    font-weight:800;
    margin:0 0 18px;
    color:#fff;
}

.vs-title-main span{
    color:var(--yellow);
    display:block;
}

.vs-subtitle-main{
    color:var(--muted);
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    max-width:590px;
}

.vs-wrap{
    position:relative;
    z-index:5;
    margin:-82px 45px 0;
}

.vs-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:28px;
}

.vs-stat{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:6px;
    padding:20px;
    min-height:118px;
    position:relative;
    overflow:hidden;
}

.vs-stat::after{
    content:"";
    position:absolute;
    right:-38px;
    top:-38px;
    width:115px;
    height:115px;
    border-radius:50%;
    background:rgba(240,185,11,.12);
}

.vs-stat-icon{
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

.vs-stat-label{
    color:var(--muted);
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.06em;
}

.vs-stat-value{
    font-size:22px;
    font-weight:800;
    margin-top:6px;
    color:#fff;
}

.vs-card{
    background:var(--surface);
    border:1.5px solid var(--yellow);
    border-radius:7px;
    overflow:hidden;
    box-shadow:var(--shadow);
}

.vs-card-head{
    background:var(--surface-alt);
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    padding:18px 22px;
}

.vs-title{
    margin:0;
    font-size:15px;
    font-weight:800;
    display:flex;
    align-items:center;
    gap:9px;
    color:#fff;
}

.vs-title i{
    color:var(--yellow);
    font-size:18px;
}

.vs-subtitle{
    margin-top:5px;
    color:var(--muted);
    font-size:12px;
    font-weight:700;
}

.vs-table-wrap{
    width:100%;
    overflow-x:auto;
}

.vs-table{
    width:100%;
    min-width:980px;
    border-collapse:collapse;
    font-size:13px;
}

.vs-table th{
    background:var(--surface-alt);
    color:var(--muted2);
    font-size:10px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
    text-align:left;
    padding:14px 16px;
    border-bottom:1px solid var(--border);
    white-space:nowrap;
}

.vs-table td{
    padding:15px 16px;
    border-top:1px solid var(--border);
    color:#c9ced3;
    vertical-align:middle;
    font-weight:700;
}

.vs-table tr:hover td{
    background:var(--surface-alt);
}

.vs-ref{
    color:var(--yellow);
    font-family:monospace;
    font-weight:900;
    font-size:12px;
}

.vs-name{
    font-weight:900;
    color:#fff;
}

.vs-small{
    font-size:11px;
    color:var(--muted);
    margin-top:3px;
    font-weight:700;
}

.vs-role{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    white-space:nowrap;
    background:var(--input);
    color:#d5dade;
    border:1px solid var(--border);
}

.vs-role.merchant{
    background:#3b2a09;
    color:var(--gold);
    border-color:#624912;
}

.vs-link{
    display:inline-flex;
    align-items:center;
    gap:5px;
    color:var(--yellow);
    text-decoration:none;
    font-size:12px;
    font-weight:900;
    margin-top:5px;
}

.vs-link:hover{
    color:var(--gold);
}

.vs-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    white-space:nowrap;
}

.vs-badge-green{
    background:#0d2b1e;
    color:var(--green);
}

.vs-badge-yellow{
    background:#3b2a09;
    color:var(--gold);
}

.vs-btn{
    border:0;
    border-radius:4px;
    padding:10px 14px;
    font-size:12px;
    font-weight:900;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    white-space:nowrap;
    text-decoration:none;
    font-family:inherit;
}

.vs-btn-green{
    background:var(--green);
    color:#052e16;
}

.vs-btn-red{
    background:var(--red);
    color:#fff;
}

.vs-btn-green:hover,
.vs-btn-red:hover{
    transform:translateY(-1px);
    opacity:.92;
}

.vs-pagination{
    padding:16px 22px;
    border-top:1px solid var(--border);
    background:var(--surface-alt);
}

.vs-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:800;
}

@media(max-width:1300px){
    .vs-wrap{margin:-82px 24px 0;}
    .vs-table th{font-size:9px;padding:12px 10px;}
    .vs-table td{font-size:12px;padding:13px 10px;}
}

@media(max-width:1100px){
    .vs-stats{grid-template-columns:repeat(2,1fr);}
    .vs-table{min-width:0;}
    .vs-table thead{display:none;}
    .vs-table,
    .vs-table tbody,
    .vs-table tr,
    .vs-table td{display:block;width:100%;}
    .vs-table tr{
        background:var(--surface-alt);
        border:1px solid var(--border);
        border-radius:7px;
        margin:12px;
        padding:14px;
    }
    .vs-table td{
        border-top:0;
        padding:9px 0;
        white-space:normal;
    }
    .vs-table td::before{
        content:attr(data-label);
        display:block;
        color:var(--muted2);
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
        margin-bottom:5px;
    }
}

@media(max-width:768px){
    .vs-page{margin:-16px;}
    .vs-hero{padding:45px 24px 110px;}
    .vs-title-main{font-size:34px;}
    .vs-wrap{margin:-76px 18px 0;}
    .vs-stats{grid-template-columns:1fr;}
}
</style>
@endpush

@section('content')
<div class="vs-page">
    <section class="vs-hero">
        <div class="vs-hero-inner">
            <div class="vs-eyebrow">BITXNOW ADMIN</div>

            <h1 class="vs-title-main">
                User Verification
                <span>Settings</span>
            </h1>

            <div class="vs-subtitle-main">
                Verify or unverify client and merchant accounts, review Aadhaar details,
                and manage KYC approval status from one secure admin page.
            </div>
        </div>
    </section>

    <main class="vs-wrap">
        <section class="vs-stats">
            <div class="vs-stat">
                <div class="vs-stat-icon"><i class="ti ti-users"></i></div>
                <div class="vs-stat-label">Total Users</div>
                <div class="vs-stat-value">{{ $users->total() ?? $users->count() }}</div>
            </div>

            <div class="vs-stat">
                <div class="vs-stat-icon"><i class="ti ti-shield-check"></i></div>
                <div class="vs-stat-label">Verified</div>
                <div class="vs-stat-value">{{ $verifiedCount ?? 0 }}</div>
            </div>

            <div class="vs-stat">
                <div class="vs-stat-icon"><i class="ti ti-clock"></i></div>
                <div class="vs-stat-label">Not Verified</div>
                <div class="vs-stat-value">{{ $unverifiedCount ?? 0 }}</div>
            </div>

            <div class="vs-stat">
                <div class="vs-stat-icon"><i class="ti ti-building-store"></i></div>
                <div class="vs-stat-label">Merchants</div>
                <div class="vs-stat-value">{{ $merchantCount ?? 0 }}</div>
            </div>
        </section>

        <section class="vs-card">
            <div class="vs-card-head">
                <div>
                    <h2 class="vs-title">
                        <i class="ti ti-shield-check"></i>
                        User Verification Settings
                    </h2>
                    <div class="vs-subtitle">Admin can verify or unverify client and merchant accounts here.</div>
                </div>
            </div>

            <div class="vs-table-wrap">
                <table class="vs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aadhaar</th>
                            <th>Verification</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td data-label="#">
                                <span class="vs-ref">#{{ $user->id }}</span>
                            </td>

                            <td data-label="User">
                                <div class="vs-name">{{ $user->name }}</div>
                                <div class="vs-small">{{ $user->phone ?? 'No phone' }}</div>
                            </td>

                            <td data-label="Email">{{ $user->email }}</td>

                            <td data-label="Role">
                                <span class="vs-role {{ $user->role === 'merchant' ? 'merchant' : '' }}">
                                    <i class="ti {{ $user->role === 'merchant' ? 'ti-building-store' : 'ti-user' }}"></i>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td data-label="Aadhaar">
                                {{ $user->aadhaar ?? '—' }}

                                @if($user->aadhaar_photo)
                                    <br>
                                    <a href="{{ asset('storage/'.$user->aadhaar_photo) }}" target="_blank" class="vs-link">
                                        <i class="ti ti-photo"></i>
                                        View Aadhaar Photo
                                    </a>
                                @endif
                            </td>

                            <td data-label="Verification">
                                @if($user->is_verified)
                                    <span class="vs-badge vs-badge-green">
                                        <i class="ti ti-circle-check"></i>
                                        Verified
                                    </span>
                                @else
                                    <span class="vs-badge vs-badge-yellow">
                                        <i class="ti ti-clock"></i>
                                        Not Verified
                                    </span>
                                @endif
                            </td>

                            <td data-label="Action">
                                <form method="POST" action="{{ route('admin.settings.toggle-verification', $user) }}" style="margin:0;">
                                    @csrf

                                    @if($user->is_verified)
                                        <button type="submit" class="vs-btn vs-btn-red" onclick="return confirm('Mark this user as unverified?')">
                                            <i class="ti ti-x"></i>
                                            Mark Unverified
                                        </button>
                                    @else
                                        <button type="submit" class="vs-btn vs-btn-green" onclick="return confirm('Verify this user?')">
                                            <i class="ti ti-check"></i>
                                            Verify User
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="vs-empty">No users found.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="vs-pagination">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </section>
    </main>
</div>
@endsection

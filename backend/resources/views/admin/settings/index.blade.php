
@extends('layouts.admin', ['title' => 'Settings'])

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

.vs-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:60px;
}

.vs-hero{
    background:var(--hero);
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
    max-width:680px;
}

.vs-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.vs-title-main{
    font-size:46px;
    line-height:1.12;
    font-weight:900;
    margin:0 0 18px;
}

.vs-title-main span{
    color:var(--red);
    display:block;
}

.vs-subtitle-main{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
    max-width:580px;
}

.vs-wrap{
    position:relative;
    z-index:5;
    margin:-82px 85px 0;
}

.vs-card{
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:7px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.vs-card-head{
    background:var(--panel);
    border-bottom:1px solid var(--line);
    padding:18px 22px;
}

.vs-title{
    margin:0;
    font-size:18px;
    font-weight:900;
    display:flex;
    align-items:center;
    gap:9px;
}

.vs-title i{
    color:var(--red);
}

.vs-subtitle{
    margin-top:6px;
    color:var(--muted);
    font-size:13px;
    font-weight:700;
}

.vs-table-wrap{
    overflow-x:auto;
}

.vs-table{
    width:100%;
    min-width:980px;
    border-collapse:collapse;
}

.vs-table th{
    background:var(--panel);
    color:var(--muted2);
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.05em;
    text-align:left;
    padding:14px 16px;
    border-bottom:1px solid var(--line);
    font-weight:900;
    white-space:nowrap;
}

.vs-table td{
    padding:15px 16px;
    border-top:1px solid var(--line);
    color:#c9ced3;
    vertical-align:middle;
    font-weight:700;
}

.vs-table tr:hover td{
    background:#30363a;
}

.vs-ref{
    color:var(--red);
    font-family:monospace;
    font-weight:900;
}

.vs-name{
    font-weight:900;
    color:#fff;
}

.vs-small{
    font-size:11px;
    color:var(--muted);
    margin-top:3px;
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
    border:1px solid var(--line);
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
    color:var(--red);
    text-decoration:none;
    font-size:12px;
    font-weight:900;
    margin-top:5px;
}

.vs-link:hover{
    color:#ff6b7b;
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
    font-size:13px;
    font-weight:900;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:7px;
    white-space:nowrap;
}

.vs-btn-green{
    background:var(--green);
    color:#052e16;
}

.vs-btn-red{
    background:var(--red);
    color:#fff;
}

.vs-btn-red:hover{
    background:var(--red2);
}

.vs-btn-green:hover{
    opacity:.9;
}

.vs-pagination{
    padding:16px 22px;
    border-top:1px solid var(--line);
    background:var(--panel);
}

.vs-empty{
    text-align:center;
    padding:45px;
    color:var(--muted);
    font-weight:800;
}

@media(max-width:1100px){
    .vs-wrap{
        margin:-82px 24px 0;
    }
}

@media(max-width:768px){
    .vs-page{
        margin:-16px;
    }

    .vs-hero{
        padding:45px 24px 110px;
    }

    .vs-title-main{
        font-size:34px;
    }

    .vs-wrap{
        margin:-76px 18px 0;
    }
}
</style>
@endpush

@section('content')

<div class="vs-page">

    <section class="vs-hero">
        <div class="vs-hero-inner">
            <div class="vs-eyebrow">Xynder Wallet Admin</div>

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

        <section class="vs-card">
            <div class="vs-card-head">
                <h2 class="vs-title">
                    <i class="ti ti-shield-check"></i>
                    User Verification Settings
                </h2>

                <div class="vs-subtitle">
                    Admin can verify or unverify client and merchant accounts here.
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
                            <td>
                                <span class="vs-ref">#{{ $user->id }}</span>
                            </td>

                            <td>
                                <div class="vs-name">{{ $user->name }}</div>
                                <div class="vs-small">{{ $user->phone ?? 'No phone' }}</div>
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>
                                <span class="vs-role {{ $user->role === 'merchant' ? 'merchant' : '' }}">
                                    <i class="ti {{ $user->role === 'merchant' ? 'ti-building-store' : 'ti-user' }}"></i>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td>
                                {{ $user->aadhaar ?? '—' }}

                                @if($user->aadhaar_photo)
                                    <br>
                                    <a href="{{ asset('storage/'.$user->aadhaar_photo) }}"
                                       target="_blank"
                                       class="vs-link">
                                        <i class="ti ti-photo"></i>
                                        View Aadhaar Photo
                                    </a>
                                @endif
                            </td>

                            <td>
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

                            <td>
                                <form method="POST"
                                      action="{{ route('admin.settings.toggle-verification', $user) }}"
                                      style="margin:0;">
                                    @csrf

                                    @if($user->is_verified)
                                        <button type="submit"
                                                class="vs-btn vs-btn-red"
                                                onclick="return confirm('Mark this user as unverified?')">
                                            <i class="ti ti-x"></i>
                                            Mark Unverified
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="vs-btn vs-btn-green"
                                                onclick="return confirm('Verify this user?')">
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


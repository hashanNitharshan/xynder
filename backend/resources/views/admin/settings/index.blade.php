@extends('layouts.admin', ['title' => 'Settings'])

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

.vs *{
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

.vs-page{
    background:var(--page);
    padding:20px;
    min-height:100vh;
    color:var(--text);
}

.vs-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    overflow:hidden;
    box-shadow:var(--shadow);
}

.vs-card-head{
    padding:16px;
    border-bottom:1px solid var(--border);
}

.vs-title{
    margin:0;
    font-size:18px;
    font-weight:800;
}

.vs-subtitle{
    margin-top:5px;
    color:var(--muted);
    font-size:13px;
}

.vs-table-wrap{
    overflow-x:auto;
}

.vs-table{
    width:100%;
    border-collapse:collapse;
}

.vs-table th{
    background:#020617;
    color:var(--muted);
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.05em;
    text-align:left;
    padding:12px 14px;
    border-bottom:1px solid var(--border);
}

.vs-table td{
    padding:14px;
    border-bottom:1px solid var(--border);
    color:var(--muted2);
    vertical-align:middle;
}

.vs-table tr:hover td{
    background:#111827;
}

.vs-name{
    font-weight:700;
    color:var(--text);
}

.vs-small{
    font-size:12px;
    color:var(--muted);
    margin-top:2px;
}

.vs-link{
    color:var(--yellow);
    text-decoration:none;
    font-size:12px;
    font-weight:700;
}

.vs-link:hover{
    text-decoration:underline;
}

.vs-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:4px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
}

.vs-badge::before{
    content:'';
    width:6px;
    height:6px;
    border-radius:50%;
}

.vs-badge-green{
    background:rgba(34,197,94,.15);
    color:#86efac;
}

.vs-badge-green::before{
    background:#22c55e;
}

.vs-badge-yellow{
    background:rgba(250,204,21,.15);
    color:#fde68a;
}

.vs-badge-yellow::before{
    background:#facc15;
}

.vs-btn{
    border:none;
    border-radius:8px;
    padding:8px 14px;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
}

.vs-btn-green{
    background:var(--green);
    color:#052e16;
}

.vs-btn-red{
    background:var(--red);
    color:white;
}

.vs-btn:hover{
    opacity:.9;
}

.vs-pagination{
    padding:16px;
}
</style>

<div class="vs">
<div class="vs-page">

    <div class="vs-card">

        <div class="vs-card-head">
            <h2 class="vs-title">User Verification Settings</h2>
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

                        <td>{{ $user->id }}</td>

                        <td>
                            <div class="vs-name">
                                {{ $user->name }}
                            </div>

                            <div class="vs-small">
                                {{ $user->phone ?? 'No phone' }}
                            </div>
                        </td>

                        <td>
                            {{ $user->email }}
                        </td>

                        <td>
                            {{ ucfirst($user->role) }}
                        </td>

                        <td>

                            {{ $user->aadhaar ?? '—' }}

                            @if($user->aadhaar_photo)

                                <br>

                                <a
                                    href="{{ asset('storage/'.$user->aadhaar_photo) }}"
                                    target="_blank"
                                    class="vs-link"
                                >
                                    View Aadhaar Photo
                                </a>

                            @endif

                        </td>

                        <td>

                            @if($user->is_verified)

                                <span class="vs-badge vs-badge-green">
                                    Verified
                                </span>

                            @else

                                <span class="vs-badge vs-badge-yellow">
                                    Not Verified
                                </span>

                            @endif

                        </td>

                        <td>

                            <form
                                method="POST"
                                action="{{ route('admin.settings.toggle-verification',$user) }}"
                            >
                                @csrf

                                @if($user->is_verified)

                                    <button
                                        type="submit"
                                        class="vs-btn vs-btn-red"
                                        onclick="return confirm('Mark this user as unverified?')"
                                    >
                                        Mark Unverified
                                    </button>

                                @else

                                    <button
                                        type="submit"
                                        class="vs-btn vs-btn-green"
                                        onclick="return confirm('Verify this user?')"
                                    >
                                        Verify User
                                    </button>

                                @endif

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">
                            No users found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="vs-pagination">
            {{ $users->appends(request()->query())->links() }}
        </div>

    </div>

</div>
</div>

@endsection
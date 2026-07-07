@extends('layouts.admin', ['title' => 'My Profile'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#0B0E11;
    --surface:#181A20;
    --surface-soft:#1E2329;
    --border:#2B3139;

    --blue:#F0B90B;
    --blue-dark:#C99400;
    --blue-soft:rgba(240,185,11,.12);

    --green:#0ecb81;
    --green-soft:rgba(14,203,129,.12);

    --amber:#F0B90B;
    --amber-soft:rgba(240,185,11,.12);

    --red:#ef4444;
    --text:#ffffff;
    --muted:#848E9C;
    --muted2:#5e6673;

    --shadow:0 18px 45px rgba(0,0,0,.35);
}

*{box-sizing:border-box}

.xprof-page{
    margin:-28px;
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
    padding:24px 36px 48px;
}

/* Top bar */
.xprof-topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:16px;
    margin-bottom:20px;
    background:radial-gradient(circle at 92% 0%,rgba(240,185,11,.20),transparent 38%),linear-gradient(135deg,#181A20,#0B0E11);
    border:1px solid var(--border);
    border-radius:16px;
    padding:18px 20px;
    box-shadow:var(--shadow);
}

.xprof-topbar-left{display:flex;align-items:center;gap:12px}

.xprof-topbar-icon{
    width:40px;height:40px;border-radius:10px;
    background:var(--blue-soft);color:var(--blue);
    display:flex;align-items:center;justify-content:center;font-size:19px;
    border:1px solid rgba(240,185,11,.35);
}

.xprof-topbar-left h1{margin:0;font-size:19px;font-weight:800;letter-spacing:-.2px;color:#fff}
.xprof-topbar-left small{display:block;color:var(--muted);font-size:12px;font-weight:600;margin-top:1px}

.xprof-topbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}

.xprof-pill{
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 13px;border-radius:9px;font-size:12px;font-weight:700;
    border:1px solid var(--border);background:var(--surface);color:var(--muted);
}

.xprof-pill.verified{background:var(--green-soft);border-color:#CDEFDA;color:var(--green)}
.xprof-pill.pending{background:var(--amber-soft);border-color:#F5E2BB;color:var(--amber)}
.xprof-pill.locked{background:var(--amber-soft);border-color:rgba(240,185,11,.35);color:var(--amber)}

.xprof-btn-save{
    display:inline-flex;align-items:center;gap:8px;
    border:0;border-radius:9px;background:var(--blue);color:#fff;
    padding:10px 18px;font-size:13px;font-weight:700;cursor:pointer;
    box-shadow:0 10px 24px rgba(240,185,11,.20);
    transition:.15s;
}

.xprof-btn-save:hover{background:var(--blue-dark)}
.xprof-btn-save[disabled]{opacity:.45;cursor:not-allowed;box-shadow:none}

/* Layout */
.xprof-wrap{
    display:grid;
    grid-template-columns:320px minmax(0,1fr);
    gap:20px;
    align-items:start;
}

.xprof-card{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    box-shadow:var(--shadow);
    overflow:hidden;
}

.xprof-card:hover{
    border-color:rgba(240,185,11,.42);
}

/* Left column */
.xprof-left{display:flex;flex-direction:column;gap:20px}

.xprof-card-head{
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    flex-wrap:wrap;
}

.xprof-card-head h3{margin:0;font-size:14px;font-weight:800}
.xprof-card-head p{margin:3px 0 0;font-size:12px;color:var(--muted);font-weight:600}

.xprof-card-body{padding:20px}

.xprof-photo-frame{
    position:relative;
    width:100%;
    aspect-ratio:1/1;
    border-radius:12px;
    overflow:hidden;
    background:radial-gradient(circle at 80% 0%,rgba(240,185,11,.24),transparent 38%),linear-gradient(135deg,#1E2329,#0B0E11);
    display:flex;align-items:center;justify-content:center;
    margin-bottom:14px;
    border:1px solid var(--border);
}

.xprof-photo-frame img{width:100%;height:100%;object-fit:cover}

.xprof-photo-frame .ph-initial{
    font-size:52px;font-weight:800;color:#fff;
    width:100%;height:100%;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#F0B90B,#FFD45A,#C99400); color:#0B0E11;
}

.xprof-photo-remove{
    position:absolute;top:10px;right:10px;
    width:28px;height:28px;border-radius:50%;
    background:rgba(16,24,40,.55);color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-size:14px;cursor:pointer;border:0;
}

.xprof-upload-btn{
    display:block;width:100%;text-align:center;
    padding:11px;border-radius:9px;border:1px solid var(--border);
    background:var(--surface-soft);color:var(--text);
    font-size:13px;font-weight:700;cursor:pointer;position:relative;overflow:hidden;
}

.xprof-upload-btn input[type=file]{
    position:absolute;inset:0;opacity:0;cursor:pointer;
}

.xprof-upload-btn:hover{background:var(--surface-soft)}

.xprof-upload-btn.disabled{
    opacity:.45;
    cursor:not-allowed;
    pointer-events:none;
}

.xprof-divider{height:1px;background:var(--border);margin:18px 0}

.xprof-field{margin-bottom:14px}
.xprof-field:last-child{margin-bottom:0}

.xprof-field label{
    display:block;font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:7px;
}

.xprof-field input,.xprof-field textarea,.xprof-field select{
    width:100%;background:var(--surface);border:1px solid var(--border);
    color:var(--text);border-radius:9px;padding:10px 12px;outline:none;
    font-size:13.5px;font-weight:500;font-family:inherit;transition:.15s;
}

.xprof-field input:focus,.xprof-field textarea:focus{
    border-color:var(--blue);box-shadow:0 0 0 3px rgba(59,109,240,.12);
}

.xprof-field input:disabled,.xprof-field textarea:disabled{background:var(--surface-soft);color:var(--muted2);cursor:not-allowed}

.xprof-field .xprof-readonly-hint{
    color:var(--muted2);font-weight:600;font-size:11.5px;margin-left:6px;
}

.xprof-btn-secondary{
    width:100%;padding:11px;border-radius:9px;border:1px solid var(--border);
    background:var(--blue);color:#0B0E11;font-size:13px;font-weight:700;cursor:pointer;
}

.xprof-btn-secondary:hover{background:var(--blue-dark)}

.xprof-mini-doc{
    display:flex;align-items:center;gap:10px;
    border:1px solid var(--border);border-radius:10px;padding:10px;
    margin-bottom:10px;
}

.xprof-mini-doc:last-child{margin-bottom:0}

.xprof-mini-doc .thumb{
    width:44px;height:44px;border-radius:8px;overflow:hidden;flex-shrink:0;
    background:var(--surface-soft);display:flex;align-items:center;justify-content:center;
    color:var(--muted2);font-size:18px;border:1px solid var(--border);
}

.xprof-mini-doc .thumb img{width:100%;height:100%;object-fit:cover}

.xprof-mini-doc .info{flex:1;min-width:0}
.xprof-mini-doc .info .t{font-size:12.5px;font-weight:700;color:var(--text)}
.xprof-mini-doc .info .s{font-size:11px;color:var(--muted);font-weight:600}

.xprof-mini-doc .chg{
    flex-shrink:0;position:relative;overflow:hidden;
    font-size:11.5px;font-weight:700;color:var(--blue);
    padding:6px 10px;border-radius:7px;border:1px solid rgba(240,185,11,.28);background:var(--blue-soft);
    cursor:pointer;
}

.xprof-mini-doc .chg input{position:absolute;inset:0;opacity:0;cursor:pointer}

.xprof-mini-doc .chg.disabled{
    opacity:.45;
    cursor:not-allowed;
    pointer-events:none;
}

/* Right column */
.xprof-right{display:flex;flex-direction:column;gap:20px}

.xprof-section-title{
    display:flex;align-items:center;gap:9px;
    font-size:15px;font-weight:800;margin:0;
}

.xprof-section-title i{color:var(--blue);font-size:18px}

.xprof-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px 20px}
.xprof-grid.one{grid-template-columns:1fr}

.xprof-footer-note{
    text-align:center;color:var(--muted2);font-size:11.5px;font-weight:600;
    padding-top:8px;
}

.xprof-lock-alert{
    display:flex;
    align-items:center;
    gap:10px;
    background:var(--amber-soft);
    color:var(--amber);
    border:1px solid rgba(240,185,11,.35);
    border-radius:10px;
    padding:13px 15px;
    font-size:13px;
    font-weight:700;
    margin-bottom:20px;
}

/* Aadhaar card section (replaces Payment Details) */
.xprof-aadhaar-row{
    display:flex;
    align-items:center;
    gap:18px;
    flex-wrap:wrap;
}

.xprof-aadhaar-thumb{
    width:110px;
    height:78px;
    border-radius:10px;
    overflow:hidden;
    flex-shrink:0;
    background:var(--surface-soft);
    border:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--muted2);
    font-size:26px;
}

.xprof-aadhaar-thumb img{width:100%;height:100%;object-fit:cover}

.xprof-aadhaar-info{flex:1;min-width:180px}
.xprof-aadhaar-info .t{font-size:13.5px;font-weight:800;color:var(--text)}
.xprof-aadhaar-info .s{font-size:12px;color:var(--muted);font-weight:600;margin-top:2px}

.xprof-aadhaar-upload{
    display:inline-flex;align-items:center;gap:8px;
    border:1px solid var(--border);border-radius:9px;
    background:var(--blue);color:#0B0E11;
    padding:10px 18px;font-size:13px;font-weight:800;cursor:pointer;
    position:relative;overflow:hidden;white-space:nowrap;
    box-shadow:0 10px 24px rgba(240,185,11,.20);
}

.xprof-aadhaar-upload:hover{background:var(--blue-dark)}

.xprof-aadhaar-upload input[type=file]{
    position:absolute;inset:0;opacity:0;cursor:pointer;
}

.xprof-aadhaar-upload.disabled{
    opacity:.45;
    cursor:not-allowed;
    pointer-events:none;
    box-shadow:none;
}

@media(max-width:1100px){
    .xprof-wrap{grid-template-columns:1fr}
}

@media(max-width:640px){
    .xprof-page{margin:-18px;padding:16px 14px 30px}
    .xprof-grid{grid-template-columns:1fr}
    .xprof-topbar{flex-direction:column;align-items:flex-start}
    .xprof-topbar-right{width:100%;justify-content:space-between}
    .xprof-aadhaar-row{flex-direction:column;align-items:flex-start}
    .xprof-aadhaar-upload{width:100%;justify-content:center}
}
</style>
@endpush

@section('content')

@php
    $profileRoute = $user->role === 'merchant'
        ? route('merchant.profile.update')
        : route('client.profile.update');

    $photo = $user->photo_url ?? ($user->photo ? url('/api/storage/'.$user->photo) : null);
    $aadhaarPhoto = $user->aadhaar_photo_url ?? ($user->aadhaar_photo ? url('/api/storage/'.$user->aadhaar_photo) : null);
    $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
    $walletId = $user->wallet_id ?? '—';

    $isLocked = ! empty($user->profile_locked_at);
@endphp

<form method="POST" action="{{ $profileRoute }}" enctype="multipart/form-data" id="profileForm">
    @csrf

    <div class="xprof-page">

        {{-- Top bar --}}
        <div class="xprof-topbar">
            <div class="xprof-topbar-left">
                <div class="xprof-topbar-icon"><i class="ti ti-user-circle"></i></div>
                <div>
                    <h1>My Profile</h1>
                    <small>Account settings &amp; KYC information</small>
                </div>
            </div>

            <div class="xprof-topbar-right">
                @if($user->is_verified)
                    <span class="xprof-pill verified"><i class="ti ti-circle-check"></i> Verified Account</span>
                @else
                    <span class="xprof-pill pending"><i class="ti ti-clock"></i> Verification Pending</span>
                @endif

                @if($isLocked)
                    <span class="xprof-pill locked"><i class="ti ti-lock"></i> Profile Locked</span>
                @endif

                <span class="xprof-pill"><i class="ti ti-wallet"></i> {{ $walletId }}</span>

                @if(! $isLocked)
                    <button type="submit" class="xprof-btn-save"><i class="ti ti-device-floppy"></i> Save Changes</button>
                @else
                    <button type="button" class="xprof-btn-save" disabled title="Profile already updated once">
                        <i class="ti ti-lock"></i> Locked
                    </button>
                @endif
            </div>
        </div>

        @if($isLocked)
            <div class="xprof-lock-alert">
                <i class="ti ti-lock"></i>
                Your profile has already been updated once and is now locked for editing. Contact support if you need a change.
            </div>
        @endif

        @if(session('success'))
            <div class="xprof-lock-alert" style="background:var(--green-soft);color:var(--green);border-color:rgba(14,203,129,.35)">
                <i class="ti ti-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="xprof-lock-alert" style="background:rgba(239,68,68,.12);color:#ff9b9b;border-color:rgba(239,68,68,.35)">
                <i class="ti ti-alert-triangle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="xprof-wrap">

            {{-- LEFT: Account Management --}}
            <aside class="xprof-left">
                <div class="xprof-card">
                    <div class="xprof-card-head">
                        <h3>Account Management</h3>
                        <p>Profile photo &amp; account security</p>
                    </div>

                    <div class="xprof-card-body">
                        <div class="xprof-photo-frame" id="photoPreview">
                            @if($photo)
                                <img src="{{ $photo }}" alt="Profile Photo">
                            @else
                                <div class="ph-initial">{{ $initial }}</div>
                            @endif
                        </div>

                        @if(! $isLocked)
                            <label class="xprof-upload-btn">
                                <i class="ti ti-upload"></i> Upload Photo
                                <input type="file" name="photo" accept="image/*" data-preview="photoPreview">
                            </label>
                        @else
                            <label class="xprof-upload-btn disabled">
                                <i class="ti ti-lock"></i> Photo Locked
                            </label>
                        @endif

                        <div class="xprof-divider"></div>

                        <div class="xprof-field">
                            <label>Full Name</label>
                            <div style="font-size:13.5px;font-weight:700">{{ $user->name ?? '—' }}</div>
                        </div>
                        <div class="xprof-field">
                            <label>Email</label>
                            <div style="font-size:13.5px;font-weight:700;color:var(--muted)">{{ $user->email ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- RIGHT: Profile Information --}}
            <main class="xprof-right">

                <div class="xprof-card">
                    <div class="xprof-card-head">
                        <h3 class="xprof-section-title"><i class="ti ti-id-badge-2"></i> Profile Information</h3>
                    </div>

                    <div class="xprof-card-body">
                        <div class="xprof-grid">
                            <div class="xprof-field">
                                <label>Full Name</label>
                                @if(! $isLocked)
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}">
                                @else
                                    <input type="text" value="{{ $user->name }}" disabled>
                                @endif
                            </div>
                            <div class="xprof-field">
                                <label>Original / Legal Name</label>
                                @if(! $isLocked)
                                    <input type="text" name="original_name" value="{{ old('original_name', $user->original_name) }}">
                                @else
                                    <input type="text" value="{{ $user->original_name }}" disabled>
                                @endif
                            </div>
                            <div class="xprof-field">
                                <label>Phone Number</label>
                                @if(! $isLocked)
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                                @else
                                    <input type="text" value="{{ $user->phone }}" disabled>
                                @endif
                            </div>
                            <div class="xprof-field">
                                <label>Email <span class="xprof-readonly-hint">(cannot change)</span></label>
                                <input type="email" value="{{ $user->email }}" disabled>
                            </div>
                            <div class="xprof-field">
                                <label>Country</label>
                                @if(! $isLocked)
                                    <input type="text" name="country" value="{{ old('country', $user->country) }}">
                                @else
                                    <input type="text" value="{{ $user->country }}" disabled>
                                @endif
                            </div>
                            <div class="xprof-field">
                                <label>State</label>
                                @if(! $isLocked)
                                    <input type="text" name="state" value="{{ old('state', $user->state) }}">
                                @else
                                    <input type="text" value="{{ $user->state }}" disabled>
                                @endif
                            </div>
                        </div>

                        <div class="xprof-grid one" style="margin-top:16px">
                            <div class="xprof-field">
                                <label>Address</label>
                                @if(! $isLocked)
                                    <textarea rows="3" name="address">{{ old('address', $user->address) }}</textarea>
                                @else
                                    <textarea rows="3" disabled>{{ $user->address }}</textarea>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aadhaar Card Photo (replaces Payment Details) --}}
                <div class="xprof-card">
                    <div class="xprof-card-head">
                        <h3 class="xprof-section-title"><i class="ti ti-id"></i> Aadhaar Card</h3>
                        <p>Upload your Aadhaar card photo for KYC verification</p>
                    </div>

                    <div class="xprof-card-body">
                        <div class="xprof-aadhaar-row">
                            <div class="xprof-aadhaar-thumb" id="aadhaarThumb">
                                @if($aadhaarPhoto)
                                    <img src="{{ $aadhaarPhoto }}" alt="Aadhaar Card">
                                @else
                                    <i class="ti ti-id"></i>
                                @endif
                            </div>

                            <div class="xprof-aadhaar-info">
                                <div class="t">Aadhaar Card Photo</div>
                                <div class="s">{{ $aadhaarPhoto ? 'Uploaded' : 'Not uploaded yet' }}</div>
                            </div>

                            @if(! $isLocked)
                                <label class="xprof-aadhaar-upload">
                                    <i class="ti ti-upload"></i> {{ $aadhaarPhoto ? 'Change Photo' : 'Upload Photo' }}
                                    <input type="file" name="aadhaar_photo" accept="image/*" data-preview-img="aadhaarThumb">
                                </label>
                            @else
                                <span class="xprof-aadhaar-upload disabled">
                                    <i class="ti ti-lock"></i> Locked
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="xprof-footer-note">
                    @if(! $isLocked)
                        Only your profile photo and Aadhaar card photo can be updated. Save changes once you're ready.
                    @else
                        Your profile is locked and shown here for reference only.
                    @endif
                </div>
            </main>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
// Live preview for main photo frame
document.querySelectorAll('input[type="file"][data-preview]').forEach(function(input){
    input.addEventListener('change', function(){
        const file = input.files && input.files[0];
        const target = document.getElementById(input.dataset.preview);
        if(!file || !target) return;

        const reader = new FileReader();
        reader.onload = function(e){
            target.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(file);
    });
});

// Live preview for Aadhaar thumbnail
document.querySelectorAll('input[type="file"][data-preview-img]').forEach(function(input){
    input.addEventListener('change', function(){
        const file = input.files && input.files[0];
        const target = document.getElementById(input.dataset.previewImg);
        if(!file || !target) return;

        const reader = new FileReader();
        reader.onload = function(e){
            target.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
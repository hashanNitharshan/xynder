
@extends('layouts.admin', ['title' => 'Transaction Chat'])

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

.chat-page{
    margin:-24px;
    min-height:100vh;
    background:var(--dark);
    color:var(--text);
    font-family:Inter,Arial,sans-serif;
    padding-bottom:50px;
}

.chat-hero{
    background:var(--hero);
    padding:55px 80px 105px;
    position:relative;
    overflow:hidden;
}

.chat-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.07;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.chat-hero-content{
    position:relative;
    z-index:2;
    max-width:650px;
}

.chat-eyebrow{
    color:var(--red);
    font-size:12px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    margin-bottom:14px;
}

.chat-title{
    font-size:44px;
    font-weight:900;
    line-height:1.12;
    margin:0 0 14px;
}

.chat-title span{
    color:var(--red);
    display:block;
}

.chat-sub{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
}

.chat-shell{
    position:relative;
    z-index:5;
    margin:-75px 80px 0;
    display:grid;
    grid-template-columns:370px 1fr;
    height:calc(100dvh - 185px);
    min-height:660px;
    background:var(--box);
    border:1.5px solid var(--red);
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}

.chat-sidebar{
    display:flex;
    flex-direction:column;
    min-height:0;
    background:var(--panel);
    border-right:1px solid var(--line);
}

.chat-sb-head{
    padding:20px;
    border-bottom:1px solid var(--line);
    flex-shrink:0;
}

.chat-sb-title{
    font-size:20px;
    font-weight:900;
    margin-bottom:14px;
    display:flex;
    align-items:center;
    gap:10px;
}

.chat-sb-title i{color:var(--red)}

.chat-filter{
    display:flex;
    gap:8px;
    margin-bottom:12px;
}

.chat-filter select{
    flex:1;
    height:42px;
    background:var(--input);
    border:1px solid var(--line);
    color:#fff;
    border-radius:4px;
    padding:0 12px;
    outline:0;
    font-weight:700;
}

.chat-filter button,
.chat-filter a{
    height:42px;
    border:0;
    text-decoration:none;
    background:var(--red);
    color:#fff;
    border-radius:4px;
    padding:0 13px;
    font-size:12px;
    font-weight:900;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.chat-filter a{
    background:var(--input);
    border:1px solid var(--line);
}

.chat-search{
    height:46px;
    background:var(--input);
    border:1px solid var(--line);
    border-radius:4px;
    display:flex;
    align-items:center;
    gap:10px;
    padding:0 14px;
}

.chat-search i{color:var(--muted2)}

.chat-search input{
    flex:1;
    background:transparent;
    border:0;
    outline:0;
    color:#fff;
    font-size:13px;
    font-weight:700;
}

.chat-list{
    flex:1;
    min-height:0;
    overflow-y:auto;
    padding:12px;
    scrollbar-width:none;
    -ms-overflow-style:none;
}

.chat-list::-webkit-scrollbar{display:none}

.chat-item{
    display:flex;
    align-items:center;
    gap:12px;
    padding:13px;
    border-radius:6px;
    color:#fff;
    text-decoration:none;
    transition:.15s;
    border:1px solid transparent;
}

.chat-item:hover,
.chat-item.active{
    background:#30363a;
    border-color:var(--red);
}

.chat-av{
    width:48px;
    height:48px;
    flex:0 0 48px;
    border-radius:50%;
    background:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-weight:900;
    position:relative;
}

.chat-lock{
    position:absolute;
    right:-4px;
    bottom:-4px;
    background:#3a1018;
    color:#ff6b7b;
    border:1px solid #71313a;
    border-radius:50%;
    width:20px;
    height:20px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:11px;
}

.chat-body{flex:1;min-width:0}

.chat-top{
    display:flex;
    align-items:center;
    gap:8px;
    min-width:0;
}

.chat-name{
    font-size:14px;
    font-weight:900;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.chat-tag{
    flex-shrink:0;
    padding:3px 7px;
    border-radius:20px;
    font-size:10px;
    font-weight:900;
}

.chat-tag.req{background:#3b2a09;color:var(--gold)}
.chat-tag.tra{background:#3a1018;color:#ff6b7b}

.chat-last{
    margin-top:5px;
    color:#9fa5aa;
    font-size:12px;
    font-weight:700;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.chat-meta{
    text-align:right;
    flex-shrink:0;
}

.chat-time{
    color:#747b82;
    font-size:11px;
    font-weight:800;
}

.chat-pager{
    flex-shrink:0;
    padding:12px;
    border-top:1px solid var(--line);
    background:var(--panel);
}

.chat-main{
    min-width:0;
    min-height:0;
    display:flex;
    flex-direction:column;
    background:var(--box);
}

.chat-head{
    flex-shrink:0;
    padding:16px 20px;
    background:var(--panel);
    border-bottom:1px solid var(--line);
    display:flex;
    align-items:center;
    gap:13px;
}

.chat-head-av{
    width:48px;
    height:48px;
    flex:0 0 48px;
    border-radius:50%;
    background:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
}

.chat-head-info{
    flex:1;
    min-width:0;
}

.chat-head-name{
    font-size:16px;
    font-weight:900;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.chat-head-sub{
    margin-top:4px;
    color:#9fa5aa;
    font-size:12px;
    font-weight:800;
    line-height:1.6;
}

.chat-online{color:var(--green)}
.chat-offline{color:#9ca3af}
.chat-admin-pill{color:var(--gold);font-weight:900}

.chat-back{
    color:#fff;
    text-decoration:none;
    background:var(--input);
    border:1px solid var(--line);
    padding:10px 14px;
    border-radius:4px;
    font-size:13px;
    font-weight:900;
}

.chat-back:hover{
    background:var(--red);
    border-color:var(--red);
}

.chat-ref{
    flex-shrink:0;
    align-self:center;
    margin:12px auto 4px;
    background:#3a1018;
    border:1px solid rgba(232,25,44,.35);
    color:#ff6b7b;
    border-radius:20px;
    padding:7px 15px;
    font-size:12px;
    font-family:monospace;
    font-weight:900;
}

.chat-alert{
    flex-shrink:0;
    margin:8px 18px;
    padding:13px 16px;
    border-radius:4px;
    font-size:13px;
    font-weight:800;
}

.chat-alert.ok{background:#0d2b1e;color:var(--green);border:1px solid #1a4a35}
.chat-alert.err{background:#3a1018;color:#ff6b7b;border:1px solid #71313a}

.chat-msg-area{
    flex:1;
    min-height:0;
    overflow-y:auto;
    padding:18px 20px 12px;
    display:flex;
    flex-direction:column;
    gap:10px;
    scrollbar-width:none;
    -ms-overflow-style:none;
}

.chat-msg-area::-webkit-scrollbar{display:none}

.chat-date{
    text-align:center;
    color:#747b82;
    font-size:11px;
    font-weight:900;
    margin:8px 0 4px;
}

.chat-row{
    display:flex;
    align-items:flex-end;
    gap:9px;
}

.chat-row.mine{
    flex-direction:row-reverse;
}

.chat-msg-av{
    width:32px;
    height:32px;
    flex:0 0 32px;
    border-radius:50%;
    background:var(--input);
    border:1px solid var(--line);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:11px;
    font-weight:900;
    color:#fff;
}

.chat-bubble{
    max-width:66%;
    padding:11px 14px;
    border-radius:12px;
    font-size:13px;
    line-height:1.55;
    word-break:break-word;
    font-weight:700;
}

.chat-bubble.them{
    background:var(--input);
    color:#e5e7eb;
    border:1px solid var(--line);
    border-bottom-left-radius:3px;
}

.chat-bubble.mine{
    background:var(--red);
    color:#fff;
    border-bottom-right-radius:3px;
}

.chat-bubble.admin{
    border:1px solid var(--gold);
}

.chat-bubble-name{
    color:#ff6b7b;
    font-size:11px;
    font-weight:900;
    margin-bottom:5px;
}

.chat-bubble-time{
    text-align:right;
    font-size:10px;
    margin-top:7px;
    color:rgba(255,255,255,.55);
    font-weight:800;
}

.chat-img{
    max-width:220px;
    border-radius:8px;
    margin-top:8px;
    display:block;
}

.chat-file{
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:8px;
    background:rgba(255,255,255,.10);
    color:#fff;
    padding:9px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:12px;
    font-weight:900;
}

.chat-input-wrap{
    flex-shrink:0;
    padding:13px 18px 16px;
    background:var(--panel);
    border-top:1px solid var(--line);
}

.chat-file-preview{
    display:none;
    align-items:center;
    justify-content:space-between;
    margin-bottom:9px;
    padding:9px 12px;
    border-radius:4px;
    background:#3a1018;
    color:#ff6b7b;
    border:1px solid #71313a;
    font-size:12px;
    font-weight:900;
}

.chat-file-preview.show{display:flex}

.chat-input-row{
    min-height:54px;
    display:flex;
    align-items:center;
    gap:9px;
    background:var(--input);
    border:1px solid var(--line);
    border-radius:4px;
    padding:7px;
}

.chat-input-row select{
    width:165px;
    background:var(--panel);
    border:1px solid var(--line);
    outline:0;
    color:#fff;
    border-radius:4px;
    padding:10px;
    font-size:12px;
    font-weight:800;
}

.chat-attach{
    width:40px;
    height:40px;
    border-radius:4px;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    color:#aeb4ba;
    font-size:21px;
}

.chat-attach:hover{
    background:#30363a;
    color:#fff;
}

.chat-input-row input[type=text]{
    flex:1;
    background:transparent;
    border:0;
    outline:0;
    color:#fff;
    font-size:13px;
    font-weight:700;
    padding:0 6px;
}

.chat-input-row input[type=text]::placeholder{color:#747b82}

.chat-send{
    height:40px;
    border:0;
    border-radius:4px;
    background:var(--red);
    color:#fff;
    padding:0 18px;
    font-size:13px;
    font-weight:900;
    cursor:pointer;
}

.chat-send:hover{background:var(--red2)}

@media(max-width:900px){
    .chat-page{margin:-16px}
    .chat-hero{padding:45px 24px 95px}
    .chat-title{font-size:34px}
    .chat-shell{
        margin:-70px 20px 0;
        grid-template-columns:1fr;
        height:calc(100dvh - 150px);
    }
    .chat-sidebar{display:none}
    .chat-bubble{max-width:82%}
    .chat-input-row{flex-wrap:wrap}
    .chat-input-row select,
    .chat-input-row input[type=text]{
        width:100%;
        flex:unset;
    }
}
</style>
@endpush

@section('content')
@php
    $isTransfer = !is_null($conversation->wallet_transfer_id);

    $chatNo = $isTransfer
        ? 'TRA'.str_pad($conversation->wallet_transfer_id, 9, '0', STR_PAD_LEFT)
        : 'TNS'.str_pad($conversation->wallet_request_id, 9, '0', STR_PAD_LEFT);

    $userOneName = $conversation->userOne->name ?? 'Deleted User';
    $userTwoName = $conversation->userTwo->name ?? 'Deleted User';

    $userOneOnline = $conversation->userOne && $conversation->userOne->is_online;
    $userTwoOnline = $conversation->userTwo && $conversation->userTwo->is_online;

    $locked = method_exists($conversation, 'isLocked') ? $conversation->isLocked() : false;
@endphp

<div class="chat-page">

    <section class="chat-hero">
        <div class="chat-hero-content">
            <div class="chat-eyebrow">Xynder Wallet Admin</div>

            <h1 class="chat-title">
                Transaction
                <span>Chat</span>
            </h1>

            <div class="chat-sub">
                Admin chat view for {{ $isTransfer ? 'wallet transfer' : 'wallet request' }} reference {{ $chatNo }}.
            </div>
        </div>
    </section>

    <section class="chat-shell">
        <aside class="chat-sidebar">
            <div class="chat-sb-head">
                <div class="chat-sb-title">
                    <i class="ti ti-messages"></i>
                    Transaction Chats
                </div>

                <form method="GET" action="{{ route('admin.chats.index') }}" class="chat-filter">
                    <select name="type">
                        <option value="">All</option>
                        <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfers</option>
                        <option value="request" {{ request('type') === 'request' ? 'selected' : '' }}>Requests</option>
                    </select>

                    <button type="submit">
                        <i class="ti ti-filter"></i>
                    </button>

                    <a href="{{ route('admin.chats.index') }}">
                        <i class="ti ti-refresh"></i>
                    </a>
                </form>

                <div class="chat-search">
                    <i class="ti ti-search"></i>
                    <input type="text" id="chatSearch" placeholder="Search user or reference...">
                </div>
            </div>

            <div class="chat-list" id="chatList">
                @forelse($conversations as $c)
                    @php
                        $cIsTransfer = !is_null($c->wallet_transfer_id);

                        $cChatNo = $cIsTransfer
                            ? 'TRA'.str_pad($c->wallet_transfer_id, 9, '0', STR_PAD_LEFT)
                            : 'TNS'.str_pad($c->wallet_request_id, 9, '0', STR_PAD_LEFT);

                        $cUserOne = $c->userOne->name ?? 'Deleted User';
                        $cUserTwo = $c->userTwo->name ?? 'Deleted User';
                        $cLast = $c->messages->first();
                        $cLocked = method_exists($c, 'isLocked') ? $c->isLocked() : false;
                    @endphp

                    <a href="{{ route('admin.chats.show', $c) }}"
                       class="chat-item {{ $c->id == $conversation->id ? 'active' : '' }}"
                       data-name="{{ strtolower($cUserOne.' '.$cUserTwo) }}"
                       data-ref="{{ strtolower($cChatNo) }}">

                        <div class="chat-av">
                            {{ strtoupper(substr($cUserOne, 0, 1)) }}

                            @if($cLocked)
                                <span class="chat-lock">
                                    <i class="ti ti-lock"></i>
                                </span>
                            @endif
                        </div>

                        <div class="chat-body">
                            <div class="chat-top">
                                <div class="chat-name">{{ $cUserOne }} ↔ {{ $cUserTwo }}</div>

                                <span class="chat-tag {{ $cIsTransfer ? 'tra' : 'req' }}">
                                    {{ $cIsTransfer ? 'TRA' : 'REQ' }}
                                </span>
                            </div>

                            <div class="chat-last">
                                @if($cLocked)
                                    Chat locked
                                @elseif($cLast)
                                    {{ $cLast->sender->name ?? 'User' }}:
                                    {{ $cLast->message
                                        ? \Illuminate\Support\Str::limit($cLast->message, 32)
                                        : 'Attachment: '.$cLast->attachment_name }}
                                @else
                                    No messages yet
                                @endif
                            </div>
                        </div>

                        <div class="chat-meta">
                            <div class="chat-time">
                                {{ $c->updated_at?->isToday()
                                    ? $c->updated_at->format('H:i')
                                    : $c->updated_at?->format('M d') }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="text-align:center;color:#9fa5aa;padding:42px 20px;font-weight:800;">
                        No chats found.
                    </div>
                @endforelse
            </div>

            @if($conversations->hasPages())
                <div class="chat-pager">
                    {{ $conversations->links() }}
                </div>
            @endif
        </aside>

        <main class="chat-main">
            <div class="chat-head">
                <div class="chat-head-av">
                    {{ strtoupper(substr($userOneName, 0, 1)) }}
                </div>

                <div class="chat-head-info">
                    <div class="chat-head-name">
                        {{ $userOneName }} ↔ {{ $userTwoName }}
                    </div>

                    <div class="chat-head-sub">
                        <span class="{{ $userOneOnline ? 'chat-online' : 'chat-offline' }}">
                            ● {{ $userOneName }} {{ $userOneOnline ? 'Online' : 'Offline' }}
                        </span>
                        ·
                        <span class="{{ $userTwoOnline ? 'chat-online' : 'chat-offline' }}">
                            ● {{ $userTwoName }} {{ $userTwoOnline ? 'Online' : 'Offline' }}
                        </span>
                        ·
                        <span class="chat-admin-pill">ADMIN VIEW</span>
                    </div>
                </div>

                <a href="{{ route('admin.chats.index') }}" class="chat-back">
                    <i class="ti ti-arrow-left"></i>
                    Back
                </a>
            </div>

            <div class="chat-ref">
                {{ $isTransfer ? 'Wallet Transfer' : 'Wallet Request' }} · {{ $chatNo }}
            </div>

            @if(session('success'))
                <div class="chat-alert ok">
                    <i class="ti ti-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="chat-alert err">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <div class="chat-msg-area" id="msgArea">
                @forelse(
                    $conversation->messages
                        ->sortBy('created_at')
                        ->groupBy(fn($m) => $m->created_at?->toDateString())
                    as $date => $msgs
                )
                    <div class="chat-date">
                        {{ \Carbon\Carbon::parse($date)->isToday()
                            ? 'Today'
                            : \Carbon\Carbon::parse($date)->format('M d, Y') }}
                    </div>

                    @foreach($msgs as $msg)
                        @php
                            $isMe = $msg->sender_id == $user->id;
                            $isAdminMsg = $msg->sender && $msg->sender->role === 'admin';
                        @endphp

                        <div class="chat-row {{ $isMe ? 'mine' : '' }}">
                            <div class="chat-msg-av">
                                {{ strtoupper(substr($msg->sender->name ?? 'U', 0, 1)) }}
                            </div>

                            <div class="chat-bubble {{ $isMe ? 'mine' : 'them' }} {{ $isAdminMsg ? 'admin' : '' }}">
                                <div class="chat-bubble-name">
                                    {{ $msg->sender->name ?? 'Deleted User' }}
                                    @if($isAdminMsg)
                                        · Admin
                                    @endif
                                </div>

                                @if($msg->message)
                                    <div>{{ $msg->message }}</div>
                                @endif

                                @if($msg->attachment_url)
                                    @if($msg->attachment_type === 'image')
                                        <a href="{{ $msg->attachment_url }}" target="_blank">
                                            <img src="{{ $msg->attachment_url }}" class="chat-img" loading="lazy">
                                        </a>
                                    @else
                                        <a href="{{ $msg->attachment_url }}" target="_blank" class="chat-file">
                                            <i class="ti ti-paperclip"></i>
                                            {{ $msg->attachment_name ?? 'Attachment' }}
                                        </a>
                                    @endif
                                @endif

                                <div class="chat-bubble-time">
                                    {{ $msg->created_at?->format('H:i') }}
                                    @if($isMe && $msg->status)
                                        · {{ strtoupper($msg->status) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div style="text-align:center;color:#9fa5aa;padding:55px 20px;font-weight:800;">
                        No messages yet.
                    </div>
                @endforelse
            </div>

            <div class="chat-input-wrap">
                <div class="chat-file-preview" id="filePreview">
                    <span id="fileName">No file selected</span>

                    <button type="button" onclick="clearFile()"
                            style="background:transparent;border:0;color:#ff6b7b;cursor:pointer;font-size:16px;font-weight:900;">
                        ×
                    </button>
                </div>

                <form method="POST"
                      action="{{ route('admin.chats.send', $conversation) }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="chat-input-row">
                        <select name="receiver_id" required>
                            <option value="{{ $conversation->user_one_id }}">
                                To: {{ $userOneName }}
                            </option>
                            <option value="{{ $conversation->user_two_id }}">
                                To: {{ $userTwoName }}
                            </option>
                        </select>

                        <label for="attachment" class="chat-attach" title="Attach file">
                            <i class="ti ti-paperclip"></i>
                        </label>

                        <input type="file"
                               name="attachment"
                               id="attachment"
                               style="display:none;"
                               accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">

                        <input type="text"
                               name="message"
                               placeholder="Type admin message..."
                               value="{{ old('message') }}"
                               autocomplete="off">

                        <button type="submit" class="chat-send">
                            Send <i class="ti ti-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </section>
</div>

<script>
const msgArea = document.getElementById('msgArea');
if (msgArea) {
    msgArea.scrollTop = msgArea.scrollHeight;
}

document.getElementById('attachment')?.addEventListener('change', function () {
    const preview = document.getElementById('filePreview');
    const name = document.getElementById('fileName');

    if (this.files.length) {
        name.innerText = this.files[0].name;
        preview.classList.add('show');
    }
});

function clearFile() {
    const attachment = document.getElementById('attachment');
    const preview = document.getElementById('filePreview');

    if (attachment) attachment.value = '';
    if (preview) preview.classList.remove('show');
}

document.getElementById('chatSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();

    document.querySelectorAll('.chat-item').forEach(el => {
        el.style.display =
            (!q || el.dataset.name.includes(q) || el.dataset.ref.includes(q))
                ? '' : 'none';
    });
});
</script>
@endsection


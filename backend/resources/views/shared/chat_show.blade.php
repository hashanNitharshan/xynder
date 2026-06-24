@extends('layouts.admin', ['title' => $title ?? 'Chat'])

@push('styles')
<style>
/* ── Reset & Base ─────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

.xyn-wrap{
    display:flex;
    height:calc(100dvh - 120px);
    min-height:520px;
    max-height:calc(100dvh - 120px);
    border-radius:28px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,.07);
    background:#0e1220;
}

/* ── Sidebar ──────────────────────────────────── */
.xyn-sb{
    width:340px;
    flex:0 0 340px;
    background:#0e1220;
    display:flex;
    flex-direction:column;
    border-right:1px solid rgba(255,255,255,.08);
}

.xyn-sb-head{
    flex-shrink:0;
    padding:20px;
    border-bottom:1px solid rgba(255,255,255,.08);
}

.xyn-sb-title{
    font-size:20px;
    font-weight:900;
    color:#fff;
    margin-bottom:14px;
}

.xyn-search{
    display:flex;
    align-items:center;
    gap:8px;
    background:#131929;
    border:1px solid rgba(255,255,255,.08);
    border-radius:15px;
    padding:11px 13px;
}

.xyn-search input{
    background:transparent;
    border:0;
    outline:0;
    color:#fff;
    width:100%;
    font-size:13px;
}

.xyn-search input::placeholder{color:#4b5563;}

/* hidden scrollbar — WhatsApp style */
.xyn-list{
    flex:1;
    min-height:0;
    overflow-y:auto;
    padding:10px;
    scrollbar-width:none;
    -ms-overflow-style:none;
}
.xyn-list::-webkit-scrollbar{display:none;}

.xyn-chat-item{
    display:flex;
    align-items:center;
    gap:11px;
    padding:12px;
    border-radius:17px;
    text-decoration:none;
    transition:.15s;
}

.xyn-chat-item:hover,
.xyn-chat-item.active{background:rgba(124,92,252,.18);}

.xyn-av{
    width:46px;
    height:46px;
    flex:0 0 46px;
    border-radius:15px;
    background:linear-gradient(135deg,#7c5cfc,#06b6d4);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:16px;
    font-weight:900;
    position:relative;
}

.xyn-lock-badge{
    position:absolute;
    right:-4px;
    bottom:-4px;
    background:#1f2937;
    color:#f87171;
    border-radius:7px;
    font-size:10px;
    padding:2px 4px;
    line-height:1;
}

.xyn-body{flex:1;min-width:0;}

.xyn-top{
    display:flex;
    gap:7px;
    align-items:center;
    min-width:0;
}

.xyn-name{
    font-size:14px;
    font-weight:900;
    color:#fff;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.xyn-tag{
    font-size:10px;
    font-weight:900;
    padding:3px 7px;
    border-radius:7px;
    flex-shrink:0;
}
.xyn-tag.req{background:rgba(245,158,11,.14);color:#fbbf24;}
.xyn-tag.tra{background:rgba(124,92,252,.16);color:#a78bfa;}

.xyn-last{
    font-size:12px;
    color:#6b7280;
    margin-top:4px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.xyn-meta{text-align:right;flex-shrink:0;}

.xyn-time{font-size:11px;color:#6b7280;}

.xyn-unread{
    margin-top:6px;
    display:inline-block;
    background:#7c5cfc;
    color:#fff;
    border-radius:999px;
    font-size:11px;
    font-weight:900;
    padding:3px 8px;
}

.xyn-pager{
    flex-shrink:0;
    padding:10px 12px;
    border-top:1px solid rgba(255,255,255,.08);
    background:#0e1220;
    color:#9ca3af;
}
.xyn-pager nav{display:flex;justify-content:center;}
.xyn-pager svg{width:18px;height:18px;}

/* ── Main chat panel ─────────────────────────── */
.xyn-main{
    flex:1;
    min-width:0;
    min-height:0;
    background:#111827;
    display:flex;
    flex-direction:column;
}

.xyn-head{
    flex-shrink:0;
    padding:14px 18px;
    border-bottom:1px solid rgba(255,255,255,.08);
    display:flex;
    align-items:center;
    gap:13px;
    background:#0f172a;
}

.xyn-head-av{
    width:46px;
    height:46px;
    flex:0 0 46px;
    border-radius:15px;
    background:linear-gradient(135deg,#7c5cfc,#06b6d4);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:16px;
    font-weight:900;
}

.xyn-head-info{flex:1;min-width:0;}

.xyn-head-name{
    font-size:15px;
    font-weight:900;
    color:#fff;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.xyn-head-sub{
    font-size:12px;
    color:#6b7280;
    margin-top:3px;
}

.xyn-status-open{color:#34d399;font-weight:900;}
.xyn-status-lock{color:#f87171;font-weight:900;}

.xyn-back-btn{
    flex-shrink:0;
    background:#1f2937;
    color:#9ca3af;
    padding:9px 14px;
    border-radius:12px;
    text-decoration:none;
    font-size:13px;
    font-weight:700;
    transition:.15s;
    border:1px solid rgba(255,255,255,.06);
}
.xyn-back-btn:hover{background:#374151;color:#fff;}

/* reference pill */
.xyn-ref{
    flex-shrink:0;
    align-self:center;
    margin:10px auto 2px;
    background:rgba(124,92,252,.13);
    border:1px solid rgba(124,92,252,.25);
    color:#a78bfa;
    border-radius:999px;
    padding:6px 14px;
    font-size:12px;
    font-family:monospace;
    letter-spacing:.4px;
}

/* alerts */
.xyn-alert-success,
.xyn-alert-error{
    flex-shrink:0;
    margin:8px 16px;
    padding:11px 14px;
    border-radius:13px;
    font-size:13px;
    font-weight:800;
}
.xyn-alert-success{
    background:rgba(16,185,129,.12);
    border:1px solid rgba(16,185,129,.22);
    color:#d1fae5;
}
.xyn-alert-error{
    background:rgba(239,68,68,.12);
    border:1px solid rgba(239,68,68,.22);
    color:#fee2e2;
}

/* message area — hidden scrollbar */
.xyn-msg-area{
    flex:1;
    min-height:0;
    overflow-y:auto;
    padding:18px 18px 10px;
    display:flex;
    flex-direction:column;
    gap:10px;
    scrollbar-width:none;
    -ms-overflow-style:none;
}
.xyn-msg-area::-webkit-scrollbar{display:none;}

.xyn-date{
    text-align:center;
    color:#4b5563;
    font-size:11px;
    margin:6px 0 2px;
}

.xyn-msg-row{
    display:flex;
    gap:9px;
    align-items:flex-end;
}
.xyn-msg-row.mine{flex-direction:row-reverse;}

.xyn-msg-av{
    width:30px;
    height:30px;
    flex:0 0 30px;
    border-radius:10px;
    background:linear-gradient(135deg,#7c5cfc,#06b6d4);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:11px;
    font-weight:900;
}

.xyn-bubble{
    max-width:65%;
    padding:10px 13px;
    border-radius:18px;
    font-size:13px;
    line-height:1.55;
    word-break:break-word;
}

.xyn-bubble.them{
    background:#1f2937;
    color:#e5e7eb;
    border-bottom-left-radius:4px;
}

.xyn-bubble.mine{
    background:linear-gradient(135deg,#7c5cfc,#6d28d9);
    color:#fff;
    border-bottom-right-radius:4px;
}

.xyn-bubble-name{
    font-size:11px;
    color:#a78bfa;
    font-weight:900;
    margin-bottom:4px;
}

.xyn-bubble-time{
    text-align:right;
    font-size:10px;
    margin-top:6px;
    color:rgba(255,255,255,.4);
}

.xyn-img{
    max-width:210px;
    border-radius:12px;
    margin-top:7px;
    display:block;
}

.xyn-file-attach{
    display:flex;
    gap:8px;
    align-items:center;
    margin-top:7px;
    background:rgba(255,255,255,.08);
    padding:9px 12px;
    border-radius:12px;
    color:#fff;
    text-decoration:none;
    font-size:12px;
}
.xyn-file-attach:hover{background:rgba(255,255,255,.14);}

/* input bar */
.xyn-input-wrap{
    flex-shrink:0;
    padding:12px 16px 14px;
    border-top:1px solid rgba(255,255,255,.08);
    background:#0f172a;
}

.xyn-file-preview{
    display:none;
    background:rgba(124,92,252,.12);
    color:#a78bfa;
    padding:8px 12px;
    border-radius:11px;
    margin-bottom:8px;
    font-size:12px;
    align-items:center;
    justify-content:space-between;
}
.xyn-file-preview.show{display:flex;}

.xyn-input-row{
    display:flex;
    gap:9px;
    align-items:center;
    background:#131929;
    border:1px solid rgba(255,255,255,.09);
    border-radius:17px;
    padding:7px;
}

.xyn-input-row input[type=text]{
    flex:1;
    background:transparent;
    border:0;
    outline:0;
    color:#fff;
    padding:9px 6px;
    font-size:13px;
}
.xyn-input-row input[type=text]::placeholder{color:#4b5563;}

.xyn-attach-btn{
    cursor:pointer;
    color:#9ca3af;
    font-size:20px;
    padding:6px;
    line-height:1;
    transition:.15s;
}
.xyn-attach-btn:hover{color:#fff;}

.xyn-send-btn{
    background:#7c5cfc;
    border:0;
    color:#fff;
    border-radius:12px;
    padding:10px 16px;
    font-weight:900;
    font-size:13px;
    cursor:pointer;
    transition:.15s;
    white-space:nowrap;
}
.xyn-send-btn:hover{background:#6d4ef0;}
.xyn-send-btn:active{transform:scale(.96);}

/* lock bar */
.xyn-lock-bar{
    flex-shrink:0;
    margin:0 16px 14px;
    background:rgba(239,68,68,.10);
    border:1px solid rgba(239,68,68,.22);
    color:#f87171;
    padding:13px;
    border-radius:14px;
    text-align:center;
    font-weight:900;
    font-size:13px;
}

/* ── Mobile ───────────────────────────────────── */
@media(max-width:760px){
    .xyn-wrap{
        border-radius:18px;
        height:calc(100dvh - 90px);
        max-height:calc(100dvh - 90px);
    }
    .xyn-sb{display:none;}
    .xyn-bubble{max-width:80%;}
}
</style>
@endpush

@section('content')
@php
    $isTransfer = !is_null($conversation->wallet_transfer_id);

    $chatNo = $isTransfer
        ? 'TRA'.str_pad($conversation->wallet_transfer_id, 9, '0', STR_PAD_LEFT)
        : 'TNS'.str_pad($conversation->wallet_request_id, 9, '0', STR_PAD_LEFT);

    $locked    = $conversation->isLocked();
    $remaining = $conversation->remainingSeconds();

    $sendRoute = $user->role === 'merchant'
        ? route('merchant.chats.send', $conversation)
        : route('client.chats.send', $conversation);

    $backRoute = $user->role === 'merchant'
        ? route('merchant.chats')
        : route('client.chats');

    $sideConversations = $conversations;
@endphp

<div class="xyn-wrap">

    {{-- ── Sidebar ── --}}
    <div class="xyn-sb">
        <div class="xyn-sb-head">
            <div class="xyn-sb-title">Transaction Chats</div>
            <div class="xyn-search">
                <span>🔍</span>
                <input type="text" id="chatSearch" placeholder="Search chat or reference…">
            </div>
        </div>

        <div class="xyn-list" id="chatList">
            @forelse($sideConversations as $c)
                @php
                    $cIsTransfer = !is_null($c->wallet_transfer_id);

                    $cChatNo = $cIsTransfer
                        ? 'TRA'.str_pad($c->wallet_transfer_id, 9, '0', STR_PAD_LEFT)
                        : 'TNS'.str_pad($c->wallet_request_id, 9, '0', STR_PAD_LEFT);

                    $cOther  = $c->user_one_id == $user->id ? $c->userTwo : $c->userOne;
                    $cLast   = $c->messages->first();
                    $cLocked = $c->isLocked();

                    $cUnread = \App\Models\ChatMessage::where('conversation_id', $c->id)
                        ->where('receiver_id', $user->id)
                        ->whereIn('status', ['sent', 'delivered'])
                        ->count();

                    $cRoute = $user->role === 'merchant'
                        ? route('merchant.chats.show', $c)
                        : route('client.chats.show', $c);
                @endphp

                <a href="{{ $cRoute }}"
                   class="xyn-chat-item {{ $c->id == $conversation->id ? 'active' : '' }}"
                   data-name="{{ strtolower($cOther->name ?? '') }}"
                   data-ref="{{ strtolower($cChatNo) }}">

                    <div class="xyn-av">
                        {{ strtoupper(substr($cOther->name ?? 'U', 0, 1)) }}
                        @if($cLocked)
                            <span class="xyn-lock-badge">🔒</span>
                        @endif
                    </div>

                    <div class="xyn-body">
                        <div class="xyn-top">
                            <div class="xyn-name">{{ $cOther->name ?? 'Unknown' }}</div>
                            <span class="xyn-tag {{ $cIsTransfer ? 'tra' : 'req' }}">
                                {{ $cIsTransfer ? 'TRA' : 'REQ' }}
                            </span>
                        </div>

                        <div class="xyn-last">
                            @if($cLocked)
                                🔒 Chat locked
                            @elseif($cLast)
                                {{ $cLast->message
                                    ? \Illuminate\Support\Str::limit($cLast->message, 38)
                                    : '📎 '.$cLast->attachment_name }}
                            @else
                                No messages yet
                            @endif
                        </div>
                    </div>

                    <div class="xyn-meta">
                        <div class="xyn-time">
                            {{ $c->updated_at?->isToday()
                                ? $c->updated_at->format('H:i')
                                : $c->updated_at?->format('M d') }}
                        </div>
                        @if($cUnread > 0)
                            <span class="xyn-unread">{{ $cUnread }}</span>
                        @endif
                    </div>
                </a>

            @empty
                <div style="text-align:center;color:#6b7280;padding:30px;">No chats</div>
            @endforelse
        </div>

        <div class="xyn-pager">
            {{ $sideConversations->links() }}
        </div>
    </div>

    {{-- ── Main chat ── --}}
    <div class="xyn-main">

        {{-- Header --}}
        <div class="xyn-head">
            <div class="xyn-head-av">
                {{ strtoupper(substr($otherUser->name ?? 'U', 0, 1)) }}
            </div>

            <div class="xyn-head-info">
                <div class="xyn-head-name">{{ $otherUser->name ?? 'Unknown' }}</div>
                <div class="xyn-head-sub">
                    {{ strtoupper($otherUser->role ?? '') }} ·
                    @if($locked)
                        <span class="xyn-status-lock">LOCKED</span>
                    @else
                        <span class="xyn-status-open">OPEN</span>
                        @if($remaining > 0)
                            · <span id="timer" data-sec="{{ $remaining }}">
                                {{ floor($remaining / 60) }}:{{ str_pad($remaining % 60, 2, '0', STR_PAD_LEFT) }} left
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <a href="{{ $backRoute }}" class="xyn-back-btn">← Back</a>
        </div>

        {{-- Reference pill --}}
        <div class="xyn-ref">
            {{ $isTransfer ? 'Wallet Transfer' : 'Wallet Request' }} · {{ $chatNo }}
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="xyn-alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="xyn-alert-error">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        {{-- Messages --}}
        <div class="xyn-msg-area" id="msgArea">
            @forelse(
                $conversation->messages
                    ->sortBy('created_at')
                    ->groupBy(fn($m) => $m->created_at?->toDateString())
                as $date => $msgs
            )
                <div class="xyn-date">
                    {{ \Carbon\Carbon::parse($date)->isToday()
                        ? 'Today'
                        : \Carbon\Carbon::parse($date)->format('M d, Y') }}
                </div>

                @foreach($msgs as $msg)
                    @php $isMe = $msg->sender_id == $user->id; @endphp

                    <div class="xyn-msg-row {{ $isMe ? 'mine' : '' }}">
                        <div class="xyn-msg-av">
                            {{ strtoupper(substr($msg->sender->name ?? 'U', 0, 1)) }}
                        </div>

                        <div class="xyn-bubble {{ $isMe ? 'mine' : 'them' }}">
                            @if(!$isMe)
                                <div class="xyn-bubble-name">{{ $msg->sender->name ?? 'User' }}</div>
                            @endif

                            @if($msg->message)
                                <div>{{ $msg->message }}</div>
                            @endif

                            @if($msg->attachment_url)
                                @if($msg->attachment_type === 'image')
                                    <a href="{{ $msg->attachment_url }}" target="_blank">
                                        <img src="{{ $msg->attachment_url }}" class="xyn-img" loading="lazy">
                                    </a>
                                @else
                                    <a href="{{ $msg->attachment_url }}" target="_blank" class="xyn-file-attach">
                                        📎 {{ $msg->attachment_name ?? 'Attachment' }}
                                    </a>
                                @endif
                            @endif

                            <div class="xyn-bubble-time">
                                {{ $msg->created_at?->format('H:i') }}
                                @if($isMe) · {{ strtoupper($msg->status) }} @endif
                            </div>
                        </div>
                    </div>
                @endforeach

            @empty
                <div style="text-align:center;color:#4b5563;padding:50px 20px;">
                    No messages yet. Say hello! 👋
                </div>
            @endforelse
        </div>

        {{-- Lock bar or input --}}
        @if($locked)
            <div class="xyn-lock-bar">🔒 This chat has been locked after 15 minutes.</div>
        @else
            <div class="xyn-input-wrap">
                <div class="xyn-file-preview" id="filePreview">
                    <span id="fileName">No file</span>
                    <button type="button" onclick="clearFile()"
                            style="background:transparent;border:0;color:#f87171;cursor:pointer;font-size:16px;line-height:1;">✕</button>
                </div>

                <form method="POST" action="{{ $sendRoute }}" enctype="multipart/form-data">
                    @csrf
                    <div class="xyn-input-row">
                        <label for="attachment" class="xyn-attach-btn" title="Attach file">📎</label>
                        <input type="file" name="attachment" id="attachment" style="display:none;"
                               accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">

                        <input type="text" name="message" placeholder="Type a message…"
                               value="{{ old('message') }}" autocomplete="off">

                        <button type="submit" class="xyn-send-btn">Send ➤</button>
                    </div>
                </form>
            </div>
        @endif

    </div>{{-- /.xyn-main --}}
</div>{{-- /.xyn-wrap --}}

<script>
// Auto-scroll to latest message
const msgArea = document.getElementById('msgArea');
if (msgArea) msgArea.scrollTop = msgArea.scrollHeight;

// Countdown timer
const timerEl = document.getElementById('timer');
if (timerEl) {
    let sec = parseInt(timerEl.dataset.sec || 0);
    const tick = setInterval(() => {
        if (sec <= 0) { timerEl.innerText = 'Locking…'; clearInterval(tick); return; }
        sec--;
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        timerEl.innerText = m + ':' + (s < 10 ? '0' : '') + s + ' left';
    }, 1000);
}

// File attachment preview
document.getElementById('attachment')?.addEventListener('change', function () {
    const preview = document.getElementById('filePreview');
    const name    = document.getElementById('fileName');
    if (this.files.length) {
        name.innerText = this.files[0].name;
        preview.classList.add('show');
    }
});

function clearFile() {
    document.getElementById('attachment').value = '';
    document.getElementById('filePreview').classList.remove('show');
}

// Sidebar search
document.getElementById('chatSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.xyn-chat-item').forEach(el => {
        el.style.display =
            (!q || el.dataset.name.includes(q) || el.dataset.ref.includes(q))
                ? '' : 'none';
    });
});
</script>
@endsection
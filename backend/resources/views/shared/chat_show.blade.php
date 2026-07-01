@extends('layouts.admin', ['title' => $title ?? 'Transaction Chat'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --dark:#101518;--box:#2b2f32;--panel:#24292d;--input:#1f2428;--line:#3b4248;
    --red:#e8192c;--red2:#c91022;--green:#0ecb81;--gold:#ffc933;
    --text:#fff;--muted:#aeb4ba;--muted2:#747b82;--blue-tick:#53bdeb;
}
*{box-sizing:border-box}
.chat-page{margin:-24px;height:100vh;background:var(--dark);color:var(--text);font-family:Inter,Arial,sans-serif;overflow:hidden}
.chat-shell{height:100vh;background:var(--box);display:flex;flex-direction:column}

.chat-head{height:72px;background:var(--panel);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:12px 18px;flex-shrink:0}
.chat-head-left{display:flex;align-items:center;gap:12px;min-width:0}
.chat-back{width:42px;height:42px;border-radius:50%;background:var(--input);border:1px solid var(--line);color:#fff;text-decoration:none;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.chat-av{width:48px;height:48px;border-radius:50%;background:#3a1018;color:var(--red);display:flex;align-items:center;justify-content:center;font-size:23px;font-weight:900;flex-shrink:0}
.chat-title-wrap{min-width:0}
.chat-title{font-size:16px;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.chat-sub{margin-top:3px;font-size:12px;color:var(--muted);font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.chat-ref{font-family:monospace;color:var(--red);font-weight:900}
.chat-tag{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:900;margin-left:6px}
.chat-tag.req{background:#3b2a09;color:var(--gold)}
.chat-tag.tra{background:#3a1018;color:#ff6b7b}
.chat-status-open{color:var(--green)}
.chat-status-lock{color:#ff6b7b}

.chat-messages{
    flex:1;min-height:0;overflow-y:auto;padding:22px;
    background:radial-gradient(circle at top right,rgba(232,25,44,.06),transparent 35%),var(--dark);
    scrollbar-width:thin;scrollbar-color:#3b4248 transparent;
}
.chat-date-divider{display:flex;align-items:center;justify-content:center;margin:18px 0}
.chat-date-pill{background:var(--panel);border:1px solid var(--line);color:var(--muted);font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;padding:6px 14px;border-radius:20px}

.msg-row{display:flex;margin-bottom:4px}
.msg-row.mine{justify-content:flex-end}
.msg-row + .msg-row{margin-top:2px}
.msg-bubble{max-width:68%;background:var(--input);border:1px solid var(--line);border-radius:12px 12px 12px 3px;padding:9px 11px 7px;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.18)}
.msg-row.mine .msg-bubble{background:var(--red);border-color:var(--red2);border-radius:12px 12px 3px 12px}
.msg-sender{font-size:11px;font-weight:900;color:var(--gold);margin-bottom:4px}
.msg-row.mine .msg-sender{color:rgba(255,255,255,.85)}
.msg-text{font-size:13.5px;line-height:1.5;font-weight:600;white-space:pre-wrap;word-break:break-word}
.msg-img{max-width:230px;border-radius:8px;margin-top:7px;display:block}
.msg-attach{display:flex;align-items:center;gap:8px;margin-top:6px;color:#fff;text-decoration:none;font-weight:800;font-size:12px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:8px;padding:8px 10px}
.msg-meta{display:flex;align-items:center;justify-content:flex-end;gap:4px;margin-top:4px}
.msg-time{font-size:10.5px;color:rgba(255,255,255,.65);font-weight:700}
.msg-row:not(.mine) .msg-time{color:var(--muted)}
.msg-ticks{display:inline-flex;align-items:center;line-height:0}
.msg-ticks svg{width:16px;height:11px;display:block}
.tick-sent path,.tick-delivered path{fill:rgba(255,255,255,.65)}
.tick-read path{fill:var(--blue-tick)}
.chat-empty{height:100%;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--muted);font-weight:900}

.chat-alert{margin:10px 14px 0;padding:10px 12px;border-radius:5px;font-weight:800;font-size:13px}
.chat-alert.ok{background:#0d2b1e;color:var(--green)}
.chat-alert.err{background:#3a1018;color:#ff9aaa}

.chat-form{padding:13px;background:var(--panel);border-top:1px solid var(--line);flex-shrink:0}
.chat-file-preview{display:none;align-items:center;justify-content:space-between;margin-bottom:9px;padding:9px 12px;border-radius:4px;background:#3a1018;color:#ff6b7b;border:1px solid #71313a;font-size:12px;font-weight:900}
.chat-file-preview.show{display:flex}
.chat-form-inner{display:grid;grid-template-columns:1fr 48px 52px;gap:9px;align-items:center}
.chat-input{height:46px;background:var(--input);border:1px solid var(--line);border-radius:22px;color:#fff;padding:0 16px;outline:0;font-weight:800;width:100%}
.chat-input:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(232,25,44,.12)}
.chat-file{display:none}
.chat-file-btn,.chat-send{height:46px;width:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:20px}
.chat-file-btn{background:var(--input);color:#fff;border:1px solid var(--line)}
.chat-file-btn:hover{border-color:var(--red)}
.chat-send{background:var(--red);color:#fff;border:0}
.chat-send:hover{background:var(--red2)}
.chat-locked{padding:16px;background:#3a1018;color:#ff9aaa;text-align:center;font-weight:900;border-top:1px solid #71313a}

@media(max-width:900px){
    .chat-page{margin:-16px}
    .chat-head{height:auto;padding:10px}
    .msg-bubble{max-width:88%}
}
</style>
@endpush

@section('content')
@php
    $isTransfer = !is_null($conversation->wallet_transfer_id);

    $chatNo = $isTransfer
        ? 'TRA'.str_pad($conversation->wallet_transfer_id, 9, '0', STR_PAD_LEFT)
        : 'TNS'.str_pad($conversation->wallet_request_id, 9, '0', STR_PAD_LEFT);

    $locked = method_exists($conversation, 'isLocked') ? $conversation->isLocked() : false;
    $remaining = method_exists($conversation, 'remainingSeconds') ? $conversation->remainingSeconds() : 0;

    $sendRoute = $user->role === 'merchant'
        ? route('merchant.chats.send', $conversation)
        : route('client.chats.send', $conversation);

    $backRoute = $user->role === 'merchant'
        ? route('merchant.chats')
        : route('client.chats');

    $lastDateLabel = null;
@endphp

<div class="chat-page">
    <section class="chat-shell">

        <header class="chat-head">
            <div class="chat-head-left">
               

                <div class="chat-av">
                    {{ strtoupper(substr($otherUser->name ?? 'U', 0, 1)) }}
                </div>

                <div class="chat-title-wrap">
                    <div class="chat-title">{{ $otherUser->name ?? 'Unknown User' }}</div>

                    <div class="chat-sub">
                        {{ strtoupper($otherUser->role ?? 'USER') }} ·
                        <span class="chat-ref">{{ $chatNo }}</span>
                        <span class="chat-tag {{ $isTransfer ? 'tra' : 'req' }}">
                            {{ $isTransfer ? 'TRANSFER' : 'REQUEST' }}
                        </span>

                        @if($locked)
                            · <span class="chat-status-lock">LOCKED</span>
                        @else
                            · <span class="chat-status-open">ACTIVE</span>
                            @if($remaining > 0)
                                · <span id="timer" data-sec="{{ $remaining }}">
                                    {{ floor($remaining / 60) }}:{{ str_pad($remaining % 60, 2, '0', STR_PAD_LEFT) }} left
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div class="chat-alert ok">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="chat-alert err">{{ $errors->first() }}</div>
        @endif

        <main class="chat-messages" id="chatMessages">
            @forelse($conversation->messages->sortBy('created_at') as $message)
                @php
                    $mine = (int)$message->sender_id === (int)$user->id;
                    $msgDate = $message->created_at;

                    if ($msgDate) {
                        if ($msgDate->isToday()) {
                            $dateLabel = 'Today';
                        } elseif ($msgDate->isYesterday()) {
                            $dateLabel = 'Yesterday';
                        } else {
                            $dateLabel = $msgDate->format('d F Y');
                        }
                    } else {
                        $dateLabel = null;
                    }

                    $status = $message->status ?? 'sent';

                    if ($status === 'seen' || $status === 'read') {
                        $tickState = 'read';
                    } elseif ($status === 'delivered') {
                        $tickState = 'delivered';
                    } else {
                        $tickState = 'sent';
                    }
                @endphp

                @if($dateLabel && $dateLabel !== $lastDateLabel)
                    <div class="chat-date-divider">
                        <div class="chat-date-pill">{{ $dateLabel }}</div>
                    </div>
                    @php $lastDateLabel = $dateLabel; @endphp
                @endif

                <div class="msg-row {{ $mine ? 'mine' : '' }}">
                    <div class="msg-bubble">
                        @if(!$mine)
                            <div class="msg-sender">{{ $message->sender->name ?? 'User' }}</div>
                        @endif

                        @if($message->message)
                            <div class="msg-text">{{ $message->message }}</div>
                        @endif

                        @if($message->attachment_url)
                            @if(($message->attachment_type ?? '') === 'image')
                                <a href="{{ $message->attachment_url }}" target="_blank">
                                    <img src="{{ $message->attachment_url }}" class="msg-img" loading="lazy">
                                </a>
                            @else
                                <a class="msg-attach" href="{{ $message->attachment_url }}" target="_blank">
                                    <i class="ti ti-paperclip"></i>
                                    {{ $message->attachment_name ?? 'Attachment' }}
                                </a>
                            @endif
                        @endif

                        <div class="msg-meta">
                            <span class="msg-time">{{ $message->created_at?->format('h:i A') }}</span>

                            @if($mine)
                                <span class="msg-ticks">
                                    @if($tickState === 'sent')
                                        <svg viewBox="0 0 16 11" class="tick-sent">
                                            <path d="M11.1 0.4 4.8 6.7 2.4 4.3 1 5.7l3.8 3.8 7.7-7.7z"/>
                                        </svg>
                                    @elseif($tickState === 'delivered')
                                        <svg viewBox="0 0 20 11" class="tick-delivered">
                                            <path d="M15.1 0.4 8.8 6.7 6.4 4.3 5 5.7l3.8 3.8 7.7-7.7z"/>
                                            <path d="M10.1 0.4 3.8 6.7 1.4 4.3 0 5.7l3.8 3.8 7.7-7.7z"/>
                                        </svg>
                                    @else
                                        <svg viewBox="0 0 20 11" class="tick-read">
                                            <path d="M15.1 0.4 8.8 6.7 6.4 4.3 5 5.7l3.8 3.8 7.7-7.7z"/>
                                            <path d="M10.1 0.4 3.8 6.7 1.4 4.3 0 5.7l3.8 3.8 7.7-7.7z"/>
                                        </svg>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="chat-empty">
                    <div>
                        <i class="ti ti-message-circle" style="font-size:56px;color:var(--red);"></i>
                        <div style="margin-top:12px;">No messages yet. Start the conversation.</div>
                    </div>
                </div>
            @endforelse
        </main>

        @if($locked)
            <div class="chat-locked">
                <i class="ti ti-lock"></i> This chat is locked.
            </div>
        @else
            <form method="POST" action="{{ $sendRoute }}" enctype="multipart/form-data" class="chat-form">
                @csrf

                <div class="chat-file-preview" id="filePreview">
                    <span id="fileName">No file selected</span>
                    <button type="button" onclick="clearFile()" style="background:transparent;border:0;color:#ff6b7b;cursor:pointer;font-size:16px;font-weight:900;">×</button>
                </div>

                <div class="chat-form-inner">
                    <input type="text" name="message" class="chat-input" placeholder="Type a message..." value="{{ old('message') }}" autocomplete="off">

                    <label class="chat-file-btn" title="Attach file">
                        <i class="ti ti-paperclip"></i>
                        <input type="file" name="attachment" id="attachment" class="chat-file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
                    </label>

                    <button type="submit" class="chat-send" title="Send">
                        <i class="ti ti-send"></i>
                    </button>
                </div>
            </form>
        @endif

    </section>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

const timerEl = document.getElementById('timer');
if (timerEl) {
    let sec = parseInt(timerEl.dataset.sec || 0);

    const tick = setInterval(() => {
        if (sec <= 0) {
            timerEl.innerText = 'Locking...';
            clearInterval(tick);
            return;
        }

        sec--;
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        timerEl.innerText = m + ':' + (s < 10 ? '0' : '') + s + ' left';
    }, 1000);
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
</script>
@endsection
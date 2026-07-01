@extends('layouts.admin', ['title' => 'Transaction Chat'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --dark:#101518;--box:#2b2f32;--panel:#24292d;--input:#1f2428;--line:#3b4248;
    --red:#e8192c;--green:#0ecb81;--gold:#ffc933;--text:#fff;--muted:#aeb4ba;--muted2:#747b82;
    --mine:#e8192c;--mine2:#c91022;--them:#1f2428;--blue-tick:#53bdeb;
}
*{box-sizing:border-box}
.chat-page{margin:-24px;height:100vh;background:var(--dark);color:var(--text);font-family:Inter,Arial,sans-serif;overflow:hidden;}
.chat-shell{height:100vh;background:var(--box);display:flex;flex-direction:column;}

.chat-head{height:72px;background:var(--panel);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:12px 18px;flex-shrink:0;}
.chat-head-left{display:flex;align-items:center;gap:12px;min-width:0;}
.chat-back{width:42px;height:42px;border-radius:50%;background:var(--input);border:1px solid var(--line);color:#fff;text-decoration:none;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.chat-av{width:48px;height:48px;border-radius:50%;background:#3a1018;color:var(--red);display:flex;align-items:center;justify-content:center;font-size:23px;font-weight:900;flex-shrink:0;}
.chat-title-wrap{min-width:0;}
.chat-title{font-size:16px;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.chat-sub{margin-top:3px;font-size:12px;color:var(--muted);font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.chat-ref{font-family:monospace;color:var(--red);font-weight:900;}
.chat-tag{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:900;margin-left:6px;}
.chat-tag.req{background:#3b2a09;color:var(--gold);}
.chat-tag.tra{background:#3a1018;color:#ff6b7b;}

.chat-action{color:#fff;text-decoration:none;font-weight:900;font-size:13px;background:var(--input);border:1px solid var(--line);padding:10px 12px;border-radius:5px;}
.chat-action:hover{border-color:var(--red);color:#fff;}

/* ===== MESSAGE AREA ===== */
.chat-messages{
    flex:1;min-height:0;overflow-y:auto;padding:22px;
    background:
        radial-gradient(circle at top right,rgba(232,25,44,.06),transparent 35%),
        var(--dark);
    scrollbar-width:thin;scrollbar-color:#3b4248 transparent;
}

.chat-date-divider{
    display:flex;
    align-items:center;
    justify-content:center;
    margin:18px 0;
}

.chat-date-pill{
    background:var(--panel);
    border:1px solid var(--line);
    color:var(--muted);
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.04em;
    padding:6px 14px;
    border-radius:20px;
}

.msg-row{display:flex;margin-bottom:4px;}
.msg-row.mine{justify-content:flex-end;}
.msg-row + .msg-row{margin-top:2px;}

.msg-bubble{
    max-width:68%;
    background:var(--them);
    border:1px solid var(--line);
    border-radius:12px 12px 12px 3px;
    padding:9px 11px 7px;
    color:#fff;
    box-shadow:0 4px 12px rgba(0,0,0,.18);
    position:relative;
}

.msg-row.mine .msg-bubble{
    background:var(--mine);
    border-color:var(--mine2);
    border-radius:12px 12px 3px 12px;
}

.msg-sender{font-size:11px;font-weight:900;color:var(--gold);margin-bottom:4px;}
.msg-row.mine .msg-sender{color:rgba(255,255,255,.85);}

.msg-text{font-size:13.5px;line-height:1.5;font-weight:600;white-space:pre-wrap;word-break:break-word;}

.msg-attach{
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:6px;
    color:#fff;
    text-decoration:none;
    font-weight:800;
    font-size:12px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.15);
    border-radius:8px;
    padding:8px 10px;
}
.msg-attach i{font-size:16px;flex-shrink:0;}
.msg-attach:hover{background:rgba(255,255,255,.14);}

.msg-meta{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:4px;
    margin-top:4px;
}

.msg-time{font-size:10.5px;color:rgba(255,255,255,.65);font-weight:700;}
.msg-row:not(.mine) .msg-time{color:var(--muted);}

/* WhatsApp-style ticks — only shown on own (mine) messages */
.msg-ticks{
    display:inline-flex;
    align-items:center;
    line-height:0;
}
.msg-ticks svg{width:16px;height:11px;display:block;}
.tick-sent path{fill:rgba(255,255,255,.65);}
.tick-delivered path{fill:rgba(255,255,255,.65);}
.tick-read path{fill:var(--blue-tick);}

.chat-empty{height:100%;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--muted);font-weight:900;}

/* ===== INPUT FORM ===== */
.chat-form{padding:13px;background:var(--panel);border-top:1px solid var(--line);flex-shrink:0;}
.chat-form-inner{display:grid;grid-template-columns:190px 1fr 48px 52px;gap:9px;align-items:center;}
.chat-select,.chat-input{height:46px;background:var(--input);border:1px solid var(--line);border-radius:22px;color:#fff;padding:0 16px;outline:0;font-weight:800;}
.chat-select{border-radius:5px;}
.chat-input{width:100%;}
.chat-select:focus,.chat-input:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(232,25,44,.12);}
.chat-file{display:none;}
.chat-file-btn,.chat-send{height:46px;width:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:20px;}
.chat-file-btn{background:var(--input);color:#fff;border:1px solid var(--line);}
.chat-file-btn:hover{border-color:var(--red);}
.chat-send{background:var(--red);color:#fff;border:0;}
.chat-send:hover{background:var(--mine2);}
.chat-locked{padding:16px;background:#3a1018;color:#ff9aaa;text-align:center;font-weight:900;border-top:1px solid #71313a;}
.alert{margin:10px 14px 0;padding:10px 12px;border-radius:5px;font-weight:800;font-size:13px;}
.alert-success{background:#0d2b1e;color:var(--green);}
.alert-error{background:#3a1018;color:#ff9aaa;}

@media(max-width:900px){
    .chat-page{margin:-16px;}
    .chat-head{height:auto;padding:10px;}
    .chat-action span{display:none;}
    .chat-form-inner{grid-template-columns:1fr 48px 52px;}
    .chat-select{grid-column:1/-1;}
    .msg-bubble{max-width:88%;}
}
</style>
@endpush

@section('content')
@php
    use Carbon\Carbon;

    $isTransfer = !is_null($conversation->wallet_transfer_id);

    $chatNo = $isTransfer
        ? 'TRA'.str_pad($conversation->wallet_transfer_id, 9, '0', STR_PAD_LEFT)
        : 'TNS'.str_pad($conversation->wallet_request_id, 9, '0', STR_PAD_LEFT);

    $userOne = $conversation->userOne;
    $userTwo = $conversation->userTwo;

    $userOneName = $userOne->name ?? 'Deleted User';
    $userTwoName = $userTwo->name ?? 'Deleted User';

    $locked = method_exists($conversation, 'isLocked') ? $conversation->isLocked() : false;

    // Group messages under date dividers, WhatsApp style
    $lastDateLabel = null;
@endphp

<div class="chat-page">
    <section class="chat-shell">

        <header class="chat-head">
            <div class="chat-head-left">
               

                <div class="chat-av">
                    <i class="ti {{ $isTransfer ? 'ti-arrows-exchange' : 'ti-receipt' }}"></i>
                </div>

                <div class="chat-title-wrap">
                    <div class="chat-title">
                        {{ $userOneName }} ↔ {{ $userTwoName }}
                    </div>

                    <div class="chat-sub">
                        <span class="chat-ref">{{ $chatNo }}</span>
                        <span class="chat-tag {{ $isTransfer ? 'tra' : 'req' }}">
                            {{ $isTransfer ? 'TRANSFER' : 'REQUEST' }}
                        </span>

                        @if($locked)
                            · Locked
                        @else
                            · Active Chat
                        @endif
                    </div>
                </div>
            </div>

         
        </header>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <main class="chat-messages" id="chatMessages">
            @forelse($conversation->messages as $message)
                @php
                    $mine = (int) $message->sender_id === (int) auth()->id();

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

                    // Read-receipt state (works whether your Message model
                    // has read_at / delivered_at, or neither).
                    $readAt = $message->read_at ?? null;
                    $deliveredAt = $message->delivered_at ?? true;

                    if ($readAt) {
                        $tickState = 'read';
                    } elseif ($deliveredAt) {
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
                        <div class="msg-sender">
                            {{ $message->sender->name ?? 'User' }}
                        </div>

                        @if($message->message)
                            <div class="msg-text">{{ $message->message }}</div>
                        @endif

                        @if($message->attachment_url)
                            <a class="msg-attach" href="{{ $message->attachment_url }}" target="_blank">
                                <i class="ti ti-paperclip"></i>
                                {{ $message->attachment_name ?? 'Attachment' }}
                            </a>
                        @endif

                        <div class="msg-meta">
                            <span class="msg-time">
                                {{ $message->created_at?->format('h:i A') }}
                            </span>

                            @if($mine)
                                <span class="msg-ticks">
                                    @if($tickState === 'sent')
                                        {{-- single tick: sent, not yet delivered --}}
                                        <svg viewBox="0 0 16 11" class="tick-sent">
                                            <path d="M11.1 0.4 4.8 6.7 2.4 4.3 1 5.7l3.8 3.8 7.7-7.7z"/>
                                        </svg>
                                    @elseif($tickState === 'delivered')
                                        {{-- double tick: delivered, not yet read --}}
                                        <svg viewBox="0 0 20 11" class="tick-delivered">
                                            <path d="M15.1 0.4 8.8 6.7 6.4 4.3 5 5.7l3.8 3.8 7.7-7.7z"/>
                                            <path d="M10.1 0.4 3.8 6.7 1.4 4.3 0 5.7l3.8 3.8 7.7-7.7z"/>
                                        </svg>
                                    @else
                                        {{-- double tick, blue: read --}}
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
                        <div style="margin-top:12px;">No messages yet.</div>
                    </div>
                </div>
            @endforelse
        </main>

        @if($locked)
            <div class="chat-locked">
                <i class="ti ti-lock"></i> This chat is locked.
            </div>
        @else
            <form method="POST"
                  action="{{ route('admin.chats.send', ['conversation' => $conversation->id]) }}"
                  enctype="multipart/form-data"
                  class="chat-form">
                @csrf

                <div class="chat-form-inner">
                    <select name="receiver_id" class="chat-select" required>
                        @if($userOne)
                            <option value="{{ $userOne->id }}">{{ $userOneName }}</option>
                        @endif

                        @if($userTwo)
                            <option value="{{ $userTwo->id }}">{{ $userTwoName }}</option>
                        @endif
                    </select>

                    <input type="text"
                           name="message"
                           class="chat-input"
                           placeholder="Type a message...">

                    <label class="chat-file-btn" title="Attach file">
                        <i class="ti ti-paperclip"></i>
                        <input type="file" name="attachment" class="chat-file">
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
</script>
@endsection
@extends('layouts.admin', ['title' => 'Transaction Chat'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{
    --c-bg:#0B0E11;
    --c-card:#181A20;
    --c-panel:#1E2329;
    --c-field:#0B0E11;
    --c-line:#2B3139;
    --c-yellow:#F0B90B;
    --c-yellow-dark:#C99400;
    --c-gold:#FFD45A;
    --c-green:#0ECB81;
    --c-red:#EF4444;
    --c-black:#000000;
    --c-text:#FFFFFF;
    --c-muted:#848E9C;
    --c-muted2:#5E6673;
    --c-blue:#1477D1;
}

*{box-sizing:border-box}

.chat-page{
    margin:-24px;
    background:var(--c-bg);
    color:var(--c-text);
    font-family:Inter,Arial,sans-serif;
    overflow:hidden;
}

.chat-shell{
    height:100%;
    min-height:0;
    display:flex;
    flex-direction:column;
    background:var(--c-card);
    overflow:hidden;
}

/* TOP */
.chat-head{
    flex:0 0 auto;
    background:var(--c-panel);
    border-bottom:1px solid var(--c-line);
    padding:11px 16px;
}
.chat-head-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
}
.chat-left{display:flex;align-items:center;gap:11px;min-width:0}
.chat-av-wrap{position:relative;flex:0 0 auto}
.chat-av{
    width:44px;height:44px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    color:var(--c-yellow);background:rgba(240,185,11,.13);
    border:1px solid rgba(240,185,11,.32);font-size:21px;
}
.chat-main-dot{
    position:absolute;right:-2px;bottom:-2px;
    width:14px;height:14px;border-radius:50%;border:2px solid var(--c-panel);
}
.chat-main-dot.status-both{background:var(--c-green)}
.chat-main-dot.status-one{background:var(--c-yellow)}
.chat-main-dot.status-none{background:var(--c-red)}
.chat-main-dot.status-locked{background:var(--c-black);border-color:var(--c-yellow)}

.chat-title-box{min-width:0}
.chat-title{
    display:flex;align-items:center;gap:6px;flex-wrap:wrap;
    font-size:15px;line-height:1.2;font-weight:900;
}
.chat-user{display:inline-flex;align-items:center;gap:5px;max-width:230px;min-width:0}
.chat-user-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.chat-user-dot{width:7px;height:7px;border-radius:50%;flex:0 0 auto}
.chat-user-dot.on{background:var(--c-green)}
.chat-user-dot.off{background:var(--c-muted2)}
.chat-sep{color:var(--c-muted2);font-weight:900}
.chat-status{
    margin-top:5px;display:flex;align-items:center;gap:5px;
    font-size:11px;line-height:1;font-weight:900;
}
.chat-status.status-both{color:var(--c-green)}
.chat-status.status-one{color:var(--c-yellow)}
.chat-status.status-none{color:var(--c-red)}
.chat-status.status-locked{color:var(--c-muted)}

.chat-ref{
    flex:0 0 auto;display:flex;align-items:center;gap:8px;
    background:var(--c-field);border:1px solid var(--c-line);
    border-radius:7px;padding:8px 11px;
}
.chat-ref-no{font-family:Consolas,Monaco,monospace;color:var(--c-yellow);font-weight:900;font-size:13px;letter-spacing:.02em}
.chat-ref-type{border-radius:999px;padding:3px 8px;font-size:10px;line-height:1;font-weight:900}
.chat-ref-type.req{background:#3B2A09;color:var(--c-gold)}
.chat-ref-type.tra{background:#10291D;color:var(--c-green)}


.chat-flash{position:relative;z-index:20}
.chat-flash.is-hide{opacity:0;transform:translateY(-6px);pointer-events:none}
.alert-success{background:#0D2B1E;color:var(--c-green)}
.alert-error{background:#3A1018;color:#FF9AAA}

/* ONLY THIS PART SCROLL */
.chat-messages{
    flex:1 1 auto;
    min-height:0;
    overflow-y:auto;
    overscroll-behavior:contain;
    padding:16px 20px 18px;
    background:radial-gradient(circle at top right,rgba(240,185,11,.055),transparent 34%),var(--c-bg);
    scrollbar-width:thin;
    scrollbar-color:#3B4248 transparent;
}
.chat-messages::-webkit-scrollbar{width:7px}
.chat-messages::-webkit-scrollbar-thumb{background:#3B4248;border-radius:20px}

.chat-date{display:flex;justify-content:center;margin:12px 0}
.chat-date span{background:var(--c-panel);border:1px solid var(--c-line);color:var(--c-muted);border-radius:999px;padding:5px 12px;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.04em}

.msg-row{display:flex;margin:3px 0}
.msg-row.mine{justify-content:flex-end}
.msg-bubble{
    max-width:62%;
    width:auto;
    background:var(--c-panel);
    border:1px solid var(--c-line);
    color:var(--c-text);
    border-radius:10px 10px 10px 3px;
    padding:6px 9px;
    box-shadow:0 3px 10px rgba(0,0,0,.16);
    overflow-wrap:anywhere;
}
.msg-row.mine .msg-bubble{
    background:linear-gradient(135deg,var(--c-yellow),var(--c-gold));
    border-color:var(--c-yellow-dark);
    color:#0B0E11;
    border-radius:10px 10px 3px 10px;
}

/* sender + message + time + tick in one compact line */
.msg-one-line{
    display:flex;
    align-items:flex-end;
    gap:6px;
    flex-wrap:wrap;
    line-height:1.15;
}
.msg-sender{
    color:var(--c-yellow);
    font-size:10px;
    line-height:1.1;
    font-weight:900;
    white-space:nowrap;
}
.msg-row.mine .msg-sender{color:rgba(11,14,17,.62)}
.msg-text{
    font-size:13px;
    line-height:1.25;
    font-weight:700;
    white-space:pre-wrap;
}
.msg-meta{
    margin-left:auto;
    display:inline-flex;
    align-items:center;
    gap:3px;
    white-space:nowrap;
    line-height:1;
    padding-left:4px;
}
.msg-time{font-size:9px;line-height:1;font-weight:900;color:var(--c-muted)}
.msg-row.mine .msg-time{color:rgba(11,14,17,.56)}
.msg-ticks{display:inline-flex;line-height:0;align-items:center}
.msg-ticks svg{width:13px;height:9px;display:block}
.tick-sent path,.tick-delivered path{fill:rgba(11,14,17,.50)}
.tick-read path{fill:var(--c-blue)}

.msg-attach{
    margin-top:6px;display:flex;align-items:center;gap:7px;width:max-content;max-width:100%;
    background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);
    color:#fff;border-radius:8px;padding:6px 8px;font-size:11px;font-weight:900;text-decoration:none;
}
.msg-row.mine .msg-attach{background:rgba(11,14,17,.12);border-color:rgba(11,14,17,.18);color:#0B0E11}

.chat-empty{height:100%;min-height:220px;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--c-muted);font-weight:900}

/* BOTTOM TYPE SECTION - ALWAYS VISIBLE */
.chat-form{
    flex:0 0 auto;
    padding:10px 12px;
    background:var(--c-panel);
    border-top:1px solid var(--c-line);
    z-index:5;
}
.chat-form-inner{display:grid;grid-template-columns:180px minmax(0,1fr) 44px 48px;gap:8px;align-items:center}
.chat-select,.chat-input{
    height:42px;background:var(--c-field);border:1px solid var(--c-line);color:var(--c-text);
    outline:0;font-family:inherit;font-size:13px;font-weight:400;
}
.chat-select{border-radius:7px;padding:0 10px}
.chat-input{width:100%;border-radius:999px;padding:0 15px}
.chat-select:focus,.chat-input:focus{border-color:var(--c-yellow);box-shadow:0 0 0 3px rgba(240,185,11,.14)}
.chat-file{display:none}
.chat-file-btn,.chat-send{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:18px}
.chat-file-btn{background:var(--c-field);color:var(--c-text);border:1px solid var(--c-line)}
.chat-file-btn:hover{border-color:var(--c-yellow);color:var(--c-yellow)}
.chat-send{border:0;background:var(--c-yellow);color:#0B0E11}
.chat-send:hover{background:var(--c-yellow-dark)}
.chat-locked{flex:0 0 auto;padding:14px;background:#111316;border-top:1px solid var(--c-line);color:var(--c-muted);text-align:center;font-weight:900;z-index:5}

@media(max-width:900px){
    .chat-page{margin:-16px}
    .chat-head-row{align-items:flex-start;flex-direction:column}
    .chat-ref{width:100%;justify-content:space-between}
    .chat-form-inner{grid-template-columns:minmax(0,1fr) 42px 46px}
    .chat-select{grid-column:1/-1}
    .msg-bubble{max-width:88%}
}
</style>
@endpush

@section('content')
@php
    use Carbon\Carbon;

    $isTransfer = ! is_null($conversation->wallet_transfer_id);

    $chatNo = $isTransfer
        ? 'TRA' . str_pad((string) $conversation->wallet_transfer_id, 9, '0', STR_PAD_LEFT)
        : 'TNS' . str_pad((string) $conversation->wallet_request_id, 9, '0', STR_PAD_LEFT);

    $userOne = $conversation->userOne;
    $userTwo = $conversation->userTwo;

    $userOneName = $userOne->name ?? 'Deleted User';
    $userTwoName = $userTwo->name ?? 'Deleted User';

    $locked = method_exists($conversation, 'isLocked') ? $conversation->isLocked() : false;

    $isOnline = function ($user) {
        if (! $user) return false;

        if (array_key_exists('is_online', $user->getAttributes())) {
            return (bool) $user->is_online;
        }

        if (! empty($user->last_seen_at)) {
            return Carbon::parse($user->last_seen_at)->gt(now()->subMinutes(5));
        }

        return false;
    };

    $oneIsOnline = $isOnline($userOne);
    $twoIsOnline = $isOnline($userTwo);

    if ($locked) {
        $statusKey = 'locked';
        $statusText = 'Closed / Locked';
    } elseif ($oneIsOnline && $twoIsOnline) {
        $statusKey = 'both';
        $statusText = 'Both Online';
    } elseif ($oneIsOnline || $twoIsOnline) {
        $statusKey = 'one';
        $statusText = 'One Online';
    } else {
        $statusKey = 'none';
        $statusText = 'Both Offline';
    }

    $lastDateLabel = null;
@endphp

<div class="chat-page" id="chatPage">
    <section class="chat-shell">
        <header class="chat-head">
            <div class="chat-head-row">
                <div class="chat-left">
                    <div class="chat-av-wrap">
                        <div class="chat-av">
                            <i class="ti {{ $isTransfer ? 'ti-arrows-exchange' : 'ti-receipt' }}"></i>
                        </div>
                        <span class="chat-main-dot status-{{ $statusKey }}"></span>
                    </div>

                    <div class="chat-title-box">
                        <div class="chat-title">
                            <span class="chat-user">
                                <span class="chat-user-dot {{ $oneIsOnline ? 'on' : 'off' }}"></span>
                                <span class="chat-user-name">{{ $userOneName }}</span>
                            </span>
                            <span class="chat-sep">↔</span>
                            <span class="chat-user">
                                <span class="chat-user-dot {{ $twoIsOnline ? 'on' : 'off' }}"></span>
                                <span class="chat-user-name">{{ $userTwoName }}</span>
                            </span>
                        </div>

                        <div class="chat-status status-{{ $statusKey }}">
                            <i class="ti ti-{{ $locked ? 'lock' : 'activity' }}"></i>
                            {{ $statusText }}
                        </div>
                    </div>
                </div>

                <div class="chat-ref">
                    <span class="chat-ref-no">{{ $chatNo }}</span>
                    <span class="chat-ref-type {{ $isTransfer ? 'tra' : 'req' }}">
                        {{ $isTransfer ? 'TRANSFER' : 'REQUEST' }}
                    </span>
                </div>
            </div>
        </header>

      
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

                    $hasReadColumn = array_key_exists('read_at', $message->getAttributes());
                    $hasDeliveredColumn = array_key_exists('delivered_at', $message->getAttributes());

                    if ($hasReadColumn && $message->read_at) {
                        $tickState = 'read';
                    } elseif ($hasDeliveredColumn && $message->delivered_at) {
                        $tickState = 'delivered';
                    } else {
                        $tickState = 'sent';
                    }
                @endphp

                @if($dateLabel && $dateLabel !== $lastDateLabel)
                    <div class="chat-date"><span>{{ $dateLabel }}</span></div>
                    @php $lastDateLabel = $dateLabel; @endphp
                @endif

                <div class="msg-row {{ $mine ? 'mine' : '' }}">
                    <div class="msg-bubble">
                        <div class="msg-one-line">
                            <span class="msg-sender">{{ $message->sender->name ?? 'User' }}</span>

                            @if($message->message)
                                <span class="msg-text">{{ $message->message }}</span>
                            @endif

                            <span class="msg-meta">
                                <span class="msg-time">{{ $message->created_at?->format('h:i A') }}</span>

                                @if($mine)
                                    <span class="msg-ticks">
                                        @if($tickState === 'read')
                                            <svg viewBox="0 0 20 11" class="tick-read" aria-hidden="true">
                                                <path d="M15.1 0.4 8.8 6.7 6.4 4.3 5 5.7l3.8 3.8 7.7-7.7z"/>
                                                <path d="M10.1 0.4 3.8 6.7 1.4 4.3 0 5.7l3.8 3.8 7.7-7.7z"/>
                                            </svg>
                                        @elseif($tickState === 'delivered')
                                            <svg viewBox="0 0 20 11" class="tick-delivered" aria-hidden="true">
                                                <path d="M15.1 0.4 8.8 6.7 6.4 4.3 5 5.7l3.8 3.8 7.7-7.7z"/>
                                                <path d="M10.1 0.4 3.8 6.7 1.4 4.3 0 5.7l3.8 3.8 7.7-7.7z"/>
                                            </svg>
                                        @else
                                            <svg viewBox="0 0 16 11" class="tick-sent" aria-hidden="true">
                                                <path d="M11.1 0.4 4.8 6.7 2.4 4.3 1 5.7l3.8 3.8 7.7-7.7z"/>
                                            </svg>
                                        @endif
                                    </span>
                                @endif
                            </span>
                        </div>

                        @if(! empty($message->attachment_url))
                            <a class="msg-attach" href="{{ $message->attachment_url }}" target="_blank" rel="noopener">
                                <i class="ti ti-paperclip"></i>
                                <span>{{ $message->attachment_name ?? 'Attachment' }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="chat-empty">
                    <div>
                        <i class="ti ti-message-circle" style="font-size:54px;color:var(--c-yellow)"></i>
                        <div style="margin-top:12px">No messages yet.</div>
                    </div>
                </div>
            @endforelse
        </main>

        @if($locked)
            <div class="chat-locked">
                <i class="ti ti-lock"></i> This chat is closed / locked.
            </div>
        @else
            <form method="POST"
                  action="{{ route('admin.chats.send', ['conversation' => $conversation->id]) }}"
                  enctype="multipart/form-data"
                  class="chat-form"
                  autocomplete="off">
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

                    <input type="text" name="message" class="chat-input" placeholder="Type a message...">

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
(function () {
    function findFooter() {
        return document.querySelector('footer, .footer, .admin-footer, .main-footer');
    }

    function sizeChatPage() {
        var page = document.getElementById('chatPage');
        if (!page) return;

        var top = page.getBoundingClientRect().top;
        var footer = findFooter();
        var bottom = window.innerHeight;

        if (footer) {
            var footerTop = footer.getBoundingClientRect().top;
            if (footerTop > top && footerTop < window.innerHeight) {
                bottom = footerTop;
            }
        }

        var height = bottom - top;
        page.style.height = Math.max(height, 360) + 'px';
    }

    function scrollChatBottom() {
        var box = document.getElementById('chatMessages');
        if (!box) return;
        box.scrollTop = box.scrollHeight;
    }

    window.addEventListener('resize', sizeChatPage);
    window.addEventListener('orientationchange', sizeChatPage);

    document.addEventListener('DOMContentLoaded', function () {
        sizeChatPage();
        scrollChatBottom();
        setTimeout(function () {
            sizeChatPage();
            scrollChatBottom();
        }, 150);
    });
})();
</script>
@endsection

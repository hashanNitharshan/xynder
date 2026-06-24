@extends('layouts.admin', ['title' => 'Transaction Chats'])

@push('styles')
<style>
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

.xyn-filter{
    display:flex;
    gap:8px;
    margin-bottom:12px;
}

.xyn-filter select{
    flex:1;
    background:#131929;
    border:1px solid rgba(255,255,255,.08);
    color:#fff;
    border-radius:13px;
    padding:10px 12px;
    font-size:13px;
    outline:0;
}

.xyn-filter button,
.xyn-filter a{
    border:0;
    text-decoration:none;
    background:#7c5cfc;
    color:#fff;
    border-radius:13px;
    padding:10px 12px;
    font-size:12px;
    font-weight:900;
    cursor:pointer;
}

.xyn-filter a{
    background:#1f2937;
    color:#9ca3af;
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
.xyn-chat-item.active{
    background:rgba(124,92,252,.18);
}

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

.xyn-pager{
    flex-shrink:0;
    padding:10px 12px;
    border-top:1px solid rgba(255,255,255,.08);
    background:#0e1220;
    color:#9ca3af;
}
.xyn-pager nav{display:flex;justify-content:center;}
.xyn-pager svg{width:18px;height:18px;}

.xyn-empty{
    flex:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:14px;
    background:#111827;
    color:#374151;
}

.xyn-empty-icon{font-size:56px;opacity:.4;}
.xyn-empty-text{font-size:15px;font-weight:700;color:#4b5563;}

@media(max-width:760px){
    .xyn-wrap{
        border-radius:18px;
        height:calc(100dvh - 90px);
        max-height:calc(100dvh - 90px);
    }
    .xyn-sb{width:100%;flex:1;}
    .xyn-empty{display:none;}
}
</style>
@endpush

@section('content')
<div class="xyn-wrap">

    <div class="xyn-sb">
        <div class="xyn-sb-head">
            <div class="xyn-sb-title">Transaction Chats</div>

            <form method="GET" class="xyn-filter">
                <select name="type">
                    <option value="">All</option>
                    <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfers</option>
                    <option value="request" {{ request('type') === 'request' ? 'selected' : '' }}>Requests</option>
                </select>
                <button type="submit">Filter</button>
                <a href="{{ route('admin.chats.index') }}">Reset</a>
            </form>

            <div class="xyn-search">
                <span>🔍</span>
                <input type="text" id="chatSearch" placeholder="Search user or reference…">
            </div>
        </div>

        <div class="xyn-list" id="chatList">
            @forelse($conversations as $c)
                @php
                    $cIsTransfer = !is_null($c->wallet_transfer_id);

                    $cChatNo = $cIsTransfer
                        ? 'TRA'.str_pad($c->wallet_transfer_id, 9, '0', STR_PAD_LEFT)
                        : 'TNS'.str_pad($c->wallet_request_id, 9, '0', STR_PAD_LEFT);

                    $userOne = $c->userOne->name ?? 'Deleted User';
                    $userTwo = $c->userTwo->name ?? 'Deleted User';
                    $cLast = $c->messages->first();
                    $cLocked = method_exists($c, 'isLocked') ? $c->isLocked() : false;
                @endphp

                <a href="{{ route('admin.chats.show', $c) }}"
                   class="xyn-chat-item"
                   data-name="{{ strtolower($userOne.' '.$userTwo) }}"
                   data-ref="{{ strtolower($cChatNo) }}">

                    <div class="xyn-av">
                        {{ strtoupper(substr($userOne, 0, 1)) }}
                        @if($cLocked)
                            <span class="xyn-lock-badge">🔒</span>
                        @endif
                    </div>

                    <div class="xyn-body">
                        <div class="xyn-top">
                            <div class="xyn-name">{{ $userOne }} ↔ {{ $userTwo }}</div>
                            <span class="xyn-tag {{ $cIsTransfer ? 'tra' : 'req' }}">
                                {{ $cIsTransfer ? 'TRA' : 'REQ' }}
                            </span>
                        </div>

                        <div class="xyn-last">
                            @if($cLocked)
                                🔒 Chat locked
                            @elseif($cLast)
                                {{ $cLast->sender->name ?? 'User' }}:
                                {{ $cLast->message
                                    ? \Illuminate\Support\Str::limit($cLast->message, 32)
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
                    </div>
                </a>
            @empty
                <div style="text-align:center;color:#6b7280;padding:40px 20px;">
                    No chats found.
                </div>
            @endforelse
        </div>

        <div class="xyn-pager">
            {{ $conversations->links() }}
        </div>
    </div>

    <div class="xyn-empty">
        <div class="xyn-empty-icon">💬</div>
        <div class="xyn-empty-text">Select a conversation to view admin chat</div>
    </div>

</div>

<script>
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
@extends('layouts.admin', ['title' => $title ?? 'Chats'])

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
    position:relative;
    min-height:280px;
    padding:60px 80px 110px;
    background:var(--hero);
    overflow:hidden;
}

.chat-hero::after{
    content:"";
    position:absolute;
    inset:0;
    opacity:.08;
    background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
    background-size:260px 260px;
}

.chat-hero-content{
    position:relative;
    z-index:2;
    max-width:600px;
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
    font-size:46px;
    line-height:1.15;
    font-weight:900;
    margin:0 0 18px;
}

.chat-title span{color:var(--red)}

.chat-sub{
    color:#b8bdc2;
    font-size:15px;
    line-height:1.7;
    font-weight:700;
}

.chat-shell{
    position:relative;
    z-index:5;
    max-width:1200px;
    margin:-70px 80px 0;
    display:grid;
    grid-template-columns:380px 1fr;
    min-height:620px;
    height:calc(100dvh - 210px);
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
    background:#24292d;
    border-right:1px solid var(--line);
}

.chat-sb-head{
    flex-shrink:0;
    padding:22px;
    border-bottom:1px solid var(--line);
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

.chat-search{
    height:48px;
    background:#1f2428;
    border:1px solid #3b4248;
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

.chat-search input::placeholder{color:#747b82}

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

.chat-item:hover{
    background:#2f353a;
    border-color:#424a51;
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

.chat-unread{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    margin-top:7px;
    min-width:22px;
    height:22px;
    padding:0 7px;
    background:var(--red);
    color:#fff;
    border-radius:20px;
    font-size:11px;
    font-weight:900;
}

.chat-pager{
    flex-shrink:0;
    padding:12px;
    border-top:1px solid var(--line);
    background:#24292d;
}

.chat-pager nav{display:flex;justify-content:center}
.chat-pager svg{width:18px;height:18px}

.chat-empty-panel{
    background:#2b2f32;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px;
}

.chat-empty-card{
    text-align:center;
    max-width:360px;
}

.chat-empty-icon{
    width:86px;
    height:86px;
    margin:0 auto 18px;
    border-radius:50%;
    background:#3a1018;
    color:var(--red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:42px;
}

.chat-empty-card h3{
    font-size:22px;
    font-weight:900;
    margin:0 0 10px;
}

.chat-empty-card p{
    color:#aeb4ba;
    font-size:14px;
    line-height:1.7;
    font-weight:700;
}

@media(max-width:900px){
    .chat-page{margin:-16px}
    .chat-hero{padding:45px 24px 100px}
    .chat-title{font-size:34px}
    .chat-shell{
        margin:-70px 20px 0;
        grid-template-columns:1fr;
        height:calc(100dvh - 160px);
    }
    .chat-empty-panel{display:none}
}
</style>
@endpush

@section('content')
<div class="chat-page">

    <section class="chat-hero">
        <div class="chat-hero-content">
            <div class="chat-eyebrow">Xynder Wallet</div>
            <h1 class="chat-title">Transaction <span>Chats</span></h1>
            <div class="chat-sub">
                Chat with clients and merchants for wallet requests and wallet transfers.
                Secure messages, attachments, and transaction based conversation history.
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

                <div class="chat-search">
                    <i class="ti ti-search"></i>
                    <input type="text" id="chatSearch" placeholder="Search chat or reference...">
                </div>
            </div>

            <div class="chat-list" id="chatList">
                @forelse($conversations as $c)
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
                       class="chat-item"
                       data-name="{{ strtolower($cOther->name ?? '') }}"
                       data-ref="{{ strtolower($cChatNo) }}">

                        <div class="chat-av">
                            {{ strtoupper(substr($cOther->name ?? 'U', 0, 1)) }}
                            @if($cLocked)
                                <span class="chat-lock">
                                    <i class="ti ti-lock"></i>
                                </span>
                            @endif
                        </div>

                        <div class="chat-body">
                            <div class="chat-top">
                                <div class="chat-name">{{ $cOther->name ?? 'Unknown' }}</div>
                                <span class="chat-tag {{ $cIsTransfer ? 'tra' : 'req' }}">
                                    {{ $cIsTransfer ? 'TRA' : 'REQ' }}
                                </span>
                            </div>

                            <div class="chat-last">
                                @if($cLocked)
                                    Chat locked
                                @elseif($cLast)
                                    {{ $cLast->message
                                        ? \Illuminate\Support\Str::limit($cLast->message, 42)
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

                            @if($cUnread > 0)
                                <span class="chat-unread">{{ $cUnread }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div style="text-align:center;color:#9fa5aa;padding:42px 20px;font-weight:800;">
                        No chats yet.
                    </div>
                @endforelse
            </div>

            @if($conversations->hasPages())
                <div class="chat-pager">
                    {{ $conversations->links() }}
                </div>
            @endif
        </aside>

        <main class="chat-empty-panel">
            <div class="chat-empty-card">
                <div class="chat-empty-icon">
                    <i class="ti ti-message-circle"></i>
                </div>
                <h3>Select a Conversation</h3>
                <p>Choose a transaction chat from the left side to view messages and send replies.</p>
            </div>
        </main>
    </section>
</div>

<script>
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
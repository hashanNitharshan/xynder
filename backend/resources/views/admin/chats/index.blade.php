@extends('layouts.admin', ['title' => 'Transaction Chats'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

<style>
:root{--dark:#101518;--box:#2b2f32;--panel:#24292d;--input:#1f2428;--line:#3b4248;--red:#e8192c;--red2:#c91022;--green:#0ecb81;--gold:#ffc933;--text:#fff;--muted:#aeb4ba;--muted2:#747b82;--mine:#e8192c;--them:#1f2428;}
*{box-sizing:border-box}
.chat-page{margin:-24px;height:100vh;max-height:100vh;background:var(--dark);color:var(--text);font-family:Inter,Arial,sans-serif;overflow:hidden;}
.chat-shell{height:100%;display:grid;grid-template-columns:390px 1fr;background:var(--box);border-left:1px solid var(--line);border-right:1px solid var(--line);}
.chat-sidebar{display:flex;flex-direction:column;min-height:0;background:var(--panel);border-right:1px solid var(--line);}
.chat-sb-head{flex-shrink:0;padding:16px;border-bottom:1px solid var(--line);background:var(--panel);}
.chat-brand{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
.chat-brand-left{display:flex;align-items:center;gap:10px;min-width:0;}
.chat-brand-icon{width:42px;height:42px;border-radius:50%;background:#3a1018;color:var(--red);display:flex;align-items:center;justify-content:center;font-size:22px;}
.chat-brand-title{font-size:18px;font-weight:900;line-height:1.2;}
.chat-brand-sub{font-size:11px;color:var(--muted);font-weight:800;margin-top:2px;}
.chat-filter{display:grid;grid-template-columns:1fr 42px 42px;gap:8px;margin-bottom:10px;}
.chat-filter select{height:42px;background:var(--input);border:1px solid var(--line);color:#fff;border-radius:5px;padding:0 12px;outline:0;font-weight:800;}
.chat-filter button,.chat-filter a{height:42px;border:0;text-decoration:none;background:var(--red);color:#fff;border-radius:5px;font-size:16px;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.chat-filter a{background:var(--input);border:1px solid var(--line);}
.chat-search{height:44px;background:var(--input);border:1px solid var(--line);border-radius:5px;display:flex;align-items:center;gap:10px;padding:0 13px;}
.chat-search i{color:var(--muted2);font-size:18px;}
.chat-search input{flex:1;background:transparent;border:0;outline:0;color:#fff;font-size:13px;font-weight:800;}
.chat-search input::placeholder{color:var(--muted2);}
.chat-list{flex:1;min-height:0;overflow-y:auto;padding:8px;scrollbar-width:thin;scrollbar-color:#3b4248 transparent;}
.chat-item{display:grid;grid-template-columns:48px 1fr auto;gap:12px;align-items:center;padding:12px;border-radius:8px;color:#fff;text-decoration:none;border:1px solid transparent;transition:.15s;}
.chat-item:hover,.chat-item.active{background:#30363a;border-color:rgba(232,25,44,.55);}
.chat-av{width:48px;height:48px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;position:relative;}
.chat-lock{position:absolute;right:-4px;bottom:-4px;background:#3a1018;color:#ff6b7b;border:1px solid #71313a;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:11px;}
.chat-body{min-width:0;}
.chat-top{display:flex;align-items:center;gap:7px;min-width:0;}
.chat-name{font-size:14px;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.chat-tag{flex-shrink:0;padding:3px 7px;border-radius:20px;font-size:10px;font-weight:900;}
.chat-tag.req{background:#3b2a09;color:var(--gold);}
.chat-tag.tra{background:#3a1018;color:#ff6b7b;}
.chat-last{margin-top:5px;color:#9fa5aa;font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.chat-time{color:#747b82;font-size:11px;font-weight:900;white-space:nowrap;}
.chat-pager{flex-shrink:0;padding:10px;border-top:1px solid var(--line);background:var(--panel);}
.chat-main{display:flex;align-items:center;justify-content:center;background:radial-gradient(circle at top right,rgba(232,25,44,.07),transparent 35%),var(--box);padding:25px;}
.chat-empty-card{text-align:center;max-width:430px;background:var(--panel);border:1px solid var(--line);border-radius:10px;padding:34px;}
.chat-empty-icon{width:86px;height:86px;margin:0 auto 18px;border-radius:50%;background:#3a1018;color:var(--red);display:flex;align-items:center;justify-content:center;font-size:42px;}
.chat-empty-card h3{font-size:22px;font-weight:900;margin:0 0 10px;}
.chat-empty-card p{color:var(--muted);font-size:14px;line-height:1.7;font-weight:700;margin:0;}
@media(max-width:900px){.chat-page{margin:-16px;}.chat-shell{grid-template-columns:1fr;}.chat-main{display:none;}.chat-sidebar{border-right:0;}}
</style>
@endpush

@section('content')
<div class="chat-page">
    <section class="chat-shell">
        <aside class="chat-sidebar">
            <div class="chat-sb-head">
                <div class="chat-brand">
                    <div class="chat-brand-left">
                        <div class="chat-brand-icon"><i class="ti ti-messages"></i></div>
                        <div>
                            <div class="chat-brand-title">Transaction Chats</div>
                            <div class="chat-brand-sub">Requests & transfers</div>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.chats.index') }}" class="chat-filter">
                    <select name="type">
                        <option value="">All Chats</option>
                        <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfers</option>
                        <option value="request" {{ request('type') === 'request' ? 'selected' : '' }}>Requests</option>
                    </select>
                    <button type="submit" title="Filter"><i class="ti ti-filter"></i></button>
                    <a href="{{ route('admin.chats.index') }}" title="Reset"><i class="ti ti-refresh"></i></a>
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
                        $cChatNo = $cIsTransfer ? 'TRA'.str_pad($c->wallet_transfer_id, 9, '0', STR_PAD_LEFT) : 'TNS'.str_pad($c->wallet_request_id, 9, '0', STR_PAD_LEFT);
                        $userOne = $c->userOne->name ?? 'Deleted User';
                        $userTwo = $c->userTwo->name ?? 'Deleted User';
                        $cLast = $c->messages->first();
                        $cLocked = method_exists($c, 'isLocked') ? $c->isLocked() : false;
                    @endphp

                    <a href="{{ route('admin.chats.show', $c) }}" class="chat-item" data-name="{{ strtolower($userOne.' '.$userTwo) }}" data-ref="{{ strtolower($cChatNo) }}">
                        <div class="chat-av">
                            {{ strtoupper(substr($userOne, 0, 1)) }}
                            @if($cLocked)<span class="chat-lock"><i class="ti ti-lock"></i></span>@endif
                        </div>
                        <div class="chat-body">
                            <div class="chat-top">
                                <div class="chat-name">{{ $userOne }} ↔ {{ $userTwo }}</div>
                                <span class="chat-tag {{ $cIsTransfer ? 'tra' : 'req' }}">{{ $cIsTransfer ? 'TRA' : 'REQ' }}</span>
                            </div>
                            <div class="chat-last">
                                @if($cLocked)
                                    Chat locked
                                @elseif($cLast)
                                    {{ $cLast->sender->name ?? 'User' }}: {{ $cLast->message ? \Illuminate\Support\Str::limit($cLast->message, 34) : 'Attachment: '.$cLast->attachment_name }}
                                @else
                                    No messages yet
                                @endif
                            </div>
                        </div>
                        <div class="chat-time">{{ $c->updated_at?->isToday() ? $c->updated_at->format('H:i') : $c->updated_at?->format('M d') }}</div>
                    </a>
                @empty
                    <div style="text-align:center;color:#9fa5aa;padding:42px 20px;font-weight:800;">No chats found.</div>
                @endforelse
            </div>

            @if($conversations->hasPages())
                <div class="chat-pager">{{ $conversations->links() }}</div>
            @endif
        </aside>

        <main class="chat-main">
            <div class="chat-empty-card">
                <div class="chat-empty-icon"><i class="ti ti-message-circle"></i></div>
                <h3>Select a Conversation</h3>
                <p>Choose a transaction chat to view messages in a fixed WhatsApp-style admin chat window.</p>
            </div>
        </main>
    </section>
</div>

<script>
document.getElementById('chatSearch')?.addEventListener('input',function(){
    const q=this.value.toLowerCase().trim();
    document.querySelectorAll('.chat-item').forEach(el=>{
        el.style.display=(!q||el.dataset.name.includes(q)||el.dataset.ref.includes(q))?'':'none';
    });
});
</script>
@endsection

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\WalletRequest;
use App\Models\WalletTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private function adminOnly(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    private function conversationsQuery(Request $request)
    {
        $query = Conversation::with([
            'userOne:id,name,email,role,photo,is_online,last_seen_at',
            'userTwo:id,name,email,role,photo,is_online,last_seen_at',
            'walletTransfer',
            'walletRequest',
            'messages' => fn ($q) => $q
                ->with('sender:id,name,email,role,photo')
                ->latest()
                ->limit(1),
        ])->latest('updated_at');

        if ($request->get('type') === 'transfer') {
            $query->whereNotNull('wallet_transfer_id');
        }

        if ($request->get('type') === 'request') {
            $query->whereNotNull('wallet_request_id');
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->adminOnly();

        $user = $request->user();

        $conversations = $this->conversationsQuery($request)
            ->paginate(30)
            ->withQueryString();

        foreach ($conversations as $conversation) {
            if (method_exists($conversation, 'lockIfExpired')) {
                $conversation->lockIfExpired();
            }
        }

        return view('admin.chats.index', compact('conversations', 'user'));
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->adminOnly();

        $user = $request->user();

        if (method_exists($conversation, 'lockIfExpired')) {
            $conversation->lockIfExpired();
        }

        $conversation->load([
            'userOne:id,name,email,role,photo,is_online,last_seen_at',
            'userTwo:id,name,email,role,photo,is_online,last_seen_at',
            'walletTransfer',
            'walletRequest',
            'messages' => fn ($q) => $q->oldest(),
            'messages.sender:id,name,email,role,photo,is_online,last_seen_at',
            'messages.receiver:id,name,email,role,photo,is_online,last_seen_at',
        ]);

        $conversations = $this->conversationsQuery($request)
            ->paginate(30)
            ->withQueryString();

        foreach ($conversations as $sideConversation) {
            if (method_exists($sideConversation, 'lockIfExpired')) {
                $sideConversation->lockIfExpired();
            }
        }

        return view('admin.chats.show', compact(
            'conversation',
            'conversations',
            'user'
        ));
    }

    public function send(Request $request, Conversation $conversation)
    {
        $this->adminOnly();

        if (method_exists($conversation, 'isLocked') && $conversation->isLocked()) {
            return back()->withErrors([
                'message' => 'This chat is locked.',
            ]);
        }

        $data = $request->validate([
            'message' => 'nullable|string|max:2000',
            'receiver_id' => 'required|exists:users,id',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);

        if (empty($data['message']) && ! $request->hasFile('attachment')) {
            return back()
                ->withErrors(['message' => 'Message or attachment is required.'])
                ->withInput();
        }

        if (! in_array((int) $data['receiver_id'], [
            (int) $conversation->user_one_id,
            (int) $conversation->user_two_id,
        ], true)) {
            return back()
                ->withErrors(['receiver_id' => 'Receiver must be one of this chat users.'])
                ->withInput();
        }

        ChatMessage::create(array_merge([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'receiver_id' => (int) $data['receiver_id'],
            'message' => $data['message'] ?? '',
            'status' => 'sent',
        ], $this->storeAttachment($request)));

        $conversation->touch();

        return back()->with('success', 'Message sent.');
    }

    public function openTransfer(WalletTransfer $walletTransfer)
    {
        $this->adminOnly();

        if (! $walletTransfer->sender_id || ! $walletTransfer->receiver_id) {
            return back()->withErrors([
                'transfer' => 'This transfer does not have both sender and receiver.',
            ]);
        }

        $conversation = Conversation::where('wallet_transfer_id', $walletTransfer->id)->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $walletTransfer->sender_id,
                'user_two_id' => $walletTransfer->receiver_id,
                'wallet_transfer_id' => $walletTransfer->id,
                'wallet_request_id' => null,
            ]);
        }

        return redirect()->route('admin.chats.show', ['conversation' => $conversation->id]);
    }

    public function openRequest(WalletRequest $walletRequest)
    {
        $this->adminOnly();

        if (! $walletRequest->user_id || ! $walletRequest->merchant_id) {
            return back()->withErrors([
                'request' => 'This request does not have both client and merchant.',
            ]);
        }

        $conversation = Conversation::where('wallet_request_id', $walletRequest->id)->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $walletRequest->user_id,
                'user_two_id' => $walletRequest->merchant_id,
                'wallet_request_id' => $walletRequest->id,
                'wallet_transfer_id' => null,
            ]);
        }

        return redirect()->route('admin.chats.show', ['conversation' => $conversation->id]);
    }

    private function storeAttachment(Request $request): array
    {
        if (! $request->hasFile('attachment')) {
            return [];
        }

        $file = $request->file('attachment');
        $path = $file->store('chat_attachments', 'public');
        $mime = $file->getMimeType();

        return [
            'attachment_path' => $path,
            'attachment_url' => url('/api/storage/' . $path),
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_mime' => $mime,
            'attachment_size' => $file->getSize(),
            'attachment_type' => str_starts_with($mime, 'image/') ? 'image' : 'file',
        ];
    }
}
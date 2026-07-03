<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\WalletRequest;
use App\Models\WalletTransfer;

class WebChatController extends Controller
{


public function showByRequest(Request $request, WalletRequest $walletRequest)
{
    $user = $request->user();

    abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

    abort_unless(in_array($user->id, [
        (int) $walletRequest->user_id,
        (int) $walletRequest->merchant_id,
    ], true), 403);

    $conversation = Conversation::firstOrCreate(
        ['wallet_request_id' => $walletRequest->id],
        [
            'user_one_id' => $walletRequest->user_id,
            'user_two_id' => $walletRequest->merchant_id,
            'wallet_transfer_id' => null,
            'chat_started_at' => now(),
        ]
    );

    return redirect()->route(
        $user->role === 'merchant' ? 'merchant.chats.show' : 'client.chats.show',
        $conversation
    );
}

public function showByTransfer(Request $request, WalletTransfer $walletTransfer)
{
    $user = $request->user();

    abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

    abort_unless(in_array($user->id, [
        (int) $walletTransfer->sender_id,
        (int) $walletTransfer->receiver_id,
    ], true), 403);

    $conversation = Conversation::firstOrCreate(
        ['wallet_transfer_id' => $walletTransfer->id],
        [
            'user_one_id' => $walletTransfer->sender_id,
            'user_two_id' => $walletTransfer->receiver_id,
            'wallet_request_id' => null,
            'chat_started_at' => now(),
        ]
    );

    return redirect()->route(
        $user->role === 'merchant' ? 'merchant.chats.show' : 'client.chats.show',
        $conversation
    );
}

    public function clientIndex(Request $request)
    {
        abort_unless($request->user()->role === 'client', 403);
        return $this->index($request, 'client.chats');
    }

    public function merchantIndex(Request $request)
    {
        abort_unless($request->user()->role === 'merchant', 403);
        return $this->index($request, 'merchant.chats');
    }

    private function conversationsQuery($user)
    {
        return Conversation::with([
            'userOne:id,name,email,role,photo,is_online,last_seen_at',
            'userTwo:id,name,email,role,photo,is_online,last_seen_at',
            'walletTransfer',
            'walletRequest',
            'messages' => fn ($q) => $q->latest()->limit(1),
        ])
        ->where(function ($q) use ($user) {
            $q->where('user_one_id', $user->id)
              ->orWhere('user_two_id', $user->id);
        })
        ->latest('updated_at');
    }

    private function index(Request $request, string $view)
    {
        $user = $request->user();

        $conversations = $this->conversationsQuery($user)
            ->paginate(30)
            ->withQueryString();

        foreach ($conversations as $conversation) {
            $conversation->lockIfExpired();
        }

        ChatMessage::where('receiver_id', $user->id)
            ->where('status', 'sent')
            ->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

        return view($view, compact('conversations', 'user'));
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        abort_unless(in_array($user->id, [
            (int) $conversation->user_one_id,
            (int) $conversation->user_two_id,
        ]), 403);

        $conversation->load(['walletRequest', 'walletTransfer']);
        $conversation->lockIfExpired();

        ChatMessage::where('conversation_id', $conversation->id)
            ->where('receiver_id', $user->id)
            ->whereIn('status', ['sent', 'delivered'])
            ->update([
                'status' => 'seen',
                'seen_at' => now(),
            ]);

        $conversation->load([
            'userOne:id,name,email,role,photo,is_online,last_seen_at',
            'userTwo:id,name,email,role,photo,is_online,last_seen_at',
            'walletTransfer',
            'walletRequest',
            'messages' => fn ($q) => $q->oldest(),
            'messages.sender:id,name,email,role,photo,is_online,last_seen_at',
            'messages.receiver:id,name,email,role,photo,is_online,last_seen_at',
        ]);

        $otherUser = $conversation->user_one_id == $user->id
            ? $conversation->userTwo
            : $conversation->userOne;

        $conversations = $this->conversationsQuery($user)
            ->paginate(30)
            ->withQueryString();

        foreach ($conversations as $sideConversation) {
            $sideConversation->lockIfExpired();
        }

        $view = $user->role === 'merchant'
            ? 'merchant.chat_show'
            : 'client.chat_show';

        return view($view, compact('conversation', 'conversations', 'user', 'otherUser'));
    }

   public function send(Request $request, Conversation $conversation)
{
    try {
        $user = $request->user();

        abort_unless(in_array($user->id, [
            (int) $conversation->user_one_id,
            (int) $conversation->user_two_id,
        ], true), 403);

        $conversation->load(['walletRequest', 'walletTransfer']);
        $conversation->lockIfExpired();

        if ($conversation->isLocked()) {
            return back()->withErrors([
                'message' => 'This chat is locked. 15 minutes completed.',
            ]);
        }

        $data = $request->validate([
            'message'    => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);

        if (empty($data['message']) && ! $request->hasFile('attachment')) {
            return back()
                ->withErrors(['message' => 'Message or attachment is required.'])
                ->withInput();
        }

        if (! $conversation->chat_started_at) {
            $conversation->update(['chat_started_at' => now()]);
        }

        $receiverId = (int) $conversation->user_one_id === (int) $user->id
            ? (int) $conversation->user_two_id
            : (int) $conversation->user_one_id;

        $attachmentData = $this->storeAttachment($request);

        ChatMessage::create([
            'conversation_id'  => $conversation->id,
            'sender_id'        => $user->id,
            'receiver_id'      => $receiverId,
            'message'          => $data['message'] ?? '',
            'status'           => 'sent',
            'attachment_path'  => $attachmentData['attachment_path'] ?? null,
            'attachment_url'   => $attachmentData['attachment_url'] ?? null,
            'attachment_name'  => $attachmentData['attachment_name'] ?? null,
            'attachment_mime'  => $attachmentData['attachment_mime'] ?? null,
            'attachment_size'  => $attachmentData['attachment_size'] ?? null,
            'attachment_type'  => $attachmentData['attachment_type'] ?? null,
        ]);

        $conversation->touch();

        return redirect()->route(
            $user->role === 'merchant' ? 'merchant.chats.show' : 'client.chats.show',
            $conversation
        );
    } catch (\Throwable $e) {
        Log::error('WEB CHAT SEND ERROR', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);

        return back()->withErrors([
            'message' => 'Message send failed. Check laravel.log for exact error.',
        ]);
    }
}

    private function storeAttachment(Request $request): array
    {
        if (! $request->hasFile('attachment')) {
            return [];
        }

        $file = $request->file('attachment');

        if (! $file->isValid()) {
            throw new \Exception('Uploaded file is not valid.');
        }

        $path = $file->store('chat_attachments', 'public');

        if (! $path || ! Storage::disk('public')->exists($path)) {
            throw new \Exception('Attachment upload failed. Check storage permission.');
        }

        $mime = $file->getMimeType() ?: 'application/octet-stream';

        return [
            'attachment_path' => $path,
            'attachment_url' => asset('storage/' . $path),
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_mime' => $mime,
            'attachment_size' => $file->getSize(),
            'attachment_type' => str_starts_with($mime, 'image/') ? 'image' : 'file',
        ];
    }
}
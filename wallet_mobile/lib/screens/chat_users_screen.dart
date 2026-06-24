import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'chat_screen.dart';

class _C {
  static const bg = Color(0xff0a0a0a);
  static const surface = Color(0xff141414);
  static const surfaceAlt = Color(0xff1c1c1e);
  static const border = Color(0xff2a2a2a);

  static const orange = Color(0xffFF4500);
  static const amber = Color(0xffFFB800);
  static const gold = Color(0xffFFD700);

  static const success = Color(0xff22c55e);
  static const red = Color(0xffef4444);
  static const blue = Color(0xff3b82f6);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff8E8E93);

  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [orange, amber, gold],
  );

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xff1a0a00), Color(0xff2d1200), Color(0xff1a0800)],
  );
}

class ChatUsersScreen extends StatefulWidget {
  const ChatUsersScreen({super.key});

  @override
  State<ChatUsersScreen> createState() => _ChatUsersScreenState();
}

class _ChatUsersScreenState extends State<ChatUsersScreen>
    with SingleTickerProviderStateMixin {
  bool loading = true;
  String? errorMsg;
  List conversations = [];
  Timer? _refreshTimer;

  late AnimationController _anim;
  late Animation<double> _fade;
  late Animation<Offset> _slide;

  @override
  void initState() {
    super.initState();

    _anim = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 650),
    );

    _fade = CurvedAnimation(parent: _anim, curve: Curves.easeOut);
    _slide = Tween<Offset>(
      begin: const Offset(0, 0.05),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _anim, curve: Curves.easeOut));

    _anim.forward();

    loadConversations();
    ApiService.chatDelivered();

    _refreshTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      loadConversations(silent: true);
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _anim.dispose();
    super.dispose();
  }

  Future<void> loadConversations({bool silent = false}) async {
    if (!silent) {
      setState(() {
        loading = true;
        errorMsg = null;
      });
    }

    final res = await ApiService.chatConversations();

    if (!mounted) return;

    setState(() {
      loading = false;

      if (res["success"] == true) {
        conversations = List.from(res["conversations"] ?? []);
        errorMsg = null;
      } else {
        errorMsg = res["message"]?.toString() ?? "Failed to load chats";
      }
    });
  }

  void _showLockedMessage() {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: _C.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        content: const Text(
          "This chat is locked. 15 minutes completed.",
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
    );
  }

  String _formatTime(String? raw) {
    if (raw == null || raw.isEmpty) return "";

    try {
      final dt = DateTime.parse(raw).toLocal();
      final now = DateTime.now();
      final diff = now.difference(dt);

      if (diff.inMinutes < 1) return "now";
      if (diff.inHours < 1) return "${diff.inMinutes}m";
      if (diff.inDays < 1) return "${diff.inHours}h";
      if (diff.inDays < 7) return "${diff.inDays}d";

      return "${dt.day}/${dt.month}";
    } catch (_) {
      return "";
    }
  }

  String _txNo(String chatType, String chatId) {
    final padded = chatId.padLeft(9, "0");
    return chatType == "transfer" ? "TRA$padded" : "TNS$padded";
  }

  Widget _topBar() {
    return Container(
      padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
      decoration: const BoxDecoration(
        gradient: _C.gradientCard,
        border: Border(
          bottom: BorderSide(color: Color(0xff3a1500)),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Row(
          children: [
            GestureDetector(
              onTap: () => Navigator.maybePop(context),
              child: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.25),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.white.withOpacity(0.08)),
                ),
                child: const Icon(
                  Icons.arrow_back_ios_new_rounded,
                  color: Colors.white,
                  size: 18,
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    "Transaction Chats",
                    style: TextStyle(
                      color: _C.textPrimary,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.3,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    conversations.isEmpty
                        ? "Transfer and request conversations"
                        : "${conversations.length} conversation${conversations.length == 1 ? '' : 's'}",
                    style: const TextStyle(
                      color: _C.textSecondary,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),
            GestureDetector(
              onTap: () => loadConversations(),
              child: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  gradient: _C.gradientAccent,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.refresh_rounded,
                  color: Colors.black,
                  size: 21,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _emptyState() {
    return Center(
      child: Container(
        margin: const EdgeInsets.all(22),
        padding: const EdgeInsets.all(28),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(28),
          border: Border.all(color: _C.border),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 78,
              height: 78,
              decoration: const BoxDecoration(
                gradient: _C.gradientAccent,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.chat_bubble_outline_rounded,
                color: Colors.black,
                size: 38,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              "No transaction chats yet",
              style: TextStyle(
                color: Colors.white,
                fontSize: 17,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              "Chats appear after a transfer or wallet request",
              textAlign: TextAlign.center,
              style: TextStyle(
                color: _C.textSecondary,
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _errorState() {
    return Center(
      child: Container(
        margin: const EdgeInsets.all(22),
        padding: const EdgeInsets.all(28),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(28),
          border: Border.all(color: _C.border),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.wifi_off_rounded,
              color: _C.red,
              size: 50,
            ),
            const SizedBox(height: 14),
            Text(
              errorMsg ?? "Something went wrong",
              style: const TextStyle(color: Colors.white70),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 18),
            GestureDetector(
              onTap: () => loadConversations(),
              child: Container(
                height: 48,
                padding: const EdgeInsets.symmetric(horizontal: 22),
                decoration: BoxDecoration(
                  gradient: _C.gradientAccent,
                  borderRadius: BorderRadius.circular(15),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.refresh_rounded, color: Colors.black, size: 18),
                    SizedBox(width: 8),
                    Text(
                      "Retry",
                      style: TextStyle(
                        color: Colors.black,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _chatTile(Map<String, dynamic> item) {
    final otherUser = Map<String, dynamic>.from(item["other_user"] ?? {});
    final name = otherUser["name"]?.toString() ?? "User";
    final role = otherUser["role"]?.toString() ?? "";

    final photoUrl = ApiService.fixUrl(
      otherUser["photo_url"] ?? otherUser["photo"],
    );

    final isOnline = otherUser["is_online"] == true ||
        otherUser["is_online"] == 1 ||
        otherUser["is_online"]?.toString() == "1";

    final bool isLocked = item["is_locked"] == true ||
        item["is_locked"] == 1 ||
        item["is_locked"]?.toString() == "1";

    final chatType = item["chat_type"]?.toString() ?? "transfer";

    final rawChatId = item["chat_id"];
    final chatIdInt = rawChatId is int
        ? rawChatId
        : int.tryParse(rawChatId?.toString() ?? "") ?? 0;

    final chatId = chatIdInt > 0 ? chatIdInt.toString() : "";

    final lastMsg = item["last_message"]?.toString();
    final lastMsgAt = item["last_message_at"]?.toString();
    final unreadCount = (item["unread_count"] as num?)?.toInt() ?? 0;

    final bool isTransfer = chatType == "transfer";
    final String badgeLabel =
        isLocked ? "Locked" : "${isTransfer ? 'Transfer' : 'Request'} ${_txNo(chatType, chatId)}";

    return FadeTransition(
      opacity: _fade,
      child: SlideTransition(
        position: _slide,
        child: GestureDetector(
          onTap: chatId.isEmpty
              ? null
              : () async {
                  if (isLocked) {
                    _showLockedMessage();
                    return;
                  }

                  await Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => ChatScreen(
                        otherUser: otherUser,
                        chatType: chatType,
                        chatId: chatId,
                      ),
                    ),
                  );

                  loadConversations(silent: true);
                },
          child: Container(
            margin: const EdgeInsets.fromLTRB(18, 0, 18, 14),
            decoration: BoxDecoration(
              color: _C.surface,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(
                color: isLocked
                    ? _C.red
                    : unreadCount > 0
                        ? _C.amber
                        : _C.border,
                width: isLocked || unreadCount > 0 ? 1.5 : 1,
              ),
              boxShadow: [
                BoxShadow(
                  color: unreadCount > 0
                      ? _C.orange.withOpacity(0.10)
                      : Colors.black.withOpacity(0.22),
                  blurRadius: 16,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(24),
              child: Column(
                children: [
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      children: [
                        Stack(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(2),
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                gradient: isLocked ? null : _C.gradientAccent,
                                color: isLocked ? _C.red : null,
                              ),
                              child: CircleAvatar(
                                radius: 28,
                                backgroundColor: _C.surfaceAlt,
                                backgroundImage: photoUrl.isNotEmpty
                                    ? NetworkImage(photoUrl)
                                    : null,
                                child: photoUrl.isEmpty
                                    ? Text(
                                        name.isNotEmpty
                                            ? name[0].toUpperCase()
                                            : "U",
                                        style: const TextStyle(
                                          color: _C.amber,
                                          fontWeight: FontWeight.w900,
                                          fontSize: 19,
                                        ),
                                      )
                                    : null,
                              ),
                            ),
                            Positioned(
                              right: 0,
                              bottom: 0,
                              child: Container(
                                width: 14,
                                height: 14,
                                decoration: BoxDecoration(
                                  color: isLocked
                                      ? _C.red
                                      : isOnline
                                          ? _C.success
                                          : _C.textSecondary,
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: _C.surface,
                                    width: 2,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      name,
                                      overflow: TextOverflow.ellipsis,
                                      style: TextStyle(
                                        color: isLocked
                                            ? Colors.white70
                                            : Colors.white,
                                        fontWeight: unreadCount > 0
                                            ? FontWeight.w900
                                            : FontWeight.w800,
                                        fontSize: 16,
                                      ),
                                    ),
                                  ),
                                  Text(
                                    _formatTime(lastMsgAt),
                                    style: TextStyle(
                                      color: isLocked
                                          ? _C.red
                                          : unreadCount > 0
                                              ? _C.amber
                                              : _C.textSecondary,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Text(
                                    isLocked
                                        ? "Locked"
                                        : isOnline
                                            ? "Online"
                                            : "Offline",
                                    style: TextStyle(
                                      color: isLocked
                                          ? _C.red
                                          : isOnline
                                              ? _C.success
                                              : _C.textSecondary,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                  if (role.isNotEmpty) ...[
                                    const SizedBox(width: 6),
                                    const Text(
                                      "•",
                                      style: TextStyle(
                                        color: Colors.white38,
                                        fontSize: 11,
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Text(
                                      role.toUpperCase(),
                                      style: const TextStyle(
                                        color: Colors.white54,
                                        fontSize: 10,
                                        fontWeight: FontWeight.w900,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                              const SizedBox(height: 8),
                              _chatBadge(
                                isTransfer: isTransfer,
                                isLocked: isLocked,
                                label: badgeLabel,
                              ),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      isLocked
                                          ? "🔒 Chat locked"
                                          : (lastMsg ?? "No messages yet"),
                                      overflow: TextOverflow.ellipsis,
                                      style: TextStyle(
                                        color: isLocked
                                            ? _C.red
                                            : unreadCount > 0
                                                ? Colors.white70
                                                : _C.textSecondary,
                                        fontSize: 13,
                                        fontWeight: isLocked || unreadCount > 0
                                            ? FontWeight.w800
                                            : FontWeight.w500,
                                      ),
                                    ),
                                  ),
                                  if (unreadCount > 0 && !isLocked)
                                    Container(
                                      margin: const EdgeInsets.only(left: 8),
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 4,
                                      ),
                                      decoration: BoxDecoration(
                                        gradient: _C.gradientAccent,
                                        borderRadius: BorderRadius.circular(14),
                                      ),
                                      child: Text(
                                        unreadCount > 99 ? "99+" : "$unreadCount",
                                        style: const TextStyle(
                                          color: Colors.black,
                                          fontSize: 11,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                                    ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (isLocked)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      color: _C.red.withOpacity(0.14),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.lock_rounded, color: _C.red, size: 15),
                          SizedBox(width: 6),
                          Text(
                            "Locked after 15 minutes",
                            style: TextStyle(
                              color: _C.red,
                              fontSize: 12,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ],
                      ),
                    )
                  else if (unreadCount > 0)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      decoration: const BoxDecoration(
                        gradient: _C.gradientAccent,
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.mark_chat_unread_rounded,
                            color: Colors.black,
                            size: 15,
                          ),
                          SizedBox(width: 6),
                          Text(
                            "New Message",
                            style: TextStyle(
                              color: Colors.black,
                              fontSize: 12,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _chatBadge({
    required bool isTransfer,
    required bool isLocked,
    required String label,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: isLocked
            ? _C.red.withOpacity(0.10)
            : isTransfer
                ? _C.blue.withOpacity(0.10)
                : _C.orange.withOpacity(0.10),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: isLocked
              ? _C.red.withOpacity(0.35)
              : isTransfer
                  ? _C.blue.withOpacity(0.28)
                  : _C.orange.withOpacity(0.28),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            isLocked
                ? Icons.lock_rounded
                : isTransfer
                    ? Icons.swap_horiz_rounded
                    : Icons.receipt_long_rounded,
            color: isLocked ? _C.red : _C.amber,
            size: 13,
          ),
          const SizedBox(width: 6),
          Flexible(
            child: Text(
              label,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: isLocked ? _C.red : _C.amber,
                fontSize: 10,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _listBody() {
    if (loading) {
      return const Center(
        child: CircularProgressIndicator(
          color: _C.orange,
          strokeWidth: 2.5,
        ),
      );
    }

    if (errorMsg != null) return _errorState();

    if (conversations.isEmpty) return _emptyState();

    return RefreshIndicator(
      onRefresh: loadConversations,
      color: _C.orange,
      backgroundColor: _C.surface,
      child: ListView.builder(
        padding: const EdgeInsets.only(top: 18, bottom: 24),
        itemCount: conversations.length,
        itemBuilder: (context, index) {
          return _chatTile(
            Map<String, dynamic>.from(conversations[index]),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: Column(
        children: [
          _topBar(),
          Expanded(child: _listBody()),
        ],
      ),
    );
  }
}
import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ChatDetailScreen extends StatefulWidget {
  final dynamic requestId;
  final Map currentUser;

  const ChatDetailScreen({
    super.key,
    required this.requestId,
    required this.currentUser,
  });

  @override
  State<ChatDetailScreen> createState() => _ChatDetailScreenState();
}

class _ChatDetailScreenState extends State<ChatDetailScreen> {
  final TextEditingController messageCtrl = TextEditingController();
  final ScrollController scrollCtrl = ScrollController();

  bool loading = true;
  bool sending = false;
  List messages = [];
  Map<String, dynamic>? otherUser;
  Map<String, dynamic>? walletRequest;
  Timer? timer;

  @override
  void initState() {
    super.initState();
    loadMessages();
    timer = Timer.periodic(
      const Duration(seconds: 3),
      (_) => loadMessages(silent: true),
    );
  }

  @override
  void dispose() {
    timer?.cancel();
    messageCtrl.dispose();
    scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> loadMessages({bool silent = false}) async {
    if (!silent) setState(() => loading = true);

    final res = await ApiService.chatMessages(widget.requestId.toString());

    if (!mounted) return;

    if (res['success'] == true) {
      setState(() {
        messages = res['messages'] ?? [];

        if (res['other_user'] != null) {
          otherUser = Map<String, dynamic>.from(res['other_user']);
        }

        if (res['request'] != null) {
          walletRequest = Map<String, dynamic>.from(res['request']);
        }

        loading = false;
      });

      WidgetsBinding.instance.addPostFrameCallback((_) => scrollToBottom());
    } else {
      setState(() => loading = false);
    }
  }

  void scrollToBottom() {
    if (!scrollCtrl.hasClients) return;

    scrollCtrl.animateTo(
      scrollCtrl.position.maxScrollExtent,
      duration: const Duration(milliseconds: 250),
      curve: Curves.easeOut,
    );
  }

  Future<void> sendMessage() async {
    final text = messageCtrl.text.trim();

    if (text.isEmpty || sending) return;

    setState(() => sending = true);

    final res = await ApiService.sendChatMessage(
      widget.requestId.toString(),
      text,
    );

    if (!mounted) return;

    setState(() => sending = false);

    if (res['success'] == true) {
      messageCtrl.clear();
      await loadMessages(silent: true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message']?.toString() ?? 'Message failed'),
        ),
      );
    }
  }

  bool isMe(Map msg) {
    return msg['sender_id']?.toString() ==
        widget.currentUser['id']?.toString();
  }

  bool isOnline(Map? user) {
    if (user == null) return false;

    return user['is_online'] == true ||
        user['is_online'] == 1 ||
        user['is_online']?.toString() == '1';
  }

  String onlineText() {
    if (isOnline(otherUser)) return 'online';

    final last = otherUser?['last_seen_at']?.toString();

    if (last == null || last.isEmpty || last == 'null') {
      return 'offline';
    }

    return 'last seen $last';
  }

  String formatMessageTime(dynamic value) {
    final raw = value?.toString() ?? '';

    if (raw.length >= 16) {
      return raw.substring(11, 16);
    }

    return '';
  }

  Widget tickIcon(String status) {
    if (status == 'seen') {
      return const Icon(
        Icons.done_all_rounded,
        size: 16,
        color: Color(0xff34b7f1),
      );
    }

    if (status == 'delivered') {
      return const Icon(
        Icons.done_all_rounded,
        size: 16,
        color: Colors.white54,
      );
    }

    return const Icon(
      Icons.done_rounded,
      size: 16,
      color: Colors.white54,
    );
  }

  Widget bubble(Map<String, dynamic> msg) {
    final mine = isMe(msg);

    return Align(
      alignment: mine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.fromLTRB(14, 10, 10, 7),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.76,
        ),
        decoration: BoxDecoration(
          color: mine ? const Color(0xff005c4b) : const Color(0xff202c33),
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(16),
            topRight: const Radius.circular(16),
            bottomLeft: Radius.circular(mine ? 16 : 3),
            bottomRight: Radius.circular(mine ? 3 : 16),
          ),
        ),
        child: Column(
          crossAxisAlignment:
              mine ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            Text(
              msg['message']?.toString() ?? '',
              style: const TextStyle(
                color: Colors.white,
                fontSize: 15,
                height: 1.3,
              ),
            ),
            const SizedBox(height: 5),
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  formatMessageTime(msg['created_at']),
                  style: const TextStyle(
                    color: Colors.white54,
                    fontSize: 10,
                  ),
                ),
                if (mine) ...[
                  const SizedBox(width: 4),
                  tickIcon(msg['status']?.toString() ?? 'sent'),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final title = otherUser?['name']?.toString() ?? 'Chat';

    final requestType = walletRequest?['type']?.toString() == 'withdrawal'
        ? 'Sell USD'
        : 'Buy USD';

    final amount = walletRequest?['amount']?.toString() ?? '';
    final photo = ApiService.fixUrl(otherUser?['photo_url']);

    return Scaffold(
      backgroundColor: const Color(0xff0b141a),
      appBar: AppBar(
        backgroundColor: const Color(0xff181a20),
        titleSpacing: 0,
        title: Row(
          children: [
            CircleAvatar(
              backgroundColor: const Color(0xff00ff5a),
              backgroundImage: photo.isNotEmpty ? NetworkImage(photo) : null,
              child: photo.isEmpty
                  ? const Icon(Icons.person, color: Colors.black)
                  : null,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 16),
                  ),
                  Text(
                    '$requestType $amount • ${onlineText()}',
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 11,
                      color: Colors.white60,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      body: loading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xff00ff5a)),
            )
          : Column(
              children: [
                Expanded(
                  child: ListView.builder(
                    controller: scrollCtrl,
                    padding: const EdgeInsets.fromLTRB(12, 14, 12, 8),
                    itemCount: messages.length,
                    itemBuilder: (context, index) {
                      return bubble(
                        Map<String, dynamic>.from(messages[index]),
                      );
                    },
                  ),
                ),
                Container(
                  padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
                  color: const Color(0xff181a20),
                  child: Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: messageCtrl,
                          style: const TextStyle(color: Colors.white),
                          minLines: 1,
                          maxLines: 4,
                          decoration: InputDecoration(
                            hintText: 'Message',
                            hintStyle:
                                const TextStyle(color: Colors.white54),
                            filled: true,
                            fillColor: const Color(0xff2a3942),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 11,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(26),
                              borderSide: BorderSide.none,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      CircleAvatar(
                        radius: 23,
                        backgroundColor: const Color(0xff00ff5a),
                        child: IconButton(
                          onPressed: sending ? null : sendMessage,
                          icon: sending
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: Colors.black,
                                  ),
                                )
                              : const Icon(Icons.send_rounded),
                          color: Colors.black,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}
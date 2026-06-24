import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'chat_detail_screen.dart';

class ChatListScreen extends StatefulWidget {
  final Map user;

  const ChatListScreen({super.key, required this.user});

  @override
  State<ChatListScreen> createState() => _ChatListScreenState();
}

class _ChatListScreenState extends State<ChatListScreen> {
  bool loading = true;
  List conversations = [];
  Timer? timer;

  @override
  void initState() {
    super.initState();
    loadChats();
    timer = Timer.periodic(
      const Duration(seconds: 5),
      (_) => loadChats(silent: true),
    );
  }

  @override
  void dispose() {
    timer?.cancel();
    super.dispose();
  }

  Future<void> loadChats({bool silent = false}) async {
    if (!silent) setState(() => loading = true);

    await ApiService.chatDelivered();
    final res = await ApiService.chatConversations();

    if (!mounted) return;

    if (res['success'] == true) {
      setState(() {
        conversations = res['conversations'] ?? [];
        loading = false;
      });
    } else {
      setState(() => loading = false);
    }
  }

  String requestTitle(Map request) {
    final type =
        request['type']?.toString() == 'withdrawal' ? 'Sell USD' : 'Buy USD';
    return '$type • \$ ${request['amount'] ?? '0.00'}';
  }

  String lastText(dynamic last) {
    if (last == null) return 'Tap to start chat';
    return last['message']?.toString() ?? 'Message';
  }

  bool isOnline(Map user) {
    return user['is_online'] == true ||
        user['is_online'] == 1 ||
        user['is_online']?.toString() == '1';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xff0b0e11),
      appBar: AppBar(
        backgroundColor: const Color(0xff181a20),
        title: const Text('Chats'),
      ),
      body: RefreshIndicator(
        onRefresh: loadChats,
        child: loading
            ? const Center(
                child: CircularProgressIndicator(color: Color(0xff00ff5a)),
              )
            : conversations.isEmpty
                ? ListView(
                    children: const [
                      SizedBox(height: 180),
                      Center(
                        child: Text(
                          'No request chats yet',
                          style: TextStyle(color: Colors.white54),
                        ),
                      ),
                    ],
                  )
                : ListView.builder(
                    itemCount: conversations.length,
                    itemBuilder: (context, index) {
                      final item =
                          Map<String, dynamic>.from(conversations[index]);
                      final request =
                          Map<String, dynamic>.from(item['request']);
                      final other =
                          Map<String, dynamic>.from(item['other_user']);
                      final unread = int.tryParse(
                            item['unread_count']?.toString() ?? '0',
                          ) ??
                          0;
                      final online = isOnline(other);
                      final photo = ApiService.fixUrl(other['photo_url']);

                      return ListTile(
                        onTap: () async {
                          await Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ChatDetailScreen(
                                requestId: request['id'],
                                currentUser: widget.user,
                              ),
                            ),
                          );
                          loadChats();
                        },
                        leading: Stack(
                          children: [
                            CircleAvatar(
                              radius: 25,
                              backgroundColor: const Color(0xff00ff5a),
                              backgroundImage:
                                  photo.isNotEmpty ? NetworkImage(photo) : null,
                              child: photo.isEmpty
                                  ? const Icon(Icons.person,
                                      color: Colors.black)
                                  : null,
                            ),
                            Positioned(
                              right: 0,
                              bottom: 0,
                              child: CircleAvatar(
                                radius: 6,
                                backgroundColor:
                                    online ? Colors.green : Colors.grey,
                              ),
                            ),
                          ],
                        ),
                        title: Text(
                          other['name']?.toString() ?? 'User',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        subtitle: Text(
                          '${requestTitle(request)}\n${lastText(item['last_message'])}',
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(color: Colors.white54),
                        ),
                        trailing: unread > 0
                            ? CircleAvatar(
                                radius: 13,
                                backgroundColor: const Color(0xff00ff5a),
                                child: Text(
                                  unread.toString(),
                                  style: const TextStyle(
                                    color: Colors.black,
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              )
                            : const Icon(
                                Icons.chevron_right,
                                color: Colors.white38,
                              ),
                      );
                    },
                  ),
      ),
    );
  }
}
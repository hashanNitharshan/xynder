import 'dart:async';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';

import '../services/api_service.dart';

class _C {
  static const bg = Color(0xff0B0E11);
  static const surface = Color(0xff181A20);
  static const surfaceAlt = Color(0xff1E2329);
  static const border = Color(0xff2B3139);

  static const yellow = Color(0xffF0B90B);
  static const yellowDark = Color(0xffC99400);

  static const green = Color(0xff02C076);
  static const red = Color(0xffF6465D);
  static const blue = Color(0xff3B82F6);

  static const text = Colors.white;
  static const muted = Color(0xff848E9C);

  static const yellowGradient = LinearGradient(
    colors: [Color(0xffD8A800), Color(0xffF0B90B)],
  );
}

class ChatScreen extends StatefulWidget {
  final Map<String, dynamic> otherUser;
  final String chatType;
  final String chatId;

  const ChatScreen({
    super.key,
    required this.otherUser,
    required this.chatType,
    required this.chatId,
  });

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  // How long a chat stays open after it is first created, if the
  // backend does not tell us otherwise.
  static const int _chatLifetimeSeconds = 15 * 60; // 15 minutes

  final TextEditingController _messageCtrl = TextEditingController();
  final ScrollController _scrollCtrl = ScrollController();
  final ImagePicker _picker = ImagePicker();

  bool _loading = true;
  bool _sending = false;
  bool _isLocked = false;

  int _remainingSeconds = 0;

  List _messages = [];

  // Polls the server for new messages / lock state.
  Timer? _pollTimer;

  // Ticks the on-screen countdown down every second, and auto-locks
  // the chat locally the moment it reaches zero (in addition to
  // whatever the server says), so a chat can never be used past its
  // 15 minute window even if a poll is delayed.
  Timer? _countdownTimer;

  String? _myUserId;
  DateTime? _chatOpenedAt;

  @override
  void initState() {
    super.initState();

    _chatOpenedAt = DateTime.now();

    _loadMyProfile();
    _loadMessages();
    ApiService.chatDelivered();

    _pollTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      _loadMessages(showLoading: false);
    });
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _countdownTimer?.cancel();
    _messageCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadMyProfile() async {
    final res = await ApiService.profile();
    if (!mounted) return;

    if (res["success"] == true && res["user"] != null) {
      setState(() {
        _myUserId = res["user"]["id"].toString();
      });
    }
  }

  Future<void> _loadMessages({bool showLoading = true}) async {
    if (showLoading && mounted) {
      setState(() => _loading = true);
    }

    final res = widget.chatType == "transfer"
        ? await ApiService.transferChatMessages(widget.chatId)
        : await ApiService.requestChatMessages(widget.chatId);

    if (!mounted) return;

    final bool serverLocked = res["is_locked"] == true ||
        res["locked"] == true ||
        res["chat_locked"] == true ||
        res["chat_is_locked"] == true ||
        res["status_code"] == 423;

    final newMessages = List.from(res["messages"] ?? []);
    final grew = newMessages.length > _messages.length;

    // Prefer the server's remaining_seconds when it sends one.
    // Otherwise fall back to our own 15-minute-from-open calculation
    // so the chat still auto-locks even if the backend doesn't
    // return a countdown value.
    int? serverRemaining =
        int.tryParse(res["remaining_seconds"]?.toString() ?? "");

    int computedRemaining;
    if (serverRemaining != null) {
      computedRemaining = serverRemaining;
    } else if (_chatOpenedAt != null) {
      final elapsed = DateTime.now().difference(_chatOpenedAt!).inSeconds;
      computedRemaining = _chatLifetimeSeconds - elapsed;
    } else {
      computedRemaining = _remainingSeconds;
    }

    final bool timeExpired = computedRemaining <= 0;

    setState(() {
      _messages = newMessages;
      _isLocked = serverLocked || timeExpired;
      _remainingSeconds = _isLocked ? 0 : computedRemaining;
      _loading = false;
    });

    _restartCountdownTimer();

    if (grew || showLoading) {
      Future.delayed(const Duration(milliseconds: 120), _scrollToBottom);
    }
  }

  // Runs a local, once-per-second countdown so the timer shown to the
  // user is smooth (not just updated every 3s poll) and so the chat
  // gets locked immediately once the 15 minutes run out, without
  // waiting for the next poll.
  void _restartCountdownTimer() {
    _countdownTimer?.cancel();

    if (_isLocked || _remainingSeconds <= 0) return;

    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!mounted) {
        t.cancel();
        return;
      }

      setState(() {
        if (_remainingSeconds > 1) {
          _remainingSeconds--;
        } else {
          _remainingSeconds = 0;
          _isLocked = true;
        }
      });

      if (_isLocked) t.cancel();
    });
  }

  Future<void> _sendMessage() async {
    if (_isLocked || _sending) return;

    final text = _messageCtrl.text.trim();
    if (text.isEmpty) return;

    setState(() => _sending = true);
    _messageCtrl.clear();

    final res = await ApiService.sendChatMessageMultipart(
      chatType: widget.chatType,
      chatId: widget.chatId,
      message: text,
    );

    if (!mounted) return;

    final bool locked = res["is_locked"] == true ||
        res["locked"] == true ||
        res["chat_locked"] == true ||
        res["chat_is_locked"] == true ||
        res["status_code"] == 423;

    setState(() {
      _sending = false;
      if (locked) {
        _isLocked = true;
        _remainingSeconds = 0;
      }
    });

    if (locked) {
      _countdownTimer?.cancel();
      return;
    }

    if (res["success"] == true) {
      await _loadMessages(showLoading: false);
      Future.delayed(const Duration(milliseconds: 80), _scrollToBottom);
    }
  }

  Future<void> _sendImage(ImageSource source) async {
    if (_isLocked || _sending) return;

    final image = await _picker.pickImage(source: source, imageQuality: 80);
    if (image == null) return;

    setState(() => _sending = true);

    final res = await ApiService.sendChatMessageMultipart(
      chatType: widget.chatType,
      chatId: widget.chatId,
      message: _messageCtrl.text.trim(),
      image: image,
    );

    if (!mounted) return;

    _messageCtrl.clear();

    final bool locked = res["is_locked"] == true ||
        res["locked"] == true ||
        res["chat_locked"] == true ||
        res["chat_is_locked"] == true ||
        res["status_code"] == 423;

    setState(() {
      _sending = false;
      if (locked) {
        _isLocked = true;
        _remainingSeconds = 0;
      }
    });

    if (locked) {
      _countdownTimer?.cancel();
      return;
    }

    if (res["success"] == true) {
      await _loadMessages(showLoading: false);
      Future.delayed(const Duration(milliseconds: 80), _scrollToBottom);
    }
  }

  Future<void> _sendFile() async {
    if (_isLocked || _sending) return;

    final result = await FilePicker.platform.pickFiles(
      withData: true,
      type: FileType.custom,
      allowedExtensions: [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'txt',
        'zip',
      ],
    );

    if (result == null || result.files.isEmpty) return;

    setState(() => _sending = true);

    final res = await ApiService.sendChatMessageMultipart(
      chatType: widget.chatType,
      chatId: widget.chatId,
      message: _messageCtrl.text.trim(),
      file: result.files.first,
    );

    if (!mounted) return;

    _messageCtrl.clear();

    final bool locked = res["is_locked"] == true ||
        res["locked"] == true ||
        res["chat_locked"] == true ||
        res["chat_is_locked"] == true ||
        res["status_code"] == 423;

    setState(() {
      _sending = false;
      if (locked) {
        _isLocked = true;
        _remainingSeconds = 0;
      }
    });

    if (locked) {
      _countdownTimer?.cancel();
      return;
    }

    if (res["success"] == true) {
      await _loadMessages(showLoading: false);
      Future.delayed(const Duration(milliseconds: 80), _scrollToBottom);
    }
  }

  void _openAttachment(String url) async {
    final uri = Uri.parse(ApiService.fixUrl(url));
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  void _showAttachmentMenu() {
    if (_isLocked) return;

    showModalBottomSheet(
      context: context,
      backgroundColor: _C.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                _attachmentTile(
                  icon: Icons.photo_library_rounded,
                  title: "Gallery",
                  onTap: () {
                    Navigator.pop(context);
                    _sendImage(ImageSource.gallery);
                  },
                ),
                _attachmentTile(
                  icon: Icons.camera_alt_rounded,
                  title: "Camera",
                  onTap: () {
                    Navigator.pop(context);
                    _sendImage(ImageSource.camera);
                  },
                ),
                _attachmentTile(
                  icon: Icons.attach_file_rounded,
                  title: "File",
                  onTap: () {
                    Navigator.pop(context);
                    _sendFile();
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _attachmentTile({
    required IconData icon,
    required String title,
    required VoidCallback onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: _C.surfaceAlt,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _C.border),
      ),
      child: ListTile(
        onTap: onTap,
        leading: Icon(icon, color: _C.yellow),
        title: Text(
          title,
          style: const TextStyle(
            color: _C.text,
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
    );
  }

  void _scrollToBottom() {
    if (!_scrollCtrl.hasClients) return;

    _scrollCtrl.animateTo(
      _scrollCtrl.position.maxScrollExtent,
      duration: const Duration(milliseconds: 250),
      curve: Curves.easeOut,
    );
  }

  String _remainingLabel() {
    if (_remainingSeconds <= 0) return "";
    final m = (_remainingSeconds ~/ 60).toString().padLeft(2, '0');
    final s = (_remainingSeconds % 60).toString().padLeft(2, '0');
    return "$m:$s";
  }

  String _transactionNo() {
    final raw = widget.chatId.padLeft(9, '0');
    return "TNS$raw";
  }

  // ═══════════════════════════════════════════
  //  TOP BAR - status shown as a dot on the photo, no text
  // ═══════════════════════════════════════════
  Widget _topBar() {
    final name = widget.otherUser["name"]?.toString() ?? "User";
    final photoUrl = ApiService.fixUrl(
      widget.otherUser["photo_url"] ?? widget.otherUser["photo"],
    );

    final isOnline = widget.otherUser["is_online"] == true ||
        widget.otherUser["is_online"] == 1 ||
        widget.otherUser["is_online"]?.toString() == "1";

    final statusColor = _isLocked ? _C.red : (isOnline ? _C.green : _C.red);

    return Container(
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
      decoration: const BoxDecoration(
        color: _C.bg,
        border: Border(bottom: BorderSide(color: _C.border)),
      ),
      child: SafeArea(
        bottom: false,
        child: Row(
          children: [
            GestureDetector(
              onTap: () => Navigator.maybePop(context),
              child: const SizedBox(
                width: 38,
                height: 38,
                child: Icon(
                  Icons.arrow_back_ios_new_rounded,
                  color: _C.text,
                  size: 18,
                ),
              ),
            ),
            const SizedBox(width: 8),
            Stack(
              clipBehavior: Clip.none,
              children: [
                CircleAvatar(
                  radius: 21,
                  backgroundColor: _C.surfaceAlt,
                  backgroundImage:
                      photoUrl.isNotEmpty ? NetworkImage(photoUrl) : null,
                  child: photoUrl.isEmpty
                      ? Text(
                          name.isNotEmpty ? name[0].toUpperCase() : "U",
                          style: const TextStyle(
                            color: _C.yellow,
                            fontWeight: FontWeight.w900,
                          ),
                        )
                      : null,
                ),
                Positioned(
                  bottom: -1,
                  right: -1,
                  child: Container(
                    width: 13,
                    height: 13,
                    decoration: BoxDecoration(
                      color: statusColor,
                      shape: BoxShape.circle,
                      border: Border.all(color: _C.bg, width: 2),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(width: 11),
            Expanded(
              child: Text(
                name,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: _C.text,
                  fontSize: 16,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _transactionBar() {
    final time = _remainingLabel();

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.fromLTRB(14, 12, 14, 8),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _C.border),
      ),
      child: Row(
        children: [
          const Icon(Icons.receipt_long_rounded, color: _C.yellow, size: 18),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              _transactionNo(),
              style: const TextStyle(
                color: _C.yellow,
                fontSize: 13,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.4,
              ),
            ),
          ),
          if (!_isLocked && time.isNotEmpty)
            Text(
              time,
              style: const TextStyle(
                color: _C.muted,
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
            ),
          if (_isLocked)
            const Text(
              "Locked",
              style: TextStyle(
                color: _C.red,
                fontSize: 12,
                fontWeight: FontWeight.w900,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildAttachment(Map msg, bool isMe) {
    final attachmentUrl = msg["attachment_url"]?.toString() ?? "";
    final attachmentType = msg["attachment_type"]?.toString() ?? "";
    final attachmentName = msg["attachment_name"]?.toString() ?? "File";

    if (attachmentUrl.isEmpty) return const SizedBox.shrink();

    final fixedUrl = ApiService.fixUrl(attachmentUrl);

    if (attachmentType == "image") {
      return GestureDetector(
        onTap: () => _openAttachment(fixedUrl),
        child: Container(
          margin: const EdgeInsets.only(bottom: 4),
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: isMe ? Colors.black26 : _C.border),
          ),
          child: Image.network(
            fixedUrl,
            width: 200,
            fit: BoxFit.cover,
          ),
        ),
      );
    }

    return GestureDetector(
      onTap: () => _openAttachment(fixedUrl),
      child: Container(
        margin: const EdgeInsets.only(bottom: 4),
        padding: const EdgeInsets.all(11),
        decoration: BoxDecoration(
          color: isMe ? Colors.black.withOpacity(0.08) : _C.bg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: isMe ? Colors.black26 : _C.border),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.insert_drive_file_rounded,
              color: isMe ? Colors.black87 : _C.yellow,
              size: 22,
            ),
            const SizedBox(width: 8),
            Flexible(
              child: Text(
                attachmentName,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: isMe ? Colors.black : Colors.white,
                  fontSize: 13,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  BUBBLE - single line: message, time, and tick together
  // ═══════════════════════════════════════════
  Widget _buildBubble(Map msg) {
    final senderId = msg["sender_id"]?.toString();
    final bool isMe = _myUserId != null && senderId == _myUserId;

    final text = msg["message"]?.toString() ?? "";
    final status = msg["status"]?.toString() ?? "sent";
    final createdAt = msg["created_at"]?.toString() ?? "";
    final hasAttachment = (msg["attachment_url"]?.toString() ?? "").isNotEmpty;

    String timeLabel = "";
    try {
      final dt = DateTime.parse(createdAt).toLocal();
      timeLabel =
          "${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}";
    } catch (_) {}

    final metaRow = Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          timeLabel,
          style: TextStyle(
            color: isMe ? Colors.black54 : Colors.white38,
            fontSize: 10,
            fontWeight: FontWeight.w800,
          ),
        ),
        if (isMe) ...[
          const SizedBox(width: 3),
          Icon(
            status == "seen" || status == "delivered"
                ? Icons.done_all_rounded
                : Icons.done_rounded,
            size: 13,
            color: status == "seen" ? _C.blue : Colors.black.withOpacity(0.55),
          ),
        ],
      ],
    );

    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.fromLTRB(14, 9, 10, 9),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.78,
        ),
        decoration: BoxDecoration(
          gradient: isMe ? _C.yellowGradient : null,
          color: isMe ? null : _C.surface,
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(18),
            topRight: const Radius.circular(18),
            bottomLeft: Radius.circular(isMe ? 18 : 5),
            bottomRight: Radius.circular(isMe ? 5 : 18),
          ),
          border: Border.all(
            color: isMe ? Colors.transparent : _C.border,
          ),
        ),
        child: Column(
          crossAxisAlignment:
              isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            if (hasAttachment) _buildAttachment(msg, isMe),
            if (text.isNotEmpty)
              Row(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Flexible(
                    child: Text(
                      text,
                      style: TextStyle(
                        color: isMe ? Colors.black : Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  metaRow,
                ],
              )
            else
              metaRow,
          ],
        ),
      ),
    );
  }

  Widget _buildDateDivider(String label) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Center(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
          decoration: BoxDecoration(
            color: _C.surface,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: _C.border),
          ),
          child: Text(
            label,
            style: const TextStyle(
              color: _C.muted,
              fontSize: 11,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
      ),
    );
  }

  List<Widget> _buildMessageList() {
    final widgets = <Widget>[];
    String? lastDate;

    for (final msg in _messages) {
      final raw = msg["created_at"]?.toString() ?? "";
      String dateLabel = "";

      try {
        final dt = DateTime.parse(raw).toLocal();
        final now = DateTime.now();

        if (dt.year == now.year && dt.month == now.month && dt.day == now.day) {
          dateLabel = "Today";
        } else {
          dateLabel = "${dt.day}/${dt.month}/${dt.year}";
        }
      } catch (_) {}

      if (dateLabel.isNotEmpty && dateLabel != lastDate) {
        widgets.add(_buildDateDivider(dateLabel));
        lastDate = dateLabel;
      }

      widgets.add(_buildBubble(Map<String, dynamic>.from(msg)));
    }

    return widgets;
  }

  Widget _emptyState() {
    return const Center(
      child: Text(
        "No messages yet",
        style: TextStyle(
          color: _C.muted,
          fontSize: 13,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }

  Widget _lockedInput() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
      decoration: const BoxDecoration(
        color: _C.surface,
        border: Border(top: BorderSide(color: _C.border)),
      ),
      child: const SafeArea(
        top: false,
        child: Center(
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.lock_rounded, color: _C.red, size: 17),
              SizedBox(width: 8),
              Text(
                "Chat closed / locked",
                style: TextStyle(
                  color: _C.red,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _messageInput() {
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 9, 12, 11),
      decoration: const BoxDecoration(
        color: _C.surface,
        border: Border(top: BorderSide(color: _C.border)),
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            GestureDetector(
              onTap: _sending ? null : _showAttachmentMenu,
              child: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: _C.bg,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: _C.border),
                ),
                child: const Icon(
                  Icons.add_rounded,
                  color: _C.yellow,
                  size: 25,
                ),
              ),
            ),
            const SizedBox(width: 9),
            Expanded(
              child: TextField(
                controller: _messageCtrl,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                ),
                minLines: 1,
                maxLines: 4,
                decoration: InputDecoration(
                  hintText: "Message",
                  hintStyle: const TextStyle(color: _C.muted),
                  filled: true,
                  fillColor: _C.bg,
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 11,
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: const BorderSide(color: _C.border),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: const BorderSide(color: _C.yellow, width: 1.4),
                  ),
                ),
                onSubmitted: (_) => _sendMessage(),
              ),
            ),
            const SizedBox(width: 9),
            GestureDetector(
              onTap: _sending ? null : _sendMessage,
              child: Container(
                width: 46,
                height: 46,
                decoration: const BoxDecoration(
                  gradient: _C.yellowGradient,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: _sending
                      ? const SizedBox(
                          width: 17,
                          height: 17,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.black,
                          ),
                        )
                      : const Icon(
                          Icons.send_rounded,
                          color: Colors.black,
                          size: 21,
                        ),
                ),
              ),
            ),
          ],
        ),
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
          _transactionBar(),
          Expanded(
            child: _loading
                ? const Center(
                    child: CircularProgressIndicator(
                      color: _C.yellow,
                      strokeWidth: 2.5,
                    ),
                  )
                : _messages.isEmpty
                    ? _emptyState()
                    : ListView(
                        controller: _scrollCtrl,
                        padding: const EdgeInsets.fromLTRB(14, 8, 14, 10),
                        children: _buildMessageList(),
                      ),
          ),
          _isLocked ? _lockedInput() : _messageInput(),
        ],
      ),
    );
  }
}
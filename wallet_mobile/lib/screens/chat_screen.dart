import 'dart:async';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';

import '../services/api_service.dart';

class _C {
  static const bg = Color(0xff0a0a0a);
  static const surface = Color(0xff141414);
  static const surfaceAlt = Color(0xff1c1c1e);
  static const border = Color(0xff2a2a2a);
  static const borderFaint = Color(0xff1e1e1e);

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

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.2, -0.6),
    radius: 1.25,
    colors: [Color(0x55FF4500), Color(0x22FFB800), Color(0x00000000)],
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
  final TextEditingController _messageCtrl = TextEditingController();
  final ScrollController _scrollCtrl = ScrollController();
  final ImagePicker _picker = ImagePicker();

  bool _loading = true;
  bool _sending = false;
  bool _isLocked = false;

  int _remainingSeconds = 0;

  List _messages = [];
  Timer? _timer;
  String? _myUserId;

  @override
  void initState() {
    super.initState();
    _loadMyProfile();
    _loadMessages();
    ApiService.chatDelivered();

    _timer = Timer.periodic(const Duration(seconds: 3), (_) {
      if (!_isLocked) {
        _loadMessages(showLoading: false);
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
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
    if (showLoading) setState(() => _loading = true);

    final res = widget.chatType == "transfer"
        ? await ApiService.transferChatMessages(widget.chatId)
        : await ApiService.requestChatMessages(widget.chatId);

    if (!mounted) return;

    if (res["is_locked"] == true || res["status_code"] == 423) {
      setState(() {
        _isLocked = true;
        _loading = false;
      });

      if (showLoading) {
        _snack(res["message"]?.toString() ?? "This chat is locked.");
      }
      return;
    }

    if (res["success"] == true) {
      final newMessages = List.from(res["messages"] ?? []);
      final grew = newMessages.length > _messages.length;

      setState(() {
        _messages = newMessages;
        _remainingSeconds =
            int.tryParse(res["remaining_seconds"]?.toString() ?? "0") ?? 0;
        _isLocked = false;
        _loading = false;
      });

      if (grew || showLoading) {
        Future.delayed(const Duration(milliseconds: 120), _scrollToBottom);
      }
    } else {
      setState(() => _loading = false);

      if (showLoading) {
        _snack(res["message"]?.toString() ?? "Failed to load chat");
      }
    }
  }

  Future<void> _sendMessage() async {
    if (_isLocked) {
      _snack("This chat is locked. 15 minutes completed.");
      return;
    }

    final text = _messageCtrl.text.trim();
    if (text.isEmpty || _sending) return;

    setState(() => _sending = true);
    _messageCtrl.clear();

    final res = await ApiService.sendChatMessageMultipart(
      chatType: widget.chatType,
      chatId: widget.chatId,
      message: text,
    );

    if (!mounted) return;

    setState(() => _sending = false);

    if (res["is_locked"] == true || res["status_code"] == 423) {
      setState(() => _isLocked = true);
      _snack(res["message"]?.toString() ?? "This chat is locked.");
      return;
    }

    if (res["success"] == true) {
      await _loadMessages(showLoading: false);
      Future.delayed(const Duration(milliseconds: 80), _scrollToBottom);
    } else {
      _snack(res["message"]?.toString() ?? "Failed to send message");
    }
  }

  Future<void> _sendImage(ImageSource source) async {
    if (_isLocked) {
      _snack("This chat is locked. 15 minutes completed.");
      return;
    }

    if (_sending) return;

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
    setState(() => _sending = false);

    if (res["is_locked"] == true || res["status_code"] == 423) {
      setState(() => _isLocked = true);
      _snack(res["message"]?.toString() ?? "This chat is locked.");
      return;
    }

    if (res["success"] == true) {
      await _loadMessages(showLoading: false);
      Future.delayed(const Duration(milliseconds: 80), _scrollToBottom);
    } else {
      _snack(res["message"]?.toString() ?? "Failed to upload image");
    }
  }

  Future<void> _sendFile() async {
    if (_isLocked) {
      _snack("This chat is locked. 15 minutes completed.");
      return;
    }

    if (_sending) return;

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

    final file = result.files.first;

    setState(() => _sending = true);

    final res = await ApiService.sendChatMessageMultipart(
      chatType: widget.chatType,
      chatId: widget.chatId,
      message: _messageCtrl.text.trim(),
      file: file,
    );

    if (!mounted) return;

    _messageCtrl.clear();
    setState(() => _sending = false);

    if (res["is_locked"] == true || res["status_code"] == 423) {
      setState(() => _isLocked = true);
      _snack(res["message"]?.toString() ?? "This chat is locked.");
      return;
    }

    if (res["success"] == true) {
      await _loadMessages(showLoading: false);
      Future.delayed(const Duration(milliseconds: 80), _scrollToBottom);
    } else {
      _snack(res["message"]?.toString() ?? "Failed to upload file");
    }
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: _C.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        content: Text(
          message,
          style: const TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
    );
  }

  void _openAttachment(String url) async {
    final fixedUrl = ApiService.fixUrl(url);
    final uri = Uri.parse(fixedUrl);
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  void _showAttachmentMenu() {
    if (_isLocked) {
      _snack("This chat is locked. 15 minutes completed.");
      return;
    }

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (_) {
        return Container(
          decoration: const BoxDecoration(
            color: _C.surfaceAlt,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(18, 12, 18, 18),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 44,
                    height: 4,
                    decoration: BoxDecoration(
                      color: _C.border,
                      borderRadius: BorderRadius.circular(20),
                    ),
                  ),
                  const SizedBox(height: 18),
                  const Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      "Send Attachment",
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                  _attachmentTile(
                    icon: Icons.photo_library_rounded,
                    title: "Photo Gallery",
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
        color: _C.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _C.border),
      ),
      child: ListTile(
        onTap: onTap,
        leading: Container(
          width: 38,
          height: 38,
          decoration: BoxDecoration(
            gradient: _C.gradientAccent,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(icon, color: Colors.black, size: 20),
        ),
        title: Text(
          title,
          style: const TextStyle(
            color: Colors.white,
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
    final raw = widget.chatId;
    final padded = raw.padLeft(9, '0');
    return widget.chatType == "transfer" ? "TRA$padded" : "TNS$padded";
  }

  Widget _topBar() {
    final name = widget.otherUser["name"]?.toString() ?? "User";
    final role = widget.otherUser["role"]?.toString() ?? "";
    final photoUrl = ApiService.fixUrl(
      widget.otherUser["photo_url"] ?? widget.otherUser["photo"],
    );

    final isOnline = widget.otherUser["is_online"] == true ||
        widget.otherUser["is_online"] == 1 ||
        widget.otherUser["is_online"]?.toString() == "1";

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
      decoration: const BoxDecoration(
        gradient: _C.gradientCard,
        border: Border(
          bottom: BorderSide(color: Color(0xff3a1500), width: 1),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Row(
          children: [
            GestureDetector(
              onTap: () => Navigator.maybePop(context),
              child: Container(
                width: 42,
                height: 42,
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
            const SizedBox(width: 12),
            Stack(
              children: [
                Container(
                  padding: const EdgeInsets.all(2),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: _isLocked ? null : _C.gradientAccent,
                    color: _isLocked ? _C.red : null,
                  ),
                  child: CircleAvatar(
                    radius: 24,
                    backgroundColor: _C.surfaceAlt,
                    backgroundImage:
                        photoUrl.isNotEmpty ? NetworkImage(photoUrl) : null,
                    child: photoUrl.isEmpty
                        ? Text(
                            name.isNotEmpty ? name[0].toUpperCase() : "U",
                            style: const TextStyle(
                              color: _C.amber,
                              fontWeight: FontWeight.w900,
                              fontSize: 18,
                            ),
                          )
                        : null,
                  ),
                ),
                Positioned(
                  right: 0,
                  bottom: 0,
                  child: Container(
                    width: 13,
                    height: 13,
                    decoration: BoxDecoration(
                      color: _isLocked
                          ? _C.red
                          : isOnline
                              ? _C.success
                              : _C.textSecondary,
                      shape: BoxShape.circle,
                      border: Border.all(color: const Color(0xff1a0a00), width: 2),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    name,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      Text(
                        _isLocked
                            ? "Locked"
                            : isOnline
                                ? "Online"
                                : "Offline",
                        style: TextStyle(
                          color: _isLocked
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
                          style: TextStyle(color: Colors.white38, fontSize: 11),
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
                ],
              ),
            ),
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                gradient: _C.gradientAccent,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(
                widget.chatType == "transfer"
                    ? Icons.swap_horiz_rounded
                    : Icons.receipt_long_rounded,
                color: Colors.black,
                size: 21,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTransactionBanner() {
    final bool isTransfer = widget.chatType == "transfer";

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.fromLTRB(16, 14, 16, 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: _isLocked ? null : _C.gradientCard,
        color: _isLocked ? _C.surface : null,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: _isLocked ? _C.red : const Color(0xff3a1500)),
        boxShadow: [
          BoxShadow(
            color: _C.orange.withOpacity(0.10),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(24),
                gradient: _C.gradientGlow,
              ),
            ),
          ),
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: _isLocked ? _C.red.withOpacity(0.15) : null,
                  gradient: _isLocked ? null : _C.gradientAccent,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Icon(
                  _isLocked
                      ? Icons.lock_rounded
                      : isTransfer
                          ? Icons.swap_horiz_rounded
                          : Icons.receipt_long_rounded,
                  color: _isLocked ? _C.red : Colors.black,
                  size: 24,
                ),
              ),
              const SizedBox(width: 13),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isTransfer ? "Wallet Transfer" : "Buy / Sell Request",
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _transactionNo(),
                      style: TextStyle(
                        color: _isLocked ? _C.red : _C.amber,
                        fontWeight: FontWeight.w900,
                        fontSize: 13,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _isLocked
                          ? "Chat locked after 15 minutes"
                          : _remainingLabel().isEmpty
                              ? "Chat linked to this transaction"
                              : "Time left: ${_remainingLabel()}",
                      style: const TextStyle(
                        color: _C.textSecondary,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.28),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.white.withOpacity(0.08)),
                ),
                child: Text(
                  _isLocked ? "LOCKED" : isTransfer ? "TRANSFER" : "REQUEST",
                  style: TextStyle(
                    color: _isLocked ? _C.red : _C.amber,
                    fontSize: 9,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.8,
                  ),
                ),
              ),
            ],
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
          margin: EdgeInsets.only(
            top: msg["message"] != null && msg["message"].toString().isNotEmpty
                ? 8
                : 0,
          ),
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            color: Colors.black26,
            border: Border.all(color: isMe ? Colors.black26 : _C.border),
          ),
          child: Image.network(
            fixedUrl,
            width: 220,
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) {
              return Container(
                width: 220,
                padding: const EdgeInsets.all(14),
                child: Text(
                  "Image not available",
                  style: TextStyle(
                    color: isMe ? Colors.black87 : Colors.white60,
                  ),
                ),
              );
            },
          ),
        ),
      );
    }

    return GestureDetector(
      onTap: () => _openAttachment(fixedUrl),
      child: Container(
        margin: EdgeInsets.only(
          top: msg["message"] != null && msg["message"].toString().isNotEmpty
              ? 8
              : 0,
        ),
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
              color: isMe ? Colors.black87 : _C.amber,
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

  Widget _buildBubble(Map msg) {
    final senderId = msg["sender_id"]?.toString();
    final bool isMe = _myUserId != null && senderId == _myUserId;

    final text = msg["message"]?.toString() ?? "";
    final status = msg["status"]?.toString() ?? "sent";
    final createdAt = msg["created_at"]?.toString() ?? "";

    String timeLabel = "";
    try {
      final dt = DateTime.parse(createdAt).toLocal();
      timeLabel =
          "${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}";
    } catch (_) {}

    final isAdmin = msg["sender"]?["role"] == "admin";

    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.fromLTRB(14, 11, 14, 8),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.76,
        ),
        decoration: BoxDecoration(
          gradient: isMe ? _C.gradientAccent : null,
          color: isMe ? null : _C.surface,
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(20),
            topRight: const Radius.circular(20),
            bottomLeft: Radius.circular(isMe ? 20 : 5),
            bottomRight: Radius.circular(isMe ? 5 : 20),
          ),
          border: Border.all(
            color: isMe
                ? Colors.transparent
                : isAdmin
                    ? _C.amber
                    : _C.border,
            width: isAdmin ? 1.2 : 0.9,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.22),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment:
              isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            if (!isMe && isAdmin) ...[
              const Text(
                "Admin",
                style: TextStyle(
                  color: _C.amber,
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 3),
            ],
            if (text.isNotEmpty)
              Text(
                text,
                style: TextStyle(
                  color: isMe ? Colors.black : Colors.white,
                  fontSize: 15,
                  height: 1.35,
                  fontWeight: isMe ? FontWeight.w800 : FontWeight.w500,
                ),
              ),
            _buildAttachment(msg, isMe),
            const SizedBox(height: 6),
            Row(
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
                  const SizedBox(width: 4),
                  Icon(
                    status == "seen" || status == "delivered"
                        ? Icons.done_all_rounded
                        : Icons.done_rounded,
                    size: 14,
                    color: status == "seen"
                        ? _C.blue
                        : Colors.black.withOpacity(0.55),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDateDivider(String label) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        children: [
          const Expanded(child: Divider(color: _C.border)),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
            decoration: BoxDecoration(
              color: _C.surface,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: _C.border),
            ),
            child: Text(
              label,
              style: const TextStyle(
                color: _C.textSecondary,
                fontSize: 11,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const Expanded(child: Divider(color: _C.border)),
        ],
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

  Widget _emptyState(String name) {
    return Center(
      child: Container(
        margin: const EdgeInsets.all(22),
        padding: const EdgeInsets.all(26),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(26),
          border: Border.all(color: _C.border),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 76,
              height: 76,
              decoration: BoxDecoration(
                gradient: _C.gradientAccent,
                shape: BoxShape.circle,
              ),
              child: Icon(
                widget.chatType == "transfer"
                    ? Icons.swap_horiz_rounded
                    : Icons.receipt_long_rounded,
                color: Colors.black,
                size: 36,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              "No messages yet",
              style: TextStyle(
                color: Colors.white,
                fontSize: 17,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 7),
            Text(
              "Start the conversation with $name",
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: _C.textSecondary,
                fontSize: 12,
              ),
            ),
          ],
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
      child: SafeArea(
        top: false,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
          decoration: BoxDecoration(
            color: _C.red.withOpacity(0.10),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: _C.red.withOpacity(0.35)),
          ),
          child: const Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.lock_rounded, color: _C.red, size: 18),
              SizedBox(width: 8),
              Text(
                "Chat locked after 15 minutes",
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
                  color: _C.amber,
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
                  hintStyle: const TextStyle(color: _C.textSecondary),
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
                    borderSide: const BorderSide(color: _C.orange, width: 1.4),
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
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
                  gradient: _C.gradientAccent,
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
    final name = widget.otherUser["name"]?.toString() ?? "User";

    return Scaffold(
      backgroundColor: _C.bg,
      body: Column(
        children: [
          _topBar(),
          _buildTransactionBanner(),
          Expanded(
            child: _loading
                ? const Center(
                    child: CircularProgressIndicator(
                      color: _C.orange,
                      strokeWidth: 2.5,
                    ),
                  )
                : _messages.isEmpty
                    ? _emptyState(name)
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
import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'chat_screen.dart';
import 'transaction_detail_screen.dart';

// ─────────────────────────────────────────────────────────────
//  DESIGN TOKENS - SAME STYLE AS OTHER SCREENS
// ─────────────────────────────────────────────────────────────
class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff0D0D0D);
  static const surfaceAlt = Color(0xff171717);

  static const border = Color(0xff2E2E2E);
  static const borderFaint = Color(0xff202020);

  // Theme
  static const orange = Color(0xffFACC15);
  static const amber = Color(0xffFFD700);
  static const gold = Color(0xffFFF176);

  static const green = Color(0xff22C55E);
  static const red = Color(0xffEF4444);
  static const blue = Color(0xffFACC15);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xffA3A3A3);
  static const textMuted = Color(0xff666666);

  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [
      Color(0xffFACC15),
      Color(0xffFFD700),
      Color(0xffFFF176),
    ],
  );
}
class HistoryScreen extends StatefulWidget {
  final Map user;

  const HistoryScreen({super.key, required this.user});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  late Map user;

  bool _loading = true;
  List requests = [];
  List transfers = [];

  String _search = "";
  bool _newestFirst = true;

  @override
  void initState() {
    super.initState();
    user = widget.user;
    _loadData();
  }

  Future<void> _loadData() async {
    if (mounted) setState(() => _loading = true);

    try {
      final requestData = await ApiService.myRequests();
      final transferData = await ApiService.walletTransfers();

      if (requestData["success"] == true && mounted) {
        requests = List.from(requestData["requests"] ?? []);
      }

      if (transferData["success"] == true && mounted) {
        transfers = List.from(transferData["transfers"] ?? []);
      }
    } catch (_) {}

    if (mounted) setState(() => _loading = false);
  }

  // Goes back to the Dashboard home page instead of just popping
  // one route (this screen may be shown as a dashboard tab).
  void _goBackToDashboard() {
    Navigator.popUntil(context, (route) => route.isFirst);
  }

  String fmtDate(dynamic v) {
    final raw = v?.toString();
    if (raw == null || raw.isEmpty) return "—";
    try {
      final dt = DateTime.parse(raw.replaceFirst(" ", "T"))
          .toUtc()
          .add(const Duration(hours: 5, minutes: 30));
      String p(int n) => n.toString().padLeft(2, "0");
      return "${p(dt.day)}/${p(dt.month)}/${dt.year}  ${p(dt.hour)}:${p(dt.minute)}";
    } catch (_) {
      return raw;
    }
  }

 Color statusColor(String s) {
  s = s.toLowerCase();
  if (s == "approved" || s == "completed") return _C.green;
  if (s == "rejected") return _C.red;
  if (s == "closed") return _C.textSecondary;
  return _C.amber;
}
  // ═══════════════════════════════════════════
  //  TOP BAR
  // ═══════════════════════════════════════════
  Widget _topBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Row(
        children: [
          GestureDetector(
            onTap: _goBackToDashboard,
            child: Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: _C.surface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: _C.border),
              ),
              child: const Icon(
                Icons.arrow_back_ios_new_rounded,
                color: Colors.white,
                size: 18,
              ),
            ),
          ),
          const SizedBox(width: 14),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "Transaction History",
                  style: TextStyle(
                    color: _C.textPrimary,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.3,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  "All your requests & transfers",
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
Widget _filters() {
  return Padding(
    padding: const EdgeInsets.fromLTRB(20, 18, 20, 12),
    child: Row(
      children: [
        Expanded(
          child: Container(
            decoration: BoxDecoration(
              color: _C.surface,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: _C.border),
            ),
            child: TextField(
              style: const TextStyle(color: Colors.white, fontSize: 14),
              onChanged: (v) => setState(() => _search = v),
              decoration: const InputDecoration(
                hintText: "Search transactions...",
                hintStyle: TextStyle(color: _C.textSecondary, fontSize: 13),
                prefixIcon: Icon(
                  Icons.search_rounded,
                  color: _C.textSecondary,
                  size: 20,
                ),
                border: InputBorder.none,
                contentPadding:
                    EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              ),
            ),
          ),
        ),
        const SizedBox(width: 10),
        _sortIcon(
          icon: _newestFirst
              ? Icons.arrow_downward_rounded
              : Icons.arrow_upward_rounded,
          onTap: () => setState(() => _newestFirst = !_newestFirst),
        ),
      ],
    ),
  );
}

Widget _sortIcon({
  required IconData icon,
  required VoidCallback onTap,
}) {
  return GestureDetector(
    onTap: onTap,
    child: Container(
      width: 50,
      height: 50,
      decoration: BoxDecoration(
        gradient: _C.gradientAccent,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
          color: _C.amber.withOpacity(0.28),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Icon(
        icon,
        color: Colors.black,
        size: 22,
      ),
    ),
  );
}

  // ═══════════════════════════════════════════
  //  DATA
  // ═══════════════════════════════════════════
  List<Map<String, dynamic>> _activityItems() {
    final allItems = <Map<String, dynamic>>[];

    for (final r in requests) {
      allItems.add({
        "source_type": "request",
        "data": Map<String, dynamic>.from(r),
      });
    }

    for (final t in transfers) {
      allItems.add({
        "source_type": "transfer",
        "data": Map<String, dynamic>.from(t),
      });
    }

    allItems.sort((a, b) {
      final ad = a["data"]["created_at"]?.toString() ?? "";
      final bd = b["data"]["created_at"]?.toString() ?? "";
      return _newestFirst ? bd.compareTo(ad) : ad.compareTo(bd);
    });

    final q = _search.trim().toLowerCase();
    if (q.isEmpty) return allItems;

    return allItems.where((wrap) {
      final sourceType = wrap["source_type"].toString();
      final r = Map<String, dynamic>.from(wrap["data"]);
      final type = r["type"]?.toString().toLowerCase() ?? "";

      final title = sourceType == "transfer"
          ? "wallet transfer"
          : (type == "withdrawal" ? "sell usd" : "buy usd");
      final status = sourceType == "transfer"
          ? "completed"
          : (r["status"]?.toString().toLowerCase() ?? "pending");
      final id = r["id"]?.toString().toLowerCase() ?? "";
      final amount = r["amount"]?.toString().toLowerCase() ?? "";

      return title.contains(q) ||
          status.contains(q) ||
          id.contains(q) ||
          amount.contains(q);
    }).toList();
  }

  // ═══════════════════════════════════════════
  //  LIST
  // ═══════════════════════════════════════════
  Widget _list() {
    final items = _activityItems();

    if (items.isEmpty) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
        child: Container(
          height: 120,
          decoration: BoxDecoration(
            color: _C.surface,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: _C.border),
          ),
          child: Center(
            child: Text(
              _search.trim().isEmpty
                  ? "No transactions yet"
                  : "No matching transactions",
              style: const TextStyle(color: _C.textSecondary, fontSize: 13),
            ),
          ),
        ),
      );
    }

    return Column(
      children: items.map((wrap) => _tile(wrap)).toList(),
    );
  }
Widget _tile(Map<String, dynamic> wrap) {
  final sourceType = wrap["source_type"].toString();
  final r = Map<String, dynamic>.from(wrap["data"]);

  final status = sourceType == "transfer"
      ? "completed"
      : r["status"]?.toString().toLowerCase() ?? "pending";

  final isClosed = sourceType == "request" && status == "closed";
  final type = r["type"]?.toString().toLowerCase() ?? "";
  final color = statusColor(status);
  final isTransfer = sourceType == "transfer";

  final title = isTransfer
      ? "WALLET TRANSFER"
      : isClosed
          ? "TRANSACTION CLOSED"
          : type == "withdrawal"
              ? "SELL USD"
              : "BUY USD";

  final idText = r["transaction_no"]?.toString() ?? r["id"]?.toString() ?? "-";

  return GestureDetector(
    onTap: () => _showTransactionOptions(r, sourceType),
    child: Container(
      margin: const EdgeInsets.fromLTRB(20, 0, 20, 10),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _C.border),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(
              isClosed
                  ? Icons.lock_rounded
                  : isTransfer
                      ? Icons.swap_horiz_rounded
                      : status == "approved"
                          ? Icons.check_rounded
                          : status == "rejected"
                              ? Icons.close_rounded
                              : Icons.access_time_rounded,
              color: color,
              size: 20,
            ),
          ),
          const SizedBox(width: 12),

          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        title,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: _C.textPrimary,
                          fontWeight: FontWeight.w800,
                          fontSize: 14,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Flexible(
                      child: Text(
                        "#$idText",
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: _C.textMuted,
                          fontWeight: FontWeight.w600,
                          fontSize: 10,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 3),
                Text(
                  fmtDate(r["created_at"]),
                  style: const TextStyle(
                    color: _C.textSecondary,
                    fontSize: 11,
                  ),
                ),
              ],
            ),
          ),

          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                isClosed ? "Closed" : "\$${r["amount"] ?? "0.00"}",
                style: TextStyle(
                  color: isClosed ? _C.textSecondary : _C.textPrimary,
                  fontWeight: FontWeight.w900,
                  fontSize: 15,
                ),
              ),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  isClosed ? "CLOSED" : status.toUpperCase(),
                  style: TextStyle(
                    color: color,
                    fontSize: 9,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    ),
  );
}

  // ═══════════════════════════════════════════
  //  CHAT / DETAIL OPTIONS
  // ═══════════════════════════════════════════
  Map<String, dynamic> _otherUserForChat(
    Map<String, dynamic> item,
    String sourceType,
  ) {
    final myId = user["id"]?.toString() ?? "";

    if (sourceType == "transfer") {
      final senderId = item["sender_id"]?.toString() ?? "";
      final isSender = senderId == myId;

      return {
        "id": isSender ? item["receiver_id"] : item["sender_id"],
        "name": isSender
            ? (item["receiver_name"] ?? item["receiver"]?["name"] ?? "Receiver")
            : (item["sender_name"] ?? item["sender"]?["name"] ?? "Sender"),
        "wallet_id": isSender
            ? (item["receiver_wallet_id"] ?? item["receiver"]?["wallet_id"])
            : (item["sender_wallet_id"] ?? item["sender"]?["wallet_id"]),
        "photo_url": isSender
            ? (item["receiver_photo_url"] ?? item["receiver"]?["photo_url"])
            : (item["sender_photo_url"] ?? item["sender"]?["photo_url"]),
        "role": "user",
      };
    }

    return {
      "id": item["merchant_id"] ?? item["merchant"]?["id"],
      "name": item["merchant_name"] ?? item["merchant"]?["name"] ?? "Merchant",
      "wallet_id": item["merchant_wallet_id"] ?? item["merchant"]?["wallet_id"],
      "photo_url": item["merchant_photo_url"] ?? item["merchant"]?["photo_url"],
      "role": "merchant",
    };
  }

  Future<void> _showTransactionOptions(
    Map<String, dynamic> item,
    String sourceType,
  ) async {
    await showModalBottomSheet(
      context: context,
      backgroundColor: _C.surfaceAlt,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 18, 20, 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  "Transaction Options",
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 18),
                _optionTile(
                  icon: Icons.receipt_long_rounded,
                  title: "Summary / Receipt",
                  sub: "View transaction details",
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => TransactionDetailScreen(
                          item: item,
                          sourceType: sourceType,
                          user: user,
                        ),
                      ),
                    );
                  },
                ),
                const SizedBox(height: 10),
                _optionTile(
                  icon: Icons.chat_rounded,
                  title: "Chat",
                  sub: "Open transaction chat",
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ChatScreen(
                          chatType: sourceType,
                          chatId: item["id"].toString(),
                          otherUser: _otherUserForChat(item, sourceType),
                        ),
                      ),
                    );
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _optionTile({
    required IconData icon,
    required String title,
    required String sub,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: _C.border),
        ),
        child: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: _C.amber.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: _C.orange, size: 21),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title,
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 14,
                          fontWeight: FontWeight.w800)),
                  Text(sub,
                      style: const TextStyle(
                          color: _C.textSecondary, fontSize: 12)),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios_rounded,
                color: _C.textSecondary, size: 14),
          ],
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  BUILD
  // ═══════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(
        backgroundColor: _C.bg,
        body: Center(
          child: CircularProgressIndicator(color: _C.orange),
        ),
      );
    }

    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: RefreshIndicator(
          color: _C.amber,
          onRefresh: _loadData,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Column(
              children: [
                _topBar(),
                _filters(),
                _list(),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
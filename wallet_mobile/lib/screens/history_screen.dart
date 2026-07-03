import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'chat_screen.dart';
import 'transaction_detail_screen.dart';

class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff0D0D0D);
  static const surfaceAlt = Color(0xff171717);
  static const border = Color(0xff2E2E2E);

  static const orange = Color(0xffB8860B);
  static const amber = Color(0xff9A6B00);
  static const gold = Color(0xffD4A017);

  static const green = Color(0xff22C55E);
  static const red = Color(0xffEF4444);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xffA3A3A3);
  static const textMuted = Color(0xff666666);

  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [
      Color(0xff8A6300),
      Color(0xffB8860B),
      Color(0xffD4A017),
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
  String _filterType = "all";
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

      if (requestData["success"] == true) {
        requests = List.from(requestData["requests"] ?? []);
      }

      if (transferData["success"] == true) {
        transfers = List.from(transferData["transfers"] ?? []);
      }
    } catch (_) {}

    if (mounted) setState(() => _loading = false);
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
    return _C.gold;
  }

  Color amountColor({
    required String sourceType,
    required String type,
    required String status,
  }) {
    if (status == "closed") return _C.textSecondary;
    if (sourceType == "transfer") return _C.green;
    if (type == "withdrawal") return _C.red;
    return _C.green;
  }

  String amountText({
    required String sourceType,
    required String type,
    required String status,
    required dynamic amount,
  }) {
    if (status == "closed") return "Closed";

    final value = amount?.toString() ?? "0.00";

    if (sourceType == "transfer") return "+\$$value";
    if (type == "withdrawal") return "-\$$value";
    return "+\$$value";
  }

  Widget _filters() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 18, 20, 12),
      child: Column(
        children: [
          Row(
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
                      hintStyle:
                          TextStyle(color: _C.textSecondary, fontSize: 13),
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
          const SizedBox(height: 12),
          Row(
            children: [
              _filterChip("All", "all"),
              const SizedBox(width: 8),
              _filterChip("Request", "request"),
              const SizedBox(width: 8),
              _filterChip("Transfer", "transfer"),
            ],
          ),
        ],
      ),
    );
  }

  Widget _filterChip(String label, String value) {
    final active = _filterType == value;

    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _filterType = value),
        child: Container(
          height: 42,
          decoration: BoxDecoration(
            gradient: active ? _C.gradientAccent : null,
            color: active ? null : _C.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: active ? _C.gold : _C.border),
          ),
          child: Center(
            child: Text(
              label,
              style: TextStyle(
                color: active ? Colors.white : _C.textSecondary,
                fontSize: 13,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
        ),
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
        width: 42,
        height: 42,
        decoration: BoxDecoration(
          gradient: _C.gradientAccent,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(icon, color: Colors.white, size: 17),
      ),
    );
  }

  List<Map<String, dynamic>> _activityItems() {
    final allItems = <Map<String, dynamic>>[];

    if (_filterType == "all" || _filterType == "request") {
      for (final r in requests) {
        allItems.add({
          "source_type": "request",
          "data": Map<String, dynamic>.from(r),
        });
      }
    }

    if (_filterType == "all" || _filterType == "transfer") {
      for (final t in transfers) {
        allItems.add({
          "source_type": "transfer",
          "data": Map<String, dynamic>.from(t),
        });
      }
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
          : type == "withdrawal"
              ? "sell usd request"
              : "buy usd request";

      final status = sourceType == "transfer"
          ? "completed"
          : r["status"]?.toString().toLowerCase() ?? "pending";

      final id = r["id"]?.toString().toLowerCase() ?? "";
      final trx = r["transaction_no"]?.toString().toLowerCase() ?? "";
      final amount = r["amount"]?.toString().toLowerCase() ?? "";

      return title.contains(q) ||
          status.contains(q) ||
          id.contains(q) ||
          trx.contains(q) ||
          amount.contains(q);
    }).toList();
  }

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

    final type = r["type"]?.toString().toLowerCase() ?? "";
    final isTransfer = sourceType == "transfer";
    final isClosed = sourceType == "request" && status == "closed";

    final color = statusColor(status);

    final title = isTransfer
        ? "WALLET TRANSFER"
        : isClosed
            ? "TRANSACTION CLOSED"
            : type == "withdrawal"
                ? "SELL USD"
                : "BUY USD";

    final idText = r["transaction_no"]?.toString() ?? r["id"]?.toString() ?? "-";

    final finalAmountColor = amountColor(
      sourceType: sourceType,
      type: type,
      status: status,
    );

    final finalAmountText = amountText(
      sourceType: sourceType,
      type: type,
      status: status,
      amount: r["amount"],
    );

    return GestureDetector(
      onTap: () {
        if (isTransfer) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => TransactionDetailScreen(
                item: r,
                sourceType: "transfer",
                user: user,
              ),
            ),
          );
        } else {
          _showRequestOptions(r);
        }
      },
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
                  finalAmountText,
                  style: TextStyle(
                    color: finalAmountColor,
                    fontWeight: FontWeight.w900,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 4),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
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

  Map<String, dynamic> _merchantForChat(Map<String, dynamic> item) {
    return {
      "id": item["merchant_id"] ?? item["merchant"]?["id"],
      "name": item["merchant_name"] ?? item["merchant"]?["name"] ?? "Merchant",
      "wallet_id": item["merchant_wallet_id"] ?? item["merchant"]?["wallet_id"],
      "photo_url": item["merchant_photo_url"] ?? item["merchant"]?["photo_url"],
      "photo": item["merchant_photo"] ?? item["merchant"]?["photo"],
      "is_online": item["merchant"]?["is_online"],
      "role": "merchant",
    };
  }

  Future<void> _showRequestOptions(Map<String, dynamic> item) async {
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
                  "Request Options",
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
                  sub: "View request details",
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => TransactionDetailScreen(
                          item: item,
                          sourceType: "request",
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
                  sub: "Open request chat",
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ChatScreen(
                          chatType: "request",
                          chatId: item["id"].toString(),
                          otherUser: _merchantForChat(item),
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
                color: _C.gold.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: _C.orange, size: 21),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  Text(
                    sub,
                    style: const TextStyle(
                      color: _C.textSecondary,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
            const Icon(
              Icons.arrow_forward_ios_rounded,
              color: _C.textSecondary,
              size: 14,
            ),
          ],
        ),
      ),
    );
  }

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
          color: _C.gold,
          onRefresh: _loadData,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Column(
              children: [
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
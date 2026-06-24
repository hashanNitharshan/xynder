import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class TransactionDetailScreen extends StatelessWidget {
  final Map item;
  final String sourceType; // request / transfer
  final Map user;

  const TransactionDetailScreen({
    super.key,
    required this.item,
    required this.sourceType,
    required this.user,
  });

  static const bg = Color(0xff000000);
  static const green = Color(0xff00C076);
  static const red = Color(0xffef4444);
  static const amber = Color(0xffFFB800);
  static const textMuted = Color(0xff6f6f76);

  String v(dynamic x) {
    final s = x?.toString() ?? "";
    return s.isEmpty || s == "null" ? "—" : s;
  }

  double d(dynamic x) => double.tryParse(x?.toString() ?? "0") ?? 0;

  String fmtDate(dynamic raw) {
    final s = raw?.toString();
    if (s == null || s.isEmpty) return "—";
    try {
      final dt = DateTime.parse(s.replaceFirst(" ", "T"));
      String p(int n) => n.toString().padLeft(2, "0");
      return "${dt.year}-${p(dt.month)}-${p(dt.day)} ${p(dt.hour)}:${p(dt.minute)}:${p(dt.second)}";
    } catch (_) {
      return s;
    }
  }

  String get title {
    if (sourceType == "transfer") return "Transfer Details";
    final type = item["type"]?.toString().toLowerCase();
    return type == "withdrawal" ? "Withdrawal Details" : "Deposit Details";
  }

  String get quantity {
    final amount = d(item["amount"]);
    if (amount == 0) return "0 USDT";
    return "${amount.toStringAsFixed(amount.truncateToDouble() == amount ? 0 : 2)} USDT";
  }

  String get status {
    if (sourceType == "transfer") return "Transfer Completed";
    final s = item["status"]?.toString().toLowerCase() ?? "pending";
    if (s == "approved") {
      final type = item["type"]?.toString().toLowerCase();
      return type == "withdrawal" ? "Withdrawal Completed" : "Deposit Completed";
    }
    if (s == "rejected") return "Request Rejected";
    return "Request Pending";
  }

  Color get statusColor {
    final s = item["status"]?.toString().toLowerCase() ?? "";
    if (sourceType == "transfer") return green;
    if (s == "approved") return green;
    if (s == "rejected") return red;
    return amber;
  }

String get hash {
  final tx = item["transaction_no"]?.toString();
  if (tx != null && tx.isNotEmpty && tx != "null") return tx;

  final id = v(item["id"]);
  return sourceType == "transfer"
      ? "TRA${id.padLeft(9, "0")}"
      : "TNS${id.padLeft(9, "0")}";
}

  String get account {
    if (sourceType == "transfer") {
      final myId = user["id"]?.toString();
      final senderId = item["sender_id"]?.toString();
      return senderId == myId ? "Sent Transfer" : "Received Transfer";
    }
    final type = item["type"]?.toString().toLowerCase();
    return type == "withdrawal" ? "Funding Account" : "Wallet Account";
  }

  String get chainType {
    if (sourceType == "transfer") return "Internal Transfer";
    return "Merchant Request";
  }

  String get address {
    if (sourceType == "transfer") {
      final myId = user["id"]?.toString();
      final senderId = item["sender_id"]?.toString();
      return senderId == myId
          ? v(item["receiver_wallet_id"] ?? item["receiver_id"])
          : v(item["sender_wallet_id"] ?? item["sender_id"]);
    }
    return v(item["merchant_wallet_id"] ?? item["merchant_id"] ?? item["user_id"]);
  }

  String get fees {
    final xynder = d(item["xynder_fee"]);
    final network = d(item["network_fee"]);
    final total = xynder + network;
    if (total == 0) return "0";
    return total.toStringAsFixed(2);
  }

  void copy(BuildContext context, String text) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text("Copied"),
        backgroundColor: green,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  Widget row(BuildContext context, String left, String right, {bool copyable = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 22),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Text(
              left,
              style: const TextStyle(
                color: textMuted,
                fontSize: 18,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: GestureDetector(
              onTap: copyable ? () => copy(context, right) : null,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Flexible(
                    child: Text(
                      right,
                      textAlign: TextAlign.right,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        height: 1.25,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  if (copyable) ...[
                    const SizedBox(width: 6),
                    const Icon(Icons.copy_rounded, color: Colors.white70, size: 16),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final id = v(item["id"]);

    return Scaffold(
      backgroundColor: bg,
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(18, 16, 18, 0),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 32),
                  ),
                  Expanded(
                    child: Text(
                      title,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 23,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  const SizedBox(width: 32),
                ],
              ),
            ),

            const SizedBox(height: 70),

            const Text(
              "Quantity",
              style: TextStyle(color: textMuted, fontSize: 21),
            ),
            const SizedBox(height: 10),
            Text(
              quantity,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 31,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.check_circle_rounded, color: statusColor, size: 22),
                const SizedBox(width: 8),
                Text(
                  status,
                  style: TextStyle(
                    color: statusColor,
                    fontSize: 21,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),

            const SizedBox(height: 92),

            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 28),
              child: Column(
                children: [
                  row(context, sourceType == "transfer" ? "Transfer Account" : "Withdrawal Account", account),
                  row(context, "Fees", fees),
                  row(context, "Chain Type", chainType),
                  row(context, "Time", fmtDate(item["created_at"])),
                  row(context, sourceType == "transfer" ? "Wallet Address" : "Withdrawal Address", address, copyable: true),
                 row(context, "Transaction No", hash, copyable: true),
row(context, "Reference ID", hash, copyable: true),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
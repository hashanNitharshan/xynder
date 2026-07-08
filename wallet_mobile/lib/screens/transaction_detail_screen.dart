import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'chat_screen.dart';

class TransactionDetailScreen extends StatelessWidget {
  final Map item;
  final String sourceType;
  final Map user;

  const TransactionDetailScreen({
    super.key,
    required this.item,
    required this.sourceType,
    required this.user,
  });

  static const bg = Color(0xff000000);
  static const cardBg = Color(0xff0D0D0D);
  static const cardBorder = Color(0xff2E2E2E);
  static const green = Color(0xff00C076);
  static const red = Color(0xffef4444);
  static const amber = Color(0xffFFB800);
  static const grey = Color(0xff8E8E93);
  static const textMuted = Color(0xff6f6f76);

  String v(dynamic x) {
    final s = x?.toString() ?? "";
    return s.isEmpty || s == "null" ? "—" : s;
  }

  double d(dynamic x) => double.tryParse(x?.toString() ?? "0") ?? 0;

  bool get isClosed {
    return sourceType == "request" &&
        item["status"]?.toString().toLowerCase() == "closed";
  }

  bool get canChat => sourceType != "transfer";

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
    if (isClosed) return "Transaction Closed";
    final type = item["type"]?.toString().toLowerCase();
    return type == "withdrawal" ? "Withdrawal Details" : "Deposit Details";
  }

  String get quantity {
    if (isClosed) return "Transaction Closed";
    final amount = d(item["amount"]);
    if (amount == 0) return "0 USDT";
    return "${amount.toStringAsFixed(amount.truncateToDouble() == amount ? 0 : 2)} USDT";
  }

  String get status {
    if (sourceType == "transfer") return "Transfer Completed";

    final s = item["status"]?.toString().toLowerCase() ?? "pending";

    if (s == "closed") return "Transaction Closed";
    if (s == "approved") return "Request Accepted";
    if (s == "rejected") return "Request Rejected";

    return "Request Pending";
  }

  Color get statusColor {
    final s = item["status"]?.toString().toLowerCase() ?? "";
    if (sourceType == "transfer") return green;
    if (s == "approved") return green;
    if (s == "rejected") return red;
    if (s == "closed") return grey;
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
    if (isClosed) return "Transaction Closed";
    final type = item["type"]?.toString().toLowerCase();
    return type == "withdrawal" ? "Funding Account" : "Wallet Account";
  }

  String get chainType {
    if (sourceType == "transfer") return "Internal Transfer";
    return isClosed ? "Closed Request" : "Merchant Request";
  }

  String get address {
    if (isClosed) return "—";

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
    if (isClosed) return "—";
    final xynder = d(item["xynder_fee"]);
    final network = d(item["network_fee"]);
    final total = xynder + network;
    if (total == 0) return "0";
    return total.toStringAsFixed(2);
  }

  // ── Other-party lookup (for the connect/chat row) ─────────────────────
  Map<String, dynamic> _otherParty() {
    if (sourceType == "transfer") {
      final myId = user["id"]?.toString();
      final senderId = item["sender_id"]?.toString();
      final isSender = senderId == myId;

      return {
        "id": isSender ? item["receiver_id"] : item["sender_id"],
        "name": isSender
            ? (item["receiver_name"] ?? "User")
            : (item["sender_name"] ?? "User"),
        "wallet_id": isSender
            ? (item["receiver_wallet_id"] ?? item["receiver_id"])
            : (item["sender_wallet_id"] ?? item["sender_id"]),
        "photo_url": isSender
            ? item["receiver_photo_url"]
            : item["sender_photo_url"],
        "photo": isSender ? item["receiver_photo"] : item["sender_photo"],
        "role": "user",
        "role_label": "User",
      };
    }

    final myRole = user["role"]?.toString().toLowerCase() ?? "client";

    if (myRole == "merchant") {
      return {
        "id": item["client_id"] ?? item["user_id"] ?? item["client"]?["id"],
        "name": item["client_name"] ??
            item["user_name"] ??
            item["client"]?["name"] ??
            "Client",
        "wallet_id": item["client_wallet_id"] ??
            item["user_wallet_id"] ??
            item["client"]?["wallet_id"],
        "photo_url": item["client_photo_url"] ??
            item["user_photo_url"] ??
            item["client"]?["photo_url"],
        "photo": item["client_photo"] ?? item["client"]?["photo"],
        "role": "client",
        "role_label": "Client",
      };
    }

    return {
      "id": item["merchant_id"] ?? item["merchant"]?["id"],
      "name": item["merchant_name"] ?? item["merchant"]?["name"] ?? "Merchant",
      "wallet_id":
          item["merchant_wallet_id"] ?? item["merchant"]?["wallet_id"],
      "photo_url":
          item["merchant_photo_url"] ?? item["merchant"]?["photo_url"],
      "photo": item["merchant_photo"] ?? item["merchant"]?["photo"],
      "is_online": item["merchant"]?["is_online"],
      "role": "merchant",
      "role_label": "Merchant",
    };
  }

  void copy(BuildContext context, String text) {
    if (text == "—") return;
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
                  if (copyable && right != "—") ...[
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

  // ── Connect-with-other-party row ──────────────────────────────────────
  // Now shows "Connect with {Name}" as the main heading, with the role
  // (Merchant / Client / User) as a muted subtitle underneath.
  Widget _chatWithRow(BuildContext context) {
    final other = _otherParty();
    final name = other["name"]?.toString() ?? "User";
    final roleLabel = other["role_label"]?.toString() ?? "User";

    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => ChatScreen(
              chatType: sourceType,
              chatId: item["id"].toString(),
              otherUser: Map<String, dynamic>.from(other),
            ),
          ),
        );
      },
      child: Container(
        margin: const EdgeInsets.fromLTRB(28, 0, 28, 26),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: cardBg,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: cardBorder),
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: amber.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Icon(Icons.chat_rounded, color: amber, size: 21),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    "Connect with $name",
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    roleLabel,
                    style: const TextStyle(
                      color: textMuted,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),
            const Icon(
              Icons.arrow_forward_ios_rounded,
              color: textMuted,
              size: 14,
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final amountTitle = isClosed ? "Status" : "Quantity";

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

            Expanded(
              child: SingleChildScrollView(
                child: Column(
                  children: [
                    const SizedBox(height: 50),

                    Text(amountTitle, style: const TextStyle(color: textMuted, fontSize: 21)),
                    const SizedBox(height: 10),
                    Text(
                      quantity,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: isClosed ? grey : Colors.white,
                        fontSize: isClosed ? 26 : 31,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 16),

                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          isClosed ||
                                  item["status"]?.toString().toLowerCase() == "approved" ||
                                  item["status"]?.toString().toLowerCase() == "rejected"
                              ? Icons.lock_rounded
                              : Icons.access_time_rounded,
                          color: statusColor,
                          size: 22,
                        ),
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

                    const SizedBox(height: 40),

                    if (canChat) _chatWithRow(context),

                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 28),
                      child: Column(
                        children: [
                          row(context, sourceType == "transfer" ? "Transfer Account" : "Request Account", account),
                          row(context, "Fees", fees),
                          row(context, "Chain Type", chainType),
                          row(context, "Time", fmtDate(item["created_at"])),
                          row(context, sourceType == "transfer" ? "Wallet Address" : "Merchant Address", address, copyable: !isClosed),
                          row(context, "Transaction No", hash, copyable: true),
                          row(context, "Reference ID", hash, copyable: true),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
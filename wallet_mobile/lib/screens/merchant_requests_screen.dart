import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'chat_screen.dart';

class _C {
  static const bg = Color(0xff0B0E11);
  static const surface = Color(0xff181A20);
  static const surfaceAlt = Color(0xff1E2329);

  static const border = Color(0xff2B3139);

  // Theme
  static const orange = Color(0xffF0B90B);
  static const amber = Color(0xffF0B90B);
  static const gold = Color(0xffFFD45A);

  static const success = Color(0xff22C55E);
  static const red = Color(0xffEF4444);
  static const blue = Color(0xffF0B90B);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff848E9C);

  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [
      Color(0xffF0B90B),
      Color(0xffC99400),
      Color(0xffFFD45A),
    ],
  );

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xff0B0E11),
      Color(0xff181A20),
      Color(0xff1E2329),
    ],
  );

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.2, -0.6),
    radius: 1.2,
    colors: [
      Color(0x33F0B90B),
      Color(0x22C99400),
      Color(0x00000000),
    ],
  );
}

class MerchantRequestsScreen extends StatefulWidget {
  const MerchantRequestsScreen({super.key});

  @override
  State<MerchantRequestsScreen> createState() => _MerchantRequestsScreenState();
}

class _MerchantRequestsScreenState extends State<MerchantRequestsScreen>
    with SingleTickerProviderStateMixin {
  bool loading = true;
  List requests = [];

  // How many pending cards to show before the user taps "View All".
  static const int _pendingPreviewCount = 3;
  bool showAllPending = false;

  // Fixed height for the scrollable history box.
  static const double _historyBoxHeight = 320;

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
    loadRequests();
  }

  @override
  void dispose() {
    _anim.dispose();
    super.dispose();
  }

  Future<void> loadRequests() async {
    setState(() => loading = true);

    final data = await ApiService.myRequests();

    if (!mounted) return;

    setState(() {
      loading = false;
      requests = data["success"] == true ? List.from(data["requests"] ?? []) : [];
    });
  }

  Future<void> approve(String id) async {
    final data = await ApiService.approveRequest(id);
    showMsg(data["message"]?.toString() ?? "Done");
    await loadRequests();
  }

  Future<void> reject(String id) async {
    final data = await ApiService.rejectRequest(id);
    showMsg(data["message"]?.toString() ?? "Done", success: false);
    await loadRequests();
  }

  Future<void> close(String id) async {
    final data = await ApiService.closeRequest(id);
    showMsg(data["message"]?.toString() ?? "Closed");
    await loadRequests();
  }

  Future<void> _confirmAction({
    required String title,
    required String message,
    required String confirmText,
    required Color color,
    required VoidCallback onConfirm,
  }) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          backgroundColor: _C.surface,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
            side: const BorderSide(color: _C.border),
          ),
          title: Text(
            title,
            style: const TextStyle(
              color: _C.textPrimary,
              fontSize: 16,
              fontWeight: FontWeight.w900,
            ),
          ),
          content: Text(
            message,
            style: const TextStyle(
              color: _C.textSecondary,
              fontSize: 13,
              height: 1.45,
              fontWeight: FontWeight.w500,
            ),
          ),
          actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text(
                "Cancel",
                style: TextStyle(
                  color: _C.textSecondary,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: color,
                foregroundColor: Colors.white,
                elevation: 0,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              onPressed: () => Navigator.pop(context, true),
              child: Text(
                confirmText,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ],
        );
      },
    );

    if (ok == true) onConfirm();
  }

  void showMsg(String msg, {bool success = true}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: success ? _C.amber : _C.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        content: Text(
          msg,
          style: TextStyle(
            color: success ? Colors.black : Colors.white,
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
    );
  }

  Color statusColor(String status) {
    status = status.toLowerCase();
    if (status == "approved") return _C.success;
    if (status == "rejected") return _C.red;
    if (status == "closed") return _C.textSecondary;
    return _C.amber;
  }

  List<Map<String, dynamic>> get _normalized =>
      requests.map((e) => Map<String, dynamic>.from(e)).toList();

  List<Map<String, dynamic>> get pendingList =>
      _normalized.where((r) => (r["status"]?.toString() ?? "pending") == "pending").toList();

  List<Map<String, dynamic>> get historyList =>
      _normalized.where((r) => (r["status"]?.toString() ?? "pending") != "pending").toList();

  // Pending list actually rendered on screen (respects the "View All" toggle).
  List<Map<String, dynamic>> get visiblePendingList => showAllPending
      ? pendingList
      : pendingList.take(_pendingPreviewCount).toList();

  int get pendingCount => pendingList.length;

  int get approvedCount =>
      _normalized.where((r) => r["status"]?.toString() == "approved").length;

  int get rejectedCount =>
      _normalized.where((r) => r["status"]?.toString() == "rejected").length;

  int get closedCount =>
      _normalized.where((r) => r["status"]?.toString() == "closed").length;

  // ================= TOP BAR =================

  Widget _topBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
      child: Row(
        children: [
          GestureDetector(
            onTap: () => Navigator.maybePop(context),
            child: Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: _C.surface,
                borderRadius: BorderRadius.circular(10),
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
                  "P2P Requests",
                  style: TextStyle(
                    color: _C.textPrimary,
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.3,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  "Approve or reject client requests",
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 11,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
          GestureDetector(
            onTap: loadRequests,
            child: Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                gradient: _C.gradientAccent,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.refresh_rounded,
                color: Colors.black,
                size: 19,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ================= STATS STRIP =================

  Widget _statChip({
    required String label,
    required int value,
    required Color color,
    required IconData icon,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 10),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: _C.border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 26,
              height: 26,
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(9),
                border: Border.all(color: color.withOpacity(0.3)),
              ),
              child: Icon(icon, color: color, size: 14),
            ),
            const SizedBox(height: 10),
            Text(
              value.toString(),
              style: const TextStyle(
                color: Colors.white,
                fontSize: 15,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: const TextStyle(
                color: _C.textSecondary,
                fontSize: 10,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _statsStrip() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 18, 20, 4),
      child: Row(
        children: [
          _statChip(
            label: "Pending",
            value: pendingCount,
            color: _C.amber,
            icon: Icons.pending_actions_rounded,
          ),
          const SizedBox(width: 10),
          _statChip(
            label: "Approved",
            value: approvedCount,
            color: _C.success,
            icon: Icons.check_circle_rounded,
          ),
          const SizedBox(width: 10),
          _statChip(
            label: "Rejected",
            value: rejectedCount,
            color: _C.red,
            icon: Icons.cancel_rounded,
          ),
          const SizedBox(width: 10),
          _statChip(
            label: "Closed",
            value: closedCount,
            color: _C.textSecondary,
            icon: Icons.lock_rounded,
          ),
        ],
      ),
    );
  }

  // ================= SECTION HEADER =================

  Widget _sectionHeader(
    String title,
    IconData icon, {
    String? tag,
    Widget? trailing,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 8),
      child: Row(
        children: [
          Icon(icon, color: _C.amber, size: 14),
          const SizedBox(width: 8),
          Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 13,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.2,
            ),
          ),
          if (tag != null) ...[
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: _C.amber.withOpacity(0.12),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: _C.amber.withOpacity(0.3)),
              ),
              child: Text(
                tag,
                style: const TextStyle(
                  color: _C.amber,
                  fontSize: 10,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ],
          if (trailing != null) ...[
            const Spacer(),
            trailing,
          ],
        ],
      ),
    );
  }

  // View All / Show Less toggle button for the pending section.
  Widget _viewAllPendingButton() {
    return GestureDetector(
      onTap: () => setState(() => showAllPending = !showAllPending),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: _C.amber.withOpacity(0.12),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: _C.amber.withOpacity(0.3)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              showAllPending ? "Show Less" : "View All",
              style: const TextStyle(
                color: _C.amber,
                fontSize: 10,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(width: 3),
            Icon(
              showAllPending
                  ? Icons.expand_less_rounded
                  : Icons.expand_more_rounded,
              color: _C.amber,
              size: 14,
            ),
          ],
        ),
      ),
    );
  }

  // ================= PENDING CARD (medium action card) =================

  Widget _statusPill(String status) {
    final color = statusColor(status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.35)),
      ),
      child: Text(
        status.toUpperCase(),
        style: TextStyle(
          color: color,
          fontSize: 10,
          fontWeight: FontWeight.w900,
          letterSpacing: 0.5,
        ),
      ),
    );
  }

  Widget _typeBadge({
    required String type,
    required bool isBuy,
    required String id,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: isBuy ? _C.blue.withOpacity(0.10) : _C.orange.withOpacity(0.10),
        borderRadius: BorderRadius.circular(9),
        border: Border.all(
          color: isBuy ? _C.blue.withOpacity(0.28) : _C.orange.withOpacity(0.28),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            isBuy ? Icons.trending_up_rounded : Icons.trending_down_rounded,
            color: _C.amber,
            size: 12,
          ),
          const SizedBox(width: 5),
          Text(
            "$type  #$id",
            style: const TextStyle(
              color: _C.amber,
              fontSize: 10,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }

  // Compact info row - reduced bottom padding vs original for a tighter box.
  Widget _infoRow(String title, dynamic value, {Color? color}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        children: [
          Text(
            title,
            style: const TextStyle(
              color: _C.textSecondary,
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
          ),
          const Spacer(),
          Flexible(
            child: Text(
              value?.toString() ?? "-",
              textAlign: TextAlign.right,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: color ?? Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _requestCard(Map<String, dynamic> r) {
    final user = Map<String, dynamic>.from(r["user"] ?? {});
    final id = r["id"].toString();
    final isBuy = r["type"] == "deposit";
    final type = isBuy ? "BUY USD" : "SELL USD";
    final status = r["status"]?.toString() ?? "pending";
    final note = (r["note"] ?? "").toString();

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: _C.border,
          width: 1,
        ),
      ),
      child: Column(
        children: [
          Row(
            children: [
              _typeBadge(type: type, isBuy: isBuy, id: id),
              const Spacer(),
              _statusPill(status),
            ],
          ),
          const SizedBox(height: 12),

          Row(
            children: [
              CircleAvatar(
                radius: 20,
                backgroundColor: _C.surfaceAlt,
                child: Text(
                  (user["name"]?.toString().isNotEmpty ?? false)
                      ? user["name"].toString()[0].toUpperCase()
                      : "U",
                  style: const TextStyle(
                    color: _C.amber,
                    fontWeight: FontWeight.w900,
                    fontSize: 14,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      user["name"]?.toString() ?? "Client",
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      user["email"]?.toString() ?? "-",
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: _C.textSecondary,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Compact info box - reduced padding so it takes up less vertical space.
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            decoration: BoxDecoration(
              color: _C.bg,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: _C.border),
            ),
            child: Column(
              children: [
                _infoRow("USD Amount", r["amount"]),
                _infoRow(
                  "INR Total",
                  r["total_amount"],
                  color: _C.amber,
                ),
                if (note.isNotEmpty) _infoRow("Note", note),
              ],
            ),
          ),

          const SizedBox(height: 12),

          Row(
            children: [
              Expanded(
                child: _solidButton(
                  label: "Accept",
                  icon: Icons.check_rounded,
                  color: _C.success,
                  onTap: () => _confirmAction(
                    title: "Accept request?",
                    message: "This will approve the client request and update the transaction status.",
                    confirmText: "Accept",
                    color: _C.success,
                    onConfirm: () => approve(id),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _solidButton(
                  label: "Reject",
                  icon: Icons.close_rounded,
                  color: _C.red,
                  onTap: () => _confirmAction(
                    title: "Reject request?",
                    message: "This will reject the client request. Please confirm before continuing.",
                    confirmText: "Reject",
                    color: _C.red,
                    onConfirm: () => reject(id),
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 8),

          Row(
            children: [
              Expanded(
                child: _outlineButton(
                  label: "Chat",
                  icon: Icons.chat_rounded,
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ChatScreen(
                          otherUser: user,
                          chatType: "request",
                          chatId: id,
                        ),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _outlineButton(
                  label: "Close",
                  icon: Icons.lock_outline_rounded,
                  onTap: () => _confirmAction(
                    title: "Close request?",
                    message: "This will mark the request as closed without approving or rejecting it.",
                    confirmText: "Close",
                    color: _C.textSecondary,
                    onConfirm: () => close(id),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _outlineButton({
    required String label,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 38,
        decoration: BoxDecoration(
          color: _C.bg,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: _C.amber),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: _C.amber, size: 15),
            const SizedBox(width: 7),
            Text(
              label,
              style: const TextStyle(
                color: _C.amber,
                fontWeight: FontWeight.w900,
                fontSize: 13,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _solidButton({
    required String label,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 38,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(10),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.22),
              blurRadius: 8,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: Colors.white, size: 15),
            const SizedBox(width: 6),
            Text(
              label,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w900,
                fontSize: 13,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ================= HISTORY TABLE (approved / rejected / closed) =================

  Widget _historyTableHeader() {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 0),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: _C.surfaceAlt,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
        border: Border.all(color: _C.border),
      ),
      child: const Row(
        children: [
          Expanded(
            flex: 4,
            child: Text(
              "CLIENT / TYPE",
              style: TextStyle(
                color: _C.textSecondary,
                fontSize: 10,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.4,
              ),
            ),
          ),
          Expanded(
            flex: 3,
            child: Text(
              "AMOUNT",
              textAlign: TextAlign.right,
              style: TextStyle(
                color: _C.textSecondary,
                fontSize: 10,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.4,
              ),
            ),
          ),
          Expanded(
            flex: 3,
            child: Text(
              "STATUS",
              textAlign: TextAlign.right,
              style: TextStyle(
                color: _C.textSecondary,
                fontSize: 10,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.4,
              ),
            ),
          ),
          SizedBox(width: 28),
        ],
      ),
    );
  }

  Widget _historyRow(Map<String, dynamic> r, bool isLast) {
    final user = Map<String, dynamic>.from(r["user"] ?? {});
    final id = r["id"].toString();
    final isBuy = r["type"] == "deposit";
    final status = r["status"]?.toString() ?? "pending";
    final color = statusColor(status);

    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => ChatScreen(
              otherUser: user,
              chatType: "request",
              chatId: id,
            ),
          ),
        );
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
        decoration: BoxDecoration(
          color: _C.surface,
          border: Border(
            bottom: isLast
                ? BorderSide.none
                : const BorderSide(color: _C.border, width: 1),
          ),
        ),
        child: Row(
          children: [
            Expanded(
              flex: 4,
              child: Row(
                children: [
                  Container(
                    width: 30,
                    height: 30,
                    decoration: BoxDecoration(
                      color: (isBuy ? _C.blue : _C.orange).withOpacity(0.10),
                      borderRadius: BorderRadius.circular(9),
                    ),
                    child: Icon(
                      isBuy ? Icons.trending_up_rounded : Icons.trending_down_rounded,
                      color: _C.amber,
                      size: 15,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          user["name"]?.toString() ?? "Client",
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          "#$id · ${isBuy ? 'Buy' : 'Sell'}",
                          style: const TextStyle(
                            color: _C.textSecondary,
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              flex: 3,
              child: Text(
                r["amount"]?.toString() ?? "-",
                textAlign: TextAlign.right,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 13,
                ),
              ),
            ),
            Expanded(
              flex: 3,
              child: Align(
                alignment: Alignment.centerRight,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: color.withOpacity(0.35)),
                  ),
                  child: Text(
                    status.toUpperCase(),
                    style: TextStyle(
                      color: color,
                      fontSize: 9,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0.4,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 6),
            const Icon(
              Icons.chevron_right_rounded,
              color: _C.textSecondary,
              size: 20,
            ),
          ],
        ),
      ),
    );
  }

  Widget _historyTable() {
    if (historyList.isEmpty) {
      return Container(
        margin: const EdgeInsets.fromLTRB(16, 0, 16, 10),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: _C.border),
        ),
        child: const Center(
          child: Text(
            "No resolved requests yet",
            style: TextStyle(
              color: _C.textSecondary,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      );
    }

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 10),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: _C.border),
      ),
      child: Column(
        children: [
          _historyTableHeader(),
          // Own fixed-height scrollable box so a long history doesn't push
          // the rest of the page down endlessly - it scrolls internally.
          SizedBox(
            height: _historyBoxHeight,
            child: ClipRRect(
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(10)),
              child: Container(
                color: _C.surface,
                child: Scrollbar(
                  thumbVisibility: true,
                  child: ListView.builder(
                    padding: EdgeInsets.zero,
                    physics: const ClampingScrollPhysics(),
                    itemCount: historyList.length,
                    itemBuilder: (context, i) =>
                        _historyRow(historyList[i], i == historyList.length - 1),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ================= EMPTY / LOADING =================

  Widget _emptyState() {
    return Center(
      child: Container(
        margin: const EdgeInsets.all(20),
        padding: const EdgeInsets.all(22),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(26),
          border: Border.all(color: _C.border),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: _C.amber.withOpacity(0.10),
                shape: BoxShape.circle,
                border: Border.all(color: _C.amber.withOpacity(0.30)),
              ),
              child: const Icon(
                Icons.receipt_long_rounded,
                color: _C.amber,
                size: 30,
              ),
            ),
            const SizedBox(height: 12),
            const Text(
              "No requests yet",
              style: TextStyle(
                color: Colors.white,
                fontSize: 15,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              "Client Buy/Sell USD requests will appear here",
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

  Widget _listBody() {
    if (loading) {
      return const Center(
        child: CircularProgressIndicator(
          color: _C.orange,
          strokeWidth: 2.5,
        ),
      );
    }

    if (requests.isEmpty) return _emptyState();

    return RefreshIndicator(
      color: _C.orange,
      backgroundColor: _C.surface,
      onRefresh: loadRequests,
      child: ListView(
        padding: const EdgeInsets.only(bottom: 30),
        children: [
          _statsStrip(),

          if (pendingList.isNotEmpty) ...[
            _sectionHeader(
              "Pending Requests",
              Icons.pending_actions_rounded,
              tag: pendingCount.toString(),
              trailing: pendingCount > _pendingPreviewCount
                  ? _viewAllPendingButton()
                  : null,
            ),
            for (final r in visiblePendingList) _requestCard(r),
          ],

          _sectionHeader("Request History", Icons.history_rounded),
          _historyTable(),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: Column(
          children: [
            _topBar(),
            Expanded(child: _listBody()),
          ],
        ),
      ),
    );
  }
}
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

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.2, -0.6),
    radius: 1.2,
    colors: [Color(0x55FF4500), Color(0x22FF8C00), Color(0x00000000)],
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

  void showMsg(String msg, {bool success = true}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: success ? _C.amber : _C.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
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
    if (status == "approved") return _C.success;
    if (status == "rejected") return _C.red;
    return _C.amber;
  }

  int get pendingCount {
    return requests.where((e) {
      final r = Map<String, dynamic>.from(e);
      return r["status"]?.toString() == "pending";
    }).length;
  }

  int get approvedCount {
    return requests.where((e) {
      final r = Map<String, dynamic>.from(e);
      return r["status"]?.toString() == "approved";
    }).length;
  }

  Widget _topBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Row(
        children: [
          GestureDetector(
            onTap: () => Navigator.maybePop(context),
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
                  "Merchant Requests",
                  style: TextStyle(
                    color: _C.textPrimary,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.3,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  "Approve or reject client requests",
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
          GestureDetector(
            onTap: loadRequests,
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
    );
  }

 
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

  Widget _infoRow(String title, dynamic value, {Color? color}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 9),
      child: Row(
        children: [
          Text(
            title,
            style: const TextStyle(
              color: _C.textSecondary,
              fontSize: 12,
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
                fontSize: 13,
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
      margin: const EdgeInsets.fromLTRB(20, 0, 20, 14),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(
          color: status == "pending" ? _C.amber : _C.border,
          width: status == "pending" ? 1.4 : 1,
        ),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(24),
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(18),
              child: Column(
                children: [
                  Row(
                    children: [
                      _typeBadge(type: type, isBuy: isBuy, id: id),
                      const Spacer(),
                      _statusPill(status),
                    ],
                  ),
                  const SizedBox(height: 16),

                  Row(
                    children: [
                      CircleAvatar(
                        radius: 24,
                        backgroundColor: _C.surfaceAlt,
                        child: Text(
                          (user["name"]?.toString().isNotEmpty ?? false)
                              ? user["name"].toString()[0].toUpperCase()
                              : "U",
                          style: const TextStyle(
                            color: _C.amber,
                            fontWeight: FontWeight.w900,
                            fontSize: 17,
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
                                fontSize: 15,
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

                  const SizedBox(height: 16),

                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: _C.bg,
                      borderRadius: BorderRadius.circular(16),
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

                  const SizedBox(height: 16),

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
                      if (status == "pending") ...[
                        const SizedBox(width: 8),
                        Expanded(
                          child: _solidButton(
                            label: "Accept",
                            icon: Icons.check_rounded,
                            color: _C.success,
                            onTap: () => approve(id),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _solidButton(
                            label: "Reject",
                            icon: Icons.close_rounded,
                            color: _C.red,
                            onTap: () => reject(id),
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            if (status == "pending")
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 9),
                decoration: const BoxDecoration(
                  gradient: _C.gradientAccent,
                ),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.pending_actions_rounded,
                        color: Colors.black, size: 15),
                    SizedBox(width: 6),
                    Text(
                      "Waiting for merchant action",
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
        height: 46,
        decoration: BoxDecoration(
          color: _C.bg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: _C.amber),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: _C.amber, size: 17),
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
        height: 46,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.22),
              blurRadius: 12,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: Colors.white, size: 17),
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

  Widget _emptyState() {
    return Center(
      child: Container(
        margin: const EdgeInsets.all(20),
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
                color: _C.orange.withOpacity(0.10),
                shape: BoxShape.circle,
                border: Border.all(color: _C.orange.withOpacity(0.25)),
              ),
              child: const Icon(
                Icons.receipt_long_rounded,
                color: _C.amber,
                size: 36,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              "No requests yet",
              style: TextStyle(
                color: Colors.white,
                fontSize: 16,
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
      child: ListView.builder(
        padding: const EdgeInsets.only(top: 18, bottom: 30),
        itemCount: requests.length,
        itemBuilder: (context, index) {
          return _requestCard(
            Map<String, dynamic>.from(requests[index]),
          );
        },
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
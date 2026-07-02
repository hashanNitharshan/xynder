import 'dart:math';
import 'package:flutter/material.dart';

import '../screens/profile_screen.dart';
import '../screens/request_screen.dart';
import '../services/api_service.dart';
import '../screens/login_screen.dart';
import '../widgets/bottom_nav.dart';
import '../screens/wallet_transfer_screen.dart';
import '../screens/merchant_requests_screen.dart';
import '../screens/transaction_detail_screen.dart';
import '../screens/chat_screen.dart';
import '../screens/history_screen.dart';
import '../screens/settings_screen.dart';

class _C {
  // Background
  static const bg = Color(0xff0B0E11);
  static const surface = Color(0xff181A20);
  static const surfaceAlt = Color(0xff202630);

  // Borders
  static const border = Color(0xff2B3139);
  static const borderFaint = Color(0xff30363D);

  // Binance Yellow
  static const orange = Color(0xffF0B90B);
  static const amber = Color(0xffD8A800);
  static const gold = Color(0xffC99400);

  static const green = Color(0xff02C076);
  static const red = Color(0xffF6465D);
  static const blue = Color(0xff3B82F6);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff848E9C);
  static const textMuted = Color(0xff5E6673);

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xff1A1D24),
      Color(0xff15181E),
      Color(0xff111318),
    ],
  );

  static const gradientAccent = LinearGradient(
    colors: [
      Color(0xffD8A800),
      Color(0xffF0B90B),
    ],
  );

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.5, -0.8),
    radius: 1.4,
    colors: [
      Color(0x18F0B90B),
      Color(0x06F0B90B),
      Colors.transparent,
    ],
  );
}
// ─────────────────────────────────────────────────────────────
//  GAUGE PAINTER
// ─────────────────────────────────────────────────────────────
class _GaugePainter extends CustomPainter {
  final double value; // 0.0 to 1.0
  _GaugePainter(this.value);

  @override
  void paint(Canvas canvas, Size size) {
    final cx = size.width / 2;
    final cy = size.height * 0.85;
    final r = size.width * 0.42;
    const startAngle = pi;
    const sweepAngle = pi;

    // Track
    final trackPaint = Paint()
      ..color = const Color(0xff2a2a2a)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 14
      ..strokeCap = StrokeCap.round;
    canvas.drawArc(
      Rect.fromCircle(center: Offset(cx, cy), radius: r),
      startAngle,
      sweepAngle,
      false,
      trackPaint,
    );

    // Progress with gradient shader
    final rect = Rect.fromCircle(center: Offset(cx, cy), radius: r);
    final gradPaint = Paint()
      ..shader = const LinearGradient(
        colors: [_C.orange, _C.amber, _C.gold],
      ).createShader(Rect.fromLTWH(0, 0, size.width, size.height))
      ..style = PaintingStyle.stroke
      ..strokeWidth = 14
      ..strokeCap = StrokeCap.round;

    canvas.drawArc(rect, startAngle, sweepAngle * value.clamp(0, 1), false, gradPaint);

    // Needle dot
    final angle = startAngle + sweepAngle * value.clamp(0, 1);
    final nx = cx + r * cos(angle);
    final ny = cy + r * sin(angle);
    canvas.drawCircle(
      Offset(nx, ny),
      7,
      Paint()..color = Colors.white,
    );
    canvas.drawCircle(
      Offset(nx, ny),
      4,
      Paint()..color = _C.amber,
    );
  }

  @override
  bool shouldRepaint(_GaugePainter old) => old.value != value;
}

// ─────────────────────────────────────────────────────────────
//  MAIN WIDGET
// ─────────────────────────────────────────────────────────────
class DashboardLayout extends StatefulWidget {
  final String title;
  final String role;
  final Map user;
  final List<String> menus;

  const DashboardLayout({
    super.key,
    required this.title,
    required this.role,
    required this.user,
    required this.menus,
  });

  @override
  State<DashboardLayout> createState() => _DashboardLayoutState();
}

class _DashboardLayoutState extends State<DashboardLayout>
    with TickerProviderStateMixin {
  late Map user;
  List requests = [];
  List transfers = [];
  int currentIndex = 0;
  bool balanceHidden = false;

  late AnimationController _cardAnim;
  late Animation<double> _cardFade;
  late Animation<Offset> _cardSlide;

  @override
  void initState() {
    super.initState();
    user = widget.user;

    _cardAnim = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );
    _cardFade = CurvedAnimation(parent: _cardAnim, curve: Curves.easeOut);
    _cardSlide = Tween<Offset>(
      begin: const Offset(0, 0.06),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _cardAnim, curve: Curves.easeOut));

    _cardAnim.forward();
    refreshProfile();
  }

  @override
  void dispose() {
    _cardAnim.dispose();
    super.dispose();
  }

 Future<void> refreshProfile() async {
  try {
    final profileData = await ApiService.profile();
    final requestData = await ApiService.myRequests();
    final transferData = await ApiService.walletTransfers();

    if (profileData["success"] == true && mounted) {
      user = Map<String, dynamic>.from(profileData["user"]);
    }

    if (requestData["success"] == true && mounted) {
      requests = List.from(requestData["requests"] ?? []);
    }

    if (transferData["success"] == true && mounted) {
      transfers = List.from(transferData["transfers"] ?? []);
    }

    await ApiService.ping();
  } catch (_) {}

  if (mounted) setState(() {});
}

  Future<void> logout() async {
    await ApiService.logout();
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (_) => false,
    );
  }

  double toDouble(dynamic v) => double.tryParse(v?.toString() ?? "0") ?? 0;

  bool get isVerified =>
      user["is_verified"] == true ||
      user["is_verified"] == 1 ||
      user["is_verified"]?.toString() == "1";

  String maskCard(dynamic v) {
    final c = v?.toString() ?? "";
    return c.length >= 4
        ? "**** **** **** ${c.substring(c.length - 4)}"
        : "**** **** **** ****";
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
    if (s == "approved") return _C.green;
    if (s == "rejected") return _C.red;
    return _C.amber;
  }

  // ═══════════════════════════════════════════
  //  TOP BAR
  // ═══════════════════════════════════════════
  Widget _topBar() {
    final photoUrl = ApiService.fixUrl(user["photo_url"]);
    final name = user["name"]?.toString() ?? "User";

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Row(
        children: [
          // Left: greeting
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "Good day 👋",
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  name,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: _C.textPrimary,
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    letterSpacing: -0.3,
                  ),
                ),
              ],
            ),
          ),

          // Notification bell
          Container(
            width: 42,
            height: 42,
            margin: const EdgeInsets.only(right: 10),
            decoration: BoxDecoration(
              color: _C.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: _C.border),
            ),
            child: const Icon(Icons.notifications_none_rounded,
                color: Colors.white, size: 20),
          ),

          // Avatar
          PopupMenuButton<String>(
            color: _C.surfaceAlt,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            onSelected: (v) {
              if (v == "profile") {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => ProfileScreen(user: user)),
                ).then((_) => refreshProfile());
              }
              if (v == "logout") logout();
            },
            itemBuilder: (_) => const [
              PopupMenuItem(
                value: "profile",
                child:
                    Text("Profile", style: TextStyle(color: Colors.white)),
              ),
              PopupMenuItem(
                value: "logout",
                child: Text("Logout",
                    style: TextStyle(color: Color(0xffef4444))),
              ),
            ],
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(color: _C.orange, width: 2),
                  ),
                  child: CircleAvatar(
                    radius: 20,
                   backgroundColor: const Color(0xff181A20),
                    backgroundImage: photoUrl.isNotEmpty
                        ? NetworkImage(photoUrl)
                        : null,
                    child: photoUrl.isEmpty
                        ? const Icon(Icons.person,
                            color: Colors.white54, size: 20)
                        : null,
                  ),
                ),
                if (isVerified)
                  Positioned(
                    right: -2,
                    bottom: -2,
                    child: Container(
                      padding: const EdgeInsets.all(2),
                      decoration: const BoxDecoration(
                        color: _C.bg,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.verified_rounded,
                          color: _C.orange,size: 14),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
Widget _heroCard() {
  final balance = toDouble(user["balance"]);
  final name = user["name"]?.toString() ?? "CARD HOLDER";

  return FadeTransition(
    opacity: _cardFade,
    child: SlideTransition(
      position: _cardSlide,
      child: Container(
        margin: const EdgeInsets.fromLTRB(20, 20, 20, 0),
        height: 200,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(28),
          gradient: _C.gradientCard,
          border: Border.all(
            color: const Color(0xff353C47),
            width: 1.2,
          ),
        ),
        child: Stack(
          children: [
            Positioned.fill(
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(28),
                  gradient: _C.gradientGlow,
                ),
              ),
            ),

            Positioned(
              top: -40,
              right: -40,
              child: Container(
                width: 160,
                height: 160,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: _C.orange.withOpacity(0.06),
                ),
              ),
            ),

            Positioned(
              bottom: -30,
              right: 60,
              child: Container(
                width: 100,
                height: 100,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: _C.amber.withOpacity(0.05),
                ),
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      ShaderMask(
                        shaderCallback: (b) =>
                            _C.gradientAccent.createShader(b),
                        child: const Text(
                          "XYNDER",
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 16,
                            letterSpacing: 2.5,
                          ),
                        ),
                      ),
                      const Spacer(),
                      GestureDetector(
                        onTap: () =>
                            setState(() => balanceHidden = !balanceHidden),
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.07),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Icon(
                            balanceHidden
                                ? Icons.visibility_off_outlined
                                : Icons.visibility_outlined,
                            color: Colors.white54,
                            size: 16,
                          ),
                        ),
                      ),
                    ],
                  ),

                  const Spacer(),

                  Text(
                    "My Wallet",
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.4),
                      fontSize: 11,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    balanceHidden
                        ? "••••••••"
                        : "\$${balance.toStringAsFixed(2)}",
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 30,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.5,
                    ),
                  ),

                  const Spacer(),

                  Row(
                    children: [
                      Text(
                        maskCard(user["card_number"]),
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.5),
                          fontSize: 13,
                          letterSpacing: 1.8,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const Spacer(),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            name.toUpperCase(),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 0.5,
                            ),
                          ),
                          Text(
                            "12/29",
                            style: TextStyle(
                              color: Colors.white.withOpacity(0.4),
                              fontSize: 10,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    ),
  );
}

 

  Widget _outlineBtn(
      {required IconData icon,
      required String label,
      required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 13),
        decoration: BoxDecoration(
         color: const Color(0xff181A20),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: _C.border),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: Colors.white70, size: 17),
            const SizedBox(width: 7),
            Text(
  label,
  style: TextStyle(
    color: Colors.white,
                fontWeight: FontWeight.w700,
                fontSize: 13,
              ),
            ),
          ],
        ),
      ),
    );
  }
Widget _solidBtn({
  required IconData icon,
  required String label,
  required VoidCallback onTap,
}) {
  return GestureDetector(
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.symmetric(vertical: 13),
      decoration: BoxDecoration(
      gradient: _C.gradientAccent,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            icon,
            color: Colors.white,
            size: 17,
          ),
          const SizedBox(width: 7),
          Text(
            label,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w800,
              fontSize: 13,
            ),
          ),
        ],
      ),
    ),
  );
}
  // ═══════════════════════════════════════════
  //  STATISTICS CARD  (gauge + bar chart)
  // ═══════════════════════════════════════════
  Widget _statsCard() {
    final balance = toDouble(user["balance"]);

    double approvedRequests = 0;
    double pendingRequests = 0;
    double totalTransfers = 0;

    for (final r in requests) {
      final item = Map<String, dynamic>.from(r);
      final status = item["status"]?.toString().toLowerCase() ?? "pending";
      final amount = toDouble(item["amount"]);
      if (status == "approved") {
        approvedRequests += amount;
      } else if (status == "pending") {
        pendingRequests += amount;
      }
    }

    for (final t in transfers) {
      final item = Map<String, dynamic>.from(t);
      totalTransfers += toDouble(item["amount"]);
    }

    final totalActivity = approvedRequests + pendingRequests + totalTransfers;
    final limit = totalActivity <= 0 ? 1000.0 : max(1000.0, totalActivity * 1.25);
    final requestRatio = (approvedRequests / limit).clamp(0.0, 1.0);
    final transferRatio = (totalTransfers / limit).clamp(0.0, 1.0);
    final pendingRatio = (pendingRequests / limit).clamp(0.0, 1.0);
    final totalRatio = (totalActivity / limit).clamp(0.0, 1.0);

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(26),
          border: Border.all(
  color: const Color(0xff2B2B2B),
),
       gradient: const LinearGradient(
  begin: Alignment.topLeft,
  end: Alignment.bottomRight,
  colors: [
    Color(0xff1A1D24),
    Color(0xff161A20),
    Color(0xff12151B),
  ],
),
       boxShadow: [
  BoxShadow(
    color: Colors.black.withOpacity(0.55),
    blurRadius: 25,
    offset: const Offset(0, 15),
  ),
],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    gradient: _C.gradientAccent,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.insights_rounded,
                    color: Color(0xff111111), size: 22),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "Statistics",
                        style: TextStyle(
                          color: _C.textPrimary,
                          fontWeight: FontWeight.w900,
                          fontSize: 17,
                          letterSpacing: -0.2,
                        ),
                      ),
                      SizedBox(height: 2),
                      Text(
                        "Wallet activity overview",
                        style: TextStyle(color: _C.textSecondary, fontSize: 11),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.06),
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(color: Colors.white.withOpacity(0.08)),
                  ),
                  child: const Text(
                    "Live",
                    style: TextStyle(
                      color: _C.amber,
                      fontWeight: FontWeight.w800,
                      fontSize: 11,
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 18),

            Row(
              children: [
                Expanded(
                  child: _statTile(
                    title: "Balance",
                    value: "\$${balance.toStringAsFixed(2)}",
                    icon: Icons.account_balance_wallet_rounded,
                    color: _C.orange,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _statTile(
                    title: "Activity",
                    value: "\$${totalActivity.toStringAsFixed(2)}",
                    icon: Icons.bolt_rounded,
                    color: _C.amber,
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),

            ClipRRect(
              borderRadius: BorderRadius.circular(999),
              child: SizedBox(
                height: 10,
                child: Stack(
                  children: [
                    Container(color: _C.surfaceAlt),
                    FractionallySizedBox(
                      widthFactor: totalRatio,
                      child: Container(
                        decoration: const BoxDecoration(
                          gradient: _C.gradientAccent,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
            _miniProgressBar("Approved Buy / Sell", _C.orange, requestRatio),
            const SizedBox(height: 8),
            _miniProgressBar("Wallet Transfers", _C.blue, transferRatio),
            const SizedBox(height: 8),
            _miniProgressBar("P2P Request", _C.amber, pendingRatio),
          ],
        ),
      ),
    );
  }
Widget _cardActions() {
  return Padding(
    padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
    child: Row(
      children: [
        Expanded(
          child: _outlineBtn(
            icon: Icons.account_balance_wallet_rounded,
            label: "Request",
            onTap: () => setState(() => currentIndex = 1),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _solidBtn(
            icon: Icons.send_rounded,
            label: "Transfer",
            onTap: () => setState(() => currentIndex = 2),
          ),
        ),
      ],
    ),
  );
}
  Widget _statTile({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.045),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.white.withOpacity(0.075)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 30,
                height: 30,
                decoration: BoxDecoration(
                  color: color.withOpacity(0.14),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: color, size: 17),
              ),
              const Spacer(),
              Icon(Icons.trending_up_rounded, color: color, size: 16),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            title,
            style: const TextStyle(color: _C.textSecondary, fontSize: 11),
          ),
          const SizedBox(height: 3),
          Text(
            value,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: _C.textPrimary,
              fontSize: 15,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }

  Widget _miniProgressBar(String label, Color color, double value) {
    return Column(
      children: [
        Row(
          children: [
            Text(label,
                style: const TextStyle(
                    color: _C.textSecondary, fontSize: 12)),
            const Spacer(),
            Text(
              "${(value * 100).toStringAsFixed(0)}%",
              style: TextStyle(
                  color: color,
                  fontSize: 12,
                  fontWeight: FontWeight.w700),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(6),
          child: LinearProgressIndicator(
            value: value.clamp(0.0, 1.0),
            minHeight: 6,
            backgroundColor: _C.surfaceAlt,
            valueColor: AlwaysStoppedAnimation<Color>(color),
          ),
        ),
      ],
    );
  }

  // ═══════════════════════════════════════════
  //  ACCOUNT STATUS STRIP
  // ═══════════════════════════════════════════
  Widget _statusStrip() {
    if (isVerified) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
      child: GestureDetector(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => ProfileScreen(user: user)),
        ).then((_) => refreshProfile()),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          decoration: BoxDecoration(
            color: const Color(0xff2a1500),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: _C.orange.withOpacity(0.5)),
          ),
          child: Row(
            children: [
              const Icon(Icons.warning_amber_rounded,
                  color: _C.amber, size: 18),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  "Complete KYC to unlock all features",
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
              const Text(
                "Verify →",
                style: TextStyle(
                  color: _C.orange,
                  fontWeight: FontWeight.w800,
                  fontSize: 12,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _recentActivityBox() {
    final latest = _activityItems(includeTransfers: true).take(40).toList();

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
      child: Container(
        height: 260,
        decoration: BoxDecoration(
         color: const Color(0xff181A20),
          borderRadius: BorderRadius.circular(24),
          border: Border.all(color: _C.border),
        ),
        child: latest.isEmpty
            ? const Center(
                child: Text(
                  "No transactions yet",
                  style: TextStyle(color: _C.textSecondary, fontSize: 13),
                ),
              )
            : Scrollbar(
                thumbVisibility: true,
                radius: const Radius.circular(20),
                child: ListView.separated(
                  padding: const EdgeInsets.all(12),
                  physics: const BouncingScrollPhysics(),
                  itemCount: latest.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (_, index) => _transactionTile(
                    latest[index],
                    compact: true,
                    removeOuterMargin: true,
                  ),
                ),
              ),
      ),
    );
  }

  List<Map<String, dynamic>> _activityItems({
    bool includeTransfers = false,
    String query = "",
    bool newestFirst = true,
  }) {
    final allItems = <Map<String, dynamic>>[];

    for (final r in requests) {
      allItems.add({
        "source_type": "request",
        "data": Map<String, dynamic>.from(r),
      });
    }

    if (includeTransfers) {
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
      return newestFirst ? bd.compareTo(ad) : ad.compareTo(bd);
    });

    final q = query.trim().toLowerCase();
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

  Widget _transactionTile(
    Map<String, dynamic> wrap, {
    bool compact = false,
    bool removeOuterMargin = false,
  }) {
    final sourceType = wrap["source_type"].toString();
    final r = Map<String, dynamic>.from(wrap["data"]);

    final status = sourceType == "transfer"
        ? "completed"
        : r["status"]?.toString() ?? "pending";

    final type = r["type"]?.toString().toLowerCase() ?? "";
    final color = statusColor(status);
    final isTransfer = sourceType == "transfer";

    final title = isTransfer
        ? "WALLET TRANSFER"
        : type == "withdrawal"
            ? "SELL USD"
            : "BUY USD";

    final idText = r["id"]?.toString() ?? "-";

    return GestureDetector(
      onTap: () => _showTransactionOptions(r, sourceType),
      child: Container(
        margin: removeOuterMargin
            ? EdgeInsets.zero
            : const EdgeInsets.fromLTRB(20, 0, 20, 10),
        padding: EdgeInsets.all(compact ? 13 : 16),
        decoration: BoxDecoration(
          color: compact ? _C.surfaceAlt : _C.surface,
          borderRadius: BorderRadius.circular(compact ? 16 : 18),
          border: Border.all(color: compact ? _C.borderFaint : _C.border),
        ),
        child: Row(
          children: [
            Container(
              width: compact ? 40 : 44,
              height: compact ? 40 : 44,
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(
                isTransfer
                    ? Icons.swap_horiz_rounded
                    : status.toLowerCase() == "approved"
                        ? Icons.check_rounded
                        : status.toLowerCase() == "rejected"
                            ? Icons.close_rounded
                            : Icons.access_time_rounded,
                color: color,
                size: compact ? 18 : 20,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          color: _C.textPrimary,
                          fontWeight: FontWeight.w800,
                          fontSize: compact ? 13 : 14,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        "#$idText",
                        style: const TextStyle(
                          color: _C.textMuted,
                          fontWeight: FontWeight.w600,
                          fontSize: 10,
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
                  "\$${r["amount"] ?? "0.00"}",
                  style: TextStyle(
                    color: _C.textPrimary,
                    fontWeight: FontWeight.w900,
                    fontSize: compact ? 14 : 15,
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
                    status.toUpperCase(),
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
              color: _C.orange.withOpacity(0.12),
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
  //  HELPERS
  // ═══════════════════════════════════════════
  Widget _sectionHeader(String title,
      {String? action, VoidCallback? onAction}) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
      child: Row(
        children: [
          Text(
            title,
            style: const TextStyle(
              color: _C.textPrimary,
              fontSize: 17,
              fontWeight: FontWeight.w800,
              letterSpacing: -0.2,
            ),
          ),
          const Spacer(),
          if (action != null)
            GestureDetector(
              onTap: onAction,
              child: Text(
                action,
                style: const TextStyle(
                  color: _C.orange,
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                ),
              ),
            ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  HOME PAGE
  // ═══════════════════════════════════════════
  Widget _homePage() {
    return RefreshIndicator(
      onRefresh: refreshProfile,
      color: _C.orange,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _topBar(),
            _heroCard(),
            _cardActions(),
            const SizedBox(height: 20),
            _statsCard(),
            _sectionHeader("Recent Activity",
                action: "See all",
                onAction: () => setState(() => currentIndex = 3)),
            _recentActivityBox(),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  BODY ROUTER
  // ═══════════════════════════════════════════
  Widget _body() {
    final role = user["role"]?.toString().toLowerCase() ?? "";

    switch (currentIndex) {
      case 0:
        return _homePage();
      case 1:
        return role == "merchant"
            ? const MerchantRequestsScreen()
            : const RequestScreen();
      case 2:
        return WalletTransferScreen(user: user, onSuccess: refreshProfile);
      case 3:
        return HistoryScreen(user: user);
      default:
        return SettingsScreen(user: user, onProfileUpdated: refreshProfile);
    }
  }

  // ═══════════════════════════════════════════
  //  BUILD
  // ═══════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(child: _body()),
      bottomNavigationBar: BottomNav(
        currentIndex: currentIndex,
        onTap: (i) => BottomNav.handleTap(
          context,
          i,
          setState: (tab) => setState(() => currentIndex = tab),
        ),
      ),
    );
  }
}
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
import '../utils/page_transitions.dart';

class _C {
  static const bg = Color(0xff0B0E11);
  static const surface = Color(0xff181A20);
  static const surfaceAlt = Color(0xff1E2329);
  static const field = Color(0xff1E2329);

  static const border = Color(0xff2B3139);
  static const borderFaint = Color(0xff30363D);

  static const gold = Color(0xffF0B90B);
  static const goldDark = Color(0xffC99400);
  static const goldLight = Color(0xffFFD45A);

  static const green = Color(0xff02C076);
  static const red = Color(0xffF6465D);
  static const blue = Color(0xff3B8EFF);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff848E9C);
  static const textMuted = Color(0xff5E6673);

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xff181A20),
      Color(0xff11151B),
      Color(0xff0B0E11),
    ],
  );

  static const gradientAccent = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xffC99400),
      Color(0xffF0B90B),
      Color(0xffFFD45A),
    ],
  );

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.5, -0.8),
    radius: 1.4,
    colors: [
      Color(0x22F0B90B),
      Color(0x08F0B90B),
      Colors.transparent,
    ],
  );
}

/// Donut chart painter — draws proportional colored segments with rounded
/// caps and a small gap between them. Values are absolute amounts; the
/// painter normalizes them internally. `sweep` (0..1) animates the draw.
class _DonutChartPainter extends CustomPainter {
  final List<double> values;
  final List<Color> colors;
  final double sweep;
  final double stroke;

  _DonutChartPainter({
    required this.values,
    required this.colors,
    required this.sweep,
    this.stroke = 20,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = (min(size.width, size.height) / 2) - stroke / 2;
    final rect = Rect.fromCircle(center: center, radius: radius);

    // Track ring
    final trackPaint = Paint()
      ..color = _C.surfaceAlt
      ..style = PaintingStyle.stroke
      ..strokeWidth = stroke;
    canvas.drawCircle(center, radius, trackPaint);

    final total = values.fold<double>(0, (a, b) => a + b);
    if (total <= 0) return;

    const startBase = -pi / 2; // top
    const gap = 0.06; // radians gap between segments
    double start = startBase;

    for (var i = 0; i < values.length; i++) {
      final frac = values[i] / total;
      final fullSweep = (frac * 2 * pi) - gap;
      if (fullSweep <= 0) {
        start += frac * 2 * pi;
        continue;
      }
      final animSweep = fullSweep * sweep;

      final segPaint = Paint()
        ..color = colors[i]
        ..style = PaintingStyle.stroke
        ..strokeWidth = stroke
        ..strokeCap = StrokeCap.round;

      canvas.drawArc(rect, start + gap / 2, animSweep, false, segPaint);
      start += frac * 2 * pi;
    }
  }

  @override
  bool shouldRepaint(_DonutChartPainter old) =>
      old.sweep != sweep ||
      old.values != values ||
      old.colors != colors;
}

class _ChipPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final rrect = RRect.fromRectAndRadius(
      Rect.fromLTWH(0, 0, size.width, size.height),
      const Radius.circular(6),
    );

    final basePaint = Paint()
      ..shader = const LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [
          Color(0xffFFE18A),
          Color(0xffF0B90B),
          Color(0xffC99400),
        ],
      ).createShader(Rect.fromLTWH(0, 0, size.width, size.height));

    canvas.drawRRect(rrect, basePaint);

    final linePaint = Paint()
      ..color = const Color(0x33000000)
      ..strokeWidth = 1.1;

    canvas.drawLine(
      Offset(size.width * 0.36, 0),
      Offset(size.width * 0.36, size.height),
      linePaint,
    );
    canvas.drawLine(
      Offset(size.width * 0.64, 0),
      Offset(size.width * 0.64, size.height),
      linePaint,
    );
    canvas.drawLine(
      Offset(0, size.height * 0.5),
      Offset(size.width, size.height * 0.5),
      linePaint,
    );

    final boxPaint = Paint()
      ..color = const Color(0x33000000)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.1;

    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(
          size.width * 0.36,
          size.height * 0.18,
          size.width * 0.28,
          size.height * 0.64,
        ),
        const Radius.circular(3),
      ),
      boxPaint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _StaggerItem extends StatelessWidget {
  final Animation<double> controller;
  final double start;
  final double end;
  final Widget child;
  final Offset beginOffset;

  const _StaggerItem({
    required this.controller,
    required this.start,
    required this.end,
    required this.child,
    this.beginOffset = const Offset(0, 0.08),
  });

  @override
  Widget build(BuildContext context) {
    final curved = CurvedAnimation(
      parent: controller,
      curve: Interval(start, end, curve: Curves.easeOutCubic),
    );

    return FadeTransition(
      opacity: curved,
      child: SlideTransition(
        position: Tween<Offset>(
          begin: beginOffset,
          end: Offset.zero,
        ).animate(curved),
        child: child,
      ),
    );
  }
}

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

  late AnimationController _staggerAnim;

  @override
  void initState() {
    super.initState();
    user = widget.user;

    _staggerAnim = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 550),
    );

    _staggerAnim.forward();
    refreshProfile();
  }

  @override
  void dispose() {
    _staggerAnim.dispose();
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
      XRoute.fade(const LoginScreen()),
      (_) => false,
    );
  }

  double toDouble(dynamic v) {
    return double.tryParse(v?.toString() ?? "0") ?? 0;
  }

  bool get isVerified {
    return user["is_verified"] == true ||
        user["is_verified"] == 1 ||
        user["is_verified"]?.toString() == "1";
  }

  String maskCard(dynamic v) {
    final c = v?.toString() ?? "";

    if (c.length >= 4) {
      return "**** **** **** ${c.substring(c.length - 4)}";
    }

    return "**** **** **** ****";
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
    if (s == "rejected" || s == "failed" || s == "cancelled") return _C.red;
    if (s == "closed") return Colors.black;

    return _C.gold;
  }

  Color amountColor({
    required String sourceType,
    required String type,
    required String status,
  }) {
    status = status.toLowerCase();
    type = type.toLowerCase();

    if (status == "closed") return Colors.black;
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
    status = status.toLowerCase();
    type = type.toLowerCase();

    if (status == "closed") return "Closed";

    final value = amount?.toString() ?? "0.00";

    if (sourceType == "transfer") return "+\$$value";
    if (type == "withdrawal") return "-\$$value";

    return "+\$$value";
  }

  Widget _topBar() {
    final photoUrl = ApiService.fixUrl(user["photo_url"]);
    final name = user["name"]?.toString() ?? "User";

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
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
          Container(
            width: 42,
            height: 42,
            margin: const EdgeInsets.only(right: 10),
            decoration: BoxDecoration(
              color: _C.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: _C.border),
            ),
            child: const Icon(
              Icons.notifications_none_rounded,
              color: Colors.white,
              size: 20,
            ),
          ),
          PopupMenuButton<String>(
            color: _C.surfaceAlt,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
            ),
            onSelected: (v) {
              if (v == "profile") {
                Navigator.push(
                  context,
                  XRoute.slideRight(ProfileScreen(user: user)),
                ).then((_) => refreshProfile());
              }

              if (v == "logout") logout();
            },
            itemBuilder: (_) => const [
              PopupMenuItem(
                value: "profile",
                child: Text(
                  "Profile",
                  style: TextStyle(color: Colors.white),
                ),
              ),
              PopupMenuItem(
                value: "logout",
                child: Text(
                  "Logout",
                  style: TextStyle(color: _C.red),
                ),
              ),
            ],
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(color: _C.gold, width: 2),
                  ),
                  child: CircleAvatar(
                    radius: 20,
                    backgroundColor: _C.surface,
                    backgroundImage:
                        photoUrl.isNotEmpty ? NetworkImage(photoUrl) : null,
                    child: photoUrl.isEmpty
                        ? const Icon(
                            Icons.person,
                            color: Colors.white54,
                            size: 20,
                          )
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
                      child: const Icon(
                        Icons.verified_rounded,
                        color: _C.gold,
                        size: 14,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  /// Unique "physical card" styled balance card — chip, contactless icon,
  /// embossed-style number, and a custom dual-ring brand mark instead of
  /// a generic gradient block.
  Widget _heroCard() {
    final balance = toDouble(user["balance"]);
    final name = user["name"]?.toString() ?? "CARD HOLDER";

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 20, 20, 0),
      height: 252,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(28),
        gradient: _C.gradientCard,
        border: Border.all(
          color: _C.gold.withOpacity(0.30),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.45),
            blurRadius: 24,
            offset: const Offset(0, 14),
          ),
        ],
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
                color: _C.gold.withOpacity(0.06),
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
                color: _C.goldDark.withOpacity(0.05),
              ),
            ),
          ),
          // Fine diagonal texture lines for a "security print" feel.
          Positioned.fill(
            child: ClipRRect(
              borderRadius: BorderRadius.circular(28),
              child: CustomPaint(
                painter: _CardTexturePainter(),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 18, 24, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ShaderMask(
                      shaderCallback: (b) {
                        return _C.gradientAccent.createShader(b);
                      },
                      child: const Text(
                        "BITXNOW",
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 16,
                          letterSpacing: 2.5,
                        ),
                      ),
                    ),
                    const Spacer(),
                    Icon(
                      Icons.contactless_rounded,
                      color: Colors.white.withOpacity(0.55),
                      size: 22,
                    ),
                    const SizedBox(width: 10),
                    GestureDetector(
                      onTap: () {
                        setState(() => balanceHidden = !balanceHidden);
                      },
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
                const SizedBox(height: 14),
                SizedBox(
                  width: 42,
                  height: 30,
                  child: CustomPaint(painter: _ChipPainter()),
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
                Text(
                  maskCard(user["card_number"]),
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.55),
                    fontSize: 15,
                    letterSpacing: 2.4,
                    fontWeight: FontWeight.w700,
                    fontFeatures: const [FontFeature.tabularFigures()],
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            "CARD HOLDER",
                            style: TextStyle(
                              color: Colors.white.withOpacity(0.35),
                              fontSize: 8,
                              letterSpacing: 0.8,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            name.toUpperCase(),
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          "VALID THRU",
                          style: TextStyle(
                            color: Colors.white.withOpacity(0.35),
                            fontSize: 8,
                            letterSpacing: 0.8,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 2),
                        const Text(
                          "12/29",
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(width: 14),
                    // Custom dual-ring brand mark (own mark, not a copy of
                    // any existing card network logo).
                    SizedBox(
                      width: 34,
                      height: 22,
                      child: Stack(
                        children: [
                          Positioned(
                            left: 0,
                            child: Container(
                              width: 22,
                              height: 22,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: _C.gold.withOpacity(0.85),
                              ),
                            ),
                          ),
                          Positioned(
                            right: 0,
                            child: Container(
                              width: 22,
                              height: 22,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: Colors.white.withOpacity(0.85),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  /// Statistics section — standard fintech style: a donut breakdown chart
  /// with a center total, plus a clean legend (colored dot + label + amount
  /// + mini progress bar) for each category.
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

    // Segments for the donut + legend.
    final segValues = <double>[
      approvedRequests,
      totalTransfers,
      pendingRequests,
    ];
    final segColors = <Color>[_C.green, _C.blue, _C.gold];
    final segLabels = <String>["Approved", "Transfers", "Pending"];

    double pct(double v) =>
        totalActivity <= 0 ? 0 : (v / totalActivity).clamp(0.0, 1.0);

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(26),
          border: Border.all(color: _C.gold.withOpacity(0.25)),
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
                  child: const Icon(
                    Icons.pie_chart_rounded,
                    color: _C.bg,
                    size: 22,
                  ),
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
                        style: TextStyle(
                          color: _C.textSecondary,
                          fontSize: 11,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: _C.gold.withOpacity(0.10),
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(color: _C.gold.withOpacity(0.20)),
                  ),
                  child: const Text(
                    "Live",
                    style: TextStyle(
                      color: _C.gold,
                      fontWeight: FontWeight.w800,
                      fontSize: 11,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 18),

            // Donut + legend row
            Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Donut chart with center total
                SizedBox(
                  width: 132,
                  height: 132,
                  child: TweenAnimationBuilder<double>(
                    tween: Tween(begin: 0, end: 1),
                    duration: const Duration(milliseconds: 750),
                    curve: Curves.easeOutCubic,
                    builder: (_, v, __) {
                      return Stack(
                        alignment: Alignment.center,
                        children: [
                          CustomPaint(
                            size: const Size(132, 132),
                            painter: _DonutChartPainter(
                              values: totalActivity <= 0
                                  ? [1, 0, 0]
                                  : segValues,
                              colors: segColors,
                              sweep: v,
                              stroke: 18,
                            ),
                          ),
                          Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Text(
                                "TOTAL",
                                style: TextStyle(
                                  color: _C.textMuted,
                                  fontSize: 9,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.2,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                "\$${(totalActivity * v).toStringAsFixed(0)}",
                                style: const TextStyle(
                                  color: _C.textPrimary,
                                  fontSize: 20,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: -0.5,
                                ),
                              ),
                            ],
                          ),
                        ],
                      );
                    },
                  ),
                ),
                const SizedBox(width: 18),

                // Legend
                Expanded(
                  child: Column(
                    children: List.generate(segValues.length, (i) {
                      return Padding(
                        padding: EdgeInsets.only(
                          bottom: i == segValues.length - 1 ? 0 : 14,
                        ),
                        child: _legendRow(
                          label: segLabels[i],
                          color: segColors[i],
                          amount: segValues[i],
                          ratio: pct(segValues[i]),
                        ),
                      );
                    }),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 18),
            Container(height: 1, color: _C.border),
            const SizedBox(height: 16),

            // Bottom summary tiles
            Row(
              children: [
                Expanded(
                  child: _statTile(
                    title: "Balance",
                    value: "\$${balance.toStringAsFixed(2)}",
                    icon: Icons.account_balance_wallet_rounded,
                    color: _C.gold,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _statTile(
                    title: "Activity",
                    value: "\$${totalActivity.toStringAsFixed(2)}",
                    icon: Icons.bolt_rounded,
                    color: _C.goldDark,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  /// One legend line: colored dot + label + amount, with a thin animated
  /// progress bar underneath showing its share of total activity.
  Widget _legendRow({
    required String label,
    required Color color,
    required double amount,
    required double ratio,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              width: 9,
              height: 9,
              decoration: BoxDecoration(
                color: color,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                label,
                style: const TextStyle(
                  color: _C.textSecondary,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            Text(
              "\$${amount.toStringAsFixed(0)}",
              style: const TextStyle(
                color: _C.textPrimary,
                fontSize: 12.5,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(6),
          child: TweenAnimationBuilder<double>(
            tween: Tween(begin: 0, end: ratio),
            duration: const Duration(milliseconds: 750),
            curve: Curves.easeOutCubic,
            builder: (_, v, __) {
              return LinearProgressIndicator(
                value: v,
                minHeight: 5,
                backgroundColor: _C.surfaceAlt,
                valueColor: AlwaysStoppedAnimation<Color>(color),
              );
            },
          ),
        ),
      ],
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
            style: const TextStyle(
              color: _C.textSecondary,
              fontSize: 11,
            ),
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

  Widget _statusStrip() {
    if (isVerified) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
      child: GestureDetector(
        onTap: () {
          Navigator.push(
            context,
            XRoute.slideRight(ProfileScreen(user: user)),
          ).then((_) => refreshProfile());
        },
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          decoration: BoxDecoration(
            color: const Color(0xff2A2100),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: _C.gold.withOpacity(0.50)),
          ),
          child: const Row(
            children: [
              Icon(
                Icons.warning_amber_rounded,
                color: _C.gold,
                size: 18,
              ),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  "Complete KYC to unlock all features",
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
              Text(
                "Verify →",
                style: TextStyle(
                  color: _C.gold,
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
          color: _C.surface,
          borderRadius: BorderRadius.circular(24),
          border: Border.all(color: _C.border),
        ),
        child: latest.isEmpty
            ? const Center(
                child: Text(
                  "No transactions yet",
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 13,
                  ),
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
                  itemBuilder: (_, index) {
                    return TweenAnimationBuilder<double>(
                      tween: Tween(begin: 0, end: 1),
                      duration: Duration(
                        milliseconds: 180 + (index * 20).clamp(0, 200),
                      ),
                      curve: Curves.easeOutCubic,
                      builder: (_, v, child) {
                        return Opacity(
                          opacity: v,
                          child: Transform.translate(
                            offset: Offset((1 - v) * 24, 0),
                            child: child,
                          ),
                        );
                      },
                      child: _transactionTile(
                        latest[index],
                        compact: true,
                        removeOuterMargin: true,
                      ),
                    );
                  },
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
          : type == "withdrawal"
              ? "sell usd"
              : "buy usd";

      final status = sourceType == "transfer"
          ? "completed"
          : r["status"]?.toString().toLowerCase() ?? "pending";

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

    final idText =
        r["transaction_no"]?.toString() ?? r["id"]?.toString() ?? "-";

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
      onTap: () => _showTransactionOptions(r, sourceType),
      child: Container(
        margin: removeOuterMargin
            ? EdgeInsets.zero
            : const EdgeInsets.fromLTRB(20, 0, 20, 10),
        padding: EdgeInsets.all(compact ? 13 : 16),
        decoration: BoxDecoration(
          color: compact ? _C.surfaceAlt : _C.surface,
          borderRadius: BorderRadius.circular(compact ? 16 : 18),
          border: Border.all(
            color: isClosed
                ? Colors.black
                : compact
                    ? _C.borderFaint
                    : _C.border,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: compact ? 40 : 44,
              height: compact ? 40 : 44,
              decoration: BoxDecoration(
                color: isClosed
                    ? Colors.black.withOpacity(0.35)
                    : color.withOpacity(0.12),
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
                color: isClosed ? Colors.white : color,
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
                      Flexible(
                        child: Text(
                          title,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: _C.textPrimary,
                            fontWeight: FontWeight.w800,
                            fontSize: compact ? 13 : 14,
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
                    fontSize: compact ? 14 : 15,
                  ),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: isClosed
                        ? Colors.black.withOpacity(0.35)
                        : color.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    isClosed ? "CLOSED" : status.toUpperCase(),
                    style: TextStyle(
                      color: isClosed ? Colors.white : color,
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

  dynamic _safeGet(dynamic map, String key) {
    if (map is Map) return map[key];
    return null;
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
            ? item["receiver_name"] ??
                _safeGet(item["receiver"], "name") ??
                "Receiver"
            : item["sender_name"] ??
                _safeGet(item["sender"], "name") ??
                "Sender",
        "wallet_id": isSender
            ? item["receiver_wallet_id"] ??
                _safeGet(item["receiver"], "wallet_id")
            : item["sender_wallet_id"] ??
                _safeGet(item["sender"], "wallet_id"),
        "photo_url": isSender
            ? item["receiver_photo_url"] ??
                _safeGet(item["receiver"], "photo_url")
            : item["sender_photo_url"] ??
                _safeGet(item["sender"], "photo_url"),
        "role": "user",
      };
    }

    return {
      "id": item["merchant_id"] ?? _safeGet(item["merchant"], "id"),
      "name": item["merchant_name"] ??
          _safeGet(item["merchant"], "name") ??
          "Merchant",
      "wallet_id": item["merchant_wallet_id"] ??
          _safeGet(item["merchant"], "wallet_id"),
      "photo_url": item["merchant_photo_url"] ??
          _safeGet(item["merchant"], "photo_url"),
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
                      XRoute.slideRight(
                        TransactionDetailScreen(
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
                      XRoute.slideRight(
                        ChatScreen(
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
                color: _C.gold.withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: _C.gold, size: 21),
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

  Widget _sectionHeader(
    String title, {
    String? action,
    VoidCallback? onAction,
  }) {
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
                  color: _C.gold,
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                ),
              ),
            ),
        ],
      ),
    );
  }

  void _switchTab(int i) {
    setState(() => currentIndex = i);

    if (i == 0) {
      _staggerAnim.forward(from: 0);
    }
  }

  Widget _homePage() {
    return RefreshIndicator(
      onRefresh: () async {
        await refreshProfile();
        _staggerAnim.forward(from: 0);
      },
      color: _C.gold,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _StaggerItem(
              controller: _staggerAnim,
              start: 0.00,
              end: 0.45,
              child: _topBar(),
            ),
            _StaggerItem(
              controller: _staggerAnim,
              start: 0.08,
              end: 0.55,
              child: _heroCard(),
            ),
            const SizedBox(height: 20),
            _StaggerItem(
              controller: _staggerAnim,
              start: 0.18,
              end: 0.68,
              child: _statusStrip(),
            ),
            _StaggerItem(
              controller: _staggerAnim,
              start: 0.24,
              end: 0.75,
              child: _statsCard(),
            ),
            _StaggerItem(
              controller: _staggerAnim,
              start: 0.32,
              end: 0.85,
              child: _sectionHeader(
                "Recent Activity",
                action: "See all",
                onAction: () => _switchTab(3),
              ),
            ),
            _StaggerItem(
              controller: _staggerAnim,
              start: 0.38,
              end: 0.95,
              child: _recentActivityBox(),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

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
        return WalletTransferScreen(
          user: user,
          onSuccess: refreshProfile,
        );

      case 3:
        return HistoryScreen(user: user);

      default:
        return SettingsScreen(
          user: user,
          onProfileUpdated: refreshProfile,
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: AnimatedSwitcher(
          duration: const Duration(milliseconds: 180),
          switchInCurve: Curves.easeOutCubic,
          switchOutCurve: Curves.easeInCubic,
          transitionBuilder: (child, animation) {
            final slide = Tween<Offset>(
              begin: const Offset(0, 0.02),
              end: Offset.zero,
            ).animate(animation);

            return FadeTransition(
              opacity: animation,
              child: SlideTransition(
                position: slide,
                child: child,
              ),
            );
          },
          child: KeyedSubtree(
            key: ValueKey(currentIndex),
            child: _body(),
          ),
        ),
      ),
      bottomNavigationBar: BottomNav(
        currentIndex: currentIndex,
        onTap: (i) {
          BottomNav.handleTap(
            context,
            i,
            setState: (tab) => _switchTab(tab),
          );
        },
      ),
    );
  }
}

/// Subtle diagonal hairline texture drawn across the card to give it a
/// "printed security pattern" feel instead of a flat gradient block.
class _CardTexturePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withOpacity(0.025)
      ..strokeWidth = 1;

    const gap = 14.0;
    for (double x = -size.height; x < size.width; x += gap) {
      canvas.drawLine(
        Offset(x, size.height),
        Offset(x + size.height, 0),
        paint,
      );
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
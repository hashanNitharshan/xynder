import 'dart:math';
import 'package:flutter/material.dart';

import '../screens/profile_screen.dart';
import '../screens/request_screen.dart';
import '../services/api_service.dart';
import '../screens/login_screen.dart';
import '../widgets/bottom_nav.dart';
import '../screens/wallet_transfer_screen.dart';
import '../screens/chat_users_screen.dart';
import '../screens/merchant_requests_screen.dart';
import 'pinwheel_loader.dart';
import '../screens/transaction_detail_screen.dart';

// ─────────────────────────────────────────────────────────────
//  DESIGN TOKENS
// ─────────────────────────────────────────────────────────────
class _C {
  static const bg = Color(0xff0a0a0a);
  static const surface = Color(0xff141414);
  static const surfaceAlt = Color(0xff1c1c1e);
  static const border = Color(0xff2a2a2a);
  static const borderFaint = Color(0xff1e1e1e);

  static const orange = Color(0xffFF4500);
  static const amber = Color(0xffFFB800);
  static const gold = Color(0xffFFD700);

  static const green = Color(0xff22c55e);
  static const red = Color(0xffef4444);
  static const blue = Color(0xff3b82f6);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff8E8E93);
  static const textMuted = Color(0xff3a3a3c);

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xff1a0a00), Color(0xff2d1200), Color(0xff1a0800)],
  );

  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [orange, amber, gold],
  );

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.2, -0.6),
    radius: 1.2,
    colors: [Color(0x55FF4500), Color(0x22FF8C00), Color(0x00000000)],
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
                    backgroundColor: _C.surfaceAlt,
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
                          color: _C.green, size: 14),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  HERO CARD  (Wavix-style with glow)
  // ═══════════════════════════════════════════
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
            border: Border.all(color: const Color(0xff3a1500), width: 1.2),
          ),
          child: Stack(
            children: [
              // Glow
              Positioned.fill(
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(28),
                    gradient: _C.gradientGlow,
                  ),
                ),
              ),

              // Decorative circles
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

              // Content
              Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Top row
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

                    // Balance
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

                    // Bottom row
                    Row(
                      children: [
                        // Card number
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

  // ═══════════════════════════════════════════
  //  CARD ACTION BUTTONS  (Manage / Transfer)
  // ═══════════════════════════════════════════
  Widget _cardActions() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
      child: Row(
        children: [
          Expanded(
            child: _outlineBtn(
              icon: Icons.credit_card_rounded,
              label: "Manage Card",
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => ProfileScreen(user: user)),
              ),
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

  Widget _outlineBtn(
      {required IconData icon,
      required String label,
      required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 13),
        decoration: BoxDecoration(
          color: _C.surface,
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
              style: const TextStyle(
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

  Widget _solidBtn(
      {required IconData icon,
      required String label,
      required VoidCallback onTap}) {
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
            Icon(icon, color: Colors.black, size: 17),
            const SizedBox(width: 7),
            Text(
              label,
              style: const TextStyle(
                color: Colors.black,
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
  //  RECENT CONTACTS ROW
  // ═══════════════════════════════════════════
  Widget _recentContacts() {
    // Extract unique counterpart users from transfers
    final seen = <int>{};
    final contacts = <Map>[];
    for (final t in transfers) {
      final r = Map<String, dynamic>.from(t);
      final myId = int.tryParse(user["id"]?.toString() ?? "0") ?? 0;
      final senderId = int.tryParse(r["sender_id"]?.toString() ?? "0") ?? 0;
      final receiverId =
          int.tryParse(r["receiver_id"]?.toString() ?? "0") ?? 0;
      final otherId = senderId == myId ? receiverId : senderId;
      if (!seen.contains(otherId) && otherId != 0) {
        seen.add(otherId);
        contacts.add(r);
      }
    }

    final List<Color> avatarColors = [
      const Color(0xffFF4500),
      const Color(0xffFFB800),
      const Color(0xff3b82f6),
      const Color(0xff22c55e),
      const Color(0xffa855f7),
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionHeader("Recent Contacts",
            action: "Manage", onAction: () => setState(() => currentIndex = 3)),
        SizedBox(
          height: 90,
          child: ListView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.only(left: 20, right: 12),
            children: [
              // Add new
              GestureDetector(
                onTap: () => setState(() => currentIndex = 2),
                child: Column(
                  children: [
                    Container(
                      width: 52,
                      height: 52,
                      margin: const EdgeInsets.only(right: 14),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(
                            color: _C.border, width: 1.5,
                            style: BorderStyle.solid),
                        color: _C.surface,
                      ),
                      child: const Icon(Icons.add_rounded,
                          color: Colors.white54, size: 22),
                    ),
                    const SizedBox(height: 6),
                    const Text("Add new",
                        style: TextStyle(
                            color: _C.textSecondary,
                            fontSize: 10,
                            fontWeight: FontWeight.w500)),
                  ],
                ),
              ),

              if (contacts.isEmpty)
                Center(
                  child: Text("No transfers yet",
                      style:
                          TextStyle(color: _C.textSecondary, fontSize: 12)),
                )
              else
                ...contacts.take(8).toList().asMap().entries.map((e) {
                  final idx = e.key;
                  final r = Map<String, dynamic>.from(e.value);
                  final myId =
                      int.tryParse(user["id"]?.toString() ?? "0") ?? 0;
                  final senderId =
                      int.tryParse(r["sender_id"]?.toString() ?? "0") ?? 0;
                  final otherName = senderId == myId
                      ? (r["receiver_name"]?.toString() ?? "User")
                      : (r["sender_name"]?.toString() ?? "User");
                  final initials = otherName.isNotEmpty
                      ? otherName[0].toUpperCase()
                      : "?";
                  return GestureDetector(
                    onTap: () => setState(() => currentIndex = 3),
                    child: Column(
                      children: [
                        Container(
                          width: 52,
                          height: 52,
                          margin: const EdgeInsets.only(right: 14),
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: avatarColors[idx % avatarColors.length]
                                .withOpacity(0.18),
                            border: Border.all(
                              color: avatarColors[idx % avatarColors.length]
                                  .withOpacity(0.3),
                            ),
                          ),
                          child: Center(
                            child: Text(
                              initials,
                              style: TextStyle(
                                color:
                                    avatarColors[idx % avatarColors.length],
                                fontWeight: FontWeight.w900,
                                fontSize: 18,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          otherName.split(" ").first,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  );
                }),
            ],
          ),
        ),
      ],
    );
  }

  // ═══════════════════════════════════════════
  //  QUICK ACTIONS
  // ═══════════════════════════════════════════
  Widget _quickActions() {
    final role = user["role"]?.toString().toLowerCase() ?? "";

    final List<_Action> actions = [
      _Action(Icons.swap_horiz_rounded, "Transfer", _C.orange,
          () => setState(() => currentIndex = 2)),
      _Action(Icons.trending_up_rounded, role == "merchant" ? "Requests" : "Buy/Sell",
          _C.amber, () => setState(() => currentIndex = 1)),
      _Action(Icons.chat_bubble_outline_rounded, "Chats", _C.blue,
          () => setState(() => currentIndex = 3)),
      _Action(Icons.history_rounded, "History", const Color(0xffa855f7),
          () => setState(() => currentIndex = 4)),
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 18),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(24),
          border: Border.all(color: _C.border),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: actions.map((a) {
            return GestureDetector(
              onTap: a.onTap,
              child: Column(
                children: [
                  Container(
                    width: 50,
                    height: 50,
                    decoration: BoxDecoration(
                      color: a.color.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Icon(a.icon, color: a.color, size: 22),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    a.label,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            );
          }).toList(),
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
          border: Border.all(color: const Color(0xff3a1500)),
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Color(0xff17100b), Color(0xff111111), Color(0xff1a0a00)],
          ),
          boxShadow: [
            BoxShadow(
              color: _C.orange.withOpacity(0.08),
              blurRadius: 26,
              offset: const Offset(0, 14),
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
                      color: Colors.black, size: 22),
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
            _miniProgressBar("Pending Requests", _C.amber, pendingRatio),
          ],
        ),
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

  Widget _transactionList({bool includeTransfers = false}) {
    final latest = _activityItems(includeTransfers: includeTransfers).take(60).toList();

    if (latest.isEmpty) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
        child: Container(
          height: 120,
          decoration: BoxDecoration(
            color: _C.surface,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: _C.border),
          ),
          child: const Center(
            child: Text(
              "No transactions yet",
              style: TextStyle(color: _C.textSecondary, fontSize: 13),
            ),
          ),
        ),
      );
    }

    return Column(
      children: latest.map((wrap) => _transactionTile(wrap)).toList(),
    );
  }

  Widget _recentActivityBox() {
    final latest = _activityItems(includeTransfers: false).take(40).toList();

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

  List<Map<String, dynamic>> _activityItems({bool includeTransfers = false}) {
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
      return bd.compareTo(ad);
    });

    return allItems;
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

    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => TransactionDetailScreen(
              item: r,
              sourceType: sourceType,
              user: user,
            ),
          ),
        );
      },
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
                  Text(
                    title,
                    style: TextStyle(
                      color: _C.textPrimary,
                      fontWeight: FontWeight.w800,
                      fontSize: compact ? 13 : 14,
                    ),
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
  // ═══════════════════════════════════════════
  //  HELPERS  // ═══════════════════════════════════════════
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

  void _showSnack(String msg, {bool success = true}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      backgroundColor: success ? _C.green : _C.red,
      content: Text(msg),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ));
  }

  Widget _dialogInput(TextEditingController ctrl, String label,
      {bool obscure = false, int maxLines = 1}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextField(
        controller: ctrl,
        obscureText: obscure,
        maxLines: maxLines,
        style: const TextStyle(color: Colors.white),
        decoration: InputDecoration(
          labelText: label,
          labelStyle: const TextStyle(color: _C.textSecondary),
          filled: true,
          fillColor: _C.bg,
          border:
              OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: _C.border),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: _C.orange),
          ),
        ),
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
            _quickActions(),
            _recentContacts(),
            _statsCard(),
            _sectionHeader("Recent Activity",
                action: "See all",
                onAction: () => setState(() => currentIndex = 4)),
            _recentActivityBox(),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  HISTORY PAGE
  // ═══════════════════════════════════════════
  Widget _historyPage() {
    return SingleChildScrollView(
      child: Column(
        children: [
          _topBar(),
          _sectionHeader("Transaction History"),
          _transactionList(includeTransfers: true),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  SETTINGS PAGE
  // ═══════════════════════════════════════════
  Widget _settingsTile({
    required IconData icon,
    required String label,
    required String sub,
    required VoidCallback onTap,
    Color color = _C.orange,
  }) {
    return GestureDetector(
      onTap: onTap,
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
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(13),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label,
                      style: const TextStyle(
                          color: _C.textPrimary,
                          fontWeight: FontWeight.w700,
                          fontSize: 14)),
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

  Future<void> _showChangePassword() async {
    final curr = TextEditingController();
    final nw = TextEditingController();
    final cf = TextEditingController();
    bool loading = false;
    await showDialog(
      context: context,
      builder: (_) => StatefulBuilder(builder: (ctx, set) {
        return AlertDialog(
          backgroundColor: _C.surfaceAlt,
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20)),
          title: const Text("Change Password",
              style: TextStyle(color: Colors.white)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _dialogInput(curr, "Current Password", obscure: true),
              _dialogInput(nw, "New Password", obscure: true),
              _dialogInput(cf, "Confirm Password", obscure: true),
            ],
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text("Cancel")),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: _C.orange,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: loading
                  ? null
                  : () async {
                      set(() => loading = true);
                      final res = await ApiService.changePassword(
                        currentPassword: curr.text.trim(),
                        newPassword: nw.text.trim(),
                        confirmPassword: cf.text.trim(),
                      );
                      set(() => loading = false);
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      _showSnack(
                        res["message"] ??
                            (res["success"] == true
                                ? "Password changed"
                                : "Failed"),
                        success: res["success"] == true,
                      );
                    },
              child: loading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.black))
                  : const Text("Save"),
            ),
          ],
        );
      }),
    );
  }

  Future<void> _showSupport() async {
    final nameCtrl =
        TextEditingController(text: user["name"]?.toString() ?? "");
    final emailCtrl =
        TextEditingController(text: user["email"]?.toString() ?? "");
    final msgCtrl = TextEditingController();
    bool loading = false;
    List tickets = [];
    final td = await ApiService.supportTickets();
    if (td["success"] == true) tickets = td["tickets"] ?? [];

    await showDialog(
      context: context,
      builder: (_) => StatefulBuilder(builder: (ctx, set) {
        return AlertDialog(
          backgroundColor: _C.surfaceAlt,
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20)),
          title: const Text("Help & Support",
              style: TextStyle(color: Colors.white)),
          content: SizedBox(
            width: double.maxFinite,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (tickets.isNotEmpty) ...[
                    const Text("Your Tickets",
                        style: TextStyle(
                            color: Colors.white70,
                            fontWeight: FontWeight.w700)),
                    const SizedBox(height: 8),
                    ...tickets.take(3).map((t) {
                      final ti = Map<String, dynamic>.from(t);
                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: _C.bg,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: _C.border),
                        ),
                        child: Text(
                          "${ti["message"]}\nStatus: ${ti["status"]}",
                          style: const TextStyle(
                              color: Colors.white70, fontSize: 12),
                        ),
                      );
                    }),
                    const SizedBox(height: 12),
                  ],
                  _dialogInput(nameCtrl, "Name"),
                  _dialogInput(emailCtrl, "Email"),
                  _dialogInput(msgCtrl, "Message", maxLines: 4),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text("Cancel")),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: _C.orange,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: loading
                  ? null
                  : () async {
                      set(() => loading = true);
                      final res = await ApiService.createSupportTicket(
                        name: nameCtrl.text.trim(),
                        email: emailCtrl.text.trim(),
                        message: msgCtrl.text.trim(),
                      );
                      set(() => loading = false);
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      _showSnack(
                        res["message"] ??
                            (res["success"] == true
                                ? "Ticket submitted"
                                : "Failed"),
                        success: res["success"] == true,
                      );
                    },
              child: loading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.black))
                  : const Text("Submit"),
            ),
          ],
        );
      }),
    );
  }

  Widget _settingsPage() {
    final photoUrl = ApiService.fixUrl(user["photo_url"]);
    final name = user["name"]?.toString() ?? "User";
    final email = user["email"]?.toString() ?? "";
    final phone = user["phone"]?.toString() ?? "";

    return SingleChildScrollView(
      child: Column(
        children: [
          _topBar(),
          _sectionHeader("Settings"),

          // Profile card
          GestureDetector(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => ProfileScreen(user: user)),
            ).then((_) => refreshProfile()),
            child: Container(
              margin: const EdgeInsets.fromLTRB(20, 0, 20, 16),
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                gradient: _C.gradientCard,
                borderRadius: BorderRadius.circular(22),
                border: Border.all(color: const Color(0xff3a1500)),
              ),
              child: Row(
                children: [
                  Container(
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: _C.orange, width: 2),
                    ),
                    child: CircleAvatar(
                      radius: 30,
                      backgroundColor: _C.surfaceAlt,
                      backgroundImage: photoUrl.isNotEmpty
                          ? NetworkImage(photoUrl)
                          : null,
                      child: photoUrl.isEmpty
                          ? const Icon(Icons.person,
                              color: Colors.white54, size: 28)
                          : null,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(name,
                            style: const TextStyle(
                                color: _C.textPrimary,
                                fontSize: 17,
                                fontWeight: FontWeight.w800)),
                        if (phone.isNotEmpty)
                          Text(phone,
                              style: const TextStyle(
                                  color: _C.textSecondary, fontSize: 12)),
                        if (email.isNotEmpty)
                          Text(email,
                              style: const TextStyle(
                                  color: _C.textSecondary, fontSize: 12)),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Icon(
                              isVerified
                                  ? Icons.verified_rounded
                                  : Icons.warning_amber_rounded,
                              color: isVerified ? _C.green : _C.amber,
                              size: 14,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              isVerified ? "Verified" : "Unverified",
                              style: TextStyle(
                                color: isVerified ? _C.green : _C.amber,
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.arrow_forward_ios_rounded,
                      color: _C.textSecondary, size: 14),
                ],
              ),
            ),
          ),

          _settingsTile(
            icon: Icons.payment_rounded,
            label: "Payment Methods",
            sub: "Bank, UPI and card details",
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => ProfileScreen(user: user)),
            ).then((_) => refreshProfile()),
          ),
          _settingsTile(
            icon: Icons.lock_reset_rounded,
            label: "Change Password",
            sub: "Update your account password",
            onTap: _showChangePassword,
          ),
          _settingsTile(
            icon: Icons.support_agent_rounded,
            label: "Help & Support",
            sub: "Raise a support ticket",
            onTap: _showSupport,
          ),
          _settingsTile(
            icon: Icons.logout_rounded,
            label: "Logout",
            sub: "Sign out from your account",
            color: _C.red,
            onTap: logout,
          ),
          const SizedBox(height: 30),
        ],
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
        return const ChatUsersScreen();
      case 4:
        return _historyPage();
      default:
        return _settingsPage();
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
        onTap: (i) => setState(() => currentIndex = i),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────
//  HELPERS
// ─────────────────────────────────────────────────────────────
class _Action {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  _Action(this.icon, this.label, this.color, this.onTap);
}
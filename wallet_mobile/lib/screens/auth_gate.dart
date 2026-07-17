import 'package:flutter/material.dart';

import '../services/api_service.dart';
import '../utils/page_transitions.dart';
import 'admin_dashboard.dart';
import 'client_dashboard.dart';
import 'login_screen.dart';
import 'merchant_dashboard.dart';

const _kBg = Color(0xff0B0E11);
const _kYellow = Color(0xffF0B90B);

class AuthGate extends StatefulWidget {
  const AuthGate({super.key});

  @override
  State<AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<AuthGate> with TickerProviderStateMixin {
  late final AnimationController _logoController;
  late final AnimationController _textController;
  late final Animation<double> _logoScale;
  late final Animation<double> _logoFade;
  late final Animation<double> _textFade;
  late final Animation<Offset> _textSlide;

  @override
  void initState() {
    super.initState();

    _logoController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );
    _textController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );

    _logoScale = Tween<double>(begin: 0.6, end: 1).animate(
      CurvedAnimation(parent: _logoController, curve: Curves.easeOutBack),
    );
    _logoFade = CurvedAnimation(
      parent: _logoController,
      curve: const Interval(0, 0.6, curve: Curves.easeOut),
    );
    _textFade = CurvedAnimation(
      parent: _textController,
      curve: Curves.easeOut,
    );
    _textSlide = Tween<Offset>(
      begin: const Offset(0, 0.4),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(
        parent: _textController,
        curve: Curves.easeOutCubic,
      ),
    );

    _logoController.forward();
    Future.delayed(const Duration(milliseconds: 350), () {
      if (mounted) _textController.forward();
    });

    WidgetsBinding.instance.addPostFrameCallback((_) => checkLogin());
  }

  @override
  void dispose() {
    _logoController.dispose();
    _textController.dispose();
    super.dispose();
  }

  Future<void> checkLogin() async {
    await Future.delayed(const Duration(milliseconds: 1600));

    final savedToken = await ApiService.token();
    if (!mounted) return;

    if (savedToken == null || savedToken.isEmpty) {
      Navigator.pushReplacement(context, XRoute.fade(const LoginScreen()));
      return;
    }

    // profile() is cache-busted and always returns the current database user.
    final data = await ApiService.profile();
    if (!mounted) return;

    if (data['success'] != true || data['user'] is! Map) {
      await ApiService.clearToken();
      if (!mounted) return;
      Navigator.pushReplacement(context, XRoute.fade(const LoginScreen()));
      return;
    }

    final user = Map<String, dynamic>.from(data['user'] as Map);
    final role = user['role']?.toString().trim().toLowerCase() ?? '';

    if (role == 'admin') {
      Navigator.pushReplacement(
        context,
        XRoute.fade(const AdminDashboard()),
      );
    } else if (role == 'merchant') {
      Navigator.pushReplacement(
        context,
        XRoute.fade(MerchantDashboard(user: user)),
      );
    } else {
      Navigator.pushReplacement(
        context,
        XRoute.fade(ClientDashboard(user: user)),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      body: Stack(
        children: [
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: SizedBox(
              height: 220,
              width: double.infinity,
              child: CustomPaint(painter: _TradingChartPainter()),
            ),
          ),
          Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                AnimatedBuilder(
                  animation: _logoController,
                  builder: (_, child) {
                    return Opacity(
                      opacity: _logoFade.value,
                      child: Transform.scale(
                        scale: _logoScale.value,
                        child: child,
                      ),
                    );
                  },
                  child: Image.asset(
                    'assets/images/bitxnow_logo.jpeg',
                    width: 130,
                    height: 130,
                    fit: BoxFit.contain,
                  ),
                ),
                const SizedBox(height: 18),
                FadeTransition(
                  opacity: _textFade,
                  child: SlideTransition(
                    position: _textSlide,
                    child: const Text(
                      'BITXNOW',
                      style: TextStyle(
                        color: _kYellow,
                        fontSize: 34,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 6,
                      ),
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
}

class _TradingChartPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final barPaint = Paint()
      ..color = Colors.white.withOpacity(0.05)
      ..style = PaintingStyle.fill;

    final heights = <double>[
      0.30, 0.55, 0.42, 0.70, 0.50, 0.85, 0.62, 0.95,
      0.72, 0.60, 0.88, 0.66, 0.78, 0.52, 0.90, 0.68,
    ];

    final zoneWidth = w * 0.5;
    final start = w * 0.5;
    final slot = zoneWidth / heights.length;
    final barWidth = slot * 0.35;

    for (var i = 0; i < heights.length; i++) {
      final barHeight = heights[i] * h * 0.6;
      final x = start + (i * slot) + (slot - barWidth) / 2;
      canvas.drawRRect(
        RRect.fromRectAndRadius(
          Rect.fromLTWH(x, h - barHeight, barWidth, barHeight),
          const Radius.circular(1),
        ),
        barPaint,
      );
    }

    final points = <Offset>[
      const Offset(0.00, 0.18),
      const Offset(0.12, 0.16),
      const Offset(0.24, 0.24),
      const Offset(0.36, 0.20),
      const Offset(0.50, 0.40),
      const Offset(0.64, 0.62),
      const Offset(0.78, 0.78),
      const Offset(0.90, 0.86),
      const Offset(1.00, 0.92),
    ].map((p) => Offset(p.dx * w, h - (p.dy * h))).toList();

    final path = Path()..moveTo(points.first.dx, points.first.dy);
    for (var i = 0; i < points.length - 1; i++) {
      final p0 = points[i];
      final p1 = points[i + 1];
      final middleX = (p0.dx + p1.dx) / 2;
      path.cubicTo(middleX, p0.dy, middleX, p1.dy, p1.dx, p1.dy);
    }

    canvas.drawPath(
      path,
      Paint()
        ..color = _kYellow
        ..strokeWidth = 2.2
        ..style = PaintingStyle.stroke
        ..strokeCap = StrokeCap.round,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

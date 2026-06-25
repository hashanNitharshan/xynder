import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'login_screen.dart';
import 'client_dashboard.dart';
import 'merchant_dashboard.dart';
import 'admin_dashboard.dart';

class AuthGate extends StatefulWidget {
  const AuthGate({super.key});

  @override
  State<AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<AuthGate> {
  @override
  void initState() {
    super.initState();
    checkLogin();
    // ✅ REMOVED update check from here — moved to LoginScreen
  }

  Future<void> checkLogin() async {
    await Future.delayed(const Duration(seconds: 2));
    final savedToken = await ApiService.token();
    if (!mounted) return;

    if (savedToken == null || savedToken.isEmpty) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      return;
    }

    final data = await ApiService.profile();
    if (!mounted) return;

    if (data["success"] == true) {
      final user = data["user"];
      final role = user["role"]?.toString();

      if (role == "admin") {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => const AdminDashboard()),
        );
      } else if (role == "merchant") {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => MerchantDashboard(user: user)),
        );
      } else {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => ClientDashboard(user: user)),
        );
      }
    } else {
      await ApiService.clearToken();
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xff050805),
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            XynderLogo(size: 120),
            SizedBox(height: 18),
            Text(
              "XYNDER",
              style: TextStyle(
                color: Color(0xff00ff5a),
                fontSize: 28,
                fontWeight: FontWeight.w900,
                letterSpacing: 2,
              ),
            ),
            SizedBox(height: 28),
            CircularProgressIndicator(
              color: Color(0xff00ff5a),
              strokeWidth: 2.5,
            ),
          ],
        ),
      ),
    );
  }
}

class XynderLogo extends StatelessWidget {
  final double size;
  const XynderLogo({super.key, required this.size});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size,
      height: size,
      child: CustomPaint(painter: _XynderLogoPainter()),
    );
  }
}

class _XynderLogoPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xff00ff5a)
      ..strokeWidth = size.width * 0.14
      ..strokeCap = StrokeCap.round
      ..style = PaintingStyle.stroke;

    canvas.drawLine(
      Offset(size.width * 0.20, size.height * 0.20),
      Offset(size.width * 0.80, size.height * 0.80),
      paint,
    );
    canvas.drawLine(
      Offset(size.width * 0.80, size.height * 0.20),
      Offset(size.width * 0.20, size.height * 0.80),
      paint,
    );
  }

  @override
  bool shouldRepaint(CustomPainter oldDelegate) => false;
}
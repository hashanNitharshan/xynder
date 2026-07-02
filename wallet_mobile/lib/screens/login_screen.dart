import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'register_screen.dart';
import 'client_dashboard.dart';
import 'merchant_dashboard.dart';
import 'admin_dashboard.dart';

class _C {
  static const bg = Color(0xff0B0E11);
  static const card = Color(0xff181A20);
  static const card2 = Color(0xff111318);
  static const input = Color(0xff0F1218);
  static const border = Color(0xff2B3139);

  static const yellow = Color(0xffF0B90B);
  static const yellowDark = Color(0xffC99400);
  static const red = Color(0xffEF4444);

  static const text = Colors.white;
  static const muted = Color(0xff848E9C);

  static const gradientAccent = LinearGradient(
    colors: [yellowDark, yellow],
  );

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xff1A1D24),
      Color(0xff111318),
      Color(0xff0B0E11),
    ],
  );

  static const glow = RadialGradient(
    center: Alignment(-0.4, -0.8),
    radius: 1.4,
    colors: [
      Color(0x22F0B90B),
      Color(0x09000000),
      Color(0x00000000),
    ],
  );
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  final emailCtrl = TextEditingController();
  final passwordCtrl = TextEditingController();

  bool loading = false;
  bool hidePassword = true;

  late AnimationController anim;
  late Animation<double> fade;
  late Animation<Offset> slide;

  @override
  void initState() {
    super.initState();

    anim = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 650),
    );

    fade = CurvedAnimation(parent: anim, curve: Curves.easeOut);

    slide = Tween<Offset>(
      begin: const Offset(0, 0.06),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: anim, curve: Curves.easeOut));

    anim.forward();
  }

  @override
  void dispose() {
    anim.dispose();
    emailCtrl.dispose();
    passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> login() async {
    if (emailCtrl.text.trim().isEmpty || passwordCtrl.text.trim().isEmpty) {
      showMessage("Please enter email and password");
      return;
    }

    setState(() => loading = true);

    try {
      final data = await ApiService.login(
        emailCtrl.text.trim(),
        passwordCtrl.text.trim(),
      );

      if (!mounted) return;

      if (data["success"] == true) {
        final user = data["user"];
        final role = user["role"]?.toString().toLowerCase();

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
        showMessage(data["message"]?.toString() ?? "Login failed");
      }
    } catch (e) {
      if (mounted) showMessage("Login error: $e");
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  void showMessage(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: _C.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        content: Text(
          msg,
          style: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
    );
  }

  InputDecoration inputBox({
    required String label,
    required IconData icon,
    Widget? suffix,
  }) {
    return InputDecoration(
      labelText: label,
      prefixIcon: Icon(icon, color: _C.yellow),
      suffixIcon: suffix,
      labelStyle: const TextStyle(color: _C.muted),
      filled: true,
      fillColor: _C.input,
      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 18),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: _C.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: _C.yellow, width: 1.4),
      ),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
    );
  }

  Widget loginCard() {
    return FadeTransition(
      opacity: fade,
      child: SlideTransition(
        position: slide,
        child: Container(
          width: 460,
          padding: const EdgeInsets.all(26),
          decoration: BoxDecoration(
            gradient: _C.gradientCard,
            borderRadius: BorderRadius.circular(30),
            border: Border.all(color: _C.border),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.65),
                blurRadius: 40,
                offset: const Offset(0, 22),
              ),
            ],
          ),
          child: Stack(
            children: [
              Positioned.fill(
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(30),
                    gradient: _C.glow,
                  ),
                ),
              ),
              Column(
                children: [
                  Container(
                    width: 112,
                    height: 112,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.black.withOpacity(0.25),
                      border: Border.all(color: _C.border),
                    ),
                    child: Center(child: XynderLogo(size: 76)),
                  ),

                  const SizedBox(height: 20),

                  const Text(
                    "XYNDER",
                    style: TextStyle(
                      color: _C.yellow,
                      fontSize: 34,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 3,
                    ),
                  ),

                  const SizedBox(height: 8),

                  const Text(
                    "Welcome Back",
                    style: TextStyle(
                      color: _C.text,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),

                  const SizedBox(height: 6),

                  const Text(
                    "Login to continue your dashboard",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: _C.muted,
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                    ),
                  ),

                  const SizedBox(height: 34),

                  TextField(
                    controller: emailCtrl,
                    keyboardType: TextInputType.emailAddress,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                    ),
                    decoration: inputBox(
                      label: "Email Address",
                      icon: Icons.email_rounded,
                    ),
                  ),

                  const SizedBox(height: 16),

                  TextField(
                    controller: passwordCtrl,
                    obscureText: hidePassword,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                    ),
                    decoration: inputBox(
                      label: "Password",
                      icon: Icons.lock_rounded,
                      suffix: IconButton(
                        icon: Icon(
                          hidePassword
                              ? Icons.visibility_off_rounded
                              : Icons.visibility_rounded,
                          color: Colors.white54,
                        ),
                        onPressed: () {
                          setState(() => hidePassword = !hidePassword);
                        },
                      ),
                    ),
                  ),

                  const SizedBox(height: 28),

                  GestureDetector(
                    onTap: loading ? null : login,
                    child: Container(
                      width: double.infinity,
                      height: 56,
                      decoration: BoxDecoration(
                        gradient: _C.gradientAccent,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Center(
                        child: loading
                            ? const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2.5,
                                  color: Colors.black,
                                ),
                              )
                            : const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.login_rounded,
                                    color: Colors.black,
                                    size: 20,
                                  ),
                                  SizedBox(width: 9),
                                  Text(
                                    "Login",
                                    style: TextStyle(
                                      color: Colors.black,
                                      fontWeight: FontWeight.w900,
                                      fontSize: 16,
                                    ),
                                  ),
                                ],
                              ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 20),

                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: _C.card2,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: _C.border),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.person_add_alt_1_rounded,
                          color: _C.yellow,
                          size: 19,
                        ),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Text(
                            "New to Xynder?",
                            style: TextStyle(
                              color: Colors.white70,
                              fontWeight: FontWeight.w700,
                              fontSize: 13,
                            ),
                          ),
                        ),
                        GestureDetector(
                          onTap: loading
                              ? null
                              : () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => const RegisterScreen(),
                                    ),
                                  );
                                },
                          child: const Text(
                            "Create Account",
                            style: TextStyle(
                              color: _C.yellow,
                              fontWeight: FontWeight.w900,
                              fontSize: 13,
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
      ),
    );
  }

  Widget desktopInfoPanel() {
    return Container(
      width: 430,
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: _C.card.withOpacity(0.94),
        borderRadius: BorderRadius.circular(30),
        border: Border.all(color: _C.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            "XYNDER WALLET",
            style: TextStyle(
              color: _C.yellow,
              fontSize: 12,
              fontWeight: FontWeight.w900,
              letterSpacing: 1.5,
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            "Manage your\nXynder account\nin one place.",
            style: TextStyle(
              color: Colors.white,
              fontSize: 34,
              height: 1.15,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.6,
            ),
          ),
          const SizedBox(height: 14),
          const Text(
            "Access your dashboard, profile, requests, transfers, history and transaction chats.",
            style: TextStyle(
              color: _C.muted,
              fontSize: 14,
              height: 1.5,
            ),
          ),
          const SizedBox(height: 26),
          Row(
            children: [
              smallBox(title: "Admin", icon: Icons.admin_panel_settings_rounded),
              const SizedBox(width: 12),
              smallBox(title: "Client", icon: Icons.person_rounded),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              smallBox(title: "Merchant", icon: Icons.storefront_rounded),
              const SizedBox(width: 12),
              smallBox(title: "Dashboard", icon: Icons.dashboard_rounded),
            ],
          ),
        ],
      ),
    );
  }

  Widget smallBox({required String title, required IconData icon}) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: _C.card2,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: _C.border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: _C.yellow, size: 20),
            const SizedBox(height: 12),
            Text(
              title,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 14,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget glowCircle({
    required double size,
    required double opacity,
  }) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: _C.yellow.withOpacity(opacity),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.of(context).size.width >= 950;

    return Scaffold(
      backgroundColor: _C.bg,
      body: Stack(
        children: [
          Positioned(
            top: -120,
            left: -90,
            child: glowCircle(size: 280, opacity: 0.08),
          ),
          Positioned(
            bottom: -140,
            right: -90,
            child: glowCircle(size: 320, opacity: 0.06),
          ),
          Positioned(
            top: 130,
            right: 80,
            child: glowCircle(size: 90, opacity: 0.04),
          ),
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(22),
                child: isWide
                    ? Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          desktopInfoPanel(),
                          const SizedBox(width: 34),
                          loginCard(),
                        ],
                      )
                    : loginCard(),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class XynderLogo extends StatelessWidget {
  final double size;

  const XynderLogo({
    super.key,
    required this.size,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size,
      height: size,
      child: CustomPaint(painter: _LoginXynderLogoPainter()),
    );
  }
}

class _LoginXynderLogoPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = _C.yellow
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
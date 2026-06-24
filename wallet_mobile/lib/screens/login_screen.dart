import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'register_screen.dart';
import 'client_dashboard.dart';
import 'merchant_dashboard.dart';
import 'admin_dashboard.dart';
import 'auth_gate.dart';

class _C {
  static const bg = Color(0xff0a0a0a);
  static const surface = Color(0xff141414);
  static const surfaceAlt = Color(0xff1c1c1e);
  static const border = Color(0xff2a2a2a);

  static const orange = Color(0xffFF4500);
  static const amber = Color(0xffFFB800);
  static const gold = Color(0xffFFD700);
  static const red = Color(0xffef4444);

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

    final data = await ApiService.login(
      emailCtrl.text.trim(),
      passwordCtrl.text.trim(),
    );

    if (!mounted) return;
    setState(() => loading = false);

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
      showMessage(data["message"] ?? "Login failed");
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
      prefixIcon: Icon(icon, color: _C.amber),
      suffixIcon: suffix,
      labelStyle: const TextStyle(color: _C.textSecondary),
      filled: true,
      fillColor: _C.bg,
      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 18),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: _C.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: _C.orange, width: 1.5),
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
            borderRadius: BorderRadius.circular(32),
            border: Border.all(color: const Color(0xff3a1500)),
            boxShadow: [
              BoxShadow(
                color: _C.orange.withOpacity(0.22),
                blurRadius: 55,
                offset: const Offset(0, 24),
              ),
            ],
          ),
          child: Stack(
            children: [
              Positioned.fill(
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(32),
                    gradient: _C.gradientGlow,
                  ),
                ),
              ),
              Column(
                children: [
                  Container(
                    width: 118,
                    height: 118,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.white.withOpacity(0.05),
                      border: Border.all(color: _C.amber, width: 2),
                      boxShadow: [
                        BoxShadow(
                          color: _C.orange.withOpacity(0.25),
                          blurRadius: 34,
                        ),
                      ],
                    ),
                    child: const Center(
                      child: XynderLogo(size: 82),
                    ),
                  ),

                  const SizedBox(height: 18),

                  ShaderMask(
                    shaderCallback: (b) => _C.gradientAccent.createShader(b),
                    child: const Text(
                      "XYNDER",
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 34,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 2.8,
                      ),
                    ),
                  ),

                  const SizedBox(height: 8),

                  const Text(
                    "Welcome Back",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),

                  const SizedBox(height: 6),

                  const Text(
                    "Login to continue your dashboard",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: _C.textSecondary,
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
                        borderRadius: BorderRadius.circular(17),
                        boxShadow: [
                          BoxShadow(
                            color: _C.orange.withOpacity(0.3),
                            blurRadius: 16,
                            offset: const Offset(0, 6),
                          ),
                        ],
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
                      color: _C.surface.withOpacity(0.7),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: Colors.white.withOpacity(0.08)),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.person_add_alt_1_rounded,
                          color: _C.amber,
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
                              color: _C.amber,
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
        color: _C.surface.withOpacity(0.82),
        borderRadius: BorderRadius.circular(32),
        border: Border.all(color: _C.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ShaderMask(
            shaderCallback: (b) => _C.gradientAccent.createShader(b),
            child: const Text(
              "XYNDER WALLET",
              style: TextStyle(
                color: Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.5,
              ),
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
              color: _C.textSecondary,
              fontSize: 14,
              height: 1.5,
            ),
          ),

          const SizedBox(height: 26),

          Row(
            children: [
              smallBox(
                title: "Admin",
                icon: Icons.admin_panel_settings_rounded,
              ),
              const SizedBox(width: 12),
              smallBox(
                title: "Client",
                icon: Icons.person_rounded,
              ),
            ],
          ),

          const SizedBox(height: 12),

          Row(
            children: [
              smallBox(
                title: "Merchant",
                icon: Icons.storefront_rounded,
              ),
              const SizedBox(width: 12),
              smallBox(
                title: "Dashboard",
                icon: Icons.dashboard_rounded,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget smallBox({
    required String title,
    required IconData icon,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: _C.bg,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: _C.border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: _C.amber, size: 20),
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
        color: _C.orange.withOpacity(opacity),
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
            child: glowCircle(size: 280, opacity: 0.09),
          ),
          Positioned(
            bottom: -140,
            right: -90,
            child: glowCircle(size: 320, opacity: 0.075),
          ),
          Positioned(
            top: 130,
            right: 80,
            child: glowCircle(size: 90, opacity: 0.05),
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
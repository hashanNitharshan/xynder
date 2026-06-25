import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'client_dashboard.dart';
import '../widgets/pinwheel_loader.dart';

class _C {
  static const bg = Color(0xff0a0a0a);
  static const surface = Color(0xff141414);
  static const surfaceAlt = Color(0xff1c1c1e);
  static const border = Color(0xff2a2a2a);

  static const orange = Color(0xffFF4500);
  static const amber = Color(0xffFFB800);
  static const gold = Color(0xffFFD700);
  static const red = Color(0xffef4444);

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

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen>
    with SingleTickerProviderStateMixin {
  final nameCtrl = TextEditingController();
  final emailCtrl = TextEditingController();
  final passwordCtrl = TextEditingController();
  final confirmPasswordCtrl = TextEditingController();

  bool loading = false;
  bool _obscurePassword = true;
  bool _obscureConfirm = true;

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
  }

  @override
  void dispose() {
    _anim.dispose();
    nameCtrl.dispose();
    emailCtrl.dispose();
    passwordCtrl.dispose();
    confirmPasswordCtrl.dispose();
    super.dispose();
  }

  void snack(String msg, {bool ok = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: ok ? _C.amber : _C.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        content: Text(
          msg,
          style: TextStyle(
            color: ok ? Colors.black : Colors.white,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
    );
  }

  Future<void> register() async {
    if (loading) return;

    // Basic validation
    if (nameCtrl.text.trim().isEmpty) {
      snack("Please enter your full name");
      return;
    }
    if (emailCtrl.text.trim().isEmpty) {
      snack("Please enter your email");
      return;
    }
    if (passwordCtrl.text.isEmpty) {
      snack("Please enter a password");
      return;
    }
    if (passwordCtrl.text != confirmPasswordCtrl.text) {
      snack("Passwords do not match");
      return;
    }

    FocusScope.of(context).unfocus();
    setState(() => loading = true);

    try {
      final data = await ApiService.registerMultipart(
        fields: {
          "name": nameCtrl.text.trim(),
          "email": emailCtrl.text.trim(),
          "password": passwordCtrl.text.trim(),
          "password_confirmation": confirmPasswordCtrl.text.trim(),
        },
      );

      if (!mounted) return;

      if (data["success"] == true) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => ClientDashboard(user: data["user"]),
          ),
        );
      } else {
        String message = data["message"]?.toString() ?? "Registration failed";

        if (data["errors"] != null && data["errors"] is Map) {
          final errors = data["errors"] as Map;
          if (errors.values.isNotEmpty) {
            final first = errors.values.first;
            if (first is List && first.isNotEmpty) {
              message = first[0].toString();
            }
          }
        }

        snack(message);
      }
    } catch (e) {
      if (!mounted) return;
      snack("Error: $e");
    } finally {
      if (mounted) setState(() => loading = false);
    }
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
                  "Create Account",
                  style: TextStyle(
                    color: _C.textPrimary,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  "Join Xynder Wallet",
                  style: TextStyle(color: _C.textSecondary, fontSize: 12),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _hero() {
    return FadeTransition(
      opacity: _fade,
      child: SlideTransition(
        position: _slide,
        child: Container(
          margin: const EdgeInsets.fromLTRB(20, 20, 20, 0),
          padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 24),
          decoration: BoxDecoration(
            gradient: _C.gradientCard,
            borderRadius: BorderRadius.circular(28),
            border: Border.all(color: const Color(0xff3a1500)),
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
              Column(
                children: [
                  ShaderMask(
                    shaderCallback: (b) => _C.gradientAccent.createShader(b),
                    child: const Text(
                      "XYNDER REGISTER",
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 2,
                        fontSize: 13,
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  const Text(
                    "Create your account in seconds",
                    style: TextStyle(
                      color: _C.textSecondary,
                      fontSize: 12,
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

  Widget _input(
    String label,
    TextEditingController ctrl, {
    bool obscure = false,
    TextInputType keyboardType = TextInputType.text,
    IconData icon = Icons.edit_rounded,
    VoidCallback? onToggleObscure,
    bool? isObscured,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: TextField(
        controller: ctrl,
        obscureText: obscure,
        keyboardType: keyboardType,
        style: const TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w600,
        ),
        decoration: InputDecoration(
          prefixIcon: Icon(icon, color: _C.amber, size: 20),
          suffixIcon: onToggleObscure != null
              ? GestureDetector(
                  onTap: onToggleObscure,
                  child: Icon(
                    isObscured == true
                        ? Icons.visibility_off_rounded
                        : Icons.visibility_rounded,
                    color: _C.textSecondary,
                    size: 20,
                  ),
                )
              : null,
          labelText: label,
          labelStyle: const TextStyle(color: _C.textSecondary),
          filled: true,
          fillColor: _C.bg,
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: const BorderSide(color: _C.border),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: const BorderSide(color: _C.orange, width: 1.5),
          ),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
        ),
      ),
    );
  }

  Widget _sectionCard({
    required String title,
    required IconData icon,
    required List<Widget> children,
  }) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.fromLTRB(20, 18, 20, 0),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(26),
        border: Border.all(color: _C.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(9),
                decoration: BoxDecoration(
                  color: _C.orange.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: _C.orange, size: 18),
              ),
              const SizedBox(width: 12),
              Text(
                title,
                style: const TextStyle(
                  color: _C.textPrimary,
                  fontSize: 17,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          ...children,
        ],
      ),
    );
  }

  Widget _registerButton() {
    return GestureDetector(
      onTap: loading ? null : register,
      child: Container(
        width: double.infinity,
        height: 56,
        margin: const EdgeInsets.fromLTRB(20, 22, 20, 30),
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
              ? const PinwheelLoader(size: 22, stroke: 3, color: Colors.black)
              : const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.person_add_alt_1_rounded,
                        color: Colors.black, size: 18),
                    SizedBox(width: 8),
                    Text(
                      "Register",
                      style: TextStyle(
                        color: Colors.black,
                        fontWeight: FontWeight.w900,
                        fontSize: 15,
                      ),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 760),
              child: Column(
                children: [
                  _topBar(),
                  _hero(),

                  _sectionCard(
                    title: "Personal Details",
                    icon: Icons.person_rounded,
                    children: [
                      _input(
                        "Full Name",
                        nameCtrl,
                        icon: Icons.person_outline_rounded,
                      ),
                      _input(
                        "Email",
                        emailCtrl,
                        keyboardType: TextInputType.emailAddress,
                        icon: Icons.email_outlined,
                      ),
                    ],
                  ),

                  _sectionCard(
                    title: "Security",
                    icon: Icons.lock_rounded,
                    children: [
                      _input(
                        "Password",
                        passwordCtrl,
                        obscure: _obscurePassword,
                        icon: Icons.lock_outline_rounded,
                        onToggleObscure: () => setState(
                            () => _obscurePassword = !_obscurePassword),
                        isObscured: _obscurePassword,
                      ),
                      _input(
                        "Confirm Password",
                        confirmPasswordCtrl,
                        obscure: _obscureConfirm,
                        icon: Icons.lock_rounded,
                        onToggleObscure: () =>
                            setState(() => _obscureConfirm = !_obscureConfirm),
                        isObscured: _obscureConfirm,
                      ),
                    ],
                  ),

                  _registerButton(),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
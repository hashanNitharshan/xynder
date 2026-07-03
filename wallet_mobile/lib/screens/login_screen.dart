// login_screen.dart
import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'register_screen.dart';
import 'client_dashboard.dart';
import 'merchant_dashboard.dart';
import 'admin_dashboard.dart';

class _C {
  static const bg = Color(0xff0B0E11);
  static const field = Color(0xff1E2329);
  static const border = Color(0xff2B3139);

  static const yellow = Color(0xffF0B90B);
  static const yellowDark = Color(0xffC99400);
  static const red = Color(0xffEF4444);

  static const text = Colors.white;
  static const muted = Color(0xff848E9C);
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final emailCtrl = TextEditingController();
  final passwordCtrl = TextEditingController();

  bool loading = false;
  bool hidePassword = true;

  @override
  void dispose() {
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
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        content: Text(
          msg,
          style: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
    );
  }

  Widget fieldLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        text,
        style: const TextStyle(
          color: _C.text,
          fontSize: 15,
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }

  InputDecoration inputBox({Widget? suffix}) {
    return InputDecoration(
      suffixIcon: suffix,
      filled: true,
      fillColor: _C.field,
      contentPadding:
          const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(6),
        borderSide: const BorderSide(color: _C.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(6),
        borderSide: const BorderSide(color: _C.yellow, width: 1.2),
      ),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(6)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 40),

              // Logo
              Center(
                child: Image.asset(
                  "assets/images/bitxnow_logo.jpeg",
                  width: 120,
                  height: 120,
                  fit: BoxFit.contain,
                ),
              ),

              const SizedBox(height: 8),

              // BITXNOW wordmark
              const Center(
                child: Text(
                  "BITXNOW",
                  style: TextStyle(
                    color: _C.yellow,
                    fontSize: 30,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 4,
                  ),
                ),
              ),

              const SizedBox(height: 46),

              // Email
              fieldLabel("Email"),
              TextField(
                controller: emailCtrl,
                keyboardType: TextInputType.emailAddress,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                ),
                cursorColor: _C.yellow,
                decoration: inputBox(),
              ),

              const SizedBox(height: 22),

              // Password
              fieldLabel("Password"),
              TextField(
                controller: passwordCtrl,
                obscureText: hidePassword,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                ),
                cursorColor: _C.yellow,
                decoration: inputBox(
                  suffix: IconButton(
                    icon: Icon(
                      hidePassword
                          ? Icons.visibility_off_outlined
                          : Icons.visibility_outlined,
                      color: Colors.white54,
                    ),
                    onPressed: () {
                      setState(() => hidePassword = !hidePassword);
                    },
                  ),
                ),
              ),

              const SizedBox(height: 36),

              // Login button (outlined, Binance style)
              GestureDetector(
                onTap: loading ? null : login,
                child: Container(
                  height: 54,
                  decoration: BoxDecoration(
                    color: Colors.transparent,
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: _C.yellow, width: 1.4),
                  ),
                  child: Center(
                    child: loading
                        ? const SizedBox(
                            width: 22,
                            height: 22,
                            child: CircularProgressIndicator(
                              strokeWidth: 2.5,
                              color: _C.yellow,
                            ),
                          )
                        : const Text(
                            "Login",
                            style: TextStyle(
                              color: _C.yellow,
                              fontWeight: FontWeight.w700,
                              fontSize: 18,
                            ),
                          ),
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // Forgot password
              Center(
                child: GestureDetector(
                  onTap: () {},
                  child: const Text(
                    "Forgot your password?",
                    style: TextStyle(
                      color: _C.yellow,
                      fontWeight: FontWeight.w600,
                      fontSize: 15,
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 20),

              // Register line
              Center(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text(
                      "Don't have an account yet? ",
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w500,
                        fontSize: 15,
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
                        "Register",
                        style: TextStyle(
                          color: _C.yellow,
                          fontWeight: FontWeight.w700,
                          fontSize: 15,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }
}
// register_screen.dart
import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'login_screen.dart';
import 'client_dashboard.dart';

class _C {
  static const bg = Color(0xff0B0E11);
  static const field = Color(0xff1E2329);
  static const border = Color(0xff2B3139);

  static const yellow = Color(0xffF0B90B);
  static const red = Color(0xffEF4444);

  static const text = Colors.white;
  static const muted = Color(0xff848E9C);
}

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final nameCtrl = TextEditingController();
  final emailCtrl = TextEditingController();
  final passwordCtrl = TextEditingController();
  final confirmPasswordCtrl = TextEditingController();

  bool loading = false;
  bool hidePassword = true;
  bool hideConfirmPassword = true;

  @override
  void dispose() {
    nameCtrl.dispose();
    emailCtrl.dispose();
    passwordCtrl.dispose();
    confirmPasswordCtrl.dispose();
    super.dispose();
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

  // Adds @gmail.com or @icloud.com after the name the user typed.
  // If the user already typed another domain, it is replaced.
  void addEmailDomain(String domain) {
    var text = emailCtrl.text.trim();

    final at = text.indexOf('@');
    if (at != -1) {
      text = text.substring(0, at);
    }

    if (text.isEmpty) {
      showMessage("Type your email name first");
      return;
    }

    final newText = '$text$domain';

    emailCtrl.value = TextEditingValue(
      text: newText,
      selection: TextSelection.collapsed(offset: newText.length),
    );
  }

  Future<void> register() async {
    if (nameCtrl.text.trim().isEmpty ||
        emailCtrl.text.trim().isEmpty ||
        passwordCtrl.text.trim().isEmpty ||
        confirmPasswordCtrl.text.trim().isEmpty) {
      showMessage("Please fill all fields");
      return;
    }

    if (passwordCtrl.text.trim() != confirmPasswordCtrl.text.trim()) {
      showMessage("Passwords do not match");
      return;
    }

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
              message = first.first.toString();
            }
          }
        }

        showMessage(message);
      }
    } catch (e) {
      if (mounted) showMessage("Register error: $e");
    } finally {
      if (mounted) setState(() => loading = false);
    }
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
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
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

  // Small button that adds an email domain (Gmail or iCloud).
  Widget domainButton({
    required String label,
    required String domain,
    required IconData icon,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: loading ? null : () => addEmailDomain(domain),
        child: Container(
          height: 42,
          decoration: BoxDecoration(
            color: _C.field,
            borderRadius: BorderRadius.circular(6),
            border: Border.all(color: _C.border),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 18, color: _C.yellow),
              const SizedBox(width: 8),
              Text(
                label,
                style: const TextStyle(
                  color: _C.text,
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
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
      appBar: AppBar(
        backgroundColor: _C.bg,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.close, color: Colors.white, size: 26),
          onPressed: () {
            if (Navigator.canPop(context)) Navigator.pop(context);
          },
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 24),

              Center(
                child: Image.asset(
                  "assets/images/bitxnow_logo.jpeg",
                  width: 120,
                  height: 120,
                  fit: BoxFit.contain,
                ),
              ),

              const SizedBox(height: 8),

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

              const SizedBox(height: 40),

              fieldLabel("Full Name"),
              TextField(
                controller: nameCtrl,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                ),
                cursorColor: _C.yellow,
                decoration: inputBox(),
              ),

              const SizedBox(height: 20),

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

              const SizedBox(height: 10),

              Row(
                children: [
                  domainButton(
                    label: "Gmail",
                    domain: "@gmail.com",
                    icon: Icons.mail_outline,
                  ),
                  const SizedBox(width: 10),
                  domainButton(
                    label: "iCloud",
                    domain: "@icloud.com",
                    icon: Icons.cloud_outlined,
                  ),
                ],
              ),

              const SizedBox(height: 20),

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

              const SizedBox(height: 20),

              fieldLabel("Confirm Password"),
              TextField(
                controller: confirmPasswordCtrl,
                obscureText: hideConfirmPassword,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                ),
                cursorColor: _C.yellow,
                decoration: inputBox(
                  suffix: IconButton(
                    icon: Icon(
                      hideConfirmPassword
                          ? Icons.visibility_off_outlined
                          : Icons.visibility_outlined,
                      color: Colors.white54,
                    ),
                    onPressed: () {
                      setState(() {
                        hideConfirmPassword = !hideConfirmPassword;
                      });
                    },
                  ),
                ),
              ),

              const SizedBox(height: 36),

              GestureDetector(
                onTap: loading ? null : register,
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
                            "Register",
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

              Center(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text(
                      "Already have an account? ",
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
                              Navigator.pushReplacement(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const LoginScreen(),
                                ),
                              );
                            },
                      child: const Text(
                        "Login",
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
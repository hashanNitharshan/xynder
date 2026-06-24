import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

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
  final originalNameCtrl = TextEditingController();
  final emailCtrl = TextEditingController();
  final phoneCtrl = TextEditingController();
  final countryCtrl = TextEditingController();
  final stateCtrl = TextEditingController();
  final addressCtrl = TextEditingController();
  final bankNameCtrl = TextEditingController();
  final branchCtrl = TextEditingController();
  final accountCtrl = TextEditingController();
  final accountTypeCtrl = TextEditingController();
  final ifscCtrl = TextEditingController();
  final upiNameCtrl = TextEditingController();
  final upiIdCtrl = TextEditingController();
  final passwordCtrl = TextEditingController();

  bool loading = false;

  XFile? photo;
  XFile? aadhaarPhoto;
  XFile? upiQr;

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
    originalNameCtrl.dispose();
    emailCtrl.dispose();
    phoneCtrl.dispose();
    countryCtrl.dispose();
    stateCtrl.dispose();
    addressCtrl.dispose();
    bankNameCtrl.dispose();
    branchCtrl.dispose();
    accountCtrl.dispose();
    accountTypeCtrl.dispose();
    ifscCtrl.dispose();
    upiNameCtrl.dispose();
    upiIdCtrl.dispose();
    passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> pickPhoto() async {
    final img = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
    );
    if (img != null) setState(() => photo = img);
  }

  Future<void> pickAadhaarPhoto() async {
    final img = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
    );
    if (img != null) setState(() => aadhaarPhoto = img);
  }

  Future<void> pickQr() async {
    final img = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
    );
    if (img != null) setState(() => upiQr = img);
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

    FocusScope.of(context).unfocus();
    setState(() => loading = true);

    try {
      final data = await ApiService.registerMultipart(
        fields: {
          "name": nameCtrl.text.trim(),
          "original_name": originalNameCtrl.text.trim(),
          "email": emailCtrl.text.trim(),
          "phone": phoneCtrl.text.trim(),
          "country": countryCtrl.text.trim(),
          "state": stateCtrl.text.trim(),
          "address": addressCtrl.text.trim(),

          // Aadhaar card number removed
          // Do not send "aadhaar"

          "bank_name": bankNameCtrl.text.trim(),
          "branch": branchCtrl.text.trim(),
          "account_number": accountCtrl.text.trim(),
          "account_type": accountTypeCtrl.text.trim(),
          "ifsc": ifscCtrl.text.trim(),
          "upi_name": upiNameCtrl.text.trim(),
          "upi_id": upiIdCtrl.text.trim(),
          "password": passwordCtrl.text.trim(),
        },
        photo: photo,
        aadhaarPhoto: aadhaarPhoto,
        upiQr: upiQr,
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
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 12,
                  ),
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
          padding: const EdgeInsets.all(24),
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
                  const SizedBox(height: 22),
                  GestureDetector(
                    onTap: loading ? null : pickPhoto,
                    child: Stack(
                      alignment: Alignment.bottomRight,
                      children: [
                        Container(
                          width: 116,
                          height: 116,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white.withOpacity(0.05),
                            border: Border.all(color: _C.amber, width: 2),
                          ),
                          child: CircleAvatar(
                            radius: 56,
                            backgroundColor: _C.surfaceAlt,
                            child: Icon(
                              photo == null
                                  ? Icons.camera_alt_rounded
                                  : Icons.check_circle_rounded,
                              color: _C.amber,
                              size: 48,
                            ),
                          ),
                        ),
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            gradient: _C.gradientAccent,
                            shape: BoxShape.circle,
                            border: Border.all(color: _C.bg, width: 3),
                          ),
                          child: const Icon(
                            Icons.add_a_photo_rounded,
                            color: Colors.black,
                            size: 17,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    photo == null ? "Tap to add profile photo" : "Photo selected",
                    style: const TextStyle(
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

  Widget input(
    String label,
    TextEditingController ctrl, {
    bool obscure = false,
    TextInputType keyboardType = TextInputType.text,
    IconData icon = Icons.edit_rounded,
    int maxLines = 1,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: TextField(
        controller: ctrl,
        obscureText: obscure,
        keyboardType: keyboardType,
        maxLines: maxLines,
        style: const TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w600,
        ),
        decoration: InputDecoration(
          prefixIcon: Icon(icon, color: _C.amber, size: 20),
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

  Widget _uploadButton({
    required String title,
    required IconData icon,
    required VoidCallback onTap,
    required bool selected,
  }) {
    final color = selected ? _C.amber : _C.orange;

    return GestureDetector(
      onTap: loading ? null : onTap,
      child: Container(
        width: double.infinity,
        height: 52,
        margin: const EdgeInsets.only(bottom: 14),
        decoration: BoxDecoration(
          color: _C.bg,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: color, width: 1.3),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              selected ? Icons.check_circle_rounded : icon,
              color: color,
              size: 20,
            ),
            const SizedBox(width: 9),
            Flexible(
              child: Text(
                title,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.w900,
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),
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
              ? const PinwheelLoader(
                  size: 22,
                  stroke: 3,
                  color: Colors.black,
                )
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
                      input("Full Name", nameCtrl,
                          icon: Icons.person_outline_rounded),
                      input("Original / Legal Name", originalNameCtrl,
                          icon: Icons.badge_outlined),
                      input("Email", emailCtrl,
                          keyboardType: TextInputType.emailAddress,
                          icon: Icons.email_outlined),
                      input("Phone Number", phoneCtrl,
                          keyboardType: TextInputType.phone,
                          icon: Icons.phone_rounded),
                      input("Country", countryCtrl,
                          icon: Icons.public_rounded),
                      input("State", stateCtrl,
                          icon: Icons.location_city_rounded),
                      input("Address", addressCtrl,
                          icon: Icons.location_on_rounded,
                          maxLines: 3),
                      _uploadButton(
                        title: aadhaarPhoto == null
                            ? "Upload Aadhaar Card Photo"
                            : "Aadhaar Photo Selected",
                        icon: Icons.credit_card_rounded,
                        onTap: pickAadhaarPhoto,
                        selected: aadhaarPhoto != null,
                      ),
                    ],
                  ),

                  _sectionCard(
                    title: "Bank Details",
                    icon: Icons.account_balance_rounded,
                    children: [
                      input("Bank Name", bankNameCtrl,
                          icon: Icons.account_balance_rounded),
                      input("Branch", branchCtrl,
                          icon: Icons.location_city_rounded),
                      input("Account Number", accountCtrl,
                          icon: Icons.credit_card_rounded),
                      input("Account Type", accountTypeCtrl,
                          icon: Icons.category_rounded),
                      input("IFSC", ifscCtrl, icon: Icons.code_rounded),
                    ],
                  ),

                  _sectionCard(
                    title: "UPI Details",
                    icon: Icons.qr_code_rounded,
                    children: [
                      input("UPI Account Name", upiNameCtrl,
                          icon: Icons.account_circle_rounded),
                      input("UPI ID", upiIdCtrl,
                          icon: Icons.link_rounded),
                      _uploadButton(
                        title: upiQr == null
                            ? "Upload UPI QR"
                            : "UPI QR Selected",
                        icon: Icons.qr_code_2_rounded,
                        onTap: pickQr,
                        selected: upiQr != null,
                      ),
                    ],
                  ),

                  _sectionCard(
                    title: "Security",
                    icon: Icons.lock_rounded,
                    children: [
                      input(
                        "Password",
                        passwordCtrl,
                        obscure: true,
                        icon: Icons.lock_outline_rounded,
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
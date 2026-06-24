import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';
import '../widgets/pinwheel_loader.dart';

// ─────────────────────────────────────────────────────────────
//  DESIGN TOKENS - SAME AS request_screen.dart
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

  static const red = Color(0xffef4444);
  static const blue = Color(0xff3b82f6);

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

class ProfileScreen extends StatefulWidget {
  final Map user;

  const ProfileScreen({super.key, required this.user});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen>
    with SingleTickerProviderStateMixin {
  late Map user;

  late TextEditingController nameCtrl;
  late TextEditingController originalNameCtrl;
  late TextEditingController phoneCtrl;
  late TextEditingController countryCtrl;
  late TextEditingController stateCtrl;
  late TextEditingController addressCtrl;
  late TextEditingController bankNameCtrl;
  late TextEditingController branchCtrl;
  late TextEditingController accountCtrl;
  late TextEditingController accountTypeCtrl;
  late TextEditingController ifscCtrl;
  late TextEditingController upiNameCtrl;
  late TextEditingController upiIdCtrl;

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
    user = widget.user;
    setControllers();

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

  void setControllers() {
    nameCtrl = TextEditingController(text: user["name"]?.toString() ?? "");
    originalNameCtrl =
        TextEditingController(text: user["original_name"]?.toString() ?? "");
    phoneCtrl = TextEditingController(text: user["phone"]?.toString() ?? "");
    countryCtrl = TextEditingController(text: user["country"]?.toString() ?? "");
    stateCtrl = TextEditingController(text: user["state"]?.toString() ?? "");
    addressCtrl = TextEditingController(text: user["address"]?.toString() ?? "");
    bankNameCtrl =
        TextEditingController(text: user["bank_name"]?.toString() ?? "");
    branchCtrl = TextEditingController(text: user["branch"]?.toString() ?? "");
    accountCtrl =
        TextEditingController(text: user["account_number"]?.toString() ?? "");
    accountTypeCtrl =
        TextEditingController(text: user["account_type"]?.toString() ?? "");
    ifscCtrl = TextEditingController(text: user["ifsc"]?.toString() ?? "");
    upiNameCtrl =
        TextEditingController(text: user["upi_name"]?.toString() ?? "");
    upiIdCtrl = TextEditingController(text: user["upi_id"]?.toString() ?? "");
  }

  @override
  void dispose() {
    _anim.dispose();
    nameCtrl.dispose();
    originalNameCtrl.dispose();
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
    super.dispose();
  }

  bool get isVerified {
    return user["is_verified"] == true ||
        user["is_verified"] == 1 ||
        user["is_verified"]?.toString() == "1";
  }

  Future<void> pickPhoto() async {
    final img = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 65,
    );

    if (img != null) {
      setState(() => photo = img);
    }
  }

  Future<void> pickAadhaarPhoto() async {
    final img = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 65,
    );

    if (img != null) {
      setState(() => aadhaarPhoto = img);
    }
  }

  Future<void> pickQr() async {
    final img = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 65,
    );

    if (img != null) {
      setState(() => upiQr = img);
    }
  }

  Future<void> saveProfile() async {
    if (loading) return;

    FocusScope.of(context).unfocus();
    setState(() => loading = true);

    try {
      final data = await ApiService.updateProfileMultipart(
        fields: {
          "name": nameCtrl.text.trim(),
          "email": user["email"]?.toString() ?? "",
          "original_name": originalNameCtrl.text.trim(),
          "phone": phoneCtrl.text.trim(),
          "country": countryCtrl.text.trim(),
          "state": stateCtrl.text.trim(),
          "address": addressCtrl.text.trim(),
          "bank_name": bankNameCtrl.text.trim(),
          "branch": branchCtrl.text.trim(),
          "account_number": accountCtrl.text.trim(),
          "account_type": accountTypeCtrl.text.trim(),
          "ifsc": ifscCtrl.text.trim(),
          "upi_name": upiNameCtrl.text.trim(),
          "upi_id": upiIdCtrl.text.trim(),
        },
        photo: photo,
        aadhaarPhoto: aadhaarPhoto,
        upiQr: upiQr,
      );

      if (!mounted) return;

      if (data["success"] == true) {
        setState(() {
          user = Map<String, dynamic>.from(data["user"] ?? user);
          photo = null;
          aadhaarPhoto = null;
          upiQr = null;
        });

        _snack("Profile updated successfully");
      } else {
        _snack(
          data["message"]?.toString() ?? "Profile update failed",
          ok: false,
        );
      }
    } catch (e) {
      if (!mounted) return;
      _snack("Error: $e", ok: false);
    } finally {
      if (mounted) {
        setState(() => loading = false);
      }
    }
  }

  void _snack(String msg, {bool ok = true}) {
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

  // ═══════════════════════════════════════════
  //  TOP BAR
  // ═══════════════════════════════════════════
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
                  "Profile",
                  style: TextStyle(
                    color: _C.textPrimary,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.3,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  "Manage personal and payment details",
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
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
  //  PROFILE HERO
  // ═══════════════════════════════════════════
  Widget _profileHero() {
    final role = user["role"]?.toString() ?? "";
    final email = user["email"]?.toString() ?? "";
    final name = user["name"]?.toString() ?? "User";
    final photoUrl = ApiService.fixUrl(user["photo_url"]);

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
              Positioned(
                top: -50,
                right: -50,
                child: Container(
                  width: 160,
                  height: 160,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: _C.orange.withOpacity(0.05),
                  ),
                ),
              ),
              Column(
                children: [
                  Row(
                    children: [
                      ShaderMask(
                        shaderCallback: (b) => _C.gradientAccent.createShader(b),
                        child: const Text(
                          "XYNDER PROFILE",
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 2,
                            fontSize: 13,
                          ),
                        ),
                      ),
                      const Spacer(),
                      _statusChip(
                        icon: isVerified
                            ? Icons.verified_rounded
                            : Icons.warning_amber_rounded,
                        text: isVerified ? "VERIFIED" : "UNVERIFIED",
                        active: isVerified,
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
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
                            boxShadow: [
                              BoxShadow(
                                color: _C.orange.withOpacity(0.22),
                                blurRadius: 32,
                              ),
                            ],
                          ),
                          child: CircleAvatar(
                            radius: 56,
                            backgroundColor: _C.surfaceAlt,
                            backgroundImage: photo == null && photoUrl.isNotEmpty
                                ? NetworkImage(photoUrl)
                                : null,
                            child: photo != null
                                ? const Icon(
                                    Icons.check_circle_rounded,
                                    color: _C.amber,
                                    size: 48,
                                  )
                                : photoUrl.isEmpty
                                    ? const Icon(
                                        Icons.person_rounded,
                                        color: _C.amber,
                                        size: 50,
                                      )
                                    : null,
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
                            Icons.camera_alt_rounded,
                            color: Colors.black,
                            size: 18,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    name,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: _C.textPrimary,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    email,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: _C.textSecondary,
                      fontSize: 12,
                    ),
                  ),
                  const SizedBox(height: 14),
                  Wrap(
                    alignment: WrapAlignment.center,
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      _statusChip(
                        icon: Icons.badge_rounded,
                        text: role.toUpperCase(),
                        active: true,
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 13,
                          vertical: 8,
                        ),
                        decoration: BoxDecoration(
                          color: _C.surface.withOpacity(0.6),
                          borderRadius: BorderRadius.circular(30),
                          border: Border.all(color: Colors.white.withOpacity(0.08)),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.camera_alt_rounded,
                              color: _C.textSecondary,
                              size: 15,
                            ),
                            SizedBox(width: 6),
                            Text(
                              "TAP PHOTO TO CHANGE",
                              style: TextStyle(
                                color: _C.textSecondary,
                                fontSize: 10,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _statusChip({
    required IconData icon,
    required String text,
    required bool active,
  }) {
    final color = active ? _C.amber : _C.red;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(30),
        border: Border.all(color: color.withOpacity(0.35)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: color, size: 15),
          const SizedBox(width: 6),
          Text(
            text,
            style: TextStyle(
              color: color,
              fontSize: 10,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.3,
            ),
          ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  SECTION CARD
  // ═══════════════════════════════════════════
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
          _sectionHeader(title, icon),
          const SizedBox(height: 20),
          ...children,
        ],
      ),
    );
  }

  Widget _sectionHeader(String title, IconData icon) {
    return Row(
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
    );
  }

  Widget _field({
    required String label,
    required TextEditingController controller,
    required IconData icon,
    Color iconColor = _C.amber,
    int maxLines = 1,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: TextField(
        controller: controller,
        maxLines: maxLines,
        style: const TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w600,
        ),
        decoration: InputDecoration(
          prefixIcon: Icon(icon, color: iconColor, size: 20),
          labelText: label,
          labelStyle: const TextStyle(
            color: _C.textSecondary,
            fontSize: 14,
          ),
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

  Widget _selectedBox(String text) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(13),
      decoration: BoxDecoration(
        color: _C.amber.withOpacity(0.10),
        borderRadius: BorderRadius.circular(15),
        border: Border.all(color: _C.amber.withOpacity(0.30)),
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle_rounded, color: _C.amber, size: 18),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                color: _C.amber,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _imagePreview({
    required String url,
    required String errorText,
  }) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: _C.bg,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _C.border),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: Image.network(
          url,
          height: 150,
          fit: BoxFit.contain,
          errorBuilder: (_, __, ___) {
            return Padding(
              padding: const EdgeInsets.all(18),
              child: Text(
                errorText,
                textAlign: TextAlign.center,
                style: const TextStyle(color: _C.textSecondary),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _gradientBtn({
    required String label,
    required VoidCallback onTap,
    bool loading = false,
    IconData? icon,
  }) {
    return GestureDetector(
      onTap: loading ? null : onTap,
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
              : Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (icon != null) ...[
                      Icon(icon, color: Colors.black, size: 18),
                      const SizedBox(width: 8),
                    ],
                    Text(
                      label,
                      style: const TextStyle(
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
    final aadhaarPhotoUrl = ApiService.fixUrl(user["aadhaar_photo_url"]);
    final qrUrl = ApiService.fixUrl(user["upi_qr_url"]);

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
                  _profileHero(),

                  _sectionCard(
                    title: "Personal Details",
                    icon: Icons.person_rounded,
                    children: [
                      _field(
                        label: "Full Name",
                        controller: nameCtrl,
                        icon: Icons.person_outline_rounded,
                      ),
                      _field(
                        label: "Original Name / Legal Name",
                        controller: originalNameCtrl,
                        icon: Icons.badge_outlined,
                      ),
                      _field(
                        label: "Phone",
                        controller: phoneCtrl,
                        icon: Icons.phone_rounded,
                      ),
                      _field(
                        label: "Country",
                        controller: countryCtrl,
                        icon: Icons.public_rounded,
                      ),
                      _field(
                        label: "State",
                        controller: stateCtrl,
                        icon: Icons.location_city_rounded,
                      ),
                      _field(
                        label: "Address",
                        controller: addressCtrl,
                        icon: Icons.location_on_rounded,
                        maxLines: 3,
                      ),
                      if (aadhaarPhotoUrl.isNotEmpty && aadhaarPhoto == null)
                        _imagePreview(
                          url: aadhaarPhotoUrl,
                          errorText: "Aadhaar card photo not available",
                        ),
                      if (aadhaarPhoto != null)
                        _selectedBox("New Aadhaar card photo selected"),
                      _uploadButton(
                        title: aadhaarPhoto == null
                            ? "Upload / Change Aadhaar Card Photo"
                            : "New Aadhaar Card Photo Selected",
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
                      _field(
                        label: "Bank Name",
                        controller: bankNameCtrl,
                        icon: Icons.account_balance_rounded,
                      ),
                      _field(
                        label: "Branch",
                        controller: branchCtrl,
                        icon: Icons.location_city_rounded,
                      ),
                      _field(
                        label: "Account Number",
                        controller: accountCtrl,
                        icon: Icons.credit_card_rounded,
                      ),
                      _field(
                        label: "Account Type",
                        controller: accountTypeCtrl,
                        icon: Icons.category_rounded,
                      ),
                      _field(
                        label: "IFSC",
                        controller: ifscCtrl,
                        icon: Icons.code_rounded,
                      ),
                    ],
                  ),

                  _sectionCard(
                    title: "UPI Details",
                    icon: Icons.qr_code_rounded,
                    children: [
                      _field(
                        label: "UPI Account Name",
                        controller: upiNameCtrl,
                        icon: Icons.account_circle_rounded,
                      ),
                      _field(
                        label: "UPI ID",
                        controller: upiIdCtrl,
                        icon: Icons.link_rounded,
                      ),
                      if (qrUrl.isNotEmpty && upiQr == null)
                        _imagePreview(
                          url: qrUrl,
                          errorText: "QR image not available",
                        ),
                      if (upiQr != null) _selectedBox("New QR selected"),
                      _uploadButton(
                        title: upiQr == null
                            ? "Upload / Change UPI QR"
                            : "New UPI QR Selected",
                        icon: Icons.qr_code_2_rounded,
                        onTap: pickQr,
                        selected: upiQr != null,
                      ),
                    ],
                  ),

                  const SizedBox(height: 22),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: _gradientBtn(
                      label: loading ? "Saving..." : "Save Profile",
                      icon: Icons.save_rounded,
                      loading: loading,
                      onTap: saveProfile,
                    ),
                  ),
                  const SizedBox(height: 30),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
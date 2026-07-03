import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';
import '../widgets/pinwheel_loader.dart';
import '../widgets/top_bar.dart';

// ─────────────────────────────────────────────────────────────
//  DESIGN TOKENS - BitXnow (Bright Yellow / Black)
// ─────────────────────────────────────────────────────────────
class _C {
  static const bg = Color(0xff0B0E11);
  static const surface = Color(0xff181A20);
  static const surfaceAlt = Color(0xff1E2329);
  static const border = Color(0xff2B3139);
  static const borderFaint = Color(0xff202020);

  // Theme (BitXnow bright yellow)
  static const orange = Color(0xffF0B90B);
  static const amber = Color(0xffC99400);
  static const gold = Color(0xffFFD45A);

  static const red = Color(0xffef4444);
  static const blue = Color(0xffF0B90B);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff848E9C);

  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [Color(0xffC99400), Color(0xffF0B90B), Color(0xffFFD45A)],
  );

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xff181A20), Color(0xff11151B), Color(0xff0B0E11)],
  );

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.2, -0.6),
    radius: 1.2,
    colors: [Color(0x55F0B90B), Color(0x22FFD45A), Color(0x00000000)],
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

  String get _role => user["role"]?.toString() ?? "user";

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
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  NORMAL PROFILE PHOTO UPLOAD SECTION
  // ═══════════════════════════════════════════
  Widget _profilePhotoSection() {
    final photoUrl = ApiService.fixUrl(user["photo_url"]);

    return _sectionCard(
      title: "Profile Photo",
      icon: Icons.image_rounded,
      children: [
        if (photoUrl.isNotEmpty && photo == null)
          Container(
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
                photoUrl,
                height: 160,
                fit: BoxFit.contain,
                errorBuilder: (_, __, ___) {
                  return const Padding(
                    padding: EdgeInsets.all(18),
                    child: Text(
                      "Profile photo not available",
                      textAlign: TextAlign.center,
                      style: TextStyle(color: _C.textSecondary),
                    ),
                  );
                },
              ),
            ),
          ),
        if (photo != null) _selectedBox("New profile photo selected"),
        _uploadButton(
          title: photo == null
              ? "Upload / Change Profile Photo"
              : "New Profile Photo Selected",
          icon: Icons.photo_camera_rounded,
          onTap: pickPhoto,
          selected: photo != null,
        ),
      ],
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
          child: Icon(icon, color: _C.gold, size: 18),
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
    final color = selected ? _C.gold : _C.orange;

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
        color: _C.gold.withOpacity(0.10),
        borderRadius: BorderRadius.circular(15),
        border: Border.all(color: _C.gold.withOpacity(0.30)),
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle_rounded, color: _C.gold, size: 18),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                color: _C.gold,
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
      appBar: TopBar(
        title: "Profile",
        user: user,
        role: _role,
        showBack: true,
        backToRoot: false,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 760),
              child: FadeTransition(
                opacity: _fade,
                child: SlideTransition(
                  position: _slide,
                  child: Column(
                    children: [
                      const SizedBox(height: 4),
                      _profilePhotoSection(),

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
        ),
      ),
    );
  }
}
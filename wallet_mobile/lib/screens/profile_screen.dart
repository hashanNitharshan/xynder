import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../services/api_service.dart';
import '../widgets/top_bar.dart';

class _C {
  static const bg = Color(0xff0B0E11);
  static const surface = Color(0xff181A20);
  static const surfaceAlt = Color(0xff1E2329);
  static const border = Color(0xff2B3139);

  static const orange = Color(0xffF0B90B);
  static const amber = Color(0xffC99400);
  static const gold = Color(0xffFFD45A);
  static const red = Color(0xffef4444);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff848E9C);

  static const gradientAccent = LinearGradient(
    colors: [Color(0xffC99400), Color(0xffF0B90B), Color(0xffFFD45A)],
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

  bool loading = false;

  XFile? photo;
  XFile? aadhaarPhoto;

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
        },
        photo: photo,
        aadhaarPhoto: aadhaarPhoto,
      );

      if (!mounted) return;

      if (data["success"] == true) {
        setState(() {
          user = Map<String, dynamic>.from(data["user"] ?? user);
          photo = null;
          aadhaarPhoto = null;
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
          prefixIcon: Icon(icon, color: _C.amber, size: 20),
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

  @override
  Widget build(BuildContext context) {
    final aadhaarPhotoUrl = ApiService.fixUrl(user["aadhaar_photo_url"]);

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
                          if (aadhaarPhotoUrl.isNotEmpty &&
                              aadhaarPhoto == null)
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
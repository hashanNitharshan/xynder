import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../services/api_service.dart';

class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff121214);
  static const divider = Color(0xff242428);

  static const orange = Color(0xffFF9F2E);
  static const green = Color(0xff00C076);
  static const red = Color(0xffF6465D);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff77777F);
  static const textMuted = Color(0xff55555C);
}

class ProfileScreen extends StatefulWidget {
  final Map user;

  const ProfileScreen({
    super.key,
    required this.user,
  });

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
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

  @override
  void initState() {
    super.initState();

    user = widget.user;
    _setControllers();
  }

  void _setControllers() {
    nameCtrl = TextEditingController(
      text: user["name"]?.toString() ?? "",
    );

    originalNameCtrl = TextEditingController(
      text: user["original_name"]?.toString() ?? "",
    );

    phoneCtrl = TextEditingController(
      text: user["phone"]?.toString() ?? "",
    );

    countryCtrl = TextEditingController(
      text: user["country"]?.toString() ?? "",
    );

    stateCtrl = TextEditingController(
      text: user["state"]?.toString() ?? "",
    );

    addressCtrl = TextEditingController(
      text: user["address"]?.toString() ?? "",
    );
  }

  @override
  void dispose() {
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

  Future<void> pickPhoto() async {
    final image = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
    );

    if (image != null && mounted) {
      setState(() {
        photo = image;
      });
    }
  }

  Future<void> pickAadhaarPhoto() async {
    final image = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
    );

    if (image != null && mounted) {
      setState(() {
        aadhaarPhoto = image;
      });
    }
  }

  Future<void> saveProfile() async {
    if (loading) return;

    FocusScope.of(context).unfocus();

    setState(() {
      loading = true;
    });

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
          user = Map<String, dynamic>.from(
            data["user"] ?? user,
          );

          photo = null;
          aadhaarPhoto = null;
        });

        _showSnack("Profile updated successfully");
      } else {
        _showSnack(
          data["message"]?.toString() ??
              "Profile update failed",
          success: false,
        );
      }
    } catch (_) {
      if (!mounted) return;

      _showSnack(
        "Profile update failed",
        success: false,
      );
    } finally {
      if (mounted) {
        setState(() {
          loading = false;
        });
      }
    }
  }

  void _showSnack(
    String message, {
    bool success = true,
  }) {
    if (!mounted) return;

    final messenger = ScaffoldMessenger.of(context);

    messenger
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          backgroundColor: success ? _C.green : _C.red,
          behavior: SnackBarBehavior.floating,
          margin: const EdgeInsets.fromLTRB(18, 0, 18, 18),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          content: Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 13,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      );
  }

  Widget _topBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(8, 10, 8, 6),
      child: Row(
        children: [
          SizedBox(
            width: 44,
            child: IconButton(
              onPressed: () => Navigator.pop(context),
              icon: const Icon(
                Icons.arrow_back_rounded,
                color: _C.textPrimary,
                size: 21,
              ),
            ),
          ),
          const Expanded(
            child: Text(
              "Profile",
              textAlign: TextAlign.center,
              style: TextStyle(
                color: _C.textPrimary,
                fontSize: 16,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(width: 44),
        ],
      ),
    );
  }

  Widget _profileHeader() {
    final photoUrl = ApiService.fixUrl(
      user["photo_url"],
    );

    final name =
        user["name"]?.toString().trim().isNotEmpty == true
            ? user["name"].toString()
            : "User";

    final email =
        user["email"]?.toString().trim() ?? "";

    return Padding(
      padding: const EdgeInsets.fromLTRB(
        18,
        14,
        18,
        20,
      ),
      child: Column(
        children: [
          GestureDetector(
            onTap: loading ? null : pickPhoto,
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  width: 86,
                  height: 86,
                  padding: const EdgeInsets.all(3),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: _C.orange,
                      width: 1.5,
                    ),
                  ),
                  child: CircleAvatar(
                    backgroundColor: _C.surface,
                    backgroundImage: photoUrl.isNotEmpty
                        ? NetworkImage(photoUrl)
                        : null,
                    child: photoUrl.isEmpty
                        ? const Icon(
                            Icons.person_rounded,
                            color: _C.orange,
                            size: 39,
                          )
                        : null,
                  ),
                ),
                Positioned(
                  right: 0,
                  bottom: 2,
                  child: Container(
                    width: 25,
                    height: 25,
                    decoration: BoxDecoration(
                      color: _C.orange,
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: _C.bg,
                        width: 2.5,
                      ),
                    ),
                    child: const Icon(
                      Icons.edit_rounded,
                      color: Colors.black,
                      size: 13,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 11),
          Text(
            name,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: _C.textPrimary,
              fontSize: 17,
              fontWeight: FontWeight.w800,
            ),
          ),
          if (email.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              email,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: _C.textSecondary,
                fontSize: 11.5,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                isVerified
                    ? Icons.verified_rounded
                    : Icons.warning_amber_rounded,
                color: isVerified
                    ? _C.green
                    : _C.red,
                size: 15,
              ),
              const SizedBox(width: 5),
              Text(
                isVerified
                    ? "Verified"
                    : "Unverified",
                style: TextStyle(
                  color: isVerified
                      ? _C.green
                      : _C.red,
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _editableRow({
    required String label,
    required IconData icon,
    required TextEditingController controller,
    TextInputType? keyboardType,
    int maxLines = 1,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(
        vertical: 12,
      ),
      decoration: const BoxDecoration(
        border: Border(
          bottom: BorderSide(
            color: _C.divider,
            width: 0.7,
          ),
        ),
      ),
      child: Row(
        crossAxisAlignment: maxLines > 1
            ? CrossAxisAlignment.start
            : CrossAxisAlignment.center,
        children: [
          SizedBox(
            width: 28,
            child: Icon(
              icon,
              color: _C.textPrimary,
              size: 18,
            ),
          ),
          const SizedBox(width: 7),
          SizedBox(
            width: 105,
            child: Text(
              label,
              style: const TextStyle(
                color: _C.textPrimary,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            child: TextField(
              controller: controller,
              keyboardType: keyboardType,
              maxLines: maxLines,
              textAlign: TextAlign.right,
              cursorColor: _C.orange,
              style: const TextStyle(
                color: _C.textSecondary,
                fontSize: 12.5,
                fontWeight: FontWeight.w500,
              ),
              decoration: const InputDecoration(
                isDense: true,
                border: InputBorder.none,
                enabledBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                contentPadding: EdgeInsets.symmetric(
                  vertical: 3,
                ),
              ),
            ),
          ),
          const SizedBox(width: 5),
          const Icon(
            Icons.chevron_right_rounded,
            color: _C.textSecondary,
            size: 18,
          ),
        ],
      ),
    );
  }

  Widget _actionRow({
    required String label,
    required String value,
    required IconData icon,
    required VoidCallback onTap,
    Color valueColor = _C.textSecondary,
  }) {
    return InkWell(
      onTap: onTap,
      splashColor: Colors.white.withOpacity(0.03),
      highlightColor: Colors.white.withOpacity(0.015),
      child: Container(
        padding: const EdgeInsets.symmetric(
          vertical: 14,
        ),
        decoration: const BoxDecoration(
          border: Border(
            bottom: BorderSide(
              color: _C.divider,
              width: 0.7,
            ),
          ),
        ),
        child: Row(
          children: [
            SizedBox(
              width: 28,
              child: Icon(
                icon,
                color: _C.textPrimary,
                size: 18,
              ),
            ),
            const SizedBox(width: 7),
            Expanded(
              child: Text(
                label,
                style: const TextStyle(
                  color: _C.textPrimary,
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            Flexible(
              child: Text(
                value,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.right,
                style: TextStyle(
                  color: valueColor,
                  fontSize: 11.5,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            const SizedBox(width: 5),
            const Icon(
              Icons.chevron_right_rounded,
              color: _C.textSecondary,
              size: 18,
            ),
          ],
        ),
      ),
    );
  }

  Widget _profileDetails() {
    final aadhaarUrl = ApiService.fixUrl(
      user["aadhaar_photo_url"],
    );

    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: 10,
      ),
      child: Column(
        children: [
          _editableRow(
            label: "Full Name",
            icon: Icons.person_outline_rounded,
            controller: nameCtrl,
          ),
          _editableRow(
            label: "Legal Name",
            icon: Icons.badge_outlined,
            controller: originalNameCtrl,
          ),
          _editableRow(
            label: "Phone",
            icon: Icons.phone_outlined,
            controller: phoneCtrl,
            keyboardType: TextInputType.phone,
          ),
          _editableRow(
            label: "Country",
            icon: Icons.public_rounded,
            controller: countryCtrl,
          ),
          _editableRow(
            label: "State",
            icon: Icons.location_city_outlined,
            controller: stateCtrl,
          ),
          _editableRow(
            label: "Address",
            icon: Icons.location_on_outlined,
            controller: addressCtrl,
            maxLines: 2,
          ),
          _actionRow(
            label: "Identity Document",
            value: aadhaarPhoto != null
                ? "Selected"
                : aadhaarUrl.isNotEmpty
                    ? "Uploaded"
                    : "Upload",
            icon: Icons.credit_card_outlined,
            valueColor: aadhaarPhoto != null
                ? _C.green
                : _C.textSecondary,
            onTap: pickAadhaarPhoto,
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            height: 46,
            child: ElevatedButton(
              onPressed: loading
                  ? null
                  : saveProfile,
              style: ElevatedButton.styleFrom(
                elevation: 0,
                backgroundColor: _C.orange,
                disabledBackgroundColor:
                    _C.orange.withOpacity(0.45),
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(24),
                ),
              ),
              child: loading
                  ? const SizedBox(
                      width: 19,
                      height: 19,
                      child: CircularProgressIndicator(
                        color: Colors.black,
                        strokeWidth: 2,
                      ),
                    )
                  : const Text(
                      "Save Profile",
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(
            parent: BouncingScrollPhysics(),
          ),
          child: Column(
            children: [
              _topBar(),
              _profileHeader(),
              _profileDetails(),
              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }
}

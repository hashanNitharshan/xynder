import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../services/api_service.dart';

class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff121214);
  static const divider = Color(0xff242428);

  static const orange = Color(0xffFF9F2E);
  static const red = Color(0xffF6465D);
  static const green = Color(0xff00C076);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff77777F);
  static const textMuted = Color(0xff55555C);
}

class PaymentMethodsScreen extends StatefulWidget {
  final Map user;

  const PaymentMethodsScreen({
    super.key,
    required this.user,
  });

  @override
  State<PaymentMethodsScreen> createState() =>
      _PaymentMethodsScreenState();
}

class _PaymentMethodsScreenState extends State<PaymentMethodsScreen> {
  late Map user;

  List banks = [];

  final upiNameCtrl = TextEditingController();
  final upiIdCtrl = TextEditingController();

  bool loading = false;
  bool initialLoading = true;
  int selectedTab = 0;

  XFile? upiQr;

  @override
  void initState() {
    super.initState();

    user = widget.user;

    upiNameCtrl.text =
        user["upi_name"]?.toString() ?? "";

    upiIdCtrl.text =
        user["upi_id"]?.toString() ?? "";

    _load();
  }

  @override
  void dispose() {
    upiNameCtrl.dispose();
    upiIdCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final data = await ApiService.paymentMethods();

      if (!mounted) return;

      if (data["success"] == true) {
        final freshUser = Map<String, dynamic>.from(
          data["user"] ?? user,
        );

        final apiBanks = List.from(
          data["bank_accounts"] ?? [],
        );

        final oldBankName =
            freshUser["bank_name"]?.toString().trim() ?? "";

        final oldBranch =
            freshUser["branch"]?.toString().trim() ?? "";

        final oldAccount =
            freshUser["account_number"]?.toString().trim() ?? "";

        final oldAccountType =
            freshUser["account_type"]?.toString().trim() ?? "";

        final oldIfsc =
            freshUser["ifsc"]?.toString().trim() ?? "";

        if (apiBanks.isEmpty &&
            (oldBankName.isNotEmpty ||
                oldBranch.isNotEmpty ||
                oldAccount.isNotEmpty ||
                oldAccountType.isNotEmpty ||
                oldIfsc.isNotEmpty)) {
          apiBanks.add({
            "id": "old",
            "bank_name": oldBankName,
            "branch": oldBranch,
            "account_number": oldAccount,
            "account_type": oldAccountType,
            "ifsc": oldIfsc,
            "is_default": true,
            "old_user_bank": true,
          });
        }

        setState(() {
          user = freshUser;
          banks = apiBanks;

          upiNameCtrl.text =
              user["upi_name"]?.toString() ?? "";

          upiIdCtrl.text =
              user["upi_id"]?.toString() ?? "";

          initialLoading = false;
        });
      } else {
        setState(() {
          initialLoading = false;
        });

        _snack(
          data["message"]?.toString() ?? "Failed to load",
          ok: false,
        );
      }
    } catch (_) {
      if (!mounted) return;

      setState(() {
        initialLoading = false;
      });

      _snack(
        "Failed to load payment methods",
        ok: false,
      );
    }
  }

  void _snack(
    String message, {
    bool ok = true,
  }) {
    if (!mounted) return;

    final messenger = ScaffoldMessenger.of(context);

    messenger
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          backgroundColor: ok ? _C.green : _C.red,
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
              "Payment Methods",
              textAlign: TextAlign.center,
              style: TextStyle(
                color: _C.textPrimary,
                fontSize: 16,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          SizedBox(
            width: 44,
            child: IconButton(
              onPressed: initialLoading ? null : _load,
              icon: const Icon(
                Icons.refresh_rounded,
                color: _C.textPrimary,
                size: 20,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _tabs() {
    final labels = [
      "Bank Details",
      "UPI Details",
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
      child: Row(
        children: List.generate(
          labels.length,
          (index) {
            final active = selectedTab == index;

            return Expanded(
              child: GestureDetector(
                onTap: () {
                  setState(() {
                    selectedTab = index;
                  });
                },
                child: Container(
                  height: 38,
                  margin: EdgeInsets.only(
                    right: index == 0 ? 8 : 0,
                    left: index == 1 ? 8 : 0,
                  ),
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: active
                        ? _C.surface
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: active
                          ? _C.orange
                          : _C.divider,
                      width: active ? 1.1 : 0.8,
                    ),
                  ),
                  child: Text(
                    labels[index],
                    style: TextStyle(
                      color: active
                          ? _C.textPrimary
                          : _C.textSecondary,
                      fontSize: 12,
                      fontWeight: active
                          ? FontWeight.w700
                          : FontWeight.w500,
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _sectionTitle({
    required String title,
    required String subtitle,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 2, 16, 12),
      child: Align(
        alignment: Alignment.centerLeft,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                color: _C.textPrimary,
                fontSize: 15,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              subtitle,
              style: const TextStyle(
                color: _C.textMuted,
                fontSize: 11,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _editableRow({
    required String label,
    required IconData icon,
    required TextEditingController controller,
    TextInputType? keyboardType,
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
            width: 112,
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

  Widget _bankInformationRow({
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.only(
        top: 4,
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 105,
            child: Text(
              label,
              style: const TextStyle(
                color: _C.textMuted,
                fontSize: 11,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value.isNotEmpty ? value : "-",
              textAlign: TextAlign.right,
              style: const TextStyle(
                color: _C.textSecondary,
                fontSize: 11.5,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _bankTile(Map bank) {
    final isDefault =
        bank["is_default"] == true ||
        bank["is_default"] == 1 ||
        bank["is_default"]?.toString() == "1";

    final isOld =
        bank["old_user_bank"] == true;

    final bankName =
        bank["bank_name"]?.toString().trim() ?? "";

    final branch =
        bank["branch"]?.toString().trim() ?? "";

    final account =
        bank["account_number"]?.toString().trim() ?? "";

    final accountType =
        bank["account_type"]?.toString().trim() ?? "";

    final ifsc =
        bank["ifsc"]?.toString().trim() ?? "";

    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: 16,
      ),
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(
            width: 28,
            child: Icon(
              Icons.account_balance_outlined,
              color: _C.textPrimary,
              size: 18,
            ),
          ),
          const SizedBox(width: 7),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        bankName.isNotEmpty
                            ? bankName
                            : "Bank Account",
                        style: const TextStyle(
                          color: _C.textPrimary,
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    if (isDefault)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: _C.orange.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Text(
                          "DEFAULT",
                          style: TextStyle(
                            color: _C.orange,
                            fontSize: 9,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 7),
                _bankInformationRow(
                  label: "Branch",
                  value: branch,
                ),
                _bankInformationRow(
                  label: "Account Number",
                  value: account,
                ),
                _bankInformationRow(
                  label: "Account Type",
                  value: accountType,
                ),
                _bankInformationRow(
                  label: "IFSC",
                  value: ifsc,
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          if (isOld)
            const Icon(
              Icons.lock_outline_rounded,
              color: _C.textSecondary,
              size: 18,
            )
          else if (!isDefault)
            PopupMenuButton<String>(
              color: _C.surface,
              padding: EdgeInsets.zero,
              icon: const Icon(
                Icons.more_vert_rounded,
                color: _C.textSecondary,
                size: 19,
              ),
              onSelected: (value) {
                if (value == "default") {
                  _setDefaultBank(bank);
                }
              },
              itemBuilder: (_) => const [
                PopupMenuItem(
                  value: "default",
                  child: Text(
                    "Set as Default",
                    style: TextStyle(
                      color: _C.textPrimary,
                      fontSize: 13,
                    ),
                  ),
                ),
              ],
            )
          else
            const Icon(
              Icons.verified_rounded,
              color: _C.orange,
              size: 18,
            ),
        ],
      ),
    );
  }

  Widget _bankTab() {
    return Column(
      children: [
        _sectionTitle(
          title: "Bank Details",
          subtitle: "Add and manage your bank accounts",
        ),
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: 16,
          ),
          child: _actionRow(
            label: "Add Bank Account",
            value: "",
            icon: Icons.add_card_rounded,
            onTap: _showAddBankDialog,
          ),
        ),
        if (banks.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(
              vertical: 38,
            ),
            child: Column(
              children: [
                Icon(
                  Icons.account_balance_outlined,
                  color: _C.textMuted,
                  size: 28,
                ),
                SizedBox(height: 10),
                Text(
                  "No bank accounts added",
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 12.5,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          )
        else
          ...banks.map(
            (bank) => _bankTile(
              Map<String, dynamic>.from(bank),
            ),
          ),
      ],
    );
  }

  Widget _upiTab() {
    final qrUrl = ApiService.fixUrl(
      user["upi_qr_url"],
    );

    return Column(
      children: [
        _sectionTitle(
          title: "UPI Details",
          subtitle: "Add or update your UPI payment details",
        ),
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: 16,
          ),
          child: Column(
            children: [
              _editableRow(
                label: "UPI Name",
                icon: Icons.account_circle_outlined,
                controller: upiNameCtrl,
              ),
              _editableRow(
                label: "UPI ID",
                icon: Icons.link_rounded,
                controller: upiIdCtrl,
              ),
              _actionRow(
                label: "UPI QR",
                value: upiQr != null
                    ? "Selected"
                    : qrUrl.isNotEmpty
                        ? "Uploaded"
                        : "Upload",
                icon: Icons.qr_code_2_rounded,
                valueColor: upiQr != null
                    ? _C.green
                    : _C.textSecondary,
                onTap: _pickQr,
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 46,
                child: ElevatedButton(
                  onPressed: loading
                      ? null
                      : _saveUpi,
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
                          "Save UPI Details",
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _tabContent() {
    if (selectedTab == 1) {
      return _upiTab();
    }

    return _bankTab();
  }

  Future<void> _pickQr() async {
    final image = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
    );

    if (image != null && mounted) {
      setState(() {
        upiQr = image;
      });
    }
  }

  Future<void> _saveUpi() async {
    if (loading) return;

    FocusScope.of(context).unfocus();

    setState(() {
      loading = true;
    });

    try {
      final response =
          await ApiService.updateUpiMultipart(
        upiName: upiNameCtrl.text.trim(),
        upiId: upiIdCtrl.text.trim(),
        upiQr: upiQr,
      );

      if (!mounted) return;

      setState(() {
        loading = false;
        upiQr = null;
      });

      _snack(
        response["message"]?.toString() ??
            "UPI details updated",
        ok: response["success"] == true,
      );

      if (response["success"] == true) {
        await _load();
      }
    } catch (_) {
      if (!mounted) return;

      setState(() {
        loading = false;
      });

      _snack(
        "Failed to update UPI details",
        ok: false,
      );
    }
  }

  Future<void> _showAddBankDialog() async {
    final bankNameCtrl = TextEditingController();
    final branchCtrl = TextEditingController();
    final accountCtrl = TextEditingController();
    final accountTypeCtrl = TextEditingController();
    final ifscCtrl = TextEditingController();

    bool isDefault = false;
    bool saving = false;

    await showDialog<void>(
      context: context,
      barrierColor: Colors.black.withOpacity(0.75),
      builder: (_) {
        return StatefulBuilder(
          builder: (dialogContext, setDialogState) {
            return AlertDialog(
              backgroundColor: _C.surface,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(18),
                side: const BorderSide(
                  color: _C.divider,
                  width: 0.8,
                ),
              ),
              title: const Text(
                "Add Bank Account",
                style: TextStyle(
                  color: _C.textPrimary,
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                ),
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _dialogField(
                      "Bank Name",
                      bankNameCtrl,
                    ),
                    _dialogField(
                      "Branch",
                      branchCtrl,
                    ),
                    _dialogField(
                      "Account Number",
                      accountCtrl,
                      keyboardType: TextInputType.number,
                    ),
                    _dialogField(
                      "Account Type",
                      accountTypeCtrl,
                    ),
                    _dialogField(
                      "IFSC",
                      ifscCtrl,
                    ),
                    SwitchListTile(
                      value: isDefault,
                      activeColor: _C.orange,
                      contentPadding: EdgeInsets.zero,
                      title: const Text(
                        "Set as default",
                        style: TextStyle(
                          color: _C.textPrimary,
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      onChanged: (value) {
                        setDialogState(() {
                          isDefault = value;
                        });
                      },
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: saving
                      ? null
                      : () => Navigator.pop(
                            dialogContext,
                          ),
                  child: const Text(
                    "Cancel",
                    style: TextStyle(
                      color: _C.textSecondary,
                    ),
                  ),
                ),
                ElevatedButton(
                  onPressed: saving
                      ? null
                      : () async {
                          setDialogState(() {
                            saving = true;
                          });

                          final response =
                              await ApiService.addBankAccount(
                            bankName:
                                bankNameCtrl.text.trim(),
                            branch:
                                branchCtrl.text.trim(),
                            accountNumber:
                                accountCtrl.text.trim(),
                            accountType:
                                accountTypeCtrl.text.trim(),
                            ifsc:
                                ifscCtrl.text.trim(),
                            isDefault: isDefault,
                          );

                          if (!dialogContext.mounted) return;

                          Navigator.pop(dialogContext);

                          _snack(
                            response["message"]?.toString() ??
                                "Bank account saved",
                            ok: response["success"] == true,
                          );

                          if (response["success"] == true) {
                            await _load();
                          }
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _C.orange,
                    foregroundColor: Colors.black,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18),
                    ),
                  ),
                  child: saving
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.black,
                          ),
                        )
                      : const Text(
                          "Save",
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                ),
              ],
            );
          },
        );
      },
    );

    bankNameCtrl.dispose();
    branchCtrl.dispose();
    accountCtrl.dispose();
    accountTypeCtrl.dispose();
    ifscCtrl.dispose();
  }

  Widget _dialogField(
    String label,
    TextEditingController controller, {
    TextInputType? keyboardType,
  }) {
    return Padding(
      padding: const EdgeInsets.only(
        bottom: 12,
      ),
      child: TextField(
        controller: controller,
        keyboardType: keyboardType,
        cursorColor: _C.orange,
        style: const TextStyle(
          color: _C.textPrimary,
          fontSize: 13,
          fontWeight: FontWeight.w600,
        ),
        decoration: InputDecoration(
          isDense: true,
          labelText: label,
          labelStyle: const TextStyle(
            color: _C.textSecondary,
            fontSize: 12.5,
          ),
          enabledBorder: const UnderlineInputBorder(
            borderSide: BorderSide(
              color: _C.divider,
            ),
          ),
          focusedBorder: const UnderlineInputBorder(
            borderSide: BorderSide(
              color: _C.orange,
              width: 1.2,
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _setDefaultBank(Map bank) async {
    if (bank["old_user_bank"] == true) {
      _snack(
        "Old profile bank details cannot be changed here",
        ok: false,
      );
      return;
    }

    final isAlreadyDefault =
        bank["is_default"] == true ||
        bank["is_default"] == 1 ||
        bank["is_default"]?.toString() == "1";

    if (isAlreadyDefault) return;

    try {
      final response =
          await ApiService.updateBankAccount(
        id: bank["id"].toString(),
        bankName:
            bank["bank_name"]?.toString() ?? "",
        branch:
            bank["branch"]?.toString() ?? "",
        accountNumber:
            bank["account_number"]?.toString() ?? "",
        accountType:
            bank["account_type"]?.toString() ?? "",
        ifsc:
            bank["ifsc"]?.toString() ?? "",
        isDefault: true,
      );

      _snack(
        response["message"]?.toString() ??
            "Default bank updated",
        ok: response["success"] == true,
      );

      if (response["success"] == true) {
        await _load();
      }
    } catch (_) {
      _snack(
        "Failed to update default bank",
        ok: false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: initialLoading
            ? const Center(
                child: CircularProgressIndicator(
                  color: _C.orange,
                  strokeWidth: 2.5,
                ),
              )
            : RefreshIndicator(
                color: _C.orange,
                backgroundColor: _C.surface,
                onRefresh: _load,
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(
                    parent: BouncingScrollPhysics(),
                  ),
                  child: Column(
                    children: [
                      _topBar(),
                      _tabs(),
                      _tabContent(),
                      const SizedBox(height: 30),
                    ],
                  ),
                ),
              ),
      ),
    );
  }
}

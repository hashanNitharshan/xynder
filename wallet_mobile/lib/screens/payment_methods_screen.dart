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

class PaymentMethodsScreen extends StatefulWidget {
  final Map user;

  const PaymentMethodsScreen({super.key, required this.user});

  @override
  State<PaymentMethodsScreen> createState() => _PaymentMethodsScreenState();
}

class _PaymentMethodsScreenState extends State<PaymentMethodsScreen> {
  late Map user;
  List banks = [];

  final upiNameCtrl = TextEditingController();
  final upiIdCtrl = TextEditingController();

  bool loading = false;
  XFile? upiQr;

  @override
  void initState() {
    super.initState();
    user = widget.user;
    upiNameCtrl.text = user["upi_name"]?.toString() ?? "";
    upiIdCtrl.text = user["upi_id"]?.toString() ?? "";
    _load();
  }

  @override
  void dispose() {
    upiNameCtrl.dispose();
    upiIdCtrl.dispose();
    super.dispose();
  }

  String get _role => user["role"]?.toString() ?? "user";

  Future<void> _load() async {
    final data = await ApiService.paymentMethods();

    if (!mounted) return;

    if (data["success"] == true) {
      final freshUser = Map<String, dynamic>.from(data["user"] ?? user);
      final apiBanks = List.from(data["bank_accounts"] ?? []);

      final oldBankName = freshUser["bank_name"]?.toString().trim() ?? "";
      final oldBranch = freshUser["branch"]?.toString().trim() ?? "";
      final oldAccount = freshUser["account_number"]?.toString().trim() ?? "";
      final oldAccountType =
          freshUser["account_type"]?.toString().trim() ?? "";
      final oldIfsc = freshUser["ifsc"]?.toString().trim() ?? "";

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
        upiNameCtrl.text = user["upi_name"]?.toString() ?? "";
        upiIdCtrl.text = user["upi_id"]?.toString() ?? "";
      });
    } else {
      _snack(data["message"]?.toString() ?? "Failed to load", ok: false);
    }
  }

  void _snack(String msg, {bool ok = true}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: ok ? _C.amber : _C.red,
        behavior: SnackBarBehavior.floating,
        content: Text(
          msg,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
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
        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
        decoration: InputDecoration(
          prefixIcon: Icon(icon, color: _C.orange, size: 20),
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
            borderSide: const BorderSide(color: _C.orange),
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
    Widget? action,
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
                child: Icon(icon, color: _C.gold, size: 18),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    color: _C.textPrimary,
                    fontSize: 17,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              if (action != null) action,
            ],
          ),
          const SizedBox(height: 20),
          ...children,
        ],
      ),
    );
  }

  Widget _button({
    required String label,
    required VoidCallback onTap,
    IconData? icon,
    bool danger = false,
  }) {
    return GestureDetector(
      onTap: loading ? null : onTap,
      child: Container(
        height: 52,
        decoration: BoxDecoration(
          gradient: danger ? null : _C.gradientAccent,
          color: danger ? _C.red.withOpacity(0.12) : null,
          borderRadius: BorderRadius.circular(16),
          border: danger ? Border.all(color: _C.red) : null,
        ),
        child: Center(
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (icon != null) ...[
                Icon(icon, color: danger ? _C.red : Colors.black, size: 18),
                const SizedBox(width: 8),
              ],
              Text(
                label,
                style: TextStyle(
                  color: danger ? _C.red : Colors.black,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _pickQr() async {
    final img = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
    );

    if (img != null) setState(() => upiQr = img);
  }

  Future<void> _saveUpi() async {
    if (loading) return;

    setState(() => loading = true);

    final res = await ApiService.updateUpiMultipart(
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
      res["message"]?.toString() ?? "UPI updated",
      ok: res["success"] == true,
    );

    if (res["success"] == true) _load();
  }

  Future<void> _showBankDialog({Map? bank}) async {
    if (bank?["old_user_bank"] == true) {
      _snack("Old profile bank details cannot edit here. Add a new bank account.", ok: false);
      return;
    }

    final bankNameCtrl =
        TextEditingController(text: bank?["bank_name"]?.toString() ?? "");
    final branchCtrl =
        TextEditingController(text: bank?["branch"]?.toString() ?? "");
    final accountCtrl =
        TextEditingController(text: bank?["account_number"]?.toString() ?? "");
    final accountTypeCtrl =
        TextEditingController(text: bank?["account_type"]?.toString() ?? "");
    final ifscCtrl =
        TextEditingController(text: bank?["ifsc"]?.toString() ?? "");

    bool isDefault = bank?["is_default"] == true ||
        bank?["is_default"] == 1 ||
        bank?["is_default"]?.toString() == "1";

    bool saving = false;

    await showDialog(
      context: context,
      builder: (_) {
        return StatefulBuilder(
          builder: (ctx, setDialog) {
            return AlertDialog(
              backgroundColor: _C.surfaceAlt,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(22),
              ),
              title: Text(
                bank == null ? "Add Bank Account" : "Edit Bank Account",
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w900,
                ),
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
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
                    SwitchListTile(
                      value: isDefault,
                      activeColor: _C.orange,
                      contentPadding: EdgeInsets.zero,
                      title: const Text(
                        "Set as default",
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      onChanged: (v) => setDialog(() => isDefault = v),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: saving ? null : () => Navigator.pop(ctx),
                  child: const Text("Cancel"),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _C.orange,
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  onPressed: saving
                      ? null
                      : () async {
                          setDialog(() => saving = true);

                          final res = bank == null
                              ? await ApiService.addBankAccount(
                                  bankName: bankNameCtrl.text.trim(),
                                  branch: branchCtrl.text.trim(),
                                  accountNumber: accountCtrl.text.trim(),
                                  accountType: accountTypeCtrl.text.trim(),
                                  ifsc: ifscCtrl.text.trim(),
                                  isDefault: isDefault,
                                )
                              : await ApiService.updateBankAccount(
                                  id: bank["id"].toString(),
                                  bankName: bankNameCtrl.text.trim(),
                                  branch: branchCtrl.text.trim(),
                                  accountNumber: accountCtrl.text.trim(),
                                  accountType: accountTypeCtrl.text.trim(),
                                  ifsc: ifscCtrl.text.trim(),
                                  isDefault: isDefault,
                                );

                          if (!ctx.mounted) return;

                          Navigator.pop(ctx);

                          _snack(
                            res["message"]?.toString() ?? "Saved",
                            ok: res["success"] == true,
                          );

                          if (res["success"] == true) _load();
                        },
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
                          style: TextStyle(fontWeight: FontWeight.w900),
                        ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Future<void> _deleteBank(Map bank) async {
    if (bank["old_user_bank"] == true) {
      _snack("Old profile bank details cannot delete here.", ok: false);
      return;
    }

    final yes = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: _C.surfaceAlt,
        title: const Text(
          "Delete Bank Account?",
          style: TextStyle(color: Colors.white),
        ),
        content: const Text(
          "This bank account will be removed.",
          style: TextStyle(color: _C.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text("Cancel"),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text("Delete", style: TextStyle(color: _C.red)),
          ),
        ],
      ),
    );

    if (yes != true) return;

    final res = await ApiService.deleteBankAccount(bank["id"].toString());

    _snack(
      res["message"]?.toString() ?? "Deleted",
      ok: res["success"] == true,
    );

    if (res["success"] == true) _load();
  }

  Widget _bankTile(Map bank) {
    final isDefault = bank["is_default"] == true ||
        bank["is_default"] == 1 ||
        bank["is_default"]?.toString() == "1";

    final isOld = bank["old_user_bank"] == true;

    final bankName = bank["bank_name"]?.toString().trim() ?? "";
    final branch = bank["branch"]?.toString().trim() ?? "";
    final account = bank["account_number"]?.toString().trim() ?? "";
    final accountType = bank["account_type"]?.toString().trim() ?? "";
    final ifsc = bank["ifsc"]?.toString().trim() ?? "";

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _C.bg,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isDefault ? _C.orange : _C.border,
          width: isDefault ? 1.3 : 1,
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: _C.orange.withOpacity(0.12),
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(Icons.account_balance_rounded, color: _C.gold),
          ),
          const SizedBox(width: 13),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  bankName.isNotEmpty ? bankName : "Bank Account",
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  "Branch: ${branch.isNotEmpty ? branch : "-"}",
                  style: const TextStyle(color: _C.textSecondary, fontSize: 12),
                ),
                Text(
                  "Account: ${account.isNotEmpty ? account : "-"}",
                  style: const TextStyle(color: _C.textSecondary, fontSize: 12),
                ),
                Text(
                  "Type: ${accountType.isNotEmpty ? accountType : "-"}",
                  style: const TextStyle(color: _C.textSecondary, fontSize: 12),
                ),
                Text(
                  "IFSC: ${ifsc.isNotEmpty ? ifsc : "-"}",
                  style: const TextStyle(color: _C.textSecondary, fontSize: 12),
                ),
                const SizedBox(height: 6),
                Row(
                  children: [
                    if (isDefault)
                      const Text(
                        "DEFAULT",
                        style: TextStyle(
                          color: _C.gold,
                          fontWeight: FontWeight.w900,
                          fontSize: 10,
                        ),
                      ),
                    if (isOld) ...[
                      if (isDefault) const SizedBox(width: 8),
                      const Text(
                        "OLD PROFILE BANK",
                        style: TextStyle(
                          color: _C.textSecondary,
                          fontWeight: FontWeight.w900,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
          if (isOld)
            const Padding(
              padding: EdgeInsets.only(top: 8),
              child: Icon(
                Icons.lock_outline_rounded,
                color: _C.textSecondary,
                size: 18,
              ),
            )
          else
            PopupMenuButton<String>(
              color: _C.surfaceAlt,
              icon: const Icon(Icons.more_vert_rounded, color: Colors.white70),
              onSelected: (v) {
                if (v == "edit") _showBankDialog(bank: bank);
                if (v == "delete") _deleteBank(bank);
              },
              itemBuilder: (_) => const [
                PopupMenuItem(
                  value: "edit",
                  child: Text("Edit", style: TextStyle(color: Colors.white)),
                ),
                PopupMenuItem(
                  value: "delete",
                  child: Text("Delete", style: TextStyle(color: _C.red)),
                ),
              ],
            ),
        ],
      ),
    );
  }

  Widget _upiSection() {
    final qrUrl = ApiService.fixUrl(user["upi_qr_url"]);

    return _sectionCard(
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
                qrUrl,
                height: 150,
                fit: BoxFit.contain,
                errorBuilder: (_, __, ___) {
                  return const Padding(
                    padding: EdgeInsets.all(18),
                    child: Text(
                      "UPI QR image not available",
                      textAlign: TextAlign.center,
                      style: TextStyle(color: _C.textSecondary),
                    ),
                  );
                },
              ),
            ),
          ),
        if (upiQr != null)
          Container(
            width: double.infinity,
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(13),
            decoration: BoxDecoration(
              color: _C.gold.withOpacity(0.10),
              borderRadius: BorderRadius.circular(15),
              border: Border.all(color: _C.gold.withOpacity(0.30)),
            ),
            child: const Text(
              "New UPI QR selected",
              style: TextStyle(color: _C.gold, fontWeight: FontWeight.w800),
            ),
          ),
        _button(
          label: upiQr == null ? "Upload / Change UPI QR" : "UPI QR Selected",
          icon: Icons.qr_code_2_rounded,
          onTap: _pickQr,
        ),
        const SizedBox(height: 12),
        _button(
          label: loading ? "Saving..." : "Save UPI Details",
          icon: Icons.save_rounded,
          onTap: _saveUpi,
        ),
      ],
    );
  }

  Widget _bankSection() {
    return _sectionCard(
      title: "Bank Accounts",
      icon: Icons.account_balance_rounded,
      action: GestureDetector(
        onTap: () => _showBankDialog(),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
          decoration: BoxDecoration(
            color: _C.orange.withOpacity(0.12),
            borderRadius: BorderRadius.circular(999),
            border: Border.all(color: _C.orange.withOpacity(0.35)),
          ),
          child: const Text(
            "+ Add",
            style: TextStyle(
              color: _C.gold,
              fontWeight: FontWeight.w900,
              fontSize: 12,
            ),
          ),
        ),
      ),
      children: [
        if (banks.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: _C.bg,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: _C.border),
            ),
            child: const Text(
              "No bank accounts added yet.",
              textAlign: TextAlign.center,
              style: TextStyle(color: _C.textSecondary),
            ),
          )
        else
          ...banks.map((b) => _bankTile(Map<String, dynamic>.from(b))),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      appBar: TopBar(
        title: "Payment Methods",
        user: user,
        role: _role,
        showBack: true,
        backToRoot: false,
      ),
      body: RefreshIndicator(
        color: _C.orange,
        onRefresh: _load,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 760),
              child: Column(
                children: [
                  _bankSection(),
                  _upiSection(),
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
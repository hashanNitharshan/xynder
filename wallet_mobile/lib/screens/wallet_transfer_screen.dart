import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/pinwheel_loader.dart';

// ─────────────────────────────────────────────────────────────
//  DESIGN TOKENS - SAME STYLE AS request_screen.dart (dark yellow)
// ─────────────────────────────────────────────────────────────
class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff0D0D0D);
  static const surfaceAlt = Color(0xff171717);

  static const border = Color(0xff2E2E2E);
  static const borderFaint = Color(0xff202020);

  // Theme (Dark Yellow / Goldenrod)
  static const orange = Color(0xffB8860B); // dark goldenrod
  static const amber = Color(0xff9A6B00); // deep amber
  static const gold = Color(0xffD4A017); // muted gold highlight

  static const red = Color(0xffEF4444);
  static const blue = Color(0xffB8860B);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xffA3A3A3);

  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [
      Color(0xff8A6300),
      Color(0xffB8860B),
      Color(0xffD4A017),
    ],
  );

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xff050505),
      Color(0xff111111),
      Color(0xff1A1500),
    ],
  );

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.2, -0.6),
    radius: 1.2,
    colors: [
      Color(0x55B8860B),
      Color(0x22D4A017),
      Color(0x00000000),
    ],
  );
}

class WalletTransferScreen extends StatefulWidget {
  final Map user;
  final Future<void> Function()? onSuccess;

  const WalletTransferScreen({
    super.key,
    required this.user,
    this.onSuccess,
  });

  @override
  State<WalletTransferScreen> createState() => _WalletTransferScreenState();
}

class _WalletTransferScreenState extends State<WalletTransferScreen>
    with SingleTickerProviderStateMixin {
  final walletCtrl = TextEditingController();
  final amountCtrl = TextEditingController();
  final noteCtrl = TextEditingController();

  bool loading = false;
  bool lookupLoading = false;

  Map<String, dynamic>? receiver;

  String? _lastTransferId;
  Map<String, dynamic>? _lastReceiverUser;

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
    walletCtrl.dispose();
    amountCtrl.dispose();
    noteCtrl.dispose();
    super.dispose();
  }

  double toDouble(dynamic value) {
    return double.tryParse(value?.toString() ?? "0") ?? 0;
  }

  bool get isVerified {
    return widget.user["is_verified"] == true ||
        widget.user["is_verified"] == 1 ||
        widget.user["is_verified"]?.toString() == "1";
  }

  void showSnack(String message, {bool success = true}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: success ? _C.amber : _C.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        content: Text(
          message,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
    );
  }

  Future<void> lookupWallet() async {
    final walletId = walletCtrl.text.trim();

    if (walletId.isEmpty) {
      showSnack("Enter receiver wallet address.", success: false);
      return;
    }

    setState(() {
      lookupLoading = true;
      receiver = null;
    });

    final res = await ApiService.walletLookup(walletId);

    if (!mounted) return;
    setState(() => lookupLoading = false);

    if (res["success"] == true) {
      setState(() {
        receiver = Map<String, dynamic>.from(res["user"]);
      });
    } else {
      showSnack(res["message"] ?? "Wallet not found.", success: false);
    }
  }

  Future<void> sendTransfer() async {
    if (loading) return;

    final walletId = walletCtrl.text.trim();
    final amount = amountCtrl.text.trim();

    if (walletId.isEmpty || amount.isEmpty) {
      showSnack("Wallet address and amount are required.", success: false);
      return;
    }

    if (!isVerified) {
      showSnack(
        "Your account is not verified yet. Transfers are disabled.",
        success: false,
      );
      return;
    }

    setState(() => loading = true);

    final res = await ApiService.walletTransfer(
      receiverWalletId: walletId,
      amount: amount,
      note: noteCtrl.text.trim(),
    );

    if (!mounted) return;
    setState(() => loading = false);

    if (res["success"] == true) {
      final transferId =
          res["transfer"]?["id"]?.toString() ?? res["transfer_id"]?.toString();

      final transferNo = res["transfer"]?["transaction_no"]?.toString() ??
          (transferId != null ? "TRA${transferId.padLeft(9, "0")}" : null);

      final receiverSnapshot =
          receiver != null ? Map<String, dynamic>.from(receiver!) : null;

      walletCtrl.clear();
      amountCtrl.clear();
      noteCtrl.clear();

      setState(() {
        receiver = null;
        _lastTransferId = transferId;
        _lastReceiverUser = receiverSnapshot;
      });

      if (widget.onSuccess != null) {
        await widget.onSuccess!();
      }

      _showSuccessDialog(
        transferId: transferId,
        transferNo: transferNo,
        receiverUser: receiverSnapshot,
        message: res["message"] ?? "Transfer successful.",
      );
    } else {
      showSnack(res["message"] ?? "Transfer failed.", success: false);
    }
  }

  // ═══════════════════════════════════════════
  //  FORM CARD
  // ═══════════════════════════════════════════
  Widget _transferForm() {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 18, 20, 28),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(26),
        border: Border.all(color: _C.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _formHeader(),
          _myWalletCard(),
          const SizedBox(height: 20),
          _field(
            controller: walletCtrl,
            label: "Receiver Wallet Address",
            icon: Icons.account_balance_wallet_rounded,
            iconColor: _C.orange,
          ),
          const SizedBox(height: 12),
          _outlineBtn(
            label: lookupLoading ? "Checking Receiver..." : "Check Receiver",
            icon: Icons.search_rounded,
            loading: lookupLoading,
            onTap: lookupWallet,
          ),
          if (receiver != null) ...[
            const SizedBox(height: 16),
            _receiverCard(),
          ],
          const SizedBox(height: 16),
          _field(
            controller: amountCtrl,
            label: "USD Amount",
            icon: Icons.attach_money_rounded,
            iconColor: _C.gold,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
          ),
          const SizedBox(height: 16),
          _field(
            controller: noteCtrl,
            label: "Note (optional)",
            icon: Icons.notes_rounded,
            iconColor: _C.textSecondary,
            maxLines: 3,
          ),
          const SizedBox(height: 22),
          _gradientBtn(
            label: loading ? "Processing..." : "Transfer Now",
            icon: Icons.send_rounded,
            loading: loading,
            onTap: sendTransfer,
          ),
        ],
      ),
    );
  }

  Widget _formHeader() {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(9),
          decoration: BoxDecoration(
            color: _C.orange.withOpacity(0.12),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Icon(
            Icons.send_rounded,
            color: _C.gold,
            size: 18,
          ),
        ),
        const SizedBox(width: 12),
        const Expanded(
          child: Text(
            "Transfer Details",
            style: TextStyle(
              color: _C.textPrimary,
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
      ],
    );
  }

  Widget _field({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    Color iconColor = _C.amber,
    TextInputType? keyboardType,
    int maxLines = 1,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
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
    );
  }

  Widget _outlineBtn({
    required String label,
    required IconData icon,
    required VoidCallback onTap,
    bool loading = false,
  }) {
    return GestureDetector(
      onTap: loading ? null : onTap,
      child: Container(
        width: double.infinity,
        height: 52,
        decoration: BoxDecoration(
          color: _C.bg,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: _C.gold, width: 1.3),
        ),
        child: Center(
          child: loading
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    color: _C.gold,
                    strokeWidth: 2.2,
                  ),
                )
              : Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(icon, color: _C.gold, size: 18),
                    const SizedBox(width: 8),
                    Text(
                      label,
                      style: const TextStyle(
                        color: _C.gold,
                        fontWeight: FontWeight.w900,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
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
                    color: Colors.white,
                  ),
                )
              : Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (icon != null) ...[
                      Icon(icon, color: Colors.white, size: 18),
                      const SizedBox(width: 8),
                    ],
                    Text(
                      label,
                      style: const TextStyle(
                        color: Colors.white,
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

  Widget _receiverCard() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: const Color(0xff2A2100),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _C.orange, width: 1.4),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Container(
                    width: 46,
                    height: 46,
                    decoration: BoxDecoration(
                      color: _C.gold.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(
                      Icons.person_rounded,
                      color: _C.gold,
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          "Receiver Found",
                          style: TextStyle(
                            color: _C.gold,
                            fontWeight: FontWeight.w900,
                            fontSize: 12,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          receiver?["name"]?.toString() ?? "User",
                          style: const TextStyle(
                            color: _C.textPrimary,
                            fontWeight: FontWeight.w800,
                            fontSize: 15,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          receiver?["email"]?.toString() ?? "",
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: _C.textSecondary,
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Icon(
                    Icons.check_circle_rounded,
                    color: _C.gold,
                    size: 22,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showSuccessDialog({
    String? transferId,
    String? transferNo,
    Map<String, dynamic>? receiverUser,
    required String message,
  }) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) {
        return AlertDialog(
          backgroundColor: _C.surfaceAlt,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(26),
          ),
          contentPadding: const EdgeInsets.fromLTRB(24, 0, 24, 24),
          titlePadding: const EdgeInsets.fromLTRB(24, 24, 24, 12),
          title: Column(
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: _C.gold.withOpacity(0.12),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: _C.gold.withOpacity(0.3),
                    width: 2,
                  ),
                ),
                child: const Icon(
                  Icons.check_rounded,
                  color: _C.gold,
                  size: 32,
                ),
              ),
              const SizedBox(height: 14),
              const Text(
                "Transfer Sent!",
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w900,
                  fontSize: 20,
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                message,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: _C.textSecondary,
                  fontSize: 13,
                ),
              ),
              if (transferId != null) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: _C.bg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: _C.border),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(
                        Icons.tag_rounded,
                        color: _C.gold,
                        size: 16,
                      ),
                      const SizedBox(width: 7),
                      Text(
                        "Transfer ${transferNo ?? transferId}",
                        style: const TextStyle(
                          color: _C.gold,
                          fontWeight: FontWeight.w900,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                const Text(
                  "Transfer completed successfully.",
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white38,
                    fontSize: 11,
                  ),
                ),
              ],
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: TextButton(
                  onPressed: () => Navigator.pop(context),
                  style: TextButton.styleFrom(
                    foregroundColor: _C.textSecondary,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                      side: const BorderSide(color: _C.border),
                    ),
                  ),
                  child: const Text(
                    "Close",
                    style: TextStyle(fontWeight: FontWeight.w700),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            children: [
              _transferForm(),
            ],
          ),
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  STANDARD WALLET SUMMARY CARD (redesigned)
  // ═══════════════════════════════════════════
  Widget _myWalletCard() {
    final walletId = widget.user["wallet_id"]?.toString() ?? "Not available";
    final balance = toDouble(widget.user["balance"]);

    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: _C.surfaceAlt,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _C.border),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  walletId,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: _C.textPrimary,
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.2,
                  ),
                ),
              ],
            ),
          ),
          Container(
            width: 1,
            height: 28,
            margin: const EdgeInsets.symmetric(horizontal: 14),
            color: _C.border,
          ),
          Text(
            "\$${balance.toStringAsFixed(2)}",
            style: const TextStyle(
              color: _C.gold,
              fontSize: 15,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }
}
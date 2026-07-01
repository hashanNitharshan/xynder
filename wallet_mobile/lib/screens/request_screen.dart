import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'chat_screen.dart';

class _C {
  static const bg = Color(0xff0a0a0a);
  static const surface = Color(0xff141414);
  static const surfaceAlt = Color(0xff1c1c1e);
  static const border = Color(0xff2a2a2a);
  static const borderFaint = Color(0xff1e1e1e);

  static const orange = Color(0xffFF4500);
  static const amber = Color(0xffFFB800);
  static const gold = Color(0xffFFD700);

  static const green = Color(0xff22c55e);
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
}

class RequestScreen extends StatefulWidget {
  const RequestScreen({super.key});

  @override
  State<RequestScreen> createState() => _RequestScreenState();
}

class _RequestScreenState extends State<RequestScreen>
    with SingleTickerProviderStateMixin {
  String type = "withdrawal";

  final _amountCtrl = TextEditingController();
  final _noteCtrl = TextEditingController();

  bool _pageLoading = true;
  bool _merchantLoading = false;
  bool _submitting = false;

  Map<String, dynamic>? _config;
  Map<String, dynamic>? _user;
  List _merchants = [];

  Map<String, dynamic>? _selectedMerchant;

  String? _lastRequestId;
  Map<String, dynamic>? _lastMerchantUser;

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
    _loadPage();
  }

  @override
  void dispose() {
    _anim.dispose();
    _amountCtrl.dispose();
    _noteCtrl.dispose();
    super.dispose();
  }

  double _toD(dynamic v) => double.tryParse(v?.toString() ?? "0") ?? 0;

  double get _balance => _toD(_user?["balance"]);
  double get _amount => double.tryParse(_amountCtrl.text.trim()) ?? 0;

  double get _usdRate => _toD(_config?["usd_rate"]);
  double get _inrRate => _toD(_config?["inr_rate"]);
  double get _fee => _toD(_config?["xynder_fee"]);
  double get _netFee => _toD(_config?["network_fee"]);

  bool get _isSell => type == "withdrawal";

  double get _totalFee => _fee + _netFee;
  double get _converted => _amount * _inrRate;

 double get _total {
  if (_isSell) {
    return _converted - _totalFee; // Sell
  }

  return _converted + _totalFee; // Buy
}

  bool get _isVerified {
    final v = _user?["is_verified"];

    return v == true ||
        v == 1 ||
        v?.toString() == "1" ||
        v?.toString().toLowerCase() == "true";
  }

  bool _isOnlineMerchant(dynamic m) {
    return m["is_online"] == true ||
        m["is_online"] == 1 ||
        m["is_online"]?.toString() == "1" ||
        m["is_online"]?.toString().toLowerCase() == "true";
  }

  Future<void> _loadPage() async {
    if (mounted) setState(() => _pageLoading = true);

    try {
      final pd = await ApiService.profile();
      final cd = await ApiService.currentConfig();

      if (pd["success"] == true) {
        _user = Map<String, dynamic>.from(pd["user"]);
      }

      if (cd["success"] == true) {
        _config = Map<String, dynamic>.from(cd["config"]);
      }
    } catch (_) {}

    if (mounted) setState(() => _pageLoading = false);
  }

  Future<void> _processRequest() async {
    if (_amount <= 0) {
      _snack("Please enter a valid USD amount", ok: false);
      return;
    }

    if (_isSell && _total <= 0) {
      _snack("Amount is too small after fees", ok: false);
      return;
    }

    if (_isSell && _balance < _amount) {
      _snack("Insufficient USD balance", ok: false);
      return;
    }

    if (!_isVerified) {
      _snack("Your account is not verified yet", ok: false);
      return;
    }

    setState(() => _merchantLoading = true);

    final data = await ApiService.merchants();

    if (mounted) {
      setState(() => _merchantLoading = false);
    }

    if (data["success"] != true) {
      _snack(data["message"] ?? "Failed to load merchants", ok: false);
      return;
    }

    final allMerchants = data["merchants"] ?? [];
    _merchants = allMerchants.where((m) => _isOnlineMerchant(m)).toList();

    if (_merchants.isEmpty) {
      _snack("No online merchants available now", ok: false);
      return;
    }

    _showMerchantSheet();
  }

  Future<void> _submitFinal() async {
    if (_selectedMerchant == null) {
      _snack("Please select a merchant", ok: false);
      return;
    }

    if (!_isOnlineMerchant(_selectedMerchant)) {
      _snack("Selected merchant is offline", ok: false);
      return;
    }

    setState(() => _submitting = true);

    final data = await ApiService.createRequestMultipart(
      amount: _amountCtrl.text.trim(),
      type: type,
      merchantId: _selectedMerchant!["id"].toString(),
      note: _noteCtrl.text.trim(),
    );

    if (mounted) setState(() => _submitting = false);

    if (data["success"] == true) {
      final reqId =
          data["request"]?["id"]?.toString() ?? data["request_id"]?.toString();

      final reqNo = data["request"]?["transaction_no"]?.toString() ??
          (reqId != null ? "TNS${reqId.padLeft(9, "0")}" : null);

      final merchant = Map<String, dynamic>.from(_selectedMerchant!);

      if (mounted) Navigator.pop(context);

      _amountCtrl.clear();
      _noteCtrl.clear();

      setState(() {
        _selectedMerchant = null;
        _lastRequestId = reqId;
        _lastMerchantUser = merchant;
      });

      await _loadPage();

      _showSuccessDialog(
        requestId: reqId,
        requestNo: reqNo,
        merchant: merchant,
      );
    } else {
      _snack(data["message"] ?? "Request failed", ok: false);
    }
  }

  void _snack(String msg, {bool ok = true}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: ok ? _C.green : _C.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        content: Text(
          msg,
          style: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
    );
  }

  // Goes back to the Dashboard home page instead of just popping
  // one route (fixes back button not returning to dashboard).
  void _goBackToDashboard() {
    Navigator.popUntil(context, (route) => route.isFirst);
  }

  Widget _topBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Row(
        children: [
          GestureDetector(
            onTap: _goBackToDashboard,
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
                  "P2P Request",
                  style: TextStyle(
                    color: _C.textPrimary,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.3,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  "Buy or Sell USD safely",
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
          if (_lastRequestId != null && _lastMerchantUser != null)
            GestureDetector(
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ChatScreen(
                    otherUser: _lastMerchantUser!,
                    chatType: "request",
                    chatId: _lastRequestId!,
                  ),
                ),
              ),
              child: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  gradient: _C.gradientAccent,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.chat_rounded,
                  color: Colors.black,
                  size: 20,
                ),
              ),
            ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  SMALL BALANCE STRIP (Sell only, replaces big hero card)
  // ═══════════════════════════════════════════
  Widget _smallBalanceStrip() {
    if (!_isSell) return const SizedBox.shrink();

    return FadeTransition(
      opacity: _fade,
      child: SlideTransition(
        position: _slide,
        child: Container(
          margin: const EdgeInsets.fromLTRB(20, 18, 20, 0),
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
          decoration: BoxDecoration(
            gradient: _C.gradientCard,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: const Color(0xff3a1500)),
          ),
          child: Row(
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  gradient: _C.gradientAccent,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.account_balance_wallet_rounded,
                  color: Colors.black,
                  size: 18,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      "Available USD Balance",
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.45),
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      "\$${_balance.toStringAsFixed(2)}",
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: _isVerified
                      ? _C.green.withOpacity(0.14)
                      : _C.amber.withOpacity(0.14),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: _isVerified
                        ? _C.green.withOpacity(0.3)
                        : _C.amber.withOpacity(0.3),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      _isVerified
                          ? Icons.verified_rounded
                          : Icons.warning_amber_rounded,
                      size: 12,
                      color: _isVerified ? _C.green : _C.amber,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      _isVerified ? "VERIFIED" : "UNVERIFIED",
                      style: TextStyle(
                        color: _isVerified ? _C.green : _C.amber,
                        fontSize: 9,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _typeSelector() {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 20, 20, 0),
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _C.border),
      ),
      child: Row(
        children: [
          _typeBtn("Sell USD", Icons.trending_down_rounded, "withdrawal"),
          _typeBtn("Buy USD", Icons.trending_up_rounded, "deposit"),
        ],
      ),
    );
  }

  Widget _typeBtn(String label, IconData icon, String value) {
    final active = type == value;

    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() {
          type = value;
          _selectedMerchant = null;
        }),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 220),
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            gradient: active ? _C.gradientAccent : null,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 17, color: active ? Colors.black : Colors.white54),
              const SizedBox(width: 7),
              Text(
                label,
                style: TextStyle(
                  color: active ? Colors.black : Colors.white70,
                  fontWeight: FontWeight.w900,
                  fontSize: 13,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _formCard() {
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
          const Text(
            "Request Details",
            style: TextStyle(
              color: _C.textPrimary,
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 20),
          _field(
            controller: _amountCtrl,
            label: "USD Amount",
            icon: Icons.attach_money_rounded,
            iconColor: _C.amber,
            keyboardType: TextInputType.number,
            onChanged: (_) => setState(() {}),
          ),
          const SizedBox(height: 16),
          _summaryBox(),
          const SizedBox(height: 16),
          _field(
            controller: _noteCtrl,
            label: "Note (optional)",
            icon: Icons.notes_rounded,
            iconColor: _C.textSecondary,
            maxLines: 3,
          ),
          const SizedBox(height: 22),
          _gradientBtn(
            label: _isSell ? "Continue Sell Request" : "Continue Buy Request",
            icon: Icons.arrow_forward_rounded,
            loading: _merchantLoading,
            onTap: _processRequest,
          ),
        ],
      ),
    );
  }

  Widget _field({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    Color iconColor = _C.amber,
    TextInputType? keyboardType,
    int maxLines = 1,
    Function(String)? onChanged,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      maxLines: maxLines,
      onChanged: onChanged,
      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
      decoration: InputDecoration(
        prefixIcon: Icon(icon, color: iconColor, size: 20),
        labelText: label,
        labelStyle: const TextStyle(color: _C.textSecondary, fontSize: 14),
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

  Widget _summaryBox() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _C.bg,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _C.borderFaint),
      ),
      child: Column(
        children: [
          _summaryRow("Type", _isSell ? "SELL USD" : "BUY USD",
              color: _isSell ? _C.red : _C.green),
          _summaryRow("USD Amount", "\$${_amount.toStringAsFixed(2)}"),
          _summaryRow("USD Rate", "\$${_usdRate.toStringAsFixed(2)}"),
          _summaryRow("Converted INR", "₹${_converted.toStringAsFixed(2)}"),
          _summaryRow("Xynder Fee", "₹${_fee.toStringAsFixed(2)}"),
          _summaryRow("Network Fee", "₹${_netFee.toStringAsFixed(2)}"),
          const SizedBox(height: 4),
          Container(height: 1, color: _C.border),
          const SizedBox(height: 12),
          _summaryRow(
            "Total INR",
            "₹${_total.toStringAsFixed(2)}",
            color: _C.amber,
            bold: true,
            large: true,
          ),
        ],
      ),
    );
  }

  Widget _summaryRow(
    String k,
    String v, {
    Color? color,
    bool bold = false,
    bool large = false,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            k,
            style: TextStyle(
              color: _C.textSecondary,
              fontSize: large ? 13 : 12,
              fontWeight: bold ? FontWeight.w700 : FontWeight.w500,
            ),
          ),
          Flexible(
            child: Text(
              v,
              textAlign: TextAlign.right,
              style: TextStyle(
                color: color ?? Colors.white,
                fontSize: large ? 16 : 13,
                fontWeight: bold ? FontWeight.w900 : FontWeight.w600,
              ),
            ),
          ),
        ],
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

  void _showMerchantSheet() {
    _selectedMerchant = null;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => StatefulBuilder(
        builder: (ctx, setSheet) {
          return Container(
            height: MediaQuery.of(context).size.height * 0.88,
            decoration: const BoxDecoration(
              color: _C.surfaceAlt,
              borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
            ),
            child: Column(
              children: [
                const SizedBox(height: 12),
                Center(
                  child: Container(
                    width: 44,
                    height: 4,
                    decoration: BoxDecoration(
                      color: _C.border,
                      borderRadius: BorderRadius.circular(20),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(9),
                        decoration: BoxDecoration(
                          gradient: _C.gradientAccent,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(
                          Icons.storefront_rounded,
                          color: Colors.black,
                          size: 18,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Text(
                        _isSell
                            ? "Select Online Merchant — Sell"
                            : "Select Online Merchant — Buy",
                        style: const TextStyle(
                          color: _C.textPrimary,
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: _summaryStripSmall(),
                ),
                const SizedBox(height: 14),
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    children: _merchants.map((m) {
                      return _merchantCard(
                        Map<String, dynamic>.from(m),
                        setSheet,
                      );
                    }).toList(),
                  ),
                ),
                Container(
                  padding: EdgeInsets.fromLTRB(
                    20,
                    14,
                    20,
                    MediaQuery.of(ctx).viewInsets.bottom + 20,
                  ),
                  decoration: const BoxDecoration(
                    color: _C.surfaceAlt,
                    border: Border(top: BorderSide(color: _C.border)),
                  ),
                  child: _gradientBtn(
                    label: _isSell ? "Submit Sell Request" : "Submit Buy Request",
                    icon: Icons.send_rounded,
                    loading: _submitting,
                    onTap: _submitFinal,
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _summaryStripSmall() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _C.border),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          _stripItem("Amount", "\$${_amount.toStringAsFixed(2)}", Colors.white),
          _divider(),
          _stripItem(
              "Converted", "₹${_converted.toStringAsFixed(2)}", _C.orange),
          _divider(),
          _stripItem("Total INR", "₹${_total.toStringAsFixed(2)}", _C.amber),
        ],
      ),
    );
  }

  Widget _stripItem(String label, String value, Color color) {
    return Column(
      children: [
        Text(label,
            style: const TextStyle(color: _C.textSecondary, fontSize: 10)),
        const SizedBox(height: 3),
        Text(
          value,
          style: TextStyle(
            color: color,
            fontWeight: FontWeight.w900,
            fontSize: 13,
          ),
        ),
      ],
    );
  }

  Widget _divider() {
    return Container(width: 1, height: 28, color: _C.border);
  }

  Widget _merchantCard(Map<String, dynamic> m, StateSetter setSheet) {
    final selected =
        _selectedMerchant?["id"]?.toString() == m["id"]?.toString();

    return GestureDetector(
      onTap: () {
        setSheet(() {
          _selectedMerchant = Map<String, dynamic>.from(m);
        });
        setState(() {});
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 220),
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: selected ? const Color(0xff1f0d00) : _C.bg,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: selected ? _C.orange : _C.border,
            width: selected ? 1.8 : 1,
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              const Icon(
                Icons.storefront_rounded,
                color: _C.green,
                size: 26,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      m["name"]?.toString() ?? "Merchant",
                      style: const TextStyle(
                        color: _C.textPrimary,
                        fontWeight: FontWeight.w800,
                        fontSize: 15,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      m["email"]?.toString() ?? "",
                      style: const TextStyle(
                        color: _C.textSecondary,
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: _C.green.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Text(
                  "ONLINE",
                  style: TextStyle(
                    color: _C.green,
                    fontSize: 10,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showSuccessDialog({
    String? requestId,
    String? requestNo,
    Map<String, dynamic>? merchant,
  }) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        backgroundColor: _C.surfaceAlt,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(26)),
        title: const Text(
          "Request Submitted!",
          textAlign: TextAlign.center,
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w900,
          ),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              _isSell
                  ? "Your Sell USD request has been submitted successfully."
                  : "Your Buy USD request has been submitted successfully.",
              textAlign: TextAlign.center,
              style: const TextStyle(color: _C.textSecondary, fontSize: 13),
            ),
            const SizedBox(height: 20),
            if (requestId != null && merchant != null)
              _gradientBtn(
                label: "Chat with Merchant",
                icon: Icons.chat_rounded,
                onTap: () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => ChatScreen(
                        otherUser: merchant,
                        chatType: "request",
                        chatId: requestId,
                      ),
                    ),
                  );
                },
              ),
            const SizedBox(height: 10),
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text("Close"),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_pageLoading) {
      return const Scaffold(
        backgroundColor: _C.bg,
        body: Center(
          child: CircularProgressIndicator(color: _C.orange),
        ),
      );
    }

    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: RefreshIndicator(
          color: _C.orange,
          onRefresh: _loadPage,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Column(
              children: [
                _topBar(),
                _smallBalanceStrip(),
                _typeSelector(),
                _formCard(),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/api_service.dart';
import 'chat_screen.dart';
import 'transaction_detail_screen.dart';

class _C {
  // Background
  static const bg = Color(0xff000000);
  static const surface = Color(0xff101010);
  static const surfaceAlt = Color(0xff101010);

  // Borders
  static const border = Color(0xff252525);
  static const borderFaint = Color(0xff252525);

  // Primary Theme (Dark Yellow / Goldenrod)
  static const orange = Color(0xffFF9F2E); // dark goldenrod
  static const amber = Color(0xffFF9F2E); // deep amber
  static const gold = Color(0xffFF9F2E); // muted gold highlight

  // Status Colors
  static const green = Color(0xff00C076);
  static const red = Color(0xffF6465D);
  static const blue = Color(0xffFF9F2E);

  // Text
  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff68686E);

  // Dark Yellow Button Gradient
  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [
      Color(0xffFF9F2E),
      Color(0xffFF9F2E),
    ],
  );

  // Black Card Gradient
  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xff101010),
      Color(0xff101010),
    ],
  );
}

class RequestScreen extends StatefulWidget {
  final VoidCallback? onHistoryTap;

  const RequestScreen({
    super.key,
    this.onHistoryTap,
  });

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

  Future<void> _submitFinal(BuildContext sheetContext) async {
    if (_submitting) return;

    if (_selectedMerchant == null) {
      _snack("Please select a merchant", ok: false);
      return;
    }

    if (!_isOnlineMerchant(_selectedMerchant)) {
      _snack("Selected merchant is offline", ok: false);
      return;
    }

    if (mounted) {
      setState(() => _submitting = true);
    }

    final data = await ApiService.createRequestMultipart(
      amount: _amountCtrl.text.trim(),
      type: type,
      merchantId: _selectedMerchant!["id"].toString(),
      note: _noteCtrl.text.trim(),
    );

    if (!mounted) return;

    setState(() => _submitting = false);

    if (data["success"] != true) {
      _snack(
        data["message"]?.toString() ?? "Request failed",
        ok: false,
      );
      return;
    }

    final reqId =
        data["request"]?["id"]?.toString() ??
        data["request_id"]?.toString();

    final reqNo =
        data["request"]?["transaction_no"]?.toString() ??
        (reqId != null ? "TNS${reqId.padLeft(9, "0")}" : null);

    final merchant = Map<String, dynamic>.from(
      _selectedMerchant!,
    );

    final submittedAmount = _amountCtrl.text.trim();
    final submittedNote = _noteCtrl.text.trim();

    final requestItem = data["request"] is Map
        ? Map<String, dynamic>.from(data["request"])
        : <String, dynamic>{};

    requestItem.addAll({
      if (requestItem["id"] == null && reqId != null)
        "id": reqId,
      if (requestItem["transaction_no"] == null && reqNo != null)
        "transaction_no": reqNo,
      if (requestItem["type"] == null)
        "type": type,
      if (requestItem["amount"] == null)
        "amount": submittedAmount,
      if (requestItem["note"] == null)
        "note": submittedNote,
      if (requestItem["status"] == null)
        "status": "pending",
      if (requestItem["merchant_id"] == null)
        "merchant_id": merchant["id"],
      if (requestItem["merchant_name"] == null)
        "merchant_name": merchant["name"],
      if (requestItem["merchant_wallet_id"] == null)
        "merchant_wallet_id": merchant["wallet_id"],
      if (requestItem["merchant"] == null)
        "merchant": merchant,
      if (requestItem["created_at"] == null)
        "created_at": DateTime.now().toIso8601String(),
    });

    _amountCtrl.clear();
    _noteCtrl.clear();

    setState(() {
      _selectedMerchant = null;
      _lastRequestId = reqId;
      _lastMerchantUser = merchant;
    });

    // Close only the merchant bottom sheet.
    if (sheetContext.mounted) {
      Navigator.of(sheetContext).pop();
    }

    // Wait until the bottom-sheet closing animation finishes.
    await Future<void>.delayed(
      const Duration(milliseconds: 300),
    );

    if (!mounted) return;

    // Open the new request as a pending transaction.
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => TransactionDetailScreen(
          item: requestItem,
          sourceType: "request",
          user: Map<String, dynamic>.from(
            _user ?? const <String, dynamic>{},
          ),
        ),
      ),
    );

    if (!mounted) return;

    await _loadPage();
  }

  void _snack(String msg, {bool ok = true}) {
    if (!mounted) return;

    final messenger = ScaffoldMessenger.of(context);

    messenger
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          backgroundColor: ok ? _C.green : _C.red,
          behavior: SnackBarBehavior.fixed,
          content: Text(
            msg,
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      );
  }

  // ═══════════════════════════════════════════
  //  SMALL BALANCE STRIP (Sell only)
  // ═══════════════════════════════════════════
  Widget _smallBalanceStrip() {
    if (!_isSell) return const SizedBox.shrink();

    return FadeTransition(
      opacity: _fade,
      child: SlideTransition(
        position: _slide,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 18, 20, 0),
          child: Row(
            children: [
              const Icon(
                Icons.account_balance_wallet_outlined,
                color: _C.textSecondary,
                size: 17,
              ),
              const SizedBox(width: 9),
              Expanded(
                child: Text(
                  "Available USD Balance",
                  style: const TextStyle(
                    color: _C.textSecondary,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              Text(
                "\$${_balance.toStringAsFixed(2)}",
                style: const TextStyle(
                  color: _C.textPrimary,
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(width: 8),
              Icon(
                _isVerified
                    ? Icons.verified_rounded
                    : Icons.warning_amber_rounded,
                size: 15,
                color: _isVerified ? _C.green : _C.gold,
              ),
              const SizedBox(width: 10),
              Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: widget.onHistoryTap,
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    width: 34,
                    height: 34,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: _C.orange.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: _C.orange.withOpacity(0.30),
                        width: 0.8,
                      ),
                    ),
                    child: const Icon(
                      Icons.history_rounded,
                      color: _C.orange,
                      size: 19,
                    ),
                  ),
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
      margin: const EdgeInsets.fromLTRB(0, 22, 0, 0),
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
      decoration: const BoxDecoration(
        border: Border(
          bottom: BorderSide(
            color: _C.borderFaint,
            width: 1,
          ),
        ),
      ),
      child: Row(
        children: [
          _typeBtn("Sell USD", Icons.trending_down_rounded, "withdrawal"),
          const SizedBox(width: 28),
          _typeBtn("Buy USD", Icons.trending_up_rounded, "deposit"),
        ],
      ),
    );
  }

  Widget _typeBtn(String label, IconData icon, String value) {
    final active = type == value;

    return GestureDetector(
      onTap: () => setState(() {
        type = value;
        _selectedMerchant = null;
      }),
      behavior: HitTestBehavior.opaque,
      child: Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Column(
          children: [
            Row(
              children: [
                Icon(
                  icon,
                  size: 18,
                  color: active
                      ? _C.textPrimary
                      : _C.textSecondary,
                ),
                const SizedBox(width: 7),
                Text(
                  label,
                  style: TextStyle(
                    color: active
                        ? _C.textPrimary
                        : _C.textSecondary,
                    fontWeight: FontWeight.w800,
                    fontSize: 16,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              width: 82,
              height: 3,
              decoration: BoxDecoration(
                color: active
                    ? _C.orange
                    : Colors.transparent,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _formCard() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 28),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            "P2P Details",
            style: TextStyle(
              color: _C.textPrimary,
              fontSize: 14,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 20),
          _field(
            controller: _amountCtrl,
            label: "USD Amount",
            icon: Icons.attach_money_rounded,
            iconColor: _C.gold,
            keyboardType:
                const TextInputType.numberWithOptions(decimal: true),
            inputFormatters: [
              FilteringTextInputFormatter.allow(
                RegExp(r'^\d*\.?\d{0,8}'),
              ),
            ],
            onChanged: (_) => setState(() {}),
          ),
          const SizedBox(height: 18),
          _summaryBox(),
          const SizedBox(height: 18),
          _field(
            controller: _noteCtrl,
            label: "Note (optional)",
            icon: Icons.notes_rounded,
            iconColor: _C.textSecondary,
            maxLines: 3,
          ),
          const SizedBox(height: 24),
          _gradientBtn(
            label: _isSell ? "Continue Sell P2P" : "Continue Buy P2P",
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
    Color iconColor = _C.gold,
    TextInputType? keyboardType,
    List<TextInputFormatter>? inputFormatters,
    int maxLines = 1,
    Function(String)? onChanged,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      inputFormatters: inputFormatters,
      maxLines: maxLines,
      onChanged: onChanged,
      cursorColor: _C.orange,
      style: const TextStyle(
        color: Colors.white,
        fontSize: 14,
        fontWeight: FontWeight.w600,
      ),
      decoration: InputDecoration(
        isDense: true,
        labelText: label,
        labelStyle: const TextStyle(
          color: _C.textSecondary,
          fontSize: 13,
        ),
        floatingLabelStyle: const TextStyle(
          color: _C.orange,
          fontSize: 12,
        ),
        contentPadding: const EdgeInsets.symmetric(
          vertical: 13,
        ),
        enabledBorder: const UnderlineInputBorder(
          borderSide: BorderSide(
            color: _C.border,
            width: 1,
          ),
        ),
        focusedBorder: const UnderlineInputBorder(
          borderSide: BorderSide(
            color: _C.orange,
            width: 1.5,
          ),
        ),
        border: const UnderlineInputBorder(
          borderSide: BorderSide(
            color: _C.border,
          ),
        ),
      ),
    );
  }

  Widget _summaryBox() {
    return Column(
      children: [
        _summaryRow(
          "Type",
          _isSell ? "SELL USD" : "BUY USD",
          color: _isSell ? _C.red : _C.green,
        ),
        _summaryRow(
          "USD Amount",
          "\$${_amount.toStringAsFixed(2)}",
        ),
        _summaryRow(
          "USD Rate",
          "\$${_usdRate.toStringAsFixed(2)}",
        ),
        _summaryRow(
          "Converted INR",
          "₹${_converted.toStringAsFixed(2)}",
        ),
        _summaryRow(
          "Xynder Fee",
          "₹${_fee.toStringAsFixed(2)}",
        ),
        _summaryRow(
          "Network Fee",
          "₹${_netFee.toStringAsFixed(2)}",
        ),
        const Padding(
          padding: EdgeInsets.symmetric(vertical: 4),
          child: Divider(
            color: _C.borderFaint,
            height: 1,
            thickness: 1,
          ),
        ),
        _summaryRow(
          "Total INR",
          "₹${_total.toStringAsFixed(2)}",
          color: _C.gold,
          bold: true,
          large: true,
        ),
      ],
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
      padding: const EdgeInsets.symmetric(vertical: 7),
      child: Row(
        children: [
          Expanded(
            child: Text(
              k,
              style: TextStyle(
                color: _C.textSecondary,
                fontSize: large ? 13 : 12.5,
                fontWeight:
                    bold ? FontWeight.w700 : FontWeight.w500,
              ),
            ),
          ),
          const SizedBox(width: 16),
          Text(
            v,
            textAlign: TextAlign.right,
            style: TextStyle(
              color: color ?? _C.textPrimary,
              fontSize: large ? 16 : 13.5,
              fontWeight:
                  bold ? FontWeight.w900 : FontWeight.w700,
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
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: ElevatedButton(
        onPressed: loading ? null : onTap,
        style: ElevatedButton.styleFrom(
          elevation: 0,
          backgroundColor: _C.orange,
          disabledBackgroundColor:
              _C.orange.withOpacity(0.45),
          foregroundColor: Colors.black,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(6),
          ),
        ),
        child: loading
            ? const SizedBox(
                width: 21,
                height: 21,
                child: CircularProgressIndicator(
                  strokeWidth: 2.4,
                  color: Colors.black,
                ),
              )
            : Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (icon != null) ...[
                    Icon(
                      icon,
                      color: Colors.black,
                      size: 18,
                    ),
                    const SizedBox(width: 8),
                  ],
                  Text(
                    label,
                    style: const TextStyle(
                      color: Colors.black,
                      fontWeight: FontWeight.w900,
                      fontSize: 14,
                    ),
                  ),
                ],
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
                          color: Colors.white,
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
                          fontSize: 15,
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
                    label: _isSell ? "Submit Sell P2P" : "Submit Buy P2P",
                    icon: Icons.send_rounded,
                    loading: _submitting,
                    onTap: () => _submitFinal(ctx),
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
          _stripItem("Total INR", "₹${_total.toStringAsFixed(2)}", _C.gold),
        ],
      ),
    );
  }

  Widget _stripItem(String label, String value, Color color) {
    return Column(
      children: [
        Text(label,
            style: const TextStyle(color: _C.textSecondary, fontSize: 9)),
        const SizedBox(height: 3),
        Text(
          value,
          style: TextStyle(
            color: color,
            fontWeight: FontWeight.w900,
            fontSize: 11,
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
          color: selected ? const Color(0xff2A1600) : _C.bg,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: selected ? _C.gold : _C.border,
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
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      m["email"]?.toString() ?? "",
                      style: const TextStyle(
                        color: _C.textSecondary,
                        fontSize: 10,
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
                    fontSize: 9,
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
        "P2P Submitted!",
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
                ? "Your Sell USD P2P has been submitted successfully. Chat is open only while request is pending."
                : "Your Buy USD P2P has been submitted successfully. Chat is open only while request is pending.",
            textAlign: TextAlign.center,
            style: const TextStyle(color: _C.textSecondary, fontSize: 11),
          ),
          const SizedBox(height: 14),
          if (requestNo != null)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _C.bg,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: _C.border),
              ),
              child: Text(
                requestNo,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: _C.gold,
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                ),
              ),
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
            child: const Text(
              "Close",
              style: TextStyle(
                color: _C.textSecondary,
                fontWeight: FontWeight.w800,
              ),
            ),
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
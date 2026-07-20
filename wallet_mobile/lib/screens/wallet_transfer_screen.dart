import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/api_service.dart';
import 'transaction_detail_screen.dart';

class _C {
  static const bg = Color(0xff000000);
  static const divider = Color(0xff252525);
  static const panel = Color(0xff171717);

  static const gold = Color(0xffFF9F2E);
  static const green = Color(0xff00C076);
  static const red = Color(0xffF6465D);
  static const blue = Color(0xff60A5FA);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff737378);
}

class WalletTransferScreen extends StatefulWidget {
  final Map user;
  final Future<void> Function()? onSuccess;
  final VoidCallback? onHistoryTap;

  const WalletTransferScreen({
    super.key,
    required this.user,
    this.onSuccess,
    this.onHistoryTap,
  });

  @override
  State<WalletTransferScreen> createState() =>
      _WalletTransferScreenState();
}

class _WalletTransferScreenState extends State<WalletTransferScreen> {
  final walletCtrl = TextEditingController();
  final amountCtrl = TextEditingController();
  final noteCtrl = TextEditingController();

  bool loading = false;
  bool lookupLoading = false;
  String transferType = 'internal';

  Map<String, dynamic>? receiver;

  bool get isExternal => transferType == 'external';

  @override
  void dispose() {
    walletCtrl.dispose();
    amountCtrl.dispose();
    noteCtrl.dispose();
    super.dispose();
  }

  double toDouble(dynamic value) {
    return double.tryParse(value?.toString() ?? '0') ?? 0;
  }

  bool get isVerified {
    return widget.user['is_verified'] == true ||
        widget.user['is_verified'] == 1 ||
        widget.user['is_verified']?.toString() == '1';
  }

  void showSnack(String message, {bool success = true}) {
    if (!mounted) return;

    final messenger = ScaffoldMessenger.of(context);

    messenger
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          backgroundColor: success ? _C.green : _C.red,
          behavior: SnackBarBehavior.fixed,
          content: Text(
            message,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 14,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      );
  }

  void changeTransferType(String value) {
    if (loading || lookupLoading) return;

    setState(() {
      transferType = value;
      receiver = null;
      walletCtrl.clear();
    });
  }

  Future<void> lookupWallet() async {
    if (isExternal) return;

    final walletId = walletCtrl.text.trim();

    if (walletId.isEmpty) {
      showSnack('Enter receiver wallet address.', success: false);
      return;
    }

    setState(() {
      lookupLoading = true;
      receiver = null;
    });

    final res = await ApiService.walletLookup(walletId);

    if (!mounted) return;

    setState(() {
      lookupLoading = false;
    });

    if (res['success'] == true && res['user'] is Map) {
      setState(() {
        receiver = Map<String, dynamic>.from(res['user']);
      });
    } else {
      showSnack(
        res['message']?.toString() ?? 'Wallet not found.',
        success: false,
      );
    }
  }

  bool _validExternalWallet(String value) {
    return RegExp(r'^[A-Za-z0-9]+$').hasMatch(value);
  }

  Future<void> sendTransfer() async {
    if (loading) return;

    final walletId = walletCtrl.text.trim();
    final amountText = amountCtrl.text.trim();
    final amount = double.tryParse(amountText) ?? 0;
    final balance = toDouble(widget.user['balance']);

    if (walletId.isEmpty || amountText.isEmpty) {
      showSnack(
        'Wallet address and amount are required.',
        success: false,
      );
      return;
    }

    if (!isVerified) {
      showSnack(
        'Your account is not verified yet. Transfers are disabled.',
        success: false,
      );
      return;
    }

    if (amount < 1) {
      showSnack(
        'Transfer amount must be at least \$1.00.',
        success: false,
      );
      return;
    }

    if (amount > balance) {
      showSnack(
        'Insufficient wallet balance.',
        success: false,
      );
      return;
    }

    if (isExternal && !_validExternalWallet(walletId)) {
      showSnack(
        'External wallet address may contain letters and numbers only.',
        success: false,
      );
      return;
    }

    if (!isExternal && receiver == null) {
      showSnack(
        'Please check and verify the internal receiver first.',
        success: false,
      );
      return;
    }

    setState(() {
      loading = true;
    });

    final submittedWalletId = walletId;
    final submittedAmount = amountText;
    final submittedNote = noteCtrl.text.trim();
    final submittedType = transferType;
    final receiverSnapshot = receiver == null
        ? null
        : Map<String, dynamic>.from(receiver!);

    final res = await ApiService.walletTransfer(
      transferType: submittedType,
      receiverWalletId: submittedWalletId,
      amount: submittedAmount,
      note: submittedNote,
    );

    if (!mounted) return;

    setState(() {
      loading = false;
    });

    if (res['success'] == true) {
      final transferId =
          res['transfer']?['id']?.toString() ??
          res['transfer_id']?.toString();

      final transferNo =
          res['transfer']?['transaction_no']?.toString() ??
          (transferId != null
              ? 'TRA${transferId.padLeft(9, '0')}'
              : null);

      final transferItem = res['transfer'] is Map
          ? Map<String, dynamic>.from(res['transfer'])
          : <String, dynamic>{};

      transferItem.addAll({
        if (transferItem['id'] == null && transferId != null)
          'id': transferId,
        if (transferItem['transaction_no'] == null &&
            transferNo != null)
          'transaction_no': transferNo,
        if (transferItem['transfer_type'] == null)
          'transfer_type': submittedType,
        if (transferItem['amount'] == null)
          'amount': submittedAmount,
        if (transferItem['note'] == null)
          'note': submittedNote,
        if (transferItem['status'] == null)
          'status': 'completed',
        if (transferItem['sender_id'] == null)
          'sender_id': widget.user['id'],
        if (transferItem['sender_wallet_id'] == null)
          'sender_wallet_id': widget.user['wallet_id'],
        if (transferItem['sender_name'] == null)
          'sender_name': widget.user['name'],
        if (transferItem['receiver_id'] == null &&
            submittedType == 'internal')
          'receiver_id': receiverSnapshot?['id'],
        if (transferItem['receiver_wallet_id'] == null)
          'receiver_wallet_id': submittedWalletId,
        if (transferItem['receiver_name'] == null)
          'receiver_name': submittedType == 'external'
              ? 'External Wallet'
              : receiverSnapshot?['name'],
        if (transferItem['receiver_photo_url'] == null &&
            submittedType == 'internal')
          'receiver_photo_url': receiverSnapshot?['photo_url'],
        if (transferItem['receiver_photo'] == null &&
            submittedType == 'internal')
          'receiver_photo': receiverSnapshot?['photo'],
        if (transferItem['created_at'] == null)
          'created_at': DateTime.now().toIso8601String(),
      });

      walletCtrl.clear();
      amountCtrl.clear();
      noteCtrl.clear();

      setState(() {
        receiver = null;
      });

      if (widget.onSuccess != null) {
        await widget.onSuccess!();
      }

      if (!mounted) return;

      await Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => TransactionDetailScreen(
            item: transferItem,
            sourceType: 'transfer',
            user: Map<String, dynamic>.from(widget.user),
          ),
        ),
      );
    } else {
      showSnack(
        res['message']?.toString() ?? 'Transfer failed.',
        success: false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 30),
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _header(),
              const SizedBox(height: 22),
              _myWalletSummary(),
              const SizedBox(height: 22),
              _transferModeSelector(),
              const SizedBox(height: 22),
              _sectionTitle(
                isExternal ? 'External Wallet' : 'Receiver',
              ),
              const SizedBox(height: 8),
              _field(
                controller: walletCtrl,
                label: isExternal
                    ? 'External Wallet Address'
                    : 'Receiver Wallet Address',
                icon: isExternal
                    ? Icons.public_rounded
                    : Icons.account_balance_wallet_outlined,
                inputFormatters: isExternal
                    ? [
                        FilteringTextInputFormatter.allow(
                          RegExp(r'[A-Za-z0-9]'),
                        ),
                      ]
                    : null,
                onChanged: (_) {
                  if (!isExternal && receiver != null) {
                    setState(() {
                      receiver = null;
                    });
                  }
                },
              ),
              if (isExternal) ...[
                const SizedBox(height: 8),
                const Text(
                  'No receiver lookup. The amount will only be deducted from your wallet balance.',
                  style: TextStyle(
                    color: _C.textSecondary,
                    fontSize: 12,
                    height: 1.4,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ] else ...[
                const SizedBox(height: 6),
                _checkReceiverButton(),
                if (receiver != null) ...[
                  const SizedBox(height: 12),
                  _receiverDetails(),
                ],
              ],
              const SizedBox(height: 18),
              _field(
                controller: amountCtrl,
                label: 'USD Amount',
                icon: Icons.attach_money_rounded,
                keyboardType:
                    const TextInputType.numberWithOptions(
                  decimal: true,
                ),
                inputFormatters: [
                  FilteringTextInputFormatter.allow(
                    RegExp(r'^\d*\.?\d{0,8}'),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              _field(
                controller: noteCtrl,
                label: 'Note (optional)',
                icon: Icons.notes_rounded,
                maxLines: 2,
              ),
              const SizedBox(height: 26),
              _transferButton(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _header() {
    return Row(
      children: [
        Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: _C.gold.withOpacity(0.14),
            shape: BoxShape.circle,
          ),
          child: const Icon(
            Icons.arrow_upward_rounded,
            color: _C.gold,
            size: 19,
          ),
        ),
        const SizedBox(width: 11),
        const Expanded(
          child: Text(
            'Wallet Transfer',
            style: TextStyle(
              color: _C.textPrimary,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
        Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: widget.onHistoryTap,
            borderRadius: BorderRadius.circular(10),
            child: Container(
              width: 36,
              height: 36,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: _C.gold.withOpacity(0.12),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: _C.gold.withOpacity(0.30),
                  width: 0.8,
                ),
              ),
              child: const Icon(
                Icons.history_rounded,
                color: _C.gold,
                size: 20,
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _transferModeSelector() {
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: _C.panel,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: _C.divider),
      ),
      child: Row(
        children: [
          Expanded(
            child: _modeButton(
              value: 'internal',
              label: 'Internal',
              icon: Icons.people_alt_outlined,
            ),
          ),
          Expanded(
            child: _modeButton(
              value: 'external',
              label: 'External',
              icon: Icons.public_rounded,
            ),
          ),
        ],
      ),
    );
  }

  Widget _modeButton({
    required String value,
    required String label,
    required IconData icon,
  }) {
    final selected = transferType == value;

    return InkWell(
      onTap: () => changeTransferType(value),
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        height: 40,
        decoration: BoxDecoration(
          color: selected ? _C.gold : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 16,
              color: selected ? Colors.black : _C.textSecondary,
            ),
            const SizedBox(width: 7),
            Text(
              label,
              style: TextStyle(
                color: selected ? Colors.black : _C.textSecondary,
                fontSize: 13,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _myWalletSummary() {
    final walletId =
        widget.user['wallet_id']?.toString() ?? 'Not available';
    final balance = toDouble(widget.user['balance']);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionTitle('My Wallet'),
        const SizedBox(height: 10),
        _detailRow(
          label: 'Wallet ID',
          value: walletId,
        ),
        const SizedBox(height: 9),
        _detailRow(
          label: 'Balance',
          value: '\$${balance.toStringAsFixed(2)}',
          valueColor: _C.gold,
        ),
        const SizedBox(height: 14),
        const Divider(
          color: _C.divider,
          height: 1,
          thickness: 1,
        ),
      ],
    );
  }

  Widget _receiverDetails() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _detailRow(
          label: 'Receiver',
          value: receiver?['name']?.toString() ?? 'User',
        ),
        const SizedBox(height: 8),
        _detailRow(
          label: 'Email',
          value: receiver?['email']?.toString() ?? '-',
          valueColor: _C.textSecondary,
        ),
        const SizedBox(height: 8),
        _detailRow(
          label: 'Status',
          value: 'Receiver Found',
          valueColor: _C.green,
        ),
        const SizedBox(height: 14),
        const Divider(
          color: _C.divider,
          height: 1,
          thickness: 1,
        ),
      ],
    );
  }

  Widget _detailRow({
    required String label,
    required String value,
    Color valueColor = _C.textPrimary,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 92,
          child: Text(
            label,
            style: const TextStyle(
              color: _C.textSecondary,
              fontSize: 14,
              fontWeight: FontWeight.w400,
            ),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            value,
            textAlign: TextAlign.right,
            overflow: TextOverflow.ellipsis,
            maxLines: 1,
            style: TextStyle(
              color: valueColor,
              fontSize: 14,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ],
    );
  }

  Widget _sectionTitle(String title) {
    return Text(
      title,
      style: const TextStyle(
        color: _C.textSecondary,
        fontSize: 14,
        fontWeight: FontWeight.w500,
      ),
    );
  }

  Widget _field({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    List<TextInputFormatter>? inputFormatters,
    int maxLines = 1,
    ValueChanged<String>? onChanged,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      inputFormatters: inputFormatters,
      maxLines: maxLines,
      onChanged: onChanged,
      cursorColor: _C.gold,
      style: const TextStyle(
        color: _C.textPrimary,
        fontSize: 14,
        fontWeight: FontWeight.w600,
      ),
      decoration: InputDecoration(
        isDense: true,
        contentPadding:
            const EdgeInsets.symmetric(vertical: 12),
        labelText: label,
        labelStyle: const TextStyle(
          color: _C.textSecondary,
          fontSize: 14,
        ),
        floatingLabelStyle: const TextStyle(
          color: _C.gold,
          fontSize: 12,
        ),
        enabledBorder: const UnderlineInputBorder(
          borderSide: BorderSide(color: _C.divider),
        ),
        focusedBorder: const UnderlineInputBorder(
          borderSide: BorderSide(
            color: _C.gold,
            width: 1.2,
          ),
        ),
        border: const UnderlineInputBorder(
          borderSide: BorderSide(color: _C.divider),
        ),
      ),
    );
  }

  Widget _checkReceiverButton() {
    return Align(
      alignment: Alignment.centerRight,
      child: TextButton.icon(
        onPressed: lookupLoading ? null : lookupWallet,
        style: TextButton.styleFrom(
          foregroundColor: _C.gold,
          padding: const EdgeInsets.symmetric(
            horizontal: 4,
            vertical: 6,
          ),
          textStyle: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w700,
          ),
        ),
        icon: lookupLoading
            ? const SizedBox(
                width: 13,
                height: 13,
                child: CircularProgressIndicator(
                  strokeWidth: 1.7,
                  color: _C.gold,
                ),
              )
            : const Icon(
                Icons.search_rounded,
                size: 16,
              ),
        label: Text(
          lookupLoading
              ? 'Checking...'
              : 'Check Receiver',
        ),
      ),
    );
  }

  Widget _transferButton() {
    return SizedBox(
      width: double.infinity,
      height: 46,
      child: ElevatedButton(
        onPressed: loading ? null : sendTransfer,
        style: ElevatedButton.styleFrom(
          backgroundColor: _C.gold,
          disabledBackgroundColor:
              _C.gold.withOpacity(0.45),
          foregroundColor: Colors.black,
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
          ),
        ),
        child: loading
            ? const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(
                  color: Colors.black,
                  strokeWidth: 2,
                ),
              )
            : Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    isExternal
                        ? Icons.public_rounded
                        : Icons.arrow_upward_rounded,
                    size: 17,
                  ),
                  const SizedBox(width: 7),
                  Text(
                    isExternal
                        ? 'Send to External Wallet'
                        : 'Transfer Now',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}

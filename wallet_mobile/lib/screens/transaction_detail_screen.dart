import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/api_service.dart';
import 'chat_screen.dart';

class TransactionDetailScreen extends StatefulWidget {
  final Map item;
  final String sourceType;
  final Map user;

  const TransactionDetailScreen({
    super.key,
    required this.item,
    required this.sourceType,
    required this.user,
  });

  @override
  State<TransactionDetailScreen> createState() =>
      _TransactionDetailScreenState();
}

class _TransactionDetailScreenState
    extends State<TransactionDetailScreen> {
  static const Color bg = Color(0xff000000);
  static const Color surface = Color(0xff101010);
  static const Color border = Color(0xff252525);

  static const Color green = Color(0xff00C076);
  static const Color red = Color(0xffF6465D);
  static const Color amber = Color(0xffFF9F2E);
  static const Color grey = Color(0xff8E8E93);

  static const Color textPrimary = Colors.white;
  static const Color textSecondary = Color(0xff68686E);

  late Map<String, dynamic> item;
  bool _closingRequest = false;
  bool _bankExpanded = false;

  @override
  void initState() {
    super.initState();
    item = Map<String, dynamic>.from(widget.item);
  }

  // ---------------------------------------------------------------------------
  // BASIC HELPERS
  // ---------------------------------------------------------------------------

  String value(dynamic input) {
    final String text = input?.toString().trim() ?? '';

    if (text.isEmpty || text.toLowerCase() == 'null') {
      return '—';
    }

    return text;
  }

  double number(dynamic input) {
    return double.tryParse(
          input?.toString().replaceAll(',', '').trim() ?? '0',
        ) ??
        0;
  }

  dynamic nestedValue(
    dynamic map,
    String key,
  ) {
    if (map is Map) {
      return map[key];
    }

    return null;
  }

  bool get isTransfer {
    return widget.sourceType.toLowerCase() == 'transfer';
  }

  String get currentRole {
    return widget.user['role']?.toString().toLowerCase().trim() ??
        'client';
  }

  String get requestStatus {
    if (isTransfer) {
      return 'completed';
    }

    return item['status']
            ?.toString()
            .toLowerCase()
            .trim() ??
        'pending';
  }

  bool get isPending {
    return !isTransfer && requestStatus == 'pending';
  }

  bool get isApproved {
    return !isTransfer &&
        (requestStatus == 'approved' ||
            requestStatus == 'completed');
  }

  bool get isRejected {
    return !isTransfer && requestStatus == 'rejected';
  }

  bool get isClosed {
    return !isTransfer &&
        (requestStatus == 'closed' ||
            requestStatus == 'cancelled' ||
            requestStatus == 'canceled');
  }

  bool get isWithdrawal {
    final String type =
        item['type']?.toString().toLowerCase().trim() ?? '';

    return type == 'withdrawal' ||
        type == 'sell' ||
        type == 'selling';
  }

  // Chat is available for all request statuses.
  // Wallet transfers do not have chat.
  bool get canOpenChat {
    if (isTransfer) {
      return false;
    }

    final String requestId =
        item['id']?.toString().trim() ?? '';

    return requestId.isNotEmpty &&
        requestId.toLowerCase() != 'null';
  }

  // ---------------------------------------------------------------------------
  // AMOUNT DETAILS
  // ---------------------------------------------------------------------------

  double get usdAmount {
    return number(item['amount']);
  }

  double get inrRate {
    return number(
      item['inr_rate'] ??
          item['conversion_rate'] ??
          item['rate_inr'] ??
          item['rate'],
    );
  }

  double get convertedAmount {
    final double saved = number(
      item['converted_amount'] ??
          item['converted_inr'] ??
          item['inr_amount'],
    );

    if (saved > 0) {
      return saved;
    }

    return usdAmount * inrRate;
  }

  double get xynderFee {
    return number(
      item['xynder_fee'] ??
          item['platform_fee'] ??
          item['service_fee'],
    );
  }

  double get networkFee {
    return number(item['network_fee']);
  }

  double get totalFee {
    return xynderFee + networkFee;
  }

  double get finalTotal {
    final double saved = number(
      item['total_amount'] ??
          item['final_amount'] ??
          item['payable_amount'],
    );

    if (saved > 0) {
      return saved;
    }

    if (isWithdrawal) {
      return convertedAmount - totalFee;
    }

    return convertedAmount + totalFee;
  }

  String money(
    double amount,
    String symbol,
  ) {
    return '$symbol${amount.toStringAsFixed(2)}';
  }

  String quantityText(double amount) {
    if (amount == amount.truncateToDouble()) {
      return '${amount.toStringAsFixed(0)} USDT';
    }

    return '${amount.toStringAsFixed(4)} USDT';
  }

  String formatDate(dynamic raw) {
    final String text = raw?.toString().trim() ?? '';

    if (text.isEmpty) {
      return '—';
    }

    try {
      final DateTime date = DateTime.parse(
        text.replaceFirst(' ', 'T'),
      ).toLocal();

      String pad(int number) {
        return number.toString().padLeft(2, '0');
      }

      return '${date.year}-'
          '${pad(date.month)}-'
          '${pad(date.day)} '
          '${pad(date.hour)}:'
          '${pad(date.minute)}:'
          '${pad(date.second)}';
    } catch (_) {
      return text;
    }
  }

  // ---------------------------------------------------------------------------
  // HEADER AND STATUS
  // ---------------------------------------------------------------------------

  String get headerTitle {
    if (isTransfer) {
      return 'Wallet Transfer';
    }

    return isWithdrawal ? 'Sell USDT' : 'Buy USDT';
  }

  IconData get headerIcon {
    if (isTransfer) {
      return Icons.swap_horiz_rounded;
    }

    return isWithdrawal
        ? Icons.arrow_upward_rounded
        : Icons.arrow_downward_rounded;
  }

  Color get headerIconColor {
    if (isTransfer) {
      return amber;
    }

    return isWithdrawal ? red : green;
  }

  String get statusTitle {
    if (isTransfer) {
      return 'Completed';
    }

    switch (requestStatus) {
      case 'approved':
      case 'completed':
        return 'Completed';

      case 'rejected':
        return 'Rejected';

      case 'closed':
      case 'cancelled':
      case 'canceled':
        return 'Closed';

      default:
        return 'Pending';
    }
  }

  String get statusDescription {
    if (isTransfer) {
      return 'The wallet transfer was completed successfully.';
    }

    switch (requestStatus) {
      case 'approved':
      case 'completed':
        return 'This order has been completed. You can open the connected user chat and view the transaction conversation.';

      case 'rejected':
        return 'This order was rejected. You can still open the connected user chat and view the previous conversation.';

      case 'closed':
      case 'cancelled':
      case 'canceled':
        return 'This order has been closed. You can still open the connected user chat and view the previous conversation.';

      default:
        return 'Complete the transaction within the available time. Use chat only for transaction-related communication.';
    }
  }

  Color get statusColor {
    if (isTransfer) {
      return green;
    }

    switch (requestStatus) {
      case 'approved':
      case 'completed':
        return green;

      case 'rejected':
        return red;

      case 'closed':
      case 'cancelled':
      case 'canceled':
        return grey;

      default:
        return amber;
    }
  }

  String get transactionNumber {
    final String saved =
        item['transaction_no']?.toString().trim() ?? '';

    if (saved.isNotEmpty &&
        saved.toLowerCase() != 'null') {
      return saved;
    }

    final String id = value(item['id']);

    return isTransfer
        ? 'TRA${id.padLeft(9, '0')}'
        : 'TNS${id.padLeft(9, '0')}';
  }

  String get walletAddress {
    if (isTransfer) {
      final String myId =
          widget.user['id']?.toString() ?? '';

      final String senderId =
          item['sender_id']?.toString() ?? '';

      if (senderId == myId) {
        return value(
          item['receiver_wallet_id'] ??
              item['receiver_id'],
        );
      }

      return value(
        item['sender_wallet_id'] ??
            item['sender_id'],
      );
    }

    return value(
      item['merchant_wallet_id'] ??
          nestedValue(item['merchant'], 'wallet_id') ??
          item['merchant_id'],
    );
  }

  String get paymentMethod {
    final String method = value(
      item['payment_method'] ?? item['method'],
    );

    return method == '—' ? 'Bank Transfer' : method;
  }

  // ---------------------------------------------------------------------------
  // CONNECTED USER DETAILS
  // ---------------------------------------------------------------------------

  Map<String, dynamic> get otherParty {
    if (isTransfer) {
      final String myId =
          widget.user['id']?.toString() ?? '';

      final String senderId =
          item['sender_id']?.toString() ?? '';

      final bool currentUserIsSender = senderId == myId;

      return {
        'id': currentUserIsSender
            ? item['receiver_id']
            : item['sender_id'],
        'name': currentUserIsSender
            ? (item['receiver_name'] ?? 'User')
            : (item['sender_name'] ?? 'User'),
        'wallet_id': currentUserIsSender
            ? (item['receiver_wallet_id'] ??
                item['receiver_id'])
            : (item['sender_wallet_id'] ??
                item['sender_id']),
        'photo_url': currentUserIsSender
            ? item['receiver_photo_url']
            : item['sender_photo_url'],
        'photo': currentUserIsSender
            ? item['receiver_photo']
            : item['sender_photo'],
        'role': 'user',
        'role_label': 'User',
      };
    }

    if (currentRole == 'merchant') {
      return {
        'id': item['client_id'] ??
            item['user_id'] ??
            nestedValue(item['client'], 'id') ??
            nestedValue(item['user'], 'id'),
        'name': item['client_name'] ??
            item['user_name'] ??
            item['customer_name'] ??
            nestedValue(item['client'], 'name') ??
            nestedValue(item['user'], 'name') ??
            'Client',
        'wallet_id': item['client_wallet_id'] ??
            item['user_wallet_id'] ??
            nestedValue(item['client'], 'wallet_id') ??
            nestedValue(item['user'], 'wallet_id'),
        'photo_url': item['client_photo_url'] ??
            item['user_photo_url'] ??
            nestedValue(item['client'], 'photo_url') ??
            nestedValue(item['user'], 'photo_url'),
        'photo': item['client_photo'] ??
            item['user_photo'] ??
            nestedValue(item['client'], 'photo') ??
            nestedValue(item['user'], 'photo'),
        'role': 'client',
        'role_label': 'Client',
      };
    }

    return {
      'id': item['merchant_id'] ??
          nestedValue(item['merchant'], 'id'),
      'name': item['merchant_name'] ??
          item['merchant_username'] ??
          nestedValue(item['merchant'], 'name') ??
          nestedValue(item['merchant'], 'username') ??
          'Merchant',
      'username': item['merchant_username'] ??
          nestedValue(item['merchant'], 'username'),
      'wallet_id': item['merchant_wallet_id'] ??
          nestedValue(item['merchant'], 'wallet_id'),
      'photo_url': item['merchant_photo_url'] ??
          nestedValue(item['merchant'], 'photo_url'),
      'photo': item['merchant_photo'] ??
          nestedValue(item['merchant'], 'photo'),
      'is_online':
          nestedValue(item['merchant'], 'is_online'),
      'role': 'merchant',
      'role_label': 'Merchant',
    };
  }

  String get connectedUserName {
    final String username =
        value(otherParty['username']);

    if (username != '—') {
      return username;
    }

    final String name = value(otherParty['name']);

    if (name != '—') {
      return name;
    }

    return currentRole == 'merchant' ? 'Client' : 'Merchant';
  }

  String get otherPartyRole {
    final String role =
        value(otherParty['role_label']);

    if (role != '—') {
      return role;
    }

    return isWithdrawal ? 'Buyer' : 'Seller';
  }

  // The button displays only the connected username, no icon.
  String get chatButtonText {
    return 'Contact $connectedUserName';
  }

  // ---------------------------------------------------------------------------
  // PAYMENT ACCOUNT (who receives the INR)
  // ---------------------------------------------------------------------------
  //
  // Buy USD  (deposit):    client sends INR  -> merchant's bank / UPI
  // Sell USD (withdrawal): merchant sends INR -> client's bank / UPI

  bool get payeeIsMe {
    if (isWithdrawal) {
      return currentRole == 'client';
    }

    return currentRole == 'merchant';
  }

  Map<String, dynamic> get payee {
    dynamic source;

    if (payeeIsMe) {
      source = widget.user;
    } else if (isWithdrawal) {
      source = item['user'] ?? item['client'];
    } else {
      source = item['merchant'];
    }

    if (source is Map) {
      return Map<String, dynamic>.from(source);
    }

    return <String, dynamic>{};
  }

  Map<String, dynamic> get payeeBank {
    final dynamic bank = payee['default_bank'];

    if (bank is Map) {
      return Map<String, dynamic>.from(bank);
    }

    // Fallback to the old bank fields on the user.
    return {
      'bank_name': payee['bank_name'],
      'branch': payee['branch'],
      'account_number': payee['account_number'],
      'account_type': payee['account_type'],
      'ifsc': payee['ifsc'],
    };
  }

  bool get hasBank {
    return value(payeeBank['account_number']) != '—' ||
        value(payeeBank['bank_name']) != '—';
  }

  bool get hasUpi {
    return value(payee['upi_id']) != '—';
  }

  String get upiQrUrl {
    return ApiService.fixUrl(payee['upi_qr_url']);
  }

  String get payeeTitle {
    if (payeeIsMe) {
      return isWithdrawal
          ? 'You receive INR in'
          : 'Client sends INR to you';
    }

    return isWithdrawal
        ? 'Send INR to client'
        : 'Send INR to merchant';
  }

  String maskedAccount(String account) {
    if (account == '—') {
      return '';
    }

    final String clean = account.replaceAll(' ', '');

    if (clean.length <= 4) {
      return clean;
    }

    return '••••${clean.substring(clean.length - 4)}';
  }

  // ---------------------------------------------------------------------------
  // OPEN CHAT
  // ---------------------------------------------------------------------------

  void openChat() {
    if (!canOpenChat) {
      showSnack(
        'Chat is unavailable for this transaction.',
        success: false,
      );
      return;
    }

    final String requestId =
        item['id']?.toString().trim() ?? '';

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ChatScreen(
          chatType: 'request',
          chatId: requestId,
          otherUser: Map<String, dynamic>.from(
            otherParty,
          ),
        ),
      ),
    );
  }

  void copyText(String text) {
    if (text == '—') {
      return;
    }

    Clipboard.setData(
      ClipboardData(text: text),
    );

    showSnack('Copied');
  }

  void showSnack(
    String message, {
    bool success = true,
  }) {
    if (!mounted) {
      return;
    }

    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text(
            message,
            style: const TextStyle(
              fontWeight: FontWeight.w700,
            ),
          ),
          backgroundColor: success ? green : red,
          behavior: SnackBarBehavior.fixed,
        ),
      );
  }

  void showUpiQr() {
    if (upiQrUrl.isEmpty) {
      return;
    }

    showDialog(
      context: context,
      builder: (dialogContext) {
        return Dialog(
          backgroundColor: surface,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: border),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  value(payee['upi_name']) != '—'
                      ? value(payee['upi_name'])
                      : 'UPI QR',
                  style: const TextStyle(
                    color: textPrimary,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    color: Colors.white,
                    padding: const EdgeInsets.all(8),
                    child: Image.network(
                      upiQrUrl,
                      width: 220,
                      height: 220,
                      fit: BoxFit.contain,
                      errorBuilder: (_, __, ___) => const SizedBox(
                        width: 220,
                        height: 220,
                        child: Center(
                          child: Text(
                            'QR image not available',
                            style: TextStyle(color: Colors.black54),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                TextButton(
                  onPressed: () => Navigator.of(dialogContext).pop(),
                  child: const Text(
                    'Close',
                    style: TextStyle(
                      color: amber,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  // ---------------------------------------------------------------------------
  // CLOSE TRANSACTION (CLIENT ONLY)
  // ---------------------------------------------------------------------------

  // Only the client who owns this request sees the Close Transaction button,
  // and only while the request is still pending. The backend
  // (AuthController@merchantCloseRequest) independently re-verifies both
  // the "pending" status and request ownership, so this is UI gating only.
  bool get canClientCloseRequest {
    if (isTransfer || !isPending) {
      return false;
    }

    return currentRole == 'client';
  }

  Future<void> confirmCloseRequest() async {
    if (!canClientCloseRequest || _closingRequest) {
      return;
    }

    final bool? confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: surface,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
            side: const BorderSide(color: border),
          ),
          title: const Text(
            'Close Transaction?',
            style: TextStyle(
              color: textPrimary,
              fontSize: 18,
              fontWeight: FontWeight.w800,
            ),
          ),
          content: const Text(
            'Are you sure you want to close this transaction?',
            style: TextStyle(
              color: textSecondary,
              fontSize: 13,
              height: 1.45,
              fontWeight: FontWeight.w500,
            ),
          ),
          actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(dialogContext).pop(false);
              },
              child: const Text(
                'Cancel',
                style: TextStyle(
                  color: textSecondary,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.of(dialogContext).pop(true);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: amber,
                foregroundColor: Colors.black,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              child: const Text(
                'Yes, Close Transaction',
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
          ],
        );
      },
    );

    if (confirmed == true) {
      await closeCurrentRequest();
    }
  }

  Future<void> closeCurrentRequest() async {
    final String requestId =
        item['id']?.toString().trim() ?? '';

    if (requestId.isEmpty || requestId.toLowerCase() == 'null') {
      showSnack(
        'Request ID is unavailable.',
        success: false,
      );
      return;
    }

    setState(() {
      _closingRequest = true;
    });

    final Map<String, dynamic> response =
        await ApiService.closeRequest(requestId);

    if (!mounted) {
      return;
    }

    setState(() {
      _closingRequest = false;
    });

    if (response['success'] != true) {
      showSnack(
        response['message']?.toString() ??
            'Unable to close this transaction.',
        success: false,
      );
      return;
    }

    final dynamic responseRequest = response['request'];

    setState(() {
      if (responseRequest is Map) {
        item = Map<String, dynamic>.from(responseRequest);
      } else {
        item['status'] = 'closed';
      }
    });

    showSnack(
      response['message']?.toString() ??
          'The transaction is now closed.',
    );
  }

  Widget inlineCloseRequestButton() {
    return Align(
      alignment: Alignment.centerLeft,
      child: SizedBox(
        height: 38,
        child: ElevatedButton(
          onPressed:
              _closingRequest ? null : confirmCloseRequest,
          style: ElevatedButton.styleFrom(
            elevation: 0,
            backgroundColor: amber,
            disabledBackgroundColor:
                amber.withOpacity(0.45),
            foregroundColor: Colors.black,
            padding: const EdgeInsets.symmetric(
              horizontal: 20,
              vertical: 0,
            ),
            minimumSize: const Size(0, 38),
            tapTargetSize:
                MaterialTapTargetSize.shrinkWrap,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          ),
          child: _closingRequest
              ? const SizedBox(
                  width: 17,
                  height: 17,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: Colors.black,
                  ),
                )
              : const Text(
                  'Close Transaction',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                  ),
                ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // UI
  // ---------------------------------------------------------------------------

  Widget progressLine() {
    final bool completed = isTransfer || isApproved;
    final bool failed = isRejected || isClosed;

    final Color activeColor = failed
        ? red
        : completed
            ? green
            : amber;

    return Row(
      children: [
        progressPart(activeColor),
        const SizedBox(width: 7),
        progressPart(
          completed || failed ? activeColor : border,
        ),
        const SizedBox(width: 7),
        progressPart(
          completed || failed ? activeColor : border,
        ),
      ],
    );
  }

  Widget progressPart(Color color) {
    return Expanded(
      child: Container(
        height: 3,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(10),
        ),
      ),
    );
  }

  Widget statusHeader() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        progressLine(),
        const SizedBox(height: 15),
        Text(
          statusTitle,
          style: TextStyle(
            color: statusColor,
            fontSize: 29,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 7),
        Text(
          isTransfer
              ? 'Transferred ${quantityText(usdAmount)}'
              : '${isWithdrawal ? 'Sold' : 'Bought'} '
                  '${quantityText(usdAmount)}',
          style: const TextStyle(
            color: textSecondary,
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 14),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(13),
          decoration: BoxDecoration(
            color: surface,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Text(
            statusDescription,
            style: const TextStyle(
              color: textSecondary,
              fontSize: 12,
              height: 1.4,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
      ],
    );
  }

  Widget orderHeader() {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Container(
          width: 34,
          height: 34,
          decoration: BoxDecoration(
            color: headerIconColor.withOpacity(0.15),
            shape: BoxShape.circle,
          ),
          child: Icon(
            headerIcon,
            color: headerIconColor,
            size: 17,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            headerTitle,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: textPrimary,
              fontSize: 18,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),

        // Connected username button — text only, no icon.
        if (!isTransfer && canOpenChat)
          Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: openChat,
              borderRadius: BorderRadius.circular(30),
              child: ConstrainedBox(
                constraints: const BoxConstraints(
                  maxWidth: 185,
                ),
                child: Ink(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 10,
                  ),
                  decoration: BoxDecoration(
                    color: amber,
                    borderRadius: BorderRadius.circular(30),
                  ),
                  child: Text(
                    chatButtonText,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Colors.black,
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }

  Widget detailRow(
    String label,
    String data, {
    bool copyable = false,
    Color? dataColor,
    bool bold = false,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 9),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 4,
            child: Text(
              label,
              style: const TextStyle(
                color: textSecondary,
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            flex: 6,
            child: GestureDetector(
              onTap: copyable
                  ? () => copyText(data)
                  : null,
              behavior: HitTestBehavior.opaque,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.end,
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  Flexible(
                    child: Text(
                      data,
                      textAlign: TextAlign.right,
                      style: TextStyle(
                        color: dataColor ?? textPrimary,
                        fontSize: 15,
                        fontWeight: bold
                            ? FontWeight.w800
                            : FontWeight.w600,
                        fontFeatures: const [
                          FontFeature.tabularFigures(),
                        ],
                      ),
                    ),
                  ),
                  if (copyable && data != '—') ...[
                    const SizedBox(width: 7),
                    const Icon(
                      Icons.copy_rounded,
                      color: Colors.white54,
                      size: 15,
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget divider() {
    return Container(
      height: 1,
      margin: const EdgeInsets.only(
        top: 6,
        bottom: 17,
      ),
      color: border,
    );
  }

  // Small row used inside the bank card.
  Widget bankLine(
    String label,
    String data, {
    bool copyable = false,
  }) {
    if (data == '—') {
      return const SizedBox.shrink();
    }

    return InkWell(
      onTap: copyable ? () => copyText(data) : null,
      borderRadius: BorderRadius.circular(6),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 5),
        child: Row(
          children: [
            SizedBox(
              width: 82,
              child: Text(
                label,
                style: const TextStyle(
                  color: textSecondary,
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            Expanded(
              child: Text(
                data,
                textAlign: TextAlign.right,
                style: const TextStyle(
                  color: textPrimary,
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  fontFeatures: [
                    FontFeature.tabularFigures(),
                  ],
                ),
              ),
            ),
            if (copyable) ...[
              const SizedBox(width: 6),
              const Icon(
                Icons.copy_rounded,
                color: Colors.white54,
                size: 13,
              ),
            ],
          ],
        ),
      ),
    );
  }

  // Compact bank / UPI card. Collapsed = one small row. Tap to see details.
  Widget payeeBankCard() {
    final Map<String, dynamic> bank = payeeBank;

    final String bankName = value(bank['bank_name']);
    final String account = value(bank['account_number']);
    final String upiId = value(payee['upi_id']);

    String summary;

    if (hasBank) {
      final String masked = maskedAccount(account);
      summary = bankName != '—'
          ? (masked.isNotEmpty ? '$bankName  $masked' : bankName)
          : masked;
    } else if (hasUpi) {
      summary = 'UPI: $upiId';
    } else {
      summary = payeeIsMe
          ? 'No bank or UPI added. Add one in Payment Methods.'
          : 'No bank or UPI added yet. Please ask in chat.';
    }

    final bool hasDetails = hasBank || hasUpi;

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: surface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: border),
      ),
      child: Column(
        children: [
          InkWell(
            onTap: hasDetails
                ? () => setState(() => _bankExpanded = !_bankExpanded)
                : null,
            borderRadius: BorderRadius.circular(10),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(12, 10, 10, 10),
              child: Row(
                children: [
                  Container(
                    width: 30,
                    height: 30,
                    decoration: BoxDecoration(
                      color: amber.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.account_balance_rounded,
                      color: amber,
                      size: 16,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          payeeTitle,
                          style: const TextStyle(
                            color: textSecondary,
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          summary,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: hasDetails ? textPrimary : textSecondary,
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (hasDetails)
                    AnimatedRotation(
                      turns: _bankExpanded ? 0.5 : 0,
                      duration: const Duration(milliseconds: 180),
                      child: const Icon(
                        Icons.keyboard_arrow_down_rounded,
                        color: textSecondary,
                        size: 20,
                      ),
                    ),
                ],
              ),
            ),
          ),
          AnimatedCrossFade(
            duration: const Duration(milliseconds: 180),
            crossFadeState: _bankExpanded
                ? CrossFadeState.showSecond
                : CrossFadeState.showFirst,
            firstChild: const SizedBox(width: double.infinity),
            secondChild: Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(12, 2, 12, 10),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: border)),
              ),
              child: Column(
                children: [
                  const SizedBox(height: 6),
                  bankLine('Name', value(payee['name'])),
                  bankLine('Bank', bankName),
                  bankLine('Account', account, copyable: true),
                  bankLine('IFSC', value(bank['ifsc']), copyable: true),
                  bankLine('Branch', value(bank['branch'])),
                  bankLine('Type', value(bank['account_type'])),
                  bankLine('UPI ID', upiId, copyable: true),
                  if (upiQrUrl.isNotEmpty)
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton.icon(
                        onPressed: showUpiQr,
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                          ),
                          minimumSize: const Size(0, 30),
                          tapTargetSize:
                              MaterialTapTargetSize.shrinkWrap,
                        ),
                        icon: const Icon(
                          Icons.qr_code_2_rounded,
                          color: amber,
                          size: 16,
                        ),
                        label: const Text(
                          'View UPI QR',
                          style: TextStyle(
                            color: amber,
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget paymentMethodSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Text(
              'Payment Method',
              style: TextStyle(
                color: textSecondary,
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
            const Spacer(),
            Container(
              width: 3,
              height: 14,
              decoration: BoxDecoration(
                color: red,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            const SizedBox(width: 7),
            Text(
              paymentMethod,
              style: const TextStyle(
                color: textPrimary,
                fontSize: 13,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        payeeBankCard(),
      ],
    );
  }

  Widget statusSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Status',
          style: TextStyle(
            color: textSecondary,
            fontSize: 14,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: 14),
        Row(
          children: [
            Container(
              width: 3,
              height: 19,
              decoration: BoxDecoration(
                color: statusColor,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            const SizedBox(width: 9),
            Text(
              statusTitle,
              style: const TextStyle(
                color: textPrimary,
                fontSize: 15,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
      ],
    );
  }

  List<Widget> requestRows() {
    return [
      detailRow(
        'Amount',
        money(convertedAmount, '₹'),
      ),
      detailRow(
        'Price',
        inrRate > 0
            ? '${inrRate.toStringAsFixed(2)} INR'
            : '—',
      ),
      detailRow(
        'Total Quantity',
        quantityText(usdAmount),
      ),
      detailRow(
        'Transaction Fees',
        money(totalFee, '₹'),
        dataColor: totalFee > 0
            ? amber
            : textPrimary,
      ),
      detailRow(
        isWithdrawal ? 'You Receive' : 'You Pay',
        money(finalTotal, '₹'),
        dataColor: isWithdrawal ? green : amber,
        bold: true,
      ),
      detailRow(
        'Order No.',
        transactionNumber,
        copyable: true,
      ),
      detailRow(
        'Order Time',
        formatDate(item['created_at']),
      ),
    ];
  }

  List<Widget> transferRows() {
    return [
      detailRow(
        'Amount',
        quantityText(usdAmount),
      ),
      detailRow(
        'Reference No.',
        transactionNumber,
        copyable: true,
      ),
      detailRow(
        'Transfer Time',
        formatDate(item['created_at']),
      ),
      detailRow(
        'Wallet Address',
        walletAddress,
        copyable: true,
      ),
    ];
  }

  // ---------------------------------------------------------------------------
  // SCREEN
  // ---------------------------------------------------------------------------

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: bg,
      appBar: AppBar(
        backgroundColor: bg,
        elevation: 0,
        scrolledUnderElevation: 0,
        leadingWidth: 56,
        leading: IconButton(
          onPressed: () {
            Navigator.pop(context);
          },
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: Colors.white,
            size: 18,
          ),
        ),
        title: const SizedBox.shrink(),
        centerTitle: true,
        actions: const [
          Padding(
            padding: EdgeInsets.only(right: 16),
            child: Center(
              child: Text(
                'P2P Help Center',
                style: TextStyle(
                  color: textSecondary,
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
        ],
      ),
      body: SafeArea(
        top: false,
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(
            20,
            5,
            20,
            30,
          ),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(
                maxWidth: 430,
              ),
              child: Column(
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  statusHeader(),
                  const SizedBox(height: 24),
                  orderHeader(),
                  const SizedBox(height: 18),
                  if (isTransfer)
                    ...transferRows()
                  else
                    ...requestRows(),
                  divider(),
                  isTransfer
                      ? statusSection()
                      : paymentMethodSection(),
                  if (canClientCloseRequest) ...[
                    const SizedBox(height: 18),
                    inlineCloseRequestButton(),
                  ],
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
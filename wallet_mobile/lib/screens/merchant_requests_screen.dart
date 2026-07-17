import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'chat_screen.dart';
import 'transaction_detail_screen.dart';

class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff101010);
  static const border = Color(0xff252525);

  static const orange = Color(0xffFF9F2E);
  static const green = Color(0xff00C076);
  static const red = Color(0xffF6465D);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff68686E);
  static const textMuted = Color(0xff4E4E54);
}

class MerchantRequestsScreen extends StatefulWidget {
  const MerchantRequestsScreen({super.key});

  @override
  State<MerchantRequestsScreen> createState() =>
      _MerchantRequestsScreenState();
}

class _MerchantRequestsScreenState
    extends State<MerchantRequestsScreen> {
  bool loading = true;
  bool refreshing = false;
  bool showAllPending = false;

  List requests = [];

  static const int _pendingPreviewCount = 3;

  @override
  void initState() {
    super.initState();
    loadRequests();
  }

  Future<void> loadRequests({
    bool showLoader = true,
  }) async {
    if (showLoader && mounted) {
      setState(() {
        loading = true;
      });
    }

    try {
      final data = await ApiService.myRequests();

      if (!mounted) return;

      setState(() {
        requests = data["success"] == true
            ? List.from(data["requests"] ?? [])
            : [];

        loading = false;
        refreshing = false;
      });

      if (data["success"] != true) {
        _showSnack(
          data["message"]?.toString() ??
              "Failed to load requests",
          success: false,
        );
      }
    } catch (_) {
      if (!mounted) return;

      setState(() {
        loading = false;
        refreshing = false;
      });

      _showSnack(
        "Failed to load requests",
        success: false,
      );
    }
  }

  Future<void> _refreshRequests() async {
    if (refreshing) return;

    setState(() {
      refreshing = true;
    });

    await loadRequests(showLoader: false);
  }

  Future<void> approve(String id) async {
    final data = await ApiService.approveRequest(id);

    if (!mounted) return;

    _showSnack(
      data["message"]?.toString() ?? "Request approved",
      success: data["success"] == true,
    );

    await loadRequests(showLoader: false);
  }

  Future<void> reject(String id) async {
    final data = await ApiService.rejectRequest(id);

    if (!mounted) return;

    _showSnack(
      data["message"]?.toString() ?? "Request rejected",
      success: data["success"] == true,
    );

    await loadRequests(showLoader: false);
  }

  Future<void> close(String id) async {
    final data = await ApiService.closeRequest(id);

    if (!mounted) return;

    _showSnack(
      data["message"]?.toString() ?? "Request closed",
      success: data["success"] == true,
    );

    await loadRequests(showLoader: false);
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
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      );
  }

  Future<void> _confirmAction({
    required String title,
    required String message,
    required String confirmText,
    required Color color,
    required Future<void> Function() onConfirm,
  }) async {
    final confirmed = await showDialog<bool>(
      context: context,
      barrierColor: Colors.black.withOpacity(0.75),
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: _C.surface,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
            side: const BorderSide(
              color: _C.border,
              width: 0.8,
            ),
          ),
          title: Text(
            title,
            style: const TextStyle(
              color: _C.textPrimary,
              fontSize: 16,
              fontWeight: FontWeight.w800,
            ),
          ),
          content: Text(
            message,
            style: const TextStyle(
              color: _C.textSecondary,
              fontSize: 12.5,
              height: 1.45,
              fontWeight: FontWeight.w500,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(dialogContext, false);
              },
              child: const Text(
                "Cancel",
                style: TextStyle(
                  color: _C.textSecondary,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(dialogContext, true);
              },
              style: ElevatedButton.styleFrom(
                elevation: 0,
                backgroundColor: color,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: 18,
                  vertical: 10,
                ),
              ),
              child: Text(
                confirmText,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
          ],
        );
      },
    );

    if (confirmed == true) {
      await onConfirm();
    }
  }

  List<Map<String, dynamic>> get _normalized {
    return requests
        .map((item) => Map<String, dynamic>.from(item))
        .toList();
  }

  List<Map<String, dynamic>> get pendingList {
    return _normalized.where((item) {
      final status =
          item["status"]?.toString().toLowerCase().trim() ??
              "pending";

      return status == "pending";
    }).toList();
  }

  List<Map<String, dynamic>> get historyList {
    return _normalized.where((item) {
      final status =
          item["status"]?.toString().toLowerCase().trim() ??
              "pending";

      return status != "pending";
    }).toList();
  }

  List<Map<String, dynamic>> get visiblePendingList {
    if (showAllPending) {
      return pendingList;
    }

    return pendingList
        .take(_pendingPreviewCount)
        .toList();
  }

  int get pendingCount => pendingList.length;

  int get approvedCount {
    return _normalized.where((item) {
      return item["status"]?.toString().toLowerCase() ==
          "approved";
    }).length;
  }

  int get rejectedCount {
    return _normalized.where((item) {
      return item["status"]?.toString().toLowerCase() ==
          "rejected";
    }).length;
  }

  int get closedCount {
    return _normalized.where((item) {
      return item["status"]?.toString().toLowerCase() ==
          "closed";
    }).length;
  }

  bool _isBuy(Map<String, dynamic> request) {
    final type =
        request["type"]?.toString().toLowerCase().trim() ??
            "";

    return type == "deposit" ||
        type == "buy" ||
        type == "buying";
  }

  String _typeText(Map<String, dynamic> request) {
    return _isBuy(request) ? "BUY USD" : "SELL USD";
  }

  Color _typeColor(Map<String, dynamic> request) {
    return _isBuy(request) ? _C.green : _C.red;
  }

  String _statusText(Map<String, dynamic> request) {
    final status =
        request["status"]?.toString().toLowerCase().trim() ??
            "pending";

    if (status == "approved") return "Approved";
    if (status == "rejected") return "Rejected";
    if (status == "closed") return "Closed";

    return "Pending";
  }

  Color _statusColor(Map<String, dynamic> request) {
    final status =
        request["status"]?.toString().toLowerCase().trim() ??
            "pending";

    if (status == "approved") return _C.green;
    if (status == "rejected") return _C.red;
    if (status == "closed") return _C.textSecondary;

    return _C.orange;
  }

  String _requestNumber(Map<String, dynamic> request) {
    final transactionNo =
        request["transaction_no"]?.toString().trim() ?? "";

    if (transactionNo.isNotEmpty &&
        transactionNo.toLowerCase() != "null") {
      return transactionNo;
    }

    final id = request["id"]?.toString() ?? "";

    if (id.isEmpty) return "—";

    return "TNS${id.padLeft(9, "0")}";
  }

  String _displayAmount(dynamic value) {
    final number = double.tryParse(
          value?.toString().replaceAll(",", "").trim() ?? "0",
        ) ??
        0;

    final parts = number.toStringAsFixed(2).split(".");

    final whole = parts.first.replaceAllMapped(
      RegExp(r"\B(?=(\d{3})+(?!\d))"),
      (_) => ",",
    );

    return "$whole.${parts.last}";
  }

  Widget _topBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(8, 10, 8, 4),
      child: Row(
        children: [
          SizedBox(
            width: 44,
            child: Navigator.canPop(context)
                ? IconButton(
                    onPressed: () {
                      Navigator.pop(context);
                    },
                    icon: const Icon(
                      Icons.arrow_back_rounded,
                      color: _C.textPrimary,
                      size: 21,
                    ),
                  )
                : null,
          ),
          const Expanded(
            child: Text(
              "P2P Requests",
              textAlign: TextAlign.center,
              style: TextStyle(
                color: _C.textPrimary,
                fontSize: 17,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          SizedBox(
            width: 44,
            child: IconButton(
              onPressed: refreshing
                  ? null
                  : _refreshRequests,
              icon: refreshing
                  ? const SizedBox(
                      width: 17,
                      height: 17,
                      child: CircularProgressIndicator(
                        color: _C.orange,
                        strokeWidth: 2,
                      ),
                    )
                  : const Icon(
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

  Widget _summaryStrip() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 6),
      child: Row(
        children: [
          _summaryItem(
            label: "Pending",
            value: pendingCount,
            color: _C.orange,
          ),
          const SizedBox(width: 8),
          _summaryItem(
            label: "Approved",
            value: approvedCount,
            color: _C.green,
          ),
          const SizedBox(width: 8),
          _summaryItem(
            label: "Rejected",
            value: rejectedCount,
            color: _C.red,
          ),
          const SizedBox(width: 8),
          _summaryItem(
            label: "Closed",
            value: closedCount,
            color: _C.textSecondary,
          ),
        ],
      ),
    );
  }

  Widget _summaryItem({
    required String label,
    required int value,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(
          vertical: 11,
          horizontal: 8,
        ),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: _C.border,
            width: 0.8,
          ),
        ),
        child: Column(
          children: [
            Text(
              value.toString(),
              style: TextStyle(
                color: color,
                fontSize: 15,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                color: _C.textSecondary,
                fontSize: 9.5,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _sectionHeader({
    required String title,
    required int count,
    Widget? trailing,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(
        16,
        18,
        16,
        9,
      ),
      child: Row(
        children: [
          Text(
            title,
            style: const TextStyle(
              color: _C.textPrimary,
              fontSize: 13.5,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(width: 7),
          Container(
            constraints: const BoxConstraints(
              minWidth: 20,
              minHeight: 20,
            ),
            height: 20,
            padding: const EdgeInsets.symmetric(
              horizontal: 6,
            ),
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: _C.orange.withOpacity(0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              count.toString(),
              style: const TextStyle(
                color: _C.orange,
                fontSize: 9.5,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          if (trailing != null) ...[
            const Spacer(),
            trailing,
          ],
        ],
      ),
    );
  }

  Widget _requestCard(
    Map<String, dynamic> request,
  ) {
    final client = Map<String, dynamic>.from(
      request["user"] ?? {},
    );

    final id = request["id"]?.toString() ?? "";
    final name =
        client["name"]?.toString().trim().isNotEmpty == true
            ? client["name"].toString()
            : "Client";
    final email =
        client["email"]?.toString().trim() ?? "";
    final note =
        request["note"]?.toString().trim() ?? "";

    final typeColor = _typeColor(request);
    final statusColor = _statusColor(request);
    final requestNo = _requestNumber(request);

    return Container(
      margin: const EdgeInsets.fromLTRB(
        16,
        0,
        16,
        10,
      ),
      padding: const EdgeInsets.fromLTRB(
        0,
        12,
        0,
        12,
      ),
      decoration: const BoxDecoration(
        border: Border(
          bottom: BorderSide(
            color: _C.border,
            width: 0.8,
          ),
        ),
      ),
      child: Column(
        children: [
          Row(
            children: [
              Text(
                _typeText(request),
                style: TextStyle(
                  color: typeColor,
                  fontSize: 13.5,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const Spacer(),
              Text(
                _statusText(request),
                style: TextStyle(
                  color: statusColor,
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
          const SizedBox(height: 11),
          Row(
            children: [
              CircleAvatar(
                radius: 19,
                backgroundColor: _C.surface,
                child: Text(
                  name.substring(0, 1).toUpperCase(),
                  style: const TextStyle(
                    color: _C.orange,
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment:
                      CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: _C.textPrimary,
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    if (email.isNotEmpty) ...[
                      const SizedBox(height: 3),
                      Text(
                        email,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: _C.textSecondary,
                          fontSize: 10.5,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 13),
          _informationRow(
            label: "USD Amount",
            value:
                "\$${_displayAmount(request["amount"])}",
          ),
          const SizedBox(height: 6),
          _informationRow(
            label: "INR Total",
            value:
                "₹${_displayAmount(request["total_amount"])}",
            valueColor: _C.orange,
            bold: true,
          ),
          const SizedBox(height: 6),
          _informationRow(
            label: "Request No.",
            value: requestNo,
          ),
          if (note.isNotEmpty) ...[
            const SizedBox(height: 6),
            _informationRow(
              label: "Note",
              value: note,
            ),
          ],
          const SizedBox(height: 13),
          Row(
            children: [
              Expanded(
                child: _actionButton(
                  label: "Accept",
                  icon: Icons.check_rounded,
                  backgroundColor: _C.green,
                  foregroundColor: Colors.white,
                  onTap: () {
                    _confirmAction(
                      title: "Accept request?",
                      message:
                          "This will approve the client request.",
                      confirmText: "Accept",
                      color: _C.green,
                      onConfirm: () => approve(id),
                    );
                  },
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _actionButton(
                  label: "Reject",
                  icon: Icons.close_rounded,
                  backgroundColor: _C.red,
                  foregroundColor: Colors.white,
                  onTap: () {
                    _confirmAction(
                      title: "Reject request?",
                      message:
                          "This will reject the client request.",
                      confirmText: "Reject",
                      color: _C.red,
                      onConfirm: () => reject(id),
                    );
                  },
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: _outlineButton(
                  label: "Chat",
                  icon: Icons.chat_bubble_outline_rounded,
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ChatScreen(
                          otherUser: client,
                          chatType: "request",
                          chatId: id,
                        ),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _outlineButton(
                  label: "Close",
                  icon: Icons.lock_outline_rounded,
                  onTap: () {
                    _confirmAction(
                      title: "Close request?",
                      message:
                          "This will close the request without approving or rejecting it.",
                      confirmText: "Close",
                      color: _C.textSecondary,
                      onConfirm: () => close(id),
                    );
                  },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _informationRow({
    required String label,
    required String value,
    Color valueColor = _C.textPrimary,
    bool bold = false,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: _C.textSecondary,
            fontSize: 11.5,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            value,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.right,
            style: TextStyle(
              color: valueColor,
              fontSize: 12,
              fontWeight:
                  bold ? FontWeight.w800 : FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }

  Widget _actionButton({
    required String label,
    required IconData icon,
    required Color backgroundColor,
    required Color foregroundColor,
    required VoidCallback onTap,
  }) {
    return SizedBox(
      height: 36,
      child: ElevatedButton(
        onPressed: onTap,
        style: ElevatedButton.styleFrom(
          elevation: 0,
          backgroundColor: backgroundColor,
          foregroundColor: foregroundColor,
          padding: EdgeInsets.zero,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 14,
            ),
            const SizedBox(width: 5),
            Text(
              label,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _outlineButton({
    required String label,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    return SizedBox(
      height: 36,
      child: OutlinedButton(
        onPressed: onTap,
        style: OutlinedButton.styleFrom(
          foregroundColor: _C.orange,
          side: const BorderSide(
            color: _C.border,
            width: 0.8,
          ),
          padding: EdgeInsets.zero,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 14,
            ),
            const SizedBox(width: 5),
            Text(
              label,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _historyItem(
    Map<String, dynamic> request,
  ) {
    final client = Map<String, dynamic>.from(
      request["user"] ?? {},
    );

    final name =
        client["name"]?.toString().trim().isNotEmpty == true
            ? client["name"].toString()
            : "Client";

    final statusColor = _statusColor(request);

    return InkWell(
      onTap: () async {
        await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => TransactionDetailScreen(
              item: request,
              sourceType: "request",
              user: const <String, dynamic>{
                "role": "merchant",
              },
            ),
          ),
        );

        if (mounted) {
          await loadRequests(showLoader: false);
        }
      },
      splashColor: Colors.white.withOpacity(0.03),
      highlightColor: Colors.white.withOpacity(0.015),
      child: Container(
        padding: const EdgeInsets.symmetric(
          horizontal: 14,
          vertical: 12,
        ),
        child: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: _C.surface,
              child: Icon(
                _isBuy(request)
                    ? Icons.trending_up_rounded
                    : Icons.trending_down_rounded,
                color: _typeColor(request),
                size: 16,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  Text(
                    name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: _C.textPrimary,
                      fontSize: 12.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    "${_typeText(request)} • ${_requestNumber(request)}",
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: _C.textSecondary,
                      fontSize: 10,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment:
                  CrossAxisAlignment.end,
              children: [
                Text(
                  "\$${_displayAmount(request["amount"])}",
                  style: const TextStyle(
                    color: _C.textPrimary,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  _statusText(request),
                  style: TextStyle(
                    color: statusColor,
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
            const SizedBox(width: 4),
            const Icon(
              Icons.chevron_right_rounded,
              color: _C.textMuted,
              size: 18,
            ),
          ],
        ),
      ),
    );
  }

  Widget _historyBox() {
    return Container(
      height: 300,
      margin: const EdgeInsets.fromLTRB(
        16,
        0,
        16,
        12,
      ),
      decoration: BoxDecoration(
        color: _C.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: _C.border,
          width: 0.8,
        ),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: Scrollbar(
          thumbVisibility: true,
          child: ListView.separated(
            primary: false,
            physics: const ClampingScrollPhysics(),
            padding: const EdgeInsets.symmetric(
              vertical: 4,
            ),
            itemCount: historyList.length,
            separatorBuilder: (_, __) => const Divider(
              height: 1,
              thickness: 0.8,
              indent: 14,
              endIndent: 14,
              color: _C.border,
            ),
            itemBuilder: (context, index) {
              return _historyItem(
                historyList[index],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _emptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(
          horizontal: 30,
          vertical: 70,
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 58,
              height: 58,
              decoration: const BoxDecoration(
                color: _C.surface,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.receipt_long_outlined,
                color: _C.textSecondary,
                size: 25,
              ),
            ),
            const SizedBox(height: 11),
            const Text(
              "No requests available",
              textAlign: TextAlign.center,
              style: TextStyle(
                color: _C.textSecondary,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _listBody() {
    if (loading) {
      return const Center(
        child: CircularProgressIndicator(
          color: _C.orange,
          strokeWidth: 2.5,
        ),
      );
    }

    if (requests.isEmpty) {
      return RefreshIndicator(
        color: _C.orange,
        backgroundColor: _C.surface,
        onRefresh: _refreshRequests,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          children: [
            _summaryStrip(),
            _emptyState(),
          ],
        ),
      );
    }

    return RefreshIndicator(
      color: _C.orange,
      backgroundColor: _C.surface,
      onRefresh: _refreshRequests,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(
          parent: BouncingScrollPhysics(),
        ),
        padding: const EdgeInsets.only(
          bottom: 30,
        ),
        children: [
          _summaryStrip(),
          if (pendingList.isNotEmpty) ...[
            _sectionHeader(
              title: "Pending Requests",
              count: pendingCount,
              trailing:
                  pendingCount > _pendingPreviewCount
                      ? GestureDetector(
                          onTap: () {
                            setState(() {
                              showAllPending =
                                  !showAllPending;
                            });
                          },
                          child: Text(
                            showAllPending
                                ? "Show Less"
                                : "View All",
                            style: const TextStyle(
                              color: _C.orange,
                              fontSize: 11,
                              fontWeight:
                                  FontWeight.w700,
                            ),
                          ),
                        )
                      : null,
            ),
            ...visiblePendingList.map(
              _requestCard,
            ),
          ],
          if (historyList.isNotEmpty) ...[
            _sectionHeader(
              title: "Request History",
              count: historyList.length,
            ),
            _historyBox(),
          ],
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: Column(
          children: [
            _topBar(),
            Expanded(
              child: _listBody(),
            ),
          ],
        ),
      ),
    );
  }
}

import 'dart:async';
import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:file_picker/file_picker.dart';

class ApiService {
  static String get host {
    if (kIsWeb) return "http://127.0.0.1:8000";
    return "http://10.0.2.2:8000";
  }

  static String get baseUrl => "$host/api";

 static String fixUrl(dynamic url) {
  if (url == null) return "";

  String value = url.toString().trim();
  if (value.isEmpty || value == "null") return "";

  value = value.replaceAll("\\", "/");

  value = value.replaceAll("http://127.0.0.1:8000", host);
  value = value.replaceAll("http://localhost:8000", host);
  value = value.replaceAll("http://10.0.2.2:8000", host);

  if (value.startsWith("http://") || value.startsWith("https://")) {
    return value;
  }

  if (value.startsWith("/storage/")) {
    return "$host$value";
  }

  if (value.startsWith("storage/")) {
    return "$host/$value";
  }

  if (value.startsWith("/api/storage/")) {
    return "$host$value";
  }

  if (value.startsWith("api/storage/")) {
    return "$host/$value";
  }

  return "$host/storage/$value";
}

  // ── Token ────────────────────────────────────────────────────────────

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString("token", token);
  }

  static Future<String?> token() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString("token");
  }

  static Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove("token");
  }

  static Future<Map<String, String>> headers() async {
    final t = await token();
    return {
      "Accept": "application/json",
      if (t != null && t.isNotEmpty) "Authorization": "Bearer $t",
    };
  }

  // ── Response decode ──────────────────────────────────────────────────

  static Map<String, dynamic> decode(http.Response res) {
    try {
      final decoded = jsonDecode(res.body);
      if (decoded is Map<String, dynamic>) {
        decoded["status_code"] = res.statusCode;
        return decoded;
      }
      return {
        "success": false,
        "message": "Invalid server response",
        "status_code": res.statusCode,
      };
    } catch (_) {
      return {
        "success": false,
        "message": "Invalid server response",
        "status_code": res.statusCode,
        "body": res.body,
      };
    }
  }

  static Future<http.MultipartFile> _filePart(
      String field, XFile file) async {
    final bytes = await file.readAsBytes();
    return http.MultipartFile.fromBytes(
      field,
      bytes,
      filename: file.name.isNotEmpty ? file.name : "$field.jpg",
    );
  }

  // ── Auth ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> login(
      String email, String password) async {
    await clearToken();
    final res = await http.post(
      Uri.parse("$baseUrl/login"),
      headers: {"Accept": "application/json"},
      body: {"email": email, "password": password},
    ).timeout(const Duration(seconds: 25));

    final data = decode(res);
    if (data["success"] == true && data["token"] != null) {
      await saveToken(data["token"].toString());
    }
    return data;
  }

  static Future<Map<String, dynamic>> profile() async {
    final res = await http.get(
      Uri.parse("$baseUrl/profile"),
      headers: await headers(),
    ).timeout(const Duration(seconds: 25));

    final data = decode(res);
    if (res.statusCode == 401) await clearToken();
    return data;
  }

  static Future<Map<String, dynamic>> registerMultipart({
    required Map<String, String> fields,
    XFile? photo,
    XFile? upiQr,
    XFile? aadhaarPhoto,
  }) async {
    try {
      final request = http.MultipartRequest(
          "POST", Uri.parse("$baseUrl/register"));
      request.headers.addAll({"Accept": "application/json"});
      request.fields.addAll(fields);

      if (photo != null) request.files.add(await _filePart("photo", photo));
      if (upiQr != null) request.files.add(await _filePart("upi_qr", upiQr));
      if (aadhaarPhoto != null) {
        request.files.add(await _filePart("aadhaar_photo", aadhaarPhoto));
      }

      final streamed =
          await request.send().timeout(const Duration(seconds: 60));
      final res = await http.Response.fromStream(streamed);
      final data = decode(res);

      if (data["success"] == true && data["token"] != null) {
        await saveToken(data["token"].toString());
      }
      return data;
    } on TimeoutException {
      return {"success": false, "message": "Request timeout. Please try again."};
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  static Future<Map<String, dynamic>> updateProfileMultipart({
    required Map<String, String> fields,
    XFile? photo,
    XFile? upiQr,
    XFile? aadhaarPhoto,
  }) async {
    final t = await token();
    if (t == null || t.isEmpty) {
      return {
        "success": false,
        "message": "Unauthorized. Please logout and login again.",
        "status_code": 401,
      };
    }

    try {
      final request = http.MultipartRequest(
          "POST", Uri.parse("$baseUrl/profile"));
      request.headers.addAll({
        "Accept": "application/json",
        "Authorization": "Bearer $t",
      });
      request.fields.addAll(fields);
      request.fields["_method"] = "PUT";

      if (photo != null) request.files.add(await _filePart("photo", photo));
      if (upiQr != null) request.files.add(await _filePart("upi_qr", upiQr));
      if (aadhaarPhoto != null) {
        request.files.add(await _filePart("aadhaar_photo", aadhaarPhoto));
      }

      final streamed =
          await request.send().timeout(const Duration(seconds: 60));
      final res = await http.Response.fromStream(streamed);
      return decode(res);
    } on TimeoutException {
      return {
        "success": false,
        "message": "Request timeout. Please check Laravel server.",
      };
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  static Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final res = await http.post(
        Uri.parse("$baseUrl/change-password"),
        headers: await headers(),
        body: {
          "current_password": currentPassword,
          "password": newPassword,
          "password_confirmation": confirmPassword,
        },
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  static Future<void> logout() async {
    final t = await token();
    try {
      if (t != null && t.isNotEmpty) {
        await http.post(
          Uri.parse("$baseUrl/logout"),
          headers: {
            "Accept": "application/json",
            "Authorization": "Bearer $t",
          },
        ).timeout(const Duration(seconds: 15));
      }
    } catch (_) {}
    await clearToken();
  }

  static Future<void> ping() async {
    final t = await token();
    if (t == null || t.isEmpty) return;
    try {
      await http.post(
        Uri.parse("$baseUrl/ping"),
        headers: {
          "Accept": "application/json",
          "Authorization": "Bearer $t",
        },
      ).timeout(const Duration(seconds: 10));
    } catch (_) {}
  }

  // ── Config ───────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> currentConfig() async {
    try {
      final res = await http.get(
        Uri.parse("$baseUrl/config"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 25));
      final data = decode(res);
      if (res.statusCode == 401) await clearToken();
      return data;
    } on TimeoutException {
      return {"success": false, "message": "Request timeout."};
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  // ── Merchants ────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> merchants() async {
    try {
      final res = await http.get(
        Uri.parse("$baseUrl/merchants"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 25));
      final data = decode(res);
      if (res.statusCode == 401) await clearToken();
      return data;
    } on TimeoutException {
      return {"success": false, "message": "Request timeout."};
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  // ── Requests (Buy/Sell) ──────────────────────────────────────────────

  static Future<Map<String, dynamic>> myRequests() async {
    try {
      final res = await http.get(
        Uri.parse("$baseUrl/requests"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 25));
      final data = decode(res);
      if (res.statusCode == 401) await clearToken();
      return data;
    } on TimeoutException {
      return {"success": false, "message": "Request timeout."};
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

static Future<Map<String, dynamic>> createRequestMultipart({
  required String amount,
  required String type,
  required String merchantId,
  String? note,
}) async {
  try {
    final res = await http.post(
      Uri.parse("$baseUrl/requests"),
      headers: await headers(),
      body: {
        "amount": amount,
        "type": type,
        "merchant_id": merchantId,
        "note": note ?? "",
      },
    ).timeout(const Duration(seconds: 25));

    final data = decode(res);
    if (res.statusCode == 401) await clearToken();
    return data;
  } on TimeoutException {
    return {"success": false, "message": "Request timeout."};
  } catch (e) {
    return {"success": false, "message": e.toString()};
  }
}
  // ── Support Tickets ──────────────────────────────────────────────────

  static Future<Map<String, dynamic>> supportTickets() async {
    try {
      final res = await http.get(
        Uri.parse("$baseUrl/support-tickets"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  static Future<Map<String, dynamic>> createSupportTicket({
    required String name,
    required String email,
    required String message,
  }) async {
    try {
      final res = await http.post(
        Uri.parse("$baseUrl/support-tickets"),
        headers: await headers(),
        body: {"name": name, "email": email, "message": message},
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  // ── Wallet Transfer ──────────────────────────────────────────────────

  static Future<Map<String, dynamic>> walletLookup(
      String walletId) async {
    try {
      final res = await http.post(
        Uri.parse("$baseUrl/wallet/lookup"),
        headers: await headers(),
        body: {"wallet_id": walletId},
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  static Future<Map<String, dynamic>> walletTransfer({
    required String receiverWalletId,
    required String amount,
    String? note,
  }) async {
    try {
      final res = await http.post(
        Uri.parse("$baseUrl/wallet/transfer"),
        headers: await headers(),
        body: {
          "receiver_wallet_id": receiverWalletId,
          "amount": amount,
          "note": note ?? "",
        },
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  static Future<Map<String, dynamic>> walletTransfers() async {
    try {
      final res = await http.get(
        Uri.parse("$baseUrl/wallet/transfers"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  // ── Chat: Transaction-linked ─────────────────────────────────────────

  /// All conversations (for ChatUsersScreen list)
  static Future<Map<String, dynamic>> chatConversations() async {
    try {
      final res = await http.get(
        Uri.parse("$baseUrl/chat/conversations"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  /// Mark all received messages as delivered
  static Future<Map<String, dynamic>> chatDelivered() async {
    try {
      final res = await http.post(
        Uri.parse("$baseUrl/chat/delivered"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 15));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  // ── Chat: Wallet Transfer ────────────────────────────────────────────

  /// Fetch messages for a specific wallet transfer
  static Future<Map<String, dynamic>> transferChatMessages(
      String transferId) async {
    try {
      final res = await http.get(
        Uri.parse("$baseUrl/chat/transfer/$transferId/messages"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  /// Send a message for a specific wallet transfer
  static Future<Map<String, dynamic>> sendTransferChatMessage(
    String transferId,
    String message,
  ) async {
    try {
      final res = await http.post(
        Uri.parse("$baseUrl/chat/transfer/$transferId/messages"),
        headers: await headers(),
        body: {"message": message},
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  // ── Chat: Wallet Request ─────────────────────────────────────────────

  /// Fetch messages for a specific buy/sell request
  static Future<Map<String, dynamic>> requestChatMessages(
      String requestId) async {
    try {
      final res = await http.get(
        Uri.parse("$baseUrl/chat/request/$requestId/messages"),
        headers: await headers(),
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }

  /// Send a message for a specific buy/sell request
  static Future<Map<String, dynamic>> sendRequestChatMessage(
    String requestId,
    String message,
  ) async {
    try {
      final res = await http.post(
        Uri.parse("$baseUrl/chat/request/$requestId/messages"),
        headers: await headers(),
        body: {"message": message},
      ).timeout(const Duration(seconds: 25));
      return decode(res);
    } catch (e) {
      return {"success": false, "message": e.toString()};
    }
  }
  static Future<Map<String, dynamic>> sendChatMessageMultipart({
  required String chatType,
  required String chatId,
  String message = "",
  XFile? image,
  PlatformFile? file,
}) async {
  final t = await token();
  if (t == null || t.isEmpty) {
    return {"success": false, "message": "Unauthorized"};
  }

  final url = chatType == "transfer"
      ? "$baseUrl/chat/transfer/$chatId/messages"
      : "$baseUrl/chat/request/$chatId/messages";

  try {
    final request = http.MultipartRequest("POST", Uri.parse(url));
    request.headers.addAll({
      "Accept": "application/json",
      "Authorization": "Bearer $t",
    });

    request.fields["message"] = message;

    if (image != null) {
      request.files.add(await _filePart("attachment", image));
    }

    if (file != null && file.bytes != null) {
      request.files.add(
        http.MultipartFile.fromBytes(
          "attachment",
          file.bytes!,
          filename: file.name,
        ),
      );
    }

    final streamed = await request.send().timeout(const Duration(seconds: 60));
    final res = await http.Response.fromStream(streamed);
    return decode(res);
  } catch (e) {
    return {"success": false, "message": e.toString()};
  }
}






static Future<Map<String, dynamic>> approveRequest(String requestId) async {
  try {
    final res = await http.post(
      Uri.parse("$baseUrl/requests/$requestId/approve"),
      headers: await headers(),
    ).timeout(const Duration(seconds: 25));
    return decode(res);
  } catch (e) {
    return {"success": false, "message": e.toString()};
  }
}

static Future<Map<String, dynamic>> rejectRequest(String requestId) async {
  try {
    final res = await http.post(
      Uri.parse("$baseUrl/requests/$requestId/reject"),
      headers: await headers(),
    ).timeout(const Duration(seconds: 25));
    return decode(res);
  } catch (e) {
    return {"success": false, "message": e.toString()};
  }
}
}
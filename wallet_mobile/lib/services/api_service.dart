import 'dart:async';
import 'dart:convert';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static String get host {
    if (kIsWeb) {
      return "https://wallet.bitxnow.com";
    }

    return "https://wallet.bitxnow.com";
  }

  static String get baseUrl => "$host/api";

  // ─────────────────────────────────────────────────────────────
  // URL FIXER
  // ─────────────────────────────────────────────────────────────

  static String fixUrl(dynamic url) {
    if (url == null) return "";

    String value = url.toString().trim();

    if (value.isEmpty || value == "null") {
      return "";
    }

    value = value.replaceAll("\\", "/");

    value = value.replaceAll(
      "http://127.0.0.1:8000",
      host,
    );

    value = value.replaceAll(
      "http://localhost:8000",
      host,
    );

    value = value.replaceAll(
      "http://10.0.2.2:8000",
      host,
    );

    value = value.replaceAll(
      "/api/storage/",
      "/storage/",
    );

    if (value.startsWith("http://") ||
        value.startsWith("https://")) {
      return value;
    }

    if (value.startsWith("/storage/")) {
      return "$host$value";
    }

    if (value.startsWith("storage/")) {
      return "$host/$value";
    }

    if (value.startsWith("/api/storage/")) {
      return "$host${value.replaceFirst(
        "/api/storage/",
        "/storage/",
      )}";
    }

    if (value.startsWith("api/storage/")) {
      return "$host/${value.replaceFirst(
        "api/storage/",
        "storage/",
      )}";
    }

    return "$host/storage/$value";
  }

  // ─────────────────────────────────────────────────────────────
  // TOKEN
  // ─────────────────────────────────────────────────────────────

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
    final savedToken = await token();

    return {
      "Accept": "application/json",
      if (savedToken != null && savedToken.isNotEmpty)
        "Authorization": "Bearer $savedToken",
    };
  }

  // ─────────────────────────────────────────────────────────────
  // RESPONSE DECODER
  // ─────────────────────────────────────────────────────────────

  static Map<String, dynamic> decode(
    http.Response response,
  ) {
    try {
      final decoded = jsonDecode(response.body);

      if (decoded is Map<String, dynamic>) {
        decoded["status_code"] = response.statusCode;
        return decoded;
      }

      return {
        "success": false,
        "message": "Invalid server response.",
        "status_code": response.statusCode,
      };
    } catch (_) {
      return {
        "success": false,
        "message": "Invalid server response.",
        "status_code": response.statusCode,
        "body": response.body,
      };
    }
  }

  static Future<http.MultipartFile> _filePart(
    String field,
    XFile file,
  ) async {
    final bytes = await file.readAsBytes();

    return http.MultipartFile.fromBytes(
      field,
      bytes,
      filename: file.name.isNotEmpty
          ? file.name
          : "$field.jpg",
    );
  }

  // ─────────────────────────────────────────────────────────────
  // AUTHENTICATION
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> login(
    String email,
    String password,
  ) async {
    await clearToken();

    try {
      final response = await http
          .post(
            Uri.parse("$baseUrl/login"),
            headers: {
              "Accept": "application/json",
              "Content-Type": "application/json",
              "Cache-Control": "no-cache, no-store, must-revalidate",
              "Pragma": "no-cache",
            },
            body: jsonEncode({
              "email": email,
              "password": password,
            }),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (data["success"] == true && data["token"] != null) {
        await saveToken(data["token"].toString());

        // Always replace the login user snapshot with a fresh profile
        // fetched using the newly-issued token. This prevents an old
        // wallet_id from being passed into the dashboard.
        final freshProfile = await profile();
        if (freshProfile["success"] == true &&
            freshProfile["user"] is Map) {
          data["user"] = Map<String, dynamic>.from(
            freshProfile["user"] as Map,
          );
        }
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message":
            "Login timeout. Please check the server.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": "Login failed: $error",
      };
    }
  }

  static Future<Map<String, dynamic>> profile() async {
    try {
      final requestHeaders = await headers();
      requestHeaders.addAll({
        "Cache-Control": "no-cache, no-store, must-revalidate",
        "Pragma": "no-cache",
      });

      final uri = Uri.parse("$baseUrl/profile").replace(
        queryParameters: {
          "_ts": DateTime.now().millisecondsSinceEpoch.toString(),
        },
      );

      final response = await http
          .get(
            uri,
            headers: requestHeaders,
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (data["success"] == true && data["user"] is Map) {
        data["user"] = Map<String, dynamic>.from(data["user"] as Map);
      }

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message": "Profile request timeout.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      registerMultipart({
    required Map<String, String> fields,
    XFile? photo,
    XFile? upiQr,
    XFile? aadhaarPhoto,
  }) async {
    try {
      final request = http.MultipartRequest(
        "POST",
        Uri.parse("$baseUrl/register"),
      );

      request.headers.addAll({
        "Accept": "application/json",
      });

      request.fields.addAll(fields);

      if (photo != null) {
        request.files.add(
          await _filePart("photo", photo),
        );
      }

      if (upiQr != null) {
        request.files.add(
          await _filePart("upi_qr", upiQr),
        );
      }

      if (aadhaarPhoto != null) {
        request.files.add(
          await _filePart(
            "aadhaar_photo",
            aadhaarPhoto,
          ),
        );
      }

      final streamedResponse = await request
          .send()
          .timeout(
            const Duration(seconds: 60),
          );

      final response =
          await http.Response.fromStream(
        streamedResponse,
      );

      final data = decode(response);

      if (data["success"] == true &&
          data["token"] != null) {
        await saveToken(
          data["token"].toString(),
        );
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message":
            "Request timeout. Please try again.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      updateProfileMultipart({
    required Map<String, String> fields,
    XFile? photo,
    XFile? upiQr,
    XFile? aadhaarPhoto,
  }) async {
    final savedToken = await token();

    if (savedToken == null ||
        savedToken.isEmpty) {
      return {
        "success": false,
        "message":
            "Unauthorized. Please logout and login again.",
        "status_code": 401,
      };
    }

    try {
      final request = http.MultipartRequest(
        "POST",
        Uri.parse("$baseUrl/profile"),
      );

      request.headers.addAll({
        "Accept": "application/json",
        "Authorization": "Bearer $savedToken",
      });

      request.fields.addAll(fields);
      request.fields["_method"] = "PUT";

      if (photo != null) {
        request.files.add(
          await _filePart("photo", photo),
        );
      }

      if (upiQr != null) {
        request.files.add(
          await _filePart("upi_qr", upiQr),
        );
      }

      if (aadhaarPhoto != null) {
        request.files.add(
          await _filePart(
            "aadhaar_photo",
            aadhaarPhoto,
          ),
        );
      }

      final streamedResponse = await request
          .send()
          .timeout(
            const Duration(seconds: 60),
          );

      final response =
          await http.Response.fromStream(
        streamedResponse,
      );

      return decode(response);
    } on TimeoutException {
      return {
        "success": false,
        "message":
            "Request timeout. Please check the Laravel server.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/change-password",
            ),
            headers: await headers(),
            body: {
              "current_password":
                  currentPassword,
              "password": newPassword,
              "password_confirmation":
                  confirmPassword,
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<void> logout() async {
    final savedToken = await token();

    try {
      if (savedToken != null &&
          savedToken.isNotEmpty) {
        await http
            .post(
              Uri.parse("$baseUrl/logout"),
              headers: {
                "Accept": "application/json",
                "Authorization":
                    "Bearer $savedToken",
              },
            )
            .timeout(
              const Duration(seconds: 15),
            );
      }
    } catch (_) {}

    await clearToken();
  }

  static Future<void> ping() async {
    final savedToken = await token();

    if (savedToken == null ||
        savedToken.isEmpty) {
      return;
    }

    try {
      await http
          .post(
            Uri.parse("$baseUrl/ping"),
            headers: {
              "Accept": "application/json",
              "Authorization":
                  "Bearer $savedToken",
            },
          )
          .timeout(
            const Duration(seconds: 10),
          );
    } catch (_) {}
  }

  // ─────────────────────────────────────────────────────────────
  // CONFIGURATION
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      currentConfig() async {
    try {
      final response = await http
          .get(
            Uri.parse("$baseUrl/config"),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message": "Request timeout.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // MERCHANTS
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      merchants() async {
    try {
      final response = await http
          .get(
            Uri.parse("$baseUrl/merchants"),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message": "Request timeout.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // BUY / SELL REQUESTS
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      myRequests() async {
    try {
      final response = await http
          .get(
            Uri.parse("$baseUrl/requests"),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message": "Request timeout.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      createRequestMultipart({
    required String amount,
    required String type,
    required String merchantId,
    String? note,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse("$baseUrl/requests"),
            headers: await headers(),
            body: {
              "amount": amount,
              "type": type,
              "merchant_id": merchantId,
              "note": note ?? "",
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message": "Request timeout.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      approveRequest(
    String requestId,
  ) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/requests/$requestId/approve",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      rejectRequest(
    String requestId,
  ) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/requests/$requestId/reject",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      closeRequest(
    String requestId,
  ) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/requests/$requestId/close",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message":
            "Close transaction request timeout.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // SUPPORT TICKETS
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      supportTickets() async {
    try {
      final response = await http
          .get(
            Uri.parse(
              "$baseUrl/support-tickets",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      createSupportTicket({
    required String name,
    required String email,
    required String message,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/support-tickets",
            ),
            headers: await headers(),
            body: {
              "name": name,
              "email": email,
              "message": message,
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // WALLET TRANSFER
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      walletLookup(
    String walletId,
  ) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/wallet/lookup",
            ),
            headers: await headers(),
            body: {
              "wallet_id": walletId,
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      walletTransfer({
    required String receiverWalletId,
    required String amount,
    String? note,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/wallet/transfer",
            ),
            headers: await headers(),
            body: {
              "receiver_wallet_id":
                  receiverWalletId,
              "amount": amount,
              "note": note ?? "",
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      walletTransfers() async {
    try {
      final response = await http
          .get(
            Uri.parse(
              "$baseUrl/wallet/transfers",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // CHAT CONVERSATIONS
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      chatConversations() async {
    try {
      final response = await http
          .get(
            Uri.parse(
              "$baseUrl/chat/conversations",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      chatDelivered() async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/chat/delivered",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 15),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // TRANSFER CHAT
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      transferChatMessages(
    String transferId,
  ) async {
    try {
      final response = await http
          .get(
            Uri.parse(
              "$baseUrl/chat/transfer/$transferId/messages",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      sendTransferChatMessage(
    String transferId,
    String message,
  ) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/chat/transfer/$transferId/messages",
            ),
            headers: await headers(),
            body: {
              "message": message,
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // REQUEST CHAT
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      requestChatMessages(
    String requestId,
  ) async {
    try {
      final response = await http
          .get(
            Uri.parse(
              "$baseUrl/chat/request/$requestId/messages",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      sendRequestChatMessage(
    String requestId,
    String message,
  ) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/chat/request/$requestId/messages",
            ),
            headers: await headers(),
            body: {
              "message": message,
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      sendChatMessageMultipart({
    required String chatType,
    required String chatId,
    String message = "",
    XFile? image,
    PlatformFile? file,
  }) async {
    final savedToken = await token();

    if (savedToken == null ||
        savedToken.isEmpty) {
      return {
        "success": false,
        "message": "Unauthorized.",
        "status_code": 401,
      };
    }

    final url = chatType == "transfer"
        ? "$baseUrl/chat/transfer/$chatId/messages"
        : "$baseUrl/chat/request/$chatId/messages";

    try {
      final request = http.MultipartRequest(
        "POST",
        Uri.parse(url),
      );

      request.headers.addAll({
        "Accept": "application/json",
        "Authorization": "Bearer $savedToken",
      });

      request.fields["message"] = message;

      if (image != null) {
        request.files.add(
          await _filePart(
            "attachment",
            image,
          ),
        );
      }

      if (file != null &&
          file.bytes != null) {
        request.files.add(
          http.MultipartFile.fromBytes(
            "attachment",
            file.bytes!,
            filename: file.name,
          ),
        );
      }

      final streamedResponse = await request
          .send()
          .timeout(
            const Duration(seconds: 60),
          );

      final response =
          await http.Response.fromStream(
        streamedResponse,
      );

      return decode(response);
    } on TimeoutException {
      return {
        "success": false,
        "message":
            "Message upload timeout. Please try again.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  // ─────────────────────────────────────────────────────────────
  // PAYMENT METHODS
  // ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>>
      paymentMethods() async {
    try {
      final response = await http
          .get(
            Uri.parse(
              "$baseUrl/payment-methods",
            ),
            headers: await headers(),
          )
          .timeout(
            const Duration(seconds: 25),
          );

      final data = decode(response);

      if (response.statusCode == 401) {
        await clearToken();
      }

      return data;
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      addBankAccount({
    required String bankName,
    required String branch,
    required String accountNumber,
    required String accountType,
    required String ifsc,
    bool isDefault = false,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/bank-accounts",
            ),
            headers: await headers(),
            body: {
              "bank_name": bankName,
              "branch": branch,
              "account_number": accountNumber,
              "account_type": accountType,
              "ifsc": ifsc,
              "is_default":
                  isDefault ? "1" : "0",
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      updateBankAccount({
    required String id,
    required String bankName,
    required String branch,
    required String accountNumber,
    required String accountType,
    required String ifsc,
    bool isDefault = false,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/bank-accounts/$id",
            ),
            headers: await headers(),
            body: {
              "_method": "PUT",
              "bank_name": bankName,
              "branch": branch,
              "account_number": accountNumber,
              "account_type": accountType,
              "ifsc": ifsc,
              "is_default":
                  isDefault ? "1" : "0",
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      deleteBankAccount(
    String id,
  ) async {
    try {
      final response = await http
          .post(
            Uri.parse(
              "$baseUrl/bank-accounts/$id",
            ),
            headers: await headers(),
            body: {
              "_method": "DELETE",
            },
          )
          .timeout(
            const Duration(seconds: 25),
          );

      return decode(response);
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }

  static Future<Map<String, dynamic>>
      updateUpiMultipart({
    required String upiName,
    required String upiId,
    XFile? upiQr,
  }) async {
    final savedToken = await token();

    if (savedToken == null ||
        savedToken.isEmpty) {
      return {
        "success": false,
        "message": "Unauthorized.",
        "status_code": 401,
      };
    }

    try {
      final request = http.MultipartRequest(
        "POST",
        Uri.parse(
          "$baseUrl/payment-upi",
        ),
      );

      request.headers.addAll({
        "Accept": "application/json",
        "Authorization": "Bearer $savedToken",
      });

      request.fields["_method"] = "PUT";
      request.fields["upi_name"] = upiName;
      request.fields["upi_id"] = upiId;

      if (upiQr != null) {
        request.files.add(
          await _filePart(
            "upi_qr",
            upiQr,
          ),
        );
      }

      final streamedResponse = await request
          .send()
          .timeout(
            const Duration(seconds: 60),
          );

      final response =
          await http.Response.fromStream(
        streamedResponse,
      );

      return decode(response);
    } on TimeoutException {
      return {
        "success": false,
        "message":
            "UPI update timeout. Please try again.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }
}
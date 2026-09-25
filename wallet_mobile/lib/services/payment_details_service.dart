import 'dart:async';

import 'package:http/http.dart' as http;

import 'api_service.dart';

class PaymentDetailsService {
  /// Bank / UPI details of the person who receives INR for this request.
  ///
  /// Buy USDT  (deposit):    merchant bank details
  /// Sell USDT (withdrawal): client bank details
  static Future<Map<String, dynamic>> forRequest(
    String requestId,
  ) async {
    try {
      final requestHeaders = await ApiService.headers();

      requestHeaders.addAll({
        "Cache-Control": "no-cache, no-store, must-revalidate",
        "Pragma": "no-cache",
      });

      final uri = Uri.parse(
        "${ApiService.baseUrl}/requests/$requestId/payment-details",
      ).replace(
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

      final data = ApiService.decode(response);

      if (response.statusCode == 401) {
        await ApiService.clearToken();
      }

      return data;
    } on TimeoutException {
      return {
        "success": false,
        "message": "Payment details request timeout.",
      };
    } catch (error) {
      return {
        "success": false,
        "message": error.toString(),
      };
    }
  }
}
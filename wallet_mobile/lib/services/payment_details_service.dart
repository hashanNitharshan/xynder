import 'dart:async';

import 'package:http/http.dart' as http;

import 'api_service.dart';

class PaymentDetailsService {
  /// Loads the bank / UPI details of the person who receives INR
  /// for one Buy / Sell request.
  ///
  /// GET /api/requests/{id}/payment-details
  static Future<Map<String, dynamic>> forRequest(String requestId) async {
    try {
      final response = await http
          .get(
            Uri.parse(
              '${ApiService.baseUrl}/requests/$requestId/payment-details',
            ),
            headers: await ApiService.headers(),
          )
          .timeout(const Duration(seconds: 25));

      final data = ApiService.decode(response);

      if (response.statusCode == 401) {
        await ApiService.clearToken();
      }

      return data;
    } on TimeoutException {
      return {
        'success': false,
        'message': 'Payment details timeout.',
      };
    } catch (error) {
      return {
        'success': false,
        'message': error.toString(),
      };
    }
  }
}
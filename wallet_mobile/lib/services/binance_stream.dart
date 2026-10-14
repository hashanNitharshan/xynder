import 'dart:async';
import 'dart:convert';
import 'dart:math' as math;

import 'package:web_socket_channel/web_socket_channel.dart';

/// Connection state, same as the React hook:
/// idle | connecting | live | reconnecting
enum StreamStatus { idle, connecting, live, reconnecting }

typedef BinanceMessageHandler = void Function(
  String stream,
  Map<String, dynamic> data,
);

/// Opens one combined Binance WebSocket for a list of stream names and calls
/// [onMessage] for every event. No API key is needed.
///
/// Reconnects automatically (Binance closes every connection after 24 hours,
/// and mobile networks drop often). After two failures on one host it tries
/// the other host.
class BinanceStream {
  // data-stream.binance.vision only carries market data and is the most
  // widely reachable endpoint. stream.binance.com is the fallback.
  static const List<String> _hosts = [
    'wss://data-stream.binance.vision/stream?streams=',
    'wss://stream.binance.com:9443/stream?streams=',
  ];

  final BinanceMessageHandler onMessage;
  final void Function(StreamStatus status)? onStatus;

  BinanceStream({required this.onMessage, this.onStatus});

  WebSocketChannel? _channel;
  StreamSubscription? _subscription;
  Timer? _retryTimer;

  List<String> _streams = const [];
  int _retry = 0;
  int _hostIndex = 0;
  int _session = 0;
  bool _closedByUs = true;
  bool _disposed = false;

  StreamStatus status = StreamStatus.idle;

  void _setStatus(StreamStatus next) {
    status = next;
    if (!_disposed) onStatus?.call(next);
  }

  /// Connect to a new list of streams. Closes any old connection first.
  /// Pass an empty list to stay disconnected.
  void connect(List<String> streams) {
    _streams = streams.where((s) => s.isNotEmpty).toList();
    _retryTimer?.cancel();
    _closeSocket();
    _retry = 0;

    if (_streams.isEmpty) {
      _closedByUs = true;
      _setStatus(StreamStatus.idle);
      return;
    }

    _closedByUs = false;
    _open();
  }

  /// Stop the connection, for example when the app goes to the background.
  void pause() {
    _closedByUs = true;
    _retryTimer?.cancel();
    _closeSocket();
    _setStatus(StreamStatus.idle);
  }

  /// Open again with the same streams, for example when the app comes back.
  void resume() {
    if (_streams.isEmpty || _disposed) return;
    _retryTimer?.cancel();
    _closeSocket();
    _closedByUs = false;
    _retry = 0;
    _open();
  }

  void dispose() {
    _disposed = true;
    _closedByUs = true;
    _retryTimer?.cancel();
    _closeSocket();
  }

  void _open() {
    if (_disposed) return;

    final session = ++_session;
    _setStatus(_retry == 0 ? StreamStatus.connecting : StreamStatus.reconnecting);

    final url = _hosts[_hostIndex] + _streams.join('/');

    try {
      final channel = WebSocketChannel.connect(Uri.parse(url));
      _channel = channel;

      _subscription = channel.stream.listen(
        (event) {
          if (session != _session) return;

          // The first message proves the connection works.
          if (status != StreamStatus.live) {
            _retry = 0;
            _setStatus(StreamStatus.live);
          }

          try {
            final msg = jsonDecode(event as String);
            if (msg is Map && msg['stream'] is String && msg['data'] is Map) {
              onMessage(
                msg['stream'] as String,
                Map<String, dynamic>.from(msg['data'] as Map),
              );
            }
          } catch (_) {
            // Ignore bad frames.
          }
        },
        onError: (_) => _scheduleReconnect(session),
        onDone: () => _scheduleReconnect(session),
        cancelOnError: true,
      );
    } catch (_) {
      _scheduleReconnect(session);
    }
  }

  void _scheduleReconnect(int session) {
    if (_closedByUs || _disposed || session != _session) return;

    // Make sure a later onDone for this same socket is ignored.
    _session++;

    _retry += 1;
    if (_retry % 2 == 0) {
      _hostIndex = (_hostIndex + 1) % _hosts.length;
    }

    final ms = math.min(1000 * math.pow(2, math.min(_retry, 5)).toInt(), 30000);
    _setStatus(StreamStatus.reconnecting);

    _retryTimer?.cancel();
    _retryTimer = Timer(Duration(milliseconds: ms), () {
      if (!_closedByUs && !_disposed) _open();
    });
  }

  void _closeSocket() {
    _session++;
    _subscription?.cancel();
    _subscription = null;
    _channel?.sink.close();
    _channel = null;
  }
}
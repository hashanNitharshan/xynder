// lib/utils/tv_iframe_registry.dart
// Call registerTvIframes() from main.dart BEFORE runApp() on web only.
//
// Usage in main.dart:
//   import 'package:flutter/foundation.dart';
//   import 'utils/tv_iframe_registry.dart'
//       if (dart.library.io) 'utils/tv_iframe_registry_stub.dart';
//
//   void main() {
//     WidgetsFlutterBinding.ensureInitialized();
//     registerTvIframes();   // no-op on mobile via stub
//     runApp(const WalletApp());
//   }

// ignore: avoid_web_libraries_in_flutter
import 'dart:html' as html;
import 'dart:ui_web' as ui_web;

bool _registered = false;

void registerTvIframes() {
  if (_registered) return;
  _registered = true;

  const tickerHtml = '''
<!DOCTYPE html><html>
<head>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <style>html,body{margin:0;padding:0;background:#181a20;overflow:hidden;}</style>
</head>
<body>
  <script type="module"
    src="https://widgets.tradingview-widget.com/w/en/tv-ticker-tape.js"></script>
  <tv-ticker-tape
    symbols="FOREXCOM:SPXUSD,FOREXCOM:NSXUSD,FOREXCOM:DJI,FX:EURUSD,BITSTAMP:BTCUSD,BITSTAMP:ETHUSD,CMCMARKETS:GOLD">
  </tv-ticker-tape>
</body></html>
''';

  const newsHtml = '''
<!DOCTYPE html><html>
<head>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <style>html,body{margin:0;padding:0;background:#181a20;overflow:hidden;}</style>
</head>
<body>
  <div class="tradingview-widget-container">
    <div class="tradingview-widget-container__widget"></div>
    <script type="text/javascript"
      src="https://s3.tradingview.com/external-embedding/embed-widget-timeline.js" async>
    {"displayMode":"regular","feedMode":"all_symbols","colorTheme":"dark",
     "isTransparent":false,"locale":"en","width":"100%","height":550}
    </script>
  </div>
</body></html>
''';

  ui_web.platformViewRegistry.registerViewFactory('tv-ticker', (int id) {
    return html.IFrameElement()
      ..srcdoc = tickerHtml
      ..style.border = 'none'
      ..style.width = '100%'
      ..style.height = '100%'
      ..style.background = '#181a20';
  });

  ui_web.platformViewRegistry.registerViewFactory('tv-news', (int id) {
    return html.IFrameElement()
      ..srcdoc = newsHtml
      ..style.border = 'none'
      ..style.width = '100%'
      ..style.height = '100%'
      ..style.background = '#181a20';
  });
}
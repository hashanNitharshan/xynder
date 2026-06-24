import 'package:flutter/material.dart';

class TvTickerView extends StatelessWidget {
  final String html;
  const TvTickerView({super.key, required this.html});
  @override
  Widget build(BuildContext context) => const SizedBox.shrink();
}

class TvNewsView extends StatelessWidget {
  final String html;
  const TvNewsView({super.key, required this.html});
  @override
  Widget build(BuildContext context) => const SizedBox.shrink();
}

void registerTvViews(String tickerHtml, String newsHtml) {}
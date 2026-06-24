import 'dart:math';
import 'package:flutter/material.dart';

class PinwheelLoader extends StatefulWidget {
  final double size;
  final double stroke;
  final Color color;

  const PinwheelLoader({
    super.key,
    this.size = 35,
    this.stroke = 3.5,
    this.color = const Color(0xffFFB800),
  });

  @override
  State<PinwheelLoader> createState() => _PinwheelLoaderState();
}

class _PinwheelLoaderState extends State<PinwheelLoader>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: widget.size,
      height: widget.size,
      child: AnimatedBuilder(
        animation: _controller,
        builder: (_, __) {
          return Stack(
            alignment: Alignment.center,
            children: List.generate(6, (i) {
              return Transform.rotate(
                angle: (_controller.value * pi) + (i * pi / 6),
                child: Opacity(
                  opacity: 1 - (i * 0.15),
                  child: Container(
                    width: widget.size,
                    height: widget.stroke,
                    decoration: BoxDecoration(
                      color: widget.color,
                      borderRadius: BorderRadius.circular(widget.stroke),
                    ),
                  ),
                ),
              );
            }),
          );
        },
      ),
    );
  }
}
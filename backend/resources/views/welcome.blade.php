<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Xynder Wallet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #06122e;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }

        .home {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 80% 45%, rgba(43, 92, 255, .42), transparent 38%),
                radial-gradient(circle at 18% 16%, rgba(0, 213, 255, .20), transparent 30%),
                linear-gradient(90deg, #06122e 0%, #07183f 52%, #06122e 100%);
        }

        .nav {
            height: 88px;
            padding: 0 42px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 5;
        }

        .logo {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: .4px;
            color: #fff;
        }

        .logo span { color: #21d4ff; }

        .menu {
            display: flex;
            align-items: center;
            gap: 38px;
            color: #d8e8ff;
            font-size: 17px;
            font-weight: 800;
        }

        .menu a { opacity: .95; }

        .menu a:hover { color: #20d9ff; }

        .login-btn {
            padding: 18px 32px;
            border-radius: 10px;
            background: linear-gradient(135deg, #19dcff, #2d7cff);
            color: #fff !important;
            box-shadow: 0 18px 35px rgba(25, 220, 255, .24);
        }

        .hero {
            min-height: calc(100vh - 88px);
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 30px;
            padding: 40px 62px 80px;
            position: relative;
            z-index: 2;
        }

        .left {
            max-width: 680px;
            padding-left: 52px;
        }

        h1 {
            margin: 0;
            font-size: clamp(48px, 5.5vw, 82px);
            line-height: 1.15;
            letter-spacing: -2px;
            color: #bcd3f2;
            font-weight: 900;
        }

        .subtitle {
            margin-top: 28px;
            font-size: 24px;
            color: #9eb5d6;
            line-height: 1.5;
        }

        .cta {
            margin-top: 40px;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 20px 36px;
            border: 2px solid #20d9ff;
            border-radius: 14px;
            font-size: 19px;
            font-weight: 900;
            background: rgba(10, 30, 82, .70);
            box-shadow: 0 0 25px rgba(25, 220, 255, .22);
        }

        .cta:before {
            content: "";
            width: 9px;
            height: 45px;
            border-radius: 99px;
            background: linear-gradient(#19dcff, #2d7cff);
            box-shadow: 0 0 18px rgba(25, 220, 255, .7);
        }

        .visual {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 520px;
            position: relative;
        }

        .terminal {
            width: min(520px, 90%);
            height: 330px;
            border-radius: 24px;
            background: linear-gradient(145deg, #1448aa, #061022);
            border: 10px solid #159eff;
            box-shadow: 0 35px 80px rgba(0,0,0,.55);
            transform: perspective(900px) rotateY(-16deg) rotateX(4deg);
            position: relative;
            padding: 28px;
        }

        .terminal:before {
            content: "";
            position: absolute;
            inset: 24px;
            border-radius: 12px;
            background:
                linear-gradient(135deg, transparent 20%, rgba(0, 224, 255, .35) 21%, transparent 45%),
                repeating-linear-gradient(90deg, #1fe6ff 0 9px, #2d7cff 9px 18px, #bcd3f2 18px 27px);
            opacity: .85;
            clip-path: polygon(0 70%, 12% 50%, 22% 58%, 35% 18%, 47% 44%, 60% 30%, 76% 60%, 100% 35%, 100% 100%, 0 100%);
        }

        .terminal:after {
            content: "Wallet Trading Dashboard";
            position: absolute;
            left: 42px;
            bottom: 35px;
            color: #fff;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: .4px;
        }

        .base {
            position: absolute;
            width: 280px;
            height: 70px;
            bottom: 70px;
            right: 110px;
            border-radius: 20px;
            background: linear-gradient(135deg, #19dcff, #2d7cff);
            transform: perspective(500px) rotateX(62deg) rotateZ(-8deg);
            opacity: .95;
        }

        .bar {
            position: absolute;
            width: 12px;
            height: 72px;
            border-radius: 99px;
            background: linear-gradient(#19dcff, #2d7cff);
            opacity: .55;
            animation: float 4s ease-in-out infinite;
        }

        .b1 { top: 70px; left: 20%; }
        .b2 { top: 170px; left: 38%; height: 100px; }
        .b3 { bottom: 90px; left: 15%; height: 64px; }
        .b4 { top: 210px; right: 26%; }
        .b5 { bottom: 120px; right: 10%; height: 86px; }

        @keyframes float {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-18px); }
        }

        .chat {
            position: fixed;
            right: 26px;
            bottom: 24px;
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #19dcff, #2d7cff);
            display: grid;
            place-items: center;
            font-size: 32px;
            box-shadow: 0 18px 35px rgba(0,0,0,.35);
            z-index: 9;
        }

        .wave {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            height: 65px;
            background: #fff;
            clip-path: polygon(0 52%, 17% 78%, 37% 86%, 57% 83%, 80% 80%, 100% 62%, 100% 100%, 0 100%);
            z-index: 3;
        }

        @media(max-width: 950px) {
            .nav {
                height: auto;
                padding: 22px;
                gap: 20px;
                flex-direction: column;
            }

            .menu {
                gap: 14px;
                flex-wrap: wrap;
                justify-content: center;
                font-size: 14px;
            }

            .hero {
                grid-template-columns: 1fr;
                padding: 40px 22px 100px;
                text-align: center;
            }

            .left {
                padding-left: 0;
                margin: auto;
            }

            .visual { min-height: 360px; }

            .terminal { transform: none; }

            .base { display: none; }
        }
    </style>
</head>

<body>
<section class="home">
    <nav class="nav">
        <a href="{{ route('home') }}" class="logo">
            <span>∞</span> Xynder Wallet
        </a>

        <div class="menu">
            <a href="{{ route('home') }}">Home</a>
            <a href="#about">About Us</a>
            <a href="#products">Wallet Products</a>
            <a href="#platform">Platform</a>
            <a href="#contact">Contact Us</a>

            @auth
                <a class="login-btn" href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a class="login-btn" href="{{ route('login') }}">Log In</a>
            @endauth
        </div>
    </nav>

    <span class="bar b1"></span>
    <span class="bar b2"></span>
    <span class="bar b3"></span>
    <span class="bar b4"></span>
    <span class="bar b5"></span>

    <div class="hero">
        <div class="left">
            <h1>Trade Smarter, Not Harder</h1>
            <div class="subtitle">Advanced wallet tools and insights at your fingertips</div>

            @auth
                <a class="cta" href="{{ route('dashboard') }}">Go Dashboard</a>
            @else
                <a class="cta" href="{{ route('login') }}">Get Started</a>
            @endauth
        </div>

        <div class="visual">
            <div class="terminal"></div>
            <div class="base"></div>
        </div>
    </div>

    <a href="{{ route('login') }}" class="chat">💬</a>
    <div class="wave"></div>
</section>
</body>
</html>
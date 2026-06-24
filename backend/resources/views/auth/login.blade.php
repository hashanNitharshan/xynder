<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Xynder Wallet Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background:
                radial-gradient(circle at 80% 40%, rgba(43, 92, 255, .42), transparent 38%),
                radial-gradient(circle at bottom left, rgba(0, 213, 255, .22), transparent 35%),
                linear-gradient(90deg, #06122e, #07183f);
            color: #fff;
            min-height: 100vh;
        }

        a { text-decoration: none; color: inherit; }

        .page {
            min-height: 100vh;
            padding: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: min(440px, 100%);
            background: rgba(8, 25, 70, .86);
            border: 1px solid rgba(70, 170, 255, .35);
            border-radius: 28px;
            padding: 34px;
            box-shadow: 0 35px 80px rgba(0,0,0,.45);
        }

        .brand {
            display: block;
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 28px;
        }

        .brand span { color: #21d4ff; }

        h1 {
            margin: 0;
            font-size: 34px;
            color: #bcd3f2;
        }

        .hint {
            margin: 10px 0 24px;
            color: #9eb5d6;
            line-height: 1.5;
        }

        label {
            display: block;
            margin: 16px 0 8px;
            color: #a9c5e8;
            font-size: 14px;
            font-weight: 700;
        }

        input {
            width: 100%;
            padding: 15px;
            border-radius: 14px;
            border: 1px solid rgba(82, 166, 255, .35);
            background: rgba(3, 12, 35, .90);
            color: #fff;
            outline: none;
        }

        input:focus {
            border-color: #20d9ff;
            box-shadow: 0 0 0 3px rgba(32, 217, 255, .12);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
            font-size: 14px;
            color: #9eb5d6;
        }

        .remember input { width: auto; }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 16px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #19dcff, #2d7cff);
            color: white;
            font-size: 16px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 12px 30px rgba(25, 220, 255, .22);
        }

        .back {
            display: inline-block;
            margin-top: 18px;
            color: #21d4ff;
            font-size: 14px;
        }

        .error {
            background: rgba(239, 68, 68, .16);
            border: 1px solid rgba(239, 68, 68, .35);
            color: #fecaca;
            padding: 12px;
            border-radius: 14px;
            margin-bottom: 16px;
            font-size: 14px;
        }
    </style>
</head>

<body>
<div class="page">
    <div class="card">
        <a href="{{ route('home') }}" class="brand"><span>∞</span> Xynder Wallet</a>

        <h1>Log In</h1>
        <div class="hint">Login as admin, client or merchant. Your correct dashboard will open automatically.</div>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <label>Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label>Password</label>
            <input type="password" name="password" required>

            <label class="remember">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>

            <button type="submit">Login</button>
        </form>

     <a class="back" href="{{ route('register') }}">Create new account</a>
<br>
<a class="back" href="{{ route('home') }}">← Back to Home</a>
    </div>
</div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BITXNOW Login</title>
<link rel="icon" type="image/jpeg" href="{{ asset('images/bitxnow_logo.jpeg') }}">
<link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/bitxnow_logo.jpeg') }}">
<link rel="apple-touch-icon" href="{{ asset('images/bitxnow_logo.jpeg') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

    <style>
        :root{
            /* Backgrounds */
            --bg:#0B0E11;
            --surface:#181A20;
            --surface-alt:#1E2329;

            /* Borders */
            --border:#2B3139;
            --border-faint:#202020;

            /* BitXnow yellow theme */
            --orange:#F0B90B;
            --amber:#C99400;
            --gold:#FFD45A;

            /* Status */
            --red:#ef4444;

            /* Text */
            --text-primary:#ffffff;
            --text-secondary:#848E9C;
        }

        *{
            box-sizing:border-box;
            margin:0;
            padding:0;
        }

        html,body{
            min-height:100%;
        }

        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            background:var(--bg);
            color:var(--text-primary);
            font-weight:400;
        }

        a{
            text-decoration:none;
            color:inherit;
        }

        /* ===== Top bar ===== */
        .topnav{
            height:64px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 32px;
            border-bottom:1px solid var(--border-faint);
        }

        .topnav-brand{
            display:flex;
            align-items:center;
            gap:8px;
            font-size:17px;
            font-weight:600;
            color:#fff;
        }

        .brand-logo{
            width:26px;
            height:26px;
            object-fit:contain;
            flex-shrink:0;
        }

        .brand-logo-fallback{
            display:flex;
            align-items:center;
            justify-content:center;
            width:26px;
            height:26px;
            background:var(--orange);
            color:#0B0E11;
            font-weight:700;
            font-size:13px;
            border-radius:4px;
        }

        .topnav-lang{
            display:flex;
            align-items:center;
            gap:6px;
            color:var(--text-secondary);
            font-size:13px;
            font-weight:400;
        }

        .topnav-lang i{
            font-size:16px;
        }

        /* ===== Page body ===== */
        .login-wrap{
            min-height:calc(100vh - 64px);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px 20px;
        }

        .login-card{
            width:min(400px,100%);
        }

        .login-card h1{
            font-size:24px;
            font-weight:600;
            margin-bottom:24px;
            color:#fff;
        }

        .error{
            background:rgba(239,68,68,.1);
            border:1px solid rgba(239,68,68,.4);
            color:#ff9b9b;
            padding:12px 14px;
            border-radius:6px;
            margin-bottom:18px;
            font-size:13px;
            font-weight:400;
            display:flex;
            align-items:flex-start;
            gap:8px;
        }

        .error i{
            color:var(--red);
            font-size:17px;
            flex-shrink:0;
        }

        .field{
            margin-bottom:16px;
        }

        .field label{
            display:block;
            margin-bottom:7px;
            color:var(--text-secondary);
            font-size:13px;
            font-weight:400;
        }

        .input-wrap{
            position:relative;
        }

        .input-wrap input{
            width:100%;
            height:46px;
            padding:0 42px 0 14px;
            border-radius:4px;
            border:1px solid var(--border);
            background:var(--surface-alt);
            color:#fff;
            outline:none;
            font-size:14px;
            font-weight:400;
            transition:.15s;
        }

        .input-wrap input:focus{
            border-color:var(--orange);
        }

        .input-wrap input::placeholder{
            color:#5e6673;
        }

        .input-wrap i{
            position:absolute;
            right:14px;
            top:50%;
            transform:translateY(-50%);
            color:var(--text-secondary);
            font-size:18px;
            cursor:pointer;
        }

        .remember-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            margin:4px 0 20px;
        }

        .remember{
            display:flex;
            align-items:center;
            gap:8px;
            color:var(--text-secondary);
            font-size:13px;
            font-weight:400;
            cursor:pointer;
        }

        .remember input{
            width:15px;
            height:15px;
            accent-color:var(--orange);
        }

        .forgot-link{
            color:var(--orange);
            font-size:13px;
            font-weight:400;
        }

        .forgot-link:hover{
            color:var(--gold);
        }

        .login-btn{
            width:100%;
            height:46px;
            border:0;
            border-radius:4px;
            background:var(--orange);
            color:#0B0E11;
            font-size:14px;
            font-weight:600;
            cursor:pointer;
            transition:.15s;
        }

        .login-btn:hover{
            background:var(--gold);
        }

        .register-line{
            margin-top:22px;
            text-align:center;
            color:var(--text-secondary);
            font-size:13px;
            font-weight:400;
        }

        .register-line a{
            color:var(--orange);
            font-weight:500;
        }

        .register-line a:hover{
            color:var(--gold);
        }

        .foot-note{
            margin-top:30px;
            text-align:center;
            color:#5e6673;
            font-size:12px;
            font-weight:400;
            line-height:1.6;
        }

        @media(max-width:520px){
            .topnav{
                padding:0 18px;
            }

            .login-card h1{
                font-size:21px;
            }
        }
    </style>
</head>

<body>

<div class="topnav">
    <a href="{{ route('home') }}" class="topnav-brand">
        <img src="{{ asset('images/bitxnow_logo.jpeg') }}"
             class="brand-logo"
             alt="BitXNow"
             onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('div'),{className:'brand-logo brand-logo-fallback',innerText:'B'}));">
        BitXnow
    </a>

    <div class="topnav-lang">
        <i class="ti ti-world"></i>
        English
    </div>
</div>

<div class="login-wrap">
    <div class="login-card">
        <h1>Log in to BitXnow</h1>

        @if ($errors->any())
            <div class="error">
                <i class="ti ti-alert-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="field">
                <label>Email Address</label>
                <div class="input-wrap">
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter email address"
                           required
                           autofocus>
                </div>
            </div>

            <div class="field">
                <label>Password</label>
                <div class="input-wrap">
                    <input type="password"
                           name="password"
                           id="passwordInput"
                           placeholder="Enter password"
                           required>
                    <i class="ti ti-eye" id="togglePassword"></i>
                </div>
            </div>

            <div class="remember-row">
                <label class="remember">
                    <input type="checkbox" name="remember" value="1">
                    Remember me
                </label>

                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="login-btn">
                Login
            </button>
        </form>

        <div class="register-line">
            New to BitXnow?
            <a href="{{ route('register') }}">Create an account</a>
        </div>

        <div class="foot-note">
            By continuing, you agree to BitXnow's Terms of Service<br>and Privacy Policy.
        </div>
    </div>
</div>

<script>
    const toggle = document.getElementById('togglePassword');
    const pwd = document.getElementById('passwordInput');

    if (toggle && pwd) {
        toggle.addEventListener('click', function () {
            const isHidden = pwd.type === 'password';
            pwd.type = isHidden ? 'text' : 'password';
            toggle.classList.toggle('ti-eye', !isHidden);
            toggle.classList.toggle('ti-eye-off', isHidden);
        });
    }
</script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Xynder Wallet Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css">

    <style>
        :root{
            --dark:#101518;
            --hero:#2b2f32;
            --box:#2b2f32;
            --input:#1f2428;
            --line:#3b4248;
            --red:#e8192c;
            --red2:#c91022;
            --text:#fff;
            --muted:#aeb4ba;
            --muted2:#747b82;
        }

        *{box-sizing:border-box;margin:0;padding:0}
        html,body{min-height:100%}

        body{
            font-family:Inter,Arial,sans-serif;
            background:var(--dark);
            color:var(--text);
        }

        a{text-decoration:none;color:inherit}

        .register-page{
            min-height:100vh;
            display:grid;
            grid-template-columns:1.05fr .95fr;
            background:var(--dark);
        }

        .register-left{
            position:relative;
            background:var(--hero);
            padding:70px 80px;
            overflow:hidden;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
        }

        .register-left::after{
            content:"";
            position:absolute;
            inset:0;
            opacity:.08;
            background-image:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.18) 21%,transparent 22%);
            background-size:260px 260px;
        }

        .brand{
            position:relative;
            z-index:2;
            display:inline-flex;
            align-items:center;
            gap:12px;
            color:#fff;
            font-size:22px;
            font-weight:900;
        }

        .brand-icon{
            width:44px;
            height:44px;
            border-radius:50%;
            background:var(--red);
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 0 0 6px rgba(232,25,44,.13);
            font-size:23px;
        }

        .brand span{color:var(--red)}

        .hero-content{
            position:relative;
            z-index:2;
            max-width:620px;
        }

        .eyebrow{
            color:var(--red);
            font-size:12px;
            font-weight:900;
            letter-spacing:.14em;
            text-transform:uppercase;
            margin-bottom:16px;
        }

        .hero-title{
            font-size:56px;
            line-height:1.08;
            font-weight:900;
            margin-bottom:22px;
        }

        .hero-title span{
            color:var(--red);
            display:block;
        }

        .hero-sub{
            color:#b8bdc2;
            font-size:16px;
            line-height:1.8;
            font-weight:700;
            max-width:520px;
        }

        .hero-cards{
            position:relative;
            z-index:2;
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:14px;
            margin-top:40px;
        }

        .hero-card{
            background:#24292d;
            border:1px solid var(--line);
            border-radius:6px;
            padding:18px;
        }

        .hero-card i{
            color:var(--red);
            font-size:24px;
            margin-bottom:10px;
            display:block;
        }

        .hero-card b{
            display:block;
            font-size:14px;
            font-weight:900;
            margin-bottom:4px;
        }

        .hero-card small{
            color:#9fa5aa;
            font-size:12px;
            font-weight:700;
            line-height:1.5;
        }

        .register-right{
            display:flex;
            align-items:center;
            justify-content:center;
            padding:45px;
            background:var(--dark);
        }

        .register-card{
            width:min(460px,100%);
            background:var(--box);
            border:1.5px solid var(--red);
            border-radius:7px;
            padding:34px;
            box-shadow:0 18px 40px rgba(0,0,0,.35);
        }

        .card-label{
            color:var(--red);
            font-size:12px;
            font-weight:900;
            letter-spacing:.14em;
            text-transform:uppercase;
            margin-bottom:10px;
        }

        .register-card h1{
            color:#fff;
            font-size:34px;
            line-height:1.15;
            font-weight:900;
            margin-bottom:10px;
        }

        .register-card p{
            color:#aeb4ba;
            font-size:14px;
            line-height:1.6;
            font-weight:700;
            margin-bottom:24px;
        }

        .error{
            background:#3a1018;
            border:1px solid #71313a;
            color:#ffb4bc;
            padding:13px 15px;
            border-radius:4px;
            margin-bottom:18px;
            font-size:14px;
            font-weight:800;
        }

        .field{margin-bottom:16px}

        .field label{
            display:block;
            margin-bottom:8px;
            color:#9fa5aa;
            font-size:11px;
            font-weight:900;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .input-wrap{position:relative}

        .input-wrap i{
            position:absolute;
            left:14px;
            top:50%;
            transform:translateY(-50%);
            color:#747b82;
            font-size:19px;
        }

        .input-wrap input{
            width:100%;
            height:52px;
            padding:0 14px 0 45px;
            border-radius:4px;
            border:1px solid var(--line);
            background:var(--input);
            color:#fff;
            outline:none;
            font-size:14px;
            font-weight:700;
        }

        .input-wrap input:focus{
            border-color:var(--red);
            box-shadow:0 0 0 3px rgba(232,25,44,.12);
        }

        .input-wrap input::placeholder{color:#747b82}

        .register-btn{
            width:100%;
            height:54px;
            border:0;
            border-radius:4px;
            background:var(--red);
            color:#fff;
            font-size:15px;
            font-weight:900;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:9px;
            transition:.15s;
            margin-top:6px;
        }

        .register-btn:hover{background:var(--red2)}

        .login-line{
            margin-top:22px;
            text-align:center;
            color:#aeb4ba;
            font-size:14px;
            font-weight:700;
        }

        .login-line a{
            color:var(--red);
            font-weight:900;
        }

        .back-home{
            display:inline-flex;
            align-items:center;
            gap:7px;
            margin-top:18px;
            color:#aeb4ba;
            font-size:13px;
            font-weight:800;
        }

        .back-home:hover{color:#fff}

        @media(max-width:980px){
            .register-page{grid-template-columns:1fr}
            .register-left{
                min-height:360px;
                padding:45px 28px;
            }
            .hero-title{font-size:40px}
            .hero-cards{grid-template-columns:1fr}
            .register-right{padding:30px 20px 45px}
        }

        @media(max-width:520px){
            .register-left{padding:34px 20px}
            .hero-title{font-size:34px}
            .hero-cards{display:none}
            .register-card{padding:26px 20px}
            .register-card h1{font-size:30px}
        }
    </style>
</head>

<body>
<div class="register-page">

    <section class="register-left">
        <a href="{{ route('home') }}" class="brand">
            <div class="brand-icon">
                <i class="ti ti-infinity"></i>
            </div>
            Xynder <span>Wallet</span>
        </a>

        <div class="hero-content">
            <div class="eyebrow">Create Wallet Account</div>

            <h2 class="hero-title">
                Join
                <span>Xynder Wallet</span>
            </h2>

            <div class="hero-sub">
                Create your account to access wallet requests, USD transfers, transaction chats, profile settings, and full history.
            </div>

            <div class="hero-cards">
                <div class="hero-card">
                    <i class="ti ti-user-plus"></i>
                    <b>Simple Signup</b>
                    <small>Only basic account details are needed to get started.</small>
                </div>

                <div class="hero-card">
                    <i class="ti ti-shield-lock"></i>
                    <b>Secure Access</b>
                    <small>Your account is protected with password security.</small>
                </div>

                <div class="hero-card">
                    <i class="ti ti-wallet"></i>
                    <b>Wallet Ready</b>
                    <small>Login after signup and complete your profile later.</small>
                </div>
            </div>
        </div>

        <div></div>
    </section>

    <section class="register-right">
        <div class="register-card">
            <div class="card-label">New Account</div>

            <h1>Create Account</h1>
            <p>Enter your basic details to create your Xynder Wallet account.</p>

            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}">
                @csrf

                <input type="hidden" name="role" value="client">

                <div class="field">
                    <label>Full Name</label>
                    <div class="input-wrap">
                        <i class="ti ti-user"></i>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Enter full name"
                               required
                               autofocus>
                    </div>
                </div>

                <div class="field">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="ti ti-mail"></i>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="Enter email address"
                               required>
                    </div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="ti ti-lock"></i>
                        <input type="password"
                               name="password"
                               placeholder="Enter password"
                               required>
                    </div>
                </div>

                <div class="field">
                    <label>Confirm Password</label>
                    <div class="input-wrap">
                        <i class="ti ti-lock-check"></i>
                        <input type="password"
                               name="password_confirmation"
                               placeholder="Confirm password"
                               required>
                    </div>
                </div>

                <button type="submit" class="register-btn">
                    Create Account
                    <i class="ti ti-arrow-right"></i>
                </button>
            </form>

            <div class="login-line">
                Already have account?
                <a href="{{ route('login') }}">Login</a>
            </div>

            <a class="back-home" href="{{ route('home') }}">
                <i class="ti ti-arrow-left"></i>
                Back to home
            </a>
        </div>
    </section>

</div>
</body>
</html>
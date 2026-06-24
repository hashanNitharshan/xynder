<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Xynder Wallet Signup</title>
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
            width: min(760px, 100%);
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
            margin-bottom: 24px;
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

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .full { grid-column: 1 / -1; }

        label {
            display: block;
            margin: 0 0 8px;
            color: #a9c5e8;
            font-size: 14px;
            font-weight: 700;
        }

        input, select, textarea {
            width: 100%;
            padding: 15px;
            border-radius: 14px;
            border: 1px solid rgba(82, 166, 255, .35);
            background: rgba(3, 12, 35, .90);
            color: #fff;
            outline: none;
        }

        textarea { min-height: 96px; resize: vertical; }

        input:focus, select:focus, textarea:focus {
            border-color: #20d9ff;
            box-shadow: 0 0 0 3px rgba(32, 217, 255, .12);
        }

        .section {
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px solid rgba(82, 166, 255, .20);
        }

        .section-title {
            margin-bottom: 16px;
            color: #21d4ff;
            font-weight: 900;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin-top: 26px;
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

        .links {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            margin-top: 18px;
            color: #21d4ff;
            font-size: 14px;
            flex-wrap: wrap;
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

        @media (max-width: 700px) {
            .page { padding: 18px; }
            .card { padding: 24px; }
            .grid { grid-template-columns: 1fr; }
            h1 { font-size: 30px; }
        }
    </style>
</head>

<body>
<div class="page">
    <div class="card">
        <a href="{{ route('home') }}" class="brand"><span>∞</span> Xynder Wallet</a>

        <h1>Create Account</h1>
        <div class="hint">Signup as client or merchant. After registration your dashboard will open automatically.</div>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data">
            @csrf

            <div class="section">
                <div class="section-title">Account Type</div>
                <div class="grid">
                    <div class="full">
                        <label>Register As</label>
                        <select name="role" required>
                            <option value="client" @selected(old('role') === 'client')>Client</option>
                            <option value="merchant" @selected(old('role') === 'merchant')>Merchant</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Personal Details</div>
                <div class="grid">
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div>
                        <label>Original / Legal Name</label>
                        <input type="text" name="original_name" value="{{ old('original_name') }}">
                    </div>

                    <div>
                        <label>Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div>
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}">
                    </div>

                    <div>
                        <label>Country</label>
                        <input type="text" name="country" value="{{ old('country') }}">
                    </div>

                    <div>
                        <label>State</label>
                        <input type="text" name="state" value="{{ old('state') }}">
                    </div>

                    <div class="full">
                        <label>Address</label>
                        <textarea name="address">{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label>Profile Photo</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>

                    <div>
                        <label>Aadhaar Card Photo</label>
                        <input type="file" name="aadhaar_photo" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Bank Details</div>
                <div class="grid">
                    <div>
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}">
                    </div>

                    <div>
                        <label>Branch</label>
                        <input type="text" name="branch" value="{{ old('branch') }}">
                    </div>

                    <div>
                        <label>Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}">
                    </div>

                    <div>
                        <label>Account Type</label>
                        <input type="text" name="account_type" value="{{ old('account_type') }}">
                    </div>

                    <div class="full">
                        <label>IFSC</label>
                        <input type="text" name="ifsc" value="{{ old('ifsc') }}">
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">UPI Details</div>
                <div class="grid">
                    <div>
                        <label>UPI Account Name</label>
                        <input type="text" name="upi_name" value="{{ old('upi_name') }}">
                    </div>

                    <div>
                        <label>UPI ID</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id') }}">
                    </div>

                    <div class="full">
                        <label>UPI QR</label>
                        <input type="file" name="upi_qr" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Security</div>
                <div class="grid">
                    <div>
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div>
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" required>
                    </div>
                </div>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <div class="links">
            <a href="{{ route('login') }}">Already have account? Login</a>
            <a href="{{ route('home') }}">← Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>
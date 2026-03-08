<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Code - Payroll System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .content {
            padding: 40px 30px;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #4a5568;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .code-input {
            font-size: 24px;
            letter-spacing: 8px;
            text-align: center;
            font-weight: 600;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .links {
            margin-top: 25px;
            text-align: center;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #c6f6d5;
            border: 1px solid #9ae6b4;
            color: #22543d;
        }

        .alert-danger {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #742a2a;
        }

        .text-muted {
            color: #718096;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>🔐 Verify Code</h1>
                <p>Enter the 6-digit code sent to your email</p>
            </div>

            <div class="content">
                @if(session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                <div class="info-box">
                    <strong>📧 Email:</strong> {{ session('activation_email') }}<br>
                    <strong>📱 Phone:</strong> {{ session('activation_phone') }}
                </div>

                <form method="POST" action="{{ route('activate.verify') }}">
                    @csrf

                    <div class="form-group">
                        <label for="code">6-Digit Verification Code</label>
                        <input type="text" 
                               class="form-control code-input" 
                               id="code" 
                               name="code" 
                               required 
                               maxlength="6"
                               pattern="[0-9]{6}"
                               placeholder="••••••"
                               autocomplete="off"
                               autofocus>
                        <div class="text-muted">Enter the 6-digit code from your email</div>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required 
                               minlength="6"
                               placeholder="Enter new password">
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required 
                               minlength="6"
                               placeholder="Confirm new password">
                    </div>

                    <button type="submit" class="btn">
                        ✅ Activate Account
                    </button>
                </form>

                <div class="links">
                    <form method="POST" action="{{ route('activate.resend') }}" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: #667eea; cursor: pointer; font-size: 14px; font-weight: 500;">
                            🔄 Didn't receive code? Resend
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
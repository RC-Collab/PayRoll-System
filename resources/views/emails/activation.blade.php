<!DOCTYPE html>
<html>
<head>
    <title>Account Activation</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .code-box {
            background: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-size: 40px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #667eea;
            font-family: monospace;
        }
        .expiry {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .button {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Account Activation</h1>
            <p>Payroll System</p>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>You have requested to activate your account. Please use the following activation code:</p>
            
            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>
            
            <p class="expiry">⏰ This code will expire at <strong>{{ $expiry }}</strong> (15 minutes)</p>
            
            <p>If you didn't request this, please ignore this email or contact your administrator.</p>
            
            <center>
                <p style="color: #666; font-size: 14px;">Enter this code in the app to activate your account</p>
            </center>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Payroll System. All rights reserved.</p>
            <p>This is an automated message, please do not reply.</p>
        </div>
    </div>
</body>
</html>

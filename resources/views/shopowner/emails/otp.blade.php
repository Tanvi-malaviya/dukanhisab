<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - DukanHisab</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #0f766e;
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 32px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .otp-container {
            background-color: #f0fdfa;
            border: 2px dashed #0f766e;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            color: #0f766e;
            letter-spacing: 6px;
            margin: 0;
        }
        .expiry {
            font-size: 14px;
            color: #64748b;
            text-align: center;
            margin-top: -12px;
            margin-bottom: 24px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #0f766e;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dukan<span style="color: #14b8a6;">Hisab</span></h1>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $user->name }},</div>
            <p>Thank you for signing up for DukanHisab Shop Panel. To complete your registration and verify your email address, please use the following One-Time Password (OTP):</p>
            
            <div class="otp-container">
                <p class="otp-code">{{ $otp_code }}</p>
            </div>
            
            <p class="expiry">This OTP is valid for <strong>10 minutes</strong>. If you did not request this code, you can safely ignore this email.</p>
            
            <p>Welcome aboard,<br><strong>The DukanHisab Team</strong></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DukanHisab. All rights reserved.
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notification from DukanHisab' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 30px 15px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .header h1 span {
            color: #2dd4bf;
        }
        .header p {
            color: #ccfbf1;
            margin: 4px 0 0 0;
            font-size: 13px;
        }
        .content {
            padding: 32px 28px;
            font-size: 15px;
            line-height: 1.65;
            color: #334155;
        }
        .content p {
            margin: 0 0 16px 0;
        }
        .content strong {
            color: #0f172a;
        }
        .content ul, .content ol {
            margin: 12px 0 18px 0;
            padding-left: 22px;
        }
        .content li {
            margin-bottom: 8px;
        }
        .box-highlight {
            background-color: #f0fdfa;
            border-left: 4px solid #0f766e;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin: 20px 0;
        }
        .cta-buttons {
            margin: 26px 0 20px 0;
            text-align: center;
        }
        .cta-btn {
            display: inline-block;
            background-color: #0f766e;
            color: #ffffff !important;
            padding: 11px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            margin: 6px 4px;
        }
        .cta-btn-outline {
            display: inline-block;
            background-color: #ffffff;
            color: #0f766e !important;
            border: 1px solid #0f766e;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            margin: 6px 4px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 22px 28px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            line-height: 1.5;
        }
        .footer a {
            color: #0f766e;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Dukan<span>Hisab</span></h1>
                <p>Apni Dukaan Ka Hisab, Ab Ek App Mein!</p>
            </div>
            <div class="content">
                {!! $htmlContent !!}
            </div>
            <div class="footer">
                <p style="margin: 0 0 6px 0;">&copy; {{ date('Y') }} DukanHisab. All rights reserved.</p>
                <p style="margin: 0;">Need help? <a href="https://dukanhisab.in/shop/login">Contact Support</a> &bull; <a href="https://play.google.com/store/apps/details?id=com.app.dukanhisab">Download App</a></p>
            </div>
        </div>
    </div>
</body>
</html>

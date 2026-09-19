<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $headline }} - DukanHisab</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #0f172a; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #0f766e; padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 32px; line-height: 1.6; }
        .greeting { font-size: 18px; font-weight: 600; margin-bottom: 16px; }
        .headline { font-size: 16px; font-weight: 700; color: #0f766e; margin: 0 0 8px 0; }
        .detail-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin: 16px 0; }
        .detail-row { font-size: 14px; padding: 4px 0; }
        .detail-label { color: #64748b; display: inline-block; min-width: 120px; }
        .footer { background-color: #f8fafc; padding: 24px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dukan<span style="color: #14b8a6;">Hisab</span></h1>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $user->name }},</div>
            <p class="headline">{{ $headline }}</p>
            <p>{{ $messageText }}</p>

            @if(!empty($details))
                <div class="detail-box">
                    @foreach($details as $label => $value)
                        <div class="detail-row"><span class="detail-label">{{ $label }}</span><strong>{{ $value }}</strong></div>
                    @endforeach
                </div>
            @endif

            <p>If you have any questions, contact us from the Support section in the DukanHisab app or web panel.</p>
            <p>Thanks,<br><strong>The DukanHisab Team</strong></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DukanHisab. All rights reserved.
        </div>
    </div>
</body>
</html>

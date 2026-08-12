<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Support Ticket - DukanHisab</title>
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
        .meta-row {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .ticket-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .ticket-subject {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 12px 0;
        }
        .ticket-message {
            white-space: pre-wrap;
            font-size: 14px;
            color: #334155;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dukan<span style="color: #14b8a6;">Hisab</span></h1>
        </div>
        <div class="content">
            <div class="greeting">New Support Ticket #{{ $ticket->id }}</div>
            <p class="meta-row">From: {{ $user->name }} ({{ $user->email }})</p>
            <p class="meta-row">Submitted: {{ $ticket->created_at->format('d M Y, h:i A') }}</p>

            <div class="ticket-box">
                <p class="ticket-subject">{{ $ticket->subject }}</p>
                <p class="ticket-message">{{ $ticket->message }}</p>
            </div>

            <p>Please review and reply to this ticket from the admin panel.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DukanHisab. All rights reserved.
        </div>
    </div>
</body>
</html>

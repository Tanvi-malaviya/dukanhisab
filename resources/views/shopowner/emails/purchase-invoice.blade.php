<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice {{ $purchase->purchase_number }} - {{ $shop->name }}</title>
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
        .invoice-box {
            background-color: #f0fdfa;
            border: 2px dashed #0f766e;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
        }
        .invoice-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-box td {
            padding: 4px 0;
            font-size: 14px;
        }
        .invoice-box td.label {
            color: #64748b;
        }
        .invoice-box td.value {
            text-align: right;
            font-weight: 600;
        }
        .amount {
            font-size: 28px;
            font-weight: 800;
            color: #0f766e;
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
            <h1>{{ $shop->name }}</h1>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $purchase->supplier->name ?? 'Supplier' }},</div>
            <p>Please find the purchase invoice for the recent transaction attached to this email as a PDF.</p>

            <div class="invoice-box">
                <table>
                    <tr>
                        <td class="label">Invoice No</td>
                        <td class="value">{{ $purchase->purchase_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date</td>
                        <td class="value">{{ $purchase->purchase_date->format('d M, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Payment Method</td>
                        <td class="value">{{ $purchase->payment_type }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="padding-top: 12px;">Total Amount</td>
                        <td class="value amount" style="padding-top: 12px;">₹ {{ number_format($purchase->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>

            <p>If you have any questions about this invoice, please reach out to us directly.</p>

            <p>Thank you,<br><strong>{{ $shop->name }}</strong></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DukanHisab. All rights reserved.
        </div>
    </div>
</body>
</html>

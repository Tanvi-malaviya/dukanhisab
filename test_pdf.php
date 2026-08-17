<?php
// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: NotoSansGujarati;
            font-style: normal;
            font-weight: normal;
            src: url("' . public_path('fonts/NotoSansGujarati-Regular.ttf') . '") format("truetype");
        }
        body {
            font-family: NotoSansGujarati, sans-serif;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div>Normal "બિલ" (ba + i + la): બિલ</div>
    <div>Swapped "િબલ" (i + ba + la): િબલ</div>
</body>
</html>
';

$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
try {
    $pdf = Pdf::loadHTML($html);
    $pdf->save(__DIR__ . '/test.pdf');
    echo "PDF generated successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

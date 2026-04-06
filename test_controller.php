<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $controller = $app->make(App\Http\Controllers\ExportController::class);
    $request = new Illuminate\Http\Request();
    $request->merge(['year' => 2026, 'month' => 3, 'station' => 'All']);
    $response = $controller->attendanceReport($request);
    echo "Response type: " . get_class($response) . "\n";
    
    // Try to actually execute the stream
    ob_start();
    $response->sendContent();
    $content = ob_get_clean();
    file_put_contents(__DIR__ . '/test_controller_out.xlsx', $content);
    echo "SUCCESS: " . strlen($content) . " bytes written to test_controller_out.xlsx\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}

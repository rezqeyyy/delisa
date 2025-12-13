<?php
/**
 * TEST ROUTES PASIEN NIFAS - PUSKESMAS
 * Jalankan: php test_routes.php
 */

echo "==========================================\n";
echo "TEST ROUTES PASIEN NIFAS - PUSKESMAS\n";
echo "==========================================\n";

// 1. Clear route cache
echo "1. Clearing route cache...\n";
exec('php artisan route:clear');

// 2. List all routes for pasien-nifas
echo "\n2. Listing all pasien-nifas routes:\n";
exec('php artisan route:list --name=puskesmas.pasien-nifas', $output);
foreach ($output as $line) {
    echo $line . "\n";
}

// 3. Test route existence
echo "\n3. Testing Route URLs:\n";

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

$routes = [
    // New universal routes
    'puskesmas.pasien-nifas.show' => ['type' => 'rs', 'id' => 1],
    'puskesmas.pasien-nifas.form-kf' => ['type' => 'rs', 'id' => 1, 'jenisKf' => 1],
    'puskesmas.pasien-nifas.kf.pdf' => ['type' => 'rs', 'id' => 1, 'jenisKf' => 1],
    'puskesmas.pasien-nifas.all-kf.pdf' => ['type' => 'rs', 'id' => 1],
    
    // Legacy routes
    'puskesmas.pasien-nifas.show.legacy' => ['id' => 1],
    'puskesmas.pasien-nifas.form-kf.legacy' => ['id' => 1, 'jenisKf' => 1],
    'puskesmas.pasien-nifas.kf.pdf.legacy' => ['id' => 1, 'jenisKf' => 1],
    'puskesmas.pasien-nifas.all-kf.pdf.legacy' => ['id' => 1],
];

foreach ($routes as $name => $params) {
    try {
        $url = route($name, $params);
        echo "✓ $name\n   URL: $url\n\n";
    } catch (Exception $e) {
        echo "✗ $name\n   ERROR: " . $e->getMessage() . "\n\n";
    }
}

// 4. Test URL patterns
echo "\n4. Testing URL Patterns:\n";
$urls = [
    '/puskesmas/pasien-nifas',
    '/puskesmas/pasien-nifas/rs/1',
    '/puskesmas/pasien-nifas/bidan/1',
    '/puskesmas/pasien-nifas/rs/1/kf/1',
    '/puskesmas/pasien-nifas/bidan/1/kf/1',
    '/puskesmas/pasien-nifas/1',
    '/puskesmas/pasien-nifas/1/kf/1',
];

foreach ($urls as $url) {
    try {
        $route = Route::getRoutes()->match(
            Illuminate\Http\Request::create($url, 'GET')
        );
        echo "✓ $url\n   Route: " . $route->getName() . "\n\n";
    } catch (Exception $e) {
        echo "✗ $url\n   ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "\n==========================================\n";
echo "TEST SELESAI\n";
echo "==========================================\n";
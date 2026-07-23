<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 1. Bangun "kamar-kamar" wajib untuk Laravel di dalam folder /tmp
$directories = [
    '/tmp/storage/app',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

// 2. Perintahkan Laravel untuk memakai kamar yang baru dibuat
$app->useStoragePath('/tmp/storage');
$app->usePublicPath(__DIR__ . '/../public');

// 3. Jalankan aplikasi seperti biasa
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
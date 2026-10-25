<?php

// Vercel serverless environment: asegurar directorios temporales escribibles
$dirs = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions',
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

putenv('VIEW_COMPILED_PATH=/tmp/views');
putenv('APP_CONFIG_CACHE=/tmp/config.php');
putenv('APP_ROUTES_CACHE=/tmp/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/packages.php');

// Supabase Smart Configuration para Vercel
// Si Vercel tiene el host viejo de Clever Cloud, o no tiene host, o tiene el host IPv6 directo de Supabase
$currentHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? ''));

if (empty($currentHost) || str_contains($currentHost, 'clever-cloud') || str_contains($currentHost, '127.0.0.1')) {
    $supabaseConfig = [
        'DB_CONNECTION' => 'pgsql',
        'DB_HOST' => 'aws-0-us-east-1.pooler.supabase.com',
        'DB_PORT' => '5432',
        'DB_DATABASE' => 'postgres',
        'DB_USERNAME' => 'postgres.ysdeyhdugaxvorwzdwqm',
        'DB_PASSWORD' => 'BocaCampeon2026',
        'DB_SSLMODE' => 'require',
    ];
    foreach ($supabaseConfig as $k => $v) {
        putenv("{$k}={$v}");
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }
} elseif (str_contains($currentHost, 'db.ysdeyhdugaxvorwzdwqm.supabase.co')) {
    // Si se configuró el host directo IPv6, redirigir al pooler IPv4 para evitar timeouts en Vercel
    $pooler = 'aws-0-us-east-1.pooler.supabase.com';
    putenv("DB_HOST={$pooler}");
    $_ENV['DB_HOST'] = $pooler;
    $_SERVER['DB_HOST'] = $pooler;
}

// Redirigir la petición al Front Controller de Laravel
require __DIR__ . '/../public/index.php';

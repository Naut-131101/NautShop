<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

// ── Error / Exception logging ────────────────────────────────────────────────
$logFile = BASE_PATH . '/storage/logs/app.log';

ini_set('log_errors', '1');
ini_set('error_log', $logFile);
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e) use ($logFile): void {
    $msg = sprintf(
        "[%s] UNCAUGHT %s: %s in %s:%d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    file_put_contents($logFile, $msg, FILE_APPEND | LOCK_EX);

    http_response_code(500);
    if (function_exists('env') && env('APP_DEBUG') === 'true') {
        echo '<pre>' . htmlspecialchars($msg) . '</pre>';
    } else {
        echo '500 Internal Server Error';
    }
});

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use ($logFile): bool {
    $msg = sprintf(
        "[%s] PHP Error [%d]: %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    file_put_contents($logFile, $msg, FILE_APPEND | LOCK_EX);
    return false; // tiếp tục xử lý lỗi mặc định của PHP
});

/**
 * Custom PSR-4 autoloader – thay thế vendor/autoload.php của Composer.
 * Hỗ trợ namespace App\ (app/) và Core\ (core/).
 */
spl_autoload_register(function (string $class): void {
    $map = [
        'App\\'  => BASE_PATH . '/app/',
        'Core\\' => BASE_PATH . '/core/',
    ];

    foreach ($map as $prefix => $dir) {
        $len = strlen($prefix);

        if (strncmp($class, $prefix, $len) !== 0) {
            continue;
        }

        $file = $dir . str_replace('\\', '/', substr($class, $len)) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Load helper functions (không dùng autoloader)
require BASE_PATH . '/app/Helpers/helpers.php';

$app = require BASE_PATH . '/bootstrap/app.php';
$app->run();

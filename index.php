<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

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

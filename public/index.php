<?php

declare(strict_types=1);

/**
 * Entry point kế thừa – chuyển sang root index.php.
 *
 * File này được giữ lại để .htaccess trong public/ vẫn hoạt động,
 * nhưng thực tế tất cả request đều đi qua root index.php (htdocs/index.php).
 */

define('BASE_PATH', dirname(__DIR__));

// Custom PSR-4 autoloader (không dùng vendor/autoload.php nữa)
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

require BASE_PATH . '/app/Helpers/helpers.php';

$app = require BASE_PATH . '/bootstrap/app.php';
$app->run();

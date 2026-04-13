<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

// Dùng Composer autoloader – đã map sẵn App\, Core\, Google\, v.v.
require BASE_PATH . '/vendor/autoload.php';

// Load helper functions (không dùng autoloader)
require BASE_PATH . '/app/Helpers/helpers.php';

$app = require BASE_PATH . '/bootstrap/app.php';
$app->run();

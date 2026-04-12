<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Naut Shop'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url' => env('APP_URL', 'http://localhost'),
];
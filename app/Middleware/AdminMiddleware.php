<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Session;

class AdminMiddleware
{
    public static function handle(): void
    {
        // 1) Phải đăng nhập
        JwtAuthMiddleware::handle();

        // 2) Phải có role = admin
        $user = Session::get('auth_user');

        if (!$user || ($user['role'] ?? 'user') !== 'admin') {
            redirect('/products');
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!is_auth()) {
            \Core\Session::flash('errors', [
                'general' => ['Vui lòng đăng nhập để truy cập trang sản phẩm.']
            ]);

            redirect('/login');
        }
    }
}
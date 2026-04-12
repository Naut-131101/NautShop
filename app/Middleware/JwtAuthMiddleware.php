<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Session;

/**
 * JwtAuthMiddleware – phiên bản đơn giản hóa (JWT đã bị loại bỏ).
 *
 * Chỉ kiểm tra session có auth_user hay không.
 * Giữ nguyên tên class để không phải sửa tất cả controller đang dùng nó.
 */
class JwtAuthMiddleware
{
    public static function handle(): void
    {
        if (!Session::has('auth_user')) {
            Session::flash('errors', [
                'general' => ['Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.']
            ]);

            redirect('/login');
        }
    }
}

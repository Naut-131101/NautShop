<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;
use Core\Session;

class ProfileCompletedMiddleware
{
    public static function handle(): void
    {
        $authUser = auth();

        if (!$authUser) {
            Session::flash('errors', [
                'general' => ['Bạn chưa đăng nhập.']
            ]);
            redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findById((int) $authUser['id']);

        if (!$user) {
            Session::flash('errors', [
                'general' => ['Không tìm thấy tài khoản.']
            ]);
            redirect('/login');
        }

        if (empty($user['phone'])) {
            Session::flash('errors', [
                'general' => ['Vui lòng hoàn tất hồ sơ trước khi tiếp tục.']
            ]);
            redirect('/complete-profile');
        }
    }
}
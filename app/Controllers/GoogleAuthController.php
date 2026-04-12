<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * GoogleAuthController – tính năng bị vô hiệu hóa.
 *
 * google/apiclient đã bị loại bỏ để tương thích với shared hosting.
 * Chuyển hướng người dùng về trang đăng nhập với thông báo.
 */
class GoogleAuthController extends Controller
{
    public function redirectToGoogle(Request $request, Response $response): void
    {
        Session::flash('errors', [
            'general' => ['Đăng nhập bằng Google hiện không khả dụng. Vui lòng dùng email và mật khẩu.']
        ]);

        $this->redirect('/login');
    }

    public function handleGoogleCallback(Request $request, Response $response): void
    {
        Session::flash('errors', [
            'general' => ['Đăng nhập bằng Google hiện không khả dụng. Vui lòng dùng email và mật khẩu.']
        ]);

        $this->redirect('/login');
    }
}

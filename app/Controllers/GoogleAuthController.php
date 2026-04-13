<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\GoogleAuthService;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class GoogleAuthController extends Controller
{
    private GoogleAuthService $googleAuth;
    private User $userModel;

    public function __construct()
    {
        $this->googleAuth = new GoogleAuthService();
        $this->userModel  = new User();
    }

    /**
     * Chuyển hướng người dùng đến trang đăng nhập của Google.
     */
    public function redirectToGoogle(Request $request, Response $response): void
    {
        $authUrl = $this->googleAuth->getAuthUrl();

        if (empty($authUrl)) {
            Session::flash('errors', [
                'general' => ['Không thể kết nối với Google. Vui lòng thử lại sau.']
            ]);
            $this->redirect('/login');
        }

        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Xử lý callback từ Google sau khi người dùng xác thực.
     */
    public function handleGoogleCallback(Request $request, Response $response): void
    {
        $code = $request->input('code', '');

        if (empty($code)) {
            Session::flash('errors', [
                'general' => ['Đăng nhập bằng Google thất bại. Vui lòng thử lại.']
            ]);
            $this->redirect('/login');
        }

        $googleUser = $this->googleAuth->handleCallback($code);

        if ($googleUser === false) {
            Session::flash('errors', [
                'general' => ['Không thể lấy thông tin từ Google. Vui lòng thử lại.']
            ]);
            $this->redirect('/login');
        }

        // Tìm user theo google_id
        $user = $this->userModel->findByGoogleId($googleUser['id']);

        if (!$user) {
            // Tìm theo email (nếu đã đăng ký bằng email)
            $user = $this->userModel->findByEmail($googleUser['email']);

            if ($user) {
                // Liên kết google_id vào tài khoản email có sẵn
                $this->userModel->linkGoogleId((int) $user['id'], $googleUser['id']);
                $user = $this->userModel->findById((int) $user['id']);
            } else {
                // Tạo tài khoản mới
                $this->userModel->create([
                    'name'      => $googleUser['name'],
                    'email'     => $googleUser['email'],
                    'phone'     => '',
                    'password'  => '',
                    'google_id' => $googleUser['id'],
                ]);

                $user = $this->userModel->findByEmail($googleUser['email']);
            }
        }

        if (!$user) {
            Session::flash('errors', [
                'general' => ['Đăng nhập bằng Google thất bại. Vui lòng thử lại.']
            ]);
            $this->redirect('/login');
        }

        Session::regenerate();

        Session::set('auth_user', [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'role'  => $user['role'] ?? 'user',
        ]);

        // Nếu chưa có số điện thoại, yêu cầu hoàn tất hồ sơ
        if (empty($user['phone'])) {
            $this->redirect('/complete-profile');
        }

        Session::flash('success', 'Đăng nhập bằng Google thành công!');
        $this->redirect('/products');
    }
}

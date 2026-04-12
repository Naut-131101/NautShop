<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\OrderService;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected OrderService $orders;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->orders = new OrderService();
    }

    public function showRegister(Request $request, Response $response): void
    {
        $this->view('auth/register', [
            'title' => t('title.register'),
            'errors' => flash('errors', []),
            'success' => flash('success'),
        ]);
    }

    public function register(Request $request, Response $response): void
    {
        $data = $request->only([
            'name',
            'email',
            'phone',
            'password',
            'password_confirmation',
        ]);

        Session::putOld($data);

        $result = $this->authService->register($data);

        if (!$result['success']) {
            Session::flash('errors', $result['errors']);
            $this->redirect('/register');
        }

        Session::clearOld();
        Session::flash('success', t('flash.auth.register_success'));

        $this->redirect('/login');
    }

    public function showLogin(Request $request, Response $response): void
    {
        $this->view('auth/login', [
            'title' => t('title.login'),
            'errors' => flash('errors', []),
            'success' => flash('success'),
        ]);
    }

    public function login(Request $request, Response $response): void
    {
        $data = $request->only([
            'email',
            'password',
        ]);

        Session::putOld($data);

        $result = $this->authService->login($data);

        if (!$result['success']) {
            Session::flash('errors', $result['errors']);
            $this->redirect('/login');
        }

        Session::clearOld();
        Session::regenerate();

        $user = $result['user'];

        // Lưu thông tin user vào session (không dùng JWT)
        Session::set('auth_user', [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role'  => $user['role'] ?? 'user',
        ]);

        Session::flash('success', t('flash.auth.login_success'));
        $this->redirect('/products');
    }

    public function logout(Request $request, Response $response): void
    {
        // Hủy đơn hàng đang chờ thanh toán (nếu có)
        $this->orders->cancelPendingOrder(null, 'logout_before_payment');

        // Xóa session
        Session::destroy();

        Session::start();
        Session::flash('success', t('flash.auth.logout_success'));

        $this->redirect('/login');
    }

    /**
     * Endpoint /token/refresh – không còn cần thiết, giữ lại để tránh 404.
     */
    public function refresh(Request $request, Response $response): void
    {
        $response->json([
            'success' => false,
            'message' => 'JWT đã bị loại bỏ. Vui lòng đăng nhập lại.'
        ], 410);
    }
}

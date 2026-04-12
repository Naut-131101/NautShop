<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\ForgotPasswordController;
use App\Controllers\GoogleAuthController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\ProfileController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminProductController;
use App\Controllers\Admin\AdminOrderController;
use App\Controllers\Admin\AdminUserController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/db-test', [HomeController::class, 'dbTest']);

$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/show', [ProductController::class, 'show']);
$router->get('/products/cache-clear', [ProductController::class, 'clearCache']);

$router->get('/cart', [CartController::class, 'index']);
$router->get('/orders', [CartController::class, 'orders']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/buy-now', [CartController::class, 'buyNow']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);

$router->get('/checkout', [CartController::class, 'checkout']);
$router->post('/checkout', [CartController::class, 'placeOrder']);
$router->post('/checkout/voucher', [CartController::class, 'applyVoucher']);
$router->post('/checkout/voucher/remove', [CartController::class, 'removeVoucher']);
$router->get('/checkout/payment', [CartController::class, 'payment']);
$router->post('/checkout/payment/confirm', [CartController::class, 'confirmPayment']);
$router->post('/checkout/payment/cancel', [CartController::class, 'cancelPayment']);
$router->post('/checkout/payment/abandon', [CartController::class, 'abandonPayment']);
$router->get('/checkout/success', [CartController::class, 'success']);

$router->get('/token/refresh', [AuthController::class, 'refresh']);

$router->get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm']);
$router->post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);

$router->get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerifyForm']);
$router->post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp']);

$router->get('/reset-password', [ForgotPasswordController::class, 'showResetForm']);
$router->post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

$router->get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
$router->get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

$router->get('/complete-profile', [ProfileController::class, 'showCompleteProfile']);
$router->post('/complete-profile', [ProfileController::class, 'completeProfile']);

// ─── Admin ───────────────────────────────────────────────────────────────────
$router->get('/admin',                           [AdminDashboardController::class, 'index']);
$router->get('/admin/dashboard/stats',           [AdminDashboardController::class, 'stats']);

$router->get('/admin/products',                  [AdminProductController::class, 'index']);
$router->get('/admin/products/create',           [AdminProductController::class, 'create']);
$router->post('/admin/products/create',          [AdminProductController::class, 'store']);
$router->get('/admin/products/edit',             [AdminProductController::class, 'edit']);
$router->post('/admin/products/edit',            [AdminProductController::class, 'update']);
$router->post('/admin/products/delete',          [AdminProductController::class, 'destroy']);

$router->get('/admin/orders',                    [AdminOrderController::class, 'index']);
$router->get('/admin/orders/show',               [AdminOrderController::class, 'show']);
$router->post('/admin/orders/update-status',     [AdminOrderController::class, 'updateStatus']);

$router->get('/admin/users',                     [AdminUserController::class, 'index']);
$router->post('/admin/users/set-role',           [AdminUserController::class, 'setRole']);

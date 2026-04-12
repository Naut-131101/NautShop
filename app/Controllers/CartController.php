<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Middleware\JwtAuthMiddleware;
use App\Middleware\ProfileCompletedMiddleware;
use App\Services\CartService;
use App\Services\OrderService;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class CartController extends Controller
{
    protected CartService $cart;
    protected OrderService $orders;

    public function __construct()
    {
        $this->cart = new CartService();
        $this->orders = new OrderService();
    }

    protected function guard(): void
    {
        JwtAuthMiddleware::handle();
        ProfileCompletedMiddleware::handle();
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard();

        $this->view('cart/index', [
            'title' => t('title.cart'),
            'cartItems' => $this->cart->items(),
            'totals' => $this->cart->totals(),
        ]);
    }

    public function add(Request $request, Response $response): void
    {
        $this->guard();

        $productId = (int) $request->input('product_id', 0);
        $quantity = max(1, (int) $request->input('quantity', 1));
        $redirectTo = (string) $request->input('redirect_to', '/cart');
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

        if (!$this->cart->add($productId, $quantity)) {
            if ($isAjax) {
                $response->json([
                    'success' => false,
                    'message' => t('flash.cart.product_not_found_add'),
                    'cartCount' => $this->cart->count(),
                ], 404);
            }

            Session::flash('success', t('flash.cart.product_not_found_add'));
            $this->redirect('/products');
        }

        if ($isAjax) {
            $response->json([
                'success' => true,
                'message' => t('flash.cart.add_success'),
                'cartCount' => $this->cart->count(),
            ]);
        }

        Session::flash('success', t('flash.cart.add_success'));
        $this->redirect($redirectTo);
    }

    public function buyNow(Request $request, Response $response): void
    {
        $this->guard();

        $productId = (int) $request->input('product_id', 0);
        $quantity = max(1, (int) $request->input('quantity', 1));

        $this->cart->clear();

        if (!$this->cart->add($productId, $quantity)) {
            Session::flash('success', t('flash.cart.product_not_found_buy_now'));
            $this->redirect('/products');
        }

        $this->redirect('/checkout');
    }

    public function update(Request $request, Response $response): void
    {
        $this->guard();

        $productId = (int) $request->input('product_id', 0);
        $quantity = (int) $request->input('quantity', 1);

        $this->cart->update($productId, $quantity);
        Session::flash('success', t('flash.cart.update_success'));
        $this->redirect('/cart');
    }

    public function remove(Request $request, Response $response): void
    {
        $this->guard();

        $productId = (int) $request->input('product_id', 0);
        $this->cart->remove($productId);

        Session::flash('success', t('flash.cart.remove_success'));
        $this->redirect('/cart');
    }

    public function checkout(Request $request, Response $response): void
    {
        $this->guard();

        $pendingOrder = $this->orders->getPendingOrderForSession();

        if ($pendingOrder) {
            $this->redirect('/checkout/payment');
        }

        $items = $this->cart->items();

        if (empty($items)) {
            Session::flash('success', t('flash.cart.empty_before_checkout'));
            $this->redirect('/products');
        }

        $user = auth() ?? [];

        $totals = $this->cart->totals();

        $this->view('cart/checkout', [
            'title' => t('title.checkout'),
            'cartItems' => $items,
            'totals' => $totals,
            'availableVouchers' => $this->cart->availableVouchers(),
            'checkoutData' => [
                'name' => $user['name'] ?? '',
                'phone' => $user['phone'] ?? '',
                'email' => $user['email'] ?? '',
                'address' => '',
                'payment_method' => Order::METHOD_VISA,
                'note' => '',
                'voucher_code' => (string) ($totals['voucher']['code'] ?? ''),
            ],
            'errors' => [],
            'paymentMethods' => $this->orders->paymentMethods(),
        ]);
    }

    public function placeOrder(Request $request, Response $response): void
    {
        $this->guard();

        $items = $this->cart->items();

        if (empty($items)) {
            Session::flash('success', t('flash.cart.empty_cannot_order'));
            $this->redirect('/products');
        }

        $data = [
            'name' => trim((string) $request->input('name', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'email' => trim((string) $request->input('email', '')),
            'address' => trim((string) $request->input('address', '')),
            'payment_method' => trim((string) $request->input('payment_method', Order::METHOD_VISA)),
            'note' => trim((string) $request->input('note', '')),
            'voucher_code' => strtoupper(trim((string) $request->input('voucher_code', ''))),
        ];

        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = t('validation.name_required');
        }

        if (!preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
            $errors['phone'] = t('validation.phone_digits');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = t('validation.email_invalid');
        }

        if ($data['address'] === '') {
            $errors['address'] = t('validation.address_required');
        }

        if (!in_array($data['payment_method'], [Order::METHOD_VISA, Order::METHOD_VIETQR], true)) {
            $errors['payment_method'] = t('validation.payment_method_invalid');
        }

        if (!empty($errors)) {
            $this->view('cart/checkout', [
                'title' => t('title.checkout'),
                'cartItems' => $items,
                'totals' => $this->cart->totals(),
                'availableVouchers' => $this->cart->availableVouchers(),
                'checkoutData' => $data,
                'errors' => $errors,
                'paymentMethods' => $this->orders->paymentMethods(),
            ]);
            return;
        }

        $order = $this->orders->createPendingOrder($data, $this->cart->baseItems(), $this->cart->totals());

        // Fake payment: auto-confirm immediately, skip the payment page
        $paidOrder = $this->orders->markPendingOrderPaid($order);

        if (!$paidOrder) {
            Session::flash('success', t('flash.cart.payment_failed'));
            $this->redirect('/cart');
        }

        $this->cart->clear();
        $invoiceSent = $this->orders->sendInvoiceEmail($paidOrder);

        Session::flash(
            'success',
            $invoiceSent
                ? t('flash.cart.payment_success_email')
                : t('flash.cart.payment_success_no_email')
        );

        $this->redirect('/checkout/success');
    }

    public function payment(Request $request, Response $response): void
    {
        $this->guard();

        $order = $this->orders->getPendingOrderForSession();

        if (!$order) {
            Session::flash('success', t('flash.cart.no_pending_order'));
            $this->redirect('/cart');
        }

        $this->view('cart/payment', [
            'title' => t('title.payment'),
            'order' => $order,
            'paymentView' => $this->orders->buildPaymentViewData($order),
        ]);
    }

    public function confirmPayment(Request $request, Response $response): void
    {
        $this->guard();

        $order = $this->orders->getPendingOrderForSession();

        if (!$order) {
            Session::flash('success', t('flash.cart.pending_not_found'));
            $this->redirect('/cart');
        }

        $paidOrder = $this->orders->markPendingOrderPaid($order);

        if (!$paidOrder) {
            Session::flash('success', t('flash.cart.payment_failed'));
            $this->redirect('/cart');
        }

        $this->cart->clear();
        $invoiceSent = $this->orders->sendInvoiceEmail($paidOrder);

        Session::flash(
            'success',
            $invoiceSent
                ? t('flash.cart.payment_success_email')
                : t('flash.cart.payment_success_no_email')
        );

        $this->redirect('/checkout/success');
    }

    public function applyVoucher(Request $request, Response $response): void
    {
        $this->guard();

        $code = strtoupper(trim((string) $request->input('voucher_code', '')));

        if ($this->cart->applyVoucher($code)) {
            Session::flash('success', t('flash.cart.voucher_applied', ['code' => $code]));
            $this->redirect('/checkout');
        }

        Session::flash('success', t('flash.cart.voucher_invalid'));
        $this->redirect('/checkout');
    }

    public function removeVoucher(Request $request, Response $response): void
    {
        $this->guard();
        $this->cart->removeVoucher();
        Session::flash('success', t('flash.cart.voucher_removed'));
        $this->redirect('/checkout');
    }

    public function orders(Request $request, Response $response): void
    {
        $this->guard();

        $status = trim((string) $request->input('status', ''));
        $allowedStatuses = array_keys($this->orders->orderStatusOptions());

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $this->view('cart/orders', [
            'title' => t('title.order_history'),
            'orders' => $this->orders->listOrdersForCurrentUser($status !== '' ? $status : null),
            'statusOptions' => $this->orders->orderStatusOptions(),
            'statusCounts' => $this->orders->countOrdersByStatusForCurrentUser(),
            'currentStatus' => $status,
            'orderService' => $this->orders,
        ]);
    }

    public function cancelPayment(Request $request, Response $response): void
    {
        $this->guard();

        $this->orders->cancelPendingOrder(null, 'user_cancelled_payment');
        Session::flash('success', t('flash.cart.payment_cancelled'));
        $this->redirect('/cart');
    }

    public function abandonPayment(Request $request, Response $response): void
    {
        $this->guard();

        $this->orders->cancelPendingOrder(null, 'customer_left_payment_page');
        http_response_code(204);
        exit;
    }

    public function success(Request $request, Response $response): void
    {
        $this->guard();

        $order = $this->orders->getLastSuccessfulOrder();

        if (!$order) {
            $this->redirect('/products');
        }

        $this->view('cart/success', [
            'title' => t('title.order_success'),
            'order' => $order,
            'paymentStatusLabel' => $this->orders->paymentStatusLabel((string) ($order['payment_status'] ?? '')),
        ]);
    }
}

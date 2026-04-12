<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Middleware\AdminMiddleware;
use App\Models\Order;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class AdminOrderController extends Controller
{
    protected Order $orders;

    public function __construct()
    {
        $this->orders = new Order();
    }

    protected function guard(): void
    {
        AdminMiddleware::handle();
    }

    public static function statusLabels(): array
    {
        return [
            Order::STATUS_PENDING_PAYMENT => t('orders.status_pending'),
            Order::STATUS_PAID => t('orders.status_paid'),
            Order::STATUS_PROCESSING => t('admin.status_processing'),
            Order::STATUS_SHIPPED => t('admin.status_shipped'),
            Order::STATUS_DELIVERED => t('admin.status_delivered'),
            Order::STATUS_CANCELLED => t('orders.status_cancelled'),
        ];
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard();

        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'status' => trim((string) $request->input('status', '')),
            'keyword' => trim((string) $request->input('keyword', '')),
        ];

        if ($filters['status'] !== '' && !in_array($filters['status'], Order::ADMIN_STATUSES, true)) {
            $filters['status'] = '';
        }

        $result = $this->orders->adminPaginate($filters, $page, 25);

        $this->view('admin/orders/index', [
            'title' => t('admin.orders_page_title'),
            'orders' => $result['data'],
            'statusLabels' => self::statusLabels(),
            'statusCounts' => $this->orders->adminCountByStatus(),
            'filters' => $filters,
            'pagination' => [
                'page' => $result['page'],
                'perPage' => $result['perPage'],
                'total' => $result['total'],
                'lastPage' => $result['lastPage'],
            ],
        ], 'admin');
    }

    public function show(Request $request, Response $response): void
    {
        $this->guard();

        $order = $this->findOr404((int) $request->input('id', 0));

        $this->view('admin/orders/show', [
            'title' => t('admin.order_detail_page_title', ['code' => (string) $order['order_code']]),
            'order' => $order,
            'statusLabels' => self::statusLabels(),
        ], 'admin');
    }

    public function updateStatus(Request $request, Response $response): void
    {
        $this->guard();

        $id = (int) $request->input('id', 0);
        $newStatus = trim((string) $request->input('status', ''));

        $this->findOr404($id);

        if (!in_array($newStatus, Order::ADMIN_STATUSES, true)) {
            Session::flash('success', t('admin.invalid_status'));
            $this->redirect('/admin/orders/show?id=' . $id);
        }

        $this->orders->adminUpdateStatus($id, $newStatus);
        Session::flash('success', t('admin.order_status_updated_success'));
        $this->redirect('/admin/orders/show?id=' . $id);
    }

    protected function findOr404(int $id): array
    {
        $order = $this->orders->adminFindById($id);

        if (!$order) {
            Session::flash('success', t('admin.order_not_found'));
            $this->redirect('/admin/orders');
        }

        return $order;
    }
}

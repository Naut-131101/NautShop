<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Middleware\AdminMiddleware;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Response;

class AdminDashboardController extends Controller
{
    protected Product $products;
    protected Order $orders;
    protected User $users;

    public function __construct()
    {
        $this->products = new Product();
        $this->orders = new Order();
        $this->users = new User();
    }

    protected function guard(): void
    {
        AdminMiddleware::handle();
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard();

        $statusCounts = $this->orders->adminCountByStatus();
        $barCounts    = $this->orders->adminCountByStatusPeriod('month');
        $totalRevenue = $this->orders->adminTotalRevenue();
        $recentOrders = $this->orders->adminPaginate([], 1, 10)['data'];

        $this->view('admin/dashboard', [
            'title' => t('admin.dashboard_page_title'),
            'totalProducts' => $this->products->countAll(),
            'totalOrders' => $this->orders->adminCountAll(),
            'totalUsers' => $this->users->adminCountAll(),
            'totalRevenue' => $totalRevenue,
            'statusCounts' => $statusCounts,
            'barCounts'    => $barCounts,
            'recentOrders' => $recentOrders,
        ], 'admin');
    }

    public function stats(Request $request, Response $response): void
    {
        $this->guard();

        $period = $request->input('period', 'month');
        if (!in_array($period, ['day', 'month', 'year'], true)) {
            $period = 'month';
        }

        $counts = $this->orders->adminCountByStatusPeriod($period);

        header('Content-Type: application/json');
        echo json_encode($counts, JSON_THROW_ON_ERROR);
        exit;
    }
}

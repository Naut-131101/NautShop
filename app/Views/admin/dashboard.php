<?php
use App\Controllers\Admin\AdminOrderController;
use App\Models\Order;

$statusLabels = AdminOrderController::statusLabels();
$avColors = ['av-purple', 'av-blue', 'av-green', 'av-amber', 'av-rose', 'av-cyan'];

function adminStatusBadgeClass(string $status): string
{
    return match ($status) {
        Order::STATUS_PENDING_PAYMENT => 'admin-badge-pending',
        Order::STATUS_PAID => 'admin-badge-paid',
        Order::STATUS_PROCESSING => 'admin-badge-processing',
        Order::STATUS_SHIPPED => 'admin-badge-shipped',
        Order::STATUS_DELIVERED => 'admin-badge-delivered',
        Order::STATUS_CANCELLED => 'admin-badge-cancelled',
        default => 'admin-badge-user',
    };
}

$donutColors = ['#a594f9', '#34d399', '#60a5fa', '#fbbf24', '#fb7185', '#22d3ee'];
$maxRevenue = 0;
foreach ($recentOrders as $o) {
    if ((float) $o['total_amount'] > $maxRevenue) {
        $maxRevenue = (float) $o['total_amount'];
    }
}
if ($maxRevenue === 0) {
    $maxRevenue = 1;
}
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e(t('admin.dashboard_title')) ?></h1>
        <p class="admin-page-sub"><?= e(t('admin.dashboard_subtitle')) ?></p>
    </div>
</div>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-card-top">
            <span class="admin-stat-card-label"><?= e(t('admin.total_products')) ?></span>
            <div class="admin-stat-icon is-blue">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="2" y="3" width="20" height="5" rx="1"/>
                    <rect x="2" y="10" width="20" height="5" rx="1"/>
                    <rect x="2" y="17" width="20" height="5" rx="1"/>
                </svg>
            </div>
        </div>
        <div class="admin-stat-value"><?= e((string) $totalProducts) ?></div>
        <div class="admin-stat-trend up">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg>
            <?= e(t('admin.on_sale')) ?>
            <span><?= e(t('admin.in_stock_suffix')) ?></span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top">
            <span class="admin-stat-card-label"><?= e(t('admin.total_orders')) ?></span>
            <div class="admin-stat-icon is-amber">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    <path d="M9 12h6M9 16h4"/>
                </svg>
            </div>
        </div>
        <div class="admin-stat-value"><?= e((string) $totalOrders) ?></div>
        <div class="admin-stat-trend up">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg>
            <?= e(t('admin.processing_today', ['count' => (string) ($statusCounts[Order::STATUS_PROCESSING] ?? 0)])) ?>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top">
            <span class="admin-stat-card-label"><?= e(t('admin.users_title')) ?></span>
            <div class="admin-stat-icon is-purple">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
        </div>
        <div class="admin-stat-value"><?= e((string) $totalUsers) ?></div>
        <div class="admin-stat-trend up">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg>
            <?= e(t('admin.members')) ?>
            <span><?= e(t('admin.registered_suffix')) ?></span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top">
            <span class="admin-stat-card-label"><?= e(t('admin.revenue')) ?></span>
            <div class="admin-stat-icon is-green">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
        </div>
        <div class="admin-stat-value" style="font-size:22px;"><?= e(format_price($totalRevenue)) ?></div>
        <div class="admin-stat-trend up">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg>
            <?= e(t('admin.delivered_orders')) ?>
            <span><?= e(t('admin.accumulated_suffix')) ?></span>
        </div>
    </div>
</div>

<div class="admin-charts-row">
    <div class="admin-chart-card">
        <div class="admin-chart-header">
            <div>
                <p class="admin-chart-title"><?= e(t('admin.order_distribution')) ?></p>
                <p class="admin-chart-subtitle"><?= e(t('admin.quantity_by_status')) ?></p>
            </div>
            <div style="display:flex;gap:6px;">
                <button class="admin-chart-badge admin-period-btn" data-period="day"><?= e(t('admin.today')) ?></button>
                <button class="admin-chart-badge admin-period-btn is-active" data-period="month"><?= e(t('admin.this_month')) ?></button>
                <button class="admin-chart-badge admin-period-btn" data-period="year"><?= e(t('admin.this_year')) ?></button>
            </div>
        </div>
        <div class="admin-chart-canvas-wrap">
            <canvas id="ordersBarChart"></canvas>
        </div>
    </div>

    <div class="admin-chart-card">
        <div class="admin-chart-header">
            <div>
                <p class="admin-chart-title"><?= e(t('admin.order_status_chart')) ?></p>
                <p class="admin-chart-subtitle"><?= e(t('admin.analysis_by_type')) ?></p>
            </div>
        </div>

        <div class="admin-donut-center">
            <div class="admin-donut-value"><?= e((string) $totalOrders) ?></div>
            <div class="admin-donut-label"><?= e(t('admin.total_orders')) ?></div>
        </div>

        <div class="admin-donut-canvas-wrap">
            <canvas id="statusDonutChart"></canvas>
        </div>
    </div>
</div>

<div class="admin-table-wrap">
    <div class="admin-table-header">
        <span class="admin-table-title"><?= e(t('admin.recent_orders')) ?></span>
        <a href="<?= e(url('/admin/orders')) ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
            <?= e(t('admin.view_all')) ?> →
        </a>
    </div>

    <?php if (empty($recentOrders)): ?>
        <div class="admin-empty">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
            </svg>
            <p><?= e(t('admin.no_orders_yet')) ?></p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?= e(t('admin.customer')) ?></th>
                    <th><?= e(t('admin.order_code')) ?></th>
                    <th><?= e(t('admin.status')) ?></th>
                    <th><?= e(t('admin.order_date')) ?></th>
                    <th><?= e(t('admin.total_amount')) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $i => $order): ?>
                    <?php
                    $avClass = $avColors[$i % count($avColors)];
                    $name = (string) $order['customer_name'];
                    $initial = strtoupper(mb_substr($name, 0, 1));
                    $pct = $maxRevenue > 0 ? round(((float) $order['total_amount'] / $maxRevenue) * 100) : 0;
                    ?>
                    <tr>
                        <td>
                            <div class="adm-customer">
                                <div class="adm-avatar <?= e($avClass) ?>"><?= e($initial) ?></div>
                                <div>
                                    <div class="adm-customer-name"><?= e($name) ?></div>
                                    <div class="adm-customer-id">#<?= e((string) $order['order_code']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="color:var(--adm-text-2);font-size:13px;">
                            <?= e((string) $order['order_code']) ?>
                        </td>
                        <td>
                            <span class="admin-badge <?= e(adminStatusBadgeClass((string) $order['order_status'])) ?>">
                                <?= e((string) ($statusLabels[$order['order_status']] ?? $order['order_status'])) ?>
                            </span>
                        </td>
                        <td style="color:var(--adm-text-2);font-size:12.5px;white-space:nowrap;">
                            <?= e(date('d/m/Y H:i', strtotime((string) $order['placed_at']))) ?>
                        </td>
                        <td>
                            <div class="adm-progress-wrap">
                                <div class="adm-progress-bar">
                                    <div class="adm-progress-fill" style="width:<?= $pct ?>%;"></div>
                                </div>
                                <span class="adm-progress-pct" style="font-size:12px;white-space:nowrap;">
                                    <?= e(format_price((float) $order['total_amount'])) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <a href="<?= e(url('/admin/orders/show?id=' . $order['id'])) ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                                <?= e(t('admin.view')) ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    Chart.defaults.color = '#9098b1';
    Chart.defaults.font.family = 'Plus Jakarta Sans, sans-serif';
    Chart.defaults.font.size = 12;

    const barCtx = document.getElementById('ordersBarChart');
    if (barCtx) {
        const statusMeta = <?= json_encode(
            array_map(fn($s, $l) => [
                'key'   => $s,
                'label' => $l,
                'count' => (int) ($barCounts[$s] ?? 0),
                'color' => match ($s) {
                    'pending_payment' => '#fbbf24',
                    'paid'            => '#34d399',
                    'processing'      => '#60a5fa',
                    'shipped'         => '#22d3ee',
                    'delivered'       => '#a594f9',
                    'cancelled'       => '#fb7185',
                    default           => '#9098b1',
                },
            ], array_keys($statusLabels), array_values($statusLabels)),
            JSON_THROW_ON_ERROR
        ) ?>;

        const ordersLabel = <?= json_encode(t('admin.orders_label'), JSON_THROW_ON_ERROR) ?>;
        const unitOrders  = <?= json_encode(t('admin.unit_orders'), JSON_THROW_ON_ERROR) ?>;
        const statsUrl    = <?= json_encode(url('/admin/dashboard/stats'), JSON_THROW_ON_ERROR) ?>;

        const barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: statusMeta.map(d => d.label),
                datasets: [{
                    label: ordersLabel,
                    data: statusMeta.map(d => d.count),
                    backgroundColor: statusMeta.map(d => d.color + 'cc'),
                    borderColor: statusMeta.map(d => d.color),
                    borderWidth: 1.5,
                    borderRadius: 8,
                    borderSkipped: false,
                    barPercentage: 0.55,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e2235',
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: ctx => `  ${ctx.parsed.y} ${unitOrders}`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#9098b1' }, border: { display: false } },
                    y: { grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false }, ticks: { color: '#9098b1', stepSize: 1 }, border: { display: false }, beginAtZero: true },
                },
            },
        });

        // Period switcher
        document.querySelectorAll('.admin-period-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.admin-period-btn').forEach(b => b.classList.remove('is-active'));
                this.classList.add('is-active');

                fetch(`${statsUrl}?period=${this.dataset.period}`)
                    .then(r => r.json())
                    .then(counts => {
                        barChart.data.datasets[0].data = statusMeta.map(d => counts[d.key] ?? 0);
                        barChart.update();
                    });
            });
        });
    }

    const donutCtx = document.getElementById('statusDonutChart');
    if (donutCtx) {
        const donutData = <?= json_encode(
            array_values(array_filter(
                array_map(fn($s, $l) => [
                    'label' => $l,
                    'count' => (int) ($statusCounts[$s] ?? 0),
                ], array_keys($statusLabels), array_values($statusLabels)),
                fn($d) => $d['count'] > 0
            )),
            JSON_THROW_ON_ERROR
        ) ?>;

        const donutColors = ['#a594f9', '#34d399', '#60a5fa', '#fbbf24', '#fb7185', '#22d3ee'];
        if (donutData.length === 0) {
            donutData.push({ label: <?= json_encode(t('admin.empty_chart'), JSON_THROW_ON_ERROR) ?>, count: 1 });
        }

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: donutData.map(d => d.label),
                datasets: [{
                    data: donutData.map(d => d.count),
                    backgroundColor: donutData.map((_, i) => donutColors[i % donutColors.length]),
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e2235',
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10,
                    },
                },
            },
        });
    }

    requestAnimationFrame(() => {
        document.querySelectorAll('.adm-progress-fill').forEach(el => {
            const w = el.style.width;
            el.style.width = '0%';
            requestAnimationFrame(() => { el.style.width = w; });
        });
    });
})();
</script>

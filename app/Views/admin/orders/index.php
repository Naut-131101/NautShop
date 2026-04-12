<?php
use App\Models\Order;

function adminOrderBadgeClass(string $status): string
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
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e(t('admin.orders_title')) ?></h1>
        <p class="admin-page-sub"><?= e(t('admin.orders_count', ['count' => (string) $pagination['total']])) ?></p>
    </div>
</div>

<div class="admin-stats-grid" style="margin-bottom:20px;">
    <?php
    $allCount = $statusCounts['all'] ?? 0;
    $countItems = [
        ['label' => t('admin.all'), 'value' => $allCount, 'status' => ''],
    ];
    foreach ($statusLabels as $st => $lbl) {
        $countItems[] = ['label' => $lbl, 'value' => $statusCounts[$st] ?? 0, 'status' => $st];
    }
    ?>
    <?php foreach ($countItems as $ci): ?>
        <?php $active = $filters['status'] === $ci['status']; ?>
        <a href="<?= e(url('/admin/orders?' . http_build_query(array_filter(['status' => $ci['status'], 'keyword' => $filters['keyword']])))) ?>"
           class="admin-stat-card"
           style="flex-direction:row;align-items:center;gap:12px;padding:12px 16px;text-decoration:none;<?= $active ? 'border-color:var(--accent);' : '' ?>">
            <?php if ($ci['status'] !== ''): ?>
                <span class="admin-badge <?= e(adminOrderBadgeClass($ci['status'])) ?>" style="font-size:12px;padding:3px 10px;">
                    <?= e($ci['label']) ?>
                </span>
            <?php else: ?>
                <span style="font-size:13px;font-weight:600;color:var(--text-soft);"><?= e(t('admin.all')) ?></span>
            <?php endif; ?>
            <span style="font-size:20px;font-weight:800;color:var(--text-main);margin-left:auto;">
                <?= e((string) $ci['value']) ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>

<form method="GET" action="<?= e(url('/admin/orders')) ?>" class="admin-filter-bar">
    <div class="admin-filter-grid">
        <div class="admin-filter-field">
            <label for="keyword"><?= e(t('admin.search_orders')) ?></label>
            <div class="admin-filter-search" data-admin-search data-history-key="admin_orders_searches">
                <input id="keyword" name="keyword" type="text" class="admin-input" placeholder="<?= e(t('admin.search_orders_placeholder')) ?>" value="<?= e($filters['keyword']) ?>" autocomplete="off">
                <button type="button" class="admin-filter-clear" aria-label="<?= e(t('admin.clear_keyword')) ?>" <?= $filters['keyword'] === '' ? 'hidden' : '' ?>>×</button>
                <div class="admin-filter-search-panel" hidden>
                    <div class="admin-filter-search-head">
                        <strong><?= e(t('admin.recent_searches')) ?></strong>
                        <button type="button" class="admin-filter-search-trash" aria-label="<?= e(t('admin.clear_search_history')) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm-2 6h2v8H7V9Zm4 0h2v8h-2V9Zm4 0h2v8h-2V9ZM6 7h12l-1 13H7L6 7Z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="admin-filter-search-list"></div>
                </div>
            </div>
        </div>

        <div class="admin-filter-field">
            <label for="status"><?= e(t('admin.status')) ?></label>
            <div class="admin-filter-select" data-admin-select>
                <select id="status" name="status" class="admin-select admin-filter-native" tabindex="-1" aria-hidden="true">
                    <option value=""><?= e(t('admin.all_statuses')) ?></option>
                    <?php foreach ($statusLabels as $st => $lbl): ?>
                        <option value="<?= e($st) ?>" <?= ($filters['status'] === $st) ? 'selected' : '' ?>>
                            <?= e($lbl) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="button" class="admin-filter-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span class="admin-filter-select-label"><?= e($filters['status'] !== '' ? ($statusLabels[$filters['status']] ?? $filters['status']) : t('admin.all_statuses')) ?></span>
                    <span class="admin-filter-select-chevron" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M7 14l5-5 5 5"/></svg>
                    </span>
                </button>

                <div class="admin-filter-select-panel" role="listbox" hidden>
                    <button type="button" class="admin-filter-select-option <?= $filters['status'] === '' ? 'is-selected' : '' ?>" data-value="" role="option" aria-selected="<?= $filters['status'] === '' ? 'true' : 'false' ?>"><?= e(t('admin.all_statuses')) ?></button>
                    <?php foreach ($statusLabels as $st => $lbl): ?>
                        <button type="button"
                                class="admin-filter-select-option <?= $filters['status'] === $st ? 'is-selected' : '' ?>"
                                data-value="<?= e($st) ?>"
                                role="option"
                                aria-selected="<?= $filters['status'] === $st ? 'true' : 'false' ?>">
                            <?= e($lbl) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="admin-filter-actions">
            <button type="submit" class="admin-btn admin-btn-primary"><?= e(t('admin.apply')) ?></button>
            <a href="<?= e(url('/admin/orders')) ?>" class="admin-btn admin-btn-secondary"><?= e(t('admin.reset')) ?></a>
        </div>
    </div>
</form>

<div class="admin-table-wrap">
    <?php if (empty($orders)): ?>
        <div class="admin-empty">
            <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
            <p><?= e(t('admin.no_orders_found')) ?></p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?= e(t('admin.order_code')) ?></th>
                    <th><?= e(t('admin.customer')) ?></th>
                    <th><?= e(t('admin.total_amount')) ?></th>
                    <th><?= e(t('admin.payment')) ?></th>
                    <th><?= e(t('admin.status')) ?></th>
                    <th><?= e(t('admin.order_date')) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?= e((string) $order['order_code']) ?></strong></td>
                        <td>
                            <?= e((string) $order['customer_name']) ?>
                            <div style="font-size:12px;color:var(--text-faint);"><?= e((string) $order['customer_email']) ?></div>
                        </td>
                        <td style="font-weight:700;color:var(--gold);">
                            <?= e(format_price((float) $order['total_amount'])) ?>
                        </td>
                        <td>
                            <?php $pm = strtoupper((string) $order['payment_method']); ?>
                            <span class="admin-badge admin-badge-processing" style="font-size:11px;">
                                <?= e($pm) ?>
                            </span>
                        </td>
                        <td>
                            <span class="admin-badge <?= e(adminOrderBadgeClass((string) $order['order_status'])) ?>">
                                <?= e((string) ($statusLabels[$order['order_status']] ?? $order['order_status'])) ?>
                            </span>
                        </td>
                        <td style="font-size:13px;color:var(--text-soft);">
                            <?= e(date('d/m/Y H:i', strtotime((string) $order['placed_at']))) ?>
                        </td>
                        <td>
                            <div class="admin-table-actions">
                                <a href="<?= e(url('/admin/orders/show?id=' . $order['id'])) ?>" class="admin-btn admin-btn-secondary admin-btn-sm"><?= e(t('admin.details')) ?></a>
                                <form method="POST" action="<?= e(url('/admin/orders/update-status')) ?>" class="admin-inline-status-form">
                                    <input type="hidden" name="id" value="<?= e((string) $order['id']) ?>">
                                    <select name="status" class="admin-select admin-select-sm" onchange="this.form.submit()">
                                        <?php foreach ($statusLabels as $st => $lbl): ?>
                                            <option value="<?= e($st) ?>" <?= ($order['order_status'] === $st) ? 'selected' : '' ?>>
                                                <?= e($lbl) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($pagination['lastPage'] > 1): ?>
    <div class="admin-pagination">
        <?php
        $base = url('/admin/orders') . '?' . http_build_query(array_filter([
            'status' => $filters['status'],
            'keyword' => $filters['keyword'],
        ]));
        $pageBase = str_contains($base, '=') ? $base . '&' : $base;
        ?>
        <?php if ($pagination['page'] > 1): ?>
            <a href="<?= e($pageBase . 'page=' . ($pagination['page'] - 1)) ?>" class="admin-page-link">‹</a>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $pagination['lastPage']; $p++): ?>
            <a href="<?= e($pageBase . 'page=' . $p) ?>" class="admin-page-link <?= $p === $pagination['page'] ? 'is-active' : '' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>

        <?php if ($pagination['page'] < $pagination['lastPage']): ?>
            <a href="<?= e($pageBase . 'page=' . ($pagination['page'] + 1)) ?>" class="admin-page-link">›</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

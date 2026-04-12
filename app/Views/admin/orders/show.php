<?php
use App\Models\Order;

function resolveOrderItemImage(string $imageName): string
{
    if ($imageName === '') {
        return asset('images/image-placeholder.png');
    }

    $base = BASE_PATH . '/public/assets/images/products/';
    $filename = ltrim($imageName, '/');

    // Thử đúng tên file trước
    if (is_file($base . $filename)) {
        return asset('images/products/' . $filename);
    }

    // Thử đổi extension (vd: .jpg ↔ .png ↔ .webp)
    $name = pathinfo($filename, PATHINFO_FILENAME);
    foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
        if (is_file($base . $name . '.' . $ext)) {
            return asset('images/products/' . $name . '.' . $ext);
        }
    }

    return asset('images/image-placeholder.png');
}

function adminOrderBadgeCls(string $status): string
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

$paymentLabel = match ((string) ($order['payment_method'] ?? '')) {
    'visa' => t('admin.payment_visa'),
    'vietqr' => t('admin.payment_vietqr'),
    default => strtoupper((string) ($order['payment_method'] ?? '')),
};
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e(t('admin.order_number', ['code' => (string) $order['order_code']])) ?></h1>
        <p class="admin-page-sub"><?= e(t('admin.placed_at', ['datetime' => date('d/m/Y H:i', strtotime((string) $order['placed_at']))])) ?></p>
    </div>
    <a href="<?= e(url('/admin/orders')) ?>" class="admin-btn admin-btn-secondary">← <?= e(t('admin.back')) ?></a>
</div>

<div class="admin-detail-grid">
    <div class="admin-detail-card">
        <p class="admin-detail-title"><?= e(t('admin.customer_information')) ?></p>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.name')) ?></span>
            <span class="admin-detail-val"><?= e((string) $order['customer_name']) ?></span>
        </div>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.phone')) ?></span>
            <span class="admin-detail-val"><?= e((string) $order['customer_phone']) ?></span>
        </div>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.email')) ?></span>
            <span class="admin-detail-val"><?= e((string) $order['customer_email']) ?></span>
        </div>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.address')) ?></span>
            <span class="admin-detail-val"><?= e((string) $order['shipping_address']) ?></span>
        </div>
        <?php if (!empty($order['note'])): ?>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.note')) ?></span>
            <span class="admin-detail-val"><?= e((string) $order['note']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="admin-detail-card">
        <p class="admin-detail-title"><?= e(t('admin.order_information')) ?></p>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.order_code')) ?></span>
            <span class="admin-detail-val"><?= e((string) $order['order_code']) ?></span>
        </div>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.payment')) ?></span>
            <span class="admin-detail-val"><?= e($paymentLabel) ?></span>
        </div>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.status')) ?></span>
            <span class="admin-detail-val">
                <span class="admin-badge <?= e(adminOrderBadgeCls((string) $order['order_status'])) ?>">
                    <?= e((string) ($statusLabels[$order['order_status']] ?? $order['order_status'])) ?>
                </span>
            </span>
        </div>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.subtotal')) ?></span>
            <span class="admin-detail-val"><?= e(format_price((float) $order['subtotal_amount'])) ?></span>
        </div>
        <?php if ((float) $order['discount_amount'] > 0): ?>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.discount')) ?></span>
            <span class="admin-detail-val" style="color:var(--error-text);">
                − <?= e(format_price((float) $order['discount_amount'])) ?>
            </span>
        </div>
        <?php endif; ?>
        <div class="admin-detail-row">
            <span class="admin-detail-key"><?= e(t('admin.shipping_fee')) ?></span>
            <span class="admin-detail-val"><?= e(format_price((float) $order['shipping_amount'])) ?></span>
        </div>
        <div class="admin-detail-row">
            <span class="admin-detail-key" style="font-weight:700;"><?= e(t('admin.total')) ?></span>
            <span class="admin-detail-val" style="color:var(--gold);font-size:17px;">
                <?= e(format_price((float) $order['total_amount'])) ?>
            </span>
        </div>
    </div>
</div>

<p class="admin-recent-label"><?= e(t('admin.products_in_order')) ?></p>
<div class="admin-table-wrap" style="margin-bottom:28px;">
    <table class="admin-table">
        <thead>
            <tr>
                <th><?= e(t('admin.product')) ?></th>
                <th><?= e(t('admin.category')) ?></th>
                <th><?= e(t('admin.unit_price')) ?></th>
                <th><?= e(t('admin.quantity')) ?></th>
                <th><?= e(t('admin.line_total')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <img src="<?= e(resolveOrderItemImage((string) ($item['product_image'] ?? ''))) ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px;border:1px solid var(--border-soft);" loading="lazy">
                            <span><?= e((string) $item['product_name']) ?></span>
                        </div>
                    </td>
                    <td><?= e((string) $item['product_category']) ?></td>
                    <td><?= e(format_price((float) $item['unit_price'])) ?></td>
                    <td><?= e((string) $item['quantity']) ?></td>
                    <td style="font-weight:700;"><?= e(format_price((float) $item['line_total'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="admin-detail-card" style="max-width:480px;">
    <p class="admin-detail-title"><?= e(t('admin.update_status')) ?></p>
    <form method="POST" action="<?= e(url('/admin/orders/update-status')) ?>" style="display:flex;gap:10px;align-items:flex-end;">
        <input type="hidden" name="id" value="<?= e((string) $order['id']) ?>">
        <div class="admin-field" style="flex:1;">
            <label class="admin-label" for="newStatus"><?= e(t('admin.new_status')) ?></label>
            <select id="newStatus" name="status" class="admin-select" style="width:100%;">
                <?php foreach ($statusLabels as $st => $lbl): ?>
                    <option value="<?= e($st) ?>" <?= ($order['order_status'] === $st) ? 'selected' : '' ?>>
                        <?= e($lbl) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary"><?= e(t('admin.save')) ?></button>
    </form>

</div>

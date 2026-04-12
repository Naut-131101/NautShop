<section class="checkout-success-page">
    <div class="center-card-wrap">
        <div class="center-card success-card">
            <span class="section-eyebrow"><?= e(t('success.order_placed')) ?></span>
            <h2><?= e(t('success.purchase_completed')) ?></h2>
            <p><?= e(t('success.note')) ?></p>

            <div class="success-order-box">
                <div class="summary-line">
                    <span><?= e(t('success.order_code')) ?></span>
                    <strong><?= e((string) ($order['order_code'] ?? '')) ?></strong>
                </div>
                <div class="summary-line">
                    <span><?= e(t('success.created_at')) ?></span>
                    <strong><?= e((string) ($order['placed_at'] ?? '')) ?></strong>
                </div>
                <div class="summary-line">
                    <span><?= e(t('success.payment_status')) ?></span>
                    <strong><?= e((string) ($paymentStatusLabel ?? '')) ?></strong>
                </div>
                <div class="summary-line">
                    <span><?= e(t('success.transaction_code')) ?></span>
                    <strong><?= e((string) (($order['payment_reference'] ?? '') !== '' ? $order['payment_reference'] : t('success.no_reference'))) ?></strong>
                </div>
                <div class="summary-line summary-line-total">
                    <span><?= e(t('cart.total')) ?></span>
                    <strong><?= format_price((float) ($order['totals']['total'] ?? 0)) ?></strong>
                </div>
            </div>

            <div class="cart-summary-actions success-actions">
                <a href="<?= e(url('/products')) ?>" class="btn btn-primary"><?= e(t('cart.continue_shopping')) ?></a>
                <a href="<?= e(url('/orders')) ?>" class="btn btn-secondary"><?= e(t('success.orders_history')) ?></a>
                <a href="<?= e(url('/cart')) ?>" class="btn btn-secondary"><?= e(t('checkout.back_to_cart')) ?></a>
            </div>
        </div>
    </div>
</section>

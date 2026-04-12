<?php $paymentMethods = $orderService->paymentMethods(); ?>

<section class="checkout-page orders-page">
    <section class="products-catalog-shell orders-shell">
        <div class="section-heading-row">
            <div>
                <span class="section-eyebrow"><?= e(t('orders.heading')) ?></span>
                <h1><?= e(t('orders.title')) ?></h1>
            </div>
            <p><?= e(t('orders.description')) ?></p>
        </div>

        <div class="orders-status-grid">
            <article class="order-status-card">
                <span><?= e(t('orders.status_all')) ?></span>
                <strong><?= e((string) ($statusCounts['all'] ?? 0)) ?></strong>
            </article>
            <article class="order-status-card is-pending">
                <span><?= e(t('orders.status_pending')) ?></span>
                <strong><?= e((string) ($statusCounts[\App\Models\Order::STATUS_PENDING_PAYMENT] ?? 0)) ?></strong>
            </article>
            <article class="order-status-card is-paid">
                <span><?= e(t('orders.status_paid')) ?></span>
                <strong><?= e((string) ($statusCounts[\App\Models\Order::STATUS_PAID] ?? 0)) ?></strong>
            </article>
            <article class="order-status-card is-cancelled">
                <span><?= e(t('orders.status_cancelled')) ?></span>
                <strong><?= e((string) ($statusCounts[\App\Models\Order::STATUS_CANCELLED] ?? 0)) ?></strong>
            </article>
        </div>

        <form action="<?= e(url('/orders')) ?>" method="GET" class="filter-form-modern orders-filter-form">
            <div class="orders-filter-row">
                <div class="form-group">
                    <label for="status"><?= e(t('orders.filter_label')) ?></label>
                    <div class="category-select-shell" id="orderStatusSelectShell">
                        <select id="status" name="status" class="category-select-native" tabindex="-1" aria-hidden="true">
                            <?php foreach (($statusOptions ?? []) as $statusValue => $statusLabel): ?>
                                <option value="<?= e((string) $statusValue) ?>" <?= ($currentStatus === $statusValue) ? 'selected' : '' ?>>
                                    <?= e((string) $statusLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="button" class="category-select-trigger" id="orderStatusSelectTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="category-select-label" id="orderStatusSelectLabel">
                                <?= e((string) (($statusOptions[$currentStatus] ?? null) ?? ($statusOptions[''] ?? t('orders.status_all')))) ?>
                            </span>
                            <span class="category-select-chevron" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="m7 10 5 5 5-5"></path>
                                </svg>
                            </span>
                        </button>

                        <div class="category-select-panel" id="orderStatusSelectPanel" role="listbox" hidden>
                            <?php foreach (($statusOptions ?? []) as $statusValue => $statusLabel): ?>
                                <button
                                    type="button"
                                    class="category-select-option <?= ($currentStatus === $statusValue) ? 'is-selected' : '' ?>"
                                    data-value="<?= e((string) $statusValue) ?>"
                                    role="option"
                                    aria-selected="<?= ($currentStatus === $statusValue) ? 'true' : 'false' ?>"
                                >
                                    <?= e((string) $statusLabel) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="filter-action-group">
                    <button type="submit" class="btn btn-primary"><?= e(t('orders.apply')) ?></button>
                    <a href="<?= e(url('/orders')) ?>" class="btn btn-secondary"><?= e(t('orders.reset')) ?></a>
                </div>
            </div>
        </form>

        <?php if (empty($orders)): ?>
            <section class="empty-products-state">
                <h3><?= e(t('orders.empty_title')) ?></h3>
                <p><?= e(t('orders.empty_description')) ?></p>
                <div class="cart-empty-actions">
                    <a href="<?= e(url('/products')) ?>" class="btn btn-primary"><?= e(t('orders.shop_now')) ?></a>
                </div>
            </section>
        <?php else: ?>
            <div class="orders-history-list">
                <?php foreach ($orders as $order): ?>
                    <?php
                        $status = (string) ($order['order_status'] ?? '');
                        $statusLabel = $orderService->orderStatusLabel($status);
                        $statusClass = $orderService->orderStatusClass($status);
                        $items = $order['items'] ?? [];
                        $paymentKey = (string) ($order['payment_method'] ?? '');
                        $paymentLabel = (string) ($paymentMethods[$paymentKey]['label'] ?? strtoupper($paymentKey));
                    ?>
                    <article class="order-history-card">
                        <div class="order-history-head">
                            <div class="order-history-main">
                                <span class="order-history-code"><?= e((string) ($order['order_code'] ?? '')) ?></span>
                                <strong><?= e((string) ($order['customer_name'] ?? '')) ?></strong>
                                <span class="order-history-date"><?= e((string) ($order['placed_at'] ?? '')) ?></span>
                            </div>

                            <div class="order-history-side">
                                <span class="order-status-pill <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
                                <strong class="order-history-total"><?= e(format_price((float) ($order['total_amount'] ?? 0))) ?></strong>
                            </div>
                        </div>

                        <div class="order-history-meta">
                            <span><?= e(t('orders.payment_method')) ?>: <strong><?= e($paymentLabel) ?></strong></span>
                            <span><?= e(t('orders.transaction_code')) ?>: <strong><?= e((string) (($order['payment_reference'] ?? '') !== '' ? $order['payment_reference'] : t('orders.no_reference'))) ?></strong></span>
                            <span><?= e(t('orders.invoice_status')) ?>: <strong><?= e(!empty($order['invoice_sent_at']) ? t('orders.invoice_sent') : t('orders.invoice_pending')) ?></strong></span>
                        </div>

                        <div class="checkout-mini-list">
                            <?php foreach ($items as $item): ?>
                                <div class="checkout-mini-item">
                                    <div>
                                        <strong><?= e((string) ($item['product_name'] ?? '')) ?></strong>
                                        <span>x<?= e((string) ($item['quantity'] ?? '0')) ?></span>
                                    </div>
                                    <strong><?= e(format_price((float) ($item['line_total'] ?? 0))) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>

<script>
    (function () {
        const statusSelect = document.getElementById('status');
        const statusShell = document.getElementById('orderStatusSelectShell');
        const statusTrigger = document.getElementById('orderStatusSelectTrigger');
        const statusLabel = document.getElementById('orderStatusSelectLabel');
        const statusPanel = document.getElementById('orderStatusSelectPanel');
        let isStatusOpen = false;

        if (!statusSelect || !statusShell || !statusTrigger || !statusLabel || !statusPanel) {
            return;
        }

        const syncStatusVisibility = function () {
            statusShell.classList.toggle('is-open', isStatusOpen);
            statusTrigger.setAttribute('aria-expanded', isStatusOpen ? 'true' : 'false');
            statusPanel.hidden = !isStatusOpen;
        };

        const syncStatusUi = function () {
            const selectedOption = statusSelect.options[statusSelect.selectedIndex];
            statusLabel.textContent = selectedOption ? selectedOption.textContent : '';

            Array.prototype.forEach.call(statusPanel.querySelectorAll('.category-select-option'), function (optionButton) {
                const isSelected = optionButton.dataset.value === statusSelect.value;
                optionButton.classList.toggle('is-selected', isSelected);
                optionButton.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        };

        statusTrigger.addEventListener('click', function () {
            isStatusOpen = !isStatusOpen;
            syncStatusVisibility();
        });

        Array.prototype.forEach.call(statusPanel.querySelectorAll('.category-select-option'), function (optionButton) {
            optionButton.addEventListener('click', function () {
                statusSelect.value = optionButton.dataset.value || '';
                syncStatusUi();
                isStatusOpen = false;
                syncStatusVisibility();
            });
        });

        document.addEventListener('click', function (event) {
            if (statusShell.contains(event.target)) {
                return;
            }

            isStatusOpen = false;
            syncStatusVisibility();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            isStatusOpen = false;
            syncStatusVisibility();
        });

        syncStatusUi();
        syncStatusVisibility();
    })();
</script>

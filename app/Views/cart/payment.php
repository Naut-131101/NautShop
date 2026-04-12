<?php $bankDisplay = (string) (($paymentView['bankName'] ?? '') !== '' ? ($paymentView['bankName'] ?? '') : ($paymentView['bankId'] ?? '')); ?>

<section class="checkout-page payment-page">
    <section class="cart-layout">
        <div
            class="cart-main-panel payment-flow-panel"
            id="paymentFlowPanel"
            data-abandon-url="<?= e(url('/checkout/payment/abandon')) ?>"
        >
            <div class="section-heading-row cart-heading-row">
                <div>
                    <span class="section-eyebrow"><?= e(t('payment.heading')) ?></span>
                    <h1><?= e(t('payment.title')) ?></h1>
                </div>
                <p><?= e(t('payment.leave_notice')) ?></p>
            </div>

            <div class="payment-order-banner">
                <div>
                    <span><?= e(t('payment.order_code')) ?></span>
                    <strong><?= e((string) ($order['order_code'] ?? '')) ?></strong>
                </div>
                <div>
                    <span><?= e(t('payment.status')) ?></span>
                    <strong><?= e(t('payment.awaiting_payment')) ?></strong>
                </div>
                <div>
                    <span><?= e(t('payment.amount_due')) ?></span>
                    <strong><?= e(format_price((float) ($order['total_amount'] ?? 0))) ?></strong>
                </div>
            </div>

            <?php if (($paymentView['selectedMethod'] ?? '') === 'visa'): ?>
                <section class="payment-method-panel">
                    <div class="payment-method-card payment-method-card-visa">
                        <span class="payment-chip"><?= e(t('payment.card_badge')) ?></span>
                        <strong>**** 4242</strong>
                        <span><?= e(t('payment.card_note')) ?></span>
                    </div>

                    <div class="payment-guidance">
                        <h3><?= e(t('payment.card_heading')) ?></h3>
                        <p><?= e(t('payment.card_description')) ?></p>
                    </div>

                    <form action="<?= e(url('/checkout/payment/confirm')) ?>" method="POST" class="payment-action-form">
                        <button type="submit" class="btn btn-primary" data-allow-leave="true" data-loading-message="<?= e(t('payment.loading_default')) ?>"><?= e(t('payment.card_confirm')) ?></button>
                    </form>
                </section>
            <?php else: ?>
                <section class="payment-method-panel">
                    <div class="payment-qr-shell">
                        <img src="<?= e((string) ($paymentView['vietQrImageUrl'] ?? '')) ?>" alt="<?= e(t('payment.alt_qr')) ?>">
                    </div>

                    <div class="payment-guidance">
                        <h3><?= e(t('payment.qr_heading')) ?></h3>
                        <p><?= e(t('payment.qr_description')) ?></p>
                        <div class="payment-bank-meta">
                            <span><?= e(t('payment.qr_source')) ?>: <strong><?= e((string) ($paymentView['providerLabel'] ?? 'VietQR')) ?></strong></span>
                            <span><?= e(t('payment.bank_name')) ?>: <strong><?= e($bankDisplay) ?></strong></span>
                            <span><?= e(t('payment.account_no')) ?>: <strong><?= e((string) ($paymentView['accountNo'] ?? '')) ?></strong></span>
                            <span><?= e(t('payment.account_name')) ?>: <strong><?= e((string) ($paymentView['accountName'] ?? '')) ?></strong></span>
                            <span><?= e(t('payment.transfer_content')) ?>: <strong><?= e((string) ($paymentView['transferContent'] ?? '')) ?></strong></span>
                        </div>
                    </div>

                    <form action="<?= e(url('/checkout/payment/confirm')) ?>" method="POST" class="payment-action-form">
                        <button type="submit" class="btn btn-primary" data-allow-leave="true" data-loading-message="<?= e(t('payment.loading_default')) ?>"><?= e(t('payment.transfer_confirm')) ?></button>
                    </form>
                </section>
            <?php endif; ?>

            <div class="checkout-actions">
                <form action="<?= e(url('/checkout/payment/cancel')) ?>" method="POST">
                    <button type="submit" class="btn btn-secondary" data-allow-leave="true" data-loading-message="<?= e(t('payment.loading_default')) ?>"><?= e(t('payment.cancel_order')) ?></button>
                </form>
            </div>
        </div>

        <aside class="cart-summary-panel">
            <span class="section-eyebrow"><?= e(t('cart.order_summary')) ?></span>
            <h2><?= e(t('payment.summary_heading')) ?></h2>
            <p class="summary-copy"><?= e(t('payment.summary_copy')) ?></p>

            <div class="summary-line">
                <span><?= e(t('payment.customer')) ?></span>
                <strong><?= e((string) ($order['customer_name'] ?? '')) ?></strong>
            </div>
            <div class="summary-line">
                <span>Email</span>
                <strong><?= e((string) ($order['customer_email'] ?? '')) ?></strong>
            </div>
            <div class="summary-line">
                <span><?= e(t('checkout.phone')) ?></span>
                <strong><?= e((string) ($order['customer_phone'] ?? '')) ?></strong>
            </div>
            <div class="summary-line">
                <span><?= e(t('payment.method')) ?></span>
                <strong><?= e((string) (($paymentView['methods'][$paymentView['selectedMethod']]['label'] ?? strtoupper((string) ($paymentView['selectedMethod'] ?? ''))))) ?></strong>
            </div>
            <div class="summary-line summary-line-total">
                <span><?= e(t('cart.total')) ?></span>
                <strong><?= e(format_price((float) ($order['total_amount'] ?? 0))) ?></strong>
            </div>
        </aside>
    </section>
</section>

<div class="payment-loading-overlay" id="paymentLoadingOverlay" hidden>
    <div class="payment-loading-card">
        <span class="payment-loading-spinner" aria-hidden="true"></span>
        <strong id="paymentLoadingText"><?= e(t('payment.loading_default')) ?></strong>
        <p><?= e(t('payment.loading_wait')) ?></p>
    </div>
</div>

<script>
    (function () {
        const panel = document.getElementById('paymentFlowPanel');
        const loadingOverlay = document.getElementById('paymentLoadingOverlay');
        const loadingText = document.getElementById('paymentLoadingText');

        if (!panel) {
            return;
        }

        let allowLeave = false;
        let beaconSent = false;

        const abandonOrder = function () {
            if (allowLeave || beaconSent) {
                return;
            }

            if (!window.navigator || typeof window.navigator.sendBeacon !== 'function') {
                return;
            }

            beaconSent = true;
            const payload = new FormData();
            window.navigator.sendBeacon(panel.dataset.abandonUrl || '', payload);
        };

        Array.prototype.forEach.call(document.querySelectorAll('[data-allow-leave="true"]'), function (node) {
            node.addEventListener('click', function () {
                allowLeave = true;
            });
        });

        Array.prototype.forEach.call(panel.querySelectorAll('form'), function (form) {
            form.addEventListener('submit', function (event) {
                const submitButton = event.submitter || form.querySelector('button[type="submit"]');
                const message = submitButton && submitButton.dataset.loadingMessage
                    ? submitButton.dataset.loadingMessage
                    : <?= json_encode(t('payment.loading_default'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

                if (loadingOverlay && loadingText) {
                    loadingText.textContent = message;
                    loadingOverlay.hidden = false;
                }

                Array.prototype.forEach.call(panel.querySelectorAll('button'), function (button) {
                    button.disabled = true;
                });
            });
        });

        window.addEventListener('pagehide', abandonOrder);
        window.addEventListener('beforeunload', abandonOrder);
    })();
</script>

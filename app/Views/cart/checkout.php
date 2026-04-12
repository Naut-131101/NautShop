<section class="checkout-page">
    <section class="cart-layout">
        <div class="cart-main-panel">
            <div class="section-heading-row cart-heading-row">
                <div>
                    <span class="section-eyebrow"><?= e(t('checkout.heading')) ?></span>
                    <h1><?= e(t('checkout.complete_order')) ?></h1>
                </div>
                <p><?= e(t('checkout.flow_note')) ?></p>
            </div>

            <form action="<?= e(url('/checkout')) ?>" method="POST" class="checkout-form-grid">
                <input type="hidden" name="voucher_code" value="<?= e((string) ($totals['voucher']['code'] ?? '')) ?>">
                <div class="form-group">
                    <label for="name"><?= e(t('checkout.full_name')) ?></label>
                    <input type="text" id="name" name="name" value="<?= e((string) ($checkoutData['name'] ?? '')) ?>">
                    <?php if (!empty($errors['name'])): ?><span class="error-text"><?= e((string) $errors['name']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="phone"><?= e(t('checkout.phone')) ?></label>
                    <input type="text" id="phone" name="phone" value="<?= e((string) ($checkoutData['phone'] ?? '')) ?>">
                    <?php if (!empty($errors['phone'])): ?><span class="error-text"><?= e((string) $errors['phone']) ?></span><?php endif; ?>
                </div>

                <div class="form-group form-group-wide checkout-span-2">
                    <label for="email"><?= e(t('checkout.email')) ?></label>
                    <input type="email" id="email" name="email" value="<?= e((string) ($checkoutData['email'] ?? '')) ?>">
                    <?php if (!empty($errors['email'])): ?><span class="error-text"><?= e((string) $errors['email']) ?></span><?php endif; ?>
                </div>

                <div class="form-group form-group-wide checkout-span-2">
                    <label for="address"><?= e(t('checkout.shipping_address')) ?></label>
                    <textarea id="address" name="address" rows="4"><?= e((string) ($checkoutData['address'] ?? '')) ?></textarea>
                    <?php if (!empty($errors['address'])): ?><span class="error-text"><?= e((string) $errors['address']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="payment_method"><?= e(t('checkout.payment_method')) ?></label>
                    <div class="category-select-shell" id="paymentMethodSelectShell">
                        <select id="payment_method" name="payment_method" class="category-select-native" tabindex="-1" aria-hidden="true">
                            <?php foreach (($paymentMethods ?? []) as $paymentKey => $paymentMethod): ?>
                                <option value="<?= e((string) $paymentKey) ?>" <?= (($checkoutData['payment_method'] ?? '') === $paymentKey) ? 'selected' : '' ?>>
                                    <?= e((string) ($paymentMethod['label'] ?? $paymentKey)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="button" class="category-select-trigger" id="paymentMethodSelectTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="category-select-label" id="paymentMethodSelectLabel">
                                <?=
                                    e((string) (
                                        (($checkoutData['payment_method'] ?? '') !== '' && isset($paymentMethods[$checkoutData['payment_method'] ?? '']))
                                            ? ($paymentMethods[$checkoutData['payment_method'] ?? '']['label'] ?? '')
                                            : (array_values($paymentMethods ?? [])[0]['label'] ?? '')
                                    ))
                                ?>
                            </span>
                            <span class="category-select-chevron" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="m7 10 5 5 5-5"></path>
                                </svg>
                            </span>
                        </button>

                        <div class="category-select-panel" id="paymentMethodSelectPanel" role="listbox" hidden>
                            <?php foreach (($paymentMethods ?? []) as $paymentKey => $paymentMethod): ?>
                                <button
                                    type="button"
                                    class="category-select-option <?= (($checkoutData['payment_method'] ?? '') === $paymentKey) ? 'is-selected' : '' ?>"
                                    data-value="<?= e((string) $paymentKey) ?>"
                                    role="option"
                                    aria-selected="<?= (($checkoutData['payment_method'] ?? '') === $paymentKey) ? 'true' : 'false' ?>"
                                >
                                    <?= e((string) ($paymentMethod['label'] ?? $paymentKey)) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (!empty($errors['payment_method'])): ?><span class="error-text"><?= e((string) $errors['payment_method']) ?></span><?php endif; ?>
                    <?php foreach (($paymentMethods ?? []) as $paymentKey => $paymentMethod): ?>
                        <span class="checkout-method-note<?= (($checkoutData['payment_method'] ?? '') === $paymentKey) ? ' is-active' : '' ?>" data-payment-note="<?= e((string) $paymentKey) ?>">
                            <?= e((string) ($paymentMethod['description'] ?? '')) ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="form-group checkout-span-2">
                    <label for="note"><?= e(t('checkout.order_note')) ?></label>
                    <textarea id="note" name="note" rows="4" placeholder="<?= e(t('checkout.order_note_placeholder')) ?>"><?= e((string) ($checkoutData['note'] ?? '')) ?></textarea>
                </div>

                <div class="checkout-actions checkout-span-2">
                    <a href="<?= e(url('/cart')) ?>" class="btn btn-secondary"><?= e(t('checkout.back_to_cart')) ?></a>
                    <button type="submit" class="btn btn-primary"><?= e(t('checkout.place_order')) ?></button>
                </div>
            </form>
        </div>

        <div class="checkout-side-column">
            <aside class="cart-summary-panel">
                <span class="section-eyebrow"><?= e(t('cart.order_summary')) ?></span>
                <h2><?= e(t('checkout.items_in_order')) ?></h2>
                <p class="summary-copy"><?= e(t('checkout.review_last_time')) ?></p>

                <div class="checkout-mini-list">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="checkout-mini-item">
                            <div>
                                <strong><?= e((string) $item['name']) ?></strong>
                                <span>x<?= e((string) $item['quantity']) ?></span>
                            </div>
                            <strong><?= format_price((float) $item['subtotal']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-line">
                    <span><?= e(t('cart.subtotal')) ?></span>
                    <strong><?= format_price((float) $totals['subtotal']) ?></strong>
                </div>
                <div class="summary-line">
                    <span><?= e(t('cart.shipping')) ?></span>
                    <strong><?= format_price((float) $totals['shipping']) ?></strong>
                </div>
                <div class="summary-line">
                    <span><?= e(t('cart.discount')) ?></span>
                    <strong>-<?= format_price((float) $totals['discount']) ?></strong>
                </div>
                <div class="summary-line summary-line-total">
                    <span><?= e(t('cart.total')) ?></span>
                    <strong><?= format_price((float) $totals['total']) ?></strong>
                </div>

            </aside>

            <section class="checkout-voucher-panel cart-summary-panel">
                <div class="checkout-voucher-head">
                    <strong><?= e(t('checkout.voucher_title')) ?></strong>
                    <?php if (!empty($availableVouchers)): ?>
                        <div class="checkout-voucher-suggest">
                            <span class="checkout-voucher-suggest-label"><?= e(t('checkout.voucher_popular')) ?>:</span>
                            <div class="checkout-voucher-carousel">
                                <button type="button" class="voucher-scroll-btn voucher-scroll-btn-prev" data-voucher-scroll="prev" aria-label="Scroll vouchers left">‹</button>
                                <div class="checkout-voucher-chip-list-wrap">
                                    <div class="checkout-voucher-chip-list" id="checkoutVoucherChipList">
                                    <?php foreach ($availableVouchers as $voucherItem): ?>
                                        <button
                                            type="button"
                                            class="checkout-voucher-chip"
                                            data-voucher-code="<?= e((string) ($voucherItem['code'] ?? '')) ?>"
                                            aria-label="<?= e((string) (($voucherItem['code'] ?? '') . ' ' . ($voucherItem['label'] ?? ''))) ?>">
                                            <span class="checkout-voucher-chip-main">
                                                <small class="checkout-voucher-chip-kicker">Black Friday</small>
                                                <span class="checkout-voucher-chip-value-row">
                                                    <strong class="checkout-voucher-chip-value"><?= e((string) ($voucherItem['label'] ?? '')) ?></strong>
                                                    <em class="checkout-voucher-chip-sideword">OFF</em>
                                                </span>
                                            </span>
                                            <span class="checkout-voucher-chip-tail">
                                                <img
                                                    src="https://api.qrserver.com/v1/create-qr-code/?size=92x92&data=<?= e(rawurlencode((string) ($voucherItem['code'] ?? ''))) ?>"
                                                    alt="<?= e((string) ('QR ' . ($voucherItem['code'] ?? ''))) ?>"
                                                    loading="lazy">
                                                <span><?= e((string) ($voucherItem['code'] ?? '')) ?></span>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                    </div>
                                </div>
                                <button type="button" class="voucher-scroll-btn voucher-scroll-btn-next" data-voucher-scroll="next" aria-label="Scroll vouchers right">›</button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <span><?= e(t('checkout.voucher_hint')) ?></span>
                </div>

                <form action="<?= e(url('/checkout/voucher')) ?>" method="POST" class="checkout-voucher-form">
                    <input
                        type="text"
                        name="voucher_code"
                        value="<?= e((string) ($totals['voucher']['code'] ?? ($checkoutData['voucher_code'] ?? ''))) ?>"
                        placeholder="<?= e(t('checkout.voucher_placeholder')) ?>"
                        autocomplete="off">
                    <button type="submit" class="btn btn-primary"><?= e(t('checkout.voucher_apply')) ?></button>
                </form>

                <?php if (!empty($totals['voucher']['code'])): ?>
                    <div class="checkout-voucher-active">
                        <span><?= e(t('checkout.voucher_active')) ?>: <strong><?= e((string) $totals['voucher']['code']) ?></strong></span>
                        <form action="<?= e(url('/checkout/voucher/remove')) ?>" method="POST">
                            <button type="submit" class="btn btn-secondary"><?= e(t('checkout.voucher_remove')) ?></button>
                        </form>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </section>
</section>

<script>
    (function () {
        const paymentSelect = document.getElementById('payment_method');
        const notes = document.querySelectorAll('[data-payment-note]');
        const paymentShell = document.getElementById('paymentMethodSelectShell');
        const paymentTrigger = document.getElementById('paymentMethodSelectTrigger');
        const paymentLabel = document.getElementById('paymentMethodSelectLabel');
        const paymentPanel = document.getElementById('paymentMethodSelectPanel');
        const voucherInput = document.querySelector('.checkout-voucher-form input[name="voucher_code"]');
        const voucherChips = document.querySelectorAll('[data-voucher-code]');
        const voucherChipList = document.getElementById('checkoutVoucherChipList');
        const voucherScrollButtons = document.querySelectorAll('[data-voucher-scroll]');
        let isPaymentOpen = false;

        if (!paymentSelect || !notes.length) {
            return;
        }

        const syncPaymentNotes = function () {
            Array.prototype.forEach.call(notes, function (note) {
                note.classList.toggle('is-active', note.getAttribute('data-payment-note') === paymentSelect.value);
            });
        };

        const syncPaymentVisibility = function () {
            if (!paymentShell || !paymentTrigger || !paymentPanel) {
                return;
            }

            paymentShell.classList.toggle('is-open', isPaymentOpen);
            paymentTrigger.setAttribute('aria-expanded', isPaymentOpen ? 'true' : 'false');
            paymentPanel.hidden = !isPaymentOpen;
        };

        const syncPaymentUi = function () {
            if (!paymentLabel || !paymentPanel) {
                return;
            }

            const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
            paymentLabel.textContent = selectedOption ? selectedOption.textContent : '';

            Array.prototype.forEach.call(paymentPanel.querySelectorAll('.category-select-option'), function (optionButton) {
                const isSelected = optionButton.dataset.value === paymentSelect.value;
                optionButton.classList.toggle('is-selected', isSelected);
                optionButton.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        };

        if (paymentTrigger && paymentShell && paymentPanel) {
            syncPaymentUi();
            syncPaymentVisibility();

            paymentTrigger.addEventListener('click', function () {
                isPaymentOpen = !isPaymentOpen;
                syncPaymentVisibility();
            });

            Array.prototype.forEach.call(paymentPanel.querySelectorAll('.category-select-option'), function (optionButton) {
                optionButton.addEventListener('click', function () {
                    paymentSelect.value = optionButton.dataset.value || '';
                    syncPaymentUi();
                    syncPaymentNotes();
                    isPaymentOpen = false;
                    syncPaymentVisibility();
                });
            });
        }

        document.addEventListener('click', function (event) {
            if (paymentShell && paymentShell.contains(event.target)) {
                return;
            }

            isPaymentOpen = false;
            syncPaymentVisibility();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            isPaymentOpen = false;
            syncPaymentVisibility();
        });

        paymentSelect.addEventListener('change', syncPaymentNotes);
        paymentSelect.addEventListener('change', syncPaymentUi);
        syncPaymentNotes();

        if (voucherInput) {
            const toUpperVoucher = function () {
                voucherInput.value = voucherInput.value.toUpperCase();
            };

            voucherInput.addEventListener('input', toUpperVoucher);
            voucherInput.addEventListener('blur', toUpperVoucher);
            toUpperVoucher();

            Array.prototype.forEach.call(voucherChips, function (chip) {
                chip.addEventListener('click', function () {
                    voucherInput.value = String(chip.getAttribute('data-voucher-code') || '').toUpperCase();
                    voucherInput.focus();
                });
            });
        }

        if (voucherChipList && voucherScrollButtons.length) {
            const syncVoucherScrollButtons = function () {
                const maxScrollLeft = Math.max(0, voucherChipList.scrollWidth - voucherChipList.clientWidth);
                const currentLeft = Math.max(0, voucherChipList.scrollLeft);

                Array.prototype.forEach.call(voucherScrollButtons, function (button) {
                    const isPrev = button.getAttribute('data-voucher-scroll') === 'prev';
                    const shouldDisable = isPrev ? currentLeft <= 2 : currentLeft >= (maxScrollLeft - 2);
                    button.disabled = shouldDisable;
                });
            };

            Array.prototype.forEach.call(voucherScrollButtons, function (button) {
                button.addEventListener('click', function () {
                    const direction = button.getAttribute('data-voucher-scroll') === 'prev' ? -1 : 1;
                    const firstChip = voucherChipList.querySelector('.checkout-voucher-chip');
                    const step = firstChip ? firstChip.offsetWidth : voucherChipList.clientWidth;
                    const styles = window.getComputedStyle(voucherChipList);
                    const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
                    const delta = Math.round((step + gap) * direction);
                    voucherChipList.scrollBy({
                        left: delta,
                        behavior: 'smooth',
                    });
                });
            });

            voucherChipList.addEventListener('scroll', syncVoucherScrollButtons, {
                passive: true,
            });

            window.addEventListener('resize', syncVoucherScrollButtons);
            syncVoucherScrollButtons();
        }
    })();
</script>

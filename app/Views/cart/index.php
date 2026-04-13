<section class="cart-page">
    <section class="cart-layout">
        <div class="cart-main-panel">
            <div class="section-heading-row cart-heading-row">
                <div>
                    <span class="section-eyebrow"><?= e(t('cart.shopping_cart')) ?></span>
                    <h1><?= e(t('cart.selected_products')) ?></h1>
                </div>
                <p><?= e(t('cart.session_note')) ?></p>
            </div>

            <?php if (empty($cartItems)): ?>
                <div class="empty-products-state">
                    <h3><?= e(t('cart.empty_title')) ?></h3>
                    <p><?= e(t('cart.empty_description')) ?></p>
                    <div class="cart-empty-actions">
                        <a href="<?= e(url('/products')) ?>" class="btn btn-primary"><?= e(t('cart.continue_shopping')) ?></a>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-item-list">
                    <?php foreach ($cartItems as $item): ?>
                        <article class="cart-item-card">
                            <div class="cart-item-media">
                                <?php
                                    $imageName = trim((string) ($item['image'] ?? ''));
                                    $imageSrc  = ($imageName !== '' && is_file(BASE_PATH . '/public/assets/images/products/' . $imageName))
                                        ? asset('images/products/' . $imageName)
                                        : asset('images/image-placeholder.png');
                                ?>
                                <img src="<?= e($imageSrc) ?>" alt="<?= e((string) $item['name']) ?>">
                            </div>

                            <div class="cart-item-content">
                                <div class="cart-item-top">
                                    <div>
                                        <span class="product-modern-category"><?= e((string) $item['category']) ?></span>
                                        <h3><?= e((string) $item['name']) ?></h3>
                                        <p class="cart-item-price-note"><?= format_price((float) $item['price']) ?> <?= e(t('cart.each')) ?></p>
                                    </div>
                                    <strong class="cart-item-subtotal"><?= format_price((float) $item['subtotal']) ?></strong>
                                </div>

                                <div class="cart-item-actions-row">
                                    <form action="<?= e(url('/cart/update')) ?>" method="POST" class="cart-quantity-form">
                                        <input type="hidden" name="product_id" value="<?= e((string) $item['id']) ?>">
                                        <label for="qty-<?= e((string) $item['id']) ?>"><?= e(t('cart.quantity')) ?></label>
                                        <input id="qty-<?= e((string) $item['id']) ?>" type="number" min="1" name="quantity" value="<?= e((string) $item['quantity']) ?>">
                                        <button type="submit" class="btn btn-secondary"><?= e(t('cart.update')) ?></button>
                                    </form>

                                    <form action="<?= e(url('/cart/remove')) ?>" method="POST">
                                        <input type="hidden" name="product_id" value="<?= e((string) $item['id']) ?>">
                                        <button type="submit" class="btn btn-secondary"><?= e(t('cart.remove')) ?></button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="cart-summary-panel">
            <span class="section-eyebrow"><?= e(t('cart.order_summary')) ?></span>
            <h2><?= e(t('cart.checkout_preview')) ?></h2>
            <p class="summary-copy"><?= e(t('cart.review_totals')) ?></p>

            <div class="summary-line">
                <span><?= e(t('cart.items')) ?></span>
                <strong><?= e((string) $totals['count']) ?></strong>
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

            <div class="cart-summary-actions">
                <a href="<?= e(url('/checkout')) ?>" class="btn btn-primary btn-full <?= empty($cartItems) ? 'is-disabled' : '' ?>"><?= e(t('cart.proceed_checkout')) ?></a>
                <a href="<?= e(url('/products')) ?>" class="btn btn-secondary btn-full"><?= e(t('cart.continue_shopping')) ?></a>
            </div>
        </aside>
    </section>
</section>

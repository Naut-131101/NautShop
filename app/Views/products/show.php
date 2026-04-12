<section class="product-detail-page">
    <div class="product-detail-breadcrumbs">
        <a href="<?= e(url('/products')) ?>"><?= e(t('layout.products')) ?></a>
        <span>/</span>
        <span><?= e((string) $product['category']) ?></span>
    </div>

    <section class="product-detail-hero">
        <div class="product-gallery-panel">
            <div class="product-gallery-main">
                <span class="product-gallery-label"><?= e(t('product.featured_view')) ?></span>
                <strong><img
                        src="<?= e((string) ($product['image_url'] ?? asset('images/image-placeholder.png'))) ?>"
                        alt="<?= e((string) ($product['image_alt'] ?? $product['name'] ?? 'Product image')) ?>"
                        class="product-detail-main-img"
                        loading="eager"></strong>
                <small><?= e((string) $product['name']) ?></small>
            </div>

            <div class="product-gallery-grid">
                <?php foreach ($gallery as $item): ?>
                    <button type="button" class="product-detail-thumb" aria-label="<?= e((string) $item['label']) ?>">
                        <img
                            src="<?= e((string) ($item['image'] ?? asset('images/image-placeholder.png'))) ?>"
                            alt="<?= e((string) $item['label']) ?>"
                            class="product-detail-thumb-img"
                            loading="lazy">
                        <span><?= e((string) $item['label']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-info-panel">
            <span class="product-detail-category"><?= e((string) $product['category']) ?></span>
            <h1><?= e((string) $product['name']) ?></h1>
            <p class="product-detail-description"><?= e((string) $product['description']) ?></p>

            <div class="product-detail-price-row">
                <strong class="product-detail-price"><?= format_price((float) $product['price']) ?></strong>
                <span class="product-detail-tax"><?= e(t('product.tax_note')) ?></span>
            </div>

            <div class="product-detail-highlights">
                <div class="highlight-card">
                    <span><?= e(t('product.delivery')) ?></span>
                    <strong><?= e(t('product.delivery_days')) ?></strong>
                </div>
                <div class="highlight-card">
                    <span><?= e(t('product.return')) ?></span>
                    <strong><?= e(t('product.return_days')) ?></strong>
                </div>
                <div class="highlight-card">
                    <span><?= e(t('product.status')) ?></span>
                    <strong><?= e(t('product.in_stock')) ?></strong>
                </div>
            </div>

            <form action="<?= e(url('/cart/add')) ?>" method="POST" class="product-purchase-form">
                <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
                <input type="hidden" name="redirect_to" value="/products/show?id=<?= e((string) $product['id']) ?>">

                <div class="product-quantity-row">
                    <label for="quantity"><?= e(t('product.quantity')) ?></label>
                    <input type="number" min="1" id="quantity" name="quantity" value="1">
                </div>

                <div class="product-action-row">
                    <button type="submit" class="btn btn-primary btn-detail-action"><?= e(t('product.add_to_cart')) ?></button>
                    <button type="submit" formaction="<?= e(url('/buy-now')) ?>" class="btn btn-secondary btn-detail-action"><?= e(t('product.buy_now')) ?></button>
                </div>
            </form>
        </div>
    </section>

    <section class="product-detail-section">
        <div class="section-heading-row">
            <div>
                <span class="section-eyebrow"><?= e(t('product.review_section')) ?></span>
                <h2><?= e(t('product.what_customers_say')) ?></h2>
            </div>
            <p><?= e(t('product.review_note')) ?></p>
        </div>

        <div class="review-grid">
            <?php foreach ($reviews as $review): ?>
                <article class="review-card">
                    <div class="review-card-head">
                        <div>
                            <strong><?= e((string) $review['name']) ?></strong>
                            <span><?= e((string) $review['date']) ?></span>
                        </div>
                        <div class="review-rating"><?= e((string) $review['rating']) ?>/5</div>
                    </div>
                    <h3><?= e((string) $review['title']) ?></h3>
                    <p><?= e((string) $review['content']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!empty($relatedProducts)): ?>
        <section class="product-detail-section">
            <div class="section-heading-row">
                <div>
                    <span class="section-eyebrow"><?= e(t('product.related_products')) ?></span>
                    <h2><?= e(t('product.more_from_category')) ?></h2>
                </div>
            </div>

            <div class="products-grid-modern related-grid">
                <?php foreach ($relatedProducts as $related): ?>
                    <article class="product-modern-card">
                        <a href="<?= e(url('/products/show?id=' . (int) $related['id'])) ?>" class="product-modern-image product-image-link">
                            <span><?= e((string) ($related['image'] ?? 'no-image')) ?></span>
                        </a>
                        <div class="product-modern-body">
                            <div class="product-modern-meta">
                                <span class="product-modern-category"><?= e((string) $related['category']) ?></span>
                            </div>
                            <h3><?= e((string) $related['name']) ?></h3>
                            <p class="product-modern-description"><?= e((string) $related['description']) ?></p>
                            <div class="product-modern-footer">
                                <strong class="product-modern-price"><?= format_price((float) $related['price']) ?></strong>
                                <a href="<?= e(url('/products/show?id=' . (int) $related['id'])) ?>" class="product-ghost-btn"><?= e(t('products.details')) ?></a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>
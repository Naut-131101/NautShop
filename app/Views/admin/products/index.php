<?php
function adminProductImageUrl(string $imageName): string
{
    if ($imageName !== '') {
        $abs = BASE_PATH . '/public/assets/images/products/' . ltrim($imageName, '/');
        if (is_file($abs)) {
            return asset('images/products/' . $imageName);
        }
    }

    return asset('images/image-placeholder.png');
}
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e(t('admin.products_title')) ?></h1>
        <p class="admin-page-sub"><?= e(t('admin.products_count', ['count' => (string) $pagination['total']])) ?></p>
    </div>
    <a href="<?= e(url('/admin/products/create')) ?>" class="admin-btn admin-btn-primary">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        <?= e(t('admin.add_product')) ?>
    </a>
</div>

<form method="GET" action="<?= e(url('/admin/products')) ?>" class="admin-filter-bar">
    <div class="admin-filter-grid">
        <div class="admin-filter-field">
            <label for="keyword"><?= e(t('admin.search_products')) ?></label>
            <div class="admin-filter-search" data-admin-search data-history-key="admin_products_searches">
                <input id="keyword" name="keyword" type="text" class="admin-input" placeholder="<?= e(t('admin.search_products_placeholder')) ?>" value="<?= e($filters['keyword']) ?>" autocomplete="off">
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
            <label for="category"><?= e(t('admin.category')) ?></label>
            <div class="admin-filter-select" data-admin-select>
                <select id="category" name="category" class="admin-select admin-filter-native" tabindex="-1" aria-hidden="true">
                    <option value=""><?= e(t('admin.all_categories')) ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e((string) $cat['category']) ?>" <?= ($filters['category'] === $cat['category']) ? 'selected' : '' ?>>
                            <?= e((string) $cat['category']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="button" class="admin-filter-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span class="admin-filter-select-label"><?= e($filters['category'] !== '' ? $filters['category'] : t('admin.all_categories')) ?></span>
                    <span class="admin-filter-select-chevron" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M7 14l5-5 5 5"/></svg>
                    </span>
                </button>

                <div class="admin-filter-select-panel" role="listbox" hidden>
                    <button type="button" class="admin-filter-select-option <?= $filters['category'] === '' ? 'is-selected' : '' ?>" data-value="" role="option" aria-selected="<?= $filters['category'] === '' ? 'true' : 'false' ?>"><?= e(t('admin.all_categories')) ?></button>
                    <?php foreach ($categories as $cat): ?>
                        <?php $categoryName = (string) $cat['category']; ?>
                        <button type="button"
                                class="admin-filter-select-option <?= $filters['category'] === $categoryName ? 'is-selected' : '' ?>"
                                data-value="<?= e($categoryName) ?>"
                                role="option"
                                aria-selected="<?= $filters['category'] === $categoryName ? 'true' : 'false' ?>">
                            <?= e($categoryName) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="admin-filter-actions">
            <button type="submit" class="admin-btn admin-btn-primary"><?= e(t('admin.apply')) ?></button>
            <a href="<?= e(url('/admin/products')) ?>" class="admin-btn admin-btn-secondary"><?= e(t('admin.reset')) ?></a>
        </div>
    </div>
</form>

<div class="admin-table-wrap">
    <?php if (empty($products)): ?>
        <div class="admin-empty">
            <svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/></svg>
            <p><?= e(t('admin.no_products_found')) ?></p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:60px;"><?= e(t('admin.image')) ?></th>
                    <th><?= e(t('admin.product_name')) ?></th>
                    <th><?= e(t('admin.category')) ?></th>
                    <th><?= e(t('admin.price')) ?></th>
                    <th style="width:140px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <img src="<?= e(adminProductImageUrl(trim((string) ($product['image'] ?? '')))) ?>"
                                 alt="<?= e((string) $product['name']) ?>"
                                 class="admin-image-preview" style="width:48px;height:48px;">
                        </td>
                        <td>
                            <strong><?= e((string) $product['name']) ?></strong>
                            <?php if (!empty($product['description'])): ?>
                                <div style="font-size:12px;color:var(--text-faint);margin-top:3px;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= e(mb_substr((string) $product['description'], 0, 80)) ?>…
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="admin-badge admin-badge-processing">
                                <?= e((string) $product['category']) ?>
                            </span>
                        </td>
                        <td style="font-weight:700;color:var(--gold);">
                            <?= e(format_price((float) $product['price'])) ?>
                        </td>
                        <td>
                            <div class="admin-table-actions">
                                <a href="<?= e(url('/admin/products/edit?id=' . $product['id'])) ?>" class="admin-btn admin-btn-secondary admin-btn-sm"><?= e(t('admin.edit')) ?></a>
                                <form method="POST" action="<?= e(url('/admin/products/delete')) ?>" onsubmit="return confirm('<?= e(t('admin.confirm_delete_product')) ?>')">
                                    <input type="hidden" name="id" value="<?= e((string) $product['id']) ?>">
                                    <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm"><?= e(t('admin.delete')) ?></button>
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
        $base = url('/admin/products') . '?'
            . http_build_query(array_filter(['keyword' => $filters['keyword'], 'category' => $filters['category']]));
        $base .= $base === url('/admin/products') . '?' ? '' : '&';
        ?>
        <?php if ($pagination['page'] > 1): ?>
            <a href="<?= e($base . 'page=' . ($pagination['page'] - 1)) ?>" class="admin-page-link">‹</a>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $pagination['lastPage']; $p++): ?>
            <a href="<?= e($base . 'page=' . $p) ?>" class="admin-page-link <?= $p === $pagination['page'] ? 'is-active' : '' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>

        <?php if ($pagination['page'] < $pagination['lastPage']): ?>
            <a href="<?= e($base . 'page=' . ($pagination['page'] + 1)) ?>" class="admin-page-link">›</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

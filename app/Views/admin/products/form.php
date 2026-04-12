<?php
$isEdit = $product !== null;
$action = $isEdit ? url('/admin/products/edit') : url('/admin/products/create');
$old = $old ?? [];

$val = function (string $key, mixed $default = '') use ($product, $old, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && isset($product[$key])) {
        return (string) $product[$key];
    }
    return (string) $default;
};

function adminProductImageUrl2(string $imageName): string
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
        <h1 class="admin-page-title"><?= e($isEdit ? t('admin.edit_product') : t('admin.add_product')) ?></h1>
        <p class="admin-page-sub"><?= e($isEdit ? t('admin.edit_product_subtitle') : t('admin.create_product_subtitle')) ?></p>
    </div>
    <a href="<?= e(url('/admin/products')) ?>" class="admin-btn admin-btn-secondary">← <?= e(t('admin.back')) ?></a>
</div>

<div class="admin-form-card">
    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $product['id']) ?>">
        <?php endif; ?>

        <div class="admin-form-grid">
            <div class="admin-form-row">
                <div class="admin-field">
                    <label class="admin-label" for="name"><?= e(t('admin.product_name_vi')) ?> <span style="color:var(--error-text)">*</span></label>
                    <input id="name" name="name" type="text" class="admin-input" value="<?= e($val('name')) ?>" placeholder="<?= e(t('admin.product_name_vi_placeholder')) ?>">
                    <?php if (!empty($errors['name'])): ?>
                        <span class="admin-field-error"><?= e($errors['name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-form-row">
                <div class="admin-field">
                    <label class="admin-label" for="price"><?= e(t('admin.price_vnd')) ?> <span style="color:var(--error-text)">*</span></label>
                    <input id="price" name="price" type="number" step="1000" min="0" class="admin-input" value="<?= e($val('price', '0')) ?>" placeholder="149000">
                    <?php if (!empty($errors['price'])): ?>
                        <span class="admin-field-error"><?= e($errors['price']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="admin-field">
                    <label class="admin-label" for="quantity"><?= e(t('admin.stock_quantity')) ?> <span style="color:var(--error-text)">*</span></label>
                    <input id="quantity" name="quantity" type="number" step="1" min="0" class="admin-input" value="<?= e($val('quantity', '0')) ?>" placeholder="0">
                    <?php if (!empty($errors['quantity'])): ?>
                        <span class="admin-field-error"><?= e($errors['quantity']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-form-row">
                <div class="admin-field">
                    <label class="admin-label" for="category"><?= e(t('admin.category_vi')) ?> <span style="color:var(--error-text)">*</span></label>
                    <div class="adm-autocomplete" id="categoryAutocomplete">
                        <input id="category" name="category" type="text" class="admin-input" value="<?= e($val('category')) ?>" placeholder="<?= e(t('admin.category_vi_placeholder')) ?>" autocomplete="off">
                        <div class="admin-filter-select-panel adm-autocomplete-panel" hidden>
                            <?php foreach ($categories as $cat): ?>
                                <?php $catVal = (string) ($cat['category'] ?? ''); if ($catVal === '') continue; ?>
                                <button type="button" class="admin-filter-select-option adm-autocomplete-opt" data-value="<?= e($catVal) ?>">
                                    <?= e($catVal) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (!empty($errors['category'])): ?>
                        <span class="admin-field-error"><?= e($errors['category']) ?></span>
                    <?php endif; ?>
                </div>

            </div>

            <div class="admin-form-row">
                <div class="admin-field">
                    <label class="admin-label" for="description"><?= e(t('admin.description_vi')) ?></label>
                    <textarea id="description" name="description" class="admin-textarea" placeholder="<?= e(t('admin.description_vi_placeholder')) ?>"><?= e($val('description')) ?></textarea>
                </div>

            </div>

            <div class="admin-field">
                <label class="admin-label" for="image"><?= e(t('admin.product_image')) ?></label>

                <?php if ($isEdit && !empty($product['image'])): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?= e(adminProductImageUrl2((string) $product['image'])) ?>" alt="<?= e(t('admin.current_image_alt')) ?>" class="admin-image-preview" id="imagePreview">
                        <p class="admin-field-hint" style="margin-top:4px;"><?= e(t('admin.current_image_hint')) ?></p>
                    </div>
                <?php else: ?>
                    <img src="<?= e(asset('images/image-placeholder.png')) ?>" alt="<?= e(t('admin.preview_alt')) ?>" class="admin-image-preview" id="imagePreview" style="display:none;">
                <?php endif; ?>

                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input" style="padding:6px;" onchange="previewImage(this)">
                <span class="admin-field-hint"><?= e(t('admin.image_hint')) ?></span>
                <?php if (!empty($errors['image'])): ?>
                    <span class="admin-field-error"><?= e($errors['image']) ?></span>
                <?php endif; ?>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <?= e($isEdit ? t('admin.save_changes') : t('admin.add_product')) ?>
                </button>
                <a href="<?= e(url('/admin/products')) ?>" class="admin-btn admin-btn-secondary"><?= e(t('admin.cancel')) ?></a>
            </div>
        </div>
    </form>
</div>

<style>
.adm-autocomplete { position: relative; }
.adm-autocomplete-panel {
    position: absolute;
    top: calc(100% + 6px);
    left: 0; right: 0;
    z-index: 50;
    max-height: 220px;
    overflow-y: auto;
    width: 100%;
}
</style>

<script>
function previewImage(input) {
    var preview = document.getElementById('imagePreview');
    if (!preview) return;
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

(function () {
    var wrap  = document.getElementById('categoryAutocomplete');
    if (!wrap) return;
    var inp   = wrap.querySelector('input');
    var panel = wrap.querySelector('.adm-autocomplete-panel');
    var opts  = Array.from(wrap.querySelectorAll('.adm-autocomplete-opt'));

    function show() { panel.hidden = false; }
    function hide() { panel.hidden = true; }

    function filter(q) {
        var low = q.trim().toLowerCase();
        var any = false;
        opts.forEach(function (o) {
            var match = low === '' || o.dataset.value.toLowerCase().includes(low);
            o.style.display = match ? '' : 'none';
            if (match) any = true;
        });
        if (any) show(); else hide();
    }

    inp.addEventListener('focus', function () { filter(inp.value); });
    inp.addEventListener('input', function () { filter(inp.value); });

    opts.forEach(function (o) {
        o.addEventListener('mousedown', function (e) {
            e.preventDefault();
            inp.value = o.dataset.value;
            hide();
        });
    });

    document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target)) hide();
    });
})();
</script>

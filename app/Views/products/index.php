<?php
$musicPlaylist = store_playlist();
$currentProductsRedirect = $_SERVER['REQUEST_URI'] ?? '/products';
$scriptBasePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

if ($scriptBasePath !== '/' && $scriptBasePath !== '' && str_starts_with($currentProductsRedirect, $scriptBasePath)) {
    $currentProductsRedirect = substr($currentProductsRedirect, strlen($scriptBasePath));
}

if ($currentProductsRedirect === '') {
    $currentProductsRedirect = '/products';
}
?>

<section class="products-page">
    <section class="products-hero">
        <div class="products-hero-copy">
            <span class="section-eyebrow"><?= e(t('products.hero_eyebrow')) ?></span>
            <h1><?= e(t('products.hero_title')) ?></h1>
            <p><?= e(t('products.hero_description')) ?></p>
        </div>

        <div class="products-hero-side">
            <div class="hero-stat-card">
                <span class="hero-stat-label"><?= e(t('products.total_products')) ?></span>
                <strong id="productsTotalCount"><?= e((string) $pagination['total']) ?></strong>
                <small><?= e(t('products.selected_ready')) ?></small>
            </div>

            <div class="hero-stat-card">
                <span class="hero-stat-label"><?= e(t('products.cart_items')) ?></span>
                <strong id="productsCartItemsCount"><?= e((string) ($cartCount ?? 0)) ?></strong>
                <small><?= e(!empty($cartCount) ? t('products.ready_checkout') : t('products.add_to_start')) ?></small>
            </div>

            <div
                class="hero-stat-card hero-music-card"
                id="heroMusicCard"
                data-playlist='<?= e((string) json_encode($musicPlaylist, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>'
                data-play-label="<?= e(t('products.play_music')) ?>"
                data-pause-label="<?= e(t('products.pause_music')) ?>"
                data-track-label="<?= e(t('products.track_label')) ?>">
                <div class="hero-music-head">
                    <div class="hero-music-topline">
                        <span class="hero-stat-label"><?= e(t('products.store_playlist')) ?></span>
                        <span class="music-live-pill"><?= e(t('products.playlist_pill')) ?></span>
                    </div>
                    <strong class="hero-music-track" id="musicTrackTitle"><?= e($musicPlaylist[0]['title']) ?></strong>
                    <small class="hero-music-artist" id="musicTrackArtist"><?= e($musicPlaylist[0]['artist']) ?></small>
                </div>

                <div class="hero-music-copy-wrap">
                    <div class="music-rhythm-mark" id="musicRhythmMark" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <p class="hero-music-copy">
                        <?= e(t('products.playlist_description')) ?>
                    </p>
                </div>

                <div id="storePlaylistYoutube" class="music-youtube-host" aria-hidden="true"></div>

                <div class="music-progress-wrap">
                    <span class="music-time-pill" id="musicCurrentTime">00:00</span>
                    <input type="range" id="musicProgress" class="music-progress" min="0" max="100" value="0" step="1" aria-label="<?= e(t('products.music_progress')) ?>">
                    <span class="music-time-pill music-time-pill-end" id="musicDuration">00:00</span>
                </div>

                <div class="music-console-row">
                    <div class="music-primary-controls">
                        <button type="button" class="music-icon-btn" id="musicPrevBtn" aria-label="<?= e(t('products.prev_track')) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M7 5h2v14H7V5Zm10 1.6v10.8c0 .8-.9 1.2-1.5.8l-7.6-5.4a1 1 0 0 1 0-1.6l7.6-5.4c.6-.4 1.5 0 1.5.8Z"></path>
                            </svg>
                        </button>

                        <button type="button" class="music-icon-btn music-icon-btn-strong" id="musicPlayBtn" aria-label="<?= e(t('products.pause_music')) ?>" data-playing="true">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 6h3v12H8V6Zm5 0h3v12h-3V6Z"></path>
                            </svg>
                        </button>

                        <button type="button" class="music-icon-btn" id="musicNextBtn" aria-label="<?= e(t('products.next_track')) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M15 5h2v14h-2V5ZM7 6.6v10.8c0 .8.9 1.2 1.5.8l7.6-5.4a1 1 0 0 0 0-1.6L8.5 5.8C7.9 5.4 7 5.8 7 6.6Z"></path>
                            </svg>
                        </button>
                    </div>

                </div>

                <div class="music-track-meta">
                    <span class="music-track-counter" id="musicTrackCounter"><?= e(t('products.track_label')) ?> 1 / <?= e((string) count($musicPlaylist)) ?></span>
                    <label class="music-volume-wrap" for="musicVolume">
                        <span class="music-volume-label"><?= e(t('products.volume')) ?></span>
                        <input type="range" id="musicVolume" min="0" max="1" value="1" step="0.05" aria-label="<?= e(t('products.volume')) ?>">
                    </label>
                    <span class="music-track-note"><?= e(t('products.playlist_note_1')) ?></span>
                    <span class="music-track-note"><?= e(t('products.playlist_note_2')) ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="products-catalog-shell">
        <section class="filter-panel">
            <form action="<?= e(url('/products')) ?>" method="GET" class="filter-form-modern" id="productsFilterForm">
                <div class="filter-modern-grid">
                    <div class="form-group form-group-wide">
                        <label for="keyword"><?= e(t('products.search_products')) ?></label>
                        <div class="search-field-shell">
                            <input
                                type="text"
                                id="keyword"
                                name="keyword"
                                placeholder="<?= e(t('products.search_placeholder')) ?>"
                                value="<?= e((string) ($filters['keyword'] ?? '')) ?>"
                                autocomplete="off">
                            <button type="button" class="search-clear-btn" id="keywordClearBtn" aria-label="<?= e(t('products.clear_search')) ?>">x</button>
                        </div>
                        <div class="search-history-panel" id="searchHistoryPanel" hidden>
                            <div class="search-history-head">
                                <strong><?= e(t('products.recent_searches')) ?></strong>
                                <button type="button" class="search-history-trash" id="clearSearchHistoryBtn" aria-label="<?= e(t('products.clear_search_history')) ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm-2 6h2v8H7V9Zm4 0h2v8h-2V9Zm4 0h2v8h-2V9ZM6 7h12l-1 13H7L6 7Z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="search-history-list" id="searchHistoryList"></div>
                        </div>
                    </div>

                    <div class="form-group form-group-category">
                        <label for="category"><?= e(t('products.category')) ?></label>
                        <div class="category-select-shell" id="categorySelectShell">
                            <select id="category" name="category" class="category-select-native" tabindex="-1" aria-hidden="true">
                                <option value=""><?= e(t('products.all_categories')) ?></option>
                                <?php foreach ($categories as $categoryItem): ?>
                                    <?php
                                    $categoryValue = (string) ($categoryItem['category'] ?? '');
                                    $categoryLabel = current_locale() === 'en'
                                        ? localized_product_field((array) $categoryItem, 'category')
                                        : $categoryValue;
                                    ?>
                                    <option
                                        value="<?= e($categoryValue) ?>"
                                        <?= (($filters['category'] ?? '') === $categoryValue) ? 'selected' : '' ?>>
                                        <?= e($categoryLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="button" class="category-select-trigger" id="categorySelectTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="category-select-label" id="categorySelectLabel">
                                    <?= e((string) (($filters['category'] ?? '') !== '' ? localized_product_field(['category' => (string) ($filters['category'] ?? '')], 'category') : t('products.all_categories'))) ?>
                                </span>
                                <span class="category-select-chevron" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="m7 10 5 5 5-5"></path>
                                    </svg>
                                </span>
                            </button>

                            <div class="category-select-panel" id="categorySelectPanel" role="listbox" hidden>
                                <button type="button" class="category-select-option <?= (($filters['category'] ?? '') === '') ? 'is-selected' : '' ?>" data-value="" role="option" aria-selected="<?= (($filters['category'] ?? '') === '') ? 'true' : 'false' ?>">
                                    <?= e(t('products.all_categories')) ?>
                                </button>
                                <?php foreach ($categories as $categoryItem): ?>
                                    <?php
                                    $categoryValue = (string) ($categoryItem['category'] ?? '');
                                    $categoryName = current_locale() === 'en'
                                        ? localized_product_field((array) $categoryItem, 'category')
                                        : $categoryValue;
                                    ?>
                                    <button
                                        type="button"
                                        class="category-select-option <?= (($filters['category'] ?? '') === $categoryValue) ? 'is-selected' : '' ?>"
                                        data-value="<?= e($categoryValue) ?>"
                                        role="option"
                                        aria-selected="<?= (($filters['category'] ?? '') === $categoryValue) ? 'true' : 'false' ?>">
                                        <?= e($categoryName) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="filter-action-group">
                        <button type="submit" class="btn btn-primary"><?= e(t('products.apply')) ?></button>
                        <a href="<?= e(url('/products')) ?>" class="btn btn-secondary" id="productsResetLink"><?= e(t('products.reset')) ?></a>
                    </div>
                </div>

            </form>
        </section>

        <div id="productsResultsRegion">
            <?php if (empty($products)): ?>
                <section class="empty-products-state">
                    <h3><?= e(t('products.no_matches')) ?></h3>
                    <p><?= e(t('products.try_different_filter')) ?></p>
                </section>
            <?php else: ?>
                <section class="products-grid-modern">
                    <?php foreach ($products as $product): ?>
                        <article class="product-modern-card">
                            <div class="product-card-flip">
                                <a href="<?= e(url('/products/show?id=' . (int) $product['id'])) ?>" class="product-card-face product-card-face-front product-modern-image product-image-link">
                                    <img
                                        src="<?= e((string) ($product['image_url'] ?? asset('images/image-placeholder.png'))) ?>"
                                        alt="<?= e((string) ($product['image_alt'] ?? $product['name'] ?? 'Product image')) ?>"
                                        class="product-modern-img"
                                        loading="lazy">
                                </a>

                                <div class="product-card-face product-card-face-back">
                                    <a href="<?= e(url('/products/show?id=' . (int) $product['id'])) ?>" class="product-modern-image product-image-link product-modern-image-back">
                                        <img
                                            src="<?= e((string) ($product['image_url'] ?? asset('images/image-placeholder.png'))) ?>"
                                            alt="<?= e((string) ($product['image_alt'] ?? $product['name'] ?? 'Product image')) ?>"
                                            class="product-modern-img"
                                            loading="lazy">
                                    </a>

                                    <div class="product-modern-body">
                                        <div class="product-modern-meta">
                                            <span class="product-modern-category"><?= e((string) $product['category']) ?></span>
                                        </div>

                                        <h3><?= e((string) $product['name']) ?></h3>

                                        <p class="product-modern-description">
                                            <?= e((string) $product['description']) ?>
                                        </p>

                                        <div class="product-modern-footer">
                                            <strong class="product-modern-price">
                                                <?= format_price((float) $product['price']) ?>
                                            </strong>

                                            <div class="product-card-actions">
                                                <a href="<?= e(url('/products/show?id=' . (int) $product['id'])) ?>" class="product-ghost-btn"><?= e(t('products.details')) ?></a>
                                                <form action="<?= e(url('/buy-now')) ?>" method="POST" class="product-inline-form">
                                                    <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn btn-secondary btn-inline"><?= e(t('product.buy_now')) ?></button>
                                                </form>
                                                <form action="<?= e(url('/cart/add')) ?>" method="POST" class="product-inline-form product-add-form">
                                                    <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <input type="hidden" name="redirect_to" value="<?= e($currentProductsRedirect) ?>">
                                                    <button type="submit" class="btn btn-primary btn-inline"><?= e(t('products.add')) ?></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>

                <?php if (($pagination['lastPage'] ?? 1) > 1): ?>
                    <div class="pagination-modern">
                        <?php
                        $currentPage = (int) $pagination['page'];
                        $lastPage = (int) $pagination['lastPage'];
                        $keyword = (string) ($filters['keyword'] ?? '');
                        $category = (string) ($filters['category'] ?? '');

                        $buildPageUrl = function (int $page) use ($keyword, $category): string {
                            $query = http_build_query([
                                'keyword' => $keyword,
                                'category' => $category,
                                'page' => $page,
                            ]);

                            return url('/products') . '?' . $query;
                        };
                        ?>

                        <?php if ($currentPage > 1): ?>
                            <a href="<?= e($buildPageUrl($currentPage - 1)) ?>" class="page-link-modern"><?= e(t('products.prev_page')) ?></a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                            <a
                                href="<?= e($buildPageUrl($i)) ?>"
                                class="page-link-modern <?= $i === $currentPage ? 'active' : '' ?>">
                                <?= e((string) $i) ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($currentPage < $lastPage): ?>
                            <a href="<?= e($buildPageUrl($currentPage + 1)) ?>" class="page-link-modern"><?= e(t('products.next_page')) ?></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <footer class="products-footer">
        <section class="products-footer-newsletter">
            <div>
                <span class="section-eyebrow products-footer-eyebrow"><?= e(t('newsletter.eyebrow')) ?></span>
                <h2><?= e(t('newsletter.heading')) ?></h2>
                <p><?= e(t('newsletter.description')) ?></p>
            </div>
            <form class="products-footer-subscribe" action="#" method="POST">
                <input type="email" placeholder="<?= e(t('newsletter.placeholder')) ?>" aria-label="<?= e(t('newsletter.placeholder')) ?>">
                <button type="submit" class="btn btn-primary"><?= e(t('newsletter.button')) ?></button>
            </form>
        </section>

        <section class="products-footer-grid">
            <div class="products-footer-brand">
                <a href="<?= e(url('/products')) ?>" class="brand-logo">
                    <img src="<?= e(asset('images/logo.png')) ?>" alt="NautShop" class="brand-logo-img">
                </a>
                <p><?= e(t('footer.brand_description')) ?></p>
            </div>

            <div class="products-footer-links">
                <h3><?= e(t('footer.discover')) ?></h3>
                <a href="<?= e(url('/products')) ?>"><?= e(t('footer.products')) ?></a>
                <a href="<?= e(url('/cart')) ?>"><?= e(t('footer.cart')) ?></a>
                <a href="<?= e(url('/checkout')) ?>"><?= e(t('footer.checkout')) ?></a>
            </div>

            <div class="products-footer-links">
                <h3><?= e(t('footer.services')) ?></h3>
                <span><?= e(t('footer.service_1')) ?></span>
                <span><?= e(t('footer.service_2')) ?></span>
                <span><?= e(t('footer.service_3')) ?></span>
            </div>

            <div class="products-footer-links">
                <h3><?= e(t('footer.contact')) ?></h3>
                <span>Email: <a href="mailto:naut.131101@gmail.com">naut.131101@gmail.com</a></span>
                <span>Hotline: <a href="tel:0792007045">0792 007 045</a></span>
                <span>Việt Nam</span>
            </div>
        </section>

        <div class="products-footer-bottom">
            <span><?= e(t('footer.copyright')) ?></span>
            <div class="products-footer-bottom-links">
                <a href="<?= e(url('/products')) ?>"><?= e(t('footer.products')) ?></a>
                <a href="<?= e(url('/cart')) ?>"><?= e(t('footer.cart')) ?></a>
                <a href="<?= e(url('/checkout')) ?>"><?= e(t('footer.checkout')) ?></a>
            </div>
        </div>
    </footer>
</section>

<script>
    (function() {
        const musicCard = document.getElementById('heroMusicCard');
        const catalogShell = document.querySelector('.products-catalog-shell');
        const filterForm = document.getElementById('productsFilterForm');
        const resultsRegion = document.getElementById('productsResultsRegion');
        const totalCountNode = document.getElementById('productsTotalCount');
        const productsCartItemsCount = document.getElementById('productsCartItemsCount');
        const siteCartCount = document.getElementById('siteCartCount');
        const resetLink = document.getElementById('productsResetLink');
        const keywordInput = document.getElementById('keyword');
        const keywordClearBtn = document.getElementById('keywordClearBtn');
        const historyPanel = document.getElementById('searchHistoryPanel');
        const historyList = document.getElementById('searchHistoryList');
        const clearHistoryBtn = document.getElementById('clearSearchHistoryBtn');
        const categoryInput = document.getElementById('category');
        const categoryShell = document.getElementById('categorySelectShell');
        const categoryTrigger = document.getElementById('categorySelectTrigger');
        const categoryLabel = document.getElementById('categorySelectLabel');
        const categoryPanel = document.getElementById('categorySelectPanel');
        const syncCartCountUi = function(count) {
            const normalizedCount = Math.max(0, parseInt(count, 10) || 0);

            if (siteCartCount) {
                siteCartCount.textContent = String(normalizedCount);
            }

            if (productsCartItemsCount) {
                productsCartItemsCount.textContent = String(normalizedCount);
            }
        };
        const addToCartSuccessMessage = <?= json_encode(t('flash.cart.add_success'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const addToCartErrorMessage = <?= json_encode(t('flash.cart.product_not_found_add'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        document.addEventListener('submit', function(event) {
            const form = event.target instanceof HTMLFormElement ? event.target : null;

            if (!form || !form.classList.contains('product-add-form')) {
                return;
            }

            event.preventDefault();

            const submitButton = form.querySelector('button[type="submit"]');
            const originalLabel = submitButton ? submitButton.textContent : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = '...';
            }

            window.fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            }).then(function(response) {
                return response.json().catch(function() {
                    return {
                        success: false,
                        message: addToCartErrorMessage,
                    };
                });
            }).then(function(payload) {
                if (typeof payload.cartCount !== 'undefined') {
                    syncCartCountUi(payload.cartCount);
                }

                if (typeof window.showFlashToast === 'function') {
                    window.showFlashToast(payload.success ? addToCartSuccessMessage : addToCartErrorMessage, payload.success ? 'success' : 'error');
                }
            }).catch(function() {
                if (typeof window.showFlashToast === 'function') {
                    window.showFlashToast(addToCartErrorMessage, 'error');
                }
            }).finally(function() {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalLabel;
                }
            });
        });

        if (filterForm && resultsRegion && keywordInput && keywordClearBtn && historyPanel && historyList && clearHistoryBtn) {
            const storageKey = 'nautshop_recent_searches';
            let pendingRequest = null;
            let isHistoryOpen = false;
            let isCategoryOpen = false;

            const readHistory = function() {
                try {
                    const stored = window.localStorage.getItem(storageKey);
                    const parsed = stored ? JSON.parse(stored) : [];
                    return Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    return [];
                }
            };

            const writeHistory = function(items) {
                window.localStorage.setItem(storageKey, JSON.stringify(items));
            };

            const syncClearButton = function() {
                keywordClearBtn.hidden = keywordInput.value.trim() === '';
            };

            const syncHistoryVisibility = function() {
                const hasItems = historyList.childElementCount > 0;
                historyPanel.hidden = !(isHistoryOpen && hasItems);
            };

            const syncCategoryVisibility = function() {
                if (!categoryShell || !categoryTrigger || !categoryPanel) {
                    return;
                }

                categoryShell.classList.toggle('is-open', isCategoryOpen);
                categoryTrigger.setAttribute('aria-expanded', isCategoryOpen ? 'true' : 'false');
                categoryPanel.hidden = !isCategoryOpen;
            };

            const syncCategoryUi = function() {
                if (!categoryInput || !categoryLabel || !categoryPanel) {
                    return;
                }

                const selectedOption = categoryInput.options[categoryInput.selectedIndex];
                categoryLabel.textContent = selectedOption ? selectedOption.textContent : '';

                Array.prototype.forEach.call(categoryPanel.querySelectorAll('.category-select-option'), function(optionButton) {
                    const isSelected = optionButton.dataset.value === categoryInput.value;
                    optionButton.classList.toggle('is-selected', isSelected);
                    optionButton.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                });
            };

            const storeSearchTerm = function(term) {
                if (term === '') {
                    return;
                }

                const items = readHistory().filter(function(item) {
                    return item.toLowerCase() !== term.toLowerCase();
                });

                items.unshift(term);
                writeHistory(items.slice(0, 8));
            };

            const renderHistory = function() {
                const items = readHistory();
                historyList.innerHTML = '';

                items.forEach(function(term) {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'search-history-chip';
                    chip.textContent = term;
                    chip.addEventListener('click', function() {
                        keywordInput.value = term;
                        syncClearButton();
                        isHistoryOpen = false;
                        syncHistoryVisibility();
                        isCategoryOpen = false;
                        syncCategoryVisibility();
                        runProductsFetch(buildProductsUrl({
                            page: 1
                        }), true, false);
                    });
                    historyList.appendChild(chip);
                });

                syncHistoryVisibility();
            };

            const buildProductsUrl = function(overrides) {
                const params = new URLSearchParams(new FormData(filterForm));
                Object.keys(overrides || {}).forEach(function(key) {
                    const value = overrides[key];

                    if (value === '' || value === null || typeof value === 'undefined') {
                        params.delete(key);
                        return;
                    }

                    params.set(key, String(value));
                });

                if ((params.get('page') || '1') === '1') {
                    params.delete('page');
                }

                const query = params.toString();
                return filterForm.action + (query ? '?' + query : '');
            };

            const syncFormFromUrl = function(urlString) {
                const nextUrl = new URL(urlString, window.location.origin);
                keywordInput.value = nextUrl.searchParams.get('keyword') || '';

                if (categoryInput) {
                    categoryInput.value = nextUrl.searchParams.get('category') || '';
                    syncCategoryUi();
                }

                syncClearButton();
            };

            const replaceResultsFromDocument = function(doc) {
                const nextRegion = doc.getElementById('productsResultsRegion');
                const nextTotal = doc.getElementById('productsTotalCount');

                if (!nextRegion) {
                    return;
                }

                resultsRegion.innerHTML = nextRegion.innerHTML;

                if (nextTotal && totalCountNode) {
                    totalCountNode.textContent = nextTotal.textContent;
                }
            };

            const runProductsFetch = function(url, pushState, scrollToResults) {
                if (pendingRequest) {
                    pendingRequest.abort();
                }

                const controller = new AbortController();
                pendingRequest = controller;
                resultsRegion.classList.add('is-loading');

                window.fetch(url, {
                    signal: controller.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).then(function(response) {
                    return response.text();
                }).then(function(html) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    replaceResultsFromDocument(doc);
                    syncFormFromUrl(url);

                    if (pushState) {
                        window.history.pushState({
                            url: url
                        }, '', url);
                    }

                    if (scrollToResults) {
                        const scrollTarget = catalogShell || resultsRegion;
                        const top = scrollTarget.getBoundingClientRect().top + window.scrollY - 28;
                        window.scrollTo({
                            top: Math.max(0, top),
                            behavior: 'smooth',
                        });
                    }
                }).catch(function(error) {
                    if (error && error.name === 'AbortError') {
                        return;
                    }

                    window.location.href = url;
                }).finally(function() {
                    if (pendingRequest === controller) {
                        pendingRequest = null;
                    }

                    resultsRegion.classList.remove('is-loading');
                });
            };

            syncClearButton();
            renderHistory();

            keywordInput.addEventListener('input', syncClearButton);
            keywordInput.addEventListener('focus', function() {
                isHistoryOpen = true;
                isCategoryOpen = false;
                syncCategoryVisibility();
                syncHistoryVisibility();
            });
            keywordInput.addEventListener('click', function() {
                isHistoryOpen = true;
                isCategoryOpen = false;
                syncCategoryVisibility();
                syncHistoryVisibility();
            });

            keywordClearBtn.addEventListener('click', function() {
                keywordInput.value = '';
                keywordInput.focus();
                syncClearButton();
            });

            clearHistoryBtn.addEventListener('click', function() {
                writeHistory([]);
                renderHistory();
            });

            if (categoryTrigger && categoryShell && categoryPanel && categoryInput) {
                syncCategoryUi();
                syncCategoryVisibility();

                categoryTrigger.addEventListener('click', function() {
                    isCategoryOpen = !isCategoryOpen;
                    isHistoryOpen = false;
                    syncHistoryVisibility();
                    syncCategoryVisibility();
                });

                Array.prototype.forEach.call(categoryPanel.querySelectorAll('.category-select-option'), function(optionButton) {
                    optionButton.addEventListener('click', function() {
                        categoryInput.value = optionButton.dataset.value || '';
                        syncCategoryUi();
                        isCategoryOpen = false;
                        syncCategoryVisibility();
                    });
                });
            }

            document.addEventListener('click', function(event) {
                if (filterForm.contains(event.target)) {
                    if (keywordInput.contains(event.target) || historyPanel.contains(event.target)) {
                        isCategoryOpen = false;
                        syncCategoryVisibility();
                        return;
                    }

                    if (categoryShell && categoryShell.contains(event.target)) {
                        isHistoryOpen = false;
                        syncHistoryVisibility();
                        return;
                    }
                }

                isHistoryOpen = false;
                syncHistoryVisibility();
                isCategoryOpen = false;
                syncCategoryVisibility();
            });

            document.addEventListener('keydown', function(event) {
                if (event.key !== 'Escape') {
                    return;
                }

                isHistoryOpen = false;
                syncHistoryVisibility();
                isCategoryOpen = false;
                syncCategoryVisibility();
            });

            filterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const term = keywordInput.value.trim();
                storeSearchTerm(term);
                renderHistory();
                isHistoryOpen = false;
                syncHistoryVisibility();
                isCategoryOpen = false;
                syncCategoryVisibility();
                runProductsFetch(buildProductsUrl({
                    page: 1
                }), true, true);
            });

            if (resetLink) {
                resetLink.addEventListener('click', function(event) {
                    event.preventDefault();
                    keywordInput.value = '';
                    const categoryInput = document.getElementById('category');
                    if (categoryInput) {
                        categoryInput.value = '';
                        syncCategoryUi();
                    }
                    syncClearButton();
                    isHistoryOpen = false;
                    syncHistoryVisibility();
                    isCategoryOpen = false;
                    syncCategoryVisibility();
                    runProductsFetch(filterForm.action, true, true);
                });
            }

            resultsRegion.addEventListener('click', function(event) {
                const pageLink = event.target.closest('.pagination-modern a');
                if (!pageLink) {
                    return;
                }

                event.preventDefault();
                runProductsFetch(pageLink.href, true, true);
            });

            window.addEventListener('popstate', function() {
                runProductsFetch(window.location.href, false, false);
            });
        }

        if (musicCard && !window.__nautshopMusicManaged) {
            const playerStateKey = 'nautshop_player_state_v3';
            const rawPlaylist = musicCard.dataset.playlist || '[]';
            let playlist = [];

            try {
                playlist = JSON.parse(rawPlaylist);
            } catch (error) {
                playlist = [];
            }

            if (!playlist.length) {
                return;
            }

            const playerHost = document.getElementById('storePlaylistYoutube');
            const title = document.getElementById('musicTrackTitle');
            const artist = document.getElementById('musicTrackArtist');
            const currentTimeNode = document.getElementById('musicCurrentTime');
            const durationNode = document.getElementById('musicDuration');
            const progress = document.getElementById('musicProgress');
            const volume = document.getElementById('musicVolume');
            const playBtn = document.getElementById('musicPlayBtn');
            const prevBtn = document.getElementById('musicPrevBtn');
            const nextBtn = document.getElementById('musicNextBtn');
            const trackCounter = document.getElementById('musicTrackCounter');
            const rhythmBars = Array.prototype.slice.call(document.querySelectorAll('#musicRhythmMark span'));
            const baseRhythmHeights = rhythmBars.map(function(_, index) {
                const midpoint = (rhythmBars.length - 1) / 2;
                const distance = Math.abs(index - midpoint);
                const ratio = midpoint > 0 ? 1 - (distance / midpoint) : 1;
                return Math.round(32 + Math.max(0.22, ratio) * 78);
            });
            let ytPlayer = null;
            let playerReady = false;
            let visualizerFrame = 0;
            let currentTrackIndex = 0;
            let pendingSeek = 0;
            let isSwitchingTrack = false;
            let autoplayUnlockBound = false;
            let autoplayUnlockHandler = null;

            const readPlayerState = function() {
                try {
                    const stored = window.localStorage.getItem(playerStateKey);
                    const parsed = stored ? JSON.parse(stored) : null;
                    return parsed && typeof parsed === 'object' ? parsed : null;
                } catch (error) {
                    return null;
                }
            };

            const writePlayerState = function(state) {
                window.localStorage.setItem(playerStateKey, JSON.stringify(state));
            };

            const formatTime = function(seconds) {
                if (!Number.isFinite(seconds) || seconds < 0) {
                    return '00:00';
                }

                const minutes = Math.floor(seconds / 60);
                const remainder = Math.floor(seconds % 60);
                return String(minutes).padStart(2, '0') + ':' + String(remainder).padStart(2, '0');
            };

            const setRhythmHeights = function(levels) {
                rhythmBars.forEach(function(bar, index) {
                    const nextHeight = levels[index] || baseRhythmHeights[index] || 32;
                    const scale = 0.94 + (nextHeight / 170) * 0.12;
                    bar.style.height = nextHeight + 'px';
                    bar.style.opacity = String(Math.min(1, 0.55 + nextHeight / 190));
                    bar.style.transform = 'scaleY(' + scale.toFixed(3) + ')';
                });
            };

            const resetRhythmBars = function() {
                setRhythmHeights(baseRhythmHeights);
            };

            const renderRhythmFromTime = function(relativeTime) {
                const midpoint = (rhythmBars.length - 1) / 2;
                const levels = rhythmBars.map(function(_, index) {
                    const distance = Math.abs(index - midpoint);
                    const spread = 1 - Math.min(1, distance / Math.max(1, midpoint));
                    const waveA = (Math.sin(relativeTime * 4.1 + index * 0.42) + 1) / 2;
                    const waveB = (Math.sin(relativeTime * 2.3 - index * 0.21) + 1) / 2;
                    const waveC = (Math.sin(relativeTime * 1.25 + index * 0.12) + 1) / 2;
                    const intensity = (waveA * 0.52) + (waveB * 0.28) + (waveC * 0.2);
                    return Math.round((baseRhythmHeights[index] || 32) + intensity * (28 + spread * 58));
                });

                setRhythmHeights(levels);
            };

            const renderPlayIcon = function(playing) {
                playBtn.dataset.playing = playing ? 'true' : 'false';
                playBtn.setAttribute('aria-label', playing ? (musicCard.dataset.pauseLabel || '') : (musicCard.dataset.playLabel || ''));
                playBtn.innerHTML = playing ?
                    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h3v12H8V6Zm5 0h3v12h-3V6Z"></path></svg>' :
                    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 7 8 5-8 5V7Z"></path></svg>';
            };

            const getCurrentTrack = function() {
                return playlist[currentTrackIndex];
            };

            const getRelativeTime = function() {
                if (!playerReady || !ytPlayer) {
                    return pendingSeek || 0;
                }

                const track = getCurrentTrack();
                const absoluteTime = Number(ytPlayer.getCurrentTime()) || track.start || 0;
                return Math.max(0, Math.min(track.duration || 0, absoluteTime - (track.start || 0)));
            };

            const saveState = function() {
                const currentVolume = playerReady && ytPlayer && typeof ytPlayer.getVolume === 'function' ?
                    ytPlayer.getVolume() / 100 :
                    parseFloat(volume.value || '1');

                writePlayerState({
                    trackIndex: currentTrackIndex,
                    currentTime: getRelativeTime(),
                    volume: currentVolume,
                    isPlaying: playerState.isPlaying,
                });
            };

            const syncTrackMeta = function() {
                const track = getCurrentTrack();
                title.textContent = track.title;
                artist.textContent = track.artist;
                durationNode.textContent = track.length || formatTime(track.duration || 0);
                currentTimeNode.textContent = '00:00';
                progress.value = '0';

                if (trackCounter) {
                    trackCounter.textContent = (musicCard.dataset.trackLabel || 'Track') + ' ' + (currentTrackIndex + 1) + ' / ' + playlist.length;
                }
            };

            const syncProgressUi = function() {
                const track = getCurrentTrack();
                const relativeTime = getRelativeTime();
                currentTimeNode.textContent = formatTime(relativeTime);
                durationNode.textContent = track.length || formatTime(track.duration || 0);
                progress.value = track.duration ? String((relativeTime / track.duration) * 100) : '0';
                return relativeTime;
            };

            const stopVisualizer = function() {
                if (visualizerFrame) {
                    window.cancelAnimationFrame(visualizerFrame);
                    visualizerFrame = 0;
                }

                resetRhythmBars();
            };

            const loadTrack = function(index, autoplay, seekSeconds) {
                currentTrackIndex = index;
                pendingSeek = typeof seekSeconds === 'number' && seekSeconds > 0 ? seekSeconds : 0;
                isSwitchingTrack = true;
                syncTrackMeta();

                if (!playerReady || !ytPlayer) {
                    return;
                }

                const track = getCurrentTrack();
                const videoOptions = {
                    videoId: track.videoId,
                    startSeconds: track.start,
                    endSeconds: track.end,
                    suggestedQuality: 'small',
                };

                if (autoplay) {
                    ytPlayer.loadVideoById(videoOptions);
                } else {
                    ytPlayer.cueVideoById(videoOptions);
                    renderPlayIcon(false);
                }

                saveState();
            };

            const attemptPlaybackWithUnlock = function() {
                if (!playerReady || !ytPlayer) {
                    return;
                }

                playerState.isPlaying = true;
                renderPlayIcon(true);

                const playResult = ytPlayer.playVideo();
                const looksLikePromise = playResult && typeof playResult.then === 'function';

                if (looksLikePromise) {
                    playResult.catch(function() {
                        playerState.isPlaying = false;
                        renderPlayIcon(false);
                        saveState();
                    });
                }

                saveState();
            };

            const unbindAutoplayUnlock = function() {
                if (!autoplayUnlockBound || !autoplayUnlockHandler) {
                    return;
                }

                ['pointerdown', 'keydown', 'touchstart'].forEach(function(eventName) {
                    document.removeEventListener(eventName, autoplayUnlockHandler);
                });

                autoplayUnlockBound = false;
                autoplayUnlockHandler = null;
            };

            const bindAutoplayUnlock = function() {
                if (autoplayUnlockBound || !playerState.isPlaying) {
                    return;
                }

                autoplayUnlockHandler = function() {
                    unbindAutoplayUnlock();
                    attemptPlaybackWithUnlock();
                };

                ['pointerdown', 'keydown', 'touchstart'].forEach(function(eventName) {
                    document.addEventListener(eventName, autoplayUnlockHandler, {
                        once: true,
                        passive: true,
                    });
                });

                autoplayUnlockBound = true;
            };

            const goToAdjacentTrack = function(direction) {
                const nextIndex = direction === 'prev' ?
                    (currentTrackIndex === 0 ? playlist.length - 1 : currentTrackIndex - 1) :
                    (currentTrackIndex === playlist.length - 1 ? 0 : currentTrackIndex + 1);

                loadTrack(nextIndex, true, 0);
            };

            const startVisualizer = function() {
                if (!playerReady || !ytPlayer || visualizerFrame) {
                    return;
                }

                const renderBars = function() {
                    if (!playerReady || !ytPlayer || ytPlayer.getPlayerState() !== window.YT.PlayerState.PLAYING) {
                        visualizerFrame = 0;
                        resetRhythmBars();
                        return;
                    }

                    const relativeTime = syncProgressUi();
                    renderRhythmFromTime(relativeTime);

                    if (relativeTime >= (getCurrentTrack().duration - 0.18)) {
                        visualizerFrame = 0;
                        goToAdjacentTrack('next');
                        return;
                    }

                    visualizerFrame = window.requestAnimationFrame(renderBars);
                };

                visualizerFrame = window.requestAnimationFrame(renderBars);
            };

            const loadYouTubeApi = function() {
                if (window.YT && window.YT.Player) {
                    return Promise.resolve(window.YT);
                }

                if (!window.__nautshopYouTubeApiPromise) {
                    window.__nautshopYouTubeApiPromise = new Promise(function(resolve) {
                        const previousReady = window.onYouTubeIframeAPIReady;

                        window.onYouTubeIframeAPIReady = function() {
                            if (typeof previousReady === 'function') {
                                previousReady();
                            }

                            resolve(window.YT);
                        };

                        if (!document.querySelector('script[src="https://www.youtube.com/iframe_api"]')) {
                            const script = document.createElement('script');
                            script.src = 'https://www.youtube.com/iframe_api';
                            document.head.appendChild(script);
                        }
                    });
                }

                return window.__nautshopYouTubeApiPromise;
            };

            let playerState = readPlayerState() || {
                trackIndex: 0,
                currentTime: 0,
                volume: 1,
                isPlaying: true,
            };

            if (typeof playerState.trackIndex !== 'number' || playerState.trackIndex < 0 || playerState.trackIndex >= playlist.length) {
                playerState.trackIndex = 0;
            }

            if (typeof playerState.currentTime !== 'number' || playerState.currentTime < 0) {
                playerState.currentTime = 0;
            }

            if (typeof playerState.volume !== 'number' || playerState.volume < 0 || playerState.volume > 1) {
                playerState.volume = 1;
            }

            if (typeof playerState.isPlaying !== 'boolean') {
                playerState.isPlaying = true;
            }

            // Trang sản phẩm luôn ưu tiên tự phát sau khi vào trang.
            playerState.isPlaying = true;

            currentTrackIndex = playerState.trackIndex;
            pendingSeek = playerState.currentTime;
            volume.value = String(playerState.volume);
            syncTrackMeta();
            resetRhythmBars();
            renderPlayIcon(playerState.isPlaying);

            loadYouTubeApi().then(function() {
                ytPlayer = new window.YT.Player(playerHost, {
                    width: '1',
                    height: '1',
                    videoId: getCurrentTrack().videoId,
                    playerVars: {
                        autoplay: 0,
                        controls: 0,
                        disablekb: 1,
                        fs: 0,
                        iv_load_policy: 3,
                        modestbranding: 1,
                        playsinline: 1,
                        rel: 0,
                        origin: window.location.origin,
                    },
                    events: {
                        onReady: function() {
                            playerReady = true;
                            ytPlayer.setVolume(Math.round(playerState.volume * 100));
                            loadTrack(currentTrackIndex, playerState.isPlaying, pendingSeek);
                            bindAutoplayUnlock();
                        },
                        onStateChange: function(event) {
                            const state = event.data;

                            if (state === window.YT.PlayerState.PLAYING) {
                                const track = getCurrentTrack();

                                if (pendingSeek > 0) {
                                    ytPlayer.seekTo(track.start + Math.min(pendingSeek, Math.max(0, track.duration - 0.2)), true);
                                    pendingSeek = 0;
                                }

                                isSwitchingTrack = false;
                                playerState.isPlaying = true;
                                renderPlayIcon(true);
                                startVisualizer();
                                saveState();
                                unbindAutoplayUnlock();
                                return;
                            }

                            if (state === window.YT.PlayerState.PAUSED) {
                                playerState.isPlaying = false;
                                renderPlayIcon(false);
                                syncProgressUi();
                                stopVisualizer();
                                saveState();
                                return;
                            }

                            if (state === window.YT.PlayerState.CUED) {
                                syncProgressUi();
                                stopVisualizer();

                                if (playerState.isPlaying) {
                                    renderPlayIcon(true);
                                    bindAutoplayUnlock();
                                    saveState();
                                    return;
                                }

                                renderPlayIcon(false);
                                saveState();
                                return;
                            }

                            if (state === window.YT.PlayerState.UNSTARTED && playerState.isPlaying) {
                                bindAutoplayUnlock();
                                return;
                            }

                            if (state === window.YT.PlayerState.ENDED) {
                                if (!isSwitchingTrack) {
                                    goToAdjacentTrack('next');
                                }
                            }
                        },
                        onError: function() {
                            playerState.isPlaying = false;
                            renderPlayIcon(false);
                            stopVisualizer();
                        },
                    },
                });
            }).catch(function() {
                renderPlayIcon(false);
            });

            playBtn.addEventListener('click', function() {
                if (!playerReady || !ytPlayer) {
                    return;
                }

                const state = ytPlayer.getPlayerState();
                if (state === window.YT.PlayerState.PLAYING || state === window.YT.PlayerState.BUFFERING) {
                    ytPlayer.pauseVideo();
                    playerState.isPlaying = false;
                    unbindAutoplayUnlock();
                    return;
                }

                playerState.isPlaying = true;
                unbindAutoplayUnlock();
                attemptPlaybackWithUnlock();
            });

            prevBtn.addEventListener('click', function() {
                playerState.isPlaying = true;
                goToAdjacentTrack('prev');
            });

            nextBtn.addEventListener('click', function() {
                playerState.isPlaying = true;
                goToAdjacentTrack('next');
            });

            progress.addEventListener('input', function() {
                if (!playerReady || !ytPlayer) {
                    return;
                }

                const track = getCurrentTrack();
                const relativeTime = (parseFloat(progress.value) / 100) * track.duration;
                pendingSeek = 0;
                ytPlayer.seekTo(track.start + relativeTime, true);
                currentTimeNode.textContent = formatTime(relativeTime);
                progress.value = String((relativeTime / track.duration) * 100);
                renderRhythmFromTime(relativeTime);
                saveState();
            });

            volume.addEventListener('input', function() {
                if (!playerReady || !ytPlayer) {
                    return;
                }

                ytPlayer.setVolume(Math.round(parseFloat(volume.value || '1') * 100));
                saveState();
            });

            window.addEventListener('pagehide', function() {
                unbindAutoplayUnlock();
                stopVisualizer();
                saveState();
            });
        }
    })();
</script>

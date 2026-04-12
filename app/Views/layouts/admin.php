<?php
$adminUser = auth() ?? [];
$currentLocale = current_locale();
$nextLocale = next_locale();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptBase = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

if ($scriptBase !== '/' && $scriptBase !== '' && str_starts_with($currentPath, $scriptBase)) {
    $currentPath = substr($currentPath, strlen($scriptBase));
}

if ($currentPath === '') {
    $currentPath = '/';
}

$navMain = [
    ['href' => '/admin', 'label' => t('admin.nav_dashboard'), 'icon' => 'dashboard'],
    ['href' => '/admin/products', 'label' => t('admin.nav_products'), 'icon' => 'products'],
    ['href' => '/admin/orders', 'label' => t('admin.nav_orders'), 'icon' => 'orders'],
    ['href' => '/admin/users', 'label' => t('admin.nav_users'), 'icon' => 'users'],
];

function adminIsActive(string $href, string $current): bool
{
    if ($href === '/admin') {
        return $current === '/admin';
    }

    return str_starts_with($current, $href);
}

function adminNavIcon(string $icon): string
{
    return match ($icon) {
        'dashboard' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'products' => '<rect x="2" y="3" width="20" height="5" rx="1"/><rect x="2" y="10" width="20" height="5" rx="1"/><rect x="2" y="17" width="20" height="5" rx="1"/>',
        'orders' => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/><path d="M9 12h6M9 16h4"/>',
        'users' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
        'store' => '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'logout' => '<path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
        default => '<circle cx="12" cy="12" r="10"/>',
    };
}

$adminName = (string) ($adminUser['name'] ?? t('layout.admin'));
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="<?= e($currentLocale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? t('admin.default_title')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <link rel="icon" type="image/png" href="<?= e(asset('images/logo.png')) ?>">
</head>
<body class="admin-body" data-theme="light">

<?php if ($flash = flash('success')): ?>
<div class="flash-toast-wrap">
    <div class="flash-toast success" id="flashToast" role="status" aria-live="polite">
        <span class="flash-toast-icon-badge" aria-hidden="true">✓</span>
        <div class="flash-toast-content">
            <span class="flash-toast-message"><?= e((string) $flash) ?></span>
        </div>
        <button type="button" class="flash-toast-close" id="flashToastClose" aria-label="<?= e(t('layout.flash_close')) ?>">✕</button>
        <span class="flash-toast-progress" aria-hidden="true"></span>
    </div>
</div>
<?php endif; ?>

<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <a href="<?= e(url('/admin')) ?>" class="admin-brand">
                <img src="<?= e(asset('images/logo.png')) ?>" alt="NautShop" class="admin-brand-logo-img">
                <span class="admin-brand-label">Naut Shop</span>
            </a>
            <button class="admin-sidebar-close" id="sidebarClose" aria-label="<?= e(t('admin.close_menu')) ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="admin-nav" role="navigation" aria-label="<?= e(t('admin.navigation')) ?>">
            <span class="admin-nav-section-label"><?= e(t('admin.main_menu')) ?></span>

            <?php foreach ($navMain as $item): ?>
                <?php $active = adminIsActive($item['href'], $currentPath); ?>
                <a href="<?= e(url($item['href'])) ?>"
                   class="admin-nav-item <?= $active ? 'is-active' : '' ?>"
                   <?= $active ? 'aria-current="page"' : '' ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <?= adminNavIcon($item['icon']) ?>
                    </svg>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-hamburger" id="sidebarToggle" aria-label="<?= e(t('admin.open_menu')) ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="admin-topbar-right">
                <button
                    type="button"
                    class="theme-toggle admin-theme-toggle"
                    id="adminThemeToggle"
                    aria-label="<?= e(t('layout.theme_toggle_aria')) ?>"
                    data-icon-light="<?= e(t('layout.theme_icon_light')) ?>"
                    data-label-light="<?= e(t('layout.theme_label_light')) ?>"
                    data-icon-dark="<?= e(t('layout.theme_icon_dark')) ?>"
                    data-label-dark="<?= e(t('layout.theme_label_dark')) ?>"
                >
                    <span class="theme-toggle-icon" id="adminThemeToggleIcon"><?= e(t('layout.theme_icon_light')) ?></span>
                    <span class="theme-toggle-label" id="adminThemeToggleLabel"><?= e(t('layout.theme_label_light')) ?></span>
                </button>

                <a href="<?= e(localized_url($nextLocale)) ?>" class="admin-topbar-language" aria-label="<?= e(t('layout.language_toggle_aria')) ?>">
                    <?= e(t('layout.language_toggle')) ?>
                </a>

                <div class="admin-topbar-divider" aria-hidden="true"></div>

                <a href="<?= e(url('/products')) ?>" class="admin-topbar-text-btn" aria-label="<?= e(t('admin.back_to_store')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <?= adminNavIcon('store') ?>
                    </svg>
                    <span><?= e(t('admin.back_to_store')) ?></span>
                </a>

                <a href="<?= e(url('/logout')) ?>" class="admin-topbar-text-btn admin-topbar-logout" aria-label="<?= e(t('layout.logout')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <?= adminNavIcon('logout') ?>
                    </svg>
                    <span><?= e(t('layout.logout')) ?></span>
                </a>

                <div class="admin-topbar-divider" aria-hidden="true"></div>

                <div class="admin-topbar-user" role="button" tabindex="0" aria-label="<?= e(t('admin.account')) ?>">
                    <div class="admin-topbar-avatar"><?= e($adminInitial) ?></div>
                    <div class="admin-topbar-user-info">
                        <span class="admin-topbar-name"><?= e($adminName) ?></span>
                        <span class="admin-topbar-role"><?= e(t('admin.role_administrator')) ?></span>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-content" id="adminContent">
            <?= $content ?>
        </main>
    </div>

    <div class="admin-overlay" id="adminOverlay" aria-hidden="true"></div>
</div>

<script>
(function () {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    const overlay = document.getElementById('adminOverlay');
    const flash = document.getElementById('flashToast');
    const flashX = document.getElementById('flashToastClose');
    const adminThemeToggle = document.getElementById('adminThemeToggle');
    const adminThemeToggleLabel = document.getElementById('adminThemeToggleLabel');
    const adminThemeToggleIcon = document.getElementById('adminThemeToggleIcon');

    const applyThemeLabel = function (theme) {
        if (!adminThemeToggle || !adminThemeToggleLabel || !adminThemeToggleIcon) {
            return;
        }

        if (theme === 'dark') {
            adminThemeToggleLabel.textContent = adminThemeToggle.dataset.labelDark || '';
            adminThemeToggleIcon.textContent = adminThemeToggle.dataset.iconDark || '';
        } else {
            adminThemeToggleLabel.textContent = adminThemeToggle.dataset.labelLight || '';
            adminThemeToggleIcon.textContent = adminThemeToggle.dataset.iconLight || '';
        }
    };

    const savedTheme = localStorage.getItem('nautshop-theme');
    if (savedTheme === 'dark') {
        body.setAttribute('data-theme', 'dark');
    }
    applyThemeLabel(body.getAttribute('data-theme') || 'light');

    function openSidebar() {
        sidebar && sidebar.classList.add('is-open');
        overlay && overlay.classList.add('is-visible');
        overlay && overlay.removeAttribute('aria-hidden');
    }

    function closeSidebar() {
        sidebar && sidebar.classList.remove('is-open');
        overlay && overlay.classList.remove('is-visible');
        overlay && overlay.setAttribute('aria-hidden', 'true');
    }

    toggle && toggle.addEventListener('click', openSidebar);
    closeBtn && closeBtn.addEventListener('click', closeSidebar);
    overlay && overlay.addEventListener('click', closeSidebar);

    if (adminThemeToggle) {
        adminThemeToggle.addEventListener('click', function () {
            const current = body.getAttribute('data-theme') || 'light';
            const next = current === 'light' ? 'dark' : 'light';

            body.setAttribute('data-theme', next);
            localStorage.setItem('nautshop-theme', next);
            applyThemeLabel(next);
        });
    }

    document.querySelectorAll('[data-admin-search]').forEach(function (searchShell) {
        const form = searchShell.closest('form');
        const input = searchShell.querySelector('input');
        const clearButton = searchShell.querySelector('.admin-filter-clear');
        const historyPanel = searchShell.querySelector('.admin-filter-search-panel');
        const historyList = searchShell.querySelector('.admin-filter-search-list');
        const clearHistoryButton = searchShell.querySelector('.admin-filter-search-trash');
        const storageKey = searchShell.dataset.historyKey || 'admin_search_history';
        let isHistoryOpen = false;

        if (!input || !clearButton) {
            return;
        }

        const readHistory = function () {
            try {
                const stored = window.localStorage.getItem(storageKey);
                const parsed = stored ? JSON.parse(stored) : [];
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        };

        const writeHistory = function (items) {
            window.localStorage.setItem(storageKey, JSON.stringify(items));
        };

        const storeSearchTerm = function (term) {
            if (term === '') {
                return;
            }

            const nextItems = readHistory().filter(function (item) {
                return String(item).toLowerCase() !== term.toLowerCase();
            });

            nextItems.unshift(term);
            writeHistory(nextItems.slice(0, 8));
        };

        const syncSearchUi = function () {
            clearButton.hidden = input.value.trim() === '';
        };

        const syncHistoryVisibility = function () {
            if (!historyPanel || !historyList) {
                return;
            }

            historyPanel.hidden = !(isHistoryOpen && historyList.childElementCount > 0);
        };

        const submitSearch = function () {
            if (form) {
                form.submit();
            }
        };

        const renderHistory = function () {
            if (!historyList) {
                return;
            }

            historyList.innerHTML = '';

            readHistory().forEach(function (term) {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'admin-filter-search-chip';
                chip.textContent = term;
                chip.addEventListener('click', function () {
                    input.value = term;
                    syncSearchUi();
                    isHistoryOpen = false;
                    syncHistoryVisibility();
                    submitSearch();
                });
                historyList.appendChild(chip);
            });

            syncHistoryVisibility();
        };

        clearButton.addEventListener('click', function () {
            input.value = '';
            input.focus();
            syncSearchUi();
        });

        input.addEventListener('input', syncSearchUi);
        input.addEventListener('focus', function () {
            isHistoryOpen = true;
            renderHistory();
        });
        input.addEventListener('click', function () {
            isHistoryOpen = true;
            renderHistory();
        });

        if (clearHistoryButton) {
            clearHistoryButton.addEventListener('click', function () {
                writeHistory([]);
                renderHistory();
            });
        }

        if (form) {
            form.addEventListener('submit', function () {
                storeSearchTerm(input.value.trim());
            });
        }

        document.addEventListener('click', function (event) {
            if (!searchShell.contains(event.target)) {
                isHistoryOpen = false;
                syncHistoryVisibility();
            }
        });

        syncSearchUi();
        renderHistory();
    });

    document.querySelectorAll('[data-admin-select]').forEach(function (selectShell) {
        const nativeSelect = selectShell.querySelector('select');
        const trigger = selectShell.querySelector('.admin-filter-select-trigger');
        const label = selectShell.querySelector('.admin-filter-select-label');
        const panel = selectShell.querySelector('.admin-filter-select-panel');
        const options = panel ? panel.querySelectorAll('.admin-filter-select-option') : [];

        if (!nativeSelect || !trigger || !label || !panel) {
            return;
        }

        const syncSelectUi = function () {
            const selectedOption = nativeSelect.options[nativeSelect.selectedIndex];
            label.textContent = selectedOption ? selectedOption.textContent : '';

            options.forEach(function (optionButton) {
                const isSelected = optionButton.dataset.value === nativeSelect.value;
                optionButton.classList.toggle('is-selected', isSelected);
                optionButton.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        };

        const closeSelect = function () {
            selectShell.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            panel.hidden = true;
        };

        const openSelect = function () {
            document.querySelectorAll('[data-admin-select].is-open').forEach(function (openShell) {
                if (openShell !== selectShell) {
                    openShell.classList.remove('is-open');
                    const openTrigger = openShell.querySelector('.admin-filter-select-trigger');
                    const openPanel = openShell.querySelector('.admin-filter-select-panel');
                    if (openTrigger) {
                        openTrigger.setAttribute('aria-expanded', 'false');
                    }
                    if (openPanel) {
                        openPanel.hidden = true;
                    }
                }
            });

            selectShell.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            panel.hidden = false;
        };

        trigger.addEventListener('click', function () {
            if (selectShell.classList.contains('is-open')) {
                closeSelect();
                return;
            }

            openSelect();
        });

        options.forEach(function (optionButton) {
            optionButton.addEventListener('click', function () {
                nativeSelect.value = optionButton.dataset.value || '';
                syncSelectUi();
                closeSelect();
            });
        });

        document.addEventListener('click', function (event) {
            if (!selectShell.contains(event.target)) {
                closeSelect();
            }
        });

        syncSelectUi();
        closeSelect();
    });

    if (flash) {
        const hide = function () {
            flash.classList.add('is-hiding');
            setTimeout(function () {
                if (flash.parentNode) {
                    flash.parentNode.removeChild(flash);
                }
            }, 300);
        };

        setTimeout(hide, 6500);
        flashX && flashX.addEventListener('click', hide);
    }
})();
</script>

</body>
</html>

<?php

declare(strict_types=1);

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = array_pad(explode('=', $line, 2), 2, '');

            $name = trim($name);
            $value = trim($value);

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv("$name=$value");
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }
}

if (!function_exists('config')) {
    function config(string $file): array
    {
        $path = BASE_PATH . '/config/' . $file . '.php';

        if (!file_exists($path)) {
            return [];
        }

        return require $path;
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException("View [$view] not found.");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url(): string
    {
        return rtrim(env('APP_URL', ''), '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return base_url() . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return base_url() . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('session')) {
    function session(string $key, mixed $default = null): mixed
    {
        return \Core\Session::get($key, $default);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return \Core\Session::old($key, $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $default = null): mixed
    {
        return \Core\Session::getFlash($key, $default);
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return \Core\Session::get('auth_user');
    }
}

if (!function_exists('is_auth')) {
    function is_auth(): bool
    {
        return \Core\Session::has('auth_user');
    }
}

if (!function_exists('is_https')) {
    function is_https(): bool
    {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        );
    }
}

if (!function_exists('set_auth_cookie')) {
    function set_auth_cookie(string $name, string $value, int $expires): void
    {
        setcookie($name, $value, [
            'expires' => $expires,
            'path' => '/',
            'secure' => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $_COOKIE[$name] = $value;
    }
}

if (!function_exists('delete_auth_cookie')) {
    function delete_auth_cookie(string $name): void
    {
        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        unset($_COOKIE[$name]);
    }
}


if (!function_exists('cart_count')) {
    function cart_count(): int
    {
        $cart = \Core\Session::get('cart', []);
        return (int) array_sum(array_column($cart, 'quantity'));
    }
}

if (!function_exists('app_home_url')) {
    function app_home_url(): string
    {
        if (!is_auth()) {
            return url('/login');
        }

        $user = auth();

        if (is_admin()) {
            return url('/admin');
        }

        if (empty($user['phone'])) {
            return url('/complete-profile');
        }

        return url('/products');
    }
}

if (!function_exists('has_completed_profile')) {
    function has_completed_profile(): bool
    {
        if (!is_auth()) {
            return false;
        }

        $user = auth();
        return !empty($user['phone']);
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        if (!is_auth()) {
            return false;
        }

        $user = auth();
        return ($user['role'] ?? 'user') === 'admin';
    }
}

if (!function_exists('supported_locales')) {
    function supported_locales(): array
    {
        return ['vi', 'en'];
    }
}

if (!function_exists('default_locale')) {
    function default_locale(): string
    {
        return 'vi';
    }
}

if (!function_exists('normalize_locale')) {
    function normalize_locale(?string $locale): string
    {
        $locale = strtolower(trim((string) $locale));
        return in_array($locale, supported_locales(), true) ? $locale : default_locale();
    }
}

if (!function_exists('boot_locale')) {
    function boot_locale(): void
    {
        $queryLocale = $_GET['lang'] ?? null;

        if ($queryLocale !== null && in_array(strtolower((string) $queryLocale), supported_locales(), true)) {
            $locale = normalize_locale((string) $queryLocale);
            \Core\Session::set('app_locale', $locale);
            setcookie('naut_shop_locale', $locale, [
                'expires' => time() + 31536000,
                'path' => '/',
                'secure' => is_https(),
                'samesite' => 'Lax',
            ]);
            $_COOKIE['naut_shop_locale'] = $locale;
            return;
        }

        if (\Core\Session::has('app_locale')) {
            return;
        }

        $cookieLocale = $_COOKIE['naut_shop_locale'] ?? default_locale();
        \Core\Session::set('app_locale', normalize_locale((string) $cookieLocale));
    }
}

if (!function_exists('current_locale')) {
    function current_locale(): string
    {
        return normalize_locale((string) \Core\Session::get('app_locale', default_locale()));
    }
}

if (!function_exists('next_locale')) {
    function next_locale(): string
    {
        return current_locale() === 'vi' ? 'en' : 'vi';
    }
}

if (!function_exists('t')) {
    function t(string $key, array $replace = []): string
    {
        static $translations = null;

        if ($translations === null) {
            $translations = config('translations');
        }

        $locale = current_locale();
        $line = $translations[$locale][$key] ?? $translations[default_locale()][$key] ?? $key;

        foreach ($replace as $name => $value) {
            $line = str_replace(':' . $name, (string) $value, $line);
        }

        return $line;
    }
}

if (!function_exists('localized_url')) {
    function localized_url(?string $locale = null, ?string $uri = null): string
    {
        $targetLocale = normalize_locale($locale ?? current_locale());
        $requestUri = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

        parse_str((string) parse_url($requestUri, PHP_URL_QUERY), $query);
        $query['lang'] = $targetLocale;

        $queryString = http_build_query($query);

        return $path . ($queryString !== '' ? '?' . $queryString : '');
    }
}

if (!function_exists('store_playlist')) {
    function store_playlist(): array
    {
        return [
            [
                'title' => 'Soft Opening',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 0,
                'end' => 185,
                'duration' => 185,
                'length' => '03:05',
            ],
            [
                'title' => 'Window Light',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 185,
                'end' => 440,
                'duration' => 255,
                'length' => '04:15',
            ],
            [
                'title' => 'After Noon Stroll',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 440,
                'end' => 629,
                'duration' => 189,
                'length' => '03:09',
            ],
            [
                'title' => 'Quiet Avenue',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 629,
                'end' => 858,
                'duration' => 229,
                'length' => '03:49',
            ],
            [
                'title' => 'Easy Fitting Room',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 858,
                'end' => 1104,
                'duration' => 246,
                'length' => '04:06',
            ],
            [
                'title' => 'Late Checkout',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 1104,
                'end' => 1278,
                'duration' => 174,
                'length' => '02:54',
            ],
            [
                'title' => 'City Fabric',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 1278,
                'end' => 1524,
                'duration' => 246,
                'length' => '04:06',
            ],
            [
                'title' => 'Warm Receipt',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 1524,
                'end' => 1722,
                'duration' => 198,
                'length' => '03:18',
            ],
            [
                'title' => 'Monochrome Steps',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 1722,
                'end' => 1963,
                'duration' => 241,
                'length' => '04:01',
            ],
            [
                'title' => 'Weekend Shelf',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 1963,
                'end' => 2193,
                'duration' => 230,
                'length' => '03:50',
            ],
            [
                'title' => 'Folded Cotton',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 2193,
                'end' => 2409,
                'duration' => 216,
                'length' => '03:36',
            ],
            [
                'title' => 'Mirror Check',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 2409,
                'end' => 2648,
                'duration' => 239,
                'length' => '03:59',
            ],
            [
                'title' => 'Slow Escalator',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 2648,
                'end' => 2807,
                'duration' => 159,
                'length' => '02:39',
            ],
            [
                'title' => 'Closing Time Glow',
                'artist' => 'Naut Lounge',
                'videoId' => 'gm3_TZub0Mk',
                'start' => 2807,
                'end' => 3011,
                'duration' => 204,
                'length' => '03:24',
            ],
        ];
    }
}

if (!function_exists('format_price')) {
    function format_price(float|int $amount): string
    {
        $locale = current_locale();

        if ($locale === 'vi') {
            // Vi: 149.000 ₫
            return number_format($amount, 0, ',', '.') . ' ₫';
        }

        // En: quy đổi VND → USD rồi format $9.99
        $rate = defined('VND_TO_USD_RATE') ? (int) VND_TO_USD_RATE : 25000;
        $usd  = $amount / $rate;

        // Làm tròn đẹp: dưới $10 giữ 2 decimal, trên $10 giữ 1 decimal
        $decimals = $usd < 10 ? 2 : 1;

        return '$' . number_format($usd, $decimals, '.', ',');
    }
}

if (!function_exists('fallback_category_translation')) {
    function fallback_category_translation(string $category): string
    {
        return match (mb_strtolower(trim($category))) {
            'áo thun' => 'T-Shirts',
            'áo sơ mi' => 'Shirts',
            'quần jean' => 'Jeans',
            'giày' => 'Shoes',
            'phụ kiện' => 'Accessories',
            'váy' => 'Dresses',
            'áo hoodie' => 'Hoodies',
            'quần short' => 'Shorts',
            'áo polo' => 'Polos',
            'dép' => 'Sandals',
            default => $category,
        };
    }
}

if (!function_exists('localized_product_field')) {
    function localized_product_field(array $product, string $field): string
    {
        $locale = current_locale();
        $defaultValue = trim((string) ($product[$field] ?? ''));

        if ($locale !== 'en') {
            return $defaultValue;
        }

        $localizedValue = trim((string) ($product[$field . '_en'] ?? ''));

        if ($field === 'category' && $localizedValue === '') {
            $fallback = fallback_category_translation($defaultValue);

            if ($fallback !== $defaultValue) {
                return $fallback;
            }
        }

        if ($localizedValue !== '') {
            return $localizedValue;
        }

        try {
            return (new \App\Services\TranslationService())->translate($defaultValue, 'vi', 'en');
        } catch (\Throwable $e) {
            return $defaultValue;
        }
    }
}

if (!function_exists('localize_product')) {
    function localize_product(array $product): array
    {
        foreach (['name', 'description', 'category'] as $field) {
            $product[$field] = localized_product_field($product, $field);
        }

        return $product;
    }
}

if (!function_exists('localized_order_item_field')) {
    function localized_order_item_field(array $item, string $field): string
    {
        $locale = current_locale();
        $baseMap = [
            'product_name' => 'product_name',
            'product_category' => 'product_category',
        ];

        $baseKey = $baseMap[$field] ?? $field;
        $defaultValue = trim((string) ($item[$baseKey] ?? ''));

        if ($locale !== 'en' || $defaultValue === '') {
            return $defaultValue;
        }

        if ($field === 'product_category') {
            $fallback = fallback_category_translation($defaultValue);

            if ($fallback !== $defaultValue) {
                return $fallback;
            }
        }

        try {
            return (new \App\Services\TranslationService())->translate($defaultValue, 'vi', 'en');
        } catch (\Throwable $e) {
            return $defaultValue;
        }
    }
}

if (!function_exists('localize_order_item')) {
    function localize_order_item(array $item): array
    {
        $item['product_name'] = localized_order_item_field($item, 'product_name');
        $item['product_category'] = localized_order_item_field($item, 'product_category');

        return $item;
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\JwtAuthMiddleware;
use App\Middleware\ProfileCompletedMiddleware;
use App\Models\Product;
use App\Services\CacheService;
use App\Services\CartService;
use App\Services\TranslationService;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class ProductController extends Controller
{
    protected const PRODUCTS_PER_PAGE = 16;
    protected const PRODUCT_IMAGE_DIR = 'images/products';
    protected const PRODUCT_PLACEHOLDER = 'images/image-placeholder.png';

    protected Product $products;
    protected CacheService $cache;
    protected CartService $cart;
    protected TranslationService $translator;

    public function __construct()
    {
        $this->products = new Product();
        $this->cache = new CacheService();
        $this->cart = new CartService();
        $this->translator = new TranslationService();
    }

    protected function guard(): void
    {
        JwtAuthMiddleware::handle();
        ProfileCompletedMiddleware::handle();
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard();

        $page = max(1, (int) $request->input('page', 1));

        $filters = [
            'keyword' => trim((string) $request->input('keyword', '')),
            'category' => trim((string) $request->input('category', '')),
        ];

        if (current_locale() === 'en' && $filters['keyword'] !== '') {
            $translatedKeyword = $this->translator->translate($filters['keyword'], 'en', 'vi');

            if ($translatedKeyword !== '' && mb_strtolower($translatedKeyword) !== mb_strtolower($filters['keyword'])) {
                $filters['keyword_alt'] = $translatedKeyword;
            }
        }

        $cacheKey = 'products_' . md5(json_encode([
            'page' => $page,
            'filters' => $filters,
            'perPage' => self::PRODUCTS_PER_PAGE,
            'locale' => current_locale(),
        ]));

        $cachedData = $this->cache->get($cacheKey);

        if ($cachedData !== null) {
            $result = $cachedData;
            $cacheStatus = 'Cache hit';
        } else {
            $result = $this->products->paginate($filters, $page, self::PRODUCTS_PER_PAGE);

            $result['data'] = array_map(
                fn(array $product): array => $this->prepareProductForView($product),
                $result['data']
            );

            $this->cache->put($cacheKey, $result, 120);
            $cacheStatus = 'Cache miss';
        }

        if ($cachedData !== null) {
            $result['data'] = array_map(
                fn(array $product): array => $this->prepareProductForView($product),
                $result['data']
            );
        }

        $this->view('products/index', [
            'title' => t('title.products'),
            'products' => $result['data'],
            'categories' => $this->products->categories(),
            'filters' => $filters,
            'pagination' => [
                'page' => $result['page'],
                'perPage' => $result['perPage'],
                'total' => $result['total'],
                'lastPage' => $result['lastPage'],
            ],
            'cacheStatus' => $cacheStatus,
            'cartCount' => $this->cart->count(),
        ]);
    }

    public function show(Request $request, Response $response): void
    {
        $this->guard();

        $productId = (int) $request->input('id', 0);
        $product = $this->products->find($productId);

        if (!$product) {
            Session::flash('success', t('flash.product.not_found'));
            $this->redirect('/products');
        }

        $rawCategory = (string) ($product['category'] ?? '');
        $product = $this->prepareProductForView($product);

        $gallery = $this->buildGallery($product);
        $reviews = $this->mockReviews((int) $product['id'], (string) $product['name']);
        $relatedProducts = array_map(
            fn(array $related): array => $this->prepareProductForView($related),
            $this->products->relatedByCategory($rawCategory, (int) $product['id'], 4)
        );

        $this->view('products/show', [
            'title' => (string) $product['name'],
            'product' => $product,
            'gallery' => $gallery,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
            'cartCount' => $this->cart->count(),
        ]);
    }

    public function clearCache(Request $request, Response $response): void
    {
        $this->guard();

        $this->cache->flush();
        Session::flash('success', t('flash.product.cache_cleared'));
        $this->redirect('/products');
    }

    protected function hydrateProductImages(array $product): array
    {
        $imageName = trim((string) ($product['image'] ?? ''));

        $product['image_url'] = $this->resolveProductImageUrl($imageName);
        $product['image_alt'] = trim((string) ($product['name'] ?? 'Product image'));

        return $product;
    }

    protected function prepareProductForView(array $product): array
    {
        return $this->hydrateProductImages(localize_product($product));
    }

    protected function resolveProductImageUrl(string $imageName): string
    {
        if ($imageName !== '') {
            $relativeFile = self::PRODUCT_IMAGE_DIR . '/' . ltrim($imageName, '/');
            $absoluteFile = BASE_PATH . '/public/assets/' . $relativeFile;

            if (is_file($absoluteFile)) {
                return asset($relativeFile);
            }
        }

        return asset(self::PRODUCT_PLACEHOLDER);
    }

    protected function buildGallery(array $product): array
    {
        $galleryImageUrl = (string) ($product['image_url'] ?? asset(self::PRODUCT_PLACEHOLDER));

        return [
            ['label' => 'Mặt trước', 'image' => $galleryImageUrl],
            ['label' => 'Góc nghiêng', 'image' => $galleryImageUrl],
            ['label' => 'Cận cảnh chi tiết', 'image' => $galleryImageUrl],
            ['label' => 'Ảnh khi lên dáng', 'image' => $galleryImageUrl],
        ];
    }

    protected function mockReviews(int $productId, string $productName): array
    {
        $seed = [
            [
                'name' => 'Ngọc Anh',
                'rating' => 5,
                'title' => 'Form đẹp, mặc lên gọn dáng',
                'content' => 'Chất vải ổn, lên dáng khá vừa người và dễ phối với đồ có sẵn trong tủ. Ảnh sản phẩm và màu thực tế khá khớp nhau.',
                'date' => '2026-04-03',
            ],
            [
                'name' => 'Quốc Minh',
                'rating' => 4,
                'title' => 'Giá hợp lý, đóng gói cẩn thận',
                'content' => 'Giao hàng đúng hẹn, sản phẩm chỉn chu và mức giá dễ mua. Nếu có thêm nhiều lựa chọn màu thì sẽ tiện hơn nữa.',
                'date' => '2026-03-27',
            ],
            [
                'name' => 'Thanh Vy',
                'rating' => 5,
                'title' => 'Mô tả rõ ràng, dễ quyết định',
                'content' => 'Mình thích cách Naut Shop ghi thông tin sản phẩm khá gọn và đủ ý. Riêng mẫu ' . $productName . ' thì đúng kiểu đơn giản, dễ mặc hằng ngày.',
                'date' => '2026-03-21',
            ],
        ];

        if ($productId % 2 === 0) {
            $seed[1]['rating'] = 5;
            $seed[1]['title'] = 'Hoàn thiện tốt hơn mong đợi';
        }

        return $seed;
    }
}

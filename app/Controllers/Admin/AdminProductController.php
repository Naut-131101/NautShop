<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Middleware\AdminMiddleware;
use App\Models\Product;
use App\Services\CacheService;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class AdminProductController extends Controller
{
    protected const IMAGE_DIR = 'images/products';
    protected const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    protected const MAX_FILE_SIZE = 2 * 1024 * 1024;

    protected Product $products;
    protected CacheService $cache;

    public function __construct()
    {
        $this->products = new Product();
        $this->cache = new CacheService();
    }

    protected function guard(): void
    {
        AdminMiddleware::handle();
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard();

        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'keyword' => trim((string) $request->input('keyword', '')),
            'category' => trim((string) $request->input('category', '')),
        ];

        $result = $this->products->adminPaginate($filters, $page, 20);

        $this->view('admin/products/index', [
            'title' => t('admin.products_page_title'),
            'products' => $result['data'],
            'categories' => $this->products->categories(),
            'filters' => $filters,
            'pagination' => [
                'page' => $result['page'],
                'perPage' => $result['perPage'],
                'total' => $result['total'],
                'lastPage' => $result['lastPage'],
            ],
        ], 'admin');
    }

    public function create(Request $request, Response $response): void
    {
        $this->guard();

        $this->view('admin/products/form', [
            'title' => t('admin.add_product_page_title'),
            'product' => null,
            'categories' => $this->products->categories(),
            'errors' => [],
        ], 'admin');
    }

    public function store(Request $request, Response $response): void
    {
        $this->guard();

        $data = $this->extractFormData($request);
        $errors = $this->validateFormData($data);

        if (!empty($errors)) {
            $this->view('admin/products/form', [
                'title' => t('admin.add_product_page_title'),
                'product' => null,
                'categories' => $this->products->categories(),
                'errors' => $errors,
                'old' => $data,
            ], 'admin');
            return;
        }

        $imageFilename = $this->handleImageUpload();

        if ($imageFilename === false) {
            $this->view('admin/products/form', [
                'title' => t('admin.add_product_page_title'),
                'product' => null,
                'categories' => $this->products->categories(),
                'errors' => ['image' => t('admin.image_invalid')],
                'old' => $data,
            ], 'admin');
            return;
        }

        $data['image'] = $imageFilename;
        $this->products->create($data);
        $this->cache->flush();

        Session::flash('success', t('admin.product_added_success'));
        $this->redirect('/admin/products');
    }

    public function edit(Request $request, Response $response): void
    {
        $this->guard();

        $product = $this->findOr404((int) $request->input('id', 0));

        $this->view('admin/products/form', [
            'title' => t('admin.edit_product_page_title'),
            'product' => $product,
            'categories' => $this->products->categories(),
            'errors' => [],
            'old' => [],
        ], 'admin');
    }

    public function update(Request $request, Response $response): void
    {
        $this->guard();

        $id = (int) $request->input('id', 0);
        $product = $this->findOr404($id);

        $data = $this->extractFormData($request);
        $errors = $this->validateFormData($data);

        if (!empty($errors)) {
            $this->view('admin/products/form', [
                'title' => t('admin.edit_product_page_title'),
                'product' => $product,
                'categories' => $this->products->categories(),
                'errors' => $errors,
                'old' => $data,
            ], 'admin');
            return;
        }

        $imageFilename = $this->handleImageUpload();

        if ($imageFilename === false) {
            $this->view('admin/products/form', [
                'title' => t('admin.edit_product_page_title'),
                'product' => $product,
                'categories' => $this->products->categories(),
                'errors' => ['image' => t('admin.image_invalid')],
                'old' => $data,
            ], 'admin');
            return;
        }

        $data['image'] = $imageFilename ?? (string) ($product['image'] ?? '');

        $this->products->update($id, $data);
        $this->cache->flush();

        Session::flash('success', t('admin.product_updated_success'));
        $this->redirect('/admin/products');
    }

    public function destroy(Request $request, Response $response): void
    {
        $this->guard();

        $id = (int) $request->input('id', 0);
        $this->findOr404($id);
        $this->products->delete($id);
        $this->cache->flush();

        Session::flash('success', t('admin.product_deleted_success'));
        $this->redirect('/admin/products');
    }

    protected function findOr404(int $id): array
    {
        $product = $this->products->find($id);

        if (!$product) {
            Session::flash('success', t('admin.product_not_found'));
            $this->redirect('/admin/products');
        }

        return $product;
    }

    protected function extractFormData(Request $request): array
    {
        return [
            'name' => trim((string) $request->input('name', '')),
            'name_en' => trim((string) $request->input('name_en', '')),
            'description' => trim((string) $request->input('description', '')),
            'description_en' => trim((string) $request->input('description_en', '')),
            'price' => (float) $request->input('price', 0),
            'category' => trim((string) $request->input('category', '')),
            'category_en' => trim((string) $request->input('category_en', '')),
            'quantity' => (int) $request->input('quantity', 0),
        ];
    }

    protected function validateFormData(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = t('admin.validation_product_name_required');
        }

        if ($data['price'] <= 0) {
            $errors['price'] = t('admin.validation_price_gt_zero');
        }

        if ($data['category'] === '') {
            $errors['category'] = t('admin.validation_category_required');
        }

        if ($data['quantity'] < 0) {
            $errors['quantity'] = t('admin.validation_quantity_non_negative');
        }

        return $errors;
    }

    protected function handleImageUpload(): string|false|null
    {
        $file = $_FILES['image'] ?? null;

        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return false;
        }

        $mime = mime_content_type($file['tmp_name']);

        if (!in_array($mime, self::ALLOWED_TYPES, true)) {
            return false;
        }

        $ext = match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $filename = uniqid('prod_', true) . '.' . $ext;
        $uploadDir = BASE_PATH . '/public/assets/' . self::IMAGE_DIR;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            return false;
        }

        return $filename;
    }
}

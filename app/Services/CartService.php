<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Core\Session;

class CartService
{
    protected string $sessionKey = 'cart';
    protected string $voucherSessionKey = 'cart_voucher_code';
    protected Product $products;
    protected const VOUCHERS = [
        'WELCOME50' => [
            'type' => 'fixed',
            'value' => 50000.0,
        ],
        'NAUT10' => [
            'type' => 'percent',
            'value' => 10.0,
        ],
        'FREESHIP30' => [
            'type' => 'fixed',
            'value' => 30000.0,
        ],
        'NAUTVIP15' => [
            'type' => 'percent',
            'value' => 15.0,
        ],
        'SAVE80' => [
            'type' => 'fixed',
            'value' => 80000.0,
        ],
    ];

    public function __construct()
    {
        $this->products = new Product();
    }

    public function all(): array
    {
        return Session::get($this->sessionKey, []);
    }

    public function count(): int
    {
        return array_sum(array_column($this->all(), 'quantity'));
    }

    public function add(int $productId, int $quantity = 1): bool
    {
        $product = $this->products->find($productId);

        if (!$product) {
            return false;
        }

        $cart = $this->all();
        $quantity = max(1, $quantity);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id' => (int) $product['id'],
                'name' => $product['name'],
                'name_en' => $product['name_en'] ?? null,
                'price' => (float) $product['price'],
                'image' => $product['image'],
                'category' => $product['category'],
                'category_en' => $product['category_en'] ?? null,
                'quantity' => $quantity,
            ];
        }

        Session::set($this->sessionKey, $cart);

        return true;
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->all();

        if (!isset($cart[$productId])) {
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $quantity;
        }

        Session::set($this->sessionKey, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->all();
        unset($cart[$productId]);
        Session::set($this->sessionKey, $cart);
    }

    public function clear(): void
    {
        Session::remove($this->sessionKey);
        Session::remove($this->voucherSessionKey);
    }

    public function applyVoucher(string $code): bool
    {
        $normalizedCode = strtoupper(trim($code));

        if ($normalizedCode === '' || !isset(self::VOUCHERS[$normalizedCode])) {
            return false;
        }

        Session::set($this->voucherSessionKey, $normalizedCode);
        return true;
    }

    public function removeVoucher(): void
    {
        Session::remove($this->voucherSessionKey);
    }

    public function appliedVoucher(): ?array
    {
        $code = strtoupper((string) Session::get($this->voucherSessionKey, ''));

        if ($code === '' || !isset(self::VOUCHERS[$code])) {
            return null;
        }

        return [
            'code' => $code,
            'type' => (string) (self::VOUCHERS[$code]['type'] ?? ''),
            'value' => (float) (self::VOUCHERS[$code]['value'] ?? 0),
        ];
    }

    public function availableVouchers(): array
    {
        $items = [];

        foreach (self::VOUCHERS as $code => $meta) {
            $type = (string) ($meta['type'] ?? '');
            $value = (float) ($meta['value'] ?? 0);
            $label = $type === 'percent'
                ? ('-' . rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%')
                : ('-' . format_price($value));
            $condition = $type === 'percent'
                ? 'Tối thiểu giao dịch từ 300.000 đ'
                : 'Áp dụng cho đơn từ 500.000 đ';
            $bank = match ((string) $code) {
                'WELCOME50', 'FREESHIP30' => 'OCB',
                'NAUT10' => 'MB',
                default => 'NAUT',
            };

            $items[] = [
                'code' => (string) $code,
                'label' => $label,
                'title' => 'Giảm ngay ' . $label,
                'condition' => $condition,
                'bank' => $bank,
            ];
        }

        return $items;
    }

    public function items(): array
    {
        return array_map(
            fn (array $item): array => localize_product($item),
            $this->baseItems()
        );
    }

    public function baseItems(): array
    {
        $items = [];

        foreach ($this->all() as $item) {
            $item['subtotal'] = ((float) $item['price']) * ((int) $item['quantity']);
            $items[] = $item;
        }

        return $items;
    }

    public function totals(): array
    {
        $items = $this->items();
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $shipping = $subtotal > 0 ? 30000.0 : 0.0;
        $baseDiscount = $subtotal >= 1000000 ? 50000.0 : 0.0;
        $voucher = $this->appliedVoucher();
        $voucherDiscount = 0.0;

        if ($voucher && $subtotal > 0) {
            if ($voucher['type'] === 'percent') {
                $voucherDiscount = $subtotal * ((float) $voucher['value'] / 100);
            } else {
                $voucherDiscount = (float) $voucher['value'];
            }
        }

        $discount = $baseDiscount + $voucherDiscount;
        $maxDiscount = max(0, $subtotal + $shipping);
        $discount = min($discount, $maxDiscount);
        $total = max(0, $subtotal + $shipping - $discount);

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'baseDiscount' => $baseDiscount,
            'voucherDiscount' => $voucherDiscount,
            'voucher' => $voucher,
            'total' => $total,
            'count' => $this->count(),
        ];
    }
}

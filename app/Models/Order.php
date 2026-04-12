<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use PDO;
use Throwable;

class Order extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PAID            = 'paid';
    public const STATUS_PROCESSING      = 'processing';
    public const STATUS_SHIPPED         = 'shipped';
    public const STATUS_DELIVERED       = 'delivered';
    public const STATUS_CANCELLED       = 'cancelled';

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID    = 'paid';
    public const PAYMENT_STATUS_FAILED  = 'failed';

    public const ADMIN_STATUSES = [
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_PAID,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    public const METHOD_VISA = 'visa';
    public const METHOD_VIETQR = 'vietqr';

    public function createPending(int $userId, array $customer, array $items, array $totals): array
    {
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();

        try {
            $orderCode = $this->generateOrderCode($pdo);

            $stmt = $pdo->prepare(
                'INSERT INTO orders (
                    order_code,
                    user_id,
                    customer_name,
                    customer_phone,
                    customer_email,
                    shipping_address,
                    payment_method,
                    note,
                    order_status,
                    payment_status,
                    subtotal_amount,
                    shipping_amount,
                    discount_amount,
                    total_amount,
                    placed_at
                ) VALUES (
                    :order_code,
                    :user_id,
                    :customer_name,
                    :customer_phone,
                    :customer_email,
                    :shipping_address,
                    :payment_method,
                    :note,
                    :order_status,
                    :payment_status,
                    :subtotal_amount,
                    :shipping_amount,
                    :discount_amount,
                    :total_amount,
                    NOW()
                )'
            );

            $stmt->execute([
                'order_code' => $orderCode,
                'user_id' => $userId,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_email' => $customer['email'],
                'shipping_address' => $customer['address'],
                'payment_method' => $customer['payment_method'],
                'note' => $customer['note'],
                'order_status' => self::STATUS_PENDING_PAYMENT,
                'payment_status' => self::PAYMENT_STATUS_PENDING,
                'subtotal_amount' => $totals['subtotal'],
                'shipping_amount' => $totals['shipping'],
                'discount_amount' => $totals['discount'],
                'total_amount' => $totals['total'],
            ]);

            $orderId = (int) $pdo->lastInsertId();
            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    product_category,
                    product_image,
                    unit_price,
                    quantity,
                    line_total
                ) VALUES (
                    :order_id,
                    :product_id,
                    :product_name,
                    :product_category,
                    :product_image,
                    :unit_price,
                    :quantity,
                    :line_total
                )'
            );

            foreach ($items as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'product_category' => $item['category'],
                    'product_image' => $item['image'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['subtotal'],
                ]);
            }

            $pdo->commit();

            return $this->findByIdForUser($orderId, $userId) ?? [];
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function findByIdForUser(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT * FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute([
            'id' => $orderId,
            'user_id' => $userId,
        ]);

        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        return $this->hydrateOrder($order);
    }

    public function markPaid(int $orderId, int $userId, string $paymentReference): bool
    {
        $stmt = $this->db->pdo()->prepare(
            'UPDATE orders
             SET
                order_status = :order_status,
                payment_status = :payment_status,
                payment_reference = :payment_reference,
                paid_at = NOW()
             WHERE id = :id
                AND user_id = :user_id
                AND order_status = :pending_status'
        );

        $stmt->execute([
            'order_status' => self::STATUS_PAID,
            'payment_status' => self::PAYMENT_STATUS_PAID,
            'payment_reference' => $paymentReference,
            'id' => $orderId,
            'user_id' => $userId,
            'pending_status' => self::STATUS_PENDING_PAYMENT,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function cancelPending(int $orderId, int $userId, string $reason): bool
    {
        $stmt = $this->db->pdo()->prepare(
            'UPDATE orders
             SET
                order_status = :order_status,
                payment_status = :payment_status,
                cancellation_reason = :cancellation_reason,
                cancelled_at = NOW()
             WHERE id = :id
                AND user_id = :user_id
                AND order_status = :pending_status'
        );

        $stmt->execute([
            'order_status' => self::STATUS_CANCELLED,
            'payment_status' => self::PAYMENT_STATUS_FAILED,
            'cancellation_reason' => $reason,
            'id' => $orderId,
            'user_id' => $userId,
            'pending_status' => self::STATUS_PENDING_PAYMENT,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function markInvoiceSent(int $orderId, int $userId): void
    {
        $stmt = $this->db->pdo()->prepare(
            'UPDATE orders
             SET invoice_sent_at = NOW()
             WHERE id = :id AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => $orderId,
            'user_id' => $userId,
        ]);
    }

    public function listByUser(int $userId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM orders WHERE user_id = :user_id';
        $params = ['user_id' => $userId];

        if ($status !== null && $status !== '') {
            $sql .= ' AND order_status = :order_status';
            $params['order_status'] = $status;
        }

        $sql .= ' ORDER BY id DESC';

        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll() ?: [];

        return array_map(fn (array $order): array => $this->hydrateOrder($order), $orders);
    }

    public function countByStatusForUser(int $userId): array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT order_status, COUNT(*) AS total
             FROM orders
             WHERE user_id = :user_id
             GROUP BY order_status'
        );
        $stmt->execute(['user_id' => $userId]);

        $totals = [
            self::STATUS_PENDING_PAYMENT => 0,
            self::STATUS_PAID => 0,
            self::STATUS_CANCELLED => 0,
        ];

        foreach ($stmt->fetchAll() ?: [] as $row) {
            $status = (string) ($row['order_status'] ?? '');

            if (array_key_exists($status, $totals)) {
                $totals[$status] = (int) ($row['total'] ?? 0);
            }
        }

        $totals['all'] = array_sum($totals);

        return $totals;
    }

    protected function hydrateOrder(array $order): array
    {
        $order['items'] = $this->items((int) $order['id']);
        $order['totals'] = [
            'subtotal' => (float) $order['subtotal_amount'],
            'shipping' => (float) $order['shipping_amount'],
            'discount' => (float) $order['discount_amount'],
            'total' => (float) $order['total_amount'],
        ];

        return $order;
    }

    protected function items(int $orderId): array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC'
        );
        $stmt->execute(['order_id' => $orderId]);

        return array_map(
            fn (array $item): array => localize_order_item($item),
            $stmt->fetchAll() ?: []
        );
    }

    // ─── Admin methods ─────────────────────────────────────────────────────────

    public function adminPaginate(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'o.order_status = :order_status';
            $params['order_status'] = $filters['status'];
        }

        if (!empty($filters['keyword'])) {
            $conditions[] = '(o.order_code LIKE :keyword OR o.customer_name LIKE :keyword OR o.customer_email LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $countStmt = $this->db->pdo()->prepare("SELECT COUNT(*) FROM orders o {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->pdo()->prepare(
            "SELECT o.*, u.name AS user_name, u.email AS user_email
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             {$where}
             ORDER BY o.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'     => $stmt->fetchAll() ?: [],
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function adminFindById(int $orderId): ?array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT o.*, u.name AS user_name, u.email AS user_email
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             WHERE o.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        return $this->hydrateOrder($order);
    }

    public function adminUpdateStatus(int $orderId, string $newStatus): bool
    {
        $stmt = $this->db->pdo()->prepare(
            'UPDATE orders SET order_status = :status WHERE id = :id'
        );

        return $stmt->execute(['status' => $newStatus, 'id' => $orderId]);
    }

    public function adminCountByStatus(): array
    {
        $stmt = $this->db->pdo()->query(
            'SELECT order_status, COUNT(*) AS total FROM orders GROUP BY order_status'
        );

        $totals = array_fill_keys(self::ADMIN_STATUSES, 0);

        foreach ($stmt->fetchAll() ?: [] as $row) {
            $status = (string) ($row['order_status'] ?? '');
            if (array_key_exists($status, $totals)) {
                $totals[$status] = (int) ($row['total'] ?? 0);
            }
        }

        $totals['all'] = array_sum($totals);

        return $totals;
    }

    public function adminCountAll(): int
    {
        return (int) $this->db->pdo()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    }

    public function adminTotalRevenue(): float
    {
        $paid = implode("','", [
            self::STATUS_PAID,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
        ]);

        $val = $this->db->pdo()
            ->query("SELECT SUM(total_amount) FROM orders WHERE order_status IN ('$paid')")
            ->fetchColumn();

        return (float) ($val ?? 0);
    }

    public function adminCountByStatusPeriod(string $period): array
    {
        $where = match ($period) {
            'day'  => 'WHERE DATE(placed_at) = CURDATE()',
            'year' => 'WHERE YEAR(placed_at) = YEAR(CURDATE())',
            default => 'WHERE YEAR(placed_at) = YEAR(CURDATE()) AND MONTH(placed_at) = MONTH(CURDATE())',
        };

        $stmt = $this->db->pdo()->query(
            "SELECT order_status, COUNT(*) AS total FROM orders $where GROUP BY order_status"
        );

        $totals = array_fill_keys(self::ADMIN_STATUSES, 0);

        foreach ($stmt->fetchAll() ?: [] as $row) {
            $status = (string) ($row['order_status'] ?? '');
            if (array_key_exists($status, $totals)) {
                $totals[$status] = (int) ($row['total'] ?? 0);
            }
        }

        return $totals;
    }

    protected function generateOrderCode(PDO $pdo): string
    {
        do {
            $orderCode = 'NS' . date('YmdHis') . random_int(100, 999);
            $stmt = $pdo->prepare('SELECT id FROM orders WHERE order_code = :order_code LIMIT 1');
            $stmt->execute(['order_code' => $orderCode]);
            $exists = (bool) $stmt->fetchColumn();
        } while ($exists);

        return $orderCode;
    }
}

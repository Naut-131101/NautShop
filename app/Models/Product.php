<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use PDO;

class Product extends Model
{
    public function paginate(array $filters = [], int $page = 1, int $perPage = 6): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['keyword'])) {
            $keywordCondition = '(name LIKE :keyword OR description LIKE :keyword OR name_en LIKE :keyword OR description_en LIKE :keyword)';

            if (!empty($filters['keyword_alt'])) {
                $keywordCondition .= ' OR name LIKE :keyword_alt OR description LIKE :keyword_alt OR name_en LIKE :keyword_alt OR description_en LIKE :keyword_alt';
                $params['keyword_alt'] = '%' . $filters['keyword_alt'] . '%';
            }

            $conditions[] = '(' . $keywordCondition . ')';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['category'])) {
            $conditions[] = '(category = :category OR category_en = :category)';
            $params['category'] = $filters['category'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $countSql = "SELECT COUNT(*) FROM products {$where}";
        $countStmt = $this->db->pdo()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM products {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->pdo()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => (int) ceil($total / $perPage),
        ];
    }

    public function categories(): array
    {
        $sql = 'SELECT DISTINCT category, category_en FROM products ORDER BY category ASC';
        $stmt = $this->db->pdo()->query($sql);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function relatedByCategory(string $category, int $excludeId, int $limit = 4): array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT * FROM products WHERE category = :category AND id != :exclude_id ORDER BY id DESC LIMIT :limit'
        );
        $stmt->bindValue(':category', $category);
        $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function adminPaginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['keyword'])) {
            $conditions[] = '(name LIKE :keyword OR description LIKE :keyword OR name_en LIKE :keyword OR description_en LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['category'])) {
            $conditions[] = '(category = :category OR category_en = :category)';
            $params['category'] = $filters['category'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $countStmt = $this->db->pdo()->prepare("SELECT COUNT(*) FROM products {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->pdo()->prepare("SELECT * FROM products {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset");

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => (int) ceil($total / $perPage),
        ];
    }

    public function create(array $data): int
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO products (name, name_en, description, description_en, price, category, category_en, image, quantity)
             VALUES (:name, :name_en, :description, :description_en, :price, :category, :category_en, :image, :quantity)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'description' => $data['description'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'price' => $data['price'],
            'category' => $data['category'],
            'category_en' => $data['category_en'] ?? null,
            'image' => $data['image'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
        ]);

        return (int) $this->db->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->pdo()->prepare(
            'UPDATE products
             SET name = :name,
                 name_en = :name_en,
                 description = :description,
                 description_en = :description_en,
                 price = :price,
                 category = :category,
                 category_en = :category_en,
                 image = :image,
                 quantity = :quantity
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'description' => $data['description'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'price' => $data['price'],
            'category' => $data['category'],
            'category_en' => $data['category_en'] ?? null,
            'image' => $data['image'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->pdo()->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->pdo()->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }
}

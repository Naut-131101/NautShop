<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class User extends Model
{
    public function findByEmail(string $email): array|false
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function findByGoogleId(string $googleId): array|false
    {
        $sql = "SELECT * FROM users WHERE google_id = :google_id LIMIT 1";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute(['google_id' => $googleId]);

        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO users (name, email, phone, password, google_id)
                VALUES (:name, :email, :phone, :password, :google_id)";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'google_id' => $data['google_id'],
        ]);
    }

    public function linkGoogleId(int $userId, string $googleId): bool
    {
        $sql = "UPDATE users SET google_id = :google_id WHERE id = :id";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute([
            'google_id' => $googleId,
            'id' => $userId,
        ]);
    }

    public function updateProfile(int $id, string $name, string $phone): bool
    {
        $sql = "UPDATE users
                SET name = :name, phone = :phone
                WHERE id = :id";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'phone' => $phone,
        ]);
    }

    public function updatePasswordByEmail(string $email, string $hashedPassword): bool
    {
        $sql = "UPDATE users SET password = :password WHERE email = :email";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute([
            'password' => $hashedPassword,
            'email' => $email,
        ]);
    }

    public function existsByEmail(string $email): bool
    {
        $sql = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetch();
    }

    // ─── Admin methods ─────────────────────────────────────────────────────────

    public function adminPaginate(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['keyword'])) {
            $conditions[] = '(name LIKE :keyword OR email LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['role'])) {
            $conditions[] = 'role = :role';
            $params['role'] = $filters['role'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $countStmt = $this->db->pdo()->prepare("SELECT COUNT(*) FROM users {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->pdo()->prepare(
            "SELECT id, name, email, phone, role, google_id, created_at FROM users {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'     => $stmt->fetchAll() ?: [],
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function adminCountAll(): int
    {
        return (int) $this->db->pdo()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function adminSetRole(int $userId, string $role): bool
    {
        $stmt = $this->db->pdo()->prepare('UPDATE users SET role = :role WHERE id = :id');
        return $stmt->execute(['role' => $role, 'id' => $userId]);
    }
}
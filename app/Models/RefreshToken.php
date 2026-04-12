<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class RefreshToken extends Model
{
    public function create(array $data): bool
    {
        $sql = "INSERT INTO refresh_tokens 
                (user_id, token_hash, expires_at, user_agent, ip_address)
                VALUES (:user_id, :token_hash, :expires_at, :user_agent, :ip_address)";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute([
            'user_id' => $data['user_id'],
            'token_hash' => $data['token_hash'],
            'expires_at' => $data['expires_at'],
            'user_agent' => $data['user_agent'],
            'ip_address' => $data['ip_address'],
        ]);
    }

    public function findValidToken(string $tokenHash): array|false
    {
        $sql = "SELECT * FROM refresh_tokens
                WHERE token_hash = :token_hash
                AND revoked_at IS NULL
                AND expires_at > NOW()
                LIMIT 1";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute(['token_hash' => $tokenHash]);

        return $stmt->fetch();
    }

    public function revokeByHash(string $tokenHash): bool
    {
        $sql = "UPDATE refresh_tokens
                SET revoked_at = NOW()
                WHERE token_hash = :token_hash";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute(['token_hash' => $tokenHash]);
    }

    public function revokeAllByUserId(int $userId): bool
    {
        $sql = "UPDATE refresh_tokens
                SET revoked_at = NOW()
                WHERE user_id = :user_id AND revoked_at IS NULL";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute(['user_id' => $userId]);
    }
}
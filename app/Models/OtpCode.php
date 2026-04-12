<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class OtpCode extends Model
{
    public function create(array $data): bool
    {
        $sql = "INSERT INTO otp_codes (user_id, email, purpose, otp_code, expires_at)
                VALUES (:user_id, :email, :purpose, :otp_code, :expires_at)";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute([
            'user_id' => $data['user_id'],
            'email' => $data['email'],
            'purpose' => $data['purpose'],
            'otp_code' => $data['otp_code'],
            'expires_at' => $data['expires_at'],
        ]);
    }

    public function findLatestOtp(string $email, string $purpose, string $otpCode): array|false
    {
        $sql = "SELECT * FROM otp_codes
                WHERE email = :email
                  AND purpose = :purpose
                  AND otp_code = :otp_code
                ORDER BY id DESC
                LIMIT 1";

        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'purpose' => $purpose,
            'otp_code' => $otpCode,
        ]);

        return $stmt->fetch();
    }

    public function markAsUsed(int $id): bool
    {
        $sql = "UPDATE otp_codes SET used_at = NOW() WHERE id = :id";
        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function invalidateOldOtps(string $email, string $purpose): bool
    {
        $sql = "UPDATE otp_codes
                SET used_at = NOW()
                WHERE email = :email
                  AND purpose = :purpose
                  AND used_at IS NULL";

        $stmt = $this->db->pdo()->prepare($sql);

        return $stmt->execute([
            'email' => $email,
            'purpose' => $purpose,
        ]);
    }
}
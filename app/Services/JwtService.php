<?php

declare(strict_types=1);

namespace App\Services;

/**
 * JwtService – stub (JWT đã bị loại bỏ, auth chỉ dùng session).
 *
 * Class này được giữ lại để tránh lỗi nếu có code nào đó còn import nó,
 * nhưng không còn được sử dụng trong luồng chính.
 */
class JwtService
{
    public function createAccessToken(array $user): string
    {
        return '';
    }

    public function createRefreshToken(array $user): string
    {
        return '';
    }

    public function decodeAccessToken(string $token): object
    {
        return new \stdClass();
    }

    public function decodeRefreshToken(string $token): object
    {
        return new \stdClass();
    }
}

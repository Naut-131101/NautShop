<?php

declare(strict_types=1);

namespace App\Services;

/**
 * TokenService – stub (JWT đã bị loại bỏ, auth chỉ dùng session).
 *
 * Class này được giữ lại để tránh lỗi nếu có code nào đó còn import nó,
 * nhưng không còn được sử dụng trong luồng chính.
 */
class TokenService
{
    public function issueTokens(array $user): array
    {
        return ['access_token' => '', 'refresh_token' => ''];
    }

    public function refresh(string $refreshToken): array|false
    {
        return false;
    }

    public function revokeCurrentRefreshToken(?string $refreshToken): void
    {
        // no-op
    }

    public function bootstrapUserFromAccessToken(?string $accessToken): ?array
    {
        return null;
    }

    public function attemptAutoRefresh(?string $refreshToken): ?array
    {
        return null;
    }
}

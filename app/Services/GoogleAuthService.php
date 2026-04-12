<?php

declare(strict_types=1);

namespace App\Services;

/**
 * GoogleAuthService – stub (google/apiclient đã bị loại bỏ).
 *
 * Tính năng đăng nhập bằng Google bị vô hiệu hóa trên môi trường
 * shared hosting (InfinityFree) vì không hỗ trợ Composer package lớn.
 */
class GoogleAuthService
{
    public function getAuthUrl(): string
    {
        return '';
    }

    public function handleCallback(string $code): array|false
    {
        return false;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

class CacheService
{
    protected string $cachePath;

    public function __construct()
    {
        $this->cachePath = BASE_PATH . '/storage/cache/';
    }

    public function get(string $key): mixed
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);

        if ($content === false) {
            return null;
        }

        $payload = json_decode($content, true);

        if (
            !is_array($payload) ||
            !isset($payload['expires_at']) ||
            !array_key_exists('data', $payload)
        ) {
            @unlink($file);
            return null;
        }

        if (time() > (int) $payload['expires_at']) {
            @unlink($file);
            return null;
        }

        return $payload['data'];
    }

    public function put(string $key, mixed $data, int $ttl = 60): bool
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }

        $payload = [
            'expires_at' => time() + $ttl,
            'data' => $data,
        ];

        return file_put_contents(
            $this->getFilePath($key),
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ) !== false;
    }

    public function forget(string $key): void
    {
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public function flush(): void
    {
        if (!is_dir($this->cachePath)) {
            return;
        }

        $files = glob($this->cachePath . '*.cache');

        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    protected function getFilePath(string $key): string
    {
        return $this->cachePath . md5($key) . '.cache';
    }
}
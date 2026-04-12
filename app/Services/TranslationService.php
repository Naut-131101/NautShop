<?php

declare(strict_types=1);

namespace App\Services;

class TranslationService
{
    protected CacheService $cache;
    protected int $ttl = 2592000;

    public function __construct()
    {
        $this->cache = new CacheService();
    }

    public function translate(string $text, string $source = 'vi', string $target = 'en'): string
    {
        $normalized = trim($text);

        if ($normalized === '' || $source === $target) {
            return $normalized;
        }

        $cacheKey = 'translate_' . md5(json_encode([
            'source' => $source,
            'target' => $target,
            'text' => $normalized,
        ], JSON_UNESCAPED_UNICODE));

        $cached = $this->cache->get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $translated = $this->requestGoogleTranslate($normalized, $source, $target);

        if ($translated === '') {
            return $normalized;
        }

        $this->cache->put($cacheKey, $translated, $this->ttl);

        return $translated;
    }

    protected function requestGoogleTranslate(string $text, string $source, string $target): string
    {
        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx'
            . '&sl=' . rawurlencode($source)
            . '&tl=' . rawurlencode($target)
            . '&dt=t&q=' . rawurlencode($text);

        $raw = null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_HTTPHEADER => [
                    'User-Agent: Mozilla/5.0',
                ],
            ]);
            $result = curl_exec($ch);
            $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($statusCode >= 200 && $statusCode < 300 && is_string($result)) {
                $raw = $result;
            }
        }

        if ($raw === null) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 8,
                    'header' => "User-Agent: Mozilla/5.0\r\n",
                ],
            ]);

            $result = @file_get_contents($url, false, $context);

            if (is_string($result)) {
                $raw = $result;
            }
        }

        if (!is_string($raw) || $raw === '') {
            return '';
        }

        $payload = json_decode($raw, true);

        if (!is_array($payload) || !isset($payload[0]) || !is_array($payload[0])) {
            return '';
        }

        $translated = '';

        foreach ($payload[0] as $chunk) {
            if (is_array($chunk) && isset($chunk[0]) && is_string($chunk[0])) {
                $translated .= $chunk[0];
            }
        }

        return trim($translated);
    }
}

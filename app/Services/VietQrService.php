<?php

declare(strict_types=1);

namespace App\Services;

class VietQrService
{
    public function generate(array $payload): array
    {
        $accountNo = (string) ($payload['accountNo'] ?? env('VIETQR_ACCOUNT_NO', '113366668888'));
        $accountName = (string) ($payload['accountName'] ?? env('VIETQR_ACCOUNT_NAME', 'NAUT SHOP DEMO'));
        $acqId = (string) ($payload['acqId'] ?? env('VIETQR_ACQ_ID', '970415'));
        $bankCode = (string) ($payload['bankCode'] ?? env('VIETQR_BANK_CODE', 'vietinbank'));
        $bankName = (string) ($payload['bankName'] ?? env('VIETQR_BANK_NAME', 'VietinBank'));
        $amount = (int) ($payload['amount'] ?? 0);
        $addInfo = (string) ($payload['addInfo'] ?? '');
        $template = (string) ($payload['template'] ?? env('VIETQR_TEMPLATE', 'compact2'));

        $apiImage = $this->generateViaOfficialApi([
            'accountNo' => $accountNo,
            'accountName' => $accountName,
            'acqId' => $acqId,
            'amount' => $amount,
            'addInfo' => $addInfo,
            'template' => $template,
        ]);

        if ($apiImage !== null) {
            return [
                'imageSrc' => $apiImage,
                'providerLabel' => 'VietQR API',
                'isOfficialApi' => true,
                'accountNo' => $accountNo,
                'accountName' => $accountName,
                'acqId' => $acqId,
                'bankCode' => $bankCode,
                'bankName' => $bankName,
            ];
        }

        $query = http_build_query([
            'amount' => $amount,
            'addInfo' => $addInfo,
            'accountName' => $accountName,
        ]);

        return [
            'imageSrc' => sprintf(
                'https://img.vietqr.io/image/%s-%s-%s.png?%s',
                rawurlencode($bankCode),
                rawurlencode($accountNo),
                rawurlencode($template),
                $query
            ),
            'providerLabel' => 'VietQR Quicklink',
            'isOfficialApi' => false,
            'accountNo' => $accountNo,
            'accountName' => $accountName,
            'acqId' => $acqId,
            'bankCode' => $bankCode,
            'bankName' => $bankName,
        ];
    }

    protected function generateViaOfficialApi(array $payload): ?string
    {
        $clientId = trim((string) env('VIETQR_CLIENT_ID', ''));
        $apiKey = trim((string) env('VIETQR_API_KEY', ''));

        if ($clientId === '' || $apiKey === '') {
            return null;
        }

        $body = json_encode([
            'accountNo' => $payload['accountNo'],
            'accountName' => $payload['accountName'],
            'acqId' => (int) $payload['acqId'],
            'amount' => (int) $payload['amount'],
            'addInfo' => $payload['addInfo'],
            'format' => 'text',
            'template' => $payload['template'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($body === false) {
            return null;
        }

        $headers = [
            'Content-Type: application/json',
            'x-client-id: ' . $clientId,
            'x-api-key: ' . $apiKey,
        ];

        $response = null;

        if (function_exists('curl_init')) {
            $ch = curl_init('https://api.vietqr.io/v2/generate');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 12,
            ]);

            $raw = curl_exec($ch);
            $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (is_string($raw) && $statusCode >= 200 && $statusCode < 300) {
                $response = json_decode($raw, true);
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => $body,
                    'timeout' => 12,
                ],
            ]);

            $raw = @file_get_contents('https://api.vietqr.io/v2/generate', false, $context);

            if (is_string($raw)) {
                $response = json_decode($raw, true);
            }
        }

        $qrDataUrl = $response['data']['qrDataURL'] ?? null;

        return is_string($qrDataUrl) && $qrDataUrl !== '' ? $qrDataUrl : null;
    }
}

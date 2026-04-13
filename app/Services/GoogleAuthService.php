<?php

declare(strict_types=1);

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleOauth2;

class GoogleAuthService
{
    private GoogleClient $client;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID', ''));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET', ''));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI', ''));
        $this->client->addScope('email');
        $this->client->addScope('profile');
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Xử lý callback từ Google, trả về thông tin user hoặc false nếu thất bại.
     *
     * @return array{id: string, name: string, email: string, avatar: string|null}|false
     */
    public function handleCallback(string $code): array|false
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                return false;
            }

            $this->client->setAccessToken($token);

            $oauth2   = new GoogleOauth2($this->client);
            $userInfo = $oauth2->userinfo->get();

            return [
                'id'     => $userInfo->getId(),
                'name'   => $userInfo->getName(),
                'email'  => $userInfo->getEmail(),
                'avatar' => $userInfo->getPicture(),
            ];
        } catch (\Throwable $e) {
            return false;
        }
    }
}

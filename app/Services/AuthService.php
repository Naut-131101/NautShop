<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Core\Validator;

class AuthService
{
    protected User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function register(array $data): array
    {
        $validator = new Validator();

        $validator
            ->required('name', $data['name'] ?? null, t('validation.name_required'))
            ->regex('name', $data['name'] ?? null, '/^[\p{L}\s]+$/u', t('validation.name_letters_spaces'))
            ->required('email', $data['email'] ?? null, t('validation.email_required'))
            ->email('email', $data['email'] ?? null, t('validation.email_invalid'))
            ->required('phone', $data['phone'] ?? null, t('validation.phone_required'))
            ->regex('phone', $data['phone'] ?? null, '/^[0-9]{10,11}$/', t('validation.phone_digits'))
            ->required('password', $data['password'] ?? null, t('validation.password_required'))
            ->regex(
                'password',
                $data['password'] ?? null,
                '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
                t('validation.password_strong')
            )
            ->confirm(
                'password_confirmation',
                $data['password'] ?? null,
                $data['password_confirmation'] ?? null,
                t('validation.password_confirmed')
            );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $existingUser = $this->users->findByEmail(trim((string) $data['email']));

        if ($existingUser) {
            return [
                'success' => false,
                'errors' => [
                    'email' => [t('validation.email_exists')]
                ],
            ];
        }

        $created = $this->users->create([
            'name' => trim((string) $data['name']),
            'email' => trim((string) $data['email']),
            'phone' => trim((string) $data['phone']),
            'password' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
        ]);

        if (!$created) {
            return [
                'success' => false,
                'errors' => [
                    'general' => ['Đăng ký thất bại, vui lòng thử lại.']
                ],
            ];
        }

        return [
            'success' => true,
        ];
    }

    public function login(array $data): array
    {
        $validator = new Validator();

        $validator
            ->required('email', $data['email'] ?? null, t('validation.email_required'))
            ->email('email', $data['email'] ?? null, t('validation.email_invalid'))
            ->required('password', $data['password'] ?? null, t('validation.password_required'));

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $user = $this->users->findByEmail(trim((string) $data['email']));

        if (!$user || !password_verify((string) $data['password'], $user['password'])) {
            return [
                'success' => false,
                'errors' => [
                    'general' => [t('validation.invalid_credentials')]
                ],
            ];
        }

        return [
            'success' => true,
            'user' => $user,
        ];
    }
}

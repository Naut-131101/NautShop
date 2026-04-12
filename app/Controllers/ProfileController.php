<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\JwtAuthMiddleware;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class ProfileController extends Controller
{
    protected User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function showCompleteProfile(Request $request, Response $response): void
    {
        JwtAuthMiddleware::handle();

        $authUser = auth();

        if (!$authUser) {
            Session::flash('errors', [
                'general' => [t('flash.profile.not_logged_in')]
            ]);
            $this->redirect('/login');
        }

        $user = $this->users->findById((int) $authUser['id']);

        if (!$user) {
            Session::flash('errors', [
                'general' => [t('flash.profile.account_not_found')]
            ]);
            $this->redirect('/login');
        }

        if (!empty($user['phone'])) {
            $this->redirect('/products');
        }

        $this->view('auth/complete-profile', [
            'title' => t('title.complete_profile'),
            'errors' => flash('errors', []),
            'success' => flash('success'),
            'user' => $user,
        ]);
    }

    public function completeProfile(Request $request, Response $response): void
    {
        JwtAuthMiddleware::handle();

        $authUser = auth();

        if (!$authUser) {
            Session::flash('errors', [
                'general' => [t('flash.profile.not_logged_in')]
            ]);
            $this->redirect('/login');
        }

        $name = trim((string) $request->input('name', ''));
        $phone = trim((string) $request->input('phone', ''));

        $validator = new Validator();
        $validator
            ->required('name', $name, t('validation.name_required'))
            ->regex('name', $name, '/^[\p{L}\s]+$/u', t('validation.name_letters_spaces'))
            ->required('phone', $phone, t('validation.phone_required'))
            ->regex('phone', $phone, '/^[0-9]{10,11}$/', t('validation.phone_digits'));

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::putOld([
                'name' => $name,
                'phone' => $phone,
            ]);
            $this->redirect('/complete-profile');
        }

        $updated = $this->users->updateProfile(
            (int) $authUser['id'],
            $name,
            $phone
        );

        if (!$updated) {
            Session::flash('errors', [
                'general' => [t('flash.profile.update_failed')]
            ]);
            $this->redirect('/complete-profile');
        }

        $user = $this->users->findById((int) $authUser['id']);

        Session::set('auth_user', [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
        ]);

        Session::clearOld();
        Session::flash('success', t('flash.profile.update_success'));

        $this->redirect('/products');
    }
}

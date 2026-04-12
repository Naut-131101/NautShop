<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Middleware\AdminMiddleware;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class AdminUserController extends Controller
{
    protected User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    protected function guard(): void
    {
        AdminMiddleware::handle();
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard();

        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'keyword' => trim((string) $request->input('keyword', '')),
            'role' => trim((string) $request->input('role', '')),
        ];

        $result = $this->users->adminPaginate($filters, $page, 25);

        $this->view('admin/users/index', [
            'title' => t('admin.users_page_title'),
            'users' => $result['data'],
            'filters' => $filters,
            'pagination' => [
                'page' => $result['page'],
                'perPage' => $result['perPage'],
                'total' => $result['total'],
                'lastPage' => $result['lastPage'],
            ],
        ], 'admin');
    }

    public function setRole(Request $request, Response $response): void
    {
        $this->guard();

        $targetId = (int) $request->input('id', 0);
        $newRole = trim((string) $request->input('role', 'user'));
        $me = auth() ?? [];

        if ((int) ($me['id'] ?? 0) === $targetId) {
            Session::flash('success', t('admin.cannot_change_own_role'));
            $this->redirect('/admin/users');
        }

        if (!in_array($newRole, ['user', 'admin'], true)) {
            Session::flash('success', t('admin.invalid_role'));
            $this->redirect('/admin/users');
        }

        $this->users->adminSetRole($targetId, $newRole);
        Session::flash('success', t('admin.user_role_updated_success'));
        $this->redirect('/admin/users');
    }
}

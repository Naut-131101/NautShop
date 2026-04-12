<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;

class HomeController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        if (!is_auth()) {
            $this->redirect('/login');
        }

        $user = auth();

        if (empty($user['phone'])) {
            $this->redirect('/complete-profile');
        }

        $this->redirect('/products');
    }

    public function dbTest(Request $request, Response $response): void
    {
        $response->json([
            'status' => 'success',
            'message' => 'Database connection OK'
        ]);
    }
}
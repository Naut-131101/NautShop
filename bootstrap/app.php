<?php

declare(strict_types=1);

use Core\App;
use Core\Request;
use Core\Response;
use Core\Router;
use Core\Session;

require_once BASE_PATH . '/config/constants.php';

loadEnv(BASE_PATH . '/.env');
date_default_timezone_set('Asia/Ho_Chi_Minh');

$router = new Router();
$request = new Request();
$response = new Response();

Session::start();
boot_locale();

require BASE_PATH . '/bootstrap/auth.php';
require BASE_PATH . '/routes/web.php';

return new App($router, $request, $response);

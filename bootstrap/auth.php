<?php

declare(strict_types=1);

use Core\Session;

/**
 * Session-based auth bootstrap.
 *
 * JWT đã được loại bỏ hoàn toàn.
 * Trạng thái đăng nhập chỉ dựa vào $_SESSION['auth_user'].
 * Session được thiết lập trong AuthController::login() và xóa khi logout().
 */

// Không cần làm gì thêm ở đây – session đã được Session::start() khởi động
// trước khi file này được require (xem bootstrap/app.php).

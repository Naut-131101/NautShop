<section class="card">
    <h2><?= e($title ?? 'Welcome') ?></h2>
    <p><?= e($message ?? '') ?></p>

    <ul>
        <li>Router: OK</li>
        <li>Controller: OK</li>
        <li>View render: OK</li>
        <li>.env loader: OK</li>
        <li>Session bootstrap: OK</li>
        <li>Database class: Ready</li>
    </ul>

    <?php if (is_auth()): ?>
        <hr>
        <p><strong>Đã đăng nhập:</strong> <?= e((string) auth()['email']) ?></p>
        <p><strong>Số điện thoại:</strong> <?= e((string) auth()['phone']) ?></p>
        <p>
            <a href="<?= e(url('/products')) ?>">Đi tới danh sách sản phẩm</a>
        </p>
    <?php else: ?>
        <hr>
        <p>Bạn chưa đăng nhập.</p>
        <p>
            <a href="<?= e(url('/login')) ?>">Đi tới đăng nhập</a> |
            <a href="<?= e(url('/register')) ?>">Đi tới đăng ký</a>
        </p>
    <?php endif; ?>
</section>
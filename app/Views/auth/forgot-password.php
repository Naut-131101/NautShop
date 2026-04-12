<div class="center-card-wrap">
    <div class="center-card">
        <h2><?= e(t('auth.forgot_heading')) ?></h2>
        <p><?= e(t('auth.forgot_text')) ?></p>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?= e((string) $success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert error"><?= e($errors['general'][0]) ?></div>
        <?php endif; ?>

        <form action="<?= e(url('/forgot-password')) ?>" method="POST" class="form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="<?= e(t('placeholder.email')) ?>" value="<?= e((string) old('email')) ?>">
                <?php if (!empty($errors['email'])): ?>
                    <small class="error-text"><?= e($errors['email'][0]) ?></small>
                <?php endif; ?>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary"><?= e(t('auth.send_otp')) ?></button>
                <a href="<?= e(url('/login')) ?>" class="btn btn-secondary"><?= e(t('auth.back_to_login')) ?></a>
            </div>
        </form>
    </div>
</div>

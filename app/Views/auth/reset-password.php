<div class="center-card-wrap">
    <div class="center-card">
        <h2><?= e(t('auth.reset_heading')) ?></h2>
        <p><?= e(t('auth.reset_text')) ?></p>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?= e((string) $success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert error"><?= e($errors['general'][0]) ?></div>
        <?php endif; ?>

        <form action="<?= e(url('/reset-password')) ?>" method="POST" class="form">
            <div class="form-group">
                <label for="password"><?= e(t('auth.new_password')) ?></label>
                <input type="password" id="password" name="password" placeholder="<?= e(t('auth.new_password_placeholder')) ?>">
                <?php if (!empty($errors['password'])): ?>
                    <small class="error-text"><?= e($errors['password'][0]) ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirmation"><?= e(t('auth.confirm_new_password')) ?></label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="<?= e(t('auth.confirm_new_password_placeholder')) ?>">
                <?php if (!empty($errors['password_confirmation'])): ?>
                    <small class="error-text"><?= e($errors['password_confirmation'][0]) ?></small>
                <?php endif; ?>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary"><?= e(t('auth.reset_password_button')) ?></button>
            </div>
        </form>
    </div>
</div>

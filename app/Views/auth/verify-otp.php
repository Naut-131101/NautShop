<div class="center-card-wrap">
    <div class="center-card">
        <h2><?= e(t('auth.verify_heading')) ?></h2>
        <p><?= str_replace(':email', e((string) $email), t('auth.verify_text')) ?></p>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?= e((string) $success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert error"><?= e($errors['general'][0]) ?></div>
        <?php endif; ?>

        <form action="<?= e(url('/forgot-password/verify')) ?>" method="POST" class="form">
            <div class="form-group">
                <label for="otp_code"><?= e(t('auth.otp_code')) ?></label>
                <input type="text" id="otp_code" name="otp_code" maxlength="6" placeholder="<?= e(t('auth.otp_placeholder')) ?>">
                <?php if (!empty($errors['otp_code'])): ?>
                    <small class="error-text"><?= e($errors['otp_code'][0]) ?></small>
                <?php endif; ?>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary"><?= e(t('auth.verify_otp_button')) ?></button>
            </div>
        </form>
    </div>
</div>

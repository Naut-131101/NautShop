<div class="center-card-wrap">
    <div class="center-card">
        <h2><?= e(t('auth.complete_profile_heading')) ?></h2>
        <p><?= e(t('auth.complete_profile_text')) ?></p>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?= e((string) $success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert error"><?= e($errors['general'][0]) ?></div>
        <?php endif; ?>

        <form action="<?= e(url('/complete-profile')) ?>" method="POST" class="form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?= e((string) ($user['email'] ?? '')) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="name"><?= e(t('auth.full_name')) ?></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="<?= e(t('placeholder.full_name')) ?>"
                    value="<?= e((string) old('name', (string) ($user['name'] ?? ''))) ?>"
                >
                <?php if (!empty($errors['name'])): ?>
                    <small class="error-text"><?= e($errors['name'][0]) ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone"><?= e(t('auth.phone')) ?></label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="<?= e(t('placeholder.phone')) ?>"
                    value="<?= e((string) old('phone', (string) ($user['phone'] ?? ''))) ?>"
                >
                <?php if (!empty($errors['phone'])): ?>
                    <small class="error-text"><?= e($errors['phone'][0]) ?></small>
                <?php endif; ?>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary"><?= e(t('auth.complete_profile_button')) ?></button>
            </div>
        </form>
    </div>
</div>

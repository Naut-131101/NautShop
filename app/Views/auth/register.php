<section class="auth-layout">
    <div class="auth-showcase auth-panel">
        <div class="showcase-copy">
            <h1 class="showcase-title"><?= t('auth.register_showcase_title') ?></h1>

            <p class="showcase-text">
                <?= e(t('auth.register_showcase_text')) ?>
            </p>
        </div>

        <div class="showcase-grid">
            <div class="showcase-card showcase-card-large">
                <h3><?= e(t('auth.register_card_title')) ?></h3>
                <p><?= e(t('auth.register_card_text')) ?></p>
                <div class="showcase-tags">
                    <span><?= e(t('auth.register_tag_1')) ?></span>
                    <span><?= e(t('auth.register_tag_2')) ?></span>
                </div>
            </div>

            <div class="showcase-card">
                <h3><?= e(t('auth.register_feature_1_title')) ?></h3>
                <p><?= e(t('auth.register_feature_1_text')) ?></p>
            </div>

            <div class="showcase-card">
                <h3><?= e(t('auth.register_feature_2_title')) ?></h3>
                <p><?= e(t('auth.register_feature_2_text')) ?></p>
            </div>
        </div>
    </div>

    <div class="auth-panel auth-form-panel">
        <div class="auth-form-card">
            <h2><?= e(t('auth.register_heading')) ?></h2>
            <p class="auth-subtitle"><?= e(t('auth.register_subtitle')) ?></p>

            <?php if (!empty($success)): ?>
                <div class="alert success"><?= e((string) $success) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert error"><?= e($errors['general'][0]) ?></div>
            <?php endif; ?>

            <form action="<?= e(url('/register')) ?>" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name"><?= e(t('auth.full_name')) ?></label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="<?= e(t('placeholder.full_name')) ?>"
                        value="<?= e((string) old('name')) ?>"
                    >
                    <?php if (!empty($errors['name'])): ?>
                        <small class="error-text"><?= e($errors['name'][0]) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="<?= e(t('placeholder.email')) ?>"
                        value="<?= e((string) old('email')) ?>"
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <small class="error-text"><?= e($errors['email'][0]) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="phone"><?= e(t('auth.phone')) ?></label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        placeholder="<?= e(t('placeholder.phone')) ?>"
                        value="<?= e((string) old('phone')) ?>"
                    >
                    <?php if (!empty($errors['phone'])): ?>
                        <small class="error-text"><?= e($errors['phone'][0]) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password"><?= e(t('auth.password')) ?></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="<?= e(t('placeholder.password_create')) ?>"
                    >
                    <?php if (!empty($errors['password'])): ?>
                        <small class="error-text"><?= e($errors['password'][0]) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password_confirmation"><?= e(t('auth.confirm_password')) ?></label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="<?= e(t('placeholder.password_confirm')) ?>"
                    >
                    <?php if (!empty($errors['password_confirmation'])): ?>
                        <small class="error-text"><?= e($errors['password_confirmation'][0]) ?></small>
                    <?php endif; ?>
                </div>

                <div class="auth-action-stack">
                    <button type="submit" class="btn btn-primary btn-full"><?= e(t('auth.register_button')) ?></button>
                </div>
            </form>

            <div class="auth-inline-links">
                <span><?= e(t('auth.have_account')) ?> <a href="<?= e(url('/login')) ?>"><?= e(t('auth.login_now')) ?></a></span>
            </div>
        </div>
    </div>
</section>

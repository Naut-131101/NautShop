<section class="auth-layout">
    <div class="auth-showcase auth-panel">
        <div class="showcase-copy">
            <h1 class="showcase-title"><?= t('auth.login_showcase_title') ?></h1>

            <p class="showcase-text">
                <?= e(t('auth.login_showcase_text')) ?>
            </p>
        </div>

        <div class="showcase-grid">
            <div class="showcase-card showcase-card-large">
                <h3><?= e(t('auth.login_card_title')) ?></h3>
                <p><?= e(t('auth.login_card_text')) ?></p>
                <div class="showcase-tags">
                    <span><?= e(t('auth.login_tag_1')) ?></span>
                    <span><?= e(t('auth.login_tag_2')) ?></span>
                </div>
            </div>

            <div class="showcase-card">
                <h3><?= e(t('auth.login_feature_1_title')) ?></h3>
                <p><?= e(t('auth.login_feature_1_text')) ?></p>
            </div>

            <div class="showcase-card">
                <h3><?= e(t('auth.login_feature_2_title')) ?></h3>
                <p><?= e(t('auth.login_feature_2_text')) ?></p>
            </div>
        </div>
    </div>

    <div class="auth-panel auth-form-panel">
        <div class="auth-form-card">
            <h2><?= e(t('auth.login_heading')) ?></h2>
            <p class="auth-subtitle"><?= e(t('auth.login_subtitle')) ?></p>

            <?php if (!empty($success)): ?>
                <div class="alert success"><?= e((string) $success) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert error"><?= e($errors['general'][0]) ?></div>
            <?php endif; ?>

            <form action="<?= e(url('/login')) ?>" method="POST" class="auth-form">
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
                    <label for="password"><?= e(t('auth.password')) ?></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="<?= e(t('placeholder.password')) ?>"
                    >
                    <?php if (!empty($errors['password'])): ?>
                        <small class="error-text"><?= e($errors['password'][0]) ?></small>
                    <?php endif; ?>
                </div>

                <div class="auth-action-stack">
                    <button type="submit" class="btn btn-primary btn-full"><?= e(t('auth.login_button')) ?></button>
                    <a href="<?= e(url('/auth/google')) ?>" class="btn btn-secondary btn-full"><?= e(t('auth.login_google')) ?></a>
                </div>
            </form>

            <div class="auth-inline-links">
                <a href="<?= e(url('/forgot-password')) ?>"><?= e(t('auth.forgot_password')) ?></a>
                <span class="dot-divider"></span>
                <span><?= e(t('auth.no_account')) ?> <a href="<?= e(url('/register')) ?>"><?= e(t('auth.register_now')) ?></a></span>
            </div>
        </div>
    </div>
</section>

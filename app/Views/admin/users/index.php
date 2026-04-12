<?php $me = auth() ?? []; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e(t('admin.users_title')) ?></h1>
        <p class="admin-page-sub"><?= e(t('admin.accounts_count', ['count' => (string) $pagination['total']])) ?></p>
    </div>
</div>

<form method="GET" action="<?= e(url('/admin/users')) ?>" class="admin-filter-bar">
    <div class="admin-filter-grid">
        <div class="admin-filter-field">
            <label for="keyword"><?= e(t('admin.search_users')) ?></label>
            <div class="admin-filter-search" data-admin-search data-history-key="admin_users_searches">
                <input id="keyword" name="keyword" type="text" class="admin-input" placeholder="<?= e(t('admin.search_users_placeholder')) ?>" value="<?= e($filters['keyword']) ?>" autocomplete="off">
                <button type="button" class="admin-filter-clear" aria-label="<?= e(t('admin.clear_keyword')) ?>" <?= $filters['keyword'] === '' ? 'hidden' : '' ?>>×</button>
                <div class="admin-filter-search-panel" hidden>
                    <div class="admin-filter-search-head">
                        <strong><?= e(t('admin.recent_searches')) ?></strong>
                        <button type="button" class="admin-filter-search-trash" aria-label="<?= e(t('admin.clear_search_history')) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm-2 6h2v8H7V9Zm4 0h2v8h-2V9Zm4 0h2v8h-2V9ZM6 7h12l-1 13H7L6 7Z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="admin-filter-search-list"></div>
                </div>
            </div>
        </div>

        <div class="admin-filter-field">
            <label for="role"><?= e(t('admin.role')) ?></label>
            <div class="admin-filter-select" data-admin-select>
                <select id="role" name="role" class="admin-select admin-filter-native" tabindex="-1" aria-hidden="true">
                    <option value=""><?= e(t('admin.all_roles')) ?></option>
                    <option value="user" <?= $filters['role'] === 'user' ? 'selected' : '' ?>><?= e(t('admin.role_user')) ?></option>
                    <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>><?= e(t('admin.role_admin')) ?></option>
                </select>

                <button type="button" class="admin-filter-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span class="admin-filter-select-label"><?= e($filters['role'] !== '' ? t('admin.role_' . $filters['role']) : t('admin.all_roles')) ?></span>
                    <span class="admin-filter-select-chevron" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M7 14l5-5 5 5"/></svg>
                    </span>
                </button>

                <div class="admin-filter-select-panel" role="listbox" hidden>
                    <button type="button" class="admin-filter-select-option <?= $filters['role'] === '' ? 'is-selected' : '' ?>" data-value="" role="option" aria-selected="<?= $filters['role'] === '' ? 'true' : 'false' ?>"><?= e(t('admin.all_roles')) ?></button>
                    <button type="button" class="admin-filter-select-option <?= $filters['role'] === 'user' ? 'is-selected' : '' ?>" data-value="user" role="option" aria-selected="<?= $filters['role'] === 'user' ? 'true' : 'false' ?>"><?= e(t('admin.role_user')) ?></button>
                    <button type="button" class="admin-filter-select-option <?= $filters['role'] === 'admin' ? 'is-selected' : '' ?>" data-value="admin" role="option" aria-selected="<?= $filters['role'] === 'admin' ? 'true' : 'false' ?>"><?= e(t('admin.role_admin')) ?></button>
                </div>
            </div>
        </div>

        <div class="admin-filter-actions">
            <button type="submit" class="admin-btn admin-btn-primary"><?= e(t('admin.apply')) ?></button>
            <a href="<?= e(url('/admin/users')) ?>" class="admin-btn admin-btn-secondary"><?= e(t('admin.reset')) ?></a>
        </div>
    </div>
</form>

<div class="admin-table-wrap">
    <?php if (empty($users)): ?>
        <div class="admin-empty">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
            <p><?= e(t('admin.no_users_found')) ?></p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= e(t('admin.name')) ?></th>
                    <th><?= e(t('admin.email')) ?></th>
                    <th><?= e(t('admin.phone')) ?></th>
                    <th><?= e(t('admin.role')) ?></th>
                    <th><?= e(t('admin.registered_at')) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td style="color:var(--text-faint);font-size:13px;">#<?= e((string) $user['id']) ?></td>
                        <td>
                            <strong><?= e((string) $user['name']) ?></strong>
                            <?php if (!empty($user['google_id'])): ?>
                                <span style="font-size:11px;margin-left:4px;color:var(--text-faint);" title="<?= e(t('admin.google_login')) ?>">G</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e((string) $user['email']) ?></td>
                        <td style="color:var(--text-soft);"><?= e((string) ($user['phone'] ?? '—')) ?></td>
                        <td>
                            <span class="admin-badge admin-badge-<?= e((string) $user['role']) ?>">
                                <?= e(t('admin.role_' . (string) $user['role'])) ?>
                            </span>
                        </td>
                        <td style="font-size:13px;color:var(--text-soft);">
                            <?= e(date('d/m/Y', strtotime((string) $user['created_at']))) ?>
                        </td>
                        <td>
                            <?php if ((int) $user['id'] !== (int) ($me['id'] ?? 0)): ?>
                                <div class="admin-table-actions">
                                    <form method="POST" action="<?= e(url('/admin/users/set-role')) ?>" onsubmit="return confirm('<?= e(t('admin.confirm_change_role', ['name' => (string) $user['name'], 'role' => $user['role'] === 'admin' ? t('admin.role_user') : t('admin.role_admin')])) ?>')">
                                        <input type="hidden" name="id" value="<?= e((string) $user['id']) ?>">
                                        <input type="hidden" name="role" value="<?= e($user['role'] === 'admin' ? 'user' : 'admin') ?>">
                                        <button type="submit" class="admin-btn admin-btn-sm <?= $user['role'] === 'admin' ? 'admin-btn-danger' : 'admin-btn-primary' ?>">
                                            <?= e($user['role'] === 'admin' ? t('admin.demote_to_user') : t('admin.promote_to_admin')) ?>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="admin-badge admin-badge-processing" style="font-size:11px;"><?= e(t('admin.you')) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($pagination['lastPage'] > 1): ?>
    <div class="admin-pagination">
        <?php
        $base = url('/admin/users') . '?' . http_build_query(array_filter([
            'keyword' => $filters['keyword'],
            'role' => $filters['role'],
        ]));
        $pageBase = str_contains($base, '=') ? $base . '&' : $base;
        ?>
        <?php if ($pagination['page'] > 1): ?>
            <a href="<?= e($pageBase . 'page=' . ($pagination['page'] - 1)) ?>" class="admin-page-link">‹</a>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $pagination['lastPage']; $p++): ?>
            <a href="<?= e($pageBase . 'page=' . $p) ?>" class="admin-page-link <?= $p === $pagination['page'] ? 'is-active' : '' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>

        <?php if ($pagination['page'] < $pagination['lastPage']): ?>
            <a href="<?= e($pageBase . 'page=' . ($pagination['page'] + 1)) ?>" class="admin-page-link">›</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

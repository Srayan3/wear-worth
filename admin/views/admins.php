<div class="a-toolbar">
    <p style="color:var(--a-muted); margin:0; font-size:13px;">Only Super Admins can manage other admin accounts.</p>
    <button type="button" class="a-btn a-btn-primary" data-modal-open="adminModal">+ Add Admin</button>
</div>

<div class="a-table-wrap">
    <table class="a-table">
        <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
        <tr>
            <td style="font-weight:600;"><?= e($a['name']) ?></td>
            <td><?= e($a['username']) ?></td>
            <td><?= e($a['email']) ?></td>
            <td><span class="a-badge a-badge-off"><?= e(ucwords(str_replace('_', ' ', $a['role']))) ?></span></td>
            <td><span class="a-badge <?= $a['is_active'] ? 'a-badge-on' : 'a-badge-off' ?>"><?= $a['is_active'] ? 'Active' : 'Disabled' ?></span></td>
            <td><?= $a['last_login_at'] ? date('d M Y, h:i A', strtotime($a['last_login_at'])) : 'Never' ?></td>
            <td>
                <?php if ((int) $a['id'] !== Auth::id()): ?>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                    <button type="submit" class="a-btn a-btn-outline a-btn-sm"><?= $a['is_active'] ? 'Disable' : 'Enable' ?></button>
                </form>
                <?php else: ?>
                <span style="color:var(--a-muted); font-size:12px;">(you)</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="a-modal" id="adminModal">
    <div class="a-modal__scrim" data-modal-close></div>
    <div class="a-modal__panel">
        <div class="a-modal__head"><h3>Add Admin User</h3><button class="a-modal__close" data-modal-close>✕</button></div>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <div class="a-field"><label>Full Name</label><input type="text" name="name" class="a-input" required></div>
            <div class="a-field-row">
                <div class="a-field"><label>Username</label><input type="text" name="username" class="a-input" required></div>
                <div class="a-field"><label>Email</label><input type="email" name="email" class="a-input" required></div>
            </div>
            <div class="a-field"><label>Temporary Password</label><input type="password" name="password" class="a-input" minlength="8" required><p class="a-hint">At least 8 characters. Share this securely — they should change it after first login.</p></div>
            <div class="a-field">
                <label>Role</label>
                <select name="role" class="a-select">
                    <option value="staff">Staff — orders & products</option>
                    <option value="manager">Manager — full catalog & orders</option>
                    <option value="super_admin">Super Admin — full access</option>
                </select>
            </div>
            <button type="submit" class="a-btn a-btn-primary" style="width:100%; justify-content:center;">Create Admin</button>
        </form>
    </div>
</div>

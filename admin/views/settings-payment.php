<div class="a-toolbar">
    <p style="color:var(--a-muted); margin:0; font-size:13px;">"Requires a transaction ID at checkout" controls whether customers see and must fill in a reference field for that method — turn it off for anything paid in person, like Cash on Delivery.</p>
</div>

<?php foreach ($methods as $m): ?>
<div class="a-card a-card--solid" style="margin-bottom:16px;">
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h3 style="margin:0;"><?= e($m['name']) ?> <span style="color:var(--a-muted); font-weight:400; font-size:12px;">(<?= e($m['code']) ?>)</span></h3>
            <label class="a-checkbox-row"><input type="checkbox" name="is_active" <?= $m['is_active'] ? 'checked' : '' ?>> Enabled at checkout</label>
        </div>
        <div class="a-field-row">
            <div class="a-field"><label>Display Name</label><input type="text" name="name" class="a-input" value="<?= e($m['name']) ?>"></div>
            <div class="a-field"><label>Account / Number <span style="text-transform:none; font-weight:400;">(leave blank if not needed)</span></label><input type="text" name="account_number" class="a-input" value="<?= e($m['account_number'] ?? '') ?>"></div>
        </div>
        <div class="a-field"><label>Customer Instructions</label><textarea name="instructions" class="a-input" rows="2"><?= e($m['instructions'] ?? '') ?></textarea></div>
        <label class="a-checkbox-row" style="margin-bottom:12px;">
            <input type="checkbox" name="requires_reference" <?= $m['requires_reference'] ? 'checked' : '' ?>>
            Requires a transaction ID at checkout
        </label>
        <?php if (!$m['requires_reference']): ?>
            <p class="a-hint" style="margin-top:-8px; margin-bottom:12px;">Off — customers won't see or need to enter a transaction ID for this method.</p>
        <?php endif; ?>
        <button type="submit" class="a-btn a-btn-outline a-btn-sm">Save</button>
    </form>
</div>
<?php endforeach; ?>

<div class="a-card a-card--solid">
    <h3 style="margin-bottom:14px;">Add Another Payment Method</h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="a-field-row">
            <div class="a-field"><label>Display Name</label><input type="text" name="name" class="a-input" placeholder="e.g. Rocket" required></div>
            <div class="a-field"><label>Account / Number</label><input type="text" name="account_number" class="a-input"></div>
        </div>
        <div class="a-field"><label>Customer Instructions</label><textarea name="instructions" class="a-input" rows="2"></textarea></div>
        <label class="a-checkbox-row"><input type="checkbox" name="is_active" checked> Enabled at checkout</label>
        <label class="a-checkbox-row" style="margin-bottom:12px;">
            <input type="checkbox" name="requires_reference" checked>
            Requires a transaction ID at checkout
        </label>
        <button type="submit" class="a-btn a-btn-primary a-btn-sm">Add Payment Method</button>
    </form>
</div>

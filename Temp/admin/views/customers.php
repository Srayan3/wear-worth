<div class="a-toolbar">
    <form method="get" class="a-toolbar__filters">
        <input type="text" name="search" class="a-input" placeholder="Search name, phone, or email…" value="<?= e($search) ?>" style="width:260px;">
        <button type="submit" class="a-btn a-btn-outline a-btn-sm">Search</button>
    </form>
</div>

<h3 style="margin:0 0 12px;">Registered Accounts</h3>
<div class="a-table-wrap" style="margin-bottom:30px;">
    <table class="a-table">
        <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Orders</th><th>Total Spent</th><th>Last Order</th></tr></thead>
        <tbody>
        <?php if (empty($registered)): ?><tr><td colspan="6" class="a-empty">No registered customers yet.</td></tr><?php endif; ?>
        <?php foreach ($registered as $c): ?>
        <tr>
            <td style="font-weight:600;"><?= e($c['full_name']) ?></td>
            <td><?= e($c['phone']) ?></td>
            <td><?= e($c['email'] ?: '—') ?></td>
            <td><?= (int) $c['order_count'] ?></td>
            <td><?= money($c['total_spent']) ?></td>
            <td><?= $c['last_order_at'] ? date('d M Y', strtotime($c['last_order_at'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h3 style="margin:0 0 12px;">Guest Checkouts</h3>
<div class="a-table-wrap">
    <table class="a-table">
        <thead><tr><th>Name</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Last Order</th></tr></thead>
        <tbody>
        <?php if (empty($guests)): ?><tr><td colspan="5" class="a-empty">No guest orders yet.</td></tr><?php endif; ?>
        <?php foreach ($guests as $g): ?>
        <tr>
            <td style="font-weight:600;"><?= e($g['full_name']) ?></td>
            <td><?= e($g['phone']) ?></td>
            <td><?= (int) $g['order_count'] ?></td>
            <td><?= money($g['total_spent']) ?></td>
            <td><?= $g['last_order_at'] ? date('d M Y', strtotime($g['last_order_at'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

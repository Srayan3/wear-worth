<div class="a-toolbar">
    <form method="get" class="a-toolbar__filters">
        <input type="text" name="search" class="a-input" placeholder="Order #, name, or phone…" value="<?= e($filters['search']) ?>" style="width:220px;">
        <select name="status_id" class="a-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= (string) $filters['status_id'] === (string) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" class="a-input" value="<?= e($filters['date_from']) ?>">
        <input type="date" name="date_to" class="a-input" value="<?= e($filters['date_to']) ?>">
        <button type="submit" class="a-btn a-btn-outline a-btn-sm">Filter</button>
        <a href="<?= admin_url('orders.php') ?>" class="a-btn a-btn-outline a-btn-sm">Reset</a>
    </form>
</div>

<div class="a-table-wrap">
    <table class="a-table">
        <thead><tr><th>Order</th><th>Customer</th><th>Phone</th><th>Payment</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($result['items'])): ?>
            <tr><td colspan="8" class="a-empty">No orders found.</td></tr>
        <?php endif; ?>
        <?php foreach ($result['items'] as $o): ?>
            <tr>
                <td style="font-weight:600;"><?= e($o['order_number']) ?></td>
                <td><?= e($o['full_name']) ?></td>
                <td><?= e($o['phone']) ?></td>
                <td><?= e($o['payment_method_name']) ?><br><span class="a-badge <?= $o['payment_status'] === 'paid' ? 'a-badge-on' : 'a-badge-warn' ?>" style="margin-top:4px;"><?= e(ucfirst($o['payment_status'])) ?></span></td>
                <td><?= money($o['total']) ?></td>
                <td><span class="status-pill" style="background:<?= e($o['status_color']) ?>;"><?= e($o['status_name']) ?></span></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td><a href="<?= admin_url('order-detail.php?id=' . $o['id']) ?>" class="a-btn a-btn-outline a-btn-sm">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $pagination = $result; include ROOT_PATH . '/admin/views/partials/pagination.php'; ?>

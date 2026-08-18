<div class="container" style="padding:50px 0 90px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1 style="margin:0;">My Orders</h1>
        <form method="post" action="<?= url('account/logout') ?>"><?= csrf_field() ?><button type="submit" class="btn btn-ghost btn-sm">Sign Out</button></form>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <h2>No orders yet</h2>
            <p>When you place an order, it'll show up here.</p>
            <a href="<?= url('shop') ?>" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
    <table class="order-table">
        <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= e($order['order_number']) ?></td>
                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                <td><span class="status-pill" style="background:<?= e($order['status_color']) ?>;"><?= e($order['status_name']) ?></span></td>
                <td><?= money($order['total']) ?></td>
                <td><a href="<?= url('account/orders/' . $order['order_number']) ?>" class="btn btn-ghost btn-sm">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="auth-page" style="max-width:520px;">
    <h1>Track Your Order</h1>
    <p class="lead">Enter the phone number you used at checkout to see your orders, or enter your order number directly.</p>
    <form method="post" action="<?= url('track-order') ?>">
        <?= csrf_field() ?>
        <div class="field">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="input" required autofocus>
        </div>
        <div class="field">
            <label for="order_number">Order Number <span style="text-transform:none; font-weight:400;">(optional)</span></label>
            <input type="text" id="order_number" name="order_number" class="input" placeholder="e.g. ORD260815AB12C">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Find My Orders</button>
    </form>

    <?php if (!empty($searched)): ?>
        <?php if (empty($orders)): ?>
            <p style="margin-top:26px; color:var(--text-muted); text-align:center;">No orders found for that phone number.</p>
        <?php else: ?>
        <table class="order-table" style="margin-top:30px;">
            <thead><tr><th>Order</th><th>Date</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= e($order['order_number']) ?></td>
                    <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                    <td><span class="status-pill" style="background:<?= e($order['status_color']) ?>;"><?= e($order['status_name']) ?></span></td>
                    <td><a href="<?= url('account/orders/' . $order['order_number']) ?>" class="btn btn-ghost btn-sm">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

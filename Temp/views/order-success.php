<div class="container" style="padding:60px 0 100px; max-width:760px;">
    <div style="text-align:center; margin-bottom:44px;">
        <span class="eyebrow" style="color:var(--success);">Order Confirmed</span>
        <h1 style="margin-top:10px;">Thank you, <?= e(explode(' ', $order['full_name'])[0]) ?>.</h1>
        <p style="color:var(--text-muted);">Your order <strong><?= e($order['order_number']) ?></strong> has been placed. We'll reach out at <?= e($order['phone']) ?> to confirm delivery.</p>
    </div>

    <div class="summary-card" style="margin-bottom:24px;">
        <div class="order-detail-head">
            <div>
                <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">Order Number</div>
                <div style="font-weight:600;"><?= e($order['order_number']) ?></div>
            </div>
            <span class="status-pill" style="background:<?= e($order['status_color']) ?>;"><?= e($order['status_name']) ?></span>
        </div>
        <?php foreach ($order['items'] as $item): ?>
        <div class="checkout-summary-line">
            <div style="flex:1;">
                <div style="font-weight:600;"><?= e($item['product_name_snapshot']) ?></div>
                <div style="color:var(--text-muted); font-size:12px;"><?= e($item['variation_label_snapshot'] ?? '') ?> · Qty <?= (int) $item['quantity'] ?></div>
            </div>
            <div><?= money($item['line_total']) ?></div>
        </div>
        <?php endforeach; ?>
        <div class="summary-row" style="margin-top:12px;"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
        <div class="summary-row"><span>Delivery</span><span><?= money($order['delivery_charge']) ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?= money($order['total']) ?></span></div>
    </div>

    <div class="summary-card" style="margin-bottom:32px;">
        <h3>Delivery To</h3>
        <p style="margin:0; font-size:14px; color:var(--ink-soft);">
            <?= e($order['full_name']) ?><br>
            <?= e($order['address']) ?>, <?= e($order['area']) ?>, <?= e($order['district']) ?><br>
            <?= e($order['phone']) ?>
        </p>
        <p style="margin-top:14px; font-size:13px;"><strong>Payment:</strong> <?= e($order['payment_method_name']) ?></p>
    </div>

    <div style="text-align:center;">
        <a href="<?= url('shop') ?>" class="btn btn-primary">Continue Shopping</a>
        <a href="<?= url('track-order') ?>" class="btn btn-ghost">Track This Order</a>
    </div>
</div>

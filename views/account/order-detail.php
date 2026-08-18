<div class="container" style="padding:50px 0 90px; max-width:800px;">
    <a href="<?= url(CustomerAuth::check() ? 'account/orders' : 'track-order') ?>" class="btn btn-ghost btn-sm" style="margin-bottom:20px;">← Back</a>

    <div class="order-detail-head">
        <div>
            <h1 style="margin-bottom:4px;">Order <?= e($order['order_number']) ?></h1>
            <p style="color:var(--text-muted); font-size:13.5px;">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
        </div>
        <span class="status-pill" style="background:<?= e($order['status_color']) ?>;"><?= e($order['status_name']) ?></span>
    </div>

    <div class="cart-layout" style="grid-template-columns: 1fr 340px;">
        <div>
            <div class="summary-card" style="margin-bottom:20px;">
                <h3>Items</h3>
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

            <div class="summary-card">
                <h3>Delivery Address</h3>
                <p style="margin:0; font-size:14px; color:var(--ink-soft);">
                    <?= e($order['full_name']) ?><br>
                    <?= e($order['address']) ?>, <?= e($order['area']) ?>, <?= e($order['district']) ?><br>
                    <?= e($order['phone']) ?>
                </p>
            </div>
        </div>

        <div class="summary-card">
            <h3>Order Timeline</h3>
            <ul class="order-timeline">
                <?php foreach ($order['history'] as $h): ?>
                <li>
                    <div>
                        <strong style="font-size:13.5px;"><?= e($h['status_name']) ?></strong>
                        <?php if ($h['note']): ?><div style="font-size:12.5px; color:var(--text-muted);"><?= e($h['note']) ?></div><?php endif; ?>
                        <time><?= date('d M, h:i A', strtotime($h['created_at'])) ?></time>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

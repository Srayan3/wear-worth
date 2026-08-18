<a href="<?= admin_url('orders.php') ?>" class="a-btn a-btn-outline a-btn-sm" style="margin-bottom:18px;">← All Orders</a>

<div style="display:grid; grid-template-columns:1.5fr 1fr; gap:20px; align-items:start;">
    <div>
        <div class="a-card a-card--solid" style="margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                <div>
                    <h3 style="font-size:18px; margin-bottom:2px;"><?= e($order['order_number']) ?></h3>
                    <span style="font-size:12.5px; color:var(--a-muted);">Placed <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <a href="<?= admin_url('order-invoice.php?id=' . $order['id']) ?>" target="_blank" class="a-btn a-btn-outline a-btn-sm">Print Invoice</a>
            </div>

            <table class="a-table">
                <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                <tbody>
                <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td>
                        <?= e($item['product_name_snapshot']) ?>
                        <?php if ($item['variation_label_snapshot']): ?><br><span style="color:var(--a-muted); font-size:12px;"><?= e($item['variation_label_snapshot']) ?></span><?php endif; ?>
                    </td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= money($item['price_snapshot']) ?></td>
                    <td><?= money($item['line_total']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--a-border); max-width:280px; margin-left:auto;">
                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:13px;"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:13px;"><span>Delivery</span><span><?= money($order['delivery_charge']) ?></span></div>
                <div style="display:flex; justify-content:space-between; padding:8px 0 0; margin-top:4px; border-top:1px solid var(--a-border); font-weight:700;"><span>Total</span><span><?= money($order['total']) ?></span></div>
            </div>
        </div>

        <div class="a-card a-card--solid" style="margin-bottom:18px;">
            <h3 style="margin-bottom:14px;">Customer & Delivery</h3>
            <div class="a-field-row">
                <div><strong style="font-size:13px;">Name</strong><p style="margin:2px 0 0; font-size:13.5px;"><?= e($order['full_name']) ?></p></div>
                <div><strong style="font-size:13px;">Phone</strong><p style="margin:2px 0 0; font-size:13.5px;"><?= e($order['phone']) ?></p></div>
            </div>
            <div class="a-field-row">
                <div><strong style="font-size:13px;">Email</strong><p style="margin:2px 0 0; font-size:13.5px;"><?= e($order['email'] ?: '—') ?></p></div>
                <div><strong style="font-size:13px;">District / Area</strong><p style="margin:2px 0 0; font-size:13.5px;"><?= e($order['district']) ?> / <?= e($order['area']) ?></p></div>
            </div>
            <strong style="font-size:13px;">Address</strong><p style="margin:2px 0 0; font-size:13.5px;"><?= e($order['address']) ?></p>
            <?php if ($order['order_notes']): ?>
                <strong style="font-size:13px; display:block; margin-top:12px;">Customer Notes</strong><p style="margin:2px 0 0; font-size:13.5px;"><?= e($order['order_notes']) ?></p>
            <?php endif; ?>
        </div>

        <div class="a-card a-card--solid">
            <h3 style="margin-bottom:10px;">Internal Notes</h3>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_notes">
                <textarea name="admin_notes" class="a-input" rows="3" placeholder="Notes only visible to staff…"><?= e($order['admin_notes'] ?? '') ?></textarea>
                <button type="submit" class="a-btn a-btn-outline a-btn-sm" style="margin-top:10px;">Save Notes</button>
            </form>
        </div>
    </div>

    <div>
        <div class="a-card a-card--solid" style="margin-bottom:18px;">
            <h3 style="margin-bottom:12px;">Order Status</h3>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_status">
                <div class="a-field">
                    <select name="status_id" class="a-select">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= (int) $s['id'] ?>" <?= (int) $order['status_id'] === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="a-field"><input type="text" name="note" class="a-input" placeholder="Optional note (e.g. courier tracking ID)"></div>
                <button type="submit" class="a-btn a-btn-primary a-btn-sm" style="width:100%; justify-content:center;">Update Status</button>
            </form>
        </div>

        <div class="a-card a-card--solid" style="margin-bottom:18px;">
            <h3 style="margin-bottom:12px;">Payment</h3>
            <p style="font-size:13px; margin-bottom:10px;"><strong><?= e($order['payment_method_name']) ?></strong></p>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_payment_status">
                <div class="a-field">
                    <select name="payment_status" class="a-select">
                        <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>
                <button type="submit" class="a-btn a-btn-outline a-btn-sm" style="width:100%; justify-content:center;">Update Payment Status</button>
            </form>
        </div>

        <div class="a-card a-card--solid">
            <h3 style="margin-bottom:14px;">Timeline</h3>
            <ul class="order-timeline" style="list-style:none; margin:0; padding:0;">
                <?php foreach (array_reverse($order['history']) as $h): ?>
                <li style="padding-bottom:16px; font-size:13px;">
                    <strong><?= e($h['status_name']) ?></strong>
                    <?php if ($h['note']): ?><div style="color:var(--a-muted); font-size:12px;"><?= e($h['note']) ?></div><?php endif; ?>
                    <div style="color:var(--a-muted); font-size:11.5px;"><?= date('d M, h:i A', strtotime($h['created_at'])) ?> · <?= e($h['changed_by']) ?></div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

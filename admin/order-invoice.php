<?php
require_once __DIR__ . '/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$order = Order::find($id);
if (!$order) {
    http_response_code(404);
    die('Order not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice — <?= e($order['order_number']) ?></title>
<meta name="robots" content="noindex, nofollow">
<style>
    body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #14120F; padding: 50px; max-width: 800px; margin: 0 auto; font-size: 13.5px; }
    .row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
    h1 { font-size: 22px; margin: 0 0 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 24px; }
    th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #E4DFD6; }
    th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #83786A; }
    .totals { width: 260px; margin-left: auto; margin-top: 16px; }
    .totals div { display: flex; justify-content: space-between; padding: 5px 0; }
    .totals .grand { font-weight: 700; font-size: 15px; border-top: 1px solid #14120F; padding-top: 10px; margin-top: 6px; }
    .print-btn { margin-top: 30px; }
    @media print { .print-btn { display: none; } }
</style>
</head>
<body>
    <div class="row">
        <div>
            <h1><?= e(setting('store_name', 'Atelier')) ?></h1>
            <div><?= e(setting('store_address', '')) ?></div>
            <div><?= e(setting('store_phone', '')) ?> · <?= e(setting('store_email', '')) ?></div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:18px; font-weight:700;">INVOICE</div>
            <div><?= e($order['order_number']) ?></div>
            <div><?= date('d M Y', strtotime($order['created_at'])) ?></div>
        </div>
    </div>

    <div class="row">
        <div>
            <strong>Bill To</strong><br>
            <?= e($order['full_name']) ?><br>
            <?= e($order['address']) ?>, <?= e($order['area']) ?>, <?= e($order['district']) ?><br>
            <?= e($order['phone']) ?>
        </div>
        <div style="text-align:right;">
            <strong>Payment Method</strong><br><?= e($order['payment_method_name']) ?><br>
            <strong>Status</strong><br><?= e($order['status_name']) ?>
        </div>
    </div>

    <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th style="text-align:right;">Total</th></tr></thead>
        <tbody>
        <?php foreach ($order['items'] as $item): ?>
        <tr>
            <td><?= e($item['product_name_snapshot']) ?><?= $item['variation_label_snapshot'] ? ' — ' . e($item['variation_label_snapshot']) : '' ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td><?= money($item['price_snapshot']) ?></td>
            <td style="text-align:right;"><?= money($item['line_total']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <div><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
        <div><span>Delivery</span><span><?= money($order['delivery_charge']) ?></span></div>
        <div class="grand"><span>Total</span><span><?= money($order['total']) ?></span></div>
    </div>

    <button class="print-btn" onclick="window.print()">Print</button>
</body>
</html>

<?php
$maxSale = max([1, ...array_column($stats['salesLast7Days'], 'total')]);
$statusCounts = $stats['counts'];
?>
<div class="stat-grid">
    <div class="a-card stat-card">
        <div class="label">Total Orders</div>
        <div class="value"><?= number_format($stats['totalOrders']) ?></div>
        <div class="sub"><?= number_format($statusCounts['pending'] ?? 0) ?> pending</div>
    </div>
    <div class="a-card stat-card">
        <div class="label">Today's Sales</div>
        <div class="value"><?= money($stats['todaySales']) ?></div>
        <div class="sub">This month: <?= money($stats['monthSales']) ?></div>
    </div>
    <div class="a-card stat-card">
        <div class="label">Total Sales</div>
        <div class="value"><?= money($stats['totalSales']) ?></div>
        <div class="sub">All-time revenue</div>
    </div>
    <div class="a-card stat-card">
        <div class="label">Products</div>
        <div class="value"><?= number_format($stats['totalProducts']) ?></div>
        <div class="sub" style="color:<?= $stats['lowStock'] > 0 ? 'var(--a-warning)' : 'var(--a-muted)' ?>;"><?= number_format($stats['lowStock']) ?> low stock</div>
    </div>
</div>

<div class="stat-grid" style="grid-template-columns: repeat(6, 1fr);">
    <?php
    $labels = ['pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled', 'returned' => 'Returned'];
    foreach ($labels as $slug => $label):
    ?>
    <div class="a-card stat-card" style="padding:16px;">
        <div class="label"><?= e($label) ?></div>
        <div class="value" style="font-size:22px;"><?= number_format($statusCounts[$slug] ?? 0) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div style="display:grid; grid-template-columns: 1.4fr 1fr; gap:20px; align-items:start;">
    <div class="a-card a-card--solid">
        <h3 style="margin-bottom:18px;">Sales — Last 7 Days</h3>
        <div style="display:flex; align-items:flex-end; gap:14px; height:160px;">
            <?php foreach ($stats['salesLast7Days'] as $day): ?>
                <?php $h = max(4, ($day['total'] / $maxSale) * 140); ?>
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                    <div title="<?= money($day['total']) ?>" style="width:100%; max-width:36px; height:<?= $h ?>px; background:var(--a-ink); border-radius:4px 4px 0 0;"></div>
                    <span style="font-size:10.5px; color:var(--a-muted);"><?= date('D', strtotime($day['d'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="a-card a-card--solid">
        <h3 style="margin-bottom:14px;">Recent Orders</h3>
        <?php if (empty($stats['recentOrders'])): ?>
            <p class="a-empty" style="padding:20px 0;">No orders yet.</p>
        <?php else: ?>
        <?php foreach ($stats['recentOrders'] as $o): ?>
            <a href="<?= admin_url('order-detail.php?id=' . $o['id']) ?>" style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--a-border);">
                <div>
                    <div style="font-weight:600; font-size:13px;"><?= e($o['order_number']) ?></div>
                    <div style="font-size:11.5px; color:var(--a-muted);"><?= e($o['full_name']) ?> · <?= date('d M', strtotime($o['created_at'])) ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:600; font-size:13px;"><?= money($o['total']) ?></div>
                    <span class="status-pill" style="background:<?= e($o['status_color']) ?>; font-size:9.5px;"><?= e($o['status_name']) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
        <a href="<?= admin_url('orders.php') ?>" class="a-btn a-btn-outline a-btn-sm" style="margin-top:16px; width:100%; justify-content:center;">View All Orders</a>
        <?php endif; ?>
    </div>
</div>

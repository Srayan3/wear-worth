<div class="a-toolbar">
    <form method="get" class="a-toolbar__filters">
        <input type="text" name="search" class="a-input" placeholder="Search name or SKU…" value="<?= e($filters['search']) ?>" style="width:220px;">
        <select name="subcategory_id" class="a-select" onchange="this.form.submit()">
            <option value="">All Subcategories</option>
            <?php foreach ($subcategories as $sub): ?>
                <option value="<?= (int) $sub['id'] ?>" <?= (string) $filters['subcategory_id'] === (string) $sub['id'] ? 'selected' : '' ?>><?= e($sub['category_name']) ?> → <?= e($sub['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="is_active" class="a-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
        <label class="a-checkbox-row"><input type="checkbox" name="low_stock" value="1" onchange="this.form.submit()" <?= $filters['low_stock'] ? 'checked' : '' ?>> Low stock</label>
        <button type="submit" class="a-btn a-btn-outline a-btn-sm">Filter</button>
    </form>
    <a href="<?= admin_url('product-form.php') ?>" class="a-btn a-btn-primary">+ Add Product</a>
</div>

<div class="a-table-wrap">
    <table class="a-table">
        <thead>
        <tr>
            <th></th><th>Product</th><th>SKU</th><th>Subcategory</th><th>Price</th><th>Stock</th><th>Flags</th><th>Status</th><th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($result['items'])): ?>
            <tr><td colspan="9" class="a-empty">No products found.</td></tr>
        <?php endif; ?>
        <?php foreach ($result['items'] as $p): ?>
            <tr>
                <td><img class="a-thumb" src="<?= product_image_url($p['primary_image']) ?>" alt=""></td>
                <td><a href="<?= admin_url('product-form.php?id=' . $p['id']) ?>" style="font-weight:600;"><?= e($p['name']) ?></a></td>
                <td style="color:var(--a-muted);"><?= e($p['sku']) ?></td>
                <td><?= e($p['subcategory_name']) ?></td>
                <td>
                    <?php if ($p['discount_price']): ?>
                        <span style="text-decoration:line-through; color:var(--a-muted); font-size:12px;"><?= money($p['price']) ?></span><br><?= money($p['discount_price']) ?>
                    <?php else: ?>
                        <?= money($p['price']) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" style="display:flex; gap:6px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_stock">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <input type="number" name="stock_quantity" value="<?= (int) $p['stock_quantity'] ?>" class="a-input" style="width:64px; padding:6px 8px;" min="0">
                        <button type="submit" class="a-btn a-btn-outline a-btn-sm">Set</button>
                    </form>
                </td>
                <td>
                    <div style="display:flex; gap:5px; flex-wrap:wrap;">
                        <?php
                        $flagBtns = [
                            'is_featured' => ['toggle_featured', 'Featured'],
                            'is_new_arrival' => ['toggle_new', 'New'],
                            'is_popular' => ['toggle_popular', 'Popular'],
                        ];
                        foreach ($flagBtns as $col => [$action, $label]): ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="<?= $action ?>">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="a-badge <?= $p[$col] ? 'a-badge-on' : 'a-badge-off' ?>" style="border:none; cursor:pointer;"><?= $label ?></button>
                        </form>
                        <?php endforeach; ?>
                    </div>
                </td>
                <td>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="a-badge <?= $p['is_active'] ? 'a-badge-on' : 'a-badge-off' ?>" style="border:none; cursor:pointer;"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></button>
                    </form>
                </td>
                <td>
                    <div style="display:flex; gap:6px;">
                        <a href="<?= admin_url('product-form.php?id=' . $p['id']) ?>" class="a-btn a-btn-outline a-btn-sm">Edit</a>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="duplicate"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button type="submit" class="a-btn a-btn-outline a-btn-sm">Copy</button></form>
                        <form method="post" data-confirm="Delete this product permanently?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button type="submit" class="a-btn a-btn-danger a-btn-sm">Delete</button></form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $pagination = $result; include ROOT_PATH . '/admin/views/partials/pagination.php'; ?>

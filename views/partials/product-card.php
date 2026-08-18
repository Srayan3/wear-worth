<?php
/** Expects $product (array) in scope. Include via: foreach(...) { $product = $p; include ...; } */
$discountPct = !empty($product['discount_price'])
    ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100)
    : 0;
?>
<article class="product-card">
    <div class="product-card__media">
        <div class="product-card__badges">
            <?php if (!empty($product['is_new_arrival'])): ?><span class="badge">New</span><?php endif; ?>
            <?php if ($discountPct > 0): ?><span class="badge badge--accent">-<?= $discountPct ?>%</span><?php endif; ?>
            <?php if ($product['stock_status'] === 'out_of_stock'): ?><span class="badge badge--muted">Sold Out</span><?php endif; ?>
        </div>
        <a href="<?= url('product/' . $product['slug']) ?>" aria-label="<?= e($product['name']) ?>">
            <img class="img-primary" src="<?= product_image_url($product['primary_image'] ?? null) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
            <?php if (!empty($product['secondary_image'])): ?>
                <img class="img-secondary" src="<?= product_image_url($product['secondary_image']) ?>" alt="" loading="lazy">
            <?php endif; ?>
        </a>
        <?php if ($product['stock_status'] === 'in_stock'): ?>
        <form method="post" action="<?= url('cart/add') ?>" class="product-card__quickadd" data-add-to-cart-form>
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <?php if (empty($product['has_variations'])): ?>
            <button type="submit" class="btn btn-primary btn-sm btn-block">Quick Add</button>
            <?php else: ?>
            <a href="<?= url('product/' . $product['slug']) ?>" class="btn btn-outline btn-sm btn-block" style="background:var(--surface-glass);backdrop-filter:blur(8px);">Select Options</a>
            <?php endif; ?>
        </form>
        <?php endif; ?>
    </div>
    <a href="<?= url('product/' . $product['slug']) ?>">
        <div class="product-card__meta"><?= e($product['subcategory_name'] ?? '') ?></div>
        <div class="product-card__title"><?= e($product['name']) ?></div>
        <div class="product-card__price">
            <?php if (!empty($product['discount_price'])): ?>
                <span class="price-current"><?= money($product['discount_price']) ?></span>
                <span class="price-old"><?= money($product['price']) ?></span>
            <?php else: ?>
                <span class="price-current"><?= money($product['price']) ?></span>
            <?php endif; ?>
        </div>
    </a>
</article>

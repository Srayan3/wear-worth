<?php
$discountPct = !empty($product['discount_price'])
    ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100)
    : 0;
$displayPrice = $product['discount_price'] ?: $product['price'];
$hasVariations = !empty($product['variations']);
?>
<div class="container">
    <div class="breadcrumbs" style="padding-top:26px;">
        <a href="<?= url('') ?>">Home</a><span class="sep">/</span>
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <?php if ($crumb['url']): ?><a href="<?= url(ltrim($crumb['url'], '/')) ?>"><?= e($crumb['label']) ?></a><?php else: ?><span><?= e($crumb['label']) ?></span><?php endif; ?>
            <?php if ($i < count($breadcrumbs) - 1): ?><span class="sep">/</span><?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="product-detail" data-product-root data-variations='<?= e(json_encode($product['variations'])) ?>'>
        <div class="gallery">
            <div class="gallery__main">
                <?php foreach ($product['images'] as $i => $img): ?>
                    <img src="<?= product_image_url($img['image_path']) ?>" data-index="<?= $i ?>" alt="<?= e($product['name']) ?>" class="<?= $i === 0 ? 'is-active' : '' ?>">
                <?php endforeach; ?>
                <?php if (empty($product['images'])): ?>
                    <img src="<?= product_image_url(null) ?>" class="is-active" alt="<?= e($product['name']) ?>">
                <?php endif; ?>
            </div>
            <?php if (count($product['images']) > 1): ?>
            <div class="gallery__thumbs">
                <?php foreach ($product['images'] as $i => $img): ?>
                    <button type="button" data-index="<?= $i ?>" class="<?= $i === 0 ? 'is-active' : '' ?>">
                        <img src="<?= product_image_url($img['image_path']) ?>" alt="">
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="product-info__eyebrow">
                <span class="eyebrow"><?= e($product['subcategory_name']) ?></span>
                <?php if (!empty($product['is_new_arrival'])): ?><span class="badge">New</span><?php endif; ?>
            </div>
            <h1><?= e($product['name']) ?></h1>
            <div class="product-info__price">
                <span class="price-current"><?= money($displayPrice) ?></span>
                <?php if ($discountPct > 0): ?>
                    <span class="price-old"><?= money($product['price']) ?></span>
                    <span class="price-discount-pct">Save <?= $discountPct ?>%</span>
                <?php endif; ?>
            </div>
            <?php if ($product['short_description']): ?><p class="product-info__short"><?= e($product['short_description']) ?></p><?php endif; ?>

            <p class="stock-note <?= $product['stock_status'] === 'in_stock' ? 'in' : 'out' ?>" data-stock-note>
                <?= $product['stock_status'] === 'in_stock' ? 'In stock' : 'Out of stock' ?>
            </p>

            <form method="post" action="<?= url('cart/add') ?>" data-add-to-cart-form>
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <input type="hidden" name="variation_id" value="">

                <?php if (!empty($product['sizes'])): ?>
                <div class="option-group">
                    <h4>Size <span class="selected-value" data-selected-size></span></h4>
                    <div class="swatch-row">
                        <?php foreach ($product['sizes'] as $size): ?>
                            <button type="button" class="swatch-size" data-size-option="<?= e($size) ?>"><?= e($size) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($product['colors'])): ?>
                <div class="option-group">
                    <h4>Color <span class="selected-value" data-selected-color></span></h4>
                    <div class="swatch-row">
                        <?php foreach ($product['colors'] as $color): ?>
                            <button type="button" class="swatch-color" data-color-option="<?= e($color['name']) ?>"
                                style="background:<?= e($color['hex'] ?: '#ccc') ?>" title="<?= e($color['name']) ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="option-group">
                    <h4>Quantity</h4>
                    <div class="qty-stepper" data-qty-stepper>
                        <button type="button" data-step="-1" aria-label="Decrease quantity">–</button>
                        <input type="text" name="quantity" value="1" inputmode="numeric" data-qty-input readonly>
                        <button type="button" data-step="1" aria-label="Increase quantity">+</button>
                    </div>
                </div>

                <div class="product-actions">
                    <button type="submit" class="btn btn-primary" data-add-to-cart-btn <?= $product['stock_status'] !== 'in_stock' ? 'disabled' : '' ?>>
                        <?= $product['stock_status'] === 'in_stock' ? 'Add to Bag' : 'Out of Stock' ?>
                    </button>
                    <button type="submit" class="btn btn-outline" data-buy-now <?= $product['stock_status'] !== 'in_stock' ? 'disabled' : '' ?>>Buy Now</button>
                </div>
            </form>

            <div class="trust-mini">
                <div class="trust-mini__row">
                    <svg viewBox="0 0 24 24"><rect x="2" y="7" width="15" height="12" rx="1"/><path d="M17 10h3l2 3v6h-5"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
                    <span><?= e(setting('trust_delivery_text', '')) ?></span>
                </div>
                <div class="trust-mini__row">
                    <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg>
                    <span><?= e(setting('trust_return_text', '')) ?></span>
                </div>
                <div class="trust-mini__row">
                    <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                    <span><?= e(setting('trust_payment_text', '')) ?></span>
                </div>
            </div>

            <div class="tabs">
                <div class="tabs__nav">
                    <button type="button" class="is-active" data-tab="tabDescription">Description</button>
                    <?php if (!empty($product['size_chart'])): ?><button type="button" data-tab="tabSizeChart">Size & Fit</button><?php endif; ?>
                </div>
                <div class="tabs__panel is-active" id="tabDescription">
                    <?= nl2br(e($product['description'] ?: $product['short_description'])) ?>
                </div>
                <?php if (!empty($product['size_chart'])): ?>
                <div class="tabs__panel" id="tabSizeChart">
                    <p>All measurements in inches, taken flat.</p>
                    <table class="size-chart-table">
                        <thead><tr><th>Size</th><th>Chest</th><th>Waist</th><th>Hip</th><th>Length</th></tr></thead>
                        <tbody>
                        <?php foreach ($product['size_chart'] as $row): ?>
                            <tr>
                                <td><?= e($row['size_label']) ?></td>
                                <td><?= $row['chest_in'] !== null ? e($row['chest_in']) . '"' : '—' ?></td>
                                <td><?= $row['waist_in'] !== null ? e($row['waist_in']) . '"' : '—' ?></td>
                                <td><?= $row['hip_in'] !== null ? e($row['hip_in']) . '"' : '—' ?></td>
                                <td><?= $row['length_in'] !== null ? e($row['length_in']) . '"' : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <section class="related-products">
        <div class="section-head"><div><span class="eyebrow">You Might Also Like</span><h2>Related Pieces</h2></div></div>
        <div class="product-grid">
            <?php foreach ($related as $product): include ROOT_PATH . '/views/partials/product-card.php'; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
<?php $extraScripts = ['assets/js/product.js']; ?>

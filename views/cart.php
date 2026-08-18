<div class="container cart-page">
    <h1>Your Bag</h1>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <h2>Your bag is empty</h2>
            <p>Explore the collection and find something you love.</p>
            <a href="<?= url('shop') ?>" class="btn btn-primary">Shop All</a>
        </div>
    <?php else: ?>
    <div class="cart-layout">
        <div>
            <div class="cart-list">
                <?php foreach ($items as $item): ?>
                <div class="cart-row">
                    <div class="cart-row__media"><img src="<?= product_image_url($item['image']) ?>" alt="<?= e($item['name']) ?>"></div>
                    <div>
                        <div class="cart-row__title"><?= e($item['name']) ?></div>
                        <div class="cart-row__meta">
                            <?= e(trim(implode(' / ', array_filter([$item['size'], $item['color']])))) ?>
                            · <?= money($item['unit_price']) ?> each
                            <?php if (!$item['available']): ?><br><span style="color:var(--danger);">No longer available</span><?php endif; ?>
                        </div>
                        <form method="post" action="<?= url('cart/update') ?>" style="display:inline-flex; gap:10px; align-items:center;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                            <div class="qty-stepper">
                                <button type="button" onclick="this.nextElementSibling.stepDown(); this.form.requestSubmit()" aria-label="Decrease">–</button>
                                <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="0" max="<?= (int) $item['stock'] ?>" onchange="this.form.requestSubmit()">
                                <button type="button" onclick="this.previousElementSibling.stepUp(); this.form.requestSubmit()" aria-label="Increase">+</button>
                            </div>
                            <noscript><button type="submit" class="btn btn-ghost btn-sm">Update</button></noscript>
                        </form>
                        <form method="post" action="<?= url('cart/remove') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                            <button type="submit" class="cart-line__remove" style="margin-left:16px;">Remove</button>
                        </form>
                    </div>
                    <div class="cart-row__price"><?= money($item['line_total']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="<?= url('shop') ?>" class="btn btn-ghost" style="margin-top:20px;">← Continue Shopping</a>
        </div>

        <div class="summary-card">
            <h3>Order Summary</h3>
            <div class="summary-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
            <div class="summary-row"><span>Delivery</span><span>Calculated at checkout</span></div>
            <div class="summary-row total"><span>Estimated Total</span><span><?= money($subtotal) ?></span></div>
            <a href="<?= url('checkout') ?>" class="btn btn-primary btn-block" style="margin-top:18px;">Proceed to Checkout</a>
        </div>
    </div>
    <?php endif; ?>
</div>

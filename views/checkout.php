<div class="container checkout-page">
    <h1 style="margin-bottom:34px;">Checkout</h1>
    <form method="post" action="<?= url('checkout') ?>" id="checkoutForm">
        <?= csrf_field() ?>
        <div class="checkout-layout">
            <div>
                <div class="checkout-section">
                    <h2>Contact & Delivery</h2>
                    <div class="field-row">
                        <div class="field">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="input" value="<?= old('full_name') ?>" required>
                        </div>
                        <div class="field">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="input" value="<?= old('phone') ?>" placeholder="01XXXXXXXXX" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="email">Email <span style="text-transform:none; font-weight:400;">(optional)</span></label>
                        <input type="email" id="email" name="email" class="input" value="<?= old('email') ?>">
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="district">District</label>
                            <input type="text" id="district" name="district" class="input" value="<?= old('district') ?>" placeholder="e.g. Dhaka" required>
                        </div>
                        <div class="field">
                            <label for="area">Area</label>
                            <input type="text" id="area" name="area" class="input" value="<?= old('area') ?>" placeholder="e.g. Gulshan" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="address">Full Delivery Address</label>
                        <textarea id="address" name="address" class="input" required><?= old('address') ?></textarea>
                    </div>
                    <div class="field">
                        <label for="order_notes">Order Notes <span style="text-transform:none; font-weight:400;">(optional)</span></label>
                        <textarea id="order_notes" name="order_notes" class="input" placeholder="Delivery instructions, gift note, etc."><?= old('order_notes') ?></textarea>
                    </div>
                </div>

                <div class="checkout-section">
                    <h2>Delivery Area</h2>
                    <?php $oldZoneId = old('delivery_zone_id'); ?>
                    <?php foreach ($deliveryZones as $zone): ?>
                    <?php $zoneChecked = $oldZoneId !== '' ? ($oldZoneId === (string) $zone['id']) : (bool) $zone['is_default']; ?>
                    <label class="delivery-option">
                        <div class="delivery-option__head">
                            <span><input type="radio" name="delivery_zone_id" value="<?= (int) $zone['id'] ?>" data-charge="<?= (float) $zone['charge'] ?>" <?= $zoneChecked ? 'checked' : '' ?> required><?= e($zone['name']) ?></span>
                            <span><?= money($zone['charge']) ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="checkout-section">
                    <h2>Payment Method</h2>
                    <?php $oldPaymentId = old('payment_method_id'); ?>
                    <?php foreach ($paymentMethods as $i => $method): ?>
                    <?php $paymentChecked = $oldPaymentId !== '' ? ($oldPaymentId === (string) $method['id']) : ($i === 0); ?>
                    <label class="payment-option">
                        <div class="payment-option__head">
                            <span><input type="radio" name="payment_method_id" value="<?= (int) $method['id'] ?>" <?= $paymentChecked ? 'checked' : '' ?> required><?= e($method['name']) ?></span>
                        </div>
                        <?php if ($method['requires_reference']): ?>
                        <div class="payment-option__instructions">
                            <p><?= e($method['instructions']) ?></p>
                            <?php if ($method['account_number']): ?><p><strong>Number:</strong> <?= e($method['account_number']) ?></p><?php endif; ?>
                            <div class="field" style="margin-top:10px;">
                                <label>Transaction ID</label>
                                <input type="text" name="payment_transaction_id" class="input" placeholder="e.g. 8N7X2K1Q9P" value="<?= old('payment_transaction_id') ?>">
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="payment-option__instructions"><p><?= e($method['instructions']) ?></p></div>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="summary-card">
                <h3>Order Summary</h3>
                <?php foreach ($items as $item): ?>
                <div class="checkout-summary-line">
                    <img src="<?= product_image_url($item['image']) ?>" alt="<?= e($item['name']) ?>">
                    <div style="flex:1;">
                        <div style="font-weight:600;"><?= e($item['name']) ?></div>
                        <div style="color:var(--text-muted); font-size:12px;">
                            <?= e(trim(implode(' / ', array_filter([$item['size'], $item['color']])))) ?> · Qty <?= (int) $item['quantity'] ?>
                        </div>
                    </div>
                    <div><?= money($item['line_total']) ?></div>
                </div>
                <?php endforeach; ?>
                <div class="summary-row" style="margin-top:16px;"><span>Subtotal</span><span id="checkoutSubtotal" data-value="<?= (float) $subtotal ?>"><?= money($subtotal) ?></span></div>
                <div class="summary-row"><span>Delivery</span><span id="checkoutDelivery">—</span></div>
                <div class="summary-row"><span id="checkoutDeliveryNote"></span></div>
                <div class="summary-row total"><span>Total</span><span id="checkoutTotal"><?= money($subtotal) ?></span></div>
                <button type="submit" class="btn btn-primary btn-block" style="margin-top:18px;">Place Order</button>
                <p style="font-size:11.5px; color:var(--text-muted); margin-top:12px; text-align:center;">By placing this order you agree to pay the total above via your selected method.</p>
            </div>
        </div>
    </form>
</div>
<?php $extraScripts = ['assets/js/checkout.js']; ?>

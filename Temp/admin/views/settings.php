<?php $s = fn($k) => e($settings[$k] ?? ''); ?>
<form method="post" enctype="multipart/form-data" data-tabs>
    <?= csrf_field() ?>
    <div class="a-tabs__nav">
        <button type="button" class="is-active" data-tab="tabStore">Store Info</button>
        <button type="button" data-tab="tabHome">Homepage</button>
        <button type="button" data-tab="tabAbout">About & Footer</button>
        <button type="button" data-tab="tabSeo">SEO</button>
    </div>

    <div class="a-tabs__panel is-active" id="tabStore">
        <div class="a-card a-card--solid" style="margin-bottom:18px;">
            <div class="a-field-row">
                <div class="a-field"><label>Store Name</label><input type="text" name="store_name" class="a-input" value="<?= $s('store_name') ?>"></div>
                <div class="a-field"><label>Currency Symbol</label><input type="text" name="currency_symbol" class="a-input" value="<?= $s('currency_symbol') ?>"></div>
            </div>
            <div class="a-field"><label>Tagline</label><input type="text" name="store_tagline" class="a-input" value="<?= $s('store_tagline') ?>"></div>
            <div class="a-field-row">
                <div class="a-field"><label>Phone</label><input type="text" name="store_phone" class="a-input" value="<?= $s('store_phone') ?>"></div>
                <div class="a-field"><label>Email</label><input type="email" name="store_email" class="a-input" value="<?= $s('store_email') ?>"></div>
            </div>
            <div class="a-field"><label>Address</label><input type="text" name="store_address" class="a-input" value="<?= $s('store_address') ?>"></div>
            <div class="a-field-row">
                <div class="a-field"><label>Facebook URL</label><input type="url" name="facebook_url" class="a-input" value="<?= $s('facebook_url') ?>"></div>
                <div class="a-field"><label>Instagram URL</label><input type="url" name="instagram_url" class="a-input" value="<?= $s('instagram_url') ?>"></div>
            </div>
            <div class="a-field-row">
                <div class="a-field"><label>Logo</label><input type="file" name="logo" class="a-input" accept="image/*"><?php if (!empty($settings['store_logo'])): ?><p class="a-hint">Current: <?= $s('store_logo') ?></p><?php endif; ?></div>
                <div class="a-field"><label>Favicon</label><input type="file" name="favicon" class="a-input" accept="image/*"><?php if (!empty($settings['store_favicon'])): ?><p class="a-hint">Current: <?= $s('store_favicon') ?></p><?php endif; ?></div>
            </div>
        </div>
    </div>

    <div class="a-tabs__panel" id="tabHome">
        <div class="a-card a-card--solid" style="margin-bottom:18px;">
            <div class="a-field">
                <label>Hero Background Image</label>
                <?php $heroImg = $settings['homepage_hero_image'] ?? ''; ?>
                <img src="<?= $heroImg ? url($heroImg) : url('assets/images/hero-placeholder.svg') ?>" alt="" style="width:100%; max-width:360px; aspect-ratio:16/10; object-fit:cover; border-radius:8px; margin-bottom:10px; border:1px solid var(--a-border); display:block;">
                <input type="file" name="hero_image" class="a-input" accept="image/jpeg,image/png,image/webp">
                <p class="a-hint"><?= $heroImg ? 'Currently using an uploaded image.' : 'Currently showing the built-in placeholder illustration — upload a real photo to replace it.' ?> Wide/landscape images work best.</p>
            </div>
            <div class="a-field"><label>Hero Heading</label><input type="text" name="homepage_hero_heading" class="a-input" value="<?= $s('homepage_hero_heading') ?>"></div>
            <div class="a-field"><label>Hero Subtext</label><textarea name="homepage_hero_subtext" class="a-input" rows="2"><?= $s('homepage_hero_subtext') ?></textarea></div>
            <div class="a-field-row">
                <div class="a-field"><label>Hero Button Text</label><input type="text" name="homepage_hero_cta_text" class="a-input" value="<?= $s('homepage_hero_cta_text') ?>"></div>
                <div class="a-field"><label>Hero Button Link</label><input type="text" name="homepage_hero_cta_link" class="a-input" value="<?= $s('homepage_hero_cta_link') ?>"></div>
            </div>
            <div class="a-field"><label>Promo Banner Text</label><input type="text" name="promo_heading" class="a-input" value="<?= $s('promo_heading') ?>"></div>
            <div class="a-field-row-3">
                <div class="a-field"><label>Trust: Delivery</label><input type="text" name="trust_delivery_text" class="a-input" value="<?= $s('trust_delivery_text') ?>"></div>
                <div class="a-field"><label>Trust: Returns</label><input type="text" name="trust_return_text" class="a-input" value="<?= $s('trust_return_text') ?>"></div>
                <div class="a-field"><label>Trust: Payment</label><input type="text" name="trust_payment_text" class="a-input" value="<?= $s('trust_payment_text') ?>"></div>
            </div>
        </div>
    </div>

    <div class="a-tabs__panel" id="tabAbout">
        <div class="a-card a-card--solid" style="margin-bottom:18px;">
            <div class="a-field"><label>About Heading</label><input type="text" name="about_heading" class="a-input" value="<?= $s('about_heading') ?>"></div>
            <div class="a-field"><label>About Body</label><textarea name="about_body" class="a-input" rows="6"><?= $s('about_body') ?></textarea></div>
            <div class="a-field"><label>Footer Text</label><input type="text" name="footer_text" class="a-input" value="<?= $s('footer_text') ?>"></div>
        </div>
    </div>

    <div class="a-tabs__panel" id="tabSeo">
        <div class="a-card a-card--solid" style="margin-bottom:18px;">
            <div class="a-field"><label>Default Meta Title</label><input type="text" name="meta_default_title" class="a-input" value="<?= $s('meta_default_title') ?>"></div>
            <div class="a-field"><label>Default Meta Description</label><textarea name="meta_default_description" class="a-input" rows="3"><?= $s('meta_default_description') ?></textarea></div>
        </div>
    </div>

    <button type="submit" class="a-btn a-btn-primary">Save Settings</button>
</form>

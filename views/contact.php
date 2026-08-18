<div class="container" style="padding:60px 0 100px; max-width:600px;">
    <span class="eyebrow">Get In Touch</span>
    <h1 style="margin:10px 0 30px;">Contact Us</h1>
    <div class="summary-card">
        <?php if (setting('store_phone')): ?><p><strong>Phone:</strong> <?= e(setting('store_phone')) ?></p><?php endif; ?>
        <?php if (setting('store_email')): ?><p><strong>Email:</strong> <?= e(setting('store_email')) ?></p><?php endif; ?>
        <?php if (setting('store_address')): ?><p style="margin-bottom:0;"><strong>Address:</strong> <?= e(setting('store_address')) ?></p><?php endif; ?>
    </div>
    <?php if (setting('facebook_url')): ?>
    <p style="margin-top:20px;">Find us on <a href="<?= e(setting('facebook_url')) ?>" target="_blank" rel="noopener" style="text-decoration:underline;">Facebook</a>.</p>
    <?php endif; ?>
</div>

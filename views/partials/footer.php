</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand"><?= e(setting('store_name', 'Atelier')) ?></div>
                <p style="font-size:13.5px; color:rgba(255,255,255,0.65); max-width:32ch;"><?= e(setting('store_tagline', '')) ?></p>
                <div class="social-row" style="margin-top:16px;">
                    <?php if (setting('facebook_url')): ?>
                    <a href="<?= e(setting('facebook_url')) ?>" target="_blank" rel="noopener" aria-label="Facebook">
                        <svg viewBox="0 0 24 24"><path d="M14 9h3V6h-3a4 4 0 0 0-4 4v2H7v3h3v6h3v-6h3l1-3h-4v-2a1 1 0 0 1 1-1z"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if (setting('instagram_url')): ?>
                    <a href="<?= e(setting('instagram_url')) ?>" target="_blank" rel="noopener" aria-label="Instagram">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-col">
                <h4>Shop</h4>
                <ul>
                    <?php foreach (Category::allActive() as $cat): ?>
                    <li><a href="<?= url('category/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Help</h4>
                <ul>
                    <li><a href="<?= url('contact') ?>">Contact Us</a></li>
                    <li><a href="<?= url('track-order') ?>">Track Order</a></li>
                    <li><a href="<?= url('about') ?>">About Us</a></li>
                    <li><a href="<?= url('account/login') ?>">My Account</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Get In Touch</h4>
                <ul>
                    <?php if (setting('store_phone')): ?><li><?= e(setting('store_phone')) ?></li><?php endif; ?>
                    <?php if (setting('store_email')): ?><li><?= e(setting('store_email')) ?></li><?php endif; ?>
                    <?php if (setting('store_address')): ?><li><?= e(setting('store_address')) ?></li><?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>
                Copyright © 2026 Wear Worth. All Rights Reserved. Designed and Developped by <a style="color: #c6c0b4;" href="https://www.instagram.com/srayan007/" target="_blank" rel="noopener">Shan Abrar Srayan</a>.
            </span>
            <span>Cash on Delivery · bKash · Nagad</span>
        </div>
    </div>
</footer>

<script src="<?= asset('assets/js/main.js') ?>" defer></script>
<script src="<?= asset('assets/js/cart.js') ?>" defer></script>
<?php if (isset($extraScripts)) foreach ($extraScripts as $s): ?>
<script src="<?= asset($s) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>

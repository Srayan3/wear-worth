<section class="hero">
    <div class="hero__media">
        <img src="<?= url(setting('homepage_hero_image', 'assets/images/hero-placeholder.svg')) ?>" alt="">
    </div>
    <div class="hero__content">
        <span class="eyebrow hero__eyebrow"><?= e(setting('store_name', 'Atelier')) ?> — Dhaka, Bangladesh</span>
        <h1><?= e(setting('homepage_hero_heading', 'The New Season Edit')) ?></h1>
        <p><?= e(setting('homepage_hero_subtext', '')) ?></p>
        <div class="hero__actions">
            <a href="<?= e(setting('homepage_hero_cta_link', '/shop')) ?>" class="btn btn-primary"><?= e(setting('homepage_hero_cta_text', 'Shop New Arrivals')) ?></a>
            <a href="<?= url('shop') ?>" class="btn btn-outline">Shop All</a>
        </div>
    </div>
</section>

<div class="trust-strip">
    <div class="container trust-strip__inner">
        <div class="trust-item">
            <svg viewBox="0 0 24 24"><rect x="2" y="7" width="15" height="12" rx="1"/><path d="M17 10h3l2 3v6h-5"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
            <div><strong>Nationwide Delivery</strong><span><?= e(setting('trust_delivery_text', '')) ?></span></div>
        </div>
        <div class="trust-item">
            <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg>
            <div><strong>Easy Exchanges</strong><span><?= e(setting('trust_return_text', '')) ?></span></div>
        </div>
        <div class="trust-item">
            <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            <div><strong>Flexible Payment</strong><span><?= e(setting('trust_payment_text', '')) ?></span></div>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Shop By Category</span>
                <h2>Find Your Fit</h2>
            </div>
        </div>
        <div class="category-grid">
            <?php foreach (array_slice($categories, 0, 3) as $cat): ?>
            <?php $catImg = $cat['image'] ?: 'assets/images/placeholders/category-' . $cat['slug'] . '.svg'; ?>
            <a href="<?= url('category/' . $cat['slug']) ?>" class="category-tile">
                <img src="<?= url($catImg) ?>" alt="<?= e($cat['name']) ?>" loading="lazy" onerror="this.style.display='none'">
                <div class="category-tile__label">
                    <span><?= e($cat['name']) ?></span>
                    <small><?= count($cat['subcategories']) ?> Collections</small>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($newArrivals)): ?>
<section class="section section--sunk">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Just In</span>
                <h2>New Arrivals</h2>
            </div>
            <a href="<?= url('shop?sort=newest') ?>" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="product-grid">
            <?php foreach ($newArrivals as $product): include ROOT_PATH . '/views/partials/product-card.php'; endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featured)): ?>
<section class="section">
    <div class="container">
        <div class="editorial-split">
            <div class="editorial-split__media">
                <img src="<?= product_image_url($featured[0]['primary_image'] ?? null) ?>" alt="Featured collection">
            </div>
            <div>
                <span class="eyebrow">Featured Collection</span>
                <h2>Pieces we keep returning to</h2>
                <p>A small edit of what's resonating right now — considered fabrics, quiet detailing, cuts that hold their shape through a long Dhaka day.</p>
                <a href="<?= url('shop?featured=1') ?>" class="btn btn-primary">Shop the Edit</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="product-grid">
            <?php foreach ($featured as $product): include ROOT_PATH . '/views/partials/product-card.php'; endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($popular)): ?>
<section class="section section--sunk">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Best Sellers</span>
                <h2>Most Loved</h2>
            </div>
            <a href="<?= url('shop?sort=popular') ?>" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="product-grid">
            <?php foreach ($popular as $product): include ROOT_PATH . '/views/partials/product-card.php'; endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="newsletter">
            <span class="eyebrow">Stay In Touch</span>
            <h2>Join the list</h2>
            <p>New arrivals, restocks and quiet sales — a note or two a month, never more.</p>
            <form class="newsletter-form" id="newsletterForm" action="<?= url('newsletter') ?>" method="post">
                <?= csrf_field() ?>
                <input type="email" name="email" class="input" placeholder="you@example.com" required>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
            <p class="newsletter-status" style="margin-top:12px; font-size:13px; min-height:16px;"></p>
        </div>
    </div>
</section>

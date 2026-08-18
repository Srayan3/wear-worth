<?php $queryParams = $_GET; unset($queryParams['page']); ?>
<div class="container">
    <div class="shop-header">
        <div class="breadcrumbs">
            <a href="<?= url('') ?>">Home</a><span class="sep">/</span>
            <?php if ($currentCategory): ?>
                <a href="<?= url('shop') ?>">Shop</a><span class="sep">/</span>
                <?php if ($currentSubcategory): ?>
                    <a href="<?= url('category/' . $currentCategory['slug']) ?>"><?= e($currentCategory['name']) ?></a><span class="sep">/</span>
                    <span><?= e($currentSubcategory['name']) ?></span>
                <?php else: ?>
                    <span><?= e($currentCategory['name']) ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span>Shop</span>
            <?php endif; ?>
        </div>
        <h1><?= e($heading) ?></h1>
        <div class="shop-count"><?= (int) $pagination['total'] ?> item<?= $pagination['total'] === 1 ? '' : 's' ?></div>
    </div>

    <div class="shop-layout">
        <aside class="shop-sidebar" id="shopSidebar">
            <div class="shop-sidebar__close"><button type="button" class="icon-btn" id="shopSidebarClose">✕</button></div>
            <form method="get" id="filterForm">
                <?php if (!empty($filters['search'])): ?><input type="hidden" name="q" value="<?= e($filters['search']) ?>"><?php endif; ?>
                <input type="hidden" name="sort" value="<?= e($filters['sort'] ?? 'newest') ?>">

                <div class="filter-group">
                    <h3>Category</h3>
                    <ul class="filter-list">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?= url('category/' . $cat['slug']) ?>" class="<?= (($currentCategory['slug'] ?? '') === $cat['slug'] && empty($currentSubcategory)) ? 'is-active' : '' ?>"><?= e($cat['name']) ?></a>
                            <?php if (($currentCategory['slug'] ?? null) === $cat['slug']): ?>
                            <ul class="filter-list" style="padding-left:14px;">
                                <?php foreach ($cat['subcategories'] as $sub): ?>
                                <li><a href="<?= url('category/' . $cat['slug'] . '/' . $sub['slug']) ?>" class="<?= (($currentSubcategory['slug'] ?? '') === $sub['slug']) ? 'is-active' : '' ?>"><?= e($sub['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="filter-group">
                    <h3>Price</h3>
                    <div class="price-range-inputs">
                        <input type="number" name="min_price" class="input" placeholder="<?= (int) $priceMin ?>" value="<?= e($filters['min_price'] ?? '') ?>" min="0">
                        <span>–</span>
                        <input type="number" name="max_price" class="input" placeholder="<?= (int) $priceMax ?>" value="<?= e($filters['max_price'] ?? '') ?>" min="0">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm btn-block" style="margin-top:12px;">Apply</button>
                </div>

                <div class="filter-group">
                    <h3>Availability</h3>
                    <label class="checkbox-row">
                        <input type="checkbox" name="in_stock" value="1" data-auto-submit <?= !empty($filters['in_stock_only']) ? 'checked' : '' ?>>
                        <span style="font-size:13.5px;">In stock only</span>
                    </label>
                </div>
            </form>
        </aside>

        <div>
            <div class="shop-toolbar">
                <button type="button" class="btn btn-outline btn-sm mobile-filter-toggle" id="filterToggle">Filters</button>
                <div class="shop-toolbar__right">
                    <form method="get" id="sortForm">
                        <?php foreach ($queryParams as $k => $v): if ($k !== 'sort'): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endif; endforeach; ?>
                        <select name="sort" id="sortSelect" class="input sort-select">
                            <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price_low" <?= ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                            <option value="featured" <?= ($filters['sort'] ?? '') === 'featured' ? 'selected' : '' ?>>Featured</option>
                        </select>
                    </form>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <h2>No products found</h2>
                    <p>Try adjusting your filters or search for something else.</p>
                    <a href="<?= url('shop') ?>" class="btn btn-primary">Shop All</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): include ROOT_PATH . '/views/partials/product-card.php'; endforeach; ?>
                </div>
                <?php include ROOT_PATH . '/views/partials/pagination.php'; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $extraScripts = ['assets/js/shop.js']; ?>

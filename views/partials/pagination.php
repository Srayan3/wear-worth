<?php
/** Expects $pagination (from Product::search/allForAdmin) and $queryParams (array, current GET params) in scope. */
if (($pagination['total_pages'] ?? 1) <= 1) return;

$buildUrl = function (int $page) use ($queryParams) {
    $params = $queryParams;
    $params['page'] = $page;
    return '?' . http_build_query($params);
};
$current = $pagination['page'];
$total = $pagination['total_pages'];
?>
<nav class="pagination" aria-label="Pagination">
    <?php if ($current > 1): ?>
        <a href="<?= e($buildUrl($current - 1)) ?>" aria-label="Previous page">‹</a>
    <?php else: ?>
        <span class="is-disabled">‹</span>
    <?php endif; ?>

    <?php foreach (paginate_range($current, $total) as $p): ?>
        <?php if ($p === $current): ?>
            <span class="is-current"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= e($buildUrl($p)) ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($current < $total): ?>
        <a href="<?= e($buildUrl($current + 1)) ?>" aria-label="Next page">›</a>
    <?php else: ?>
        <span class="is-disabled">›</span>
    <?php endif; ?>
</nav>

<?php
/**
 * Finds product images (and category images) whose database row points to
 * a file that no longer exists on disk — the exact "filename in the
 * database, 404 on the page" symptom.
 *
 * This is READ-ONLY: it only reports, it never deletes or changes anything.
 * Run it once after upgrading, review the list, then either re-upload the
 * image for each product listed or remove the broken image entry from
 * Admin → Products → [product] → Images.
 *
 * Run from the command line:
 *   php database/find_broken_images.php
 *
 * No SSH? Temporarily copy this file to the project root, load it once in
 * your browser, then DELETE the copy — it has no login protection.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$isCli = PHP_SAPI === 'cli';
$db = Database::connect();

function out(string $line, bool $isCli): void
{
    echo $isCli ? $line . "\n" : nl2br(htmlspecialchars($line)) . "\n";
}

if (!$isCli) {
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Broken Image Check</title>';
    echo '<body style="font-family:monospace; white-space:pre-wrap; max-width:900px; margin:40px auto; line-height:1.6;">';
}

out('Checking product images...', $isCli);
out(str_repeat('-', 60), $isCli);

$stmt = $db->query(
    "SELECT pi.id, pi.image_path, pi.is_primary, p.id AS product_id, p.name AS product_name, p.slug
     FROM product_images pi
     JOIN products p ON p.id = pi.product_id
     ORDER BY p.id ASC"
);

$brokenCount = 0;
$totalCount = 0;

foreach ($stmt->fetchAll() as $row) {
    $totalCount++;
    $fullPath = ROOT_PATH . '/' . $row['image_path'];
    if (!is_file($fullPath)) {
        $brokenCount++;
        out(sprintf(
            'BROKEN  product #%d "%s" (image_id=%d%s) -> missing file: %s',
            $row['product_id'],
            $row['product_name'],
            $row['id'],
            $row['is_primary'] ? ', PRIMARY IMAGE' : '',
            $row['image_path']
        ), $isCli);
    }
}

out(str_repeat('-', 60), $isCli);
out("Checked {$totalCount} product image(s), found {$brokenCount} broken.", $isCli);

// Same check for category tile images
out('', $isCli);
out('Checking category images...', $isCli);
out(str_repeat('-', 60), $isCli);

$catStmt = $db->query("SELECT id, name, image FROM categories WHERE image IS NOT NULL AND image != ''");
$catBroken = 0;
$catTotal = 0;
foreach ($catStmt->fetchAll() as $cat) {
    $catTotal++;
    $fullPath = ROOT_PATH . '/' . $cat['image'];
    if (!is_file($fullPath)) {
        $catBroken++;
        out(sprintf('BROKEN  category #%d "%s" -> missing file: %s', $cat['id'], $cat['name'], $cat['image']), $isCli);
    }
}
out(str_repeat('-', 60), $isCli);
out("Checked {$catTotal} category image(s), found {$catBroken} broken.", $isCli);

out('', $isCli);
if ($brokenCount === 0 && $catBroken === 0) {
    out('Nothing broken — all image references have a matching file on disk.', $isCli);
} else {
    out('For each BROKEN line above: open that product/category in the admin panel', $isCli);
    out('and either re-upload the image, or remove the broken entry.', $isCli);
}

if (!$isCli) {
    echo '</body>';
}

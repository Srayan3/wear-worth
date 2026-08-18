<?php

class SitemapController
{
    public static function generate(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $db = Database::connect();

        $urls = [['loc' => url(''), 'priority' => '1.0']];
        $urls[] = ['loc' => url('shop'), 'priority' => '0.9'];
        $urls[] = ['loc' => url('about'), 'priority' => '0.5'];
        $urls[] = ['loc' => url('contact'), 'priority' => '0.5'];

        foreach ($db->query("SELECT slug FROM categories WHERE is_active = 1") as $c) {
            $urls[] = ['loc' => url('category/' . $c['slug']), 'priority' => '0.8'];
        }
        foreach ($db->query(
            "SELECT s.slug AS sub_slug, c.slug AS cat_slug FROM subcategories s
             JOIN categories c ON c.id = s.category_id WHERE s.is_active = 1"
        ) as $s) {
            $urls[] = ['loc' => url('category/' . $s['cat_slug'] . '/' . $s['sub_slug']), 'priority' => '0.7'];
        }
        foreach ($db->query("SELECT slug, updated_at FROM products WHERE is_active = 1") as $p) {
            $urls[] = ['loc' => url('product/' . $p['slug']), 'lastmod' => date('c', strtotime($p['updated_at'])), 'priority' => '0.6'];
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            echo "    <loc>" . e($u['loc']) . "</loc>\n";
            if (!empty($u['lastmod'])) {
                echo "    <lastmod>" . e($u['lastmod']) . "</lastmod>\n";
            }
            echo "    <priority>" . e($u['priority']) . "</priority>\n";
            echo "  </url>\n";
        }
        echo '</urlset>';
    }

    public static function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /cart\n";
        echo "Disallow: /checkout\n";
        echo "Disallow: /account\n";
        echo "Disallow: /config\n";
        echo "Disallow: /core\n";
        echo "Disallow: /models\n";
        echo "Disallow: /controllers\n";
        echo "Disallow: /database\n";
        echo "Allow: /\n\n";
        echo "Sitemap: " . url('sitemap.xml') . "\n";
    }
}

<?php

class Product
{
    /**
     * Shop listing with filters, sorting, and pagination.
     * $filters keys: category, subcategory, min_price, max_price, search,
     *                sort, in_stock_only, featured, new_arrival, popular
     */
    public static function search(array $filters, int $page = 1, int $perPage = 12): array
    {
        $db = Database::connect();
        $where = ["p.is_active = 1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "cat.slug = :category";
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['subcategory'])) {
            $where[] = "sub.slug = :subcategory";
            $params['subcategory'] = $filters['subcategory'];
        }
        if (!empty($filters['min_price']) && is_numeric($filters['min_price'])) {
            $where[] = "COALESCE(p.discount_price, p.price) >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        if (!empty($filters['max_price']) && is_numeric($filters['max_price'])) {
            $where[] = "COALESCE(p.discount_price, p.price) <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(MATCH(p.name, p.short_description, p.description) AGAINST(:search_bool IN BOOLEAN MODE)
                         OR p.name LIKE :search_like OR p.sku LIKE :search_like)";
            $params['search_bool'] = self::toBooleanSearch($filters['search']);
            $params['search_like'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['in_stock_only'])) {
            $where[] = "p.stock_status = 'in_stock'";
        }
        if (!empty($filters['featured'])) {
            $where[] = "p.is_featured = 1";
        }
        if (!empty($filters['new_arrival'])) {
            $where[] = "p.is_new_arrival = 1";
        }
        if (!empty($filters['popular'])) {
            $where[] = "p.is_popular = 1";
        }

        $whereSql = implode(' AND ', $where);

        $orderSql = match ($filters['sort'] ?? 'newest') {
            'price_low'  => "COALESCE(p.discount_price, p.price) ASC",
            'price_high' => "COALESCE(p.discount_price, p.price) DESC",
            'popular'    => "p.is_popular DESC, p.views_count DESC",
            'featured'   => "p.is_featured DESC, p.created_at DESC",
            default      => "p.created_at DESC",
        };

        $countStmt = $db->prepare(
            "SELECT COUNT(*) FROM products p
             JOIN subcategories sub ON sub.id = p.subcategory_id
             JOIN categories cat ON cat.id = sub.category_id
             WHERE {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, $perPage);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT p.*, sub.name AS subcategory_name, sub.slug AS subcategory_slug,
                    cat.name AS category_name, cat.slug AS category_slug,
                    (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) AS primary_image,
                    (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1 OFFSET 1) AS secondary_image
             FROM products p
             JOIN subcategories sub ON sub.id = p.subcategory_id
             JOIN categories cat ON cat.id = sub.category_id
             WHERE {$whereSql}
             ORDER BY {$orderSql}
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'       => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    private static function toBooleanSearch(string $term): string
    {
        $words = preg_split('/\s+/', trim($term));
        $words = array_filter($words);
        return implode(' ', array_map(fn($w) => '+' . preg_replace('/[+\-<>()~*"@]+/', '', $w) . '*', $words));
    }

    public static function findBySlug(string $slug): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT p.*, sub.name AS subcategory_name, sub.slug AS subcategory_slug,
                    cat.name AS category_name, cat.slug AS category_slug, cat.id AS category_id
             FROM products p
             JOIN subcategories sub ON sub.id = p.subcategory_id
             JOIN categories cat ON cat.id = sub.category_id
             WHERE p.slug = :slug AND p.is_active = 1 LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        $product = $stmt->fetch();
        if (!$product) {
            return null;
        }

        $product['images'] = self::images((int) $product['id']);
        $product['variations'] = self::variations((int) $product['id']);
        $product['size_chart'] = self::sizeChart((int) $product['id']);
        $product['sizes'] = array_values(array_unique(array_filter(array_column($product['variations'], 'size_label'))));
        $product['colors'] = self::distinctColors($product['variations']);

        return $product;
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private static function distinctColors(array $variations): array
    {
        $seen = [];
        $colors = [];
        foreach ($variations as $v) {
            if (!empty($v['color_name']) && !isset($seen[$v['color_name']])) {
                $seen[$v['color_name']] = true;
                $colors[] = ['name' => $v['color_name'], 'hex' => $v['color_hex']];
            }
        }
        return $colors;
    }

    public static function images(int $productId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT * FROM product_images WHERE product_id = :pid ORDER BY is_primary DESC, sort_order ASC"
        );
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll();
    }

    public static function variations(int $productId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT * FROM product_variations WHERE product_id = :pid AND is_active = 1 ORDER BY id ASC"
        );
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll();
    }

    public static function sizeChart(int $productId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT * FROM product_size_chart WHERE product_id = :pid ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll();
    }

    public static function findVariation(int $variationId): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM product_variations WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $variationId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function related(int $subcategoryId, int $excludeId, int $limit = 4): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT p.*,
                    (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) AS primary_image
             FROM products p
             WHERE p.subcategory_id = :sub AND p.id != :exclude AND p.is_active = 1
             ORDER BY p.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':sub', $subcategoryId, PDO::PARAM_INT);
        $stmt->bindValue(':exclude', $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function homepageSection(string $flag, int $limit = 8): array
    {
        $column = match ($flag) {
            'featured'    => 'is_featured',
            'new_arrival' => 'is_new_arrival',
            'popular'     => 'is_popular',
            default       => 'is_featured',
        };
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT p.*,
                    (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) AS primary_image,
                    (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1 OFFSET 1) AS secondary_image
             FROM products p
             WHERE p.is_active = 1 AND p.{$column} = 1
             ORDER BY p.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function incrementViews(int $id): void
    {
        $db = Database::connect();
        $db->prepare("UPDATE products SET views_count = views_count + 1 WHERE id = :id")->execute(['id' => $id]);
    }

    public static function priceRange(): array
    {
        $db = Database::connect();
        $row = $db->query(
            "SELECT MIN(COALESCE(discount_price, price)) AS min_p, MAX(COALESCE(discount_price, price)) AS max_p
             FROM products WHERE is_active = 1"
        )->fetch();
        return [(float) ($row['min_p'] ?? 0), (float) ($row['max_p'] ?? 0)];
    }

    // ---- Admin CRUD ----------------------------------------------------

    public static function allForAdmin(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $db = Database::connect();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.sku LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['subcategory_id'])) {
            $where[] = "p.subcategory_id = :sub_id";
            $params['sub_id'] = $filters['subcategory_id'];
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[] = "p.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }
        if (!empty($filters['low_stock'])) {
            $where[] = "p.stock_quantity <= 5 AND p.stock_status = 'in_stock'";
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT p.*, sub.name AS subcategory_name,
                    (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) AS primary_image
             FROM products p
             JOIN subcategories sub ON sub.id = p.subcategory_id
             WHERE {$whereSql}
             ORDER BY p.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page,
            'per_page' => $perPage, 'total_pages' => $totalPages,
        ];
    }

    public static function create(array $data): int
    {
        $db = Database::connect();
        $slug = unique_slug($db, 'products', $data['name']);
        $stmt = $db->prepare(
            "INSERT INTO products
                (subcategory_id, name, slug, sku, short_description, description, price, discount_price,
                 stock_quantity, stock_status, has_variations, size_chart_type, is_featured, is_new_arrival, is_popular, is_active)
             VALUES
                (:subcategory_id, :name, :slug, :sku, :short_description, :description, :price, :discount_price,
                 :stock_quantity, :stock_status, :has_variations, :size_chart_type, :is_featured, :is_new_arrival, :is_popular, :is_active)"
        );
        $stmt->execute([
            'subcategory_id'     => $data['subcategory_id'],
            'name'               => $data['name'],
            'slug'               => $slug,
            'sku'                => $data['sku'],
            'short_description'  => $data['short_description'] ?? null,
            'description'        => $data['description'] ?? null,
            'price'              => $data['price'],
            'discount_price'     => $data['discount_price'] ?: null,
            'stock_quantity'     => $data['stock_quantity'] ?? 0,
            'stock_status'       => $data['stock_status'] ?? 'in_stock',
            'has_variations'     => $data['has_variations'] ?? 0,
            'size_chart_type'    => in_array($data['size_chart_type'] ?? '', ['clothing', 'footwear'], true) ? $data['size_chart_type'] : 'clothing',
            'is_featured'        => $data['is_featured'] ?? 0,
            'is_new_arrival'     => $data['is_new_arrival'] ?? 0,
            'is_popular'         => $data['is_popular'] ?? 0,
            'is_active'          => $data['is_active'] ?? 1,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::connect();
        $slug = unique_slug($db, 'products', $data['name'], $id);
        $stmt = $db->prepare(
            "UPDATE products SET
                subcategory_id = :subcategory_id, name = :name, slug = :slug, sku = :sku,
                short_description = :short_description, description = :description,
                price = :price, discount_price = :discount_price,
                stock_quantity = :stock_quantity, stock_status = :stock_status,
                has_variations = :has_variations,
                size_chart_type = COALESCE(:size_chart_type, size_chart_type),
                is_featured = :is_featured,
                is_new_arrival = :is_new_arrival, is_popular = :is_popular, is_active = :is_active
             WHERE id = :id"
        );
        $stmt->execute([
            'subcategory_id'     => $data['subcategory_id'],
            'name'               => $data['name'],
            'slug'               => $slug,
            'sku'                => $data['sku'],
            'short_description'  => $data['short_description'] ?? null,
            'description'        => $data['description'] ?? null,
            'price'              => $data['price'],
            'discount_price'     => $data['discount_price'] ?: null,
            'stock_quantity'     => $data['stock_quantity'] ?? 0,
            'stock_status'       => $data['stock_status'] ?? 'in_stock',
            'has_variations'     => $data['has_variations'] ?? 0,
            // Only ever overwritten when explicitly present (e.g. from the Size
            // Chart tab) — saving Basic Info alone must never reset this.
            'size_chart_type'    => (isset($data['size_chart_type']) && in_array($data['size_chart_type'], ['clothing', 'footwear'], true)) ? $data['size_chart_type'] : null,
            'is_featured'        => $data['is_featured'] ?? 0,
            'is_new_arrival'     => $data['is_new_arrival'] ?? 0,
            'is_popular'         => $data['is_popular'] ?? 0,
            'is_active'          => $data['is_active'] ?? 1,
            'id'                 => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $db = Database::connect();
        foreach (self::images($id) as $img) {
            self::deleteImageFile($img['image_path']);
        }
        $db->prepare("DELETE FROM products WHERE id = :id")->execute(['id' => $id]);
    }

    public static function duplicate(int $id): int
    {
        $db = Database::connect();
        $product = self::find($id);
        if (!$product) {
            throw new RuntimeException('Product not found');
        }
        $newId = self::create([
            'subcategory_id'    => $product['subcategory_id'],
            'name'              => $product['name'] . ' (Copy)',
            'sku'               => $product['sku'] . '-COPY-' . substr(bin2hex(random_bytes(2)), 0, 4),
            'short_description' => $product['short_description'],
            'description'       => $product['description'],
            'price'             => $product['price'],
            'discount_price'    => $product['discount_price'],
            'stock_quantity'    => 0,
            'stock_status'      => 'out_of_stock',
            'has_variations'    => $product['has_variations'],
            'is_featured'       => 0,
            'is_new_arrival'    => 1,
            'is_popular'        => 0,
            'is_active'         => 0,
        ]);

        // Copy images (references, files are shared on disk)
        $imgStmt = $db->prepare(
            "INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
             SELECT :new_id, image_path, is_primary, sort_order FROM product_images WHERE product_id = :old_id"
        );
        $imgStmt->execute(['new_id' => $newId, 'old_id' => $id]);

        $varStmt = $db->prepare(
            "INSERT INTO product_variations (product_id, size_label, color_name, color_hex, sku, price_override, stock_quantity, image_path, is_active)
             SELECT :new_id, size_label, color_name, color_hex, CONCAT(COALESCE(sku,'VAR'), '-COPY'), price_override, 0, image_path, is_active
             FROM product_variations WHERE product_id = :old_id"
        );
        $varStmt->execute(['new_id' => $newId, 'old_id' => $id]);

        return $newId;
    }

    public static function toggleFlag(int $id, string $flag): void
    {
        $allowed = ['is_active', 'is_featured', 'is_new_arrival', 'is_popular'];
        if (!in_array($flag, $allowed, true)) {
            throw new InvalidArgumentException('Invalid flag');
        }
        $db = Database::connect();
        $db->prepare("UPDATE products SET {$flag} = NOT {$flag} WHERE id = :id")->execute(['id' => $id]);
    }

    public static function updateStock(int $id, int $quantity): void
    {
        $db = Database::connect();
        $status = $quantity > 0 ? 'in_stock' : 'out_of_stock';
        $stmt = $db->prepare("UPDATE products SET stock_quantity = :qty, stock_status = :status WHERE id = :id");
        $stmt->execute(['qty' => $quantity, 'status' => $status, 'id' => $id]);
    }

    /** Decrement stock after a successful order (called inside the checkout transaction). */
    public static function decrementStock(PDO $db, int $productId, ?int $variationId, int $qty): void
    {
        if ($variationId) {
            $stmt = $db->prepare(
                "UPDATE product_variations SET stock_quantity = stock_quantity - :qty
                 WHERE id = :id AND stock_quantity >= :qty2"
            );
            $stmt->execute(['qty' => $qty, 'id' => $variationId, 'qty2' => $qty]);
        } else {
            $stmt = $db->prepare(
                "UPDATE products SET stock_quantity = stock_quantity - :qty
                 WHERE id = :id AND stock_quantity >= :qty2"
            );
            $stmt->execute(['qty' => $qty, 'id' => $productId, 'qty2' => $qty]);
        }
        // Flip stock_status to out_of_stock automatically when it hits zero
        $stmt2 = $db->prepare("UPDATE products SET stock_status = 'out_of_stock' WHERE id = :id AND stock_quantity <= 0");
        $stmt2->execute(['id' => $productId]);
    }

    // ---- Images ----------------------------------------------------

    public static function addImage(int $productId, string $path, bool $isPrimary, int $sortOrder): int
    {
        $db = Database::connect();
        if ($isPrimary) {
            $db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :pid")->execute(['pid' => $productId]);
        }
        $stmt = $db->prepare(
            "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (:pid, :path, :primary, :sort)"
        );
        $stmt->execute(['pid' => $productId, 'path' => $path, 'primary' => $isPrimary ? 1 : 0, 'sort' => $sortOrder]);
        return (int) $db->lastInsertId();
    }

    public static function deleteImage(int $imageId): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM product_images WHERE id = :id");
        $stmt->execute(['id' => $imageId]);
        $img = $stmt->fetch();
        if ($img) {
            self::deleteImageFile($img['image_path']);
            $db->prepare("DELETE FROM product_images WHERE id = :id")->execute(['id' => $imageId]);
        }
    }

    public static function setPrimaryImage(int $productId, int $imageId): void
    {
        $db = Database::connect();
        $db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :pid")->execute(['pid' => $productId]);
        $db->prepare("UPDATE product_images SET is_primary = 1 WHERE id = :id AND product_id = :pid")
            ->execute(['id' => $imageId, 'pid' => $productId]);
    }

    public static function reorderImages(array $orderedImageIds): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE product_images SET sort_order = :pos WHERE id = :id");
        foreach ($orderedImageIds as $pos => $id) {
            $stmt->execute(['pos' => $pos, 'id' => (int) $id]);
        }
    }

    private static function deleteImageFile(string $path): void
    {
        // Only ever delete files that live under our own uploads directory —
        // never touch the bundled placeholder sample images.
        if (str_starts_with($path, 'uploads/products/')) {
            $full = ROOT_PATH . '/' . $path;
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }

    // ---- Variations ----------------------------------------------------

    public static function replaceVariations(int $productId, array $variations): void
    {
        $db = Database::connect();
        $db->prepare("DELETE FROM product_variations WHERE product_id = :pid")->execute(['pid' => $productId]);
        $stmt = $db->prepare(
            "INSERT INTO product_variations
                (product_id, size_label, color_name, color_hex, sku, price_override, stock_quantity, image_path, is_active)
             VALUES (:pid, :size, :color, :hex, :sku, :price, :qty, :image, 1)"
        );
        foreach ($variations as $v) {
            if (empty($v['size']) && empty($v['color'])) {
                continue;
            }
            $stmt->execute([
                'pid'   => $productId,
                'size'  => $v['size'] ?: null,
                'color' => $v['color'] ?: null,
                'hex'   => $v['hex'] ?: null,
                'sku'   => $v['sku'] ?: null,
                'price' => $v['price'] !== '' ? $v['price'] : null,
                'qty'   => $v['qty'] ?: 0,
                'image' => $v['image'] ?: null,
            ]);
        }
    }

    /**
     * Replaces a product's size chart rows. $rows is a unified shape covering
     * both chart types — pass whichever fields apply for size_chart_type and
     * leave the rest empty/omitted:
     *   clothing: size, chest, waist, hip, length
     *   footwear: size (brand's own size), uk, eu, us
     */
    public static function replaceSizeChart(int $productId, array $rows): void
    {
        $db = Database::connect();
        $db->prepare("DELETE FROM product_size_chart WHERE product_id = :pid")->execute(['pid' => $productId]);
        $stmt = $db->prepare(
            "INSERT INTO product_size_chart
                (product_id, size_label, chest_in, waist_in, hip_in, length_in, uk_size, eu_size, us_size, sort_order)
             VALUES (:pid, :size, :chest, :waist, :hip, :length, :uk, :eu, :us, :sort)"
        );
        $blank = fn($v) => ($v === null || $v === '') ? null : $v;
        $i = 0;
        foreach ($rows as $r) {
            if (empty($r['size'])) {
                continue;
            }
            $stmt->execute([
                'pid'    => $productId,
                'size'   => $r['size'],
                'chest'  => $blank($r['chest'] ?? null),
                'waist'  => $blank($r['waist'] ?? null),
                'hip'    => $blank($r['hip'] ?? null),
                'length' => $blank($r['length'] ?? null),
                'uk'     => $blank($r['uk'] ?? null),
                'eu'     => $blank($r['eu'] ?? null),
                'us'     => $blank($r['us'] ?? null),
                'sort'   => $i++,
            ]);
        }
    }

    public static function updateSizeChartType(int $id, string $type): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE products SET size_chart_type = :type WHERE id = :id");
        $stmt->execute(['type' => in_array($type, ['clothing', 'footwear'], true) ? $type : 'clothing', 'id' => $id]);
    }
}

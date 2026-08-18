<?php

class Subcategory
{
    public static function findBySlug(string $slug): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM subcategories WHERE slug = :slug AND is_active = 1 LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM subcategories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function byCategory(int $categoryId, bool $activeOnly = true): array
    {
        $db = Database::connect();
        $sql = "SELECT * FROM subcategories WHERE category_id = :cid" .
               ($activeOnly ? " AND is_active = 1" : "") .
               " ORDER BY sort_order ASC, name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['cid' => $categoryId]);
        return $stmt->fetchAll();
    }

    // ---- Admin CRUD ----------------------------------------------------

    public static function allForAdmin(): array
    {
        $db = Database::connect();
        return $db->query(
            "SELECT s.*, c.name AS category_name,
                    (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.id) AS product_count
             FROM subcategories s
             JOIN categories c ON c.id = s.category_id
             ORDER BY c.sort_order ASC, s.sort_order ASC, s.name ASC"
        )->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::connect();
        $slug = unique_slug($db, 'subcategories', $data['name']);
        $stmt = $db->prepare(
            "INSERT INTO subcategories (category_id, name, slug, description, image, sort_order, is_active)
             VALUES (:category_id, :name, :slug, :description, :image, :sort_order, :is_active)"
        );
        $stmt->execute([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'image'       => $data['image'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_active'   => $data['is_active'] ?? 1,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::connect();
        $slug = unique_slug($db, 'subcategories', $data['name'], $id);
        $stmt = $db->prepare(
            "UPDATE subcategories SET category_id = :category_id, name = :name, slug = :slug,
                description = :description, image = COALESCE(:image, image),
                sort_order = :sort_order, is_active = :is_active
             WHERE id = :id"
        );
        $stmt->execute([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'image'       => $data['image'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_active'   => $data['is_active'] ?? 1,
            'id'          => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $db = Database::connect();
        $db->prepare("DELETE FROM subcategories WHERE id = :id")->execute(['id' => $id]);
    }

    public static function toggleActive(int $id): void
    {
        $db = Database::connect();
        $db->prepare("UPDATE subcategories SET is_active = NOT is_active WHERE id = :id")->execute(['id' => $id]);
    }

    public static function reorder(array $orderedIds): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE subcategories SET sort_order = :pos WHERE id = :id");
        foreach ($orderedIds as $pos => $id) {
            $stmt->execute(['pos' => $pos, 'id' => (int) $id]);
        }
    }
}

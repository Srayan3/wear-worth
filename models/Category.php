<?php

class Category
{
    public static function allActive(): array
    {
        $db = Database::connect();
        return $db->query(
            "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
        )->fetchAll();
    }

    /** All categories with their active subcategories nested — used for nav & shop sidebar. */
    public static function withSubcategories(bool $activeOnly = true): array
    {
        $db = Database::connect();
        $catWhere = $activeOnly ? 'WHERE c.is_active = 1' : '';
        $categories = $db->query(
            "SELECT c.* FROM categories c {$catWhere} ORDER BY c.sort_order ASC, c.name ASC"
        )->fetchAll();

        $subStmt = $db->prepare(
            "SELECT * FROM subcategories WHERE category_id = :cid" . ($activeOnly ? " AND is_active = 1" : "") .
            " ORDER BY sort_order ASC, name ASC"
        );

        foreach ($categories as &$cat) {
            $subStmt->execute(['cid' => $cat['id']]);
            $cat['subcategories'] = $subStmt->fetchAll();
        }
        return $categories;
    }

    public static function findBySlug(string $slug): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM categories WHERE slug = :slug AND is_active = 1 LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ---- Admin CRUD ----------------------------------------------------

    public static function allForAdmin(): array
    {
        $db = Database::connect();
        return $db->query(
            "SELECT c.*, g.name AS gender_name,
                    (SELECT COUNT(*) FROM subcategories s WHERE s.category_id = c.id) AS subcategory_count
             FROM categories c
             JOIN genders g ON g.id = c.gender_id
             ORDER BY c.sort_order ASC, c.name ASC"
        )->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::connect();
        $slug = unique_slug($db, 'categories', $data['name']);
        $stmt = $db->prepare(
            "INSERT INTO categories (gender_id, name, slug, description, image, sort_order, is_active)
             VALUES (:gender_id, :name, :slug, :description, :image, :sort_order, :is_active)"
        );
        $stmt->execute([
            'gender_id'   => $data['gender_id'],
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
        $slug = unique_slug($db, 'categories', $data['name'], $id);
        $stmt = $db->prepare(
            "UPDATE categories SET gender_id = :gender_id, name = :name, slug = :slug,
                description = :description, image = COALESCE(:image, image),
                sort_order = :sort_order, is_active = :is_active
             WHERE id = :id"
        );
        $stmt->execute([
            'gender_id'   => $data['gender_id'],
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
        $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public static function toggleActive(int $id): void
    {
        $db = Database::connect();
        $db->prepare("UPDATE categories SET is_active = NOT is_active WHERE id = :id")->execute(['id' => $id]);
    }

    public static function reorder(array $orderedIds): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE categories SET sort_order = :pos WHERE id = :id");
        foreach ($orderedIds as $pos => $id) {
            $stmt->execute(['pos' => $pos, 'id' => (int) $id]);
        }
    }
}

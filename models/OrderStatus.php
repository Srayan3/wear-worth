<?php

class OrderStatus
{
    public static function all(): array
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM order_statuses ORDER BY sort_order ASC")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM order_statuses WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function defaultStatus(): array
    {
        $db = Database::connect();
        $row = $db->query("SELECT * FROM order_statuses WHERE is_default = 1 ORDER BY id ASC LIMIT 1")->fetch();
        return $row ?: self::all()[0];
    }

    public static function create(array $data): int
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO order_statuses (name, slug, color, sort_order, is_default) VALUES (:name, :slug, :color, :sort, :def)"
        );
        $stmt->execute([
            'name' => $data['name'], 'slug' => slugify($data['name']), 'color' => $data['color'] ?: '#0A0A0A',
            'sort' => $data['sort_order'] ?? 99, 'def' => $data['is_default'] ?? 0,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "UPDATE order_statuses SET name = :name, color = :color, sort_order = :sort WHERE id = :id"
        );
        $stmt->execute(['name' => $data['name'], 'color' => $data['color'] ?: '#0A0A0A', 'sort' => $data['sort_order'] ?? 0, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $db = Database::connect();
        $db->prepare("DELETE FROM order_statuses WHERE id = :id")->execute(['id' => $id]);
    }
}

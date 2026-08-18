<?php

class DeliveryZone
{
    public static function allActive(): array
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM delivery_zones WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM delivery_zones WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function allForAdmin(): array
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM delivery_zones ORDER BY sort_order ASC")->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO delivery_zones (name, charge, is_default, sort_order, is_active) VALUES (:name, :charge, :def, :sort, :active)"
        );
        $stmt->execute([
            'name' => $data['name'], 'charge' => $data['charge'], 'def' => $data['is_default'] ?? 0,
            'sort' => $data['sort_order'] ?? 0, 'active' => $data['is_active'] ?? 1,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::connect();
        if (!empty($data['is_default'])) {
            $db->exec("UPDATE delivery_zones SET is_default = 0");
        }
        $stmt = $db->prepare(
            "UPDATE delivery_zones SET name = :name, charge = :charge, is_default = :def,
                sort_order = :sort, is_active = :active WHERE id = :id"
        );
        $stmt->execute([
            'name' => $data['name'], 'charge' => $data['charge'], 'def' => $data['is_default'] ?? 0,
            'sort' => $data['sort_order'] ?? 0, 'active' => $data['is_active'] ?? 1, 'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $db = Database::connect();
        $db->prepare("DELETE FROM delivery_zones WHERE id = :id")->execute(['id' => $id]);
    }
}

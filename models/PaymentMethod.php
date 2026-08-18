<?php

class PaymentMethod
{
    public static function allActive(): array
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findActive(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function allForAdmin(): array
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM payment_methods ORDER BY sort_order ASC")->fetchAll();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "UPDATE payment_methods SET name = :name, account_number = :account, instructions = :instructions,
                requires_reference = :requires_reference, is_active = :active WHERE id = :id"
        );
        $stmt->execute([
            'name' => $data['name'], 'account' => $data['account_number'] ?: null,
            'instructions' => $data['instructions'] ?: null,
            'requires_reference' => $data['requires_reference'] ?? 1,
            'active' => $data['is_active'] ?? 1, 'id' => $id,
        ]);
    }

    public static function create(array $data): int
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO payment_methods (code, name, account_number, instructions, requires_reference, is_active, sort_order)
             VALUES (:code, :name, :account, :instructions, :requires_reference, :active, :sort)"
        );
        $stmt->execute([
            'code' => $data['code'], 'name' => $data['name'], 'account' => $data['account_number'] ?: null,
            'instructions' => $data['instructions'] ?: null,
            'requires_reference' => $data['requires_reference'] ?? 1,
            'active' => $data['is_active'] ?? 1,
            'sort' => $data['sort_order'] ?? 0,
        ]);
        return (int) $db->lastInsertId();
    }
}

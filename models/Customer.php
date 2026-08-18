<?php

class Customer
{
    public static function findByPhone(string $phone): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM customers WHERE phone = :phone LIMIT 1");
        $stmt->execute(['phone' => $phone]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM customers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function register(string $fullName, string $phone, ?string $email, string $password): array
    {
        if (self::findByPhone($phone)) {
            return ['success' => false, 'message' => 'An account with this phone number already exists.'];
        }
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO customers (full_name, phone, email, password_hash) VALUES (:name, :phone, :email, :hash)"
        );
        $stmt->execute([
            'name' => $fullName, 'phone' => $phone, 'email' => $email ?: null,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        return ['success' => true, 'id' => (int) $db->lastInsertId()];
    }

    public static function attemptLogin(string $phone, string $password): ?array
    {
        $customer = self::findByPhone($phone);
        if (!$customer || !$customer['password_hash'] || !password_verify($password, $customer['password_hash'])) {
            return null;
        }
        return $customer;
    }

    // ---- Admin ----------------------------------------------------

    public static function allForAdmin(string $search = ''): array
    {
        $db = Database::connect();
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = "WHERE c.full_name LIKE :s OR c.phone LIKE :s OR c.email LIKE :s";
            $params['s'] = '%' . $search . '%';
        }
        $stmt = $db->prepare(
            "SELECT c.*, COUNT(o.id) AS order_count, COALESCE(SUM(o.total),0) AS total_spent, MAX(o.created_at) AS last_order_at
             FROM customers c LEFT JOIN orders o ON o.customer_id = c.id
             {$where}
             GROUP BY c.id ORDER BY total_spent DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Guest checkouts also roll up under their phone number for the admin's customer view. */
    public static function guestSummaries(string $search = ''): array
    {
        $db = Database::connect();
        $where = "WHERE o.customer_id IS NULL";
        $params = [];
        if ($search !== '') {
            $where .= " AND (o.full_name LIKE :s OR o.phone LIKE :s)";
            $params['s'] = '%' . $search . '%';
        }
        $stmt = $db->prepare(
            "SELECT o.phone, MAX(o.full_name) AS full_name, MAX(o.email) AS email,
                    COUNT(o.id) AS order_count, COALESCE(SUM(o.total),0) AS total_spent, MAX(o.created_at) AS last_order_at
             FROM orders o {$where}
             GROUP BY o.phone ORDER BY total_spent DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

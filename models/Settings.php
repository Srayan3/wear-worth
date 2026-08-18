<?php

class Settings
{
    private static ?array $cache = null;

    private static function load(): array
    {
        if (self::$cache === null) {
            $db = Database::connect();
            $rows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
            self::$cache = [];
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        $all = self::load();
        return $all[$key] ?? $default;
    }

    public static function all(): array
    {
        return self::load();
    }

    public static function set(string $key, string $value): void
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = :v2"
        );
        $stmt->execute(['k' => $key, 'v' => $value, 'v2' => $value]);
        self::$cache = null; // bust cache
    }

    /** Persist many settings at once (used by the admin settings form). */
    public static function setMany(array $pairs): void
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = :v2"
        );
        foreach ($pairs as $key => $value) {
            $stmt->execute(['k' => $key, 'v' => $value, 'v2' => $value]);
        }
        self::$cache = null;
    }
}

function setting(string $key, string $default = ''): string
{
    return Settings::get($key, $default);
}

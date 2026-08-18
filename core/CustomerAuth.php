<?php

/** Lightweight optional-account auth for storefront customers. Guest checkout never requires this. */
class CustomerAuth
{
    public static function login(array $customer): void
    {
        session_regenerate_id(true);
        $_SESSION['customer_id'] = (int) $customer['id'];
        $_SESSION['customer_name'] = $customer['full_name'];
    }

    public static function logout(): void
    {
        unset($_SESSION['customer_id'], $_SESSION['customer_name']);
    }

    public static function check(): bool
    {
        return !empty($_SESSION['customer_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['customer_id'] ?? null;
    }

    public static function name(): string
    {
        return $_SESSION['customer_name'] ?? '';
    }
}

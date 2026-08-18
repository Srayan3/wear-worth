<?php

class PageController
{
    public static function about(): void
    {
        render('about', [], [
            'title'       => setting('about_heading', 'Our Story') . ' — ' . setting('store_name', 'Atelier'),
            'description' => setting('meta_default_description', ''),
        ]);
    }

    public static function contact(): void
    {
        render('contact', [], ['title' => 'Contact — ' . setting('store_name', 'Atelier')]);
    }

    public static function newsletter(): void
    {
        csrf_verify();
        $email = clean_str($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'message' => 'Please enter a valid email address.']);
        }
        $db = Database::connect();
        try {
            $db->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (:email)")->execute(['email' => $email]);
        } catch (Throwable $e) {
            error_log('[NEWSLETTER ERROR] ' . $e->getMessage());
        }
        json_response(['success' => true, 'message' => "You're on the list."]);
    }
}

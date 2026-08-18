<?php
require_once __DIR__ . '/bootstrap.php';

if (is_post()) {
    csrf_verify();

    $fields = [
        'store_name', 'store_tagline', 'store_phone', 'store_email', 'store_address',
        'facebook_url', 'instagram_url', 'currency_symbol',
        'homepage_hero_heading', 'homepage_hero_subtext', 'homepage_hero_cta_text', 'homepage_hero_cta_link',
        'promo_heading', 'about_heading', 'about_body', 'footer_text',
        'trust_delivery_text', 'trust_return_text', 'trust_payment_text',
        'meta_default_title', 'meta_default_description',
    ];
    $pairs = [];
    foreach ($fields as $f) {
        $pairs[$f] = clean_str($_POST[$f] ?? '');
    }

    // Logo / favicon / hero image uploads (optional)
    foreach (['store_logo' => 'logo', 'store_favicon' => 'favicon', 'homepage_hero_image' => 'hero_image'] as $settingKey => $inputName) {
        if (!empty($_FILES[$inputName]['name'])) {
            $result = ImageUpload::handleProductImage($_FILES[$inputName]);
            if ($result['success']) {
                $pairs[$settingKey] = $result['path'];
            } else {
                flash_set('error', ucfirst(str_replace('_', ' ', $inputName)) . ': ' . $result['message']);
            }
        }
    }

    Settings::setMany($pairs);
    flash_set('success', 'Settings saved.');
    redirect('admin/settings.php');
}

$activeNav = 'settings';
admin_render('settings', ['settings' => Settings::all()], 'Store Settings');

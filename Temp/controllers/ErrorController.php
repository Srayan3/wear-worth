<?php

class ErrorController
{
    public static function notFound(): void
    {
        http_response_code(404);
        render('errors/404', [], ['title' => 'Page Not Found — ' . setting('store_name', 'Atelier')]);
    }
}

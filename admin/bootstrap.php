<?php
/**
 * Every admin page (except login.php) starts with:
 *   require __DIR__ . '/bootstrap.php';
 * This loads the shared core/models and enforces authentication.
 */

require_once __DIR__ . '/../core/bootstrap.php';

Auth::requireLogin();

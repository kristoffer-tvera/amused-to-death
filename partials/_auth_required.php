<?php

/**
 * Authentication Guard
 * Include this file at the top of any page that requires authentication.
 * Redirects to home page if user is not logged in (no auth or admin session).
 */

if (!isset($_SESSION['auth']) && !isset($_SESSION['admin'])) {
    header('Location: /');
    exit;
}

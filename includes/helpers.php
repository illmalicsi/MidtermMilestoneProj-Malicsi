<?php
// includes/helpers.php - Global utility helper functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize and escape string for safe HTML rendering (XSS Protection)
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Store a flash notification message in session
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Retrieve and remove a flash notification message from session
 */
function getFlash(string $type): ?string {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

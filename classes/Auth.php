<?php
// classes/Auth.php - Session management and access control

class Auth {
    /**
     * Start session if not already started
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Save authenticated user data into session
     */
    public static function login(array $user): void {
        self::start();
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email']
        ];
    }

    /**
     * Clear session data and log out user
     */
    public static function logout(): void {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Check if a member is currently logged in
     */
    public static function check(): bool {
        self::start();
        return !empty($_SESSION['user']['id']);
    }

    /**
     * Return currently logged-in user data
     */
    public static function user(): ?array {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Return ID of current logged-in user
     */
    public static function id(): ?int {
        self::start();
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }

    /**
     * Restrict page to logged-in members; redirect guests to login
     */
    public static function requireLogin(): void {
        self::start();
        if (!self::check()) {
            $_SESSION['flash']['info'] = 'Please log in or create an account to access BaryoBites.';
            header('Location: login.php');
            exit;
        }
    }
}

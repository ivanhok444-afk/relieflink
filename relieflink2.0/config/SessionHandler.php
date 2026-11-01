<?php
/**
 * Session Handler
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/config.php';

class AppSessionHandler {
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => self::sessionCookiePath(),
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();

            $lastActivity = $_SESSION['last_activity'] ?? $_SESSION['login_time'] ?? time();
            if (isset($_SESSION['user_id']) && time() - $lastActivity > SESSION_TIMEOUT) {
                self::logout();
                session_id('');
                session_start();
            }

            if (isset($_SESSION['user_id'])) {
                $_SESSION['last_activity'] = time();
            }
        }
    }

    private static function sessionCookiePath() {
        return APP_BASE_PATH === '' ? '/' : APP_BASE_PATH . '/';
    }

    public static function createSession($user_id, $username, $role) {
        self::startSession();
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }

    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function getUserRole() {
        self::startSession();
        return $_SESSION['role'] ?? null;
    }

    public static function getUserId() {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function logout() {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]);
        }
        session_destroy();
    }

    public static function csrfToken() {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField() {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validateCsrfToken() {
        self::startSession();
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals(self::csrfToken(), $token)) {
            http_response_code(403);
            exit('Invalid or expired form token. Please go back, reload the form, and try again.');
        }
    }

    public static function isAdmin() {
        return self::getUserRole() === 'admin';
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_BASE_PATH . '/views/user/login.php');
            exit;
        }
    }

    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: ' . APP_BASE_PATH . '/views/user/dashboard.php');
            exit;
        }
    }
}
?>

<?php
/**
 * Authentication Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($username) || !is_string($password) || $username === '' || $password === '') {
        $_SESSION['error'] = 'Please fill in all fields';
        header('Location: ' . loginRedirect());
        exit;
    }

    $user = new User();
    $auth = $user->authenticate($username, $password);

    if ($auth) {
        AppSessionHandler::createSession($auth['id'], $auth['username'], $auth['role']);
        
        if ($auth['role'] === 'admin') {
            header('Location: ../views/admin/dashboard.php');
        } else {
            header('Location: ../views/user/dashboard.php');
        }
        exit;
    } else {
        $_SESSION['error'] = 'Invalid username or password';
        header('Location: ' . loginRedirect());
        exit;
    }
} else {
    header('Location: ../views/user/login.php');
    exit;
}

function loginRedirect() {
    return ($_POST['redirect'] ?? '') === '../views/admin/login.php'
        ? '../views/admin/login.php'
        : '../views/user/login.php';
}
?>

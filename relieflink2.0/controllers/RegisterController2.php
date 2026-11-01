<?php
/**
 * Register Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (!is_string($password) || !is_string($confirm_password)
        || empty($first_name) || empty($last_name) || empty($email) || empty($username) || $password === '') {
        $_SESSION['error'] = 'Please fill in all fields';
        header('Location: ../views/register.php');
        exit;
    }

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $_SESSION['error'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
        header('Location: ../views/register.php');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: ../views/register.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address';
        header('Location: ../views/register.php');
        exit;
    }

    $user = new User();
    
    if ($user->createUser($username, $email, $password, $first_name, $last_name, 'user')) {
        $_SESSION['success'] = 'Registration successful! Please login with your credentials.';
        header('Location: ../views/user/login.php');
        exit;
    } else {
        $_SESSION['error'] = 'Registration failed. Username or email may already exist.';
        header('Location: ../views/register.php');
        exit;
    }
} else {
    header('Location: ../views/register.php');
    exit;
}
?>

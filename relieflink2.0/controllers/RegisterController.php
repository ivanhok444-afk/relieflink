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
    $agree = $_POST['agree'] ?? '';
    $province = trim((string) ($_POST['province'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $barangay = trim((string) ($_POST['barangay'] ?? ''));
    $hasLocation = ($province !== '' || $city !== '' || $barangay !== '');

    if (!is_string($password) || !is_string($confirm_password)
        || empty($first_name) || empty($last_name) || empty($email) || empty($username) || $password === '') {
        $_SESSION['error'] = 'Please fill in all fields';
        header('Location: ../views/register.php');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: ../views/register.php');
        exit;
    }

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $_SESSION['error'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        header('Location: ../views/register.php');
        exit;
    }

    if (!$agree) {
        $_SESSION['error'] = 'You must agree to the Terms and Conditions';
        header('Location: ../views/register.php');
        exit;
    }

    $user = new User();

    if ($user->usernameExists($username)) {
        $_SESSION['error'] = 'Username already exists';
        header('Location: ../views/register.php');
        exit;
    }

    if ($user->emailExists($email)) {
        $_SESSION['error'] = 'Email already exists';
        header('Location: ../views/register.php');
        exit;
    }

    // Validate the selected address against the bundled dataset: the
    // municipality must belong to the province and the barangay to that
    // municipality. Placeholder / partial selections are rejected.
    $location = '';
    if ($hasLocation) {
        if ($province === '' || $city === '' || $barangay === ''
            || !User::isValidAddress($province, $city, $barangay)) {
            $_SESSION['error'] = 'Please select a valid Province, Municipality, and Barangay.';
            header('Location: ../views/register.php');
            exit;
        }
        $location = User::buildLocationLabel($barangay, $city, $province);
    }

    $result = $user->register($first_name, $last_name, $email, $username, $password, $location);

    if ($result) {
        $_SESSION['success'] = 'Registration successful! You can now login.';
        header('Location: ../views/user/login.php');
        exit;
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: ../views/register.php');
        exit;
    }
} else {
    header('Location: ../views/user/login.php');
    exit;
}

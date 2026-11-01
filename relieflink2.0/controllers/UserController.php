<?php
/**
 * User Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/User.php';

AppSessionHandler::requireAdmin();

$user = new User();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $action = $_POST['action'] ?? '';

    // Location can arrive as a free-text field (edit form) or as the
    // Province / City-Municipality / Barangay dropdowns (create form).
    $province = trim((string) ($_POST['province'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $barangay = trim((string) ($_POST['barangay'] ?? ''));
    $hasAddress = ($province !== '' || $city !== '' || $barangay !== '');

    // Sanitize the optional free-text location once; an empty value keeps any
    // previously stored location intact (handled in the model).
    $location = User::sanitizeLocation($_POST['location'] ?? '');
    $addressError = '';
    if ($hasAddress) {
        if ($province === '' || $city === '' || $barangay === '' || !User::isValidAddress($province, $city, $barangay)) {
            $addressError = 'Please select a valid Province, City/Municipality, and Barangay.';
        } else {
            $location = User::buildLocationLabel($barangay, $city, $province);
        }
    }

    if ($action === 'create') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if (!is_string($password) || empty($username) || empty($email) || $password === '' || empty($first_name) || empty($last_name)) {
            $error = 'Please fill in all fields';
        } elseif ($addressError !== '') {
            $error = $addressError;
        } elseif (!in_array($role, ['admin', 'staff', 'user'], true)) {
            $error = 'Invalid user role';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        } elseif ($user->usernameExists($username)) {
            $error = 'Username already exists';
        } elseif ($user->emailExists($email)) {
            $error = 'Email already exists';
        } else {
            if ($user->createUser($username, $email, $password, $first_name, $last_name, $role, $location)) {
                $_SESSION['success'] = 'User created successfully';
                header('Location: ' . APP_BASE_PATH . '/views/admin/users.php');
                exit;
            } else {
                $error = 'Failed to create user';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $email = $_POST['email'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';

        if (!is_string($password) || empty($email) || empty($first_name) || empty($last_name)) {
            $error = 'Please fill in all required fields';
        } else {
            if (!in_array($role, ['admin', 'staff', 'user'], true)) {
                $error = 'Invalid user role';
            } elseif (!empty($password) && strlen($password) < PASSWORD_MIN_LENGTH) {
                $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
            } elseif ($user->updateAdminUser($id, $email, $first_name, $last_name, $role, $password, $location)) {
                $_SESSION['success'] = 'User updated successfully';
            } else {
                $error = 'Failed to update user';
            }
            if (!$error) {
                header('Location: ' . APP_BASE_PATH . '/views/admin/users.php');
                exit;
            }
        }
    } elseif ($action === 'toggle') {
        $id = $_POST['id'] ?? '';
        if ($user->toggleUserActive($id)) {
            $_SESSION['success'] = 'User status updated';
        } else {
            $error = 'Failed to update user status';
        }
        header('Location: ' . APP_BASE_PATH . '/views/admin/users.php');
        exit;
    }
}

if ($error) {
    $_SESSION['error'] = $error;
}

header('Location: ' . APP_BASE_PATH . '/views/admin/users.php');
exit;

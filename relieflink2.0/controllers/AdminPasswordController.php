<?php
require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/User.php';

AppSessionHandler::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_PATH . '/views/admin/settings.php');
    exit;
}

AppSessionHandler::validateCsrfToken();

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (!is_string($currentPassword) || !is_string($newPassword) || !is_string($confirmPassword)) {
    $_SESSION['error'] = 'Please enter valid password values.';
    header('Location: ' . APP_BASE_PATH . '/views/admin/settings.php');
    exit;
}

$user = new User();
$userData = $user->getUserById(AppSessionHandler::getUserId());
$currentPasswordValid = $userData && (
    password_verify($currentPassword, $userData['password'])
    || hash_equals($userData['password'], hash('sha256', $currentPassword))
);

if (!$userData || !$currentPasswordValid) {
    $_SESSION['error'] = 'Current password is incorrect.';
} elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
    $_SESSION['error'] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
} elseif ($newPassword !== $confirmPassword) {
    $_SESSION['error'] = 'New passwords do not match.';
} elseif ($newPassword === $currentPassword) {
    $_SESSION['error'] = 'Choose a password different from your current password.';
} elseif (!$user->updatePassword($userData['id'], $newPassword)) {
    $_SESSION['error'] = 'Unable to change the password. Please try again.';
} else {
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['success'] = 'Admin password changed successfully.';
}

header('Location: ' . APP_BASE_PATH . '/views/admin/settings.php#security');
exit;

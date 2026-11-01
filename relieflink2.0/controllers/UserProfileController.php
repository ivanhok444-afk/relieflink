<?php
/**
 * User Profile Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Household.php';

AppSessionHandler::requireLogin();

$user = new User();
$household = new Household();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $action = $_POST['action'] ?? '';
    $user_id = AppSessionHandler::getUserId();

    if ($action === 'update_profile') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';

        if (empty($first_name) || empty($last_name) || empty($email)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($user->updateUser($user_id, $email, $first_name, $last_name)) {
                $_SESSION['success'] = 'Profile updated successfully';
            } else {
                $error = 'Failed to update profile';
            }
        }
    } elseif ($action === 'update_household') {
        $user_data = $user->getUserById($user_id);
        $household_data = $household->getHouseholdByUserId($user_id);

        if ($household_data) {
            $data = [
                'household_name' => $_POST['household_name'] ?? '',
                'street_address' => $household_data['street_address'],
                'barangay' => $household_data['barangay'],
                'city' => $household_data['city'],
                'province' => $household_data['province'],
                'postal_code' => $household_data['postal_code'],
                'contact_number' => $_POST['contact_number'] ?? '',
                'total_members' => $_POST['total_members'] ?? 0,
                'monthly_income' => $_POST['monthly_income'] ?? 0,
                'is_eligible' => $household_data['is_eligible'],
                'eligibility_reason' => $household_data['eligibility_reason']
            ];

            if ($household->updateHousehold($household_data['id'], $data)) {
                $_SESSION['success'] = 'Household information updated successfully';
            } else {
                $error = 'Failed to update household information';
            }
        } else {
            $error = 'No household found for your account';
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $user_data = $user->getUserById($user_id);

        $currentPasswordValid = $user_data && is_string($current_password)
            && (password_verify($current_password, $user_data['password'])
                || hash_equals($user_data['password'], hash('sha256', $current_password)));
        if (!$user_data) {
            $error = 'Account could not be found';
        } elseif (!$currentPasswordValid) {
            $error = 'Current password is incorrect';
        } elseif (!is_string($new_password) || !is_string($confirm_password)
            || strlen($new_password) < PASSWORD_MIN_LENGTH) {
            $error = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } else {
            if ($user->updatePassword($user_id, $new_password)) {
                $_SESSION['success'] = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
        }
    }
}

if ($error) {
    $_SESSION['error'] = $error;
}

if ($message) {
    $_SESSION['success'] = $message;
}

$redirect = ($_POST['redirect'] ?? '') === 'my-household.php'
    ? APP_BASE_PATH . '/views/user/my-household.php'
    : APP_BASE_PATH . '/views/user/profile.php';
header('Location: ' . $redirect);
exit;

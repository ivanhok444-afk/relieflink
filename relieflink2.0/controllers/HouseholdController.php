<?php
/**
 * Household Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../config/QuirinoAddress.php';
require_once __DIR__ . '/../models/Household.php';

AppSessionHandler::requireAdmin();

$household = new Household();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $data = [
            'user_id' => $_POST['user_id'] ?? null,
            'household_name' => $_POST['household_name'] ?? '',
            'household_id' => $_POST['household_id'] ?? '',
            'street_address' => $_POST['street_address'] ?? '',
            'barangay' => $_POST['barangay'] ?? '',
            'city' => $_POST['city'] ?? '',
            'province' => $_POST['province'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'contact_number' => $_POST['contact_number'] ?? '',
            'total_members' => $_POST['total_members'] ?? 0,
            'monthly_income' => $_POST['monthly_income'] ?? 0,
            'qr_code' => $_POST['qr_code'] ?? null
        ];
        if (!preg_match('/^QRN-\d{4}-[A-F0-9]{8}$/', $data['household_id']) || $household->householdIdExists($data['household_id'])) {
            $data['household_id'] = $household->generateHouseholdId();
        }
        $data['qr_code'] = $data['household_id'];

        if (!QuirinoAddress::isValid($data['province'], $data['city'], $data['barangay'])) {
            $error = 'Select a valid municipality and barangay in Quirino Province.';
        } elseif (empty($data['household_name']) || empty($data['household_id'])) {
            $error = 'Household name and ID are required';
        } else {
            if ($household->createHousehold($data)) {
                $_SESSION['success'] = 'Household added successfully';
                header('Location: ' . APP_BASE_PATH . '/views/admin/households.php');
                exit;
            } else {
                $error = 'Failed to add household. Household ID may already exist.';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $data = [
            'household_name' => $_POST['household_name'] ?? '',
            'street_address' => $_POST['street_address'] ?? '',
            'barangay' => $_POST['barangay'] ?? '',
            'city' => $_POST['city'] ?? '',
            'province' => $_POST['province'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'contact_number' => $_POST['contact_number'] ?? '',
            'total_members' => $_POST['total_members'] ?? 0,
            'monthly_income' => $_POST['monthly_income'] ?? 0,
            'is_eligible' => isset($_POST['is_eligible']) ? 1 : 0,
            'eligibility_reason' => $_POST['eligibility_reason'] ?? ''
        ];

        if (!QuirinoAddress::isValid($data['province'], $data['city'], $data['barangay'])) {
            $error = 'Select a valid municipality and barangay in Quirino Province.';
        } elseif (empty($data['household_name'])) {
            $error = 'Household name is required';
        } else {
            if ($household->updateHousehold($id, $data)) {
                $_SESSION['success'] = 'Household updated successfully';
                header('Location: ' . APP_BASE_PATH . '/views/admin/households.php');
                exit;
            } else {
                $error = 'Failed to update household';
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($household->deleteHousehold($id)) {
            $_SESSION['success'] = 'Household deleted successfully';
        } else {
            $error = 'Failed to delete household';
        }
        header('Location: ' . APP_BASE_PATH . '/views/admin/households.php');
        exit;
    }
}

if ($error) {
    $_SESSION['error'] = $error;
}

header('Location: ' . APP_BASE_PATH . '/views/admin/households.php');
exit;

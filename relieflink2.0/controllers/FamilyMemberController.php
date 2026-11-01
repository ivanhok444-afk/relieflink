<?php
/**
 * Family Member Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/FamilyMember.php';
require_once __DIR__ . '/../models/Household.php';

AppSessionHandler::requireAdmin();

$member = new FamilyMember();
$household = new Household();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $data = [
            'household_id' => $_POST['household_id'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'age' => $_POST['age'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'relationship' => $_POST['relationship'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'is_senior' => isset($_POST['is_senior']) ? 1 : 0,
            'is_pwd' => isset($_POST['is_pwd']) ? 1 : 0
        ];

        if (empty($data['household_id']) || empty($data['first_name']) || empty($data['last_name'])) {
            $error = 'Household, first name, and last name are required';
        } else {
            if ($member->createMember($data)) {
                $_SESSION['success'] = 'Family member added successfully';
                header('Location: ' . APP_BASE_PATH . '/views/admin/household-detail.php?id=' . $data['household_id']);
                exit;
            } else {
                $error = 'Failed to add family member';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $household_id = $_POST['household_id'] ?? '';

        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'age' => $_POST['age'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'relationship' => $_POST['relationship'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'is_senior' => isset($_POST['is_senior']) ? 1 : 0,
            'is_pwd' => isset($_POST['is_pwd']) ? 1 : 0
        ];

        if (empty($data['first_name']) || empty($data['last_name'])) {
            $error = 'First name and last name are required';
        } else {
            if ($member->updateMember($id, $data)) {
                $_SESSION['success'] = 'Family member updated successfully';
                header('Location: ' . APP_BASE_PATH . '/views/admin/household-detail.php?id=' . $household_id);
                exit;
            } else {
                $error = 'Failed to update family member';
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $household_id = $_POST['household_id'] ?? '';

        if ($member->deleteMember($id)) {
            $_SESSION['success'] = 'Family member deleted successfully';
        } else {
            $error = 'Failed to delete family member';
        }
        header('Location: ' . APP_BASE_PATH . '/views/admin/household-detail.php?id=' . $household_id);
        exit;
    }
}

if ($error) {
    $_SESSION['error'] = $error;
}

header('Location: ' . APP_BASE_PATH . '/views/admin/households.php');
exit;

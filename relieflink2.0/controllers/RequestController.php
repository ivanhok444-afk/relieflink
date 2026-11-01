<?php
/**
 * Relief Request Controller - Admin Actions
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/ReliefRequest.php';

AppSessionHandler::requireAdmin();

$request_model = new ReliefRequest();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? 0;

    if ($action === 'approve' && $request_id) {
        $user_id = AppSessionHandler::getUserId();
        $notes = $_POST['notes'] ?? '';
        if ($request_model->updateRequestStatus($request_id, 'Approved', $user_id, $notes)) {
            $_SESSION['success'] = 'Request approved successfully';
        } else {
            $_SESSION['error'] = 'Failed to approve request';
        }
    } elseif ($action === 'reject' && $request_id) {
        $user_id = AppSessionHandler::getUserId();
        $notes = $_POST['notes'] ?? '';
        if ($request_model->updateRequestStatus($request_id, 'Rejected', $user_id, $notes)) {
            $_SESSION['success'] = 'Request rejected';
        } else {
            $_SESSION['error'] = 'Failed to reject request';
        }
    }

    header('Location: ' . APP_BASE_PATH . '/views/admin/request-detail.php?id=' . $request_id);
    exit;
}

header('Location: ' . APP_BASE_PATH . '/views/admin/requests.php');
exit;

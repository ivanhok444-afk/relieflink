<?php
/**
 * Home Redirect Page
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/config/SessionHandler.php';
AppSessionHandler::startSession();

// If user is logged in, redirect to their dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: views/admin/dashboard.php');
    } else {
        header('Location: views/user/dashboard.php');
    }
    exit;
}

// If not logged in, redirect to landing page
header('Location: landing.php');
exit;
?>

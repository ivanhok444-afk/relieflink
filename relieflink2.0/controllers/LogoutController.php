<?php
/**
 * Logout Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
AppSessionHandler::logout();

header('Location: ../landing.php');
exit;
?>

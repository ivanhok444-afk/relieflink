<?php
require_once __DIR__ . '/../../config/SessionHandler.php';
AppSessionHandler::requireAdmin();

header('Location: users.php');
exit;

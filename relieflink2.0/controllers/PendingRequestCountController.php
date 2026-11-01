<?php
require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/ReliefRequest.php';

AppSessionHandler::requireAdmin();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$request = new ReliefRequest();
echo json_encode(['count' => (int) $request->getPendingCount()]);

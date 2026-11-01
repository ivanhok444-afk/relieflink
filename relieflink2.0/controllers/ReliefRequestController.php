<?php
/**
 * Relief Request Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/ReliefRequest.php';
require_once __DIR__ . '/../models/Household.php';
require_once __DIR__ . '/../models/ReliefGoods.php';

AppSessionHandler::requireLogin();

$request_model = new ReliefRequest();
$household = new Household();
$goods = new ReliefGoods();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $user_id = AppSessionHandler::getUserId();

    $household_data = $household->getHouseholdByUserId($user_id);

    if (!$household_data) {
        $error = 'No household found for your account. Please contact administrator.';
    } else {
        $household_id = $household_data['id'];
        $request_reason = $_POST['request_reason'] ?? '';
        $items = $_POST['items'] ?? [];
        $selected_items = [];
        if (is_array($items)) {
            foreach ($items as $goods_id => $quantity) {
                $goods_id = filter_var($goods_id, FILTER_VALIDATE_INT);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
                if ($goods_id && $quantity !== false && $quantity > 0) {
                    $selected_items[$goods_id] = $quantity;
                }
            }
        }

        if (empty($request_reason)) {
            $error = 'Please provide a request reason';
        } elseif (empty($selected_items)) {
            $error = 'Please select at least one item to request';
        } elseif (count($selected_items) > MAX_GOODS_PER_REQUEST) {
            $error = 'You can request up to ' . MAX_GOODS_PER_REQUEST . ' different goods at a time.';
        } else {
            foreach ($selected_items as $goods_id => $quantity) {
                $available_item = $goods->getGoodsById($goods_id);
                if (!$available_item || !$available_item['is_active'] || $quantity > $available_item['quantity_in_stock']) {
                    $error = 'One or more selected quantities exceed available stock. Please review your request.';
                    break;
                }
            }

            if (!$error) {
                $request_id = $request_model->createRequest($household_id, $request_reason);

                if ($request_id) {
                    foreach ($selected_items as $relief_goods_id => $quantity) {
                        $request_model->addRequestItem($request_id, $relief_goods_id, $quantity);
                    }
                    $success = 'Your relief request has been submitted successfully and is pending review.';
                } else {
                    $error = 'Failed to submit request. Please try again.';
                }
            }
        }
    }
}

if ($error) {
    $_SESSION['error'] = $error;
}

if ($success) {
    $_SESSION['success'] = $success;
}

header('Location: ' . APP_BASE_PATH . '/views/user/relief-request.php');
exit;

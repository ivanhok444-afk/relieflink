<?php
/**
 * Distribution Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/Distribution.php';
require_once __DIR__ . '/../models/Household.php';
require_once __DIR__ . '/../models/ReliefGoods.php';
require_once __DIR__ . '/../models/ReliefRequest.php';

AppSessionHandler::requireAdmin();

$distribution = new Distribution();
$household = new Household();
$goods = new ReliefGoods();
$request_model = new ReliefRequest();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_batch') {
        $batch_code = $_POST['batch_code'] ?? '';
        $batch_date = $_POST['batch_date'] ?? date('Y-m-d');
        $description = $_POST['description'] ?? '';

        if (empty($batch_code) || empty($batch_date)) {
            $error = 'Batch code and date are required';
        } else {
            $user_id = AppSessionHandler::getUserId();
            $batch_id = $distribution->createBatch($batch_code, $batch_date, $description, $user_id);
            if ($batch_id) {
                $_SESSION['success'] = 'Distribution batch created successfully';
                header('Location: ' . APP_BASE_PATH . '/views/admin/distribution-detail.php?id=' . $batch_id);
                exit;
            } else {
                $error = 'Failed to create batch. Batch code may already exist.';
            }
        }
    } elseif ($action === 'scan_qr') {
        $qr_code = trim($_POST['qr_code'] ?? '');
        $batch_id = $_POST['batch_id'] ?? '';

        if (empty($qr_code)) {
            $error = 'Please enter or scan a QR code';
        } else {
            $household_data = $household->getHouseholdByCode($qr_code);
            if (!$household_data) {
                $error = 'Invalid QR code. Household not found.';
            } else {
                $_SESSION['scan_household_id'] = $household_data['id'];
                $_SESSION['scan_batch_id'] = $batch_id;
                header('Location: ' . APP_BASE_PATH . '/views/admin/distribution-eligibility.php?batch_id=' . $batch_id . '&household_id=' . $household_data['id']);
                exit;
            }
        }
    } elseif ($action === 'distribute') {
        $batch_id = $_POST['batch_id'] ?? '';
        $household_id = $_POST['household_id'] ?? '';
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

        if (empty($batch_id) || empty($household_id)) {
            $error = 'Invalid batch or household';
        } elseif (empty($selected_items)) {
            $error = 'Please select at least one item to distribute';
        } elseif (count($selected_items) > MAX_GOODS_PER_REQUEST) {
            $error = 'You can distribute up to ' . MAX_GOODS_PER_REQUEST . ' different goods per household at a time.';
        } else {
            foreach ($selected_items as $goods_id => $quantity) {
                $available_item = $goods->getGoodsById($goods_id);
                if (!$available_item || !$available_item['is_active'] || $quantity > $available_item['quantity_in_stock']) {
                    $error = 'One or more selected quantities exceed available stock. Please review the distribution.';
                    break;
                }
            }

            if (!$error) {
                $user_id = AppSessionHandler::getUserId();

                $batch = $distribution->getBatchById($batch_id);
                if ($batch && $batch['status'] == 'Pending') {
                    $distribution->markBatchInProgress($batch_id);
                }

                $distribution_id = $distribution->createDistribution($batch_id, $household_id, $user_id);

                if ($distribution_id) {
                    foreach ($selected_items as $relief_goods_id => $quantity) {
                        $distribution->addDistributionDetail($distribution_id, $relief_goods_id, $quantity);
                        $goods->updateStock($relief_goods_id, $quantity, 'Stock Out');
                        $goods->addStockMovement($relief_goods_id, 'Distributed', $quantity, 'Batch: ' . $batch_id);
                    }

                    $approved_requests = $request_model->getHouseholdRequests($household_id);
                    foreach ($approved_requests as $req) {
                        if ($req['status'] == 'Approved') {
                            $request_model->updateRequestStatus($req['id'], 'Distributed', $user_id, 'Distributed in batch: ' . $batch_id);
                        }
                    }

                    $_SESSION['success'] = 'Distribution recorded successfully';
                    header('Location: ' . APP_BASE_PATH . '/views/admin/distribution-receipt.php?id=' . $distribution_id);
                    exit;
                } else {
                    $error = 'Failed to record distribution';
                }
            }
        }
    } elseif ($action === 'complete_batch') {
        $batch_id = $_POST['batch_id'] ?? '';
        if ($distribution->completeBatch($batch_id)) {
            $_SESSION['success'] = 'Batch marked as completed';
            header('Location: ' . APP_BASE_PATH . '/views/admin/distribution-detail.php?id=' . $batch_id);
            exit;
        }
    } elseif ($action === 'cancel_batch') {
        $batch_id = $_POST['batch_id'] ?? '';
        if ($distribution->cancelBatch($batch_id)) {
            $_SESSION['success'] = 'Batch cancelled';
            header('Location: ' . APP_BASE_PATH . '/views/admin/distributions.php');
            exit;
        }
    }
}

if ($error) {
    $_SESSION['error'] = $error;
}

header('Location: ' . APP_BASE_PATH . '/views/admin/distributions.php');
exit;

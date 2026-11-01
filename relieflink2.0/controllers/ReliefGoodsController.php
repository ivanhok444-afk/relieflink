<?php
/**
 * Relief Goods Controller
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/ReliefGoods.php';

AppSessionHandler::requireAdmin();

$goods = new ReliefGoods();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AppSessionHandler::validateCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $data = [
            'category_id' => $_POST['category_id'] ?? '',
            'goods_name' => $_POST['goods_name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'unit_of_measure' => $_POST['unit_of_measure'] ?? 'piece',
            'quantity_in_stock' => $_POST['quantity_in_stock'] ?? 0,
            'reorder_level' => $_POST['reorder_level'] ?? 10,
            'expiration_date' => !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null,
            'source' => $_POST['source'] ?? '',
            'unit_cost' => $_POST['unit_cost'] ?? 0
        ];

        if (empty($data['category_id']) || empty($data['goods_name'])) {
            $error = 'Category and Goods Name are required';
        } else {
            if ($goods->createGoods($data)) {
                $_SESSION['success'] = 'Relief goods added successfully';
                header('Location: ' . APP_BASE_PATH . '/views/admin/relief-goods.php');
                exit;
            } else {
                $error = 'Failed to add relief goods';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $data = [
            'category_id' => $_POST['category_id'] ?? '',
            'goods_name' => $_POST['goods_name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'unit_of_measure' => $_POST['unit_of_measure'] ?? 'piece',
            'quantity_in_stock' => $_POST['quantity_in_stock'] ?? 0,
            'reorder_level' => $_POST['reorder_level'] ?? 10,
            'expiration_date' => !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null,
            'source' => $_POST['source'] ?? '',
            'unit_cost' => $_POST['unit_cost'] ?? 0
        ];

        if (empty($data['category_id']) || empty($data['goods_name'])) {
            $error = 'Category and Goods Name are required';
        } else {
            $current = $goods->getGoodsById($id);
            $new_quantity = $data['quantity_in_stock'];
            $old_quantity = $current['quantity_in_stock'] ?? 0;
            $quantity_diff = $new_quantity - $old_quantity;

            if ($goods->updateGoods($id, $data)) {
                if ($quantity_diff !== 0) {
                    $movement_type = $quantity_diff > 0 ? 'Stock In' : 'Stock Out';
                    $goods->addStockMovement($id, $movement_type, abs($quantity_diff), 'Manual adjustment via edit');
                }
                $_SESSION['success'] = 'Relief goods updated successfully';
                header('Location: ' . APP_BASE_PATH . '/views/admin/relief-goods.php');
                exit;
            } else {
                $error = 'Failed to update relief goods';
            }
        }
    } elseif ($action === 'stock_in') {
        $id = $_POST['id'] ?? '';
        $quantity = $_POST['quantity'] ?? 0;
        $notes = $_POST['notes'] ?? '';

        if ($quantity > 0) {
            $goods->updateStock($id, $quantity, 'Stock In');
            $goods->addStockMovement($id, 'Stock In', $quantity, $notes);
            $_SESSION['success'] = 'Stock added successfully';
            header('Location: ' . APP_BASE_PATH . '/views/admin/goods-detail.php?id=' . $id);
            exit;
        }
    } elseif ($action === 'stock_out') {
        $id = $_POST['id'] ?? '';
        $quantity = $_POST['quantity'] ?? 0;
        $notes = $_POST['notes'] ?? '';

        $current = $goods->getGoodsById($id);
        if ($quantity > 0 && $current && $current['quantity_in_stock'] >= $quantity) {
            $goods->updateStock($id, $quantity, 'Stock Out');
            $goods->addStockMovement($id, 'Stock Out', $quantity, $notes);
            $_SESSION['success'] = 'Stock removed successfully';
            header('Location: ' . APP_BASE_PATH . '/views/admin/goods-detail.php?id=' . $id);
            exit;
        } else {
            $error = 'Insufficient stock or invalid quantity';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($goods->deleteGoods($id)) {
            $_SESSION['success'] = 'Relief goods deleted successfully';
        } else {
            $error = 'Failed to delete relief goods';
        }
        header('Location: ' . APP_BASE_PATH . '/views/admin/relief-goods.php');
        exit;
    }
}

// Redirect if accessed directly without POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: ' . APP_BASE_PATH . '/views/admin/relief-goods.php');
    exit;
}

if ($error) {
    $_SESSION['error'] = $error;
}

if ($message) {
    $_SESSION['success'] = $message;
}

?>

<?php
/**
 * Relief Goods Model
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/Database.php';

class ReliefGoods {
    private $conn;
    private $table = 'relief_goods';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAllGoods() {
        $query = "SELECT g.*, c.category_name FROM " . $this->table . " g
                  JOIN relief_categories c ON g.category_id = c.id
                  WHERE g.is_active = 1
                  ORDER BY g.goods_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGoodsById($id) {
        $query = "SELECT g.*, c.category_name FROM " . $this->table . " g
                  JOIN relief_categories c ON g.category_id = c.id
                  WHERE g.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createGoods($data) {
        $query = "INSERT INTO " . $this->table . "
                  (category_id, goods_name, description, unit_of_measure, quantity_in_stock, reorder_level, expiration_date, source, unit_cost)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['category_id'],
            $data['goods_name'],
            $data['description'],
            $data['unit_of_measure'],
            $data['quantity_in_stock'],
            $data['reorder_level'],
            $data['expiration_date'] ?? null,
            $data['source'],
            $data['unit_cost']
        ]);
    }

    public function updateStock($id, $quantity, $type = 'Stock In') {
        $current = $this->getGoodsById($id);
        if (!$current) return false;

        $new_quantity = $type === 'Stock In' ? 
            $current['quantity_in_stock'] + $quantity : 
            $current['quantity_in_stock'] - $quantity;

        $query = "UPDATE " . $this->table . " SET quantity_in_stock = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$new_quantity, $id]);
    }

    public function getLowStockItems() {
        $query = "SELECT g.*, c.category_name FROM " . $this->table . " g
                  JOIN relief_categories c ON g.category_id = c.id
                  WHERE g.quantity_in_stock <= g.reorder_level AND g.is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalStock() {
        $query = "SELECT SUM(quantity_in_stock) as total FROM " . $this->table . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getCategorySummary() {
        $query = "SELECT c.category_name, COUNT(g.id) as count, SUM(g.quantity_in_stock) as total_qty
                  FROM relief_categories c
                  LEFT JOIN " . $this->table . " g ON c.id = g.category_id AND g.is_active = 1
                  GROUP BY c.id
                  ORDER BY c.category_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCategories() {
        $query = "SELECT * FROM relief_categories WHERE is_active = 1 ORDER BY category_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateGoods($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  category_id = ?, goods_name = ?, description = ?, unit_of_measure = ?,
                  quantity_in_stock = ?, reorder_level = ?, expiration_date = ?, source = ?, unit_cost = ?
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['category_id'],
            $data['goods_name'],
            $data['description'],
            $data['unit_of_measure'],
            $data['quantity_in_stock'],
            $data['reorder_level'],
            $data['expiration_date'] ?? null,
            $data['source'],
            $data['unit_cost'],
            $id
        ]);
    }

    public function deleteGoods($id) {
        $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function addStockMovement($relief_goods_id, $movement_type, $quantity, $notes = '', $reference_no = '') {
        $query = "INSERT INTO stock_movements
                  (relief_goods_id, movement_type, quantity, reference_no, notes, created_by)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $relief_goods_id,
            $movement_type,
            $quantity,
            $reference_no,
            $notes,
            $_SESSION['user_id'] ?? null
        ]);
    }

    public function getStockMovements($relief_goods_id, $limit = 50) {
        $query = "SELECT sm.*, u.username as created_by_name
                  FROM stock_movements sm
                  LEFT JOIN users u ON sm.created_by = u.id
                  WHERE sm.relief_goods_id = ?
                  ORDER BY sm.created_at DESC
                  LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$relief_goods_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExpiringItems($days = 30) {
        $query = "SELECT g.*, c.category_name FROM " . $this->table . " g
                  JOIN relief_categories c ON g.category_id = c.id
                  WHERE g.expiration_date IS NOT NULL
                  AND g.expiration_date <= DATE_ADD(NOW(), INTERVAL ? DAY)
                  AND g.is_active = 1
                  ORDER BY g.expiration_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

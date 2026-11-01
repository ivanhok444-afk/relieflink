<?php
/**
 * Relief Request Model
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/Database.php';

class ReliefRequest {
    private $conn;
    private $request_table = 'relief_requests';
    private $items_table = 'request_items';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function createRequest($household_id, $reason) {
        $query = "INSERT INTO " . $this->request_table . "
                  (household_id, request_reason)
                  VALUES (?, ?)";
        
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([$household_id, $reason])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function addRequestItem($request_id, $relief_goods_id, $quantity) {
        $query = "INSERT INTO " . $this->items_table . "
                  (request_id, relief_goods_id, quantity_requested)
                  VALUES (?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$request_id, $relief_goods_id, $quantity]);
    }

    public function getAllRequests() {
        $query = "SELECT r.*, h.household_name, h.household_id FROM " . $this->request_table . " r
                  JOIN households h ON r.household_id = h.id
                  ORDER BY r.request_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequestsByStatus($status) {
        $query = "SELECT r.*, h.household_name, h.household_id FROM " . $this->request_table . " r
                  JOIN households h ON r.household_id = h.id
                  WHERE r.status = ?
                  ORDER BY r.request_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequestById($id) {
        $query = "SELECT r.*, h.household_name, h.household_id FROM " . $this->request_table . " r
                  JOIN households h ON r.household_id = h.id
                  WHERE r.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRequestItems($request_id) {
        $query = "SELECT ri.*, g.goods_name, c.category_name FROM " . $this->items_table . " ri
                  JOIN relief_goods g ON ri.relief_goods_id = g.id
                  JOIN relief_categories c ON g.category_id = c.id
                  WHERE ri.request_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$request_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRequestStatus($id, $status, $user_id, $notes = '') {
        $query = "UPDATE " . $this->request_table . "
                  SET status = ?, reviewed_by = ?, reviewed_at = NOW(), notes = ?
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $user_id, $notes, $id]);
    }

    public function getPendingCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->request_table . " WHERE status = 'Pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getHouseholdRequests($household_id) {
        $query = "SELECT * FROM " . $this->request_table . "
                  WHERE household_id = ?
                  ORDER BY request_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$household_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRequestsWithItems() {
        $query = "SELECT rr.id, rr.request_date, rr.status, rr.request_reason,
                         h.household_name, h.household_id, h.barangay, h.city,
                         rg.goods_name, rc.category_name, ri.quantity_requested
                  FROM relief_requests rr
                  JOIN households h ON rr.household_id = h.id
                  LEFT JOIN request_items ri ON ri.request_id = rr.id
                  LEFT JOIN relief_goods rg ON ri.relief_goods_id = rg.id
                  LEFT JOIN relief_categories rc ON rg.category_id = rc.id
                  ORDER BY rr.request_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

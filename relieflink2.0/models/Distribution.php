<?php
/**
 * Distribution Model
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/Database.php';

class Distribution {
    private $conn;
    private $batch_table = 'distribution_batches';
    private $distribution_table = 'distributions';
    private $details_table = 'distribution_details';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function createBatch($batch_code, $batch_date, $description, $user_id) {
        $query = "INSERT INTO " . $this->batch_table . "
                  (batch_code, batch_date, description, created_by)
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute([$batch_code, $batch_date, $description, $user_id])) {
            return false;
        }
        return $this->conn->lastInsertId();
    }

    public function markBatchInProgress($id) {
        $query = "UPDATE " . $this->batch_table . "
                  SET status = 'In Progress'
                  WHERE id = ? AND status = 'Pending'";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function completeBatch($id) {
        $query = "UPDATE " . $this->batch_table . "
                  SET status = 'Completed', completed_at = NOW()
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function cancelBatch($id) {
        $query = "UPDATE " . $this->batch_table . "
                  SET status = 'Cancelled'
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getBatchById($id) {
        $query = "SELECT b.*, u.first_name, u.last_name FROM " . $this->batch_table . " b
                  LEFT JOIN users u ON b.created_by = u.id
                  WHERE b.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllBatches() {
        $query = "SELECT b.*, u.first_name, u.last_name FROM " . $this->batch_table . " b
                  LEFT JOIN users u ON b.created_by = u.id
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createDistribution($batch_id, $household_id, $user_id, $notes = '') {
        $query = "INSERT INTO " . $this->distribution_table . "
                  (batch_id, household_id, distributed_by, notes)
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([$batch_id, $household_id, $user_id, $notes])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function addDistributionDetail($distribution_id, $relief_goods_id, $quantity) {
        $query = "INSERT INTO " . $this->details_table . "
                  (distribution_id, relief_goods_id, quantity_distributed)
                  VALUES (?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$distribution_id, $relief_goods_id, $quantity]);
    }

    public function getDistributionsByBatch($batch_id) {
        $query = "SELECT d.*, h.household_name, h.household_id, u.first_name, u.last_name
                  FROM " . $this->distribution_table . " d
                  JOIN households h ON d.household_id = h.id
                  LEFT JOIN users u ON d.distributed_by = u.id
                  WHERE d.batch_id = ?
                  ORDER BY d.distribution_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$batch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistributionDetails($distribution_id) {
        $query = "SELECT dd.*, g.goods_name, c.category_name
                  FROM " . $this->details_table . " dd
                  JOIN relief_goods g ON dd.relief_goods_id = g.id
                  JOIN relief_categories c ON g.category_id = c.id
                  WHERE dd.distribution_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$distribution_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalDistributed() {
        $query = "SELECT COUNT(*) as count FROM " . $this->distribution_table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getMonthlyDistributionCounts($months = 6) {
        $months = max(1, (int) $months);
        $startDate = (new DateTimeImmutable('first day of this month'))
            ->modify('-' . ($months - 1) . ' months')
            ->format('Y-m-d');
        $endDate = (new DateTimeImmutable('first day of next month'))->format('Y-m-d');

        $query = "SELECT DATE_FORMAT(distribution_date, '%Y-%m') AS month, COUNT(*) AS count
                  FROM " . $this->distribution_table . "
                  WHERE distribution_date >= ? AND distribution_date < ?
                  GROUP BY DATE_FORMAT(distribution_date, '%Y-%m')
                  ORDER BY month";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$startDate, $endDate]);

        $counts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $counts[$row['month']] = (int) $row['count'];
        }

        return $counts;
    }

    public function getDistributionHistory($household_id) {
        $query = "SELECT d.*, b.batch_code FROM " . $this->distribution_table . " d
                  JOIN distribution_batches b ON d.batch_id = b.id
                  WHERE d.household_id = ?
                  ORDER BY d.distribution_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$household_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasReceivedFromBatch($household_id, $batch_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->distribution_table . "
                  WHERE household_id = ? AND batch_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$household_id, $batch_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function getBatchDistributions($batch_id) {
        $query = "SELECT d.*, h.household_name, h.household_id, h.barangay, h.city,
                         u.first_name, u.last_name
                  FROM " . $this->distribution_table . " d
                  JOIN households h ON d.household_id = h.id
                  LEFT JOIN users u ON d.distributed_by = u.id
                  WHERE d.batch_id = ?
                  ORDER BY d.distribution_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$batch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistributionWithDetails($distribution_id) {
        $query = "SELECT d.*, h.household_name, h.household_id, h.barangay, h.city,
                         b.batch_code, b.batch_date,
                         u.first_name as staff_first, u.last_name as staff_last
                  FROM " . $this->distribution_table . " d
                  JOIN households h ON d.household_id = h.id
                  JOIN distribution_batches b ON d.batch_id = b.id
                  LEFT JOIN users u ON d.distributed_by = u.id
                  WHERE d.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$distribution_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAvailableGoodsForDistribution($limit = 20) {
        $query = "SELECT g.*, c.category_name                   FROM relief_goods g
                  JOIN relief_categories c ON g.category_id = c.id
                  WHERE g.quantity_in_stock > 0 AND g.is_active = 1
                  ORDER BY g.goods_name ASC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

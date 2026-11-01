<?php
/**
 * Household Model
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/Database.php';

class Household {
    private $conn;
    private $table = 'households';

    public $id;
    public $user_id;
    public $household_name;
    public $household_id;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAllHouseholds() {
        $query = "SELECT h.*, u.username FROM " . $this->table . " h 
                  LEFT JOIN users u ON h.user_id = u.id 
                  ORDER BY h.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHouseholdById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHouseholdByCode($household_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE household_id = ? OR qr_code = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$household_id, $household_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function householdIdExists($household_id) {
        $query = "SELECT 1 FROM " . $this->table . " WHERE household_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$household_id]);
        return (bool) $stmt->fetchColumn();
    }

    public function generateHouseholdId() {
        do {
            $household_id = 'QRN-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->householdIdExists($household_id));

        return $household_id;
    }

    public function getHouseholdByUserId($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createHousehold($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, household_name, household_id, street_address, barangay, city, province, postal_code, contact_number, total_members, monthly_income, qr_code)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['user_id'] ?? null,
            $data['household_name'],
            $data['household_id'],
            $data['street_address'],
            $data['barangay'],
            $data['city'],
            $data['province'],
            $data['postal_code'],
            $data['contact_number'],
            $data['total_members'],
            $data['monthly_income'],
            $data['qr_code'] ?? $data['household_id']
        ]);
    }

    public function updateHousehold($id, $data) {
        $query = "UPDATE " . $this->table . " SET 
                  household_name = ?, street_address = ?, barangay = ?, city = ?, province = ?, 
                  postal_code = ?, contact_number = ?, total_members = ?, monthly_income = ?, 
                  is_eligible = ?, eligibility_reason = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['household_name'],
            $data['street_address'],
            $data['barangay'],
            $data['city'],
            $data['province'],
            $data['postal_code'],
            $data['contact_number'],
            $data['total_members'],
            $data['monthly_income'],
            $data['is_eligible'],
            $data['eligibility_reason'],
            $id
        ]);
    }

    public function deleteHousehold($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getHouseholdCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getEligibleHouseholds() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE is_eligible = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>

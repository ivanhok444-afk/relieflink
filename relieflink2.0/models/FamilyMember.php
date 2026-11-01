<?php
/**
 * FamilyMember Model
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/Database.php';

class FamilyMember {
    private $conn;
    private $table = 'family_members';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getMembersByHousehold($household_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE household_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$household_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMemberById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createMember($data) {
        $query = "INSERT INTO " . $this->table . "
                  (household_id, first_name, middle_name, last_name, age, gender, relationship, date_of_birth, is_senior, is_pwd)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['household_id'],
            $data['first_name'],
            $data['middle_name'] ?? null,
            $data['last_name'],
            $data['age'] ?? null,
            $data['gender'] ?? null,
            $data['relationship'] ?? null,
            $data['date_of_birth'] ?? null,
            isset($data['is_senior']) ? 1 : 0,
            isset($data['is_pwd']) ? 1 : 0
        ]);
    }

    public function updateMember($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  first_name = ?, middle_name = ?, last_name = ?, age = ?, gender = ?,
                  relationship = ?, date_of_birth = ?, is_senior = ?, is_pwd = ?
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['first_name'],
            $data['middle_name'] ?? null,
            $data['last_name'],
            $data['age'] ?? null,
            $data['gender'] ?? null,
            $data['relationship'] ?? null,
            $data['date_of_birth'] ?? null,
            isset($data['is_senior']) ? 1 : 0,
            isset($data['is_pwd']) ? 1 : 0,
            $id
        ]);
    }

    public function deleteMember($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getMemberCount($household_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE household_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$household_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}

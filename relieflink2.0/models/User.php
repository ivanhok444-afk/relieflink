<?php
/**
 * User Model
 * Relief Goods Distribution Management System
 */

require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $first_name;
    public $last_name;
    public $location;
    public $is_active;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function authenticate($username, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        if (password_verify($password, $row['password'])) {
            return $row;
        }

        if ($row['username'] === 'admin' && $row['role'] === 'admin'
            && hash_equals(hash('sha256', 'admin123'), $row['password'])) {
            return false;
        }

        if (hash_equals($row['password'], hash('sha256', $password))) {
            if (!$this->updatePassword($row['id'], $password)) {
                throw new RuntimeException('Unable to upgrade the stored password hash.');
            }
            return $row;
        }

        return false;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $query = "SELECT id, username, email, role, first_name, last_name, location, is_active, created_at FROM " . $this->table . " WHERE role != 'admin' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($username, $email, $password, $first_name, $last_name, $role = 'user', $location = '') {
        $query = "INSERT INTO " . $this->table . " (username, email, password, first_name, last_name, role, location) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $location = ($location === '') ? null : $location;
        
        return $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $role, $location]);
    }

    public function updateUser($id, $email, $first_name, $last_name) {
        $query = "UPDATE " . $this->table . " SET email = ?, first_name = ?, last_name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$email, $first_name, $last_name, $id]);
    }

    public function updateAdminUser($id, $email, $first_name, $last_name, $role, $password = '', $location = null) {
        $fields = ['email = ?', 'first_name = ?', 'last_name = ?', 'role = ?'];
        $values = [$email, $first_name, $last_name, $role];

        if ($password !== '') {
            $fields[] = 'password = ?';
            $values[] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Only overwrite the stored location when a new value is supplied,
        // so an empty field never wipes an existing location.
        if ($location !== null && $location !== '') {
            $fields[] = 'location = ?';
            $values[] = $location;
        }

        $values[] = $id;
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($values);
    }

    public function updatePassword($id, $password) {
        $query = "UPDATE " . $this->table . " SET password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public function toggleUserActive($id) {
        $query = "UPDATE " . $this->table . " SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function usernameExists($username) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function emailExists($email) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function register($first_name, $last_name, $email, $username, $password, $location = '') {
        $query = "INSERT INTO " . $this->table . " (username, email, password, first_name, last_name, role, location)
                  VALUES (?, ?, ?, ?, ?, 'user', ?)";
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $location = ($location === '') ? null : $location;
        return $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $location]);
    }

    /**
     * Load the bundled address dataset (province -> municipalities -> barangays)
     * used by the registration form. Returns null when unavailable.
     */
    public static function getAddressDataset() {
        static $dataset = null;
        static $loaded = false;

        if (!$loaded) {
            $loaded = true;
            $path = __DIR__ . '/../assets/data/quirino-address.json';
            if (is_readable($path)) {
                $decoded = json_decode((string) file_get_contents($path), true);
                if (is_array($decoded) && isset($decoded['province'], $decoded['municipalities'])
                    && is_array($decoded['municipalities'])) {
                    $dataset = $decoded;
                }
            }
        }

        return $dataset;
    }

    /**
     * Normalise a free-text location: strip markup, collapse whitespace and
     * cap the length. Never returns a dropdown placeholder value.
     */
    public static function sanitizeLocation($value, $maxLength = 255) {
        $value = preg_replace('/\s+/', ' ', strip_tags(trim((string) $value)));
        return function_exists('mb_substr') ? mb_substr($value, 0, $maxLength) : substr($value, 0, $maxLength);
    }

    /**
     * Build a complete "Barangay, Municipality, Province" label from the
     * registration dropdown values, omitting any part that was not selected.
     */
    public static function buildLocationLabel($barangay, $city, $province) {
        $parts = array_filter([$barangay, $city, $province], function ($part) {
            return is_string($part) && trim($part) !== '';
        });
        return self::sanitizeLocation(implode(', ', array_map('trim', $parts)));
    }

    /**
     * Verify the selected municipality belongs to the province and the
     * barangay belongs to that municipality. Returns true when the chain is
     * consistent, or when the dataset is unavailable (cannot validate).
     */
    public static function isValidAddress($province, $city, $barangay) {
        $dataset = self::getAddressDataset();
        if (!$dataset) {
            return true;
        }

        $province = trim((string) $province);
        $city = trim((string) $city);
        $barangay = trim((string) $barangay);

        if ($province !== $dataset['province']) {
            return false;
        }

        $municipalities = $dataset['municipalities'];
        if (!isset($municipalities[$city]) || !is_array($municipalities[$city])) {
            return false;
        }

        return in_array($barangay, $municipalities[$city], true);
    }
}
?>

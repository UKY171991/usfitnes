<?php
/**
 * User Model Class
 * Handles user-related database operations
 */

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, b.branch_name 
                FROM users u 
                LEFT JOIN branches b ON u.branch_id = b.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get user by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get user by email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create new user
     */
    public function create($userData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, username, password, phone, role, branch_id, 
                                 date_of_birth, gender, address, city, state, pincode, 
                                 emergency_contact, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            
            $result = $stmt->execute([
                $userData['name'],
                $userData['email'],
                $userData['username'] ?? $userData['email'],
                $userData['password'],
                $userData['phone'],
                $userData['role'] ?? ROLE_PATIENT,
                $userData['branch_id'] ?? null,
                $userData['date_of_birth'] ?? null,
                $userData['gender'] ?? null,
                $userData['address'] ?? null,
                $userData['city'] ?? null,
                $userData['state'] ?? null,
                $userData['pincode'] ?? null,
                $userData['emergency_contact'] ?? null
            ]);

            if ($result) {
                $userId = $this->db->lastInsertId();
                Logger::activity($userId, 'User Created', $userData);
                return $userId;
            }
            return false;
        } catch (Exception $e) {
            Logger::error("Failed to create user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user
     */
    public function update($id, $userData) {
        try {
            $setParts = [];
            $params = [];

            foreach ($userData as $field => $value) {
                if ($field !== 'id') {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                }
            }

            $params[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                Logger::activity($id, 'User Updated', $userData);
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to update user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET status = 0, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                Logger::activity($id, 'User Deleted');
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to delete user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get users with pagination and filters
     */
    public function getUsers($filters = [], $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $where = ['u.status = 1'];
            $params = [];

            // Add filters
            if (!empty($filters['role'])) {
                $where[] = 'u.role = ?';
                $params[] = $filters['role'];
            }

            if (!empty($filters['branch_id'])) {
                $where[] = 'u.branch_id = ?';
                $params[] = $filters['branch_id'];
            }

            if (!empty($filters['search'])) {
                $where[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            $whereClause = implode(' AND ', $where);

            // Get total count
            $countSql = "SELECT COUNT(*) FROM users u WHERE $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Get users
            $sql = "
                SELECT u.*, b.branch_name 
                FROM users u 
                LEFT JOIN branches b ON u.branch_id = b.id 
                WHERE $whereClause 
                ORDER BY u.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll();

            return [
                'users' => $users,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (Exception $e) {
            Logger::error("Failed to get users: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get patients only
     */
    public function getPatients($branchId = null, $page = 1, $limit = 10) {
        $filters = ['role' => ROLE_PATIENT];
        if ($branchId) {
            $filters['branch_id'] = $branchId;
        }
        return $this->getUsers($filters, $page, $limit);
    }

    /**
     * Verify email
     */
    public function verifyEmail($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                Logger::activity($userId, 'Email Verified');
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to verify email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify phone
     */
    public function verifyPhone($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET phone_verified = 1 WHERE id = ?");
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                Logger::activity($userId, 'Phone Verified');
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to verify phone: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change password
     */
    public function changePassword($userId, $newPassword) {
        try {
            $hashedPassword = Security::hashPassword($newPassword);
            $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$hashedPassword, $userId]);
            
            if ($result) {
                Logger::activity($userId, 'Password Changed');
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to change password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user statistics
     */
    public function getStatistics($branchId = null) {
        try {
            $where = $branchId ? "WHERE branch_id = $branchId" : "";
            
            $stmt = $this->db->query("
                SELECT 
                    role,
                    COUNT(*) as count,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_count
                FROM users 
                $where
                GROUP BY role
            ");
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get user statistics: " . $e->getMessage());
            return false;
        }
    }
}
?>

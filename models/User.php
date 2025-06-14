<?php
require_once 'database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Get all users
    public function getAll() {
        $this->db->query('SELECT id, username, full_name, role, status, last_login, created_at FROM users ORDER BY full_name ASC');
        return $this->db->resultset();
    }

    // Get user by ID
    public function getById($id) {
        $this->db->query('SELECT id, username, full_name, role, status, last_login, created_at FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get user by username
    public function getByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    // Create new user
    public function create($data) {
        $this->db->query('INSERT INTO users (username, password, full_name, role, status) VALUES (:username, :password, :full_name, :role, :status)');
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':status', $data['status'] ?? 'active');
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Update user
    public function update($id, $data) {
        $query = 'UPDATE users SET username = :username, full_name = :full_name, role = :role, status = :status';
        
        if (!empty($data['password'])) {
            $query .= ', password = :password';
        }
        
        $query .= ' WHERE id = :id';
        
        $this->db->query($query);
        $this->db->bind(':id', $id);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':status', $data['status']);
        
        if (!empty($data['password'])) {
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        }
        
        return $this->db->execute();
    }

    // Delete user
    public function delete($id) {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Check if username exists
    public function usernameExists($username, $exclude_id = null) {
        if ($exclude_id) {
            $this->db->query('SELECT id FROM users WHERE username = :username AND id != :exclude_id');
            $this->db->bind(':exclude_id', $exclude_id);
        } else {
            $this->db->query('SELECT id FROM users WHERE username = :username');
        }
        $this->db->bind(':username', $username);
        return $this->db->single() ? true : false;
    }

    // Change password
    public function changePassword($id, $new_password) {
        $this->db->query('UPDATE users SET password = :password WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':password', password_hash($new_password, PASSWORD_DEFAULT));
        return $this->db->execute();
    }

    // Update last login
    public function updateLastLogin($id) {
        $this->db->query('UPDATE users SET last_login = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>


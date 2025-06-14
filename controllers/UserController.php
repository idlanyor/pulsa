<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/User.php';

class UserController {
    private $user;
    private $auth;

    public function __construct() {
        $this->user = new User();
        $this->auth = new Auth();
    }

    // Get all users (admin only)
    public function index() {
        if (!$this->auth->isAdmin()) {
            return ['error' => 'Access denied'];
        }
        
        return $this->user->getAll();
    }

    // Get user by ID
    public function show($id) {
        // Users can only view their own profile, admins can view any
        if (!$this->auth->isAdmin() && $_SESSION['user_id'] != $id) {
            return ['error' => 'Access denied'];
        }
        
        return $this->user->getById($id);
    }

    // Create new user (admin only)
    public function create($data) {
        if (!$this->auth->isAdmin()) {
            return ['error' => 'Access denied'];
        }

        // Validate required fields
        $required = ['username', 'password', 'full_name', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['error' => "Field $field is required"];
            }
        }

        // Validate username
        if (strlen($data['username']) < 3) {
            return ['error' => 'Username must be at least 3 characters'];
        }

        if ($this->user->usernameExists($data['username'])) {
            return ['error' => 'Username already exists'];
        }

        // Validate password
        if (strlen($data['password']) < 6) {
            return ['error' => 'Password must be at least 6 characters'];
        }

        // Validate role
        if (!in_array($data['role'], ['admin', 'operator'])) {
            return ['error' => 'Invalid role'];
        }

        $user_id = $this->user->create($data);
        if ($user_id) {
            return ['success' => true, 'user_id' => $user_id];
        }
        
        return ['error' => 'Failed to create user'];
    }

    // Update user
    public function update($id, $data) {
        // Users can only update their own profile, admins can update any
        if (!$this->auth->isAdmin() && $_SESSION['user_id'] != $id) {
            return ['error' => 'Access denied'];
        }

        // Check if user exists
        $existing = $this->user->getById($id);
        if (!$existing) {
            return ['error' => 'User not found'];
        }

        // Non-admin users cannot change their role
        if (!$this->auth->isAdmin() && isset($data['role'])) {
            unset($data['role']);
        }

        // Validate username if provided
        if (!empty($data['username'])) {
            if (strlen($data['username']) < 3) {
                return ['error' => 'Username must be at least 3 characters'];
            }

            if ($this->user->usernameExists($data['username'], $id)) {
                return ['error' => 'Username already exists'];
            }
        }

        // Validate password if provided
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return ['error' => 'Password must be at least 6 characters'];
            }
        }

        // Validate role if provided
        if (isset($data['role']) && !in_array($data['role'], ['admin', 'operator'])) {
            return ['error' => 'Invalid role'];
        }

        if ($this->user->update($id, $data)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to update user'];
    }

    // Delete user (admin only)
    public function delete($id) {
        if (!$this->auth->isAdmin()) {
            return ['error' => 'Access denied'];
        }

        // Cannot delete own account
        if ($_SESSION['user_id'] == $id) {
            return ['error' => 'Cannot delete your own account'];
        }

        // Check if user exists
        $existing = $this->user->getById($id);
        if (!$existing) {
            return ['error' => 'User not found'];
        }

        if ($this->user->delete($id)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to delete user'];
    }

    // Change password
    public function changePassword($id, $current_password, $new_password) {
        // Users can only change their own password, admins can change any
        if (!$this->auth->isAdmin() && $_SESSION['user_id'] != $id) {
            return ['error' => 'Access denied'];
        }

        // Get user data
        $user = $this->user->getByUsername($_SESSION['username']);
        if (!$user) {
            return ['error' => 'User not found'];
        }

        // Verify current password (not required for admin changing other user's password)
        if (!$this->auth->isAdmin() || $_SESSION['user_id'] == $id) {
            if (!password_verify($current_password, $user['password'])) {
                return ['error' => 'Current password is incorrect'];
            }
        }

        // Validate new password
        if (strlen($new_password) < 6) {
            return ['error' => 'New password must be at least 6 characters'];
        }

        if ($this->user->changePassword($id, $new_password)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to change password'];
    }

    // Get current user profile
    public function getProfile() {
        return $this->auth->getCurrentUser();
    }
}
?>


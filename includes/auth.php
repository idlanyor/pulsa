<?php
class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Login user
    public function login($username, $password) {
        $this->db->query("SELECT * FROM users WHERE username = :username AND status = \"active\"");
        $this->db->bind(":username", $username);
        
        $user = $this->db->single();
        
        if ($user && password_verify($password, $user["password"])) {
            // Update last login
            $this->db->query("UPDATE users SET last_login = NOW() WHERE id = :id");
            $this->db->bind(":id", $user["id"]);
            $this->db->execute();
            
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Set session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["login_time"] = time();
            
            return true;
        }
        
        return false;
    }

    // Logout user
    public function logout() {
        session_unset();
        session_destroy();
        // Redirect to login page after logout
        header("Location: index.php");
        exit();
    }

    // Check if user is logged in
    public function isLoggedIn() {
        if (isset($_SESSION["user_id"]) && isset($_SESSION["login_time"])) {
            // Check session timeout
            if (time() - $_SESSION["login_time"] > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
            // Update login time to extend session
            $_SESSION["login_time"] = time();
            return true;
        }
        return false;
    }

    // Get current user data
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                "id" => $_SESSION["user_id"],
                "username" => $_SESSION["username"],
                "full_name" => $_SESSION["full_name"],
                "role" => $_SESSION["role"]
            ];
        }
        return null;
    }

    // Check if user has admin role
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION["role"] === "admin";
    }

    // Require login
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: index.php");
            exit();
        }
    }

    // Require admin
    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>


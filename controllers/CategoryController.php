<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'models/Category.php';

class CategoryController {
    private $categoryModel;
    private $auth;
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->categoryModel = new Category($this->db);
        $this->auth = new Auth();
    }

    public function index() {
        if (!$this->auth->isLoggedIn()) {
            return ['error' => 'Unauthorized'];
        }
        return ['success' => true, 'data' => $this->categoryModel->getAll()];
    }

    public function show($id) {
        if (!$this->auth->isLoggedIn()) {
            return ['error' => 'Unauthorized'];
        }
        $category = $this->categoryModel->getById($id);
        if ($category) {
            return ['success' => true, 'data' => $category];
        } else {
            return ['error' => 'Category not found'];
        }
    }

    public function create($data) {
        if (!$this->auth->isAdmin()) {
            return ['error' => 'Access denied'];
        }
        if (empty($data['name'])) {
            return ['error' => 'Category name is required'];
        }
        if ($this->categoryModel->create($data['name'])) {
            return ['success' => true, 'message' => 'Category created successfully'];
        } else {
            return ['error' => 'Failed to create category'];
        }
    }

    public function update($id, $data) {
        if (!$this->auth->isAdmin()) {
            return ['error' => 'Access denied'];
        }
        if (empty($data['name'])) {
            return ['error' => 'Category name is required'];
        }
        if ($this->categoryModel->update($id, $data['name'])) {
            return ['success' => true, 'message' => 'Category updated successfully'];
        } else {
            return ['error' => 'Failed to update category'];
        }
    }

    public function delete($id) {
        if (!$this->auth->isAdmin()) {
            return ['error' => 'Access denied'];
        }
        if ($this->categoryModel->delete($id)) {
            return ['success' => true, 'message' => 'Category deleted successfully'];
        } else {
            return ['error' => 'Failed to delete category'];
        }
    }
}
?>


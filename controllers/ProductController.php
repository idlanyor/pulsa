<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/Product.php';
require_once 'models/Provider.php';
require_once 'models/Category.php';
require_once 'models/Stock.php';
require_once 'models/Transaction.php';

class ProductController {
    private $product;
    private $provider;
    private $category;
    private $stock;
    private $auth;

    public function __construct() {
        $this->product = new Product();
        $this->provider = new Provider();
        $this->category = new Category();
        $this->stock = new Stock();
        $this->auth = new Auth();
    }

    // Get all products
    public function index($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $products = $this->product->getAll($limit, $offset);
        $total = $this->product->getTotalCount();
        $pagination = Helper::paginate($total, $limit, $page);
        
        return [
            'products' => $products,
            'pagination' => $pagination
        ];
    }

    // Get product by ID
    public function show($id) {
        return $this->product->getById($id);
    }

    // Create new product
    public function create($data) {
        // Validate required fields
        $required = ['provider_id', 'category_id', 'name', 'quota', 'buy_price', 'sell_price'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['error' => "Field $field is required"];
            }
        }

        // Generate product code if not provided
        if (empty($data['code'])) {
            $provider = $this->provider->getById($data['provider_id']);
            $category = $this->category->getById($data['category_id']);
            $data['code'] = Helper::generateProductCode($provider['code'], $category['name'], $data['quota']);
        }

        // Check if code already exists
        if ($this->product->codeExists($data['code'])) {
            return ['error' => 'Product code already exists'];
        }

        // Validate prices
        if ($data['buy_price'] <= 0 || $data['sell_price'] <= 0) {
            return ['error' => 'Prices must be greater than 0'];
        }

        if ($data['sell_price'] <= $data['buy_price']) {
            return ['error' => 'Sell price must be greater than buy price'];
        }

        $product_id = $this->product->create($data);
        if ($product_id) {
            return ['success' => true, 'product_id' => $product_id];
        }
        
        return ['error' => 'Failed to create product'];
    }

    // Update product
    public function update($id, $data) {
        // Check if product exists
        $existing = $this->product->getById($id);
        if (!$existing) {
            return ['error' => 'Product not found'];
        }

        // Check if code already exists (excluding current product)
        if (!empty($data['code']) && $this->product->codeExists($data['code'], $id)) {
            return ['error' => 'Product code already exists'];
        }

        // Validate prices
        if (isset($data['buy_price']) && $data['buy_price'] <= 0) {
            return ['error' => 'Buy price must be greater than 0'];
        }

        if (isset($data['sell_price']) && $data['sell_price'] <= 0) {
            return ['error' => 'Sell price must be greater than 0'];
        }

        if (isset($data['buy_price']) && isset($data['sell_price']) && $data['sell_price'] <= $data['buy_price']) {
            return ['error' => 'Sell price must be greater than buy price'];
        }

        if ($this->product->update($id, $data)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to update product'];
    }

    // Delete product
    public function delete($id) {
        // Check if product exists
        $existing = $this->product->getById($id);
        if (!$existing) {
            return ['error' => 'Product not found'];
        }

        if ($this->product->delete($id)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to delete product'];
    }

    // Search products
    public function search($keyword, $provider_id = null, $category_id = null) {
        return $this->product->search($keyword, $provider_id, $category_id);
    }

    // Get products by provider
    public function getByProvider($provider_id) {
        return $this->product->getByProvider($provider_id);
    }

    // Get products by category
    public function getByCategory($category_id) {
        return $this->product->getByCategory($category_id);
    }
}
?>


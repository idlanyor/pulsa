<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';

// Set JSON response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
$db = new Database();
// Initialize auth
$auth = new Auth();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api.php', '', $path);
$path_parts = explode('/', trim($path, '/'));

// Get request body for POST/PUT requests
$input = json_decode(file_get_contents('php://input'), true);

// Response function
function sendResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit();
}

// Error response function
function sendError($message, $status_code = 400) {
    sendResponse(['error' => $message], $status_code);
}

// Success response function
function sendSuccess($data = [], $message = 'Success') {
    sendResponse(['success' => true, 'message' => $message, 'data' => $data]);
}

// Authentication endpoints (no auth required)
if ($path_parts[0] === 'auth') {
    switch ($path_parts[1] ?? '') {
        case 'login':
            if ($method !== 'POST') {
                sendError('Method not allowed', 405);
            }
            
            if (empty($input['username']) || empty($input['password'])) {
                sendError('Username and password are required');
            }
            
            if ($auth->login($input['username'], $input['password'])) {
                sendSuccess($auth->getCurrentUser(), 'Login successful');
            } else {
                sendError('Invalid credentials', 401);
            }
            break;
            
        case 'logout':
            if ($method !== 'POST') {
                sendError('Method not allowed', 405);
            }
            
            $auth->logout();
            sendSuccess([], 'Logout successful');
            break;
            
        case 'profile':
            if ($method !== 'GET') {
                sendError('Method not allowed', 405);
            }
            
            if (!$auth->isLoggedIn()) {
                sendError('Not authenticated', 401);
            }
            
            sendSuccess($auth->getCurrentUser());
            break;
            
        default:
            sendError('Endpoint not found', 404);
    }
}

// Check authentication for all other endpoints
if (!$auth->isLoggedIn()) {
    sendError('Authentication required', 401);
}

// Load controllers
require_once 'controllers/ProductController.php';
require_once 'controllers/StockController.php';
require_once 'controllers/TransactionController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/SettingController.php';
require_once 'controllers/CategoryController.php';
require_once 'controllers/ProviderController.php';

// Initialize controllers
$productController = new ProductController();
$stockController = new StockController();
$transactionController = new TransactionController();
$userController = new UserController();
$settingController = new SettingController();
$categoryController = new CategoryController();
$providerController = new ProviderController();

// Route handling
switch ($path_parts[0] ?? '') {
    case 'products':
        switch ($method) {
            case 'GET':
                if (isset($path_parts[1])) {
                    // Get specific product
                    $product = $productController->show($path_parts[1]);
                    if ($product) {
                        sendSuccess($product);
                    } else {
                        sendError('Product not found', 404);
                    }
                } else {
                    // Get all products with pagination
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 20;
                    $result = $productController->index($page, $limit);
                    sendSuccess($result);
                }
                break;
                
            case 'POST':
                $result = $productController->create($input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Product created successfully');
                }
                break;
                
            case 'PUT':
                if (!isset($path_parts[1])) {
                    sendError('Product ID is required');
                }
                
                $result = $productController->update($path_parts[1], $input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Product updated successfully');
                }
                break;
                
            case 'DELETE':
                if (!isset($path_parts[1])) {
                    sendError('Product ID is required');
                }
                
                $result = $productController->delete($path_parts[1]);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Product deleted successfully');
                }
                break;
                
            default:
                sendError('Method not allowed', 405);
        }
        break;
        
    case 'stock':
        switch ($path_parts[1] ?? '') {
            case 'summary':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                sendSuccess($stockController->getSummary());
                break;
                
            case 'low':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                sendSuccess($stockController->getLowStock());
                break;
                
            case 'statistics':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                sendSuccess($stockController->getStatistics());
                break;
                
            case 'in':
                if ($method !== 'POST') {
                    sendError('Method not allowed', 405);
                }
                
                $result = $stockController->stockIn($input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Stock added successfully');
                }
                break;
                
            case 'out':
                if ($method !== 'POST') {
                    sendError('Method not allowed', 405);
                }
                
                $result = $stockController->stockOut($input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Sale processed successfully');
                }
                break;
                
            case 'adjust':
                if ($method !== 'POST') {
                    sendError('Method not allowed', 405);
                }
                
                if (empty($input['product_id']) || !isset($input['quantity'])) {
                    sendError('Product ID and quantity are required');
                }
                
                $result = $stockController->adjustStock(
                    $input['product_id'], 
                    $input['quantity'], 
                    $input['notes'] ?? ''
                );
                
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Stock adjusted successfully');
                }
                break;
                
            case 'settings':
                if ($method !== 'PUT') {
                    sendError('Method not allowed', 405);
                }
                
                if (empty($input['product_id']) || !isset($input['min_stock']) || !isset($input['max_stock'])) {
                    sendError('Product ID, min_stock, and max_stock are required');
                }
                
                $result = $stockController->updateSettings(
                    $input['product_id'], 
                    $input['min_stock'], 
                    $input['max_stock']
                );
                
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Stock settings updated successfully');
                }
                break;
                
            case 'movements':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                
                if (empty($path_parts[2])) {
                    sendError('Product ID is required');
                }
                
                $limit = $_GET['limit'] ?? 50;
                $movements = $stockController->getMovements($path_parts[2], $limit);
                sendSuccess($movements);
                break;
                
            default:
                sendError('Endpoint not found', 404);
        }
        break;
        
    case 'transactions':
        switch ($path_parts[1] ?? '') {
            case 'in':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 20;
                $filters = [
                    'product_id' => $_GET['product_id'] ?? null,
                    'date_from' => $_GET['date_from'] ?? null,
                    'date_to' => $_GET['date_to'] ?? null
                ];
                
                $transactions = $transactionController->getStockIn($page, $limit, $filters);
                sendSuccess($transactions);
                break;
                
            case 'out':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 20;
                $filters = [
                    'product_id' => $_GET['product_id'] ?? null,
                    'date_from' => $_GET['date_from'] ?? null,
                    'date_to' => $_GET['date_to'] ?? null
                ];
                
                $transactions = $transactionController->getStockOut($page, $limit, $filters);
                sendSuccess($transactions);
                break;
                
            case 'sales':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                
                $date_from = $_GET['date_from'] ?? null;
                $date_to = $_GET['date_to'] ?? null;
                
                $sales = $transactionController->getDailySales($date_from, $date_to);
                sendSuccess($sales);
                break;
                
            case 'statistics':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                
                $period = $_GET['period'] ?? 'today';
                $stats = $transactionController->getSalesStatistics($period);
                sendSuccess($stats);
                break;
                
            case 'report':
                if ($method !== 'GET') {
                    sendError('Method not allowed', 405);
                }
                
                $date_from = $_GET['date_from'] ?? date('Y-m-01');
                $date_to = $_GET['date_to'] ?? date('Y-m-d');
                
                $report = $transactionController->generateSalesReport($date_from, $date_to);
                sendSuccess($report);
                break;
                
            default:
                sendError('Endpoint not found', 404);
        }
        break;
        
    case 'users':
        switch ($method) {
            case 'GET':
                if (isset($path_parts[1])) {
                    // Get specific user
                    $user = $userController->show($path_parts[1]);
                    if (isset($user['error'])) {
                        sendError($user['error'], 403);
                    } elseif ($user) {
                        sendSuccess($user);
                    } else {
                        sendError('User not found', 404);
                    }
                } else {
                    // Get all users
                    $users = $userController->index();
                    if (isset($users['error'])) {
                        sendError($users['error'], 403);
                    } else {
                        sendSuccess($users);
                    }
                }
                break;
                
            case 'POST':
                $result = $userController->create($input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'User created successfully');
                }
                break;
                
            case 'PUT':
                if (!isset($path_parts[1])) {
                    sendError('User ID is required');
                }
                
                $result = $userController->update($path_parts[1], $input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'User updated successfully');
                }
                break;
                
            case 'DELETE':
                if (!isset($path_parts[1])) {
                    sendError('User ID is required');
                }
                
                $result = $userController->delete($path_parts[1]);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'User deleted successfully');
                }
                break;
                
            default:
                sendError('Method not allowed', 405);
        }
        break;

    case 'categories':
        switch ($method) {
            case 'GET':
                if (isset($path_parts[1])) {
                    $category = $categoryController->show($path_parts[1]);
                    if ($category) {
                        sendSuccess($category);
                    } else {
                        sendError('Category not found', 404);
                    }
                } else {
                    sendSuccess($categoryController->index());
                }
                break;
            case 'POST':
                $result = $categoryController->create($input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Category created successfully');
                }
                break;
            case 'PUT':
                if (!isset($path_parts[1])) {
                    sendError('Category ID is required');
                }
                $result = $categoryController->update($path_parts[1], $input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Category updated successfully');
                }
                break;
            case 'DELETE':
                if (!isset($path_parts[1])) {
                    sendError('Category ID is required');
                }
                $result = $categoryController->delete($path_parts[1]);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Category deleted successfully');
                }
                break;
            default:
                sendError('Method not allowed', 405);
        }
        break;

    case 'providers':
        switch ($method) {
            case 'GET':
                if (isset($path_parts[1])) {
                    $provider = $providerController->show($path_parts[1]);
                    if ($provider) {
                        sendSuccess($provider);
                    } else {
                        sendError('Provider not found', 404);
                    }
                } else {
                    sendSuccess($providerController->index());
                }
                break;
            case 'POST':
                $result = $providerController->create($input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Provider created successfully');
                }
                break;
            case 'PUT':
                if (!isset($path_parts[1])) {
                    sendError('Provider ID is required');
                }
                $result = $providerController->update($path_parts[1], $input);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Provider updated successfully');
                }
                break;
            case 'DELETE':
                if (!isset($path_parts[1])) {
                    sendError('Provider ID is required');
                }
                $result = $providerController->delete($path_parts[1]);
                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, 'Provider deleted successfully');
                }
                break;
            default:
                sendError('Method not allowed', 405);
        }
        break;

    case 'settings':
        switch ($method) {
            case 'GET':
                sendSuccess($settingController->getSettings());
                break;
            case 'PUT':
                if (isset($path_parts[1]) && $path_parts[1] === 'general') {
                    $result = $settingController->updateGeneralSettings($input);
                } elseif (isset($path_parts[1]) && $path_parts[1] === 'database') {
                    $result = $settingController->updateDatabaseSettings($input);
                } else {
                    sendError('Invalid settings endpoint', 400);
                }

                if (isset($result['error'])) {
                    sendError($result['error']);
                } else {
                    sendSuccess($result, $result['message']);
                }
                break;
            default:
                sendError('Method not allowed', 405);
        }
        break;
        
    default:
        sendError('Endpoint not found', 404);
}
?>


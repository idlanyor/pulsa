<?php
class Helper {
    
    // Format currency
    public static function formatCurrency($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    // Format date
    public static function formatDate($date, $format = 'd/m/Y H:i') {
        return date($format, strtotime($date));
    }

    // Generate random string
    public static function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    // Sanitize input
    public static function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }

    // Validate email
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Validate phone
    public static function validatePhone($phone) {
        return preg_match('/^[0-9+\-\s()]+$/', $phone);
    }

    // Get stock status
    public static function getStockStatus($quantity, $min_stock, $max_stock) {
        if ($quantity <= $min_stock) {
            return ['status' => 'Low Stock', 'class' => 'danger'];
        } elseif ($quantity >= $max_stock) {
            return ['status' => 'Overstock', 'class' => 'warning'];
        } else {
            return ['status' => 'Normal', 'class' => 'success'];
        }
    }

    // Calculate profit percentage
    public static function calculateProfitPercentage($buy_price, $sell_price) {
        if ($buy_price > 0) {
            return round((($sell_price - $buy_price) / $buy_price) * 100, 2);
        }
        return 0;
    }

    // Generate product code
    public static function generateProductCode($provider_code, $category_name, $quota) {
        $category_code = strtoupper(substr($category_name, 0, 3));
        $quota_code = preg_replace('/[^A-Z0-9]/', '', strtoupper($quota));
        return $provider_code . '-' . $category_code . '-' . $quota_code;
    }

    // Flash message
    public static function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public static function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    // Pagination
    public static function paginate($total_records, $records_per_page, $current_page) {
        $total_pages = ceil($total_records / $records_per_page);
        $offset = ($current_page - 1) * $records_per_page;
        
        return [
            'total_pages' => $total_pages,
            'current_page' => $current_page,
            'offset' => $offset,
            'limit' => $records_per_page,
            'has_prev' => $current_page > 1,
            'has_next' => $current_page < $total_pages,
            'prev_page' => $current_page - 1,
            'next_page' => $current_page + 1
        ];
    }
}
?>


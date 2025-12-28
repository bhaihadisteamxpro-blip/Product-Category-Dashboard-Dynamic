<?php
// api/get_products_front.php
session_start();
require_once '../database/db.php';
header('Content-Type: application/json');

// LOGIC: Allow Guests but hide prices
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

try {
    $stmt = $pdo->query("SELECT p.id, p.product_name, p.price, p.image, c.category_name, p.product_description 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         WHERE p.status = 'active'
                         ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter sensitive data
    foreach ($products as &$product) {
        if (!$is_logged_in) {
            unset($product['price']);
        }
    }
    
    echo json_encode(['status' => 'success', 'products' => $products, 'guest_mode' => !$is_logged_in]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

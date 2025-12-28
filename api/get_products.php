<?php
// api/get_products.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

try {
    $stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.product_name");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $products]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

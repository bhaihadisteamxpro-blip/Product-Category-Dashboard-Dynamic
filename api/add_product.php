<?php
// api/add_product.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $sku = trim($_POST['sku'] ?? '');
    $price = $_POST['price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    $unit = $_POST['unit'] ?? 'pcs';
    $min_stock = $_POST['min_stock'] ?? 10;
    $status = $_POST['status'] ?? 'active';

    if (empty($product_name) || empty($sku)) {
        echo json_encode(['status' => 'error', 'message' => 'Name and SKU required']);
        exit();
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO products (product_name, category_id, sku, price, quantity, unit, min_stock, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$product_name, $category_id, $sku, $price, $quantity, $unit, $min_stock, $status, $_SESSION['user_id']])) {
            echo json_encode(['status' => 'success', 'message' => 'Product added']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

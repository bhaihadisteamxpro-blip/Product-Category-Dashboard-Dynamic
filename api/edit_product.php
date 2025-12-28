<?php
// api/edit_product.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $product_name = trim($_POST['product_name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'pcs');
    $min_stock = intval($_POST['min_stock'] ?? 5);
    $description = trim($_POST['product_description'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE products SET product_name = ?, sku = ?, category_id = ?, price = ?, quantity = ?, unit = ?, min_stock = ?, product_description = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$product_name, $sku, $category_id, $price, $quantity, $unit, $min_stock, $description, $id])) {
            echo json_encode(['status' => 'success', 'message' => 'Product updated']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

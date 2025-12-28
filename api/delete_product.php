<?php
// api/delete_product.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$id])) {
            $pdo->prepare("DELETE FROM admin_products WHERE product_id = ?")->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Product deleted']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

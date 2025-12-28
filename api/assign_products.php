<?php
// api/assign_products.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = intval($_POST['admin_id'] ?? 0);
    $product_ids = $_POST['product_ids'] ?? [];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM admin_products WHERE admin_id = ?")->execute([$admin_id]);
        if (!empty($product_ids)) {
            $stmt = $pdo->prepare("INSERT INTO admin_products (admin_id, product_id) VALUES (?, ?)");
            foreach ($product_ids as $prod_id) {
                $stmt->execute([$admin_id, intval($prod_id)]);
            }
        }
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Products assigned']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

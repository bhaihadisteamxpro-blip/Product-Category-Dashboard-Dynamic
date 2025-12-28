<?php
// api/bulk_assign_by_category.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = intval($_POST['admin_id'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    if ($admin_id <= 0 || $category_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit();
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT id FROM products WHERE category_id = ? AND status = 'active'");
        $stmt->execute([$category_id]);
        $products = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($products)) {
             echo json_encode(['status' => 'error', 'message' => 'No active products found']);
             exit();
        }
        $pdo->prepare("DELETE FROM admin_products WHERE admin_id = ?")->execute([$admin_id]);
        $stmt = $pdo->prepare("INSERT INTO admin_products (admin_id, product_id) VALUES (?, ?)");
        foreach ($products as $prod_id) {
            $stmt->execute([$admin_id, $prod_id]);
        }
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => count($products) . ' products assigned']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

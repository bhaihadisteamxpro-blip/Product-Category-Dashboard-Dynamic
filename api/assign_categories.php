<?php
// api/assign_categories.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = intval($_POST['admin_id'] ?? 0);
    $category_ids = $_POST['category_ids'] ?? [];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM admin_categories WHERE admin_id = ?")->execute([$admin_id]);
        if (!empty($category_ids)) {
            $stmt = $pdo->prepare("INSERT INTO admin_categories (admin_id, category_id) VALUES (?, ?)");
            foreach ($category_ids as $cat_id) {
                $stmt->execute([$admin_id, intval($cat_id)]);
            }
        }
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Categories assigned']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

<?php
// api/delete_admin.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        if ($stmt->execute([$id])) {
            $pdo->prepare("DELETE FROM admin_categories WHERE admin_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM admin_products WHERE admin_id = ?")->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Admin deleted']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

<?php
// api/toggle_product_status.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    try {
        $stmt = $pdo->prepare("SELECT status FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $new_status = ($row['status'] == 'active') ? 'inactive' : 'active';
            $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status, $id])) {
                echo json_encode(['status' => 'success', 'message' => 'Status updated', 'new_status' => $new_status]);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

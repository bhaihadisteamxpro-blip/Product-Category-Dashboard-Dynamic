<?php
// api/toggle_category_status.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        $stmt = $pdo->prepare("SELECT status FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if ($row) {
            $new_status = ($row['status'] == 'active') ? 'inactive' : 'active';
            $stmt = $pdo->prepare("UPDATE categories SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status, $id])) {
                echo json_encode(['status' => 'success', 'message' => 'Status updated to ' . $new_status, 'new_status' => $new_status]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Category not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID missing']);
}
?>

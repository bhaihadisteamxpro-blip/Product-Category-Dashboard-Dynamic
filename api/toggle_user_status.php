<?php
// api/toggle_user_status.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    try {
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $new_status = ($row['status'] == 'active') ? 'inactive' : 'active';
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status, $id])) {
                echo json_encode(['status' => 'success', 'message' => 'User status updated', 'new_status' => $new_status]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Update failed']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

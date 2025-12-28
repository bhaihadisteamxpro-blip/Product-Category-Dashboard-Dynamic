<?php
// api/get_admins.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

try {
    $stmt = $pdo->prepare("SELECT id, admin_id, full_name, username, email, phone, department, status, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $admins]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

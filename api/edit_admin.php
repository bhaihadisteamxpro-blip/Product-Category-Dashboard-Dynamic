<?php
// api/edit_admin.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if ($id <= 0 || empty($full_name) || empty($username)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $id]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, department = ?, updated_at = NOW() WHERE id = ? AND role = 'admin'");
        if ($stmt->execute([$full_name, $username, $email, $phone, $department, $id])) {
            echo json_encode(['status' => 'success', 'message' => 'Admin updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update admin']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>

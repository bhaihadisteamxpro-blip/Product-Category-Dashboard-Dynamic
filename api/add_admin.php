<?php
// api/add_admin.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
        exit();
    }
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Already exists']);
            exit();
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $admin_id = 'ADM' . date('Ymd') . rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO users (admin_id, full_name, username, email, password, phone, department, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'admin', 'active', NOW())");
        if ($stmt->execute([$admin_id, $full_name, $username, $email, $hashed_password, $phone, $department])) {
            echo json_encode(['status' => 'success', 'message' => 'Admin added']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

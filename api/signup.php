<?php
// api/signup.php
header('Content-Type: application/json');
require_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit();
    }

    try {
        // Check if username or email exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists']);
            exit();
        }

        // Auto-generate Admin ID (Simple Random)
        $admin_id = 'ADM-' . strtoupper(substr(uniqid(), -5));
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new Admin (Role = admin, Status = active for now as per requirement)
        $stmt = $pdo->prepare("INSERT INTO users (admin_id, full_name, username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'admin', 'active', NOW())");
        
        if ($stmt->execute([$admin_id, $full_name, $username, $email, $hashed_password])) {
             echo json_encode(['status' => 'success', 'message' => 'Account created successfully! Please Login.']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}
?>

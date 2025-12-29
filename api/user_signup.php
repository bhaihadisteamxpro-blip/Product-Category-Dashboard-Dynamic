<?php
// api/user_signup.php
session_start();
require_once '../database/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists']);
        exit;
    }

    // Insert new user
    $status = 'active';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $admin_id = 'USR' . date('ymd') . rand(100,999); 

    try {
        $sql = "INSERT INTO users (admin_id, full_name, username, email, password, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'user', 'active', NOW())";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$admin_id, $full_name, $username, $email, $hashed_password])) {
             $last_id = $pdo->lastInsertId();

             // Do NOT auto-login after signup. Require explicit login for security/verification.
             echo json_encode([
                 'status' => 'success',
                 'message' => 'Registration successful. Please log in.',
                 'auto_login' => false
             ]);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>

<?php
// api/login.php
header('Content-Type: application/json');
require_once '../database/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'super_admin';

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter username and password!']);
        exit();
    }

    try {
        if ($user_type == 'super_admin') {
            // Hardcoded for demo/initial setup as in original login.php
            if ($username == 'superadmin' && $password == 'admin123') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'superadmin';
                $_SESSION['full_name'] = 'Super Admin';
                $_SESSION['user_role'] = 'super_admin';
                $_SESSION['admin_id'] = 'SUPER001';
                $_SESSION['department'] = 'management';
                
                echo json_encode(['status' => 'success', 'redirect' => 'dashboard.php']);
                exit();
            } else {
                // Check DB for super admin too if not using hardcoded
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'super_admin' AND status = 'active'");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['admin_id'] = $user['admin_id'];
                    $_SESSION['department'] = $user['department'];
                    
                    echo json_encode(['status' => 'success', 'redirect' => 'dashboard.php']);
                    exit();
                }
                echo json_encode(['status' => 'error', 'message' => 'Invalid super admin credentials!']);
            }
        } else {
            // Admin Check
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['admin_id'] = $user['admin_id'];
                $_SESSION['department'] = $user['department'];
                
                // Update last login
                $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->execute([$user['id']]);
                
                echo json_encode(['status' => 'success', 'redirect' => 'admin.php']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid admin credentials or account inactive!']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>

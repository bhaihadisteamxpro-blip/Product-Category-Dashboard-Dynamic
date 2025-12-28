<?php
// backend/auth.php
session_start();

function checkAuth($role = 'super_admin') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != $role) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
            exit();
        } else {
            header('Location: ../login.php');
            exit();
        }
    }
}
?>

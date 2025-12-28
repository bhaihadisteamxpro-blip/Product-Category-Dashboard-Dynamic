<?php
// api/user_logout.php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_logged_in'])) {
    unset($_SESSION['user_logged_in']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
}

echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
?>

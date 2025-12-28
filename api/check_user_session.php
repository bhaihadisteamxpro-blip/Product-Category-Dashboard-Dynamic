<?php
// api/check_user_session.php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    echo json_encode(['status' => 'logged_in', 'user_name' => $_SESSION['user_name'] ?? 'User']);
} else {
    echo json_encode(['status' => 'guest']);
}
?>

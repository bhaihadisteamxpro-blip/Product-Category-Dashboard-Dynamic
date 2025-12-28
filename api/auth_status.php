<?php
// api/auth_status.php
header('Content-Type: application/json');
session_start();

$response = [
    'logged_in' => false,
    'user' => null
];

if (isset($_SESSION['user_id'])) {
    $response['logged_in'] = true;
    $response['user'] = [
        'id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['user_role']
    ];
}

echo json_encode($response);
?>

<?php
require_once 'config/database.php';

// Create a sample regular user
$username = 'user';
$password = '12345678';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$email = 'user@example.com';
$full_name = 'Demo User';
$phone = '1234567890';
$role = 'user';
$admin_id = 'USR'.date('Ymd').rand(100,999); // Generic ID format

// Check if exists
$check = $conn->query("SELECT id FROM users WHERE username = '$username'");
if ($check->num_rows == 0) {
    $sql = "INSERT INTO users (admin_id, full_name, username, email, password, phone, role, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $admin_id, $full_name, $username, $email, $hashed_password, $phone, $role);
    
    if ($stmt->execute()) {
        echo "Sample User created successfully.\nUsername: user\nPassword: 12345678\n";
    } else {
        echo "Error creating user: " . $conn->error . "\n";
    }
} else {
    echo "Sample User already exists.\n";
}
?>

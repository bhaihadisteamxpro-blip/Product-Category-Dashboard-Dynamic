<?php
require_once 'config/database.php';

// 1. Create admin_products table
$sql1 = "CREATE TABLE IF NOT EXISTS admin_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql1) === TRUE) {
    echo "Table admin_products created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// 2. Add 'image' column to products table
$sql2 = "SHOW COLUMNS FROM products LIKE 'image'";
$result = $conn->query($sql2);
if ($result->num_rows == 0) {
    $sql3 = "ALTER TABLE products ADD COLUMN image VARCHAR(255) AFTER category_id";
    if ($conn->query($sql3) === TRUE) {
        echo "Column 'image' added to products table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'image' already exists.\n";
}

// 3. Ensure admin_categories exists
$sql4 = "CREATE TABLE IF NOT EXISTS admin_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql4) === TRUE) {
    echo "Table admin_categories checked/created.\n";
} else {
    echo "Error checking admin_categories: " . $conn->error . "\n";
}

echo "Database updates completed.\n";
?>

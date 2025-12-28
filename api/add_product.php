<?php
// api/add_product.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $sku = trim($_POST['sku'] ?? '');
    $price = $_POST['price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    $unit = $_POST['unit'] ?? 'pcs';
    $min_stock = $_POST['min_stock'] ?? 10;
    $status = $_POST['status'] ?? 'active';

    if (empty($product_name) || empty($sku)) {
        echo json_encode(['status' => 'error', 'message' => 'Name and SKU required']);
        exit();
    }
    try {
        // Image Upload Logic
        $image_path = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = $_FILES['product_image']['name'];
            $file_tmp = $_FILES['product_image']['tmp_name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'prod_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $target_dir = "uploads/products/";
                $target_file = $target_dir . $new_filename;
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_path = $target_file; // Relative path stored in DB
                }
            }
        }

        $stmt = $pdo->prepare("INSERT INTO products (product_name, category_id, sku, price, quantity, unit, min_stock, status, image, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$product_name, $category_id, $sku, $price, $quantity, $unit, $min_stock, $status, $image_path, $_SESSION['user_id']])) {
            echo json_encode(['status' => 'success', 'message' => 'Product added']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

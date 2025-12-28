<?php
// api/edit_product.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $product_name = trim($_POST['product_name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'pcs');
    $min_stock = intval($_POST['min_stock'] ?? 5);
    $description = trim($_POST['product_description'] ?? '');

    // Handle Image Upload
    $image_path = null;
    $image_sql = "";
    
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                $upload_dir = "uploads/products/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid('prod_') . '.' . $file_ext;
            $dest_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $dest_path)) {
                $image_path = 'uploads/products/' . $new_filename;
                $image_sql = ", image = ?";
            }
        }
    }

    try {
        if ($image_path) {
            $stmt = $pdo->prepare("UPDATE products SET product_name = ?, sku = ?, category_id = ?, price = ?, quantity = ?, unit = ?, min_stock = ?, product_description = ?, updated_at = NOW() $image_sql WHERE id = ?");
            $params = [$product_name, $sku, $category_id, $price, $quantity, $unit, $min_stock, $description, $image_path, $id];
        } else {
            $stmt = $pdo->prepare("UPDATE products SET product_name = ?, sku = ?, category_id = ?, price = ?, quantity = ?, unit = ?, min_stock = ?, product_description = ?, updated_at = NOW() WHERE id = ?");
            $params = [$product_name, $sku, $category_id, $price, $quantity, $unit, $min_stock, $description, $id];
        }

        if ($stmt->execute($params)) {
             echo json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

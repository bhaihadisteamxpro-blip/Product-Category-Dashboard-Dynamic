<?php
// api/add_category.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    $category_description = trim($_POST['category_description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    if (empty($category_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Name required']);
        exit();
    }
    try {
        // Image Upload Logic
        $image_path = null;
        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['category_image']['name'];
            $file_tmp = $_FILES['category_image']['tmp_name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'cat_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $target_dir = "../User_admin/uploads/categories/";
                $target_file = $target_dir . $new_filename;
                
                // Ensure directory exists
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_path = $target_file; // Relative path stored in DB
                }
            }
        }

        $stmt = $pdo->prepare("INSERT INTO categories (category_name, category_description, status, image, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$category_name, $category_description, $status, $image_path, $_SESSION['user_id']])) {
            echo json_encode(['status' => 'success', 'message' => 'Category added']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

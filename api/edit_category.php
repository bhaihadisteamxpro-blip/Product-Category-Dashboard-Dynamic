<?php
// api/edit_category.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $category_name = trim($_POST['category_name'] ?? '');
    $category_description = trim($_POST['category_description'] ?? '');

    if ($id <= 0 || empty($category_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
        exit();
    }

    try {
        // Check if category name already exists for another ID
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE category_name = ? AND id != ?");
        $stmt->execute([$category_name, $id]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Category name already exists']);
            exit();
        }

        // Image Upload Logic
        $image_sql = "";
        $image_params = [];
        
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
                    $image_sql = ", image = ?";
                    $image_params[] = $target_file; // Relative path stored in DB
                }
            }
        }

        // Prepare SQL
        $sql = "UPDATE categories SET category_name = ?, category_description = ? $image_sql WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        // Bind params
        $params = [$category_name, $category_description];
        if (!empty($image_params)) {
            $params = array_merge($params, $image_params);
        }
        $params[] = $id;

        if ($stmt->execute($params)) {
            echo json_encode(['status' => 'success', 'message' => 'Category updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update category']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>

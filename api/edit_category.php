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

        $stmt = $pdo->prepare("UPDATE categories SET category_name = ?, category_description = ? WHERE id = ?");
        if ($stmt->execute([$category_name, $category_description, $id])) {
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

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
        $stmt = $pdo->prepare("INSERT INTO categories (category_name, category_description, status, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt->execute([$category_name, $category_description, $status, $_SESSION['user_id']])) {
            echo json_encode(['status' => 'success', 'message' => 'Category added']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

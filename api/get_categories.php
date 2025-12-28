<?php
// api/get_categories.php
header('Content-Type: application/json');
require_once '../database/db.php';
require_once '../backend/auth.php';

checkAuth('super_admin');

try {
    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY category_name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $categories]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

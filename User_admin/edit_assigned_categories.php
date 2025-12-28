<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../frontend/login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
$full_name = $_SESSION['full_name'];
$admin_id = $_SESSION['admin_id'];
$department = $_SESSION['department'] ?? '';
$user_id = $_SESSION['user_id']; // Current logged in admin ID

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../frontend/index.php');
    exit();
}

// NOTE: Status toggling and Stock updates are removed from UX as per requirement (View Only)

// Fetch assigned categories
// Fetch assigned categories
$assigned_categories_query = "SELECT c.id, c.category_name, c.category_description, c.status, 
                              (SELECT COUNT(*) FROM products p 
                               JOIN admin_products ap ON p.id = ap.product_id 
                               WHERE p.category_id = c.id AND p.status = 'active' AND ap.admin_id = ?) as product_count 
                              FROM categories c 
                              INNER JOIN admin_categories ac ON c.id = ac.category_id 
                              WHERE ac.admin_id = ? AND c.status = 'active' 
                              ORDER BY c.category_name";
$assigned_stmt = $conn->prepare($assigned_categories_query);
$assigned_stmt->bind_param("ii", $user_id, $user_id);
$assigned_stmt->execute();
$assigned_result = $assigned_stmt->get_result();

$selected_category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$category_products = [];
if ($selected_category_id > 0) {
    // Verify if category belongs to admin before showing
    $verify_cat = $conn->prepare("SELECT 1 FROM admin_categories WHERE admin_id = ? AND category_id = ?");
    $verify_cat->bind_param("ii", $user_id, $selected_category_id);
    $verify_cat->execute();
    if($verify_cat->get_result()->num_rows > 0) {
        $products_query = "SELECT p.* FROM products p 
                           INNER JOIN admin_products ap ON p.id = ap.product_id
                           WHERE p.category_id = ? AND ap.admin_id = ? AND p.status != 'deleted' 
                           ORDER BY p.product_name";
        $products_stmt = $conn->prepare($products_query);
        $products_stmt->bind_param("ii", $selected_category_id, $user_id);
        $products_stmt->execute();
        $category_products_result = $products_stmt->get_result();
        while ($product = $category_products_result->fetch_assoc()) { $category_products[] = $product; }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Inventory | Admin</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  <style>

    .category-item { padding: 12px; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px; cursor: pointer; transition: 0.3s; background: #fff; }
    .category-item:hover { background: #f4f4f4; }
    .category-item.active { background: #dc3545; color: white; border-color: #dc3545; box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3); }
    .info-badge { font-size: 11px; padding: 4px 8px; border-radius: 10px; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto"><li class="nav-item"><a class="nav-link" href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li></ul>
  </nav>

  <?php include 'sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header"><div class="container-fluid"><h1>Inventory Details <small class="text-muted">(Read-Only Mode)</small></h1></div></section>
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-4">
            <div class="card shadow-sm"><div class="card-header bg-dark"><h3 class="card-title">My Categories</h3></div>
              <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <?php while($cat = $assigned_result->fetch_assoc()): ?>
                  <div class="category-item <?php echo ($selected_category_id == $cat['id']) ? 'active' : ''; ?>" onclick="location.href='?category_id=<?php echo $cat['id']; ?>'">
                    <i class="fas fa-folder mr-2"></i>
                    <strong><?php echo htmlspecialchars($cat['category_name']); ?></strong>
                    <span class="float-right badge badge-pill <?php echo ($selected_category_id == $cat['id']) ? 'badge-light' : 'badge-primary'; ?>">
                      <?php echo $cat['product_count']; ?>
                    </span>
                  </div>
                <?php endwhile; ?>
              </div>
            </div>
          </div>
          <div class="col-md-8">
            <?php if($selected_category_id > 0): ?>
              <div class="card shadow-sm">
                <div class="card-header bg-dark"><h3 class="card-title">Products List</h3></div>
                <div class="card-body p-0">
                  <table class="table table-hover table-striped mb-0">
                    <thead class="bg-light">
                      <tr>
                        <th>Product Name</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($category_products as $p): ?>
                        <tr>
                          <td class="align-middle">
                            <div class="font-weight-bold"><?php echo htmlspecialchars($p['product_name']); ?></div>
                            <small class="text-muted">SKU: <?php echo htmlspecialchars($p['sku']); ?></small>
                          </td>
                          <td class="text-center align-middle">
                            <span class="badge badge-lg p-2 <?php echo ($p['quantity'] <= $p['min_stock']) ? 'badge-danger' : 'badge-success'; ?>" style="min-width: 50px; font-size: 14px;">
                              <?php echo $p['quantity']; ?>
                            </span>
                          </td>
                          <td class="text-center align-middle">
                            <span class="badge info-badge <?php echo ($p['status'] == 'active') ? 'badge-success' : 'badge-secondary'; ?>">
                              <?php echo strtoupper($p['status']); ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php else: ?>
              <div class="card card-outline card-info">
                <div class="card-body text-center py-5">
                  <i class="fas fa-arrow-left fa-3x text-info mb-3"></i>
                  <h4>Please select a category</h4>
                  <p class="text-muted">Select a category from the left to view its complete product inventory.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>

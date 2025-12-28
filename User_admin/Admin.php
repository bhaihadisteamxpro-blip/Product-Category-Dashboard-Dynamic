<?php
// User_admin/Admin.php
session_start();
require_once 'config/database.php';

// Check if logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../frontend/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$admin_id_str = $_SESSION['admin_id'];
$department = $_SESSION['department'] ?? 'General';

// --- DATA FETCHING ---

// 1. Total assigned categories
$cat_query = "SELECT COUNT(*) as count FROM admin_categories WHERE admin_id = $user_id";
$cat_res = $conn->query($cat_query);
$total_cats = $cat_res->fetch_assoc()['count'] ?? 0;

// 2. Total active products in assigned products list
$prod_query = "SELECT COUNT(p.id) as count 
               FROM products p 
               INNER JOIN admin_products ap ON p.id = ap.product_id
               WHERE ap.admin_id = $user_id AND p.status = 'active'";
$prod_res = $conn->query($prod_query);
$total_prods = $prod_res->fetch_assoc()['count'] ?? 0;

// 3. Low Stock Items (Quantity <= min_stock)
$low_stock_query = "SELECT COUNT(p.id) as count 
                    FROM products p 
                    INNER JOIN admin_products ap ON p.id = ap.product_id
                    WHERE ap.admin_id = $user_id AND p.quantity <= p.min_stock AND p.status = 'active'";
$low_stock_res = $conn->query($low_stock_query);
$low_stock_count = $low_stock_res->fetch_assoc()['count'] ?? 0;

// 4. Recent Activities/Products
$recent_prods_query = "SELECT p.*, c.category_name 
                      FROM products p 
                      JOIN categories c ON p.category_id = c.id
                      JOIN admin_products ap ON p.id = ap.product_id
                      WHERE ap.admin_id = $user_id
                      ORDER BY p.updated_at DESC LIMIT 6";
$recent_prods = $conn->query($recent_prods_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard | Stock Management</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="Admin.php" class="nav-link">Home</a>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="../backend/logout.php">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </nav>

  <?php include 'sidebar.php'; ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard Overview</h1>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <!-- Stats Row -->
        <div class="row">
          <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?php echo $total_cats; ?></h3>
                <p>Assigned Categories</p>
              </div>
              <div class="icon"><i class="fas fa-list"></i></div>
            </div>
          </div>
          
          <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?php echo $total_prods; ?></h3>
                <p>Assigned Products</p>
              </div>
              <div class="icon"><i class="fas fa-box"></i></div>
            </div>
          </div>

          <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><?php echo $low_stock_count; ?></h3>
                <p>Low Stock Items</p>
              </div>
              <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Recent Activity Table -->
          <div class="col-md-12">
            <div class="card">
              <div class="card-header border-transparent">
                <h3 class="card-title">Recent Products in Your Categories</h3>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table m-0">
                    <thead>
                      <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>SKU</th>
                        <th>Stock</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if($recent_prods->num_rows > 0): ?>
                        <?php while($p = $recent_prods->fetch_assoc()): ?>
                        <tr>
                          <td><strong><?php echo htmlspecialchars($p['product_name']); ?></strong></td>
                          <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                          <td><code><?php echo htmlspecialchars($p['sku']); ?></code></td>
                          <td><span class="badge badge-<?php echo ($p['quantity'] > $p['min_stock']) ? 'success' : 'danger'; ?>"><?php echo $p['quantity']; ?></span></td>
                          <td><span class="badge badge-info"><?php echo ucfirst($p['status']); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr><td colspan="5" class="text-center">No products found.</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer text-center">
                <a href="edit_assigned_categories.php" class="btn btn-sm btn-primary">Manage All Products</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">Al Hadi Solutions</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
  </footer>
</div>

<!-- Scripts -->
<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>

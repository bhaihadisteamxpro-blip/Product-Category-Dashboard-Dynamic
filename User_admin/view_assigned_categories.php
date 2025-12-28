<?php
// User_admin/view_assigned_categories.php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../frontend/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$admin_id_str = $_SESSION['admin_id'];
$department = $_SESSION['department'] ?? 'General';

// Fetch assigned categories for current admin
$query = "SELECT c.id, c.category_name, c.category_description, c.status, 
                (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'active') as active_products
          FROM categories c
          INNER JOIN admin_categories ac ON c.id = ac.category_id
          WHERE ac.admin_id = $user_id AND c.status = 'active'
          ORDER BY c.category_name";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Assigned Categories | Admin</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  <style>
    .user-info-sidebar { text-align: center; padding: 15px; background: rgba(0,0,0,0.1); border-radius: 10px; margin: 10px; }
    .user-avatar { width: 60px; height: 60px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; margin: 0 auto 15px; }
    .user-name { font-size: 16px; font-weight: bold; color: white; margin-bottom: 5px; }
    .user-role { display: inline-block; padding: 3px 10px; background: #dc3545; color: white; border-radius: 15px; font-size: 12px; font-weight: bold; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><a class="nav-link" href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="Admin.php" class="brand-link">
      <span class="brand-text font-weight-light" style="font-weight: bold !important; font-family: times; color: white !important; text-align: center !important; margin-left: 26px !important; font-size: 25px !important;">ADMIN PANEL</span>
    </a>
    <div class="sidebar">
      <div class="user-info-sidebar">
        <div class="user-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
        <div class="user-name"><?php echo htmlspecialchars($full_name); ?></div>
        <div class="user-role"><?php echo strtoupper($department); ?> ADMIN</div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item"><a href="Admin.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active"><i class="nav-icon fas fa-archive"></i><p>Categories <i class="fas fa-angle-left right"></i></p></a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="view_assigned_categories.php" class="nav-link active"><i class="far fa-circle nav-icon"></i><p>View Assigned</p></a></li>
              <li class="nav-item"><a href="edit_assigned_categories.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Manage Assigned</p></a></li>
            </ul>
          </li>
          <li class="nav-item"><a href="admin_settings.php" class="nav-link"><i class="nav-icon fas fa-cog"></i><p>Settings</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content -->
  <div class="content-wrapper">
    <section class="content-header"><div class="container-fluid"><h1>Assigned Categories</h1></div></section>
    <section class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header"><h3 class="card-title">List of categories you are responsible for</h3></div>
          <div class="card-body p-0">
            <table class="table table-striped">
              <thead><tr><th>Category Name</th><th>Description</th><th>Active Products</th><th>Status</th></tr></thead>
              <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><strong><?php echo $row['category_name']; ?></strong></td>
                  <td><?php echo $row['category_description']; ?></td>
                  <td><span class="badge badge-info"><?php echo $row['active_products']; ?> Products</span></td>
                  <td><span class="badge badge-success"><?php echo ucfirst($row['status']); ?></span></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
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

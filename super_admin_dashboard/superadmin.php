<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'super_admin') {
    header('Location: ../login.php'); // Parent folder ke login.php par jayega
    exit();
}

$user_role = $_SESSION['user_role'];
$full_name = $_SESSION['full_name'];
$admin_id = $_SESSION['admin_id'];
$department = $_SESSION['department'] ?? '';

// Handle logout - Redirect to parent folder's index.php
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../frontend/index.php'); // Parent folder ke index.php par jayega
    exit();
}
// Fetch Dashboard Statistics
$stats = [
    'total_admins' => 0,
    'active_admins' => 0,
    'total_products' => 0,
    'total_categories' => 0
];

if ($conn) {
    // Total Admins
    $res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    if ($res) $stats['total_admins'] = $res->fetch_assoc()['count'];

    // Active Admins
    $res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND status = 'active'");
    if ($res) $stats['active_admins'] = $res->fetch_assoc()['count'];

    // Total Products
    $res = $conn->query("SELECT COUNT(*) as count FROM products");
    if ($res) $stats['total_products'] = $res->fetch_assoc()['count'];

    // Total Categories
    $res = $conn->query("SELECT COUNT(*) as count FROM categories");
    if ($res) $stats['total_categories'] = $res->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Super Admin Dashboard - Stock Management</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  <style>
    /* Custom Styles for Widgets */
    .small-box .inner { padding: 15px; }
    .small-box h3 { font-size: 2.5em; font-weight: bold; margin: 0 0 5px 0; white-space: nowrap; }
    .small-box p { font-size: 1.1em; margin-bottom: 5px; }
    .small-box .icon { top: 15px; right: 15px; font-size: 60px; opacity: 0.3; transition: all 0.3s linear; }
    .small-box:hover .icon { font-size: 70px; opacity: 0.5; }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="superadmin.php" class="nav-link">Home</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- User Info -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fas fa-user-circle fa-lg"></i>
          <span class="ml-2"><?php echo $full_name; ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div class="dropdown-header">
            <div class="d-flex align-items-center">
              <div class="user-avatar" style="width: 40px; height: 40px; font-size: 18px; margin: 0; background: linear-gradient(135deg, #dc3545, #ff6b6b); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <?php echo strtoupper(substr($full_name, 0, 1)); ?>
              </div>
              <div class="ml-3">
                <h6 class="mb-0"><?php echo $full_name; ?></h6>
                <small class="text-muted"><?php echo $admin_id; ?></small>
              </div>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <a href="super_admin_profile.php" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile
          </a>
          <a href="#" class="dropdown-item">
            <i class="fas fa-cog mr-2"></i> Settings
          </a>
          <div class="dropdown-divider"></div>
          <a href="../backend/logout.php" class="dropdown-item">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <?php include 'sidebar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard Overview</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="superadmin.php">Home</a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Welcome Card -->
        <div class="row">
          <div class="col-12">
            <div class="card card-primary card-outline elevation-2">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-md-2 text-center">
                     <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #007bff, #00d2ff); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto;">
                      <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                    </div>
                  </div>
                  <div class="col-md-10">
                    <h3 class="text-primary">Welcome back, <?php echo $full_name; ?>!</h3>
                    <p class="text-muted mb-1">
                      You are logged in as <strong>Super Administrator</strong>. Here's what's happening in your stock management system today.
                    </p>
                    <div class="mt-2">
                        <span class="badge badge-info mr-2"><i class="fas fa-id-badge mr-1"></i> ID: <?php echo $admin_id; ?></span>
                        <span class="badge badge-secondary"><i class="fas fa-building mr-1"></i> Dept: <?php echo ucfirst($department); ?></span>
                        <span class="badge badge-light border ml-2"><i class="far fa-clock mr-1"></i> <?php echo date('F j, Y'); ?></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?php echo $stats['total_admins']; ?></h3>
                <p>Total Admins</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
              <a href="manage_admins.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?php echo $stats['active_admins']; ?></h3>
                <p>Active Admins</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-check"></i>
              </div>
              <a href="manage_admins.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?php echo $stats['total_products']; ?></h3>
                <p>Total Products</p>
              </div>
              <div class="icon">
                <i class="fas fa-box-open"></i>
              </div>
              <a href="manage_products.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><?php echo $stats['total_categories']; ?></h3>
                <p>Total Categories</p>
              </div>
              <div class="icon">
                <i class="fas fa-tags"></i>
              </div>
              <a href="manage_category.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>

        <!-- Recent Activities -->
        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-history mr-2"></i>Recent Activities
                </h3>
              </div>
              <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="fas fa-user-plus mr-2"></i> New admin added
                      <span class="float-right text-muted text-sm">2 mins ago</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="fas fa-box mr-2"></i> 5 new products added
                      <span class="float-right text-muted text-sm">1 hour ago</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="fas fa-chart-line mr-2"></i> Monthly report generated
                      <span class="float-right text-muted text-sm">Yesterday</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="fas fa-cog mr-2"></i> System updated
                      <span class="float-right text-muted text-sm">2 days ago</span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
          <!-- Quick Stats -->
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-2"></i>System Statistics
                </h3>
              </div>
              <div class="card-body">
                <div class="progress-group">
                  System Performance
                  <span class="float-right"><b>85</b>/100</span>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-success" style="width: 85%"></div>
                  </div>
                </div>
                
                <div class="progress-group">
                  Storage Usage
                  <span class="float-right"><b>45</b>/100 GB</span>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-warning" style="width: 45%"></div>
                  </div>
                </div>
                
                <div class="progress-group">
                  Database Load
                  <span class="float-right"><b>30</b>/100</span>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-info" style="width: 30%"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">Al Hadi Solutions</a>.</strong>
    All rights reserved. Customized by Al Hadi.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0 | 
      <b>User:</b> <?php echo $full_name; ?> | 
      <b>Role:</b> Super Admin
    </div>
  </footer>
</div>

<!-- jQuery -->
<script src="../assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>







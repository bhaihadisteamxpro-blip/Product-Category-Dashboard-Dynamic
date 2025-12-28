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
    .user-info-sidebar {
        text-align: center;
        padding: 15px;
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
        margin: 10px;
    }
    
    .user-avatar {
        width: 60px;
        height: 60px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        margin: 0 auto 15px;
    }
    
    .user-name {
        font-size: 16px;
        font-weight: bold;
        color: white;
        margin-bottom: 5px;
    }
    
    .user-role {
        display: inline-block;
        padding: 3px 10px;
        background: #dc3545;
        color: white;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    }
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
              <div class="user-avatar" style="width: 40px; height: 40px; font-size: 18px; margin: 0;">
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

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="superadmin.php" class="brand-link">
      <span class="brand-text font-weight-light" style="font-weight: bold !important; font-family: times; color: white !important; text-align: center !important; margin-left: 26px !important; font-size: 25px !important;">
        SUPER ADMIN
      </span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-info-sidebar">
        <div class="user-avatar">
          <?php echo strtoupper(substr($full_name, 0, 1)); ?>
        </div>
        <div class="user-name"><?php echo $full_name; ?></div>
        <div class="user-role">
          SUPER ADMIN
        </div>
        <div style="color: #ccc; font-size: 12px; margin-top: 5px;">ID: <?php echo $admin_id; ?></div>
      </div>
      
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item menu-open">
            <a href="superadmin.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          
          <!-- User Management -->
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fa fa-users"></i>
              <p>
                User Management
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="add_admin.php" class="nav-link">
                  <i class="fa fa-user-plus nav-icon"></i>
                  <p>Add Admins</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="manage_admins.php" class="nav-link">
                  <i class="fa fa-cog nav-icon"></i>
                  <p>Manage Admins</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="assigned_category.php" class="nav-link">
                  <i class="fas fa-list-alt nav-icon"></i>
                  <p>Assign Categories</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="assigned_product.php" class="nav-link">
                  <i class="fas fa-boxes nav-icon"></i>
                  <p>Assign Products</p>
                </a>
              </li>
            </ul> 
          </li>
          
          <!-- Categories -->
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fa fa-archive"></i>
              <p>
                Categories
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="add_category.php" class="nav-link">
                  <i class="fa fa-plus nav-icon"></i>
                  <p>Add Category</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="manage_category.php" class="nav-link">
                  <i class="fa fa-cog nav-icon"></i>
                  <p>Manage Categories</p>
                </a>
              </li>
            </ul> 
          </li>
          
          <!-- Products -->
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fa fa-box"></i>
              <p>
                Products
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="add_product.php" class="nav-link">
                  <i class="fa fa-plus nav-icon"></i>
                  <p>Add Products</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="manage_products.php" class="nav-link">
                  <i class="fa fa-cog nav-icon"></i>
                  <p>Manage Products</p>
                </a>
              </li>
            </ul> 
          </li>
          
          <!-- Super Admin Settings -->
          <li class="nav-item">
            <a href="super_admin_profile.php" class="nav-link">
              <i class="nav-icon fa fa-user-shield"></i>
              <p>Super Admin Settings</p>
            </a>
          </li>
          
          <!-- Logout -->
          <li class="nav-item">
            <a href="../backend/logout.php" class="nav-link">
              <i class="nav-icon fa fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Super Admin Dashboard</h1>
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
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-user-circle mr-2"></i>Welcome, <?php echo $full_name; ?>!
                </h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8">
                    <h5>Stock Management System - Super Admin Panel</h5>
                    <p class="mb-1">
                      <strong>Role:</strong> 
                      <span class="badge badge-danger">
                        Super Administrator
                      </span>
                    </p>
                    <p class="mb-1"><strong>Admin ID:</strong> <?php echo $admin_id; ?></p>
                    <p class="mb-1"><strong>Department:</strong> <?php echo ucfirst($department); ?></p>
                    <p class="mb-0"><strong>Login Time:</strong> <?php echo date('F j, Y, g:i a'); ?></p>
                  </div>
                  <div class="col-md-4 text-center">
                    <div class="user-avatar" style="width: 100px; height: 100px; font-size: 36px;">
                      <?php echo strtoupper(substr($full_name, 0, 1)); ?>
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
            <div class="small-box bg-info">
              <div class="inner">
                <h3>15</h3>
                <p>Total Admins</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
              <a href="manage_admins.php" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3>12</h3>
                <p>Active Admins</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-check"></i>
              </div>
              <a href="manage_admins.php?status=active" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>150</h3>
                <p>Total Products</p>
              </div>
              <div class="icon">
                <i class="fas fa-box"></i>
              </div>
              <a href="manage_products.php" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>65<sup style="font-size: 20px">%</sup></h3>
                <p>Revenue Growth</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="#" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
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







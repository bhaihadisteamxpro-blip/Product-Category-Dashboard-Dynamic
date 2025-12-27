<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
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
    header('Location: ../index.php');
    exit();
}

// Fetch assigned categories for current admin
$assigned_categories_query = "SELECT c.id, c.category_name, c.category_description, c.status, 
                                     ac.assigned_date
                              FROM categories c
                              INNER JOIN admin_categories ac ON c.id = ac.category_id
                              WHERE ac.admin_id = ? AND c.status = 'active'
                              ORDER BY c.category_name";
$assigned_stmt = $conn->prepare($assigned_categories_query);
$assigned_stmt->bind_param("i", $user_id);
$assigned_stmt->execute();
$assigned_result = $assigned_stmt->get_result();

// Count statistics
$stats_query = "SELECT 
                COUNT(*) as total_assigned_categories,
                COUNT(DISTINCT ac.category_id) as unique_categories,
                COUNT(DISTINCT p.id) as total_products_in_categories
                FROM admin_categories ac
                JOIN categories c ON ac.category_id = c.id
                LEFT JOIN products p ON c.id = p.category_id
                WHERE ac.admin_id = ? AND c.status = 'active'";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Assigned Categories - Admin Dashboard</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
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
        background: #007bff;
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
        background: #007bff;
        color: white;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .category-card {
        border: 2px solid #007bff;
        border-radius: 10px;
        margin-bottom: 20px;
        transition: transform 0.3s;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .category-icon {
        font-size: 40px;
        color: #007bff;
        margin-bottom: 15px;
    }
    
    .category-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .badge-active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .stats-card {
        border-radius: 10px;
        margin-bottom: 20px;
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
        <a href="admin.php" class="nav-link">Home</a>
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
          <a href="#" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile
          </a>
          <a href="admin_settings.php" class="dropdown-item">
            <i class="fas fa-cog mr-2"></i> Settings
          </a>
          <div class="dropdown-divider"></div>
          <a href="admin.php?logout=1" class="dropdown-item">
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

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="admin.php" class="brand-link">
      <span class="brand-text font-weight-light" style="font-weight: bold !important; font-family: times; color: white !important; text-align: center !important; margin-left: 26px !important; font-size: 25px !important;">
        ADMIN PANEL
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
          <?php echo strtoupper($department) . ' ADMIN'; ?>
        </div>
        <div style="color: #ccc; font-size: 12px; margin-top: 5px;">ID: <?php echo $admin_id; ?></div>
      </div>
      
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="admin.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          
          <!-- Categories -->
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fa fa-archive"></i>
              <p>
                Categories
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: block;">
              <li class="nav-item">
                <a href="view_assigned_categories.php" class="nav-link active">
                  <i class="fas fa-list nav-icon"></i>
                  <p>View Assigned Categories</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="edit_assigned_categories.php" class="nav-link">
                  <i class="fas fa-edit nav-icon"></i>
                  <p>Edit Assigned Categories</p>
                </a>
              </li>
            </ul> 
          </li>
          
          <!-- Admin Settings -->
          <li class="nav-item">
            <a href="admin_settings.php" class="nav-link">
              <i class="nav-icon fa fa-cog"></i>
              <p>Admin Settings</p>
            </a>
          </li>
          
          <!-- Logout -->
          <li class="nav-item">
            <a href="admin.php?logout=1" class="nav-link">
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
            <h1 class="m-0">
              <i class="fas fa-archive mr-2"></i>View Assigned Categories
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Categories</a></li>
              <li class="breadcrumb-item active">View Assigned Categories</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
          <div class="col-lg-4 col-6">
            <div class="small-box bg-info stats-card">
              <div class="inner">
                <h3><?php echo $stats['total_assigned_categories'] ?? 0; ?></h3>
                <p>Assigned Categories</p>
              </div>
              <div class="icon">
                <i class="fas fa-archive"></i>
              </div>
              <a href="#" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-4 col-6">
            <div class="small-box bg-success stats-card">
              <div class="inner">
                <h3><?php echo $stats['unique_categories'] ?? 0; ?></h3>
                <p>Unique Categories</p>
              </div>
              <div class="icon">
                <i class="fas fa-folder"></i>
              </div>
              <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-4 col-6">
            <div class="small-box bg-warning stats-card">
              <div class="inner">
                <h3><?php echo $stats['total_products_in_categories'] ?? 0; ?></h3>
                <p>Total Products</p>
              </div>
              <div class="icon">
                <i class="fas fa-box"></i>
              </div>
              <a href="#" class="small-box-footer">View Products <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Assigned Categories -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-list-alt mr-2"></i>Your Assigned Categories
                </h3>
                <div class="card-tools">
                  <span class="badge badge-primary">
                    Total: <?php echo $assigned_result->num_rows; ?> Categories
                  </span>
                </div>
              </div>
              
              <div class="card-body">
                <?php if ($assigned_result->num_rows > 0): ?>
                  <div class="row">
                    <?php while($category = $assigned_result->fetch_assoc()): ?>
                      <div class="col-md-4 mb-4">
                        <div class="card category-card">
                          <div class="card-body text-center">
                            <div class="category-icon">
                              <i class="fas fa-folder"></i>
                            </div>
                            <span class="category-badge badge-active">
                              Assigned
                            </span>
                            <h4 class="card-title"><?php echo htmlspecialchars($category['category_name']); ?></h4>
                            
                            <?php if (!empty($category['category_description'])): ?>
                              <p class="card-text">
                                <?php echo htmlspecialchars($category['category_description']); ?>
                              </p>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                              <span class="badge badge-info">
                                <i class="fas fa-calendar mr-1"></i>
                                Assigned: <?php echo date('d M Y', strtotime($category['assigned_date'])); ?>
                              </span>
                            </div>
                            
                            <div class="mt-3">
                              <a href="view_category_products.php?category_id=<?php echo $category['id']; ?>" 
                                 class="btn btn-sm btn-primary">
                                <i class="fas fa-eye mr-1"></i> View Products
                              </a>
                              <a href="edit_category_products.php?category_id=<?php echo $category['id']; ?>" 
                                 class="btn btn-sm btn-success">
                                <i class="fas fa-edit mr-1"></i> Manage Products
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  </div>
                <?php else: ?>
                  <div class="alert alert-info text-center">
                    <h4><i class="icon fas fa-info"></i> No Categories Assigned!</h4>
                    <p>You haven't been assigned any categories yet. Please contact your Super Administrator.</p>
                    <p><strong>Your Admin ID:</strong> <?php echo $admin_id; ?></p>
                    <p><strong>Your Department:</strong> <?php echo ucfirst($department); ?></p>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="card-footer">
                <div class="row">
                  <div class="col-md-6">
                    <strong>Total Assigned Categories:</strong> <?php echo $assigned_result->num_rows; ?>
                    | <strong>Admin:</strong> <?php echo $full_name; ?>
                    | <strong>Department:</strong> <?php echo ucfirst($department); ?>
                  </div>
                  <div class="col-md-6 text-right">
                    <a href="admin.php" class="btn btn-primary">
                      <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Category Summary Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-table mr-2"></i>Category Summary
                </h3>
              </div>
              <div class="card-body">
                <?php if ($assigned_result->num_rows > 0): ?>
                  <?php 
                  // Reset result pointer
                  $assigned_result->data_seek(0);
                  ?>
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Category Name</th>
                          <th>Description</th>
                          <th>Status</th>
                          <th>Assigned Date</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        $counter = 1;
                        while($category = $assigned_result->fetch_assoc()): 
                        ?>
                          <tr>
                            <td><?php echo $counter++; ?></td>
                            <td>
                              <i class="fas fa-folder mr-2"></i>
                              <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                            </td>
                            <td>
                              <?php 
                              if (!empty($category['category_description'])) {
                                echo htmlspecialchars(substr($category['category_description'], 0, 50));
                                if (strlen($category['category_description']) > 50) echo '...';
                              } else {
                                echo '<span class="text-muted">No description</span>';
                              }
                              ?>
                            </td>
                            <td>
                              <span class="category-badge badge-active">
                                <?php echo ucfirst($category['status']); ?>
                              </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($category['assigned_date'])); ?></td>
                            <td>
                              <a href="view_category_products.php?category_id=<?php echo $category['id']; ?>" 
                                 class="btn btn-sm btn-info" title="View Products">
                                <i class="fas fa-eye"></i>
                              </a>
                              <a href="edit_category_products.php?category_id=<?php echo $category['id']; ?>" 
                                 class="btn btn-sm btn-success" title="Manage Products">
                                <i class="fas fa-edit"></i>
                              </a>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning text-center">
                    <p>No category data available to display in table format.</p>
                  </div>
                <?php endif; ?>
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
    All rights reserved. Customized by Al Hadi.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0 | 
      <b>User:</b> <?php echo $full_name; ?> | 
      <b>Role:</b> Admin
    </div>
  </footer>
</div>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('table').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "pageLength": 10
    });
});
</script>
</body>
</html>
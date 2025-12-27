<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'super_admin') {
    header('Location: ../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
$full_name = $_SESSION['full_name'];
$admin_id = $_SESSION['admin_id'];
$department = $_SESSION['department'] ?? '';

// Handle category deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Check if category has any products
    $check_query = "SELECT COUNT(*) as product_count FROM products WHERE category_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['product_count'] > 0) {
        $error = "Cannot delete category. It has " . $row['product_count'] . " product(s) assigned.";
    } else {
        // Delete category
        $delete_query = "DELETE FROM categories WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $delete_id);
        
        if ($delete_stmt->execute()) {
            $success = "Category deleted successfully!";
        } else {
            $error = "Error deleting category: " . $conn->error;
        }
    }
}

// Handle status update
if (isset($_GET['toggle_status'])) {
    $toggle_id = intval($_GET['toggle_status']);
    
    // Get current status
    $status_query = "SELECT status FROM categories WHERE id = ?";
    $status_stmt = $conn->prepare($status_query);
    $status_stmt->bind_param("i", $toggle_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    
    if ($status_result->num_rows > 0) {
        $row = $status_result->fetch_assoc();
        $new_status = ($row['status'] == 'active') ? 'inactive' : 'active';
        
        // Update status
        $update_query = "UPDATE categories SET status = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_status, $toggle_id);
        
        if ($update_stmt->execute()) {
            $success = "Category status updated successfully!";
        } else {
            $error = "Error updating category status: " . $conn->error;
        }
    }
}

// Fetch all categories with creator info
$query = "SELECT c.*, u.full_name as creator_name 
          FROM categories c 
          LEFT JOIN users u ON c.created_by = u.id 
          ORDER BY c.created_at DESC";
$result = $conn->query($query);

// Count statistics
$stats_query = "SELECT 
                COUNT(*) as total_categories,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_categories,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_categories
                FROM categories";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Categories - Stock Management</title>
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
        <a href="super_admin.php" class="nav-link">Home</a>
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
          <a href="#" class="dropdown-item">
            <i class="fas fa-cog mr-2"></i> Settings
          </a>
          <div class="dropdown-divider"></div>
          <a href="super_admin.php?logout=1" class="dropdown-item">
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
    <a href="super_admin.php" class="brand-link">
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
          <li class="nav-item">
            <a href="super_admin.php" class="nav-link">
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
                <a href="add_category.php" class="nav-link">
                  <i class="fa fa-plus nav-icon"></i>
                  <p>Add Category</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="manage_category.php" class="nav-link active">
                  <i class="fa fa-cog nav-icon"></i>
                  <p>Manage Categories</p>
                </a>
              </li>
            </ul> 
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
            </ul> 
          </li>
          
          <!-- Logout -->
          <li class="nav-item">
            <a href="super_admin.php?logout=1" class="nav-link">
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
            <h1 class="m-0">Manage Categories</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="super_admin.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Categories</a></li>
              <li class="breadcrumb-item active">Manage Categories</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Display Messages -->
        <?php if (isset($error)): ?>
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            <?php echo $error; ?>
          </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            <?php echo $success; ?>
          </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info stats-card">
              <div class="inner">
                <h3><?php echo $stats['total_categories'] ?? 0; ?></h3>
                <p>Total Categories</p>
              </div>
              <div class="icon">
                <i class="fas fa-archive"></i>
              </div>
              <a href="#" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success stats-card">
              <div class="inner">
                <h3><?php echo $stats['active_categories'] ?? 0; ?></h3>
                <p>Active Categories</p>
              </div>
              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <a href="#" class="small-box-footer">View Active <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning stats-card">
              <div class="inner">
                <h3><?php echo $stats['inactive_categories'] ?? 0; ?></h3>
                <p>Inactive Categories</p>
              </div>
              <div class="icon">
                <i class="fas fa-ban"></i>
              </div>
              <a href="#" class="small-box-footer">View Inactive <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-primary stats-card">
              <div class="inner">
                <h3><?php echo date('M Y'); ?></h3>
                <p>Current Month</p>
              </div>
              <div class="icon">
                <i class="fas fa-calendar"></i>
              </div>
              <a href="add_category.php" class="small-box-footer">Add New <i class="fas fa-plus-circle"></i></a>
            </div>
          </div>
        </div>

        <!-- Categories Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-list-alt mr-2"></i>All Categories
                </h3>
                <div class="card-tools">
                  <a href="add_category.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Category
                  </a>
                </div>
              </div>
              
              <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Category Name</th>
                          <th>Description</th>
                          <th>Status</th>
                          <th>Created By</th>
                          <th>Created Date</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        $counter = 1;
                        while ($row = $result->fetch_assoc()): 
                        ?>
                          <tr>
                            <td><?php echo $counter++; ?></td>
                            <td>
                              <i class="fas fa-folder category-icon-small"></i>
                              <strong><?php echo htmlspecialchars($row['category_name']); ?></strong>
                            </td>
                            <td>
                              <?php 
                              if (!empty($row['category_description'])) {
                                echo htmlspecialchars(substr($row['category_description'], 0, 50));
                                if (strlen($row['category_description']) > 50) echo '...';
                              } else {
                                echo '<span class="text-muted">No description</span>';
                              }
                              ?>
                            </td>
                            <td>
                              <span class="category-badge <?php echo $row['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                              </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['creator_name'] ?? 'System'); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td class="action-buttons">
                              <a href="edit_category.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                <i class="fas fa-edit"></i>
                              </a>
                              <a href="manage_categories.php?toggle_status=<?php echo $row['id']; ?>" 
                                 class="btn btn-sm <?php echo $row['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?>" 
                                 title="<?php echo $row['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                <i class="fas <?php echo $row['status'] == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                              </a>
                              <a href="manage_categories.php?delete_id=<?php echo $row['id']; ?>" 
                                 class="btn btn-sm btn-danger" 
                                 title="Delete"
                                 onclick="return confirm('Are you sure you want to delete this category?')">
                                <i class="fas fa-trash"></i>
                              </a>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-info text-center">
                    <h4><i class="icon fas fa-info"></i> No Categories Found!</h4>
                    <p>You haven't created any categories yet. Start by adding your first category.</p>
                    <a href="add_category.php" class="btn btn-primary">
                      <i class="fas fa-plus"></i> Add First Category
                    </a>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="card-footer">
                <div class="row">
                  <div class="col-md-6">
                    <strong>Total Categories:</strong> <?php echo $stats['total_categories'] ?? 0; ?>
                    | <strong>Active:</strong> <?php echo $stats['active_categories'] ?? 0; ?>
                    | <strong>Inactive:</strong> <?php echo $stats['inactive_categories'] ?? 0; ?>
                  </div>
                  <div class="col-md-6 text-right">
                    <a href="add_category.php" class="btn btn-primary">
                      <i class="fas fa-plus"></i> Add New Category
                    </a>
                  </div>
                </div>
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
      <b>Version</b> 1.0.0 | <b>User:</b> <?php echo $full_name; ?>
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
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
</body>
</html>
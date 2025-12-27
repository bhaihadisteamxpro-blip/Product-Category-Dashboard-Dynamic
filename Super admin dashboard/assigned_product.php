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

// Handle product assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_product'])) {
    $admin_id_assign = intval($_POST['admin_id']);
    $product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
    
    if ($admin_id_assign > 0 && !empty($product_ids)) {
        // First, delete existing assignments for this admin
        $delete_query = "DELETE FROM admin_products WHERE admin_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $admin_id_assign);
        $delete_stmt->execute();
        
        // Insert new assignments
        $insert_query = "INSERT INTO admin_products (admin_id, product_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        
        $success_count = 0;
        foreach ($product_ids as $product_id) {
            $product_id = intval($product_id);
            if ($product_id > 0) {
                $insert_stmt->bind_param("ii", $admin_id_assign, $product_id);
                if ($insert_stmt->execute()) {
                    $success_count++;
                }
            }
        }
        
        if ($success_count > 0) {
            $success = "$success_count products assigned successfully to admin!";
        } else {
            $error = "Failed to assign products.";
        }
    } else {
        $error = "Please select an admin and at least one product.";
    }
}

// Handle bulk product assignment by category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_by_category'])) {
    $admin_id_assign = intval($_POST['admin_id']);
    $category_id = intval($_POST['category_id']);
    
    if ($admin_id_assign > 0 && $category_id > 0) {
        // Get all products from selected category
        $products_query = "SELECT id FROM products WHERE category_id = ? AND status = 'active'";
        $products_stmt = $conn->prepare($products_query);
        $products_stmt->bind_param("i", $category_id);
        $products_stmt->execute();
        $products_result = $products_stmt->get_result();
        
        // Delete existing assignments for this admin
        $delete_query = "DELETE FROM admin_products WHERE admin_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $admin_id_assign);
        $delete_stmt->execute();
        
        // Insert new assignments from category
        $insert_query = "INSERT INTO admin_products (admin_id, product_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        
        $success_count = 0;
        while ($product = $products_result->fetch_assoc()) {
            $insert_stmt->bind_param("ii", $admin_id_assign, $product['id']);
            if ($insert_stmt->execute()) {
                $success_count++;
            }
        }
        
        if ($success_count > 0) {
            $success = "$success_count products from category assigned successfully to admin!";
        } else {
            $error = "No products found in selected category or failed to assign.";
        }
    } else {
        $error = "Please select an admin and a category.";
    }
}

// Remove assignment
if (isset($_GET['remove_assignment'])) {
    $assignment_id = intval($_GET['remove_assignment']);
    
    $delete_query = "DELETE FROM admin_products WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $assignment_id);
    
    if ($delete_stmt->execute()) {
        $success = "Product assignment removed successfully!";
    } else {
        $error = "Error removing assignment: " . $conn->error;
    }
}

// Fetch all admins (excluding super admins)
$admins_query = "SELECT id, full_name, admin_id, department 
                 FROM users 
                 WHERE role = 'admin' AND status = 'active' 
                 ORDER BY full_name";
$admins_result = $conn->query($admins_query);

// Fetch all products with category info
$products_query = "SELECT p.id, p.product_name, p.sku, p.price, p.quantity, p.min_stock,
                          p.status as product_status, c.category_name, c.id as category_id
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   ORDER BY p.product_name";
$products_result = $conn->query($products_query);

// Fetch all categories for bulk assignment
$categories_query = "SELECT id, category_name FROM categories WHERE status = 'active' ORDER BY category_name";
$categories_result = $conn->query($categories_query);

// Fetch assigned products for selected admin (for edit)
$assigned_products = [];
if (isset($_GET['admin_id'])) {
    $selected_admin_id = intval($_GET['admin_id']);
    $assigned_query = "SELECT product_id FROM admin_products WHERE admin_id = ?";
    $assigned_stmt = $conn->prepare($assigned_query);
    $assigned_stmt->bind_param("i", $selected_admin_id);
    $assigned_stmt->execute();
    $assigned_result = $assigned_stmt->get_result();
    
    while ($row = $assigned_result->fetch_assoc()) {
        $assigned_products[] = $row['product_id'];
    }
}

// Fetch all assignment records
$assignments_query = "SELECT ap.*, u.full_name as admin_name, u.admin_id as admin_code, 
                      p.product_name, p.sku, p.price, p.quantity,
                      c.category_name, p.status as product_status
                      FROM admin_products ap
                      JOIN users u ON ap.admin_id = u.id
                      JOIN products p ON ap.product_id = p.id
                      LEFT JOIN categories c ON p.category_id = c.id
                      ORDER BY ap.assigned_date DESC";
$assignments_result = $conn->query($assignments_query);

// Count statistics
$stats_query = "SELECT 
                COUNT(DISTINCT admin_id) as total_admins_with_products,
                COUNT(*) as total_product_assignments,
                COUNT(DISTINCT product_id) as unique_products_assigned,
                SUM(p.price * p.quantity) as total_assigned_value
                FROM admin_products ap
                JOIN products p ON ap.product_id = p.id";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Format total value
$total_assigned_value = number_format($stats['total_assigned_value'] ?? 0, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Assign Products - Super Admin Dashboard</title>
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
    
    .assignment-card {
        border: 2px solid #28a745;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .assignment-card .card-header {
        background: #28a745;
        color: white;
        border-radius: 8px 8px 0 0;
    }
    
    .bulk-assign-box {
        border: 2px solid #ffc107;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        background: #fff8e1;
    }
    
    .product-checkbox {
        margin-right: 10px;
    }
    
    .product-item {
        padding: 10px;
        border: 1px solid #dee2e6;
        margin-bottom: 5px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .product-item:hover {
        background: #f8f9fa;
        border-color: #28a745;
    }
    
    .product-item.selected {
        background: #d4edda;
        border-color: #28a745;
    }
    
    .stats-card {
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .action-buttons .btn {
        margin: 2px;
    }
    
    .product-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .badge-active {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .badge-out_of_stock {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .price-tag {
        font-weight: bold;
        color: #28a745;
    }
    
    .stock-low {
        color: #dc3545;
        font-weight: bold;
    }
    
    .stock-ok {
        color: #28a745;
    }
    
    .product-icon-small {
        color: #6c757d;
        margin-right: 5px;
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
          
          <!-- User Management -->
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fa fa-users"></i>
              <p>
                User Management
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: block;">
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
                <a href="assigned_product.php" class="nav-link active">
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
            <a href="super_admin_settings.php" class="nav-link">
              <i class="nav-icon fa fa-user-shield"></i>
              <p>Super Admin Settings</p>
            </a>
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
            <h1 class="m-0">
              <i class="fas fa-boxes mr-2"></i>Assign Products to Admins
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="super_admin.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">User Management</a></li>
              <li class="breadcrumb-item active">Assign Products</li>
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
                <h3><?php echo $stats['total_admins_with_products'] ?? 0; ?></h3>
                <p>Admins with Products</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
              <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success stats-card">
              <div class="inner">
                <h3><?php echo $stats['total_product_assignments'] ?? 0; ?></h3>
                <p>Product Assignments</p>
              </div>
              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning stats-card">
              <div class="inner">
                <h3><?php echo $stats['unique_products_assigned'] ?? 0; ?></h3>
                <p>Unique Products</p>
              </div>
              <div class="icon">
                <i class="fas fa-box"></i>
              </div>
              <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-primary stats-card">
              <div class="inner">
                <h3>₹<?php echo $total_assigned_value; ?></h3>
                <p>Assigned Stock Value</p>
              </div>
              <div class="icon">
                <i class="fas fa-rupee-sign"></i>
              </div>
              <a href="#" class="small-box-footer">View Value <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Bulk Assign by Category -->
        <div class="row">
          <div class="col-md-12">
            <div class="bulk-assign-box">
              <h4><i class="fas fa-bolt mr-2"></i>Bulk Assign by Category</h4>
              <form method="POST" action="" class="form-inline">
                <div class="form-group mr-3">
                  <label for="admin_id_bulk" class="mr-2">Select Admin:</label>
                  <select class="form-control" id="admin_id_bulk" name="admin_id" required>
                    <option value="">-- Select Admin --</option>
                    <?php 
                    $admins_result->data_seek(0); // Reset pointer
                    while($admin = $admins_result->fetch_assoc()): 
                    ?>
                      <option value="<?php echo $admin['id']; ?>">
                        <?php echo htmlspecialchars($admin['full_name']); ?> 
                        (ID: <?php echo $admin['admin_id']; ?>)
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
                
                <div class="form-group mr-3">
                  <label for="category_id" class="mr-2">Select Category:</label>
                  <select class="form-control" id="category_id" name="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php 
                    $categories_result->data_seek(0); // Reset pointer
                    while($category = $categories_result->fetch_assoc()): 
                    ?>
                      <option value="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
                
                <button type="submit" name="assign_by_category" class="btn btn-warning">
                  <i class="fas fa-bolt mr-2"></i>Assign All Products from Category
                </button>
              </form>
              <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle mr-1"></i> This will assign ALL active products from the selected category to the admin.
              </small>
            </div>
          </div>
        </div>

        <!-- Individual Product Assignment -->
        <div class="row">
          <div class="col-md-12">
            <div class="card assignment-card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-exchange-alt mr-2"></i>Assign Individual Products to Admin
                </h3>
              </div>
              <div class="card-body">
                <form method="POST" action="" id="assignProductForm">
                  <div class="row">
                    <!-- Select Admin -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="admin_id">Select Admin:</label>
                        <select class="form-control" id="admin_id" name="admin_id" required 
                                onchange="window.location.href='assigned_product.php?admin_id=' + this.value">
                          <option value="">-- Select Admin --</option>
                          <?php 
                          $admins_result->data_seek(0); // Reset pointer
                          while($admin = $admins_result->fetch_assoc()): 
                          ?>
                            <option value="<?php echo $admin['id']; ?>" 
                                    <?php echo (isset($_GET['admin_id']) && $_GET['admin_id'] == $admin['id']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($admin['full_name']); ?> 
                              (ID: <?php echo $admin['admin_id']; ?>)
                              - <?php echo ucfirst($admin['department']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                    </div>
                    
                    <!-- Selected Admin Info -->
                    <div class="col-md-6">
                      <?php if (isset($_GET['admin_id'])): 
                        $admins_result->data_seek(0); // Reset pointer
                        $selected_admin = null;
                        while($admin = $admins_result->fetch_assoc()) {
                          if ($admin['id'] == $_GET['admin_id']) {
                            $selected_admin = $admin;
                            break;
                          }
                        }
                      ?>
                        <div class="alert alert-info">
                          <h6><i class="fas fa-user mr-2"></i>Selected Admin:</h6>
                          <p class="mb-1"><strong>Name:</strong> <?php echo $selected_admin['full_name']; ?></p>
                          <p class="mb-1"><strong>Admin ID:</strong> <?php echo $selected_admin['admin_id']; ?></p>
                          <p class="mb-0"><strong>Department:</strong> <?php echo ucfirst($selected_admin['department']); ?></p>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                  
                  <!-- Products Selection -->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Select Products to Assign:</label>
                        <div class="row" style="max-height: 400px; overflow-y: auto; padding: 10px;">
                          <?php 
                          if ($products_result->num_rows > 0):
                            while($product = $products_result->fetch_assoc()):
                              $stock_class = ($product['quantity'] <= $product['min_stock']) ? 'stock-low' : 'stock-ok';
                          ?>
                            <div class="col-md-6 col-lg-4 mb-2">
                              <div class="product-item <?php echo in_array($product['id'], $assigned_products) ? 'selected' : ''; ?>"
                                   onclick="toggleProduct(this, <?php echo $product['id']; ?>)">
                                <input type="checkbox" 
                                       class="product-checkbox" 
                                       id="product_<?php echo $product['id']; ?>" 
                                       name="product_ids[]" 
                                       value="<?php echo $product['id']; ?>"
                                       <?php echo in_array($product['id'], $assigned_products) ? 'checked' : ''; ?>
                                       style="display: none;">
                                <div class="d-flex justify-content-between align-items-start">
                                  <div>
                                    <i class="fas fa-box mr-2"></i>
                                    <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                    <br>
                                    <small class="text-muted">SKU: <?php echo $product['sku']; ?></small>
                                    <br>
                                    <small class="text-muted">Category: <?php echo $product['category_name'] ?? 'Uncategorized'; ?></small>
                                  </div>
                                  <div class="text-right">
                                    <span class="price-tag">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <br>
                                    <span class="<?php echo $stock_class; ?>">
                                      Stock: <?php echo $product['quantity']; ?>
                                    </span>
                                    <br>
                                    <span class="product-badge 
                                      <?php 
                                      if ($product['product_status'] == 'active') echo 'badge-active';
                                      elseif ($product['product_status'] == 'inactive') echo 'badge-inactive';
                                      else echo 'badge-out_of_stock';
                                      ?>">
                                      <?php 
                                      if ($product['product_status'] == 'out_of_stock') echo 'Out of Stock';
                                      else echo ucfirst($product['product_status']); 
                                      ?>
                                    </span>
                                  </div>
                                </div>
                              </div>
                            </div>
                          <?php 
                            endwhile;
                          else:
                          ?>
                            <div class="col-12">
                              <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                No products found. Please add products first from <a href="add_product.php">Add Product</a>.
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Submit Button -->
                  <div class="row">
                    <div class="col-md-12 text-center">
                      <button type="submit" name="assign_product" class="btn btn-success btn-lg">
                        <i class="fas fa-check-circle mr-2"></i>Assign Selected Products
                      </button>
                      <button type="button" class="btn btn-primary btn-lg" onclick="selectAllProducts()">
                        <i class="fas fa-check-square mr-2"></i>Select All
                      </button>
                      <button type="button" class="btn btn-warning btn-lg" onclick="deselectAllProducts()">
                        <i class="fas fa-times-circle mr-2"></i>Deselect All
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Current Assignments -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-history mr-2"></i>Current Product Assignments
                </h3>
                <div class="card-tools">
                  <span class="badge badge-success">
                    Total Assignments: <?php echo $stats['total_product_assignments'] ?? 0; ?>
                  </span>
                </div>
              </div>
              <div class="card-body">
                <?php if ($assignments_result->num_rows > 0): ?>
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Admin</th>
                          <th>Product</th>
                          <th>Category</th>
                          <th>Price</th>
                          <th>Stock</th>
                          <th>Status</th>
                          <th>Assigned Date</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        $counter = 1;
                        $assignments_result->data_seek(0); // Reset pointer
                        while($assignment = $assignments_result->fetch_assoc()): 
                          $stock_class = ($assignment['quantity'] <= 0) ? 'stock-low' : 'stock-ok';
                        ?>
                          <tr>
                            <td><?php echo $counter++; ?></td>
                            <td>
                              <i class="fas fa-user-circle mr-2"></i>
                              <strong><?php echo htmlspecialchars($assignment['admin_name']); ?></strong>
                              <br><small class="text-muted">ID: <?php echo $assignment['admin_code']; ?></small>
                            </td>
                            <td>
                              <i class="fas fa-box product-icon-small"></i>
                              <strong><?php echo htmlspecialchars($assignment['product_name']); ?></strong>
                              <br><small class="text-muted">SKU: <?php echo $assignment['sku']; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($assignment['category_name'] ?? 'Uncategorized'); ?></td>
                            <td class="price-tag">₹<?php echo number_format($assignment['price'], 2); ?></td>
                            <td class="<?php echo $stock_class; ?>">
                              <?php echo $assignment['quantity']; ?>
                            </td>
                            <td>
                              <span class="product-badge 
                                <?php 
                                if ($assignment['product_status'] == 'active') echo 'badge-active';
                                elseif ($assignment['product_status'] == 'inactive') echo 'badge-inactive';
                                else echo 'badge-out_of_stock';
                                ?>">
                                <?php 
                                if ($assignment['product_status'] == 'out_of_stock') echo 'Out of Stock';
                                else echo ucfirst($assignment['product_status']); 
                                ?>
                              </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($assignment['assigned_date'])); ?></td>
                            <td class="action-buttons">
                              <a href="assigned_product.php?admin_id=<?php echo $assignment['admin_id']; ?>" 
                                 class="btn btn-sm btn-info" title="Edit Assignment">
                                <i class="fas fa-edit"></i>
                              </a>
                              <a href="assigned_product.php?remove_assignment=<?php echo $assignment['id']; ?>" 
                                 class="btn btn-sm btn-danger" title="Remove Assignment"
                                 onclick="return confirm('Are you sure you want to remove this product assignment?')">
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
                    <h4><i class="icon fas fa-info"></i> No Product Assignments Found!</h4>
                    <p>No products have been assigned to any admin yet. Use the form above to assign products.</p>
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
// Function to toggle product selection
function toggleProduct(element, productId) {
  const checkbox = element.querySelector('.product-checkbox');
  checkbox.checked = !checkbox.checked;
  
  if (checkbox.checked) {
    element.classList.add('selected');
  } else {
    element.classList.remove('selected');
  }
}

// Function to select all products
function selectAllProducts() {
  document.querySelectorAll('.product-checkbox').forEach(checkbox => {
    checkbox.checked = true;
    if (checkbox.parentElement) {
      checkbox.parentElement.classList.add('selected');
    }
  });
}

// Function to deselect all products
function deselectAllProducts() {
  document.querySelectorAll('.product-checkbox').forEach(checkbox => {
    checkbox.checked = false;
    if (checkbox.parentElement) {
      checkbox.parentElement.classList.remove('selected');
    }
  });
}

// Initialize DataTable
$(document).ready(function() {
  $('table').DataTable({
    "paging": true,
    "lengthChange": true,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "pageLength": 10,
    "order": [[0, 'desc']]
  });
  
  // Auto-hide alerts after 5 seconds
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);
});
</script>
</body>
</html>
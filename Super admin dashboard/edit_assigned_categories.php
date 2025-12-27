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

// Handle product status update within assigned categories
if (isset($_GET['toggle_product_status'])) {
    $product_id = intval($_GET['toggle_product_status']);
    
    // Verify if product belongs to admin's assigned categories
    $verify_query = "SELECT p.* FROM products p
                     INNER JOIN admin_categories ac ON p.category_id = ac.category_id
                     WHERE p.id = ? AND ac.admin_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $product_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $product = $verify_result->fetch_assoc();
        $new_status = ($product['status'] == 'active') ? 'inactive' : 'active';
        
        // Update product status
        $update_query = "UPDATE products SET status = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_status, $product_id);
        
        if ($update_stmt->execute()) {
            $success = "Product status updated successfully!";
        } else {
            $error = "Error updating product status: " . $conn->error;
        }
    } else {
        $error = "You don't have permission to modify this product.";
    }
}

// Handle product stock update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $product_id = intval($_POST['product_id']);
    $new_quantity = intval($_POST['new_quantity']);
    
    // Verify if product belongs to admin's assigned categories
    $verify_query = "SELECT p.* FROM products p
                     INNER JOIN admin_categories ac ON p.category_id = ac.category_id
                     WHERE p.id = ? AND ac.admin_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $product_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        // Update product stock
        $update_query = "UPDATE products SET quantity = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $new_quantity, $product_id);
        
        if ($update_stmt->execute()) {
            $success = "Product stock updated successfully!";
        } else {
            $error = "Error updating product stock: " . $conn->error;
        }
    } else {
        $error = "You don't have permission to modify this product.";
    }
}

// Fetch assigned categories for current admin
$assigned_categories_query = "SELECT c.id, c.category_name, c.category_description, c.status, 
                                     COUNT(p.id) as product_count
                              FROM categories c
                              INNER JOIN admin_categories ac ON c.id = ac.category_id
                              LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                              WHERE ac.admin_id = ? AND c.status = 'active'
                              GROUP BY c.id
                              ORDER BY c.category_name";
$assigned_stmt = $conn->prepare($assigned_categories_query);
$assigned_stmt->bind_param("i", $user_id);
$assigned_stmt->execute();
$assigned_result = $assigned_stmt->get_result();

// Get selected category products
$selected_category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$category_products = [];

if ($selected_category_id > 0) {
    // Verify if category is assigned to admin
    $verify_category_query = "SELECT c.category_name FROM categories c
                              INNER JOIN admin_categories ac ON c.id = ac.category_id
                              WHERE c.id = ? AND ac.admin_id = ?";
    $verify_category_stmt = $conn->prepare($verify_category_query);
    $verify_category_stmt->bind_param("ii", $selected_category_id, $user_id);
    $verify_category_stmt->execute();
    $verify_category_result = $verify_category_stmt->get_result();
    
    if ($verify_category_result->num_rows > 0) {
        $category_info = $verify_category_result->fetch_assoc();
        $selected_category_name = $category_info['category_name'];
        
        // Fetch products for this category
        $products_query = "SELECT p.* FROM products p
                           WHERE p.category_id = ? AND p.status != 'deleted'
                           ORDER BY p.product_name";
        $products_stmt = $conn->prepare($products_query);
        $products_stmt->bind_param("i", $selected_category_id);
        $products_stmt->execute();
        $category_products_result = $products_stmt->get_result();
        
        while ($product = $category_products_result->fetch_assoc()) {
            $category_products[] = $product;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Assigned Categories - Admin Dashboard</title>
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
    
    .category-list {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .category-item {
        padding: 15px;
        border: 1px solid #dee2e6;
        margin-bottom: 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .category-item:hover {
        background: #f8f9fa;
        border-color: #007bff;
    }
    
    .category-item.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .product-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        margin-right: 5px;
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
    
    .stock-low {
        color: #dc3545;
        font-weight: bold;
    }
    
    .stock-ok {
        color: #28a745;
    }
    
    .price-tag {
        color: #28a745;
        font-weight: bold;
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
                <a href="view_assigned_categories.php" class="nav-link">
                  <i class="fas fa-list nav-icon"></i>
                  <p>View Assigned Categories</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="edit_assigned_categories.php" class="nav-link active">
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
              <i class="fas fa-edit mr-2"></i>Edit Assigned Categories
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Categories</a></li>
              <li class="breadcrumb-item active">Edit Assigned Categories</li>
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

        <div class="row">
          <!-- Categories List -->
          <div class="col-md-4">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-folder mr-2"></i>Your Assigned Categories
                </h3>
              </div>
              <div class="card-body category-list">
                <?php if ($assigned_result->num_rows > 0): ?>
                  <?php while($category = $assigned_result->fetch_assoc()): ?>
                    <div class="category-item <?php echo ($selected_category_id == $category['id']) ? 'active' : ''; ?>"
                         onclick="window.location.href='edit_assigned_categories.php?category_id=<?php echo $category['id']; ?>'">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <i class="fas fa-archive mr-2"></i>
                          <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                        </div>
                        <span class="badge badge-info"><?php echo $category['product_count']; ?> products</span>
                      </div>
                      <?php if (!empty($category['category_description'])): ?>
                        <small class="text-muted"><?php echo htmlspecialchars(substr($category['category_description'], 0, 50)); ?>...</small>
                      <?php endif; ?>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No categories assigned to you yet.
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Products in Selected Category -->
          <div class="col-md-8">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-boxes mr-2"></i>
                  <?php if ($selected_category_id > 0 && isset($selected_category_name)): ?>
                    Products in: <?php echo htmlspecialchars($selected_category_name); ?>
                  <?php else: ?>
                    Select a Category to View Products
                  <?php endif; ?>
                </h3>
              </div>
              <div class="card-body">
                <?php if ($selected_category_id > 0): ?>
                  <?php if (!empty($category_products)): ?>
                    <div class="table-responsive">
                      <table class="table table-bordered table-hover">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          $counter = 1;
                          foreach ($category_products as $product): 
                            $stock_class = ($product['quantity'] <= $product['min_stock']) ? 'stock-low' : 'stock-ok';
                          ?>
                            <tr>
                              <td><?php echo $counter++; ?></td>
                              <td>
                                <i class="fas fa-box product-icon-small"></i>
                                <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                              </td>
                              <td><code><?php echo $product['sku']; ?></code></td>
                              <td class="price-tag">₹<?php echo number_format($product['price'], 2); ?></td>
                              <td class="<?php echo $stock_class; ?>">
                                <?php echo $product['quantity']; ?> <?php echo $product['unit']; ?>
                                <?php if ($product['quantity'] <= $product['min_stock']): ?>
                                  <br><small><i class="fas fa-exclamation-triangle"></i> Low stock</small>
                                <?php endif; ?>
                              </td>
                              <td>
                                <span class="product-badge 
                                  <?php 
                                  if ($product['status'] == 'active') echo 'badge-active';
                                  elseif ($product['status'] == 'inactive') echo 'badge-inactive';
                                  else echo 'badge-out_of_stock';
                                  ?>">
                                  <?php 
                                  if ($product['status'] == 'out_of_stock') echo 'Out of Stock';
                                  else echo ucfirst($product['status']); 
                                  ?>
                                </span>
                              </td>
                              <td>
                                <!-- Stock Update Form -->
                                <form method="POST" action="" class="form-inline mb-2" style="display: inline-block;">
                                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                  <div class="input-group input-group-sm">
                                    <input type="number" name="new_quantity" value="<?php echo $product['quantity']; ?>" 
                                           class="form-control" style="width: 70px;" min="0" required>
                                    <div class="input-group-append">
                                      <button type="submit" name="update_stock" class="btn btn-sm btn-success">
                                        <i class="fas fa-save"></i>
                                      </button>
                                    </div>
                                  </div>
                                </form>
                                
                                <!-- Status Toggle Button -->
                                <a href="edit_assigned_categories.php?category_id=<?php echo $selected_category_id; ?>&toggle_product_status=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm <?php echo $product['status'] == 'active' ? 'btn-warning' : 'btn-primary'; ?>" 
                                   title="<?php echo $product['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                  <i class="fas <?php echo $product['status'] == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                                </a>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <div class="alert alert-info text-center">
                      <h4><i class="icon fas fa-info"></i> No Products Found!</h4>
                      <p>This category doesn't have any products yet.</p>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="alert alert-info text-center">
                    <h4><i class="icon fas fa-info"></i> Select a Category</h4>
                    <p>Please select a category from the left panel to view and edit its products.</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Quick Stats for Selected Category -->
            <?php if ($selected_category_id > 0 && !empty($category_products)): ?>
              <div class="row mt-3">
                <?php 
                $active_count = 0;
                $inactive_count = 0;
                $out_of_stock_count = 0;
                $total_stock = 0;
                $total_value = 0;
                
                foreach ($category_products as $product) {
                  if ($product['status'] == 'active') $active_count++;
                  elseif ($product['status'] == 'inactive') $inactive_count++;
                  elseif ($product['status'] == 'out_of_stock') $out_of_stock_count++;
                  
                  $total_stock += $product['quantity'];
                  $total_value += $product['price'] * $product['quantity'];
                }
                ?>
                <div class="col-md-3 col-6">
                  <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-box"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Total Products</span>
                      <span class="info-box-number"><?php echo count($category_products); ?></span>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 col-6">
                  <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Active</span>
                      <span class="info-box-number"><?php echo $active_count; ?></span>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 col-6">
                  <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Out of Stock</span>
                      <span class="info-box-number"><?php echo $out_of_stock_count; ?></span>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 col-6">
                  <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-rupee-sign"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Total Value</span>
                      <span class="info-box-number">₹<?php echo number_format($total_value, 2); ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
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
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
</body>
</html>
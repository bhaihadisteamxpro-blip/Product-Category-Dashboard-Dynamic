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
$created_by = $_SESSION['user_id'];

$error = '';
$success = '';

// Fetch categories for dropdown
$categories_query = "SELECT id, category_name FROM categories WHERE status = 'active' ORDER BY category_name";
$categories_result = $conn->query($categories_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    $product_description = trim($_POST['product_description']);
    $sku = trim($_POST['sku']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $unit = $_POST['unit'];
    $min_stock = intval($_POST['min_stock']);
    $status = $_POST['status'];
    
    // Validation
    if (empty($product_name)) {
        $error = "Product name is required!";
    } elseif (strlen($product_name) < 3) {
        $error = "Product name must be at least 3 characters!";
    } elseif ($price <= 0) {
        $error = "Price must be greater than 0!";
    } elseif ($quantity < 0) {
        $error = "Quantity cannot be negative!";
    } else {
        // Check if product SKU already exists
        if (!empty($sku)) {
            $check_query = "SELECT id FROM products WHERE sku = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("s", $sku);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "SKU already exists! Please use a unique SKU.";
            }
        }
        
        if (empty($error)) {
            // Generate SKU if not provided
            if (empty($sku)) {
                $sku = 'PROD' . date('Ymd') . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            }
            
            // Insert into database
            $insert_query = "INSERT INTO products (product_name, category_id, product_description, sku, price, quantity, unit, min_stock, status, created_by, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sisssdissi", $product_name, $category_id, $product_description, $sku, $price, $quantity, $unit, $min_stock, $status, $created_by);
            
            if ($insert_stmt->execute()) {
                $success = "Product added successfully!";
                // Clear form fields
                $product_name = $product_description = $sku = '';
                $price = $quantity = $min_stock = 0;
                $unit = 'pcs';
                $status = 'active';
            } else {
                $error = "Error adding product: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Product - Stock Management</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
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
          
          <!-- Products -->
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fa fa-box"></i>
              <p>
                Products
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: block;">
              <li class="nav-item">
                <a href="add_product.php" class="nav-link active">
                  <i class="fa fa-plus nav-icon"></i>
                  <p>Add Product</p>
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
                <a href="manage_categories.php" class="nav-link">
                  <i class="fa fa-cog nav-icon"></i>
                  <p>Manage Categories</p>
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
            <h1 class="m-0">Add New Product</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="super_admin.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Products</a></li>
              <li class="breadcrumb-item active">Add Product</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Display Messages -->
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            <?php echo $error; ?>
          </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            <?php echo $success; ?>
          </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-md-10 mx-auto">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box"></i> Product Information</h3>
              </div>
              <!-- form start -->
              <form method="POST" action="" id="addProductForm">
                <div class="card-body">
                  <div class="text-center mb-4">
                    <div class="product-icon">
                      <i class="fas fa-box-open"></i>
                    </div>
                    <h4>Create New Product</h4>
                    <p class="text-muted">Add products to your inventory with complete details</p>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="product_name" class="required">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" 
                               value="<?php echo isset($product_name) ? htmlspecialchars($product_name) : ''; ?>" 
                               placeholder="Enter product name" required>
                        <small class="form-text text-muted">Full name of the product (3-200 characters)</small>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="category_id" class="required">Category</label>
                        <select class="form-control select2" id="category_id" name="category_id" required style="width: 100%;">
                          <option value="">Select Category</option>
                          <?php while ($category = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                        <small class="form-text text-muted">Choose appropriate category for the product</small>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="product_description">Description</label>
                    <textarea class="form-control" id="product_description" name="product_description" 
                              rows="3" placeholder="Enter product description (optional)"><?php echo isset($product_description) ? htmlspecialchars($product_description) : ''; ?></textarea>
                    <small class="form-text text-muted">Detailed description about this product</small>
                  </div>

                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="sku">SKU (Stock Keeping Unit)</label>
                        <input type="text" class="form-control" id="sku" name="sku" 
                               value="<?php echo isset($sku) ? htmlspecialchars($sku) : ''; ?>" 
                               placeholder="Leave blank to auto-generate">
                        <small class="form-text text-muted">Unique product identifier (auto-generated if empty)</small>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-group price-input">
                        <label for="price" class="required">Price</label>
                        <input type="number" class="form-control" id="price" name="price" 
                               value="<?php echo isset($price) ? $price : ''; ?>" 
                               placeholder="0.00" step="0.01" min="0.01" required>
                        <small class="form-text text-muted">Price per unit (in ₹)</small>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="unit" class="required">Unit</label>
                        <select class="form-control select2" id="unit" name="unit" required style="width: 100%;">
                          <option value="pcs" <?php echo (isset($unit) && $unit == 'pcs') ? 'selected' : 'selected'; ?>>Pieces</option>
                          <option value="kg" <?php echo (isset($unit) && $unit == 'kg') ? 'selected' : ''; ?>>Kilogram</option>
                          <option value="g" <?php echo (isset($unit) && $unit == 'g') ? 'selected' : ''; ?>>Gram</option>
                          <option value="l" <?php echo (isset($unit) && $unit == 'l') ? 'selected' : ''; ?>>Liter</option>
                          <option value="ml" <?php echo (isset($unit) && $unit == 'ml') ? 'selected' : ''; ?>>Milliliter</option>
                          <option value="m" <?php echo (isset($unit) && $unit == 'm') ? 'selected' : ''; ?>>Meter</option>
                          <option value="cm" <?php echo (isset($unit) && $unit == 'cm') ? 'selected' : ''; ?>>Centimeter</option>
                          <option value="box" <?php echo (isset($unit) && $unit == 'box') ? 'selected' : ''; ?>>Box</option>
                          <option value="pack" <?php echo (isset($unit) && $unit == 'pack') ? 'selected' : ''; ?>>Pack</option>
                        </select>
                        <small class="form-text text-muted">Unit of measurement for this product</small>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="quantity" class="required">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               value="<?php echo isset($quantity) ? $quantity : 0; ?>" 
                               placeholder="0" min="0" required>
                        <small class="form-text text-muted">Current stock quantity</small>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="min_stock">Minimum Stock Level</label>
                        <input type="number" class="form-control" id="min_stock" name="min_stock" 
                               value="<?php echo isset($min_stock) ? $min_stock : 10; ?>" 
                               placeholder="10" min="0">
                        <small class="form-text text-muted">Alert when stock goes below this level</small>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="status" class="required">Status</label>
                        <select class="form-control select2" id="status" name="status" required style="width: 100%;">
                          <option value="active" <?php echo (isset($status) && $status == 'active') ? 'selected' : 'selected'; ?>>Active</option>
                          <option value="inactive" <?php echo (isset($status) && $status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                          <option value="out_of_stock" <?php echo (isset($status) && $status == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                        <small class="form-text text-muted">Product availability status</small>
                      </div>
                    </div>
                  </div>

                  <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Quick Tips</h5>
                    <ul class="mb-0">
                      <li>Use descriptive product names for easy identification</li>
                      <li>Assign correct category for better organization</li>
                      <li>Set minimum stock level to get alerts when stock is low</li>
                      <li>Keep product descriptions clear and concise</li>
                      <li>Update stock quantity regularly for accurate inventory</li>
                    </ul>
                  </div>
                </div>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Product
                  </button>
                  <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset Form
                  </button>
                  <a href="manage_products.php" class="btn btn-default float-right">
                    <i class="fas fa-list"></i> View All Products
                  </a>
                </div>
              </form>
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
<!-- Select2 -->
<script src="plugins/select2/js/select2.full.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        minimumResultsForSearch: -1
    });
    
    // Product name validation
    $('#product_name').on('keyup', function() {
        var product_name = $(this).val();
        if (product_name.length >= 3 && product_name.length <= 200) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Price validation
    $('#price').on('keyup', function() {
        var price = parseFloat($(this).val());
        if (price > 0) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Quantity validation
    $('#quantity').on('keyup', function() {
        var quantity = parseInt($(this).val());
        if (quantity >= 0) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Form submission validation
    $('#addProductForm').on('submit', function(e) {
        var product_name = $('#product_name').val();
        var price = parseFloat($('#price').val());
        var quantity = parseInt($('#quantity').val());
        
        if (product_name.length < 3 || product_name.length > 200) {
            e.preventDefault();
            alert('Product name must be between 3 and 200 characters.');
            return false;
        }
        
        if (price <= 0) {
            e.preventDefault();
            alert('Price must be greater than 0.');
            return false;
        }
        
        if (quantity < 0) {
            e.preventDefault();
            alert('Quantity cannot be negative.');
            return false;
        }
        
        return true;
    });
    
    // Auto-generate SKU suggestion
    $('#product_name').on('blur', function() {
        if ($('#sku').val() === '') {
            var product_name = $(this).val().toUpperCase();
            var sku_suggestion = product_name.replace(/[^A-Z0-9]/g, '').substring(0, 8);
            if (sku_suggestion.length > 3) {
                $('#sku').val(sku_suggestion + Math.floor(Math.random() * 1000));
            }
        }
    });
});
</script>
</body>
</html>
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name']);
    $category_description = trim($_POST['category_description']);
    $status = $_POST['status'];
    
    // Validation
    if (empty($category_name)) {
        $error = "Category name is required!";
    } elseif (strlen($category_name) < 3) {
        $error = "Category name must be at least 3 characters!";
    } else {
        // Check if category already exists
        $check_query = "SELECT id FROM categories WHERE category_name = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $category_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Category name already exists!";
        } else {
            // Insert into database
            $insert_query = "INSERT INTO categories (category_name, category_description, status, created_by, created_at) 
                            VALUES (?, ?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssi", $category_name, $category_description, $status, $created_by);
            
            if ($insert_stmt->execute()) {
                $success = "Category added successfully!";
                // Clear form fields
                $category_name = $category_description = '';
                $status = 'active';
            } else {
                $error = "Error adding category: " . $conn->error;
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
  <title>Add Category - Stock Management</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
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
          <li class="nav-item">
            <a href="superadmin.php" class="nav-link">
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
                <a href="add_category.php" class="nav-link active">
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
            <h1 class="m-0">Add New Category</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="superadmin.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Categories</a></li>
              <li class="breadcrumb-item active">Add Category</li>
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
          <div class="col-md-8 mx-auto">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-archive"></i> Category Information</h3>
              </div>
              <!-- form start -->
              <form method="POST" action="" id="addCategoryForm">
                <div class="card-body">
                  <div class="text-center mb-4">
                    <div class="category-icon">
                      <i class="fas fa-folder-plus"></i>
                    </div>
                    <h4>Create New Category</h4>
                    <p class="text-muted">Categories help organize your products systematically</p>
                  </div>

                  <div class="form-group">
                    <label for="category_name" class="required">Category Name</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" 
                           value="<?php echo isset($category_name) ? htmlspecialchars($category_name) : ''; ?>" 
                           placeholder="Enter category name (e.g., Electronics, Clothing)" required>
                    <small class="form-text text-muted">Unique name for the category (3-100 characters)</small>
                  </div>

                  <div class="form-group">
                    <label for="category_description">Description</label>
                    <textarea class="form-control" id="category_description" name="category_description" 
                              rows="3" placeholder="Enter category description (optional)"><?php echo isset($category_description) ? htmlspecialchars($category_description) : ''; ?></textarea>
                    <small class="form-text text-muted">Brief description about this category</small>
                  </div>

                  <div class="form-group">
                    <label for="status" class="required">Status</label>
                    <select class="form-control select2" id="status" name="status" required style="width: 100%;">
                      <option value="active" <?php echo (isset($status) && $status == 'active') ? 'selected' : 'selected'; ?>>Active</option>
                      <option value="inactive" <?php echo (isset($status) && $status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <small class="form-text text-muted">Active categories will be available for product assignment</small>
                  </div>

                  <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Quick Tips</h5>
                    <ul class="mb-0">
                      <li>Use clear and descriptive category names</li>
                      <li>Keep category names short but meaningful</li>
                      <li>Avoid duplicate category names</li>
                      <li>Set status to inactive if you want to hide the category temporarily</li>
                    </ul>
                  </div>
                </div>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Category
                  </button>
                  <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset Form
                  </button>
                  <a href="manage_categories.php" class="btn btn-default float-right">
                    <i class="fas fa-list"></i> View All Categories
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
<script src="../assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="../assets/plugins/select2/js/select2.full.min.js"></script>
<!-- AdminLTE App -->
<script src="../assets/dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        minimumResultsForSearch: -1
    });
    
    // Category name validation
    $('#category_name').on('keyup', function() {
        var category_name = $(this).val();
        if (category_name.length >= 3 && category_name.length <= 100) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Form submission validation
    $('#addCategoryForm').on('submit', function(e) {
        var category_name = $('#category_name').val();
        
        if (category_name.length < 3 || category_name.length > 100) {
            e.preventDefault();
            alert('Category name must be between 3 and 100 characters.');
            return false;
        }
        
        return true;
    });
});
</script>
</body>
</html>









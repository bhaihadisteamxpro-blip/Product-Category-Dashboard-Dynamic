<?php
session_start();
require_once 'config/database.php'; // Database connection file

// Check if super admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
    header("Location: ../frontend/login.php");
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $department = $_POST['department'];
    $role = 'admin'; // Default role for new admins
    
    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } else {
        // Check if username or email already exists
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate unique admin ID
            $admin_id = 'ADM' . date('Ymd') . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            
            // Insert into database
            $insert_query = "INSERT INTO users (admin_id, full_name, username, email, password, phone, department, role, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssssssss", $admin_id, $full_name, $username, $email, $hashed_password, $phone, $department, $role);
            
            if ($insert_stmt->execute()) {
                $new_msg_id = $conn->insert_id; // Capture standard ID
                
                // Handle Categories Assignment
                if (isset($_POST['category_ids']) && is_array($_POST['category_ids'])) {
                    $assign_cat_sql = "INSERT INTO admin_categories (admin_id, category_id) VALUES (?, ?)";
                    $assign_cat_stmt = $conn->prepare($assign_cat_sql);
                    foreach ($_POST['category_ids'] as $cat_id) {
                        $cat_id = intval($cat_id);
                        $assign_cat_stmt->bind_param("ii", $new_msg_id, $cat_id);
                        $assign_cat_stmt->execute();
                    }
                }

                // Handle Products Assignment
                if (isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
                    $assign_prod_sql = "INSERT INTO admin_products (admin_id, product_id) VALUES (?, ?)";
                    $assign_prod_stmt = $conn->prepare($assign_prod_sql);
                    foreach ($_POST['product_ids'] as $prod_id) {
                        $prod_id = intval($prod_id);
                        $assign_prod_stmt->bind_param("ii", $new_msg_id, $prod_id);
                        $assign_prod_stmt->execute();
                    }
                }

                $success = "Admin added successfully with assignments!";
                
                // Clear form fields
                $full_name = $username = $email = $phone = $department = '';
                
                // Optional: Send email notification to new admin
                // sendWelcomeEmail($email, $username, $password);
            } else {
                $error = "Error adding admin: " . $conn->error;
            }
        }
    }
}

// Fetch Categories and Products for Display
$cats_data = [];
$cats_q = "SELECT * FROM categories WHERE status='active'";
$cats_r = $conn->query($cats_q);
if($cats_r) {
    while($row = $cats_r->fetch_assoc()) {
        $cats_data[] = $row;
    }
}

// Organize Products by Category
$prods_by_cat = [];
$prods_q = "SELECT * FROM products WHERE status='active'";
$prods_r = $conn->query($prods_q);
if($prods_r) {
    while($row = $prods_r->fetch_assoc()) {
        $prods_by_cat[$row['category_id']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Admin - Stock Management</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="../assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="../assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="../assets/plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="../assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="../assets/plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="../assets/plugins/summernote/summernote-bs4.min.css">
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
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../assets/dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../assets/dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../assets/dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
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
            <h1 class="m-0">Add New Admin</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="superadmin.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">User Management</a></li>
              <li class="breadcrumb-item active">Add Admin</li>
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
                <h3 class="card-title">Admin Information</h3>
              </div>
              <!-- form start -->
              <form method="POST" action="" id="addAdminForm">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="full_name" class="required">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" 
                               placeholder="Enter full name" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="username" class="required">Username</label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text">@</span>
                          </div>
                          <input type="text" class="form-control" id="username" name="username" 
                                 value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                                 placeholder="Enter username" required>
                        </div>
                        <small class="form-text text-muted">Minimum 3 characters, letters and numbers only</small>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="email" class="required">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                               placeholder="Enter email" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" 
                               placeholder="Enter phone number">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="password" class="required">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter password" required>
                        <div id="password-strength" class="password-strength"></div>
                        <small class="form-text text-muted">
                          <i class="fas fa-info-circle"></i> Minimum 8 characters with at least 1 uppercase, 1 lowercase, 1 number
                        </small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="confirm_password" class="required">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm password" required>
                        <div id="password-match" class="form-text"></div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="department" class="required">Department</label>
                        <select class="form-control select2" id="department" name="department" required style="width: 100%;">
                          <option value="">Select Department</option>
                          <option value="inventory" <?php echo (isset($department) && $department == 'inventory') ? 'selected' : ''; ?>>Inventory Management</option>
                          <option value="purchase" <?php echo (isset($department) && $department == 'purchase') ? 'selected' : ''; ?>>Purchase Department</option>
                          <option value="sales" <?php echo (isset($department) && $department == 'sales') ? 'selected' : ''; ?>>Sales Department</option>
                          <option value="warehouse" <?php echo (isset($department) && $department == 'warehouse') ? 'selected' : ''; ?>>Warehouse</option>
                          <option value="finance" <?php echo (isset($department) && $department == 'finance') ? 'selected' : ''; ?>>Finance</option>
                          <option value="hr" <?php echo (isset($department) && $department == 'hr') ? 'selected' : ''; ?>>Human Resources</option>
                          <option value="management" <?php echo (isset($department) && $department == 'management') ? 'selected' : ''; ?>>Management</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Admin ID (Auto-generated)</label>
                        <input type="text" class="form-control" 
                               value="ADM<?php echo date('Ymd'); ?>XXX" 
                               readonly disabled>
                        <small class="form-text text-muted">This will be generated automatically</small>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input class="custom-control-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" checked>
                      <label for="send_welcome_email" class="custom-control-label">
                        Send welcome email with login credentials
                      </label>
                    </div>
                  </div>

                  <hr>
                  <div class="form-group">
                    <label>Assign Access (Categories & Products)</label>
                    <div class="card card-secondary card-outline">
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (!empty($cats_data)): ?>
                                <?php foreach ($cats_data as $cat): ?>
                                    <div class="category-block mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input category-checkbox" type="checkbox" 
                                                   id="cat_<?php echo $cat['id']; ?>" 
                                                   name="category_ids[]" 
                                                   value="<?php echo $cat['id']; ?>">
                                            <label for="cat_<?php echo $cat['id']; ?>" class="custom-control-label font-weight-bold">
                                                <?php echo htmlspecialchars($cat['category_name']); ?>
                                            </label>
                                        </div>
                                        
                                        <!-- Products List -->
                                        <div class="ml-4 mt-2 product-list" id="prod_list_<?php echo $cat['id']; ?>">
                                            <?php if (isset($prods_by_cat[$cat['id']])): ?>
                                                <?php foreach ($prods_by_cat[$cat['id']] as $prod): ?>
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input product-checkbox cat-prod-<?php echo $cat['id']; ?>" 
                                                               type="checkbox" 
                                                               id="prod_<?php echo $prod['id']; ?>" 
                                                               name="product_ids[]" 
                                                               value="<?php echo $prod['id']; ?>">
                                                        <label for="prod_<?php echo $prod['id']; ?>" class="custom-control-label font-weight-normal">
                                                            <?php echo htmlspecialchars($prod['product_name']); ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <small class="text-muted">No products available in this category.</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No active categories found to assign.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                  </div>
                </div>

                <!-- Add JS for Checkbox Logic -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // When a category is checked/unchecked
                    const catCheckboxes = document.querySelectorAll('.category-checkbox');
                    catCheckboxes.forEach(cb => {
                        cb.addEventListener('change', function() {
                            const catId = this.value;
                            const prodCheckboxes = document.querySelectorAll('.cat-prod-' + catId);
                            prodCheckboxes.forEach(pcb => {
                                pcb.checked = this.checked;
                            });
                        });
                    });

                    // (Optional) If all products are unchecked, uncheck category? 
                    // Or if one product is checked, check category?
                    // For now, let's keep it simple: Category Check = Select All Products.
                });
                </script>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add Admin
                  </button>
                  <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset Form
                  </button>
                  <a href="manage_admins.php" class="btn btn-default float-right">
                    <i class="fas fa-list"></i> View All Admins
                  </a>
                </div>
              </form>
            </div>

            <!-- Quick Tips Card -->
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lightbulb"></i> Quick Tips</h3>
              </div>
              <div class="card-body">
                <ul>
                  <li>Ensure the email is valid and accessible</li>
                  <li>Use a strong password with mixed characters</li>
                  <li>Assign appropriate department based on admin's role</li>
                  <li>The new admin will receive login credentials via email</li>
                  <li>You can modify admin permissions later in "Manage Admins"</li>
                </ul>
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
      <b>Version</b> 1.0.0
    </div>
  </footer>
</div>

<!-- jQuery -->
<script src="../assets/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
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
        theme: 'bootstrap4'
    });
    
    // Password strength checker
    $('#password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        var strengthBar = $('#password-strength');
        
        // Check password length
        if (password.length >= 8) strength++;
        
        // Check for mixed case
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        
        // Check for numbers
        if (password.match(/\d/)) strength++;
        
        // Check for special characters
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        
        // Update strength bar
        strengthBar.removeClass('strength-weak strength-medium strength-strong');
        if (strength === 0) {
            strengthBar.css('width', '0%');
        } else if (strength <= 2) {
            strengthBar.css('width', '33%').addClass('strength-weak');
        } else if (strength === 3) {
            strengthBar.css('width', '66%').addClass('strength-medium');
        } else {
            strengthBar.css('width', '100%').addClass('strength-strong');
        }
    });
    
    // Password match checker
    $('#confirm_password').on('keyup', function() {
        var password = $('#password').val();
        var confirmPassword = $(this).val();
        var matchIndicator = $('#password-match');
        
        if (confirmPassword === '') {
            matchIndicator.text('');
        } else if (password === confirmPassword) {
            matchIndicator.html('<span class="text-success"><i class="fas fa-check-circle"></i> Passwords match</span>');
        } else {
            matchIndicator.html('<span class="text-danger"><i class="fas fa-times-circle"></i> Passwords do not match</span>');
        }
    });
    
    // Username validation
    $('#username').on('keyup', function() {
        var username = $(this).val();
        var usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        
        if (usernameRegex.test(username)) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Email validation
    $('#email').on('keyup', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (emailRegex.test(email)) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Full name validation
    $('#full_name').on('keyup', function() {
        var full_name = $(this).val();
        if (full_name.length >= 3) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Form submission validation
    $('#addAdminForm').on('submit', function(e) {
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        
        if (!passwordRegex.test(password)) {
            e.preventDefault();
            alert('Password must be at least 8 characters with uppercase, lowercase, and numbers.');
            return false;
        }
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        return true;
    });
});
</script>
</body>
</html>









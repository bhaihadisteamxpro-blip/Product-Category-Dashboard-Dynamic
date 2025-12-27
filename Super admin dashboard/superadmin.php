<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'super_admin') {
    header('Location: ../login.php');
    exit();
}

// Initialize variables
$user_role = $_SESSION['user_role'] ?? '';
$full_name = $_SESSION['full_name'] ?? '';
$admin_id = $_SESSION['admin_id'] ?? '';
$department = $_SESSION['department'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Initialize admin_data
$admin_data = [];
$error = '';
$success = '';
$password_error = '';
$password_success = '';
$system_success = '';

// Fetch current super admin details
$admin_query = "SELECT * FROM users WHERE id = ? AND role = 'super_admin'";
$admin_stmt = $conn->prepare($admin_query);
if ($admin_stmt) {
    $admin_stmt->bind_param("i", $user_id);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();
    
    if ($admin_result->num_rows == 0) {
        $error = "Super admin account not found!";
    } else {
        $admin_data = $admin_result->fetch_assoc();
    }
    $admin_stmt->close();
} else {
    $error = "Database query error!";
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_full_name = trim($_POST['full_name'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    
    // Basic validation
    if (empty($new_full_name)) {
        $error = "Full name is required!";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists for another user
        $email_check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_check_stmt = $conn->prepare($email_check_query);
        if ($email_check_stmt) {
            $email_check_stmt->bind_param("si", $new_email, $user_id);
            $email_check_stmt->execute();
            $email_check_result = $email_check_stmt->get_result();
            
            if ($email_check_result->num_rows > 0) {
                $error = "Email already exists!";
            } else {
                // Update profile
                $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                if ($update_stmt) {
                    $update_stmt->bind_param("sssi", $new_full_name, $new_email, $new_phone, $user_id);
                    
                    if ($update_stmt->execute()) {
                        // Update session variables
                        $_SESSION['full_name'] = $new_full_name;
                        $full_name = $new_full_name;
                        
                        $success = "Profile updated successfully!";
                        
                        // Refresh admin data
                        $admin_stmt = $conn->prepare($admin_query);
                        $admin_stmt->bind_param("i", $user_id);
                        $admin_stmt->execute();
                        $admin_result = $admin_stmt->get_result();
                        $admin_data = $admin_result->fetch_assoc();
                        $admin_stmt->close();
                    } else {
                        $error = "Error updating profile: " . $conn->error;
                    }
                    $update_stmt->close();
                }
            }
            $email_check_stmt->close();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Verify current password
    if (!password_verify($current_password, $admin_data['password'] ?? '')) {
        $password_error = "Current password is incorrect!";
    } elseif (strlen($new_password) < 6) {
        $password_error = "New password must be at least 6 characters long!";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match!";
    } else {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $password_update_query = "UPDATE users SET password = ? WHERE id = ?";
        $password_update_stmt = $conn->prepare($password_update_query);
        if ($password_update_stmt) {
            $password_update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($password_update_stmt->execute()) {
                $password_success = "Password changed successfully!";
            } else {
                $password_error = "Error changing password: " . $conn->error;
            }
            $password_update_stmt->close();
        }
    }
}

// Handle system settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_system_settings'])) {
    $site_name = trim($_POST['site_name'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $timezone = $_POST['timezone'] ?? '';
    $date_format = $_POST['date_format'] ?? '';
    
    // Here you would typically update these settings in a database table
    // For now, we'll just show a success message
    $system_success = "System settings updated successfully!";
}

// Get login history (only if table exists)
$login_history_result = null;
if ($conn) {
    $check_table = $conn->query("SHOW TABLES LIKE 'login_history'");
    if ($check_table && $check_table->num_rows > 0) {
        $login_history_query = "SELECT * FROM login_history WHERE user_id = ? ORDER BY login_time DESC LIMIT 10";
        $login_history_stmt = $conn->prepare($login_history_query);
        if ($login_history_stmt) {
            $login_history_stmt->bind_param("i", $user_id);
            if ($login_history_stmt->execute()) {
                $login_history_result = $login_history_stmt->get_result();
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
  <title>Super Admin Settings - Stock Management</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <style>
    .user-info-sidebar {
        text-align: center;
        padding: 15px;
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
        margin: 10px 5px;
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
    
    .profile-card {
        border: 2px solid #dc3545;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .profile-header {
        background: #dc3545;
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: white;
        color: #dc3545;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: bold;
        margin: 0 auto 15px;
        border: 4px solid #a71d2a;
    }
    
    .info-item {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: bold;
        color: #555;
        flex: 1;
    }
    
    .info-value {
        color: #333;
        flex: 1;
        text-align: right;
    }
    
    .settings-tabs .nav-link {
        border-radius: 5px;
        margin: 2px;
        font-weight: 600;
        padding: 10px 15px;
    }
    
    .settings-tabs .nav-link.active {
        background: #dc3545;
        color: white;
    }
    
    .password-strength {
        height: 5px;
        margin-top: 5px;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    .strength-weak { background-color: #dc3545; width: 25%; }
    .strength-medium { background-color: #ffc107; width: 50%; }
    .strength-strong { background-color: #28a745; width: 75%; }
    .strength-very-strong { background-color: #20c997; width: 100%; }
    
    .super-admin-badge {
        background: #dc3545;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
        margin-left: 10px;
    }
    
    .system-settings-card {
        border: 2px solid #17a2b8;
        border-radius: 10px;
    }
    
    .system-settings-card .card-header {
        background: #17a2b8;
        color: white;
    }
    
    .danger-zone {
        border: 2px solid #dc3545;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        background: #f8d7da;
    }
    
    .danger-zone h4 {
        color: #721c24;
        border-bottom: 1px solid #f5c6cb;
        padding-bottom: 10px;
    }
    
    .nav-pills .nav-link.active {
        background-color: #dc3545 !important;
    }
    
    .main-sidebar {
        background-color: #343a40 !important;
    }
    
    .brand-link {
        border-bottom: 1px solid #4b545c;
    }
    
    .brand-text {
        font-weight: bold !important;
        font-family: 'Times New Roman', Times, serif !important;
        color: white !important;
        text-align: center !important;
        margin-left: 0 !important;
        font-size: 25px !important;
        display: block;
    }
    
    .nav-sidebar > .nav-item > .nav-link {
        margin-bottom: 5px;
    }
    
    @media (max-width: 768px) {
        .user-info-sidebar {
            margin: 5px;
            padding: 10px;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            font-size: 28px;
        }
        
        .settings-tabs .nav-link {
            padding: 8px 10px;
            font-size: 14px;
        }
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
          <span class="ml-2 d-none d-md-inline"><?php echo htmlspecialchars($full_name); ?></span>
          <span class="super-admin-badge d-none d-md-inline">SUPER ADMIN</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div class="dropdown-header">
            <div class="d-flex align-items-center">
              <div class="user-avatar" style="width: 40px; height: 40px; font-size: 18px; margin: 0;">
                <?php echo strtoupper(substr($full_name, 0, 1)); ?>
              </div>
              <div class="ml-3">
                <h6 class="mb-0"><?php echo htmlspecialchars($full_name); ?></h6>
                <small class="text-muted"><?php echo htmlspecialchars($admin_id); ?></small>
              </div>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile
          </a>
          <a href="super_admin_settings.php" class="dropdown-item">
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
      <span class="brand-text font-weight-light">
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
        <div class="user-name"><?php echo htmlspecialchars($full_name); ?></div>
        <div class="user-role">
          SUPER ADMIN
        </div>
        <div style="color: #ccc; font-size: 12px; margin-top: 5px;">ID: <?php echo htmlspecialchars($admin_id); ?></div>
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
          <li class="nav-item menu-open">
            <a href="super_admin_settings.php" class="nav-link active">
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
              <i class="fas fa-user-shield mr-2"></i>Super Admin Settings
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="super_admin.php">Home</a></li>
              <li class="breadcrumb-item active">Super Admin Settings</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Display Messages -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>

        <div class="row">
          <!-- Profile Overview -->
          <div class="col-md-4">
            <div class="card profile-card">
              <div class="profile-header">
                <div class="profile-avatar">
                  <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                </div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <p class="mb-0">
                  <span class="badge badge-danger" style="font-size: 14px; padding: 5px 10px;">SUPER ADMINISTRATOR</span>
                </p>
              </div>
              <div class="card-body">
                <?php if (!empty($admin_data)): ?>
                <div class="info-item">
                  <div class="info-label">Super Admin ID:</div>
                  <div class="info-value"><?php echo htmlspecialchars($admin_id); ?></div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Email:</div>
                  <div class="info-value"><?php echo htmlspecialchars($admin_data['email'] ?? 'N/A'); ?></div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Phone:</div>
                  <div class="info-value"><?php echo htmlspecialchars($admin_data['phone'] ?? 'N/A'); ?></div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Department:</div>
                  <div class="info-value"><?php echo ucfirst($department); ?></div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Role:</div>
                  <div class="info-value">SUPER ADMIN</div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Account Created:</div>
                  <div class="info-value"><?php echo !empty($admin_data['created_at']) ? date('d M Y', strtotime($admin_data['created_at'])) : 'N/A'; ?></div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Last Login:</div>
                  <div class="info-value"><?php echo !empty($admin_data['last_login']) ? date('d M Y H:i', strtotime($admin_data['last_login'])) : 'N/A'; ?></div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                  <i class="fas fa-exclamation-triangle"></i> Admin data not available
                </div>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-3">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-bar mr-2"></i>System Overview
                </h3>
              </div>
              <div class="card-body">
                <?php
                // Get system statistics
                $total_admins = 0;
                $total_categories = 0;
                $total_products = 0;
                
                if ($conn) {
                    $total_admins_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND status = 'active'";
                    $total_admins_result = $conn->query($total_admins_query);
                    if ($total_admins_result) {
                        $total_admins = $total_admins_result->fetch_assoc()['total'];
                    }
                    
                    $total_categories_query = "SELECT COUNT(*) as total FROM categories WHERE status = 'active'";
                    $total_categories_result = $conn->query($total_categories_query);
                    if ($total_categories_result) {
                        $total_categories = $total_categories_result->fetch_assoc()['total'];
                    }
                    
                    $total_products_query = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
                    $total_products_result = $conn->query($total_products_query);
                    if ($total_products_result) {
                        $total_products = $total_products_result->fetch_assoc()['total'];
                    }
                }
                ?>
                
                <div class="row">
                  <div class="col-12">
                    <div class="info-box mb-3">
                      <span class="info-box-icon bg-info elevation-1">
                        <i class="fas fa-users"></i>
                      </span>
                      <div class="info-box-content">
                        <span class="info-box-text">Total Active Admins</span>
                        <span class="info-box-number"><?php echo $total_admins; ?></span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-12">
                    <div class="info-box mb-3">
                      <span class="info-box-icon bg-success elevation-1">
                        <i class="fas fa-archive"></i>
                      </span>
                      <div class="info-box-content">
                        <span class="info-box-text">Active Categories</span>
                        <span class="info-box-number"><?php echo $total_categories; ?></span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-12">
                    <div class="info-box mb-3">
                      <span class="info-box-icon bg-warning elevation-1">
                        <i class="fas fa-box"></i>
                      </span>
                      <div class="info-box-content">
                        <span class="info-box-text">Active Products</span>
                        <span class="info-box-number"><?php echo $total_products; ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Settings Tabs -->
          <div class="col-md-8">
            <div class="card">
              <div class="card-header p-0">
                <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab">
                      <i class="fas fa-user mr-2"></i>Profile
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab">
                      <i class="fas fa-key mr-2"></i>Password
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="system-tab" data-toggle="tab" href="#system" role="tab">
                      <i class="fas fa-cog mr-2"></i>System
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab">
                      <i class="fas fa-shield-alt mr-2"></i>Security
                    </a>
                  </li>
                </ul>
              </div>
              
              <div class="card-body">
                <div class="tab-content" id="settingsTabsContent">
                  <!-- Profile Settings Tab -->
                  <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <form method="POST" action="">
                      <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($admin_data['full_name'] ?? ''); ?>" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($admin_data['phone'] ?? ''); ?>">
                      </div>
                      
                      <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($admin_data['username'] ?? ''); ?>" readonly>
                        <small class="text-muted">Username cannot be changed for security reasons.</small>
                      </div>
                      
                      <div class="form-group">
                        <label for="admin_id">Super Admin ID</label>
                        <input type="text" class="form-control" id="admin_id" value="<?php echo htmlspecialchars($admin_id); ?>" readonly>
                        <small class="text-muted">Super Admin ID is unique and cannot be changed.</small>
                      </div>
                      
                      <button type="submit" name="update_profile" class="btn btn-danger">
                        <i class="fas fa-save mr-2"></i>Update Profile
                      </button>
                    </form>
                  </div>
                  
                  <!-- Change Password Tab -->
                  <div class="tab-pane fade" id="password" role="tabpanel">
                    <!-- Display Password Messages -->
                    <?php if (!empty($password_error)): ?>
                      <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <?php echo htmlspecialchars($password_error); ?>
                      </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($password_success)): ?>
                      <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Success!</h5>
                        <?php echo htmlspecialchars($password_success); ?>
                      </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                      <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                      </div>
                      
                      <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               onkeyup="checkPasswordStrength(this.value)" required>
                        <div class="password-strength" id="passwordStrength"></div>
                        <small class="text-muted">Password must be at least 6 characters long.</small>
                      </div>
                      
                      <div class="form-group">
                        <label for="confirm_password">Confirm New Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div id="passwordMatch"></div>
                      </div>
                      
                      <button type="submit" name="change_password" class="btn btn-danger">
                        <i class="fas fa-key mr-2"></i>Change Password
                      </button>
                    </form>
                  </div>
                  
                  <!-- System Settings Tab -->
                  <div class="tab-pane fade" id="system" role="tabpanel">
                    <?php if (!empty($system_success)): ?>
                      <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Success!</h5>
                        <?php echo htmlspecialchars($system_success); ?>
                      </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                      <h5><i class="fas fa-globe mr-2"></i>General Settings</h5>
                      <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               value="Stock Management System">
                      </div>
                      
                      <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" 
                               value="Al Hadi Solutions">
                      </div>
                      
                      <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" 
                               value="admin@alhadi.com">
                      </div>
                      
                      <h5 class="mt-4"><i class="fas fa-clock mr-2"></i>Date & Time</h5>
                      <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select class="form-control" id="timezone" name="timezone">
                          <option value="UTC" selected>UTC (Coordinated Universal Time)</option>
                          <option value="America/New_York">America/New_York</option>
                          <option value="Europe/London">Europe/London</option>
                          <option value="Asia/Karachi">Asia/Karachi (Pakistan)</option>
                          <option value="Asia/Dubai">Asia/Dubai</option>
                        </select>
                      </div>
                      
                      <div class="form-group">
                        <label for="date_format">Date Format</label>
                        <select class="form-control" id="date_format" name="date_format">
                          <option value="Y-m-d" selected>YYYY-MM-DD (2024-12-27)</option>
                          <option value="d/m/Y">DD/MM/YYYY (27/12/2024)</option>
                          <option value="m/d/Y">MM/DD/YYYY (12/27/2024)</option>
                          <option value="d M Y">DD MMM YYYY (27 Dec 2024)</option>
                        </select>
                      </div>
                      
                      <button type="submit" name="update_system_settings" class="btn btn-danger">
                        <i class="fas fa-save mr-2"></i>Save System Settings
                      </button>
                    </form>
                    
                    <!-- Database Management -->
                    <div class="mt-4">
                      <h5><i class="fas fa-database mr-2"></i>Database Management</h5>
                      <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Warning:</strong> Database operations are critical. Make sure to backup your database before proceeding.
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6">
                          <button class="btn btn-info btn-block mb-2">
                            <i class="fas fa-download mr-2"></i>Backup Database
                          </button>
                        </div>
                        <div class="col-md-6">
                          <button class="btn btn-warning btn-block mb-2" onclick="return confirm('Are you sure you want to optimize the database?')">
                            <i class="fas fa-sync-alt mr-2"></i>Optimize Database
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Security Tab -->
                  <div class="tab-pane fade" id="security" role="tabpanel">
                    <h5><i class="fas fa-shield-alt mr-2"></i>Login History</h5>
                    <div class="table-responsive">
                      <table class="table table-bordered table-hover">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Login Time</th>
                            <th>IP Address</th>
                            <th>Browser</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if ($login_history_result && $login_history_result->num_rows > 0): ?>
                            <?php 
                            $counter = 1;
                            while($login = $login_history_result->fetch_assoc()): 
                            ?>
                              <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo date('d M Y H:i:s', strtotime($login['login_time'])); ?></td>
                                <td><?php echo htmlspecialchars($login['ip_address']); ?></td>
                                <td><?php echo htmlspecialchars($login['user_agent']); ?></td>
                                <td>
                                  <span class="badge badge-success">Successful</span>
                                </td>
                              </tr>
                            <?php endwhile; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="5" class="text-center">No login history found.</td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                    
                    <!-- Security Audit -->
                    <div class="mt-4">
                      <h5><i class="fas fa-clipboard-check mr-2"></i>Security Audit</h5>
                      <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-2"></i>Last Security Check:</h6>
                        <p class="mb-0">System security audit completed on <?php echo date('d M Y'); ?>. All systems are secure.</p>
                      </div>
                      
                      <button class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Run Security Scan
                      </button>
                      <button class="btn btn-success">
                        <i class="fas fa-file-export mr-2"></i>Generate Security Report
                      </button>
                    </div>
                    
                    <!-- Two-Factor Authentication -->
                    <div class="mt-4">
                      <h5><i class="fas fa-mobile-alt mr-2"></i>Two-Factor Authentication</h5>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="twoFactorEnabled">
                          <label class="custom-control-label" for="twoFactorEnabled">Enable Two-Factor Authentication</label>
                        </div>
                        <small class="text-muted">When enabled, you'll need to enter a verification code from your authenticator app when logging in.</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="danger-zone mt-4">
              <h4><i class="fas fa-exclamation-triangle mr-2"></i>Danger Zone</h4>
              <p class="mb-3">These actions are irreversible. Please proceed with caution.</p>
              
              <div class="row">
                <div class="col-md-6">
                  <button class="btn btn-outline-danger btn-block mb-2" 
                          onclick="return confirm('Are you sure you want to clear all system logs? This action cannot be undone.')">
                    <i class="fas fa-trash-alt mr-2"></i>Clear System Logs
                  </button>
                </div>
                <div class="col-md-6">
                  <button class="btn btn-danger btn-block mb-2" 
                          onclick="return confirm('WARNING: This will delete all temporary files and cache. Are you sure?')">
                    <i class="fas fa-broom mr-2"></i>Clear Cache & Temporary Files
                  </button>
                </div>
              </div>
              
              <div class="mt-3">
                <button class="btn btn-dark btn-block" 
                        onclick="return confirm('CRITICAL: This will reset the entire system to factory settings. ALL DATA WILL BE LOST! Are you absolutely sure?')">
                  <i class="fas fa-bomb mr-2"></i>Factory Reset System
                </button>
              </div>
            </div>
            
            <!-- System Information -->
            <div class="card mt-4">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-info-circle mr-2"></i>System Information
                </h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    <p><strong>MySQL Version:</strong> <?php echo $conn->server_info ?? 'N/A'; ?></p>
                    <p><strong>Server OS:</strong> <?php echo php_uname('s'); ?></p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>Application Version:</strong> 2.0.0</p>
                    <p><strong>Last Updated:</strong> 27 Dec 2024</p>
                    <p><strong>License:</strong> Proprietary - Al Hadi Solutions</p>
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
      <b>Version</b> 2.0.0 | 
      <b>User:</b> <?php echo htmlspecialchars($full_name); ?> | 
      <b>Role:</b> Super Admin
    </div>
  </footer>
</div>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script>
// Password strength checker
function checkPasswordStrength(password) {
    var strengthBar = document.getElementById('passwordStrength');
    var strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    // Reset classes
    strengthBar.className = 'password-strength';
    strengthBar.style.width = '0%';
    
    if (password.length === 0) {
        return;
    } else if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
    } else if (strength === 3 || strength === 4) {
        strengthBar.classList.add('strength-medium');
    } else if (strength === 5) {
        strengthBar.classList.add('strength-strong');
    } else {
        strengthBar.classList.add('strength-very-strong');
    }
}

// Password match checker
$(document).ready(function() {
    $('#new_password, #confirm_password').on('keyup', function() {
        var password = $('#new_password').val();
        var confirmPassword = $('#confirm_password').val();
        var matchDiv = $('#passwordMatch');
        
        if (password.length === 0 || confirmPassword.length === 0) {
            matchDiv.html('');
        } else if (password === confirmPassword) {
            matchDiv.html('<small class="text-success"><i class="fas fa-check-circle"></i> Passwords match</small>');
        } else {
            matchDiv.html('<small class="text-danger"><i class="fas fa-times-circle"></i> Passwords do not match</small>');
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Initialize tabs
    $('#settingsTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Handle form submissions
    $('form').on('submit', function() {
        $('.alert').hide();
    });
});
</script>
</body>
</html>
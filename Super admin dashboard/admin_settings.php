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
$user_id = $_SESSION['user_id'];

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Fetch current admin details
$admin_query = "SELECT * FROM users WHERE id = ?";
$admin_stmt = $conn->prepare($admin_query);
$admin_stmt->bind_param("i", $user_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin_data = $admin_result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_full_name = trim($_POST['full_name']);
    $new_email = trim($_POST['email']);
    $new_phone = trim($_POST['phone']);
    
    // Basic validation
    if (empty($new_full_name)) {
        $error = "Full name is required!";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists for another user
        $email_check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_check_stmt = $conn->prepare($email_check_query);
        $email_check_stmt->bind_param("si", $new_email, $user_id);
        $email_check_stmt->execute();
        $email_check_result = $email_check_stmt->get_result();
        
        if ($email_check_result->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Update profile
            $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssi", $new_full_name, $new_email, $new_phone, $user_id);
            
            if ($update_stmt->execute()) {
                // Update session variables
                $_SESSION['full_name'] = $new_full_name;
                $full_name = $new_full_name;
                
                $success = "Profile updated successfully!";
            } else {
                $error = "Error updating profile: " . $conn->error;
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!password_verify($current_password, $admin_data['password'])) {
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
        $password_update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($password_update_stmt->execute()) {
            $password_success = "Password changed successfully!";
        } else {
            $password_error = "Error changing password: " . $conn->error;
        }
    }
}

// Get login history (only if table exists)
$login_history_result = [];
$check_table = $conn->query("SHOW TABLES LIKE 'login_history'");
if ($check_table && $check_table->num_rows > 0) {
  $login_history_query = "SELECT * FROM login_history WHERE user_id = ? ORDER BY login_time DESC LIMIT 10";
  $login_history_stmt = $conn->prepare($login_history_query);
  if ($login_history_stmt) {
    $login_history_stmt->bind_param("i", $user_id);
    $login_history_stmt->execute();
    $login_history_result = $login_history_stmt->get_result();
  } else {
    $login_history_result = [];
  }
} else {
  $login_history_result = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Settings - Admin Dashboard</title>
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
    
    .profile-card {
        border: 2px solid #007bff;
        border-radius: 10px;
    }
    
    .profile-header {
        background: #007bff;
        color: white;
        padding: 20px;
        border-radius: 8px 8px 0 0;
        text-align: center;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: white;
        color: #007bff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: bold;
        margin: 0 auto 15px;
        border: 4px solid #0056b3;
    }
    
    .info-item {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: bold;
        color: #555;
    }
    
    .info-value {
        color: #333;
    }
    
    .settings-tabs .nav-link {
        border-radius: 5px;
        margin-bottom: 5px;
    }
    
    .password-strength {
        height: 5px;
        margin-top: 5px;
        border-radius: 3px;
    }
    
    .strength-weak { background-color: #dc3545; width: 25%; }
    .strength-medium { background-color: #ffc107; width: 50%; }
    .strength-strong { background-color: #28a745; width: 75%; }
    .strength-very-strong { background-color: #20c997; width: 100%; }
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
                <a href="view_assigned_categories.php" class="nav-link">
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
          <li class="nav-item menu-open">
            <a href="admin_settings.php" class="nav-link active">
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
              <i class="fas fa-cog mr-2"></i>Admin Settings
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
              <li class="breadcrumb-item active">Admin Settings</li>
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
          <!-- Profile Overview -->
          <div class="col-md-4">
            <div class="card profile-card">
              <div class="profile-header">
                <div class="profile-avatar">
                  <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                </div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <p class="mb-0">
                  <span class="badge badge-light"><?php echo strtoupper($department) . ' ADMIN'; ?></span>
                </p>
              </div>
              <div class="card-body">
                <div class="info-item">
                  <div class="info-label">Admin ID:</div>
                  <div class="info-value"><?php echo $admin_id; ?></div>
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
                  <div class="info-value"><?php echo ucfirst($user_role); ?></div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Account Created:</div>
                  <div class="info-value"><?php echo date('d M Y', strtotime($admin_data['created_at'] ?? 'N/A')); ?></div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Last Login:</div>
                  <div class="info-value"><?php echo date('d M Y H:i', strtotime($admin_data['last_login'] ?? 'N/A')); ?></div>
                </div>
              </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-3">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-bar mr-2"></i>Quick Stats
                </h3>
              </div>
              <div class="card-body">
                <?php
                // Get assigned categories count
                $cat_count_query = "SELECT COUNT(*) as cat_count FROM admin_categories WHERE admin_id = ?";
                $cat_count_stmt = $conn->prepare($cat_count_query);
                $cat_count_stmt->bind_param("i", $user_id);
                $cat_count_stmt->execute();
                $cat_count_result = $cat_count_stmt->get_result();
                $cat_count = $cat_count_result->fetch_assoc()['cat_count'];
                
                // Get assigned products count
                $prod_count_query = "SELECT COUNT(DISTINCT p.id) as prod_count 
                                     FROM products p
                                     INNER JOIN admin_categories ac ON p.category_id = ac.category_id
                                     WHERE ac.admin_id = ? AND p.status = 'active'";
                $prod_count_stmt = $conn->prepare($prod_count_query);
                $prod_count_stmt->bind_param("i", $user_id);
                $prod_count_stmt->execute();
                $prod_count_result = $prod_count_stmt->get_result();
                $prod_count = $prod_count_result->fetch_assoc()['prod_count'];
                ?>
                
                <div class="row">
                  <div class="col-6">
                    <div class="info-box mb-3">
                      <span class="info-box-icon bg-info elevation-1">
                        <i class="fas fa-archive"></i>
                      </span>
                      <div class="info-box-content">
                        <span class="info-box-text">Assigned Categories</span>
                        <span class="info-box-number"><?php echo $cat_count; ?></span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-6">
                    <div class="info-box mb-3">
                      <span class="info-box-icon bg-success elevation-1">
                        <i class="fas fa-box"></i>
                      </span>
                      <div class="info-box-content">
                        <span class="info-box-text">Active Products</span>
                        <span class="info-box-number"><?php echo $prod_count; ?></span>
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
                      <i class="fas fa-user mr-2"></i>Profile Settings
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab">
                      <i class="fas fa-key mr-2"></i>Change Password
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
                        <label for="department">Department</label>
                        <input type="text" class="form-control" id="department" value="<?php echo ucfirst($department); ?>" readonly>
                        <small class="text-muted">Department cannot be changed. Contact Super Admin for department changes.</small>
                      </div>
                      
                      <div class="form-group">
                        <label for="admin_id">Admin ID</label>
                        <input type="text" class="form-control" id="admin_id" value="<?php echo $admin_id; ?>" readonly>
                        <small class="text-muted">Admin ID is unique and cannot be changed.</small>
                      </div>
                      
                      <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Update Profile
                      </button>
                    </form>
                  </div>
                  
                  <!-- Change Password Tab -->
                  <div class="tab-pane fade" id="password" role="tabpanel">
                    <!-- Display Password Messages -->
                    <?php if (isset($password_error)): ?>
                      <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <?php echo $password_error; ?>
                      </div>
                    <?php endif; ?>
                    
                    <?php if (isset($password_success)): ?>
                      <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Success!</h5>
                        <?php echo $password_success; ?>
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
                      
                      <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key mr-2"></i>Change Password
                      </button>
                    </form>
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
                          <?php if ($login_history_result->num_rows > 0): ?>
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
                    
                    <div class="alert alert-info mt-3">
                      <h6><i class="fas fa-info-circle mr-2"></i>Security Tips:</h6>
                      <ul class="mb-0">
                        <li>Use a strong, unique password</li>
                        <li>Never share your password with anyone</li>
                        <li>Log out after each session</li>
                        <li>Change your password regularly</li>
                        <li>Contact Super Admin if you notice suspicious activity</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Account Information -->
            <div class="card mt-3">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-info-circle mr-2"></i>Account Information
                </h3>
              </div>
              <div class="card-body">
                <p><strong>Note:</strong> Some account settings can only be changed by the Super Administrator.</p>
                <p>If you need to change your department, role, or other account details, please contact your Super Admin.</p>
                
                <div class="alert alert-warning">
                  <h6><i class="fas fa-exclamation-triangle mr-2"></i>Important:</h6>
                  <p class="mb-0">
                    Your access is limited to the categories and products assigned to you by the Super Admin. 
                    You cannot view or modify any categories/products that are not assigned to you.
                  </p>
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
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
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
});
</script>
</body>
</html>
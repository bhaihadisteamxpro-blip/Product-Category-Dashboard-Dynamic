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

// Handle category assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_category'])) {
    $admin_id_assign = intval($_POST['admin_id']);
    $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];
    
    if ($admin_id_assign > 0 && !empty($category_ids)) {
        // First, delete existing assignments for this admin
        $delete_query = "DELETE FROM admin_categories WHERE admin_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $admin_id_assign);
        $delete_stmt->execute();
        
        // Insert new assignments
        $insert_query = "INSERT INTO admin_categories (admin_id, category_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        
        $success_count = 0;
        foreach ($category_ids as $category_id) {
            $category_id = intval($category_id);
            if ($category_id > 0) {
                $insert_stmt->bind_param("ii", $admin_id_assign, $category_id);
                if ($insert_stmt->execute()) {
                    $success_count++;
                }
            }
        }
        
        if ($success_count > 0) {
            $success = "$success_count categories assigned successfully to admin!";
        } else {
            $error = "Failed to assign categories.";
        }
    } else {
        $error = "Please select an admin and at least one category.";
    }
}

// Remove assignment
if (isset($_GET['remove_admin'])) {
    $remove_admin_id = intval($_GET['remove_admin']);
    
    $delete_query = "DELETE FROM admin_categories WHERE admin_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $remove_admin_id);
    
    if ($delete_stmt->execute()) {
        $success = "All category assignments removed for this admin!";
    } else {
        $error = "Error removing assignments: " . $conn->error;
    }
}

// Fetch all admins (excluding super admins)
$admins_query = "SELECT id, full_name, admin_id, department 
                 FROM users 
                 WHERE role = 'admin' AND status = 'active' 
                 ORDER BY full_name";
$admins_result = $conn->query($admins_query);

// Fetch all categories
$categories_query = "SELECT id, category_name, category_description AS description, status 
                     FROM categories 
                     ORDER BY category_name";
$categories_result = $conn->query($categories_query);

// Fetch assigned categories for selected admin (for edit)
$assigned_categories = [];
if (isset($_GET['admin_id'])) {
    $selected_admin_id = intval($_GET['admin_id']);
    $assigned_query = "SELECT category_id FROM admin_categories WHERE admin_id = ?";
    $assigned_stmt = $conn->prepare($assigned_query);
    $assigned_stmt->bind_param("i", $selected_admin_id);
    $assigned_stmt->execute();
    $assigned_result = $assigned_stmt->get_result();
    
    while ($row = $assigned_result->fetch_assoc()) {
        $assigned_categories[] = $row['category_id'];
    }
}

// Fetch all assignment records with counts
$assignments_query = "SELECT 
                      u.id as admin_id, 
                      u.full_name as admin_name, 
                      u.admin_id as admin_code,
                      COUNT(ac.category_id) as assigned_count,
                      GROUP_CONCAT(c.category_name SEPARATOR ', ') as category_names
                      FROM users u
                      LEFT JOIN admin_categories ac ON u.id = ac.admin_id
                      LEFT JOIN categories c ON ac.category_id = c.id
                      WHERE u.role = 'admin' AND u.status = 'active'
                      GROUP BY u.id, u.full_name, u.admin_id
                      ORDER BY u.full_name";
$assignments_result = $conn->query($assignments_query);

// Count statistics
$stats_query = "SELECT 
                COUNT(DISTINCT admin_id) as total_admins_with_categories,
                COUNT(*) as total_assignments,
                COUNT(DISTINCT category_id) as unique_categories_assigned
                FROM admin_categories";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Assign Categories - Super Admin Dashboard</title>
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
        border: 2px solid #007bff;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .assignment-card .card-header {
        background: #007bff;
        color: white;
        border-radius: 8px 8px 0 0;
    }
    
    .category-checkbox {
        margin-right: 10px;
    }
    
    .category-item {
        padding: 10px;
        border: 1px solid #dee2e6;
        margin-bottom: 5px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .category-item:hover {
        background: #f8f9fa;
        border-color: #007bff;
    }
    
    .category-item.selected {
        background: #d4edda;
        border-color: #28a745;
    }
    
    .category-badge {
        display: inline-block;
        padding: 5px 10px;
        margin: 2px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .badge-active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .badge-inactive {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .stats-card {
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .action-buttons .btn {
        margin: 2px;
    }
    
    .admin-info-card {
        border-left: 4px solid #007bff;
    }
    
    .category-icon-small {
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
                <a href="assigned_category.php" class="nav-link active">
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
              <i class="fas fa-list-alt mr-2"></i>Assign Categories to Admins
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="super_admin.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">User Management</a></li>
              <li class="breadcrumb-item active">Assign Categories</li>
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
                <h3><?php echo $stats['total_admins_with_categories'] ?? 0; ?></h3>
                <p>Admins with Categories</p>
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
                <h3><?php echo $stats['total_assignments'] ?? 0; ?></h3>
                <p>Total Assignments</p>
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
                <h3><?php echo $stats['unique_categories_assigned'] ?? 0; ?></h3>
                <p>Unique Categories</p>
              </div>
              <div class="icon">
                <i class="fas fa-archive"></i>
              </div>
              <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-primary stats-card">
              <div class="inner">
                <h3><?php echo $admins_result->num_rows; ?></h3>
                <p>Total Active Admins</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-check"></i>
              </div>
              <a href="manage_admins.php" class="small-box-footer">View Admins <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Assignment Form -->
        <div class="row">
          <div class="col-md-12">
            <div class="card assignment-card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-exchange-alt mr-2"></i>Assign Categories to Admin
                </h3>
              </div>
              <div class="card-body">
                <form method="POST" action="" id="assignCategoryForm">
                  <div class="row">
                    <!-- Select Admin -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="admin_id">Select Admin:</label>
                        <select class="form-control" id="admin_id" name="admin_id" required 
                                onchange="window.location.href='assigned_category.php?admin_id=' + this.value">
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
                        <div class="alert alert-info admin-info-card">
                          <h6><i class="fas fa-user mr-2"></i>Selected Admin:</h6>
                          <p class="mb-1"><strong>Name:</strong> <?php echo $selected_admin['full_name']; ?></p>
                          <p class="mb-1"><strong>Admin ID:</strong> <?php echo $selected_admin['admin_id']; ?></p>
                          <p class="mb-0"><strong>Department:</strong> <?php echo ucfirst($selected_admin['department']); ?></p>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                  
                  <!-- Categories Selection -->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Select Categories to Assign:</label>
                        <div class="row" style="max-height: 300px; overflow-y: auto; padding: 10px;">
                          <?php 
                          if ($categories_result->num_rows > 0):
                            while($category = $categories_result->fetch_assoc()):
                          ?>
                            <div class="col-md-4 mb-2">
                              <div class="category-item <?php echo in_array($category['id'], $assigned_categories) ? 'selected' : ''; ?>"
                                   onclick="toggleCategory(this, <?php echo $category['id']; ?>)">
                                <input type="checkbox" 
                                       class="category-checkbox" 
                                       id="category_<?php echo $category['id']; ?>" 
                                       name="category_ids[]" 
                                       value="<?php echo $category['id']; ?>"
                                       <?php echo in_array($category['id'], $assigned_categories) ? 'checked' : ''; ?>
                                       style="display: none;">
                                <div class="d-flex justify-content-between align-items-start">
                                  <div>
                                    <i class="fas fa-archive mr-2"></i>
                                    <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                                    <?php if (!empty($category['description'])): ?>
                                      <br><small class="text-muted"><?php echo htmlspecialchars(substr($category['description'], 0, 30)); ?>...</small>
                                    <?php endif; ?>
                                  </div>
                                  <span class="category-badge <?php echo $category['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo ucfirst($category['status']); ?>
                                  </span>
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
                                No categories found. Please add categories first from <a href="add_category.php">Add Category</a>.
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
                      <button type="submit" name="assign_category" class="btn btn-primary btn-lg">
                        <i class="fas fa-check-circle mr-2"></i>Assign Selected Categories
                      </button>
                      <button type="button" class="btn btn-success btn-lg" onclick="selectAllCategories()">
                        <i class="fas fa-check-square mr-2"></i>Select All
                      </button>
                      <button type="button" class="btn btn-warning btn-lg" onclick="deselectAllCategories()">
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
                  <i class="fas fa-history mr-2"></i>Current Category Assignments
                </h3>
                <div class="card-tools">
                  <span class="badge badge-primary">
                    Total Assignments: <?php echo $stats['total_assignments'] ?? 0; ?>
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
                          <th>Assigned Categories Count</th>
                          <th>Categories List</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        $counter = 1;
                        $assignments_result->data_seek(0); // Reset pointer
                        while($assignment = $assignments_result->fetch_assoc()): 
                        ?>
                          <tr>
                            <td><?php echo $counter++; ?></td>
                            <td>
                              <i class="fas fa-user-circle mr-2"></i>
                              <strong><?php echo htmlspecialchars($assignment['admin_name']); ?></strong>
                              <br><small class="text-muted">ID: <?php echo $assignment['admin_code']; ?></small>
                            </td>
                            <td>
                              <span class="badge badge-info" style="font-size: 16px;">
                                <?php echo $assignment['assigned_count']; ?>
                              </span>
                            </td>
                            <td>
                              <?php 
                              if (!empty($assignment['category_names'])) {
                                $categories = explode(', ', $assignment['category_names']);
                                foreach ($categories as $category) {
                                  echo '<span class="category-badge badge-active">' . htmlspecialchars($category) . '</span> ';
                                }
                              } else {
                                echo '<span class="text-muted">No categories assigned</span>';
                              }
                              ?>
                            </td>
                            <td class="action-buttons">
                              <a href="assigned_category.php?admin_id=<?php echo $assignment['admin_id']; ?>" 
                                 class="btn btn-sm btn-info" title="Edit Assignment">
                                <i class="fas fa-edit"></i>
                              </a>
                              <a href="assigned_category.php?remove_admin=<?php echo $assignment['admin_id']; ?>" 
                                 class="btn btn-sm btn-danger" title="Remove All Assignments"
                                 onclick="return confirm('Are you sure you want to remove all category assignments for this admin?')">
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
                    <h4><i class="icon fas fa-info"></i> No Category Assignments Found!</h4>
                    <p>No categories have been assigned to any admin yet. Use the form above to assign categories.</p>
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
// Function to toggle category selection
function toggleCategory(element, categoryId) {
  const checkbox = element.querySelector('.category-checkbox');
  checkbox.checked = !checkbox.checked;
  
  if (checkbox.checked) {
    element.classList.add('selected');
  } else {
    element.classList.remove('selected');
  }
}

// Function to select all categories
function selectAllCategories() {
  document.querySelectorAll('.category-checkbox').forEach(checkbox => {
    checkbox.checked = true;
    if (checkbox.parentElement) {
      checkbox.parentElement.classList.add('selected');
    }
  });
}

// Function to deselect all categories
function deselectAllCategories() {
  document.querySelectorAll('.category-checkbox').forEach(checkbox => {
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
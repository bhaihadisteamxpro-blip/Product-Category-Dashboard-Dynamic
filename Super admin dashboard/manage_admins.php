<?php
session_start();
require_once 'config/database.php'; // Database connection file



// Handle delete admin
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM users WHERE id = ? AND role = 'admin'";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Admin deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting admin: " . $conn->error;
    }
    header("Location: manage_admins.php");
    exit();
}

// Handle status change
if (isset($_GET['change_status'])) {
    $admin_id = $_GET['admin_id'];
    $new_status = $_GET['new_status'];
    
    $status_query = "UPDATE users SET status = ? WHERE id = ? AND role = 'admin'";
    $status_stmt = $conn->prepare($status_query);
    $status_stmt->bind_param("si", $new_status, $admin_id);
    
    if ($status_stmt->execute()) {
        $_SESSION['success'] = "Admin status updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating status: " . $conn->error;
    }
    header("Location: manage_admins.php");
    exit();
}

// Fetch all admins from database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with filters
$query = "SELECT * FROM users WHERE role = 'admin'";

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR username LIKE ? OR email LIKE ? OR admin_id LIKE ?)";
}

if (!empty($department_filter)) {
    $query .= " AND department = ?";
}

if (!empty($status_filter)) {
    $query .= " AND status = ?";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);

// Bind parameters if filters are used
if (!empty($search)) {
    $search_param = "%{$search}%";
    if (!empty($department_filter) && !empty($status_filter)) {
        $stmt->bind_param("ssssss", $search_param, $search_param, $search_param, $search_param, $department_filter, $status_filter);
    } elseif (!empty($department_filter)) {
        $stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $department_filter);
    } elseif (!empty($status_filter)) {
        $stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $status_filter);
    } else {
        $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    }
} else {
    if (!empty($department_filter) && !empty($status_filter)) {
        $stmt->bind_param("ss", $department_filter, $status_filter);
    } elseif (!empty($department_filter)) {
        $stmt->bind_param("s", $department_filter);
    } elseif (!empty($status_filter)) {
        $stmt->bind_param("s", $status_filter);
    }
}

$stmt->execute();
$result = $stmt->get_result();

// Get total count for statistics
$total_query = "SELECT COUNT(*) as total, 
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
                FROM users WHERE role = 'admin'";
$total_result = $conn->query($total_query);
$stats = $total_result->fetch_assoc();

// Get unique departments for filter dropdown
$dept_query = "SELECT DISTINCT department FROM users WHERE role = 'admin' ORDER BY department";
$dept_result = $conn->query($dept_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Admins - Stock Management</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
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
                <a href="index.php" class="nav-link">Home</a>
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
                            <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
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
                            <img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
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
                            <img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
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

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index.php" class="brand-link">
            <span class="brand-text font-weight-light" style="font-weight: bold !important; font-family: times; color: white !important; text-align: center !important; margin-left: 26px !important; font-size: 25px !important;">SUPER ADMIN</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="index.php" class="d-block" style="font-weight: bold; font-family: 'Times New Roman', Times, serif; margin-left: 15px !important;">STOCK MANAGEMENT</a>
                </div>
            </div>
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item menu-open">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon fa fa-plus"></i>
                            <p>
                                User Management
                                <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right"></span>
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
                                <a href="manage_admins.php" class="nav-link active">
                                    <i class="fa fa-users nav-icon"></i>
                                    <p>Manage Admins</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="assign_categories.php" class="nav-link">
                                    <i class="fas fa-list-alt nav-icon"></i>
                                    <p>Assign Categories</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="assign_products.php" class="nav-link">
                                    <i class="fas fa-boxes nav-icon"></i>
                                    <p>Assign Products</p>
                                </a>
                            </li>
                        </ul> 
                    </li>
            
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fa fa-archive"></i>
                            <p>
                            Categories
                            <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right"></span>
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
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fa fa-archive"></i>
                            <p>
                            Products
                            <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right"></span>
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
                
                    <li class="nav-item">
                        <a href="super_admin_settings.php" class="nav-link">
                            <i class="nav-icon fa fa-user"></i>
                            <p>Super Admin Settings</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">
                            <i class="nav-icon fa fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Manage Admins</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">User Management</a></li>
                            <li class="breadcrumb-item active">Manage Admins</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Display Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Success!</h5>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $stats['total']; ?></h3>
                                <p>Total Admins</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $stats['active']; ?></h3>
                                <p>Active Admins</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $stats['inactive']; ?></h3>
                                <p>Inactive Admins</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-slash"></i>
                            </div>
                            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>0</h3>
                                <p>Suspended Admins</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-lock"></i>
                            </div>
                            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Filters</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Search</label>
                                        <input type="text" class="form-control" name="search" 
                                               value="<?php echo htmlspecialchars($search); ?>" 
                                               placeholder="Search by name, username, email, or ID">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select class="form-control" name="department">
                                            <option value="">All Departments</option>
                                            <?php while ($dept = $dept_result->fetch_assoc()): ?>
                                                <option value="<?php echo $dept['department']; ?>" 
                                                    <?php echo ($department_filter == $dept['department']) ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($dept['department']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" name="status">
                                            <option value="">All Status</option>
                                            <option value="active" <?php echo ($status_filter == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($status_filter == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="suspended" <?php echo ($status_filter == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group" style="margin-top: 32px;">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="manage_admins.php" class="btn btn-default">
                                            <i class="fas fa-redo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Admins Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Admins List</h3>
                        <div class="card-tools">
                            <a href="add_admin.php" class="btn btn-success btn-sm">
                                <i class="fas fa-user-plus"></i> Add New Admin
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="adminsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Admin ID</th>
                                        <th>Admin</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $counter = 1; ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td>
                                                    <span class="badge badge-info"><?php echo $row['admin_id']; ?></span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar mr-2">
                                                            <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>@<?php echo htmlspecialchars($row['username']); ?></td>
                                                <td>
                                                    <a href="mailto:<?php echo $row['email']; ?>">
                                                        <?php echo htmlspecialchars($row['email']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['phone'])): ?>
                                                        <a href="tel:<?php echo $row['phone']; ?>">
                                                            <?php echo htmlspecialchars($row['phone']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="department-badge">
                                                        <?php echo ucfirst($row['department']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($row['status']) {
                                                        case 'active':
                                                            $status_class = 'status-active';
                                                            break;
                                                        case 'inactive':
                                                            $status_class = 'status-inactive';
                                                            break;
                                                        case 'suspended':
                                                            $status_class = 'status-suspended';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="status-badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-info btn-sm action-btn" 
                                                                data-toggle="modal" data-target="#viewModal<?php echo $row['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="edit_admin.php?id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-primary btn-sm action-btn">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($row['status'] == 'active'): ?>
                                                            <a href="manage_admins.php?change_status=1&admin_id=<?php echo $row['id']; ?>&new_status=inactive" 
                                                               class="btn btn-warning btn-sm action-btn"
                                                               onclick="return confirm('Are you sure you want to deactivate this admin?')">
                                                                <i class="fas fa-user-slash"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="manage_admins.php?change_status=1&admin_id=<?php echo $row['id']; ?>&new_status=active" 
                                                               class="btn btn-success btn-sm action-btn"
                                                               onclick="return confirm('Are you sure you want to activate this admin?')">
                                                                <i class="fas fa-user-check"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="manage_admins.php?delete_id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-danger btn-sm action-btn"
                                                           onclick="return confirm('Are you sure you want to delete this admin? This action cannot be undone!')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>

                                                    <!-- View Modal -->
                                                    <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Admin Details</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-3 text-center">
                                                                            <div class="avatar" style="width: 100px; height: 100px; font-size: 36px;">
                                                                                <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                                                            </div>
                                                                            <h5 class="mt-3"><?php echo htmlspecialchars($row['full_name']); ?></h5>
                                                                            <span class="status-badge <?php echo $status_class; ?>">
                                                                                <?php echo ucfirst($row['status']); ?>
                                                                            </span>
                                                                        </div>
                                                                        <div class="col-md-9">
                                                                            <table class="table table-bordered">
                                                                                <tr>
                                                                                    <th width="30%">Admin ID:</th>
                                                                                    <td><?php echo $row['admin_id']; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Username:</th>
                                                                                    <td>@<?php echo htmlspecialchars($row['username']); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Email:</th>
                                                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Phone:</th>
                                                                                    <td><?php echo $row['phone'] ? htmlspecialchars($row['phone']) : 'Not provided'; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Department:</th>
                                                                                    <td><?php echo ucfirst($row['department']); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Created Date:</th>
                                                                                    <td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Last Updated:</th>
                                                                                    <td>
                                                                                        <?php 
                                                                                        if ($row['updated_at']) {
                                                                                            echo date('d M Y, h:i A', strtotime($row['updated_at']));
                                                                                        } else {
                                                                                            echo 'Never updated';
                                                                                        }
                                                                                        ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Last Login:</th>
                                                                                    <td>
                                                                                        <?php 
                                                                                        if ($row['last_login']) {
                                                                                            echo date('d M Y, h:i A', strtotime($row['last_login']));
                                                                                        } else {
                                                                                            echo 'Never logged in';
                                                                                        }
                                                                                        ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                    <a href="edit_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                                                        <i class="fas fa-edit"></i> Edit Admin
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">
                                                <div class="py-5">
                                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                    <h4>No Admins Found</h4>
                                                    <p class="text-muted">No admin records found in the database.</p>
                                                    <a href="add_admin.php" class="btn btn-primary">
                                                        <i class="fas fa-user-plus"></i> Add First Admin
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            <span class="text-muted">
                                Showing <?php echo $result->num_rows; ?> of <?php echo $stats['total']; ?> admins
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Export Options</h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                            <button type="button" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </button>
                            <button type="button" class="btn btn-info">
                                <i class="fas fa-print"></i> Print
                            </button>
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
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#adminsTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": false, // We have our own search
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[1, 'desc']], // Sort by Admin ID descending
        "pageLength": 10,
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "zeroRecords": "No matching records found",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "search": "Search:",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });

    // Status change confirmation
    $('.status-change').click(function(e) {
        if (!confirm('Are you sure you want to change the status of this admin?')) {
            e.preventDefault();
        }
    });

    // Delete confirmation
    $('.delete-btn').click(function(e) {
        if (!confirm('Are you sure you want to delete this admin? This action cannot be undone!')) {
            e.preventDefault();
        }
    });

    // Refresh page after 30 seconds to show latest data
    setTimeout(function() {
        location.reload();
    }, 30000); // 30 seconds
});
</script>
</body>
</html>
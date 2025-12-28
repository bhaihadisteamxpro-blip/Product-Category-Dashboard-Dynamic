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

// Fetch all website users
$query = "SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC";
$result = $conn->query($query);

// Statistics
$total_query = "SELECT COUNT(*) as total, 
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
                FROM users WHERE role = 'user'";
$total_result = $conn->query($total_query);
$stats = $total_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users - Super Admin Dashboard</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="../assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="../assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <style>
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .status-active { background: #28a745; color: white; }
        .status-inactive { background: #dc3545; color: white; }
        .avatar-small { width: 35px; height: 35px; background: #6c757d; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="superadmin.php" class="nav-link">Home</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#"><i class="fas fa-user-circle fa-lg"></i><span class="ml-2"><?php echo htmlspecialchars($full_name); ?></span></a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="super_admin_profile.php" class="dropdown-item"><i class="fas fa-user mr-2"></i> Profile</a>
                    <a href="../backend/logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                </div>
            </li>
        </ul>
    </nav>

    <?php include 'sidebar.php'; ?>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0">Manage Website Users</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="superadmin.php">Home</a></li>
                            <li class="breadcrumb-item active">Manage Users</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Stats -->
                <div class="row">
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-info"><div class="inner"><h3><?php echo $stats['total']; ?></h3><p>Total Users</p></div><div class="icon"><i class="fas fa-users"></i></div></div>
                    </div>
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-success"><div class="inner"><h3><?php echo $stats['active']; ?></h3><p>Active Users</p></div><div class="icon"><i class="fas fa-user-check"></i></div></div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="small-box bg-danger"><div class="inner"><h3><?php echo $stats['inactive']; ?></h3><p>Inactive Users</p></div><div class="icon"><i class="fas fa-user-slash"></i></div></div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Website Users List</h3>
                    </div>
                    <div class="card-body">
                        <table id="usersTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User ID</th>
                                    <th>Full Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Joined Date</th>
                                    <th>Status</th>
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
                                        <td><span class="badge badge-secondary"><?php echo $row['admin_id']; ?></span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-small mr-2"><?php echo strtoupper(substr($row['full_name'], 0, 1)); ?></div>
                                                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                            </div>
                                        </td>
                                        <td>@<?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm <?php echo $row['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?> toggle-status" data-id="<?php echo $row['id']; ?>">
                                                <i class="fas <?php echo $row['status'] == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $row['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer text-center"><strong>Al Hadi Solutions &copy; 2024</strong></footer>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../assets/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        "responsive": true, 
        "autoWidth": false,
        "order": [[ 5, "desc" ]] // Sort by Joined Date by default
    });

    $('.toggle-status').on('click', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Change user status?', 
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!'
        }).then((res) => {
            if(res.isConfirmed) {
                $.post('../api/toggle_user_status.php', {id: id}, function(res) {
                    if(res.status === 'success') {
                        Swal.fire({icon: 'success', title: 'Done!', timer: 1000}).then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    });

    $('.delete-user').on('click', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete this User?', 
            text: 'This action cannot be undone!', 
            icon: 'error', 
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((res) => {
            if(res.isConfirmed) {
                $.post('../api/delete_user.php', {id: id}, function(res) {
                    if(res.status === 'success') {
                        Swal.fire({icon: 'success', title: 'Deleted!', timer: 1000}).then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>

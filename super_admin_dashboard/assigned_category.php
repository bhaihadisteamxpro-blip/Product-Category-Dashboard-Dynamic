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

// Fetch all admins (for the select dropdown)
$admins_query = "SELECT id, full_name, admin_id, department FROM users WHERE role = 'admin' AND status = 'active' ORDER BY full_name";
$admins_result = $conn->query($admins_query);

// Fetch all active categories
$categories_query = "SELECT id, category_name FROM categories WHERE status = 'active' ORDER BY category_name";
$categories_result = $conn->query($categories_query);
$all_categories = [];
while($cat = $categories_result->fetch_assoc()) {
    $all_categories[] = $cat;
}

// Fetch current assignments for the table
$assignments_query = "SELECT 
                      u.id as admin_id, 
                      u.full_name as admin_name, 
                      u.admin_id as admin_code,
                      COUNT(ac.category_id) as assigned_count,
                      GROUP_CONCAT(c.category_name SEPARATOR ', ') as category_names,
                      GROUP_CONCAT(c.id SEPARATOR ',') as category_ids
                      FROM users u
                      LEFT JOIN admin_categories ac ON u.id = ac.admin_id
                      LEFT JOIN categories c ON ac.category_id = c.id
                      WHERE u.role = 'admin' AND u.status = 'active'
                      GROUP BY u.id, u.full_name, u.admin_id
                      ORDER BY u.full_name";
$assignments_result = $conn->query($assignments_query);

// Stats
$stats_query = "SELECT COUNT(DISTINCT admin_id) as total_admins_with_categories FROM admin_categories";
$stats_res = $conn->query($stats_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Assign Categories - Super Admin Dashboard</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <style>
    .user-info-sidebar { text-align: center; padding: 15px; background: rgba(0,0,0,0.1); border-radius: 10px; margin: 10px; }
    .user-avatar { width: 60px; height: 60px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; margin: 0 auto 15px; }
    .user-name { font-size: 16px; font-weight: bold; color: white; margin-bottom: 5px; }
    .user-role { display: inline-block; padding: 3px 10px; background: #dc3545; color: white; border-radius: 15px; font-size: 12px; font-weight: bold; }
    .category-badge { display: inline-block; padding: 2px 8px; margin: 2px; border-radius: 10px; background: #e9ecef; color: #495057; font-size: 11px; }
    .category-item { padding: 8px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; transition: 0.2s; }
    .category-item:hover { background: #f8f9fa; border-color: #007bff; }
    .category-item.selected { background: #d4edda; border-color: #28a745; }
    .cat-checkbox { display: none; }
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

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="superadmin.php" class="brand-link">
      <span class="brand-text font-weight-light" style="font-weight: bold !important; font-family: times; color: white !important; text-align: center !important; margin-left: 26px !important; font-size: 25px !important;">SUPER ADMIN</span>
    </a>
    <div class="sidebar">
      <div class="user-info-sidebar">
        <div class="user-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
        <div class="user-name"><?php echo htmlspecialchars($full_name); ?></div>
        <div class="user-role">SUPER ADMIN</div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"><a href="superadmin.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active"><i class="nav-icon fa fa-users"></i><p>User Management<i class="fas fa-angle-left right"></i></p></a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="add_admin.php" class="nav-link"><i class="fa fa-user-plus nav-icon"></i><p>Add Admins</p></a></li>
              <li class="nav-item"><a href="manage_admins.php" class="nav-link"><i class="fa fa-cog nav-icon"></i><p>Manage Admins</p></a></li>
              <li class="nav-item"><a href="assigned_category.php" class="nav-link active"><i class="fas fa-list-alt nav-icon"></i><p>Assign Categories</p></a></li>
              <li class="nav-item"><a href="assigned_product.php" class="nav-link"><i class="fas fa-boxes nav-icon"></i><p>Assign Products</p></a></li>
            </ul> 
          </li>
          <li class="nav-item"><a href="manage_category.php" class="nav-link"><i class="nav-icon fa fa-archive"></i><p>Manage Categories</p></a></li>
          <li class="nav-item"><a href="../backend/logout.php" class="nav-link"><i class="nav-icon fa fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content -->
  <div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Assign Categories</h1></div></div></div></div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Category Assignments</h3>
                        <div class="card-tools"><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#assignModal"><i class="fas fa-plus mr-1"></i> New Assignment</button></div>
                    </div>
                    <div class="card-body">
                        <table id="assignTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Admin Name</th>
                                    <th>Admin ID</th>
                                    <th>Count</th>
                                    <th>Categories</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                while ($row = $assignments_result->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['admin_name']); ?></strong></td>
                                        <td><span class="badge badge-info"><?php echo $row['admin_code']; ?></span></td>
                                        <td><span class="badge badge-success"><?php echo $row['assigned_count']; ?></span></td>
                                        <td>
                                            <?php 
                                            if ($row['category_names']) {
                                                $cats = explode(', ', $row['category_names']);
                                                foreach($cats as $c) echo '<span class="category-badge">'.htmlspecialchars($c).'</span>';
                                            } else { echo '<span class="text-muted">None</span>'; }
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-assign" 
                                                    data-id="<?php echo $row['admin_id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($row['admin_name']); ?>"
                                                    data-cats="<?php echo $row['category_ids']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger remove-assign" data-id="<?php echo $row['admin_id']; ?>">
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
        </div>
      </div>
    </section>
  </div>

  <!-- Assignment Modal -->
  <div class="modal fade" id="assignModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-list-alt mr-2"></i>Assign Categories</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <form id="assignForm">
          <div class="modal-body">
            <div class="form-group">
              <label>Select Admin</label>
              <select name="admin_id" id="modal_admin_id" class="form-control" required>
                <option value="">-- Choose Admin --</option>
                <?php 
                $admins_result->data_seek(0);
                while($a = $admins_result->fetch_assoc()): ?>
                  <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['full_name']); ?> (<?php echo $a['admin_id']; ?>)</option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group">
                <label>Select Categories</label>
                <div class="d-flex flex-wrap" style="gap: 10px;">
                    <?php foreach($all_categories as $cat): ?>
                        <div class="category-item" data-id="<?php echo $cat['id']; ?>">
                            <input type="checkbox" name="category_ids[]" value="<?php echo $cat['id']; ?>" class="cat-checkbox">
                            <i class="fas fa-archive mr-1"></i> <?php echo htmlspecialchars($cat['category_name']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger">Save Assignment</button>
          </div>
        </form>
      </div>
    </div>
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
    $('#assignTable').DataTable();

    $('.category-item').on('click', function() {
        $(this).toggleClass('selected');
        let cb = $(this).find('.cat-checkbox');
        cb.prop('checked', !cb.prop('checked'));
    });

    $('.edit-assign').on('click', function() {
        let adminId = $(this).data('id');
        let cats = $(this).data('cats') ? $(this).data('cats').toString().split(',') : [];
        
        $('#modal_admin_id').val(adminId);
        $('.category-item').removeClass('selected').find('.cat-checkbox').prop('checked', false);
        
        cats.forEach(id => {
            let item = $(`.category-item[data-id="${id}"]`);
            item.addClass('selected');
            item.find('.cat-checkbox').prop('checked', true);
        });
        
        $('#assignModal').modal('show');
    });

    $('#assignForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../api/assign_categories.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                $('#assignModal').modal('hide');
                Swal.fire({icon: 'success', title: 'Assigned!', timer: 1500}).then(() => location.reload());
            } else { Swal.fire('Error', res.message, 'error'); }
        });
    });

    $('.remove-assign').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({title: 'Remove all assignments?', icon: 'warning', showCancelButton: true}).then((res) => {
            if(res.isConfirmed) {
                $.post('../api/assign_categories.php', {admin_id: id, category_ids: []}, function(res) {
                    if(res.status === 'success') Swal.fire({icon: 'success', title: 'Removed!', timer: 1000}).then(() => location.reload());
                });
            }
        });
    });
});
</script>
</body>
</html>

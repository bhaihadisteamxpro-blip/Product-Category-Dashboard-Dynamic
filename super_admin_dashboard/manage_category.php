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

// Fetch all categories with creator info
$query = "SELECT c.*, u.full_name as creator_name 
          FROM categories c 
          LEFT JOIN users u ON c.created_by = u.id 
          ORDER BY c.created_at DESC";
$result = $conn->query($query);

// Count statistics
$stats_query = "SELECT 
                COUNT(*) as total_categories,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_categories,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_categories
                FROM categories";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Categories - Stock Management</title>
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
    
    .user-name { font-size: 16px; font-weight: bold; color: white; margin-bottom: 5px; }
    .user-role { display: inline-block; padding: 3px 10px; background: #dc3545; color: white; border-radius: 15px; font-size: 12px; font-weight: bold; }
    .badge-active { background-color: #28a745; color: white; padding: 5px 10px; border-radius: 15px; }
    .badge-inactive { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 15px; }
    .swal2-popup { font-size: 0.9rem !important; }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="superadmin.php" class="nav-link">Home</a>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fas fa-user-circle fa-lg"></i>
          <span class="ml-2"><?php echo htmlspecialchars($full_name); ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div class="dropdown-header">
            <div class="d-flex align-items-center">
              <div class="user-avatar" style="width: 40px; height: 40px; font-size: 18px; margin: 0;"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
              <div class="ml-3">
                <h6 class="mb-0"><?php echo htmlspecialchars($full_name); ?></h6>
                <small class="text-muted"><?php echo htmlspecialchars($admin_id); ?></small>
              </div>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <a href="super_admin_profile.php" class="dropdown-item"><i class="fas fa-user mr-2"></i> Profile</a>
          <a href="../backend/logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
        </div>
      </li>
    </ul>
  </nav>

  <!-- Main Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="superadmin.php" class="brand-link">
      <span class="brand-text font-weight-light" style="font-weight: bold !important; font-family: times; color: white !important; text-align: center !important; margin-left: 26px !important; font-size: 25px !important;">
        SUPER ADMIN
      </span>
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
            <a href="#" class="nav-link active"><i class="nav-icon fa fa-archive"></i><p>Categories<i class="fas fa-angle-left right"></i></p></a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="add_category.php" class="nav-link"><i class="fa fa-plus nav-icon"></i><p>Add Category</p></a></li>
              <li class="nav-item"><a href="manage_category.php" class="nav-link active"><i class="fa fa-cog nav-icon"></i><p>Manage Categories</p></a></li>
            </ul> 
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="nav-icon fa fa-users"></i><p>User Management<i class="fas fa-angle-left right"></i></p></a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="add_admin.php" class="nav-link"><i class="fa fa-user-plus nav-icon"></i><p>Add Admins</p></a></li>
              <li class="nav-item"><a href="manage_admins.php" class="nav-link"><i class="fa fa-cog nav-icon"></i><p>Manage Admins</p></a></li>
              <li class="nav-item"><a href="assigned_category.php" class="nav-link"><i class="fas fa-list-alt nav-icon"></i><p>Assign Categories</p></a></li>
              <li class="nav-item"><a href="assigned_product.php" class="nav-link"><i class="fas fa-boxes nav-icon"></i><p>Assign Products</p></a></li>
            </ul> 
          </li>
          <li class="nav-item"><a href="../backend/logout.php" class="nav-link"><i class="nav-icon fa fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0">Manage Categories</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="superadmin.php">Home</a></li>
              <li class="breadcrumb-item active">Manage Categories</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <!-- Stats -->
        <div class="row">
          <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo $stats['total_categories']; ?></h3><p>Total</p></div><div class="icon"><i class="fas fa-archive"></i></div></div></div>
          <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?php echo $stats['active_categories']; ?></h3><p>Active</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
          <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?php echo $stats['inactive_categories']; ?></h3><p>Inactive</p></div><div class="icon"><i class="fas fa-ban"></i></div></div></div>
          <div class="col-lg-3 col-6"><div class="small-box bg-primary"><div class="inner"><h3><?php echo date('M Y'); ?></h3><p>Month</p></div><div class="icon"><i class="fas fa-calendar"></i></div></div></div>
        </div>

        <!-- Table -->
        <div class="row">
          <div class="col-12">
            <div class="card card-danger card-outline">
              <div class="card-header">
                <h3 class="card-title">Category List</h3>
                <div class="card-tools"><a href="add_category.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New</a></div>
              </div>
              <div class="card-body">
                <table id="categoryTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Image</th>
                      <th>Name</th>
                      <th>Description</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $counter = 1;
                    while ($row = $result->fetch_assoc()): 
                    ?>
                      <tr id="row-<?php echo $row['id']; ?>">
                        <td><?php echo $counter++; ?></td>
                        <td>
                          <?php if(!empty($row['image'])): ?>
                            <img src="../api/<?php echo htmlspecialchars($row['image']); ?>" width="50" height="50" style="object-fit:cover; border-radius:5px;">
                          <?php else: ?>
                            <span class="text-muted">No Image</span>
                          <?php endif; ?>
                        </td>
                        <td class="cat-name"><strong><?php echo htmlspecialchars($row['category_name']); ?></strong></td>
                        <td class="cat-desc"><?php echo htmlspecialchars($row['category_description']); ?></td>
                        <td>
                          <span class="badge <?php echo $row['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo ucfirst($row['status']); ?>
                          </span>
                        </td>
                        <td>
                          <button class="btn btn-sm btn-info edit-btn" 
                                  data-id="<?php echo $row['id']; ?>" 
                                  data-name="<?php echo htmlspecialchars($row['category_name']); ?>" 
                                  data-desc="<?php echo htmlspecialchars($row['category_description']); ?>"
                                  data-image="<?php echo htmlspecialchars($row['image']); ?>">
                              <i class="fas fa-edit"></i>
                          </button>
                          <button class="btn btn-sm <?php echo $row['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?> toggle-btn" data-id="<?php echo $row['id']; ?>"><i class="fas <?php echo $row['status'] == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i></button>
                          <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>"><i class="fas fa-trash"></i></button>
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

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger">
          <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Category</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="editForm">
          <div class="modal-body">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group"><label>Name</label><input type="text" class="form-control" name="category_name" id="edit_name" required></div>
            <div class="form-group"><label>Description</label><textarea class="form-control" name="category_description" id="edit_desc" rows="3"></textarea></div>
            <div class="form-group">
                <label>Category Image</label>
                <input type="file" class="form-control-file" name="category_image" id="edit_image" accept="image/*">
                <div class="mt-2" id="current_image_preview"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger">Save Changes</button>
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
  $('#categoryTable').DataTable({"responsive": true, "autoWidth": false});

  $('.edit-btn').on('click', function() {
    $('#edit_id').val($(this).data('id'));
    $('#edit_name').val($(this).data('name'));
    $('#edit_desc').val($(this).data('desc'));
    
    // Image Preview
    var img = $(this).data('image');
    if(img) {
      $('#current_image_preview').html('<img src="../api/'+img+'" width="100" style="border-radius:5px; margin-top:5px;">');
    } else {
      $('#current_image_preview').html('<span class="text-muted">No image uploaded</span>');
    }
    
    $('#editModal').modal('show');
  });

  $('#editForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this); // Use FormData for file upload

    $.ajax({
      url: '../api/edit_category.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(res) {
        if(res.status === 'success') {
          $('#editModal').modal('hide');
          Swal.fire({icon: 'success', title: 'Updated!', text: res.message, timer: 1500}).then(() => location.reload());
        } else { Swal.fire('Error', res.message, 'error'); }
      },
      error: function() {
        Swal.fire('Error', 'Request failed', 'error');
      }
    });
  });

  $('.toggle-btn').on('click', function() {
    var id = $(this).data('id');
    Swal.fire({title: 'Change status?', icon: 'warning', showCancelButton: true}).then((res) => {
      if(res.isConfirmed) {
        $.post('../api/toggle_category_status.php', {id: id}, function(res) {
          if(res.status === 'success') Swal.fire({icon: 'success', title: 'Done!', timer: 1000}).then(() => location.reload());
        });
      }
    });
  });

  $('.delete-btn').on('click', function() {
    var id = $(this).data('id');
    Swal.fire({title: 'Delete category?', text: 'This cannot be undone!', icon: 'error', showCancelButton: true}).then((res) => {
      if(res.isConfirmed) {
        $.post('../api/delete_category.php', {id: id}, function(res) {
          if(res.status === 'success') Swal.fire({icon: 'success', title: 'Deleted!', timer: 1000}).then(() => location.reload());
          else Swal.fire('Error', res.message, 'error');
        });
      }
    });
  });
});
</script>
</body>
</html>

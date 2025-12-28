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
$admin_id_code = $_SESSION['admin_id'];

// Fetch all admins
$admins_query = "SELECT id, full_name, admin_id FROM users WHERE role = 'admin' AND status = 'active' ORDER BY full_name";
$admins_result = $conn->query($admins_query);

// Fetch all active categories (for bulk assignment)
$categories_query = "SELECT id, category_name FROM categories WHERE status = 'active' ORDER BY category_name";
$categories_result = $conn->query($categories_query);

// Fetch all active products
$products_query = "SELECT p.id, p.product_name, c.category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'active' ORDER BY c.category_name, p.product_name";
$products_result = $conn->query($products_query);
$all_products = [];
while($p = $products_result->fetch_assoc()) { $all_products[] = $p; }

// Fetch unique assignments (Admins and their product counts)
$summary_query = "SELECT 
                    u.id as admin_id, 
                    u.full_name as admin_name, 
                    u.admin_id as admin_code,
                    COUNT(ap.product_id) as product_count,
                    GROUP_CONCAT(p.product_name SEPARATOR ', ') as product_list,
                    GROUP_CONCAT(p.id SEPARATOR ',') as product_ids
                    FROM users u
                    JOIN admin_products ap ON u.id = ap.admin_id
                    JOIN products p ON ap.product_id = p.id
                    WHERE u.role = 'admin'
                    GROUP BY u.id
                    ORDER BY u.full_name";
$summary_result = $conn->query($summary_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assign Products - Super Admin Dashboard</title>
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
        .prod-badge { display: inline-block; padding: 2px 8px; margin: 2px; border-radius: 10px; background: #e9ecef; font-size: 11px; }
        .product-item { padding: 8px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; height: 100%; transition: 0.2s; }
        .product-item:hover { background: #f8f9fa; border-color: #28a745; }
        .product-item.selected { background: #d4edda; border-color: #28a745; }
        .prod-checkbox { display: none; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar & Sidebar (same as others) -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#"><i class="fas fa-user-circle fa-lg"></i><span class="ml-2"><?php echo htmlspecialchars($full_name); ?></span></a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="../backend/logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                </div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="superadmin.php" class="brand-link"><span class="brand-text font-weight-light" style="font-weight: bold !important; font-family: times; color: white !important; text-align: center !important; margin-left: 26px !important; font-size: 25px !important;">SUPER ADMIN</span></a>
        <div class="sidebar">
            <div class="user-info-sidebar">
                <div class="user-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
                <div class="user-name"><?php echo htmlspecialchars($full_name); ?></div>
                <div class="user-role">SUPER ADMIN</div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item"><a href="superadmin.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                    <li class="nav-item menu-open">
                        <a href="#" class="nav-link active"><i class="nav-icon fa fa-users"></i><p>User Management<i class="fas fa-angle-left right"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="manage_admins.php" class="nav-link"><i class="fa fa-users nav-icon"></i><p>Manage Admins</p></a></li>
                            <li class="nav-item"><a href="assigned_category.php" class="nav-link"><i class="fas fa-list-alt nav-icon"></i><p>Assign Categories</p></a></li>
                            <li class="nav-item"><a href="assigned_product.php" class="nav-link active"><i class="fas fa-boxes nav-icon"></i><p>Assign Products</p></a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="manage_category.php" class="nav-link"><i class="nav-icon fa fa-archive"></i><p>Manage Categories</p></a></li>
                    <li class="nav-item"><a href="../backend/logout.php" class="nav-link"><i class="nav-icon fa fa-sign-out-alt"></i><p>Logout</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1>Assign Products</h1></div></div></div></div>
        
        <section class="content">
            <div class="container-fluid">
                <!-- Bulk Assign Row -->
                <div class="card card-warning card-outline mb-4">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-bolt mr-1"></i> Bulk Assign by Category</h3></div>
                    <div class="card-body">
                        <form id="bulkForm" class="row align-items-end">
                            <div class="col-md-4"><label>Admin</label><select name="admin_id" class="form-control" required><option value="">-- Select --</option><?php $admins_result->data_seek(0); while($a = $admins_result->fetch_assoc()) echo "<option value='{$a['id']}'>{$a['full_name']} ({$a['admin_id']})</option>"; ?></select></div>
                            <div class="col-md-4"><label>Category</label><select name="category_id" class="form-control" required><option value="">-- Select --</option><?php while($c = $categories_result->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['category_name']}</option>"; ?></select></div>
                            <div class="col-md-4"><button type="submit" class="btn btn-warning btn-block">Assign All From Category</button></div>
                        </form>
                    </div>
                </div>

                <!-- Summary Table -->
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Product Assignments</h3>
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
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; while ($row = $summary_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['admin_name']); ?></strong></td>
                                        <td><span class="badge badge-info"><?php echo $row['admin_code']; ?></span></td>
                                        <td><span class="badge badge-success"><?php echo $row['product_count']; ?></span></td>
                                        <td>
                                            <?php 
                                            $prods = explode(', ', $row['product_list']);
                                            foreach(array_slice($prods, 0, 5) as $p) echo '<span class="prod-badge">'.htmlspecialchars($p).'</span>';
                                            if (count($prods) > 5) echo ' <span class="badge badge-light">+'.(count($prods)-5).' more</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-assign" data-id="<?php echo $row['admin_id']; ?>" data-prods="<?php echo $row['product_ids']; ?>"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-danger remove-assign" data-id="<?php echo $row['admin_id']; ?>"><i class="fas fa-trash"></i></button>
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

    <!-- Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white"><h5 class="modal-title">Assign Products</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
                <form id="assignForm">
                    <div class="modal-body">
                        <div class="form-group"><label>Admin</label><select name="admin_id" id="modal_admin_id" class="form-control" required><option value="">-- Select --</option><?php $admins_result->data_seek(0); while($a = $admins_result->fetch_assoc()) echo "<option value='{$a['id']}'>{$a['full_name']}</option>"; ?></select></div>
                        <div class="row" style="max-height: 400px; overflow-y: auto;">
                            <?php 
                            $current_cat = '';
                            foreach($all_products as $p): 
                                if($p['category_name'] !== $current_cat):
                                    $current_cat = $p['category_name'];
                            ?>
                                <div class="col-12 mt-2"><h6 class="font-weight-bold text-dark border-bottom pb-1"><?php echo htmlspecialchars($current_cat); ?></h6></div>
                            <?php endif; ?>
                                <div class="col-md-4 mb-2">
                                    <div class="product-item" data-id="<?php echo $p['id']; ?>">
                                        <input type="checkbox" name="product_ids[]" value="<?php echo $p['id']; ?>" class="prod-checkbox">
                                        <strong><?php echo htmlspecialchars($p['product_name']); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-danger">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    $('#assignTable').DataTable();

    $('.product-item').on('click', function() {
        $(this).toggleClass('selected');
        let cb = $(this).find('.prod-checkbox');
        cb.prop('checked', !cb.prop('checked'));
    });

    $('.edit-assign').on('click', function() {
        let adminId = $(this).data('id');
        let prods = $(this).data('prods') ? $(this).data('prods').toString().split(',') : [];
        $('#modal_admin_id').val(adminId);
        $('.product-item').removeClass('selected').find('.prod-checkbox').prop('checked', false);
        prods.forEach(id => {
            let item = $(`.product-item[data-id="${id}"]`);
            item.addClass('selected').find('.prod-checkbox').prop('checked', true);
        });
        $('#assignModal').modal('show');
    });

    $('#assignForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../api/assign_products.php', $(this).serialize(), function(res) {
            if(res.status === 'success') Swal.fire('Success', res.message, 'success').then(() => location.reload());
            else Swal.fire('Error', res.message, 'error');
        });
    });

    $('#bulkForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../api/bulk_assign_by_category.php', $(this).serialize(), function(res) {
            if(res.status === 'success') Swal.fire('Success', res.message, 'success').then(() => location.reload());
            else Swal.fire('Error', res.message, 'error');
        });
    });

    $('.remove-assign').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({title: 'Remove all products?', icon: 'warning', showCancelButton: true}).then((res) => {
            if(res.isConfirmed) $.post('../api/assign_products.php', {admin_id: id, product_ids: []}, () => location.reload());
        });
    });
});
</script>
</body>
</html>

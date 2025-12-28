<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'super_admin') {
    header('Location: ../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
$full_name = $_SESSION['full_name'];
$admin_id_code = $_SESSION['admin_id'];

// Get Categories for Modal
$categories_res = $conn->query("SELECT id, category_name FROM categories WHERE status='active'");
$all_categories = [];
while($c = $categories_res->fetch_assoc()) { $all_categories[] = $c; }

// Fetch All Products
$query = "SELECT p.*, c.category_name, u.full_name as creator_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.created_by = u.id 
          ORDER BY p.created_at DESC";
$result = $conn->query($query);

// Stats
$stats_query = "SELECT COUNT(*) as total_products, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active_products, SUM(CASE WHEN status='out_of_stock' THEN 1 ELSE 0 END) as oos_products FROM products";
$stats = $conn->query($stats_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Products - Super Admin Dashboard</title>
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
    .stock-badge { padding: 4px 8px; border-radius: 10px; font-size: 12px; }
    .stock-ok { background: #d4edda; color: #155724; }
    .stock-low { background: #fff3cd; color: #856404; }
    .stock-empty { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar & Sidebar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
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
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          <li class="nav-item"><a href="superadmin.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item menu-open">
                <a href="#" class="nav-link active"><i class="nav-icon fa fa-box"></i><p>Products<i class="fas fa-angle-left right"></i></p></a>
                <ul class="nav nav-treeview">
                    <li class="nav-item"><a href="add_product.php" class="nav-link"><i class="fa fa-plus nav-icon"></i><p>Add Product</p></a></li>
                    <li class="nav-item"><a href="manage_products.php" class="nav-link active"><i class="fa fa-cog nav-icon"></i><p>Manage Products</p></a></li>
                </ul>
          </li>
          <li class="nav-item"><a href="manage_category.php" class="nav-link"><i class="nav-icon fa fa-archive"></i><p>Manage Categories</p></a></li>
          <li class="nav-item"><a href="../backend/logout.php" class="nav-link"><i class="nav-icon fa fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1>Manage Products</h1></div></div></div></div>
    
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-4 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo $stats['total_products']; ?></h3><p>Total Products</p></div><div class="icon"><i class="fas fa-box"></i></div></div></div>
          <div class="col-lg-4 col-6"><div class="small-box bg-success"><div class="inner"><h3><?php echo $stats['active_products']; ?></h3><p>Active</p></div><div class="icon"><i class="fas fa-check"></i></div></div></div>
          <div class="col-lg-4 col-12"><div class="small-box bg-warning"><div class="inner"><h3><?php echo $stats['oos_products']; ?></h3><p>Out of Stock</p></div><div class="icon"><i class="fas fa-exclamation-triangle"></i></div></div></div>
        </div>

        <div class="card card-outline card-danger">
          <div class="card-header"><h3 class="card-title">All Products</h3><div class="card-tools"><a href="add_product.php" class="btn btn-primary btn-sm">Add New Product</a></div></div>
          <div class="card-body">
            <table id="prodTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Product Name</th>
                  <th>Image</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php $counter = 1; while ($row = $result->fetch_assoc()): 
                    $stock_class = ($row['quantity'] <= 0) ? 'stock-empty' : (($row['quantity'] <= $row['min_stock']) ? 'stock-low' : 'stock-ok');
                ?>
                  <tr id="row-<?php echo $row['id']; ?>">
                    <td><?php echo $counter++; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['product_name']); ?></strong><br><small><?php echo htmlspecialchars($row['sku']); ?></small></td>
                    <td>
                        <?php if(!empty($row['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($row['image']); ?>" alt="Prod Img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <?php else: ?>
                            <span class="text-muted">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['category_name'] ?? 'None'); ?></td>
                    <td>₹<?php echo number_format($row['price'], 2); ?></td>
                    <td><span class="stock-badge <?php echo $stock_class; ?>"><?php echo $row['quantity']; ?> <?php echo $row['unit']; ?></span></td>
                    <td><span class="badge <?php echo $row['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td>
                      <button class="btn btn-sm btn-info edit-prod" 
                              data-id="<?php echo $row['id']; ?>" 
                              data-name="<?php echo htmlspecialchars($row['product_name']); ?>" 
                              data-sku="<?php echo htmlspecialchars($row['sku']); ?>" 
                              data-cat="<?php echo $row['category_id']; ?>" 
                              data-price="<?php echo $row['price']; ?>" 
                              data-qty="<?php echo $row['quantity']; ?>" 
                              data-unit="<?php echo $row['unit']; ?>" 
                              data-min="<?php echo $row['min_stock']; ?>" 
                              data-min="<?php echo $row['min_stock']; ?>" 
                              data-desc="<?php echo htmlspecialchars($row['product_description']); ?>"
                              data-image="<?php echo htmlspecialchars($row['image']); ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm <?php echo $row['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?> toggle-status" data-id="<?php echo $row['id']; ?>">
                        <i class="fas <?php echo $row['status'] == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                      </button>
                      <button class="btn btn-sm btn-danger delete-prod" data-id="<?php echo $row['id']; ?>"><i class="fas fa-trash"></i></button>
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

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-danger text-white"><h5 class="modal-title">Edit Product</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
    <form id="editForm"><div class="modal-body row">
        <input type="hidden" name="id" id="edit_id">
        <div class="col-md-6 form-group"><label>Product Name</label><input type="text" name="product_name" id="edit_name" class="form-control" required></div>
        <div class="col-md-6 form-group"><label>SKU</label><input type="text" name="sku" id="edit_sku" class="form-control"></div>
        <div class="col-md-6 form-group"><label>Category</label><select name="category_id" id="edit_cat" class="form-control"><?php foreach($all_categories as $c) echo "<option value='{$c['id']}'>{$c['category_name']}</option>"; ?></select></div>
        <div class="col-md-6 form-group"><label>Price (₹)</label><input type="number" step="0.01" name="price" id="edit_price" class="form-control"></div>
        <div class="col-md-4 form-group"><label>Quantity</label><input type="number" name="quantity" id="edit_qty" class="form-control"></div>
        <div class="col-md-4 form-group"><label>Unit</label><input type="text" name="unit" id="edit_unit" class="form-control"></div>
        <div class="col-md-4 form-group"><label>Min Stock</label><input type="number" name="min_stock" id="edit_min" class="form-control"></div>
        
        <div class="col-md-12 form-group">
            <label>Product Image</label>
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="edit_image" name="product_image" accept="image/*">
                    <label class="custom-file-label" for="edit_image">Choose new file</label>
                </div>
            </div>
            <div id="image_preview_container" class="mt-2" style="display:none;">
                <label>Current Image:</label>
                <img id="edit_img_preview" src="" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
            </div>
        </div>

        <div class="col-12 form-group"><label>Description</label><textarea name="product_description" id="edit_desc" class="form-control" rows="3"></textarea></div>
    </div><div class="modal-footer"><button type="submit" class="btn btn-danger">Update Product</button></div></form>
  </div></div></div>

  <footer class="main-footer text-center"><strong>Al Hadi Solutions &copy; 2024</strong></footer>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    $('#prodTable').DataTable();

    $('.edit-prod').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_sku').val($(this).data('sku'));
        $('#edit_cat').val($(this).data('cat'));
        $('#edit_price').val($(this).data('price'));
        $('#edit_qty').val($(this).data('qty'));
        $('#edit_unit').val($(this).data('unit'));
        $('#edit_min').val($(this).data('min'));
        $('#edit_desc').val($(this).data('desc'));
        
        var imgPath = $(this).data('image');
        if(imgPath) {
            $('#edit_img_preview').attr('src', '../' + imgPath);
            $('#image_preview_container').show();
        } else {
            $('#image_preview_container').hide();
        }
        
        $('#editModal').modal('show');
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: '../api/edit_product.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if(res.status === 'success') Swal.fire('Success', res.message, 'success').then(() => location.reload());
                else Swal.fire('Error', res.message, 'error');
            }
        });
    });

    $('.toggle-status').on('click', function() {
        let id = $(this).data('id');
        $.post('../api/toggle_product_status.php', {id: id}, () => location.reload());
    });

    $('.delete-prod').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({title: 'Delete product?', icon: 'error', showCancelButton: true}).then((res) => {
            if(res.isConfirmed) $.post('../api/delete_product.php', {id: id}, () => location.reload());
        });
    });
});
</script>
</body>
</html>

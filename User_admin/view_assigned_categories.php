<?php
// User_admin/view_assigned_categories.php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../frontend/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$admin_id_str = $_SESSION['admin_id'];
$department = $_SESSION['department'] ?? 'General';

// Fetch assigned categories for current admin
$query = "SELECT c.id, c.category_name, c.category_description, c.image, c.status, 
                (SELECT COUNT(*) FROM products p 
                 INNER JOIN admin_products ap ON p.id = ap.product_id 
                 WHERE p.category_id = c.id AND p.status = 'active' AND ap.admin_id = $user_id) as active_products
          FROM categories c
          INNER JOIN admin_categories ac ON c.id = ac.category_id
          WHERE ac.admin_id = $user_id AND c.status = 'active'
          ORDER BY c.category_name";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Assigned Categories | Admin</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><a class="nav-link" href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </nav>

  <?php include 'sidebar.php'; ?>

  <!-- Content -->
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1><i class="fas fa-archive mr-2"></i>Assigned Categories</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="Admin.php">Home</a></li>
              <li class="breadcrumb-item active">Assigned Categories</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    
    <section class="content">
      <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm">
          <div class="card-header">
            <h3 class="card-title">List of categories you are responsible for</h3>
            <div class="card-tools">
              <span class="badge badge-primary"><?php echo $result->num_rows; ?> Assigned</span>
            </div>
          </div>
          <div class="card-body">
            <table class="table table-hover text-nowrap valign-middle">
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Category Name</th>
                  <th>Description</th>
                  <th>Active Products</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <?php
                    // Check if image path needs 'api' prefix if not present
                    if (!empty($row['image'])) {
                         $img_path = (strpos($row['image'], 'api/') === false) ? '../api/' . $row['image'] : '../' . $row['image'];
                    } else {
                         $img_path = '../assets/img/no-image.png';
                    }
                ?>
                <tr>
                  <td>
                    <div style="width: 50px; height: 50px; overflow: hidden; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <img src="<?php echo $img_path; ?>" alt="Cat" style="width: 100%; height: 100%; object-fit: cover;" class="img-zoom" title="Click to Zoom">
                    </div>
                  </td>
                  <td><strong style="color: #007bff;"><?php echo htmlspecialchars($row['category_name']); ?></strong></td>
                  <td><span class="text-muted" style="max-width: 200px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['category_description']); ?></span></td>
                  <td><span class="badge badge-info" style="font-size: 14px; padding: 6px 12px; border-radius: 20px;"><?php echo $row['active_products']; ?> Products</span></td>
                  <td><span class="badge badge-success" style="font-size: 12px;"><?php echo ucfirst($row['status']); ?></span></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Zoom Modal -->
  <div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="background: transparent; border: none; box-shadow: none;">
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid shadow-lg" style="border-radius: 10px; max-height: 80vh;">
                <button type="button" class="btn btn-light mt-3" data-dismiss="modal"><i class="fas fa-times mr-2"></i>Close</button>
            </div>
        </div>
    </div>
  </div>

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block"><b>Version</b> 1.0.0</div>
    <strong>Copyright &copy; 2024 <a href="#">Al Hadi Solutions</a>.</strong> All rights reserved.
  </footer>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    $('.img-zoom').css('cursor', 'zoom-in');
    
    $('.img-zoom').on('click', function() {
        var src = $(this).attr('src');
        $('#modalImage').attr('src', src);
        $('#imageModal').modal('show');
    });
});
</script>
</body>
</html>

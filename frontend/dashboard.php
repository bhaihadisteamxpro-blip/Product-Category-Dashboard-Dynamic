<?php
require_once __DIR__ . '/../backend/auth.php';
checkAuth('super_admin');
?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Super Admin Dashboard</h1></div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner"><h3 id="total-categories">0</h3><p>Categories</p></div>
                        <div class="icon"><i class="fas fa-archive"></i></div>
                        <a href="manage_categories.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner"><h3 id="total-products">0</h3><p>Products</p></div>
                        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                        <a href="manage_products.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {
    // Load dashboard stats
    $.ajax({
        url: '../api/get_categories.php',
        success: function(response) {
            if(response.status === 'success') {
                $('#total-categories').text(response.stats.total_categories);
            }
        }
    });

    $.ajax({
        url: '../api/get_products.php',
        success: function(response) {
            if(response.status === 'success') {
                $('#total-products').text(response.data.length);
            }
        }
    });
});
</script>

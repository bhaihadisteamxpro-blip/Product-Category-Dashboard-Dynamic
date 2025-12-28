<?php
require_once __DIR__ . '/../backend/auth.php';
checkAuth('admin');
?>
<?php include 'header.php'; ?>
// We should check role here specifically for admin if needed, 
// but header.php currently checks for super_admin.
// For now, let's just make it a simple page.
?>
<?php include 'sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Admin Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Welcome Admin</h3>
                        </div>
                        <div class="card-body">
                            <p>You are logged in as an Admin. Your specific permissions will be shown here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

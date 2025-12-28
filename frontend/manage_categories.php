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
                <div class="col-sm-6"><h1 class="m-0">Manage Categories</h1></div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Stats -->
            <div class="row" id="stats-container">
                <!-- Stats will be loaded via AJAX -->
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list-alt mr-2"></i>All Categories</h3>
                            <div class="card-tools">
                                <a href="add_category.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="categoriesTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Category Name</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Created Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoriesBody">
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {
    loadCategories();

    function loadCategories() {
        $.ajax({
            url: '../api/get_categories.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    renderStats(response.stats);
                    renderCategories(response.data);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Error loading categories');
            }
        });
    }

    function renderStats(stats) {
        let statsHtml = `
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner"><h3>${stats.total_categories || 0}</h3><p>Total Categories</p></div>
                    <div class="icon"><i class="fas fa-archive"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner"><h3>${stats.active_categories || 0}</h3><p>Active Categories</p></div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner"><h3>${stats.inactive_categories || 0}</h3><p>Inactive Categories</p></div>
                    <div class="icon"><i class="fas fa-ban"></i></div>
                </div>
            </div>
        `;
        $('#stats-container').html(statsHtml);
    }

    function renderCategories(data) {
        let rows = '';
        data.forEach((row, index) => {
            rows += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${row.category_name}</strong></td>
                    <td>${row.category_description || '<span class="text-muted">No description</span>'}</td>
                    <td>
                        <span class="category-badge ${row.status === 'active' ? 'badge-active' : 'badge-inactive'}">
                            ${row.status.charAt(0).toUpperCase() + row.status.slice(1)}
                        </span>
                    </td>
                    <td>${row.creator_name || 'System'}</td>
                    <td>${row.created_at}</td>
                    <td>
                        <button class="btn btn-sm btn-info edit-btn" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm ${row.status === 'active' ? 'btn-warning' : 'btn-success'} toggle-btn" data-id="${row.id}">
                            <i class="fas ${row.status === 'active' ? 'fa-ban' : 'fa-check'}"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
        });
        $('#categoriesBody').html(rows);
        
        // Re-initialize DataTable if needed
        if ($.fn.DataTable.isDataTable('#categoriesTable')) {
            $('#categoriesTable').DataTable().destroy();
        }
        $('#categoriesTable').DataTable();
    }

    // Toggle Status
    $(document).on('click', '.toggle-btn', function() {
        let id = $(this).data('id');
        $.ajax({
            url: '../api/toggle_category_status.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadCategories();
                } else {
                    alert(response.message);
                }
            }
        });
    });

    // Delete Category
    $(document).on('click', '.delete-btn', function() {
        if (confirm('Are you sure you want to delete this category?')) {
            let id = $(this).data('id');
            $.ajax({
                url: '../api/delete_category.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        loadCategories();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }
    });
});
</script>

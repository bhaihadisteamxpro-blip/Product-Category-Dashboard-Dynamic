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
                <div class="col-sm-6"><h1 class="m-0">Add New Category</h1></div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div id="alert-container"></div>
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-archive"></i> Category Information</h3>
                        </div>
                        <form id="addCategoryForm">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="category_name">Category Name</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" placeholder="Enter category name" required>
                                </div>
                                <div class="form-group">
                                    <label for="category_description">Description</label>
                                    <textarea class="form-control" id="category_description" name="category_description" rows="3" placeholder="Enter category description"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control select2" id="status" name="status" style="width: 100%;">
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Category</button>
                                <button type="reset" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</button>
                                <a href="manage_categories.php" class="btn btn-default float-right"><i class="fas fa-list"></i> View All</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            url: '../api/add_category.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#alert-container').html(`
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h5><i class="icon fas fa-check"></i> Success!</h5>
                            ${response.message}
                        </div>
                    `);
                    $('#addCategoryForm')[0].reset();
                    $('.select2').trigger('change');
                } else {
                    $('#alert-container').html(`
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                            ${response.message}
                        </div>
                    `);
                }
            },
            error: function() {
                alert('An error occurred while adding the category');
            }
        });
    });
});
</script>

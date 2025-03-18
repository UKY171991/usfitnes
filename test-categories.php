<?php include('conn.php'); ?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Categories Management | AdminLTE 4</title>
    <?php include('inc/head.php'); ?>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <?php include('inc/top.php'); ?>
    <?php include('inc/sidebar.php'); ?>

    <main class="app-main">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 mt-2">
                    <div class="col-sm-6">
                        <h3>Test Categories</h3>
                    </div>
                    <div class="col-sm-6 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="app-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Test Categories List</h3>
                    </div>
                    <div class="card-body">
                        <div id="categoryList"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for Adding Category -->
    <div class="modal fade" id="newCategoryModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Add Test Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="category_name" class="form-control" placeholder="Category Name" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveCategory">Save Category</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('inc/footer.php'); ?>
</div>

<?php include('inc/js.php'); ?>

<script>
$(document).ready(function(){

    // Fetch and display categories
    function loadCategories(){
        $.ajax({
            url: 'includes/fetch_test_categories.php',
            type: 'GET',
            success: function(data){
                $('.card-body').html(data);
            }
        });
    }

    //loadCategories();

    // Save new category using AJAX
    $('#saveCategory').click(function(){
        var categoryName = $('#category_name').val();

        if(categoryName != ''){
        	alert(categoryName);
        	//return false;

            $.ajax({
                url: 'includes/insert_test_category.php',
                type: 'POST',
                data: {category_name: category_name},
                success: function(response){
                    $('#newCategoryModal').modal('hide');
                    loadCategories();
                }
            });
        }
    });

    // Initial category loading
    loadCategories();
});
</script>

</body>
</html>

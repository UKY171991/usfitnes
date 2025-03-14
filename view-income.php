<?php
include 'inc/auth.php';
include 'inc/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Income</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php include 'inc/sidebar.php'; ?>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include 'inc/topbar.php'; ?>

        <div class="container-fluid py-4">
            
            <div class="row">
                <div class="col-6">
                    <h4 class="text-dark">Income Records</h4>
                </div>
                <div class="col-6 text-end">
                    <a href="add-income.php" class="btn btn-dark">
                        <i class="fa fa-plus"></i> Add Income
                    </a>
                </div>
            </div>

            <!-- Display Messages -->
            <div class="row">
                <div class="col-md-12 mx-auto">
                    <?php if (isset($_SESSION['success_msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success_msg']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_msg']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_msg'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_msg']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_msg']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">All Income Entries</h6>
                </div>

                <div class="card-body px-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th> 
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Actual Amount</th>
                                    <th>Received Amount</th>
                                    <th>Balance Amount</th>
                                    <th>Revenue</th> <!-- Added Revenue Column -->
                                    <th>Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT 
                                            i.id, 
                                            i.name, 
                                            i.phone,  
                                            i.description, 
                                            c.category_name, 
                                            s.subcategory_name, 
                                            i.actual_amount, 
                                            i.received_amount, 
                                            i.balance_amount, 
                                            i.revenue, 
                                            i.entry_date 
                                          FROM income i
                                          INNER JOIN income_categories c ON i.category_id = c.id
                                          INNER JOIN income_subcategories s ON i.subcategory_id = s.id
                                          ORDER BY i.id DESC";
                                $result = mysqli_query($conn, $query);
                                $count = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Format date from YYYY-MM-DD to DD-MM-YYYY
                                    $formatted_date = date("d-m-Y", strtotime($row['entry_date']));

                                    echo "<tr>
                                            <td>{$count}</td>
                                            <td>{$row['name']}</td>
                                            <td>{$row['phone']}</td> 
                                            <td>{$row['category_name']}</td>
                                            <td>{$row['subcategory_name']}</td>
                                            <td>{$row['actual_amount']}</td>
                                            <td>{$row['received_amount']}</td>
                                            <td>{$row['balance_amount']}</td>
                                            <td>{$row['revenue']}</td> <!-- Added Revenue Data -->
                                            <td>{$formatted_date}</td> <!-- Fixed Date Format -->
                                            <td class='text-center'>
                                                <a href='edit-income.php?id={$row['id']}' class='badge bg-gradient-success'><i class='fa fa-edit'></i> Edit</a>
                                                <a href='delete-income.php?id={$row['id']}' class='badge bg-gradient-danger' onclick='return confirm(\"Are you sure?\")'><i class='fa fa-trash'></i> Delete</a>
                                            </td>
                                        </tr>";
                                    $count++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div> <!-- End of .table-responsive -->
                </div> <!-- End of .card-body -->
            </div> <!-- End of .card -->
        </div> <!-- End of .container-fluid -->
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="assets/js/material-dashboard.min.js?v=3.2.0"></script>

    <script>
        $(document).ready(function() {
            // Remove alert after 5 seconds (5000 milliseconds)
            setTimeout(function() {
                $(".alert").fadeOut("slow");
            }, 3000);
        });
    </script>
</body>
</html>

<?php
include 'inc/auth.php';
include 'inc/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Expenditure</title>
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
                    <h4 class="text-dark">Expenditure Records</h4>
                </div>
                <div class="col-6 text-end">
                    <a href="add-expenditure.php" class="btn btn-dark">
                        <i class="fa fa-plus"></i> Add Expenditure
                    </a>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">All Expenditure Entries</h6>
                </div>

                <div class="card-body px-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Actual Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Balance Amount</th>
                                    <th>Date of Entry</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch records from database
                                $query = "SELECT 
                                            e.id, 
                                            e.name, 
                                            e.phone,  
                                            e.description, 
                                            c.category_name, 
                                            s.subcategory_name, 
                                            e.actual_amount, 
                                            e.paid_amount, 
                                            (e.actual_amount - e.paid_amount) AS balance_amount,
                                            e.entry_date 
                                          FROM expenditure e
                                          INNER JOIN expenditure_categories c ON e.category_id = c.id
                                          INNER JOIN expenditure_subcategories s ON e.subcategory_id = s.id
                                          ORDER BY e.id DESC";

                                $result = $conn->query($query);

                                if ($result->num_rows > 0) {
                                    $count = 1;
                                    while ($row = $result->fetch_assoc()) {
                                        $formatted_date = date("d-m-Y", strtotime($row['entry_date']));
                                        echo "<tr>
                                                <td>{$count}</td>
                                                <td>{$row['name']}</td>
                                                <td>{$row['phone']}</td>
                                                <td>{$row['description']}</td>
                                                <td>{$row['category_name']}</td>
                                                <td>{$row['subcategory_name']}</td>
                                                <td>{$row['actual_amount']}</td>
                                                <td>{$row['paid_amount']}</td>
                                                <td>{$row['balance_amount']}</td>
                                                <td>{$formatted_date}</td>
                                                <td class='text-center'>
                                                    <a href='edit-expenditure.php?id={$row['id']}' class='badge bg-gradient-success'><i class='fa fa-edit'></i> Edit</a>
                                                    <a href='delete-expenditure.php?id={$row['id']}' class='badge bg-gradient-danger' onclick='return confirm(\"Are you sure?\")'><i class='fa fa-trash'></i> Delete</a>
                                                </td>
                                            </tr>";
                                        $count++;
                                    }
                                } else {
                                    echo "<tr><td colspan='11' class='text-center text-muted'>No records found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
            setTimeout(function() {
                $(".alert").fadeOut("slow");
            }, 3000);
        });
    </script>
</body>
</html>

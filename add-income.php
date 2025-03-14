<?php
include 'inc/auth.php';
include 'inc/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Income</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  	<link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  	<link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  	<script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  	<link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link  href="assets/css/style.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php include 'inc/sidebar.php'; ?>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include 'inc/topbar.php'; ?>

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <h4 class="text-dark">Add Income</h4>
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

            <!-- Add Income Form -->
            <div class="row mt-4 d-flex justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-lg">
                        <div class="card-header bg-gradient-dark text-white d-flex align-items-center">
                            <!-- <i class="material-symbols-rounded me-2">attach_money</i> -->
                            <h6 class="mb-0 text-white">Add New Income</h6>
                        </div>
                        <div class="card-body">
                            <form action="process-income.php" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control border" name="name" id="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control border" name="phone" id="phone" pattern="[0-9]{10}" maxlength="10" required>
                                            <small class="text-danger" id="phoneError" style="display: none;">Phone number must be exactly 10 digits.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select class="form-control border" name="category_id" id="category" required>
                                                <option value="">-- Select Category --</option>
                                                <?php
                                                $query = "SELECT * FROM income_categories ORDER BY category_name ASC";
                                                $result = mysqli_query($conn, $query);
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<option value='{$row['id']}'>{$row['category_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Sub-Category</label>
                                            <select class="form-control border" name="subcategory_id" id="subcategory" required>
                                                <option value="">-- Select Sub-Category --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Actual Amount</label>
                                            <input type="number" class="form-control border no-spinner" name="actual_amount" id="actualAmount" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Received Amount</label>
                                            <input type="number" class="form-control border no-spinner" name="received_amount" id="receivedAmount" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Balance Amount</label>
                                            <input type="text" class="form-control border" id="balanceAmount" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date of Entry</label>
                                            <input type="text" class="form-control border" name="date_of_entry" id="datepicker" required autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Revenue</label>
                                            <input type="number" class="form-control border no-spinner" name="revenue" id="revenue" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control border" name="description" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            
                                
                                <div class="text-end">
                                    <button type="submit" class="btn bg-gradient-dark">
                                         Submit
                                    </button>
                                </div>
                            </form>
                        </div>
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
        document.getElementById("category").addEventListener("change", function () {
            let categoryId = this.value;
            let subcategoryDropdown = document.getElementById("subcategory");
            subcategoryDropdown.innerHTML = "<option>Loading...</option>";

            fetch("fetch-subcategories.php?category_id=" + categoryId)
                .then(response => response.text())
                .then(data => {
                    subcategoryDropdown.innerHTML = data;
                });
        });

        document.getElementById("receivedAmount").addEventListener("input", function () {
            let actualAmount = parseFloat(document.getElementById("actualAmount").value) || 0;
            let receivedAmount = parseFloat(this.value) || 0;
            document.getElementById("balanceAmount").value = actualAmount - receivedAmount;
        });
    </script>

    <script>
        document.getElementById("phone").addEventListener("input", function () {
            let phoneField = this;
            let phoneError = document.getElementById("phoneError");
            let phoneValue = phoneField.value.replace(/\D/g, ''); // Remove non-numeric characters

            if (phoneValue.length > 10) {
                phoneValue = phoneValue.substring(0, 10); // Restrict input to 10 digits
            }

            phoneField.value = phoneValue; // Update field with numeric-only value

            // Show error if the length is not exactly 10
            if (phoneValue.length === 10) {
                phoneError.style.display = "none";
            } else {
                phoneError.style.display = "block";
            }
        });
    </script>

    <script>
        document.getElementById("name").addEventListener("input", function () {
            let words = this.value.split(" ");
            for (let i = 0; i < words.length; i++) {
                if (words[i].length > 0) {
                    words[i] = words[i][0].toUpperCase() + words[i].substr(1).toLowerCase();
                }
            }
            this.value = words.join(" "); // Update input with capitalized first letters
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#datepicker").datepicker({
                dateFormat: "dd-mm-yy", // Set format to dd-mm-yyyy
                changeMonth: true,  // Allows month selection
                changeYear: true,   // Allows year selection
                yearRange: "1900:+10" // Allows selection from 1900 to 10 years ahead
            });
        });
    </script>



</body>
</html>

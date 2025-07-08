<?php
require_once 'includes/init.php';
if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

include 'includes/head.php';
include 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Class Bookings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Bookings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
             <div id="alert-container"></div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Class Bookings</h3>
                </div>
                <div class="card-body">
                    <table id="bookingsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Class Name</th>
                                <th>Member Name</th>
                                <th>Class Date</th>
                                <th>Booking Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data is loaded here by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable({
        "ajax": {
            "url": "api/test_orders_api.php?action=get_bookings",
            "type": "GET",
            "dataType": "json",
            "dataSrc": function(json) {
                if (!json.success) {
                    $('#alert-container').html('<div class="alert alert-danger">Failed to load bookings: ' + json.message + '</div>');
                    return [];
                }
                return json.data;
            },
            "error": function (xhr, error, thrown) {
                 $('#alert-container').html('<div class="alert alert-danger">AJAX error: ' + error + '</div>');
            }
        },
        "columns": [
            { "data": "booking_id" },
            { "data": "class_name" },
            { "data": "member_name" },
            { "data": "class_date" },
            { "data": "booking_date" }
        ],
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
    });
});
</script>

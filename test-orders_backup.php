<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Orders - Pathology Dashboard</title>
    <meta name="description" content="Pathology Test Orders Management Dashboard">
    <meta name="keywords" content="pathology, test orders, laboratory, medical tests">
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .dashboard-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .content-section {
            padding: 2rem;
        }
        .action-buttons {
            margin-bottom: 2rem;
        }
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .btn-custom:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .table thead th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem;
        }
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid #f8f9fa;
        }
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background: #d4edda;
            color: #155724;
        }
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .action-btn {
            margin: 0 2px;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            border: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        .btn-edit {
            background: #28a745;
            color: white;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .modal-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
        }
        .modal-header .close {
            color: white;
            opacity: 0.8;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 2rem;
            }
            .content-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1 class="dashboard-title"><i class="fas fa-flask mr-3"></i>Pathology Test Orders</h1>
            <p class="dashboard-subtitle">Manage and track laboratory test orders efficiently</p>
        </div>

        <!-- Main Content -->
        <div class="content-section">
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-custom" data-toggle="modal" data-target="#addTestModal">
                    <i class="fas fa-plus mr-2"></i>Add New Test Order
                </button>
                <button class="btn btn-outline-secondary ml-2" onclick="refreshTable()">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
                <button class="btn btn-outline-info ml-2" onclick="exportData()">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>

            <!-- Test Orders Table -->
            <div class="table-container">
                <table id="testOrdersTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Patient Name</th>
                            <th>Test Type</th>
                            <th>Order Date</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Doctor</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="testOrdersBody">
                        <!-- Sample Data -->
                        <tr>
                            <td>TO-001</td>
                            <td>John Smith</td>
                            <td>Blood Glucose</td>
                            <td>2025-07-09</td>
                            <td><span class="badge badge-warning">High</span></td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td>Dr. Johnson</td>
                            <td>
                                <button class="action-btn btn-view" onclick="viewTest('TO-001')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn btn-edit" onclick="editTest('TO-001')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn btn-delete" onclick="deleteTest('TO-001')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>TO-002</td>
                            <td>Maria Garcia</td>
                            <td>Complete Blood Count</td>
                            <td>2025-07-08</td>
                            <td><span class="badge badge-info">Normal</span></td>
                            <td><span class="status-badge status-processing">Processing</span></td>
                            <td>Dr. Williams</td>
                            <td>
                                <button class="action-btn btn-view" onclick="viewTest('TO-002')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn btn-edit" onclick="editTest('TO-002')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn btn-delete" onclick="deleteTest('TO-002')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>TO-003</td>
                            <td>Robert Davis</td>
                            <td>Lipid Profile</td>
                            <td>2025-07-07</td>
                            <td><span class="badge badge-success">Low</span></td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                            <td>Dr. Brown</td>
                            <td>
                                <button class="action-btn btn-view" onclick="viewTest('TO-003')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn btn-edit" onclick="editTest('TO-003')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn btn-delete" onclick="deleteTest('TO-003')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>TO-004</td>
                            <td>Lisa Wilson</td>
                            <td>Thyroid Function</td>
                            <td>2025-07-06</td>
                            <td><span class="badge badge-warning">High</span></td>
                            <td><span class="status-badge status-cancelled">Cancelled</span></td>
                            <td>Dr. Miller</td>
                            <td>
                                <button class="action-btn btn-view" onclick="viewTest('TO-004')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn btn-edit" onclick="editTest('TO-004')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn btn-delete" onclick="deleteTest('TO-004')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Test Order Modal -->
    <div class="modal fade" id="addTestModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Add New Test Order</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addTestForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patientName">Patient Name</label>
                                    <input type="text" class="form-control" id="patientName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patientAge">Patient Age</label>
                                    <input type="number" class="form-control" id="patientAge" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="testType">Test Type</label>
                                    <select class="form-control" id="testType" required>
                                        <option value="">Select Test Type</option>
                                        <option value="Blood Glucose">Blood Glucose</option>
                                        <option value="Complete Blood Count">Complete Blood Count</option>
                                        <option value="Lipid Profile">Lipid Profile</option>
                                        <option value="Thyroid Function">Thyroid Function</option>
                                        <option value="Liver Function">Liver Function</option>
                                        <option value="Kidney Function">Kidney Function</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <select class="form-control" id="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="Low">Low</option>
                                        <option value="Normal">Normal</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="doctor">Ordering Doctor</label>
                                    <input type="text" class="form-control" id="doctor" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="orderDate">Order Date</label>
                                    <input type="date" class="form-control" id="orderDate" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" rows="3" placeholder="Additional notes or instructions"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-custom" onclick="addTestOrder()">Add Test Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Test Modal -->
    <div class="modal fade" id="viewTestModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye mr-2"></i>Test Order Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewTestContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#testOrdersTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[3, 'desc']], // Sort by date
                language: {
                    search: "Search orders:",
                    lengthMenu: "Show _MENU_ orders per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ orders"
                }
            });

            // Configure toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "timeOut": "3000",
                "positionClass": "toast-top-right"
            };

            // Set today's date as default
            document.getElementById('orderDate').value = new Date().toISOString().split('T')[0];
        });

        // Add new test order
        function addTestOrder() {
            const form = document.getElementById('addTestForm');
            const formData = new FormData(form);
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Get form values
            const patientName = document.getElementById('patientName').value;
            const testType = document.getElementById('testType').value;
            const priority = document.getElementById('priority').value;
            const doctor = document.getElementById('doctor').value;
            const orderDate = document.getElementById('orderDate').value;

            // Generate order ID
            const orderId = 'TO-' + String(Date.now()).slice(-6);

            // Add to table (in real app, this would be an API call)
            const table = $('#testOrdersTable').DataTable();
            const priorityBadge = `<span class="badge badge-${getPriorityClass(priority)}">${priority}</span>`;
            const statusBadge = '<span class="status-badge status-pending">Pending</span>';
            const actionButtons = `
                <button class="action-btn btn-view" onclick="viewTest('${orderId}')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="action-btn btn-edit" onclick="editTest('${orderId}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-btn btn-delete" onclick="deleteTest('${orderId}')">
                    <i class="fas fa-trash"></i>
                </button>
            `;

            table.row.add([
                orderId,
                patientName,
                testType,
                orderDate,
                priorityBadge,
                statusBadge,
                doctor,
                actionButtons
            ]).draw();

            // Close modal and reset form
            $('#addTestModal').modal('hide');
            form.reset();
            document.getElementById('orderDate').value = new Date().toISOString().split('T')[0];

            // Show success message
            toastr.success('Test order added successfully!', 'Success');
        }

        // View test details
        function viewTest(orderId) {
            // In real app, this would fetch data from API
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <p><strong>Order ID:</strong> ${orderId}</p>
                        <p><strong>Status:</strong> <span class="status-badge status-pending">Pending</span></p>
                        <p><strong>Priority:</strong> <span class="badge badge-warning">High</span></p>
                        <p><strong>Order Date:</strong> 2025-07-09</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Patient Information</h6>
                        <p><strong>Name:</strong> John Smith</p>
                        <p><strong>Age:</strong> 45</p>
                        <p><strong>Test Type:</strong> Blood Glucose</p>
                        <p><strong>Doctor:</strong> Dr. Johnson</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Notes</h6>
                        <p>Patient is fasting. Please collect sample in the morning.</p>
                    </div>
                </div>
            `;
            
            document.getElementById('viewTestContent').innerHTML = content;
            $('#viewTestModal').modal('show');
            toastr.info(`Viewing details for ${orderId}`, 'Information');
        }

        // Edit test order
        function editTest(orderId) {
            toastr.info(`Edit functionality for ${orderId} would open here`, 'Edit Test');
        }

        // Delete test order
        function deleteTest(orderId) {
            if (confirm(`Are you sure you want to delete test order ${orderId}?`)) {
                // Find and remove row from DataTable
                const table = $('#testOrdersTable').DataTable();
                table.rows().every(function() {
                    const data = this.data();
                    if (data[0] === orderId) {
                        this.remove();
                    }
                });
                table.draw();
                
                toastr.success(`Test order ${orderId} deleted successfully!`, 'Deleted');
            }
        }

        // Refresh table
        function refreshTable() {
            $('#testOrdersTable').DataTable().ajax.reload();
            toastr.success('Table refreshed successfully!', 'Refreshed');
        }

        // Export data
        function exportData() {
            toastr.info('Export functionality would be implemented here', 'Export');
        }

        // Helper function for priority badge classes
        function getPriorityClass(priority) {
            switch(priority) {
                case 'Low': return 'success';
                case 'Normal': return 'info';
                case 'High': return 'warning';
                case 'Urgent': return 'danger';
                default: return 'secondary';
            }
        }
    </script>
</body>
</html>

<?php
require_once 'config.php';
require_once 'includes/adminlte_template.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

renderTemplate('results_ajax', 'Test Results Management', [
    'page_title' => 'Test Results Management',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => 'dashboard_new.php'],
        ['name' => 'Test Results', 'url' => '']
    ]
]);

function getContent() {
    ob_start();
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Test Results Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard_new.php">Home</a></li>
                    <li class="breadcrumb-item active">Test Results</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Results Table Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    All Test Results
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="addResultBtn">
                        <i class="fas fa-plus mr-1"></i>
                        Add Result
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="resultsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Result ID</th>
                                <th>Patient</th>
                                <th>Test</th>
                                <th>Result</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add/Edit Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalLabel">Add Test Result</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="resultForm">
                <div class="modal-body">
                    <input type="hidden" id="resultId" name="id">
                    <input type="hidden" name="action" id="formAction" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="patientId">Patient *</label>
                                <select class="form-control" id="patientId" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="testType">Test Type *</label>
                                <select class="form-control" id="testType" name="test_type" required>
                                    <option value="">Select Test</option>
                                    <option value="CBC">Complete Blood Count (CBC)</option>
                                    <option value="LFT">Liver Function Test (LFT)</option>
                                    <option value="KFT">Kidney Function Test (KFT)</option>
                                    <option value="LIPID">Lipid Profile</option>
                                    <option value="GLUCOSE">Glucose Test</option>
                                    <option value="HBA1C">HbA1c Test</option>
                                    <option value="THYROID">Thyroid Function Test</option>
                                    <option value="URINE">Urine Analysis</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="resultValue">Result Value *</label>
                                <input type="text" class="form-control" id="resultValue" name="result_value" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" placeholder="mg/dL, g/dL, etc.">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="referenceRange">Reference Range</label>
                                <input type="text" class="form-control" id="referenceRange" name="reference_range" placeholder="e.g., 70-100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="resultStatus">Status</label>
                                <select class="form-control" id="resultStatus" name="status">
                                    <option value="normal">Normal</option>
                                    <option value="abnormal">Abnormal</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="resultDate">Result Date *</label>
                        <input type="date" class="form-control" id="resultDate" name="result_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveResultBtn">
                        <i class="fas fa-save mr-1"></i>
                        Save Result
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set today's date as default
    $('#resultDate').val(new Date().toISOString().substr(0, 10));
    
    // Initialize DataTable
    let resultsTable = $('#resultsTable').DataTable({
        ajax: {
            url: 'api/results_api.php',
            type: 'GET',
            data: { action: 'list' },
            dataSrc: function(json) {
                if (!json.success) {
                    toastr.error(json.message || 'Failed to load results');
                    return [];
                }
                return json.data || [];
            }
        },
        columns: [
            { 
                data: 'result_id',
                defaultContent: 'N/A'
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.patient_first_name || ''} ${row.patient_last_name || ''}`.trim() || 'N/A';
                }
            },
            { 
                data: 'test_type',
                defaultContent: 'N/A'
            },
            { 
                data: null,
                render: function(data, type, row) {
                    let result = row.result_value || 'N/A';
                    if (row.unit) {
                        result += ' ' + row.unit;
                    }
                    return result;
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    if (data === 'normal') badgeClass = 'success';
                    else if (data === 'abnormal') badgeClass = 'warning';
                    else if (data === 'critical') badgeClass = 'danger';
                    
                    return `<span class="badge badge-${badgeClass}">${data || 'N/A'}</span>`;
                }
            },
            { 
                data: 'result_date',
                render: function(data, type, row) {
                    if (!data) return 'N/A';
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-sm edit-result" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-primary btn-sm view-result" data-id="${row.id}" title="View Report">
                                <i class="fas fa-file-alt"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-result" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            emptyTable: "No results found",
            loadingRecords: "Loading results...",
            processing: "Processing..."
        }
    });

    // Load patients for dropdown
    loadPatients();
    
    // Add Result Button
    $('#addResultBtn').click(function() {
        resetForm();
        $('#resultModalLabel').text('Add Test Result');
        $('#formAction').val('add');
        $('#resultModal').modal('show');
    });

    // Edit Result
    $(document).on('click', '.edit-result', function() {
        const resultId = $(this).data('id');
        editResult(resultId);
    });

    // View Result
    $(document).on('click', '.view-result', function() {
        const resultId = $(this).data('id');
        viewResult(resultId);
    });

    // Delete Result
    $(document).on('click', '.delete-result', function() {
        const resultId = $(this).data('id');
        deleteResult(resultId);
    });

    // Form Submission
    $('#resultForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading state
        $('#saveResultBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
        
        $.ajax({
            url: 'api/results_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#resultModal').modal('hide');
                    resultsTable.ajax.reload(null, false);
                } else {
                    toastr.error(response.message || 'Operation failed');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Network error occurred. Please try again.');
            },
            complete: function() {
                $('#saveResultBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save Result');
            }
        });
    });

    function loadPatients() {
        $.ajax({
            url: 'api/patients_api.php',
            type: 'GET',
            data: { action: 'list' },
            success: function(response) {
                if (response.success && response.data) {
                    let options = '<option value="">Select Patient</option>';
                    response.data.forEach(function(patient) {
                        options += `<option value="${patient.id}">${patient.first_name} ${patient.last_name} (${patient.phone})</option>`;
                    });
                    $('#patientId').html(options);
                }
            }
        });
    }

    function editResult(id) {
        $.ajax({
            url: 'api/results_api.php',
            type: 'GET',
            data: { action: 'get', id: id },
            success: function(response) {
                if (response.success && response.data) {
                    const result = response.data;
                    
                    // Populate form
                    $('#resultId').val(result.id);
                    $('#patientId').val(result.patient_id);
                    $('#testType').val(result.test_type);
                    $('#resultValue').val(result.result_value);
                    $('#unit').val(result.unit);
                    $('#referenceRange').val(result.reference_range);
                    $('#resultStatus').val(result.status);
                    $('#notes').val(result.notes);
                    $('#resultDate').val(result.result_date);
                    
                    $('#resultModalLabel').text('Edit Test Result');
                    $('#formAction').val('update');
                    $('#resultModal').modal('show');
                } else {
                    toastr.error(response.message || 'Failed to load result data');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Failed to load result data');
            }
        });
    }

    function viewResult(id) {
        toastr.info('Report generation feature coming soon!');
    }

    function deleteResult(id) {
        Swal.fire({
            title: 'Delete Result',
            text: 'Are you sure you want to delete this result? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/results_api.php',
                    type: 'POST',
                    data: { 
                        action: 'delete', 
                        id: id 
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            resultsTable.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || 'Delete failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        toastr.error('Failed to delete result');
                    }
                });
            }
        });
    }

    function resetForm() {
        $('#resultForm')[0].reset();
        $('#resultId').val('');
        $('#formAction').val('add');
        $('#resultDate').val(new Date().toISOString().substr(0, 10));
    }

    // Auto-refresh table every 30 seconds
    setInterval(function() {
        if ($('#resultModal').is(':visible') === false) {
            resultsTable.ajax.reload(null, false);
        }
    }, 30000);
});
</script>

<?php
    return ob_get_clean();
}
?>

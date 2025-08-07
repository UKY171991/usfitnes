<?php
require_once 'includes/adminlte_template.php';

function patientsContent() {
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-injured mr-2"></i>Patients Management
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="showAddPatientModal()">
                            <i class="fas fa-plus mr-1"></i>Add Patient
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="patients-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Patient Modal -->
    <div class="modal fade" id="patientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle">Add Patient</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="patientForm">
                    <div class="modal-body">
                        <input type="hidden" id="patient_id" name="id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    let patientsTable;
    
    $(document).ready(function() {
        // Initialize DataTable with AJAX
        patientsTable = $('#patients-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: 'api/patients_api.php?action=list',
                type: 'GET',
                dataSrc: function(json) {
                    if (!json.success) {
                        toastr.error(json.message || 'Failed to load patients');
                        return [];
                    }
                    return json.data || [];
                }
            },
            columns: [
                { data: 'patient_id' },
                { 
                    data: null,
                    render: function(data) {
                        return data.first_name + ' ' + data.last_name;
                    }
                },
                { data: 'phone' },
                { 
                    data: 'gender',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">Not specified</span>';
                        const badge = data === 'male' ? 'primary' : data === 'female' ? 'danger' : 'secondary';
                        return `<span class="badge badge-${badge}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                {
                    data: 'date_of_birth',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        const birthDate = new Date(data);
                        const today = new Date();
                        const age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
                        return age + ' years';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return `
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info" onclick="viewPatient(${data.id})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-warning" onclick="editPatient(${data.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" onclick="deletePatient(${data.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            pageLength: 25,
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
            }
        });

        // Handle form submission
        $('#patientForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isEdit = $('#patient_id').val() !== '';
            formData.append('action', isEdit ? 'update' : 'add');
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
            
            $.ajax({
                url: 'api/patients_api.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || (isEdit ? 'Patient updated successfully!' : 'Patient added successfully!'));
                        $('#patientModal').modal('hide');
                        patientsTable.ajax.reload();
                        $('#patientForm')[0].reset();
                    } else {
                        toastr.error(response.message || 'Operation failed');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    toastr.error('Network error. Please try again.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });

    // Show add patient modal
    function showAddPatientModal() {
        $('#modalTitle').text('Add New Patient');
        $('#patientForm')[0].reset();
        $('#patient_id').val('');
        $('#patientModal').modal('show');
    }

    // View patient details
    function viewPatient(id) {
        window.open(`patient_details.php?id=${id}`, '_blank');
    }

    // Edit patient
    function editPatient(id) {
        $.ajax({
            url: `api/patients_api.php?action=get&id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const patient = response.data;
                    $('#modalTitle').text('Edit Patient');
                    $('#patient_id').val(patient.id);
                    $('#first_name').val(patient.first_name);
                    $('#last_name').val(patient.last_name);
                    $('#phone').val(patient.phone);
                    $('#gender').val(patient.gender);
                    $('#date_of_birth').val(patient.date_of_birth);
                    $('#email').val(patient.email);
                    $('#patientModal').modal('show');
                } else {
                    toastr.error(response.message || 'Failed to load patient data');
                }
            },
            error: function() {
                toastr.error('Network error. Please try again.');
            }
        });
    }

    // Delete patient
    function deletePatient(id) {
        Swal.fire({
            title: 'Delete Patient?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/patients_api.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Patient deleted successfully!');
                            patientsTable.ajax.reload();
                        } else {
                            toastr.error(response.message || 'Failed to delete patient');
                        }
                    },
                    error: function() {
                        toastr.error('Network error. Please try again.');
                    }
                });
            }
        });
    }

    // Global refresh function for auto-refresh
    window.refreshData = function() {
        patientsTable.ajax.reload();
        toastr.info('Patients data refreshed');
    };
    </script>
    <?php
}

renderTemplate('patients', 'patientsContent');
?>

<?php
// Set page title
$page_title = 'Doctors';

// Include header
include 'includes/header.php';

// Include sidebar with user info
include 'includes/sidebar.php';
?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Doctors Management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Doctors</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Registered Doctors</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addDoctorModal">
                    <i class="fas fa-plus"></i> Add Doctor
                  </button>
                </div>
              </div>
              <div class="card-body">
                <!-- Search and Filter Controls -->
                <div class="row mb-3">
                  <div class="col-md-4">
                    <div class="input-group">
                      <input type="text" class="form-control" id="searchInput" placeholder="Search doctors...">
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                          <i class="fas fa-search"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <select class="form-control" id="specializationFilter">
                      <option value="">All Specializations</option>
                      <option value="Pathology">Pathology</option>
                      <option value="Hematology">Hematology</option>
                      <option value="Microbiology">Microbiology</option>
                      <option value="Biochemistry">Biochemistry</option>
                      <option value="Immunology">Immunology</option>
                      <option value="Cytology">Cytology</option>
                      <option value="Histopathology">Histopathology</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select class="form-control" id="statusFilter">
                      <option value="">All Status</option>
                      <option value="active">Active</option>
                      <option value="inactive">Inactive</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button class="btn btn-secondary btn-block" id="clearFilters">
                      <i class="fas fa-times"></i> Clear
                    </button>
                  </div>
                </div>

                <!-- Doctors Table -->
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>License No</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="doctorsTableBody">
                      <!-- Dynamic content loaded via AJAX -->
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <div class="row mt-3">
                  <div class="col-sm-12 col-md-5">
                    <div class="dataTables_info" id="tableInfo"></div>
                  </div>
                  <div class="col-sm-12 col-md-7">
                    <div class="dataTables_paginate paging_simple_numbers">
                      <ul class="pagination" id="pagination"></ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Add Doctor Modal -->
  <div class="modal fade" id="addDoctorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Add New Doctor</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="addDoctorForm">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="add_first_name" name="first_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="add_last_name" name="last_name" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Specialization <span class="text-danger">*</span></label>
                  <select class="form-control" id="add_specialization" name="specialization" required>
                    <option value="">Select Specialization</option>
                    <option value="Pathology">Pathology</option>
                    <option value="Hematology">Hematology</option>
                    <option value="Microbiology">Microbiology</option>
                    <option value="Biochemistry">Biochemistry</option>
                    <option value="Immunology">Immunology</option>
                    <option value="Cytology">Cytology</option>
                    <option value="Histopathology">Histopathology</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>License Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="add_license_number" name="license_number" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" id="add_phone" name="phone" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="add_email" name="email" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status <span class="text-danger">*</span></label>
                  <select class="form-control" id="add_status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Years of Experience</label>
                  <input type="number" class="form-control" id="add_experience_years" name="experience_years" min="0">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <textarea class="form-control" id="add_address" name="address" rows="2" placeholder="Doctor's address..."></textarea>
            </div>
            <div class="form-group">
              <label>Notes</label>
              <textarea class="form-control" id="add_notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Save Doctor
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Doctor Modal -->
  <div class="modal fade" id="editDoctorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Doctor</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="editDoctorForm">
          <input type="hidden" id="edit_doctor_id" name="id">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Specialization <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_specialization" name="specialization" required>
                    <option value="">Select Specialization</option>
                    <option value="Pathology">Pathology</option>
                    <option value="Hematology">Hematology</option>
                    <option value="Microbiology">Microbiology</option>
                    <option value="Biochemistry">Biochemistry</option>
                    <option value="Immunology">Immunology</option>
                    <option value="Cytology">Cytology</option>
                    <option value="Histopathology">Histopathology</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>License Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_license_number" name="license_number" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="edit_email" name="email" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Years of Experience</label>
                  <input type="number" class="form-control" id="edit_experience_years" name="experience_years" min="0">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <textarea class="form-control" id="edit_address" name="address" rows="2" placeholder="Doctor's address..."></textarea>
            </div>
            <div class="form-group">
              <label>Notes</label>
              <textarea class="form-control" id="edit_notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Update Doctor
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Doctor Modal -->
  <div class="modal fade" id="viewDoctorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Doctor Details</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body" id="viewDoctorContent">
          <!-- Dynamic content loaded via AJAX -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="printDoctorInfo()">
            <i class="fas fa-print"></i> Print Info
          </button>
        </div>
      </div>
    </div>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">PathLab Pro</a>.</strong> All rights reserved.
  </footer>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
let currentPage = 1;
let currentSearch = '';
let currentSpecialization = '';
let currentStatus = '';

$(document).ready(function() {
    loadDoctors();
});

// Search functionality
$('#searchBtn').click(function() {
    currentSearch = $('#searchInput').val();
    loadDoctors(1, currentSearch, currentSpecialization, currentStatus);
});

$('#searchInput').keypress(function(e) {
    if(e.which == 13) {
        $('#searchBtn').click();
    }
});

// Filter functionality
$('#specializationFilter').change(function() {
    currentSpecialization = $(this).val();
    loadDoctors(1, currentSearch, currentSpecialization, currentStatus);
});

$('#statusFilter').change(function() {
    currentStatus = $(this).val();
    loadDoctors(1, currentSearch, currentSpecialization, currentStatus);
});

$('#clearFilters').click(function() {
    $('#searchInput').val('');
    $('#specializationFilter').val('');
    $('#statusFilter').val('');
    currentSearch = '';
    currentSpecialization = '';
    currentStatus = '';
    loadDoctors();
});

// Load doctors function
function loadDoctors(page = 1, search = '', specialization = '', status = '') {
    currentPage = page;
    currentSearch = search;
    currentSpecialization = specialization;
    currentStatus = status;
    
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'GET',
        data: {
            action: 'read',
            page: page,
            search: search,
            specialization: specialization,
            status: status
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                displayDoctors(response.data);
                updatePagination(response.pagination);
                updateTableInfo(response.pagination);
            } else {
                showAlert('Error loading doctors: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error loading doctors. Please try again.', 'danger');
        }
    });
}

// Display doctors in table
function displayDoctors(doctors) {
    let html = '';
    
    if(doctors.length === 0) {
        html = '<tr><td colspan="7" class="text-center">No doctors found</td></tr>';
    } else {
        doctors.forEach(function(doctor) {
            const statusBadge = doctor.status === 'active' ? 
                '<span class="badge badge-success">Active</span>' : 
                '<span class="badge badge-secondary">Inactive</span>';
            
            html += `
                <tr>
                    <td>Dr. ${doctor.first_name} ${doctor.last_name}</td>
                    <td>${doctor.specialization}</td>
                    <td>${doctor.license_number}</td>
                    <td>${doctor.phone}</td>
                    <td>${doctor.email}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewDoctor(${doctor.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="editDoctor(${doctor.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteDoctor(${doctor.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#doctorsTableBody').html(html);
}

// Add doctor form submission
$('#addDoctorForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=create',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#addDoctorModal').modal('hide');
                $('#addDoctorForm')[0].reset();
                loadDoctors(currentPage, currentSearch, currentSpecialization, currentStatus);
                showAlert('Doctor added successfully!', 'success');
            } else {
                showAlert('Error adding doctor: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error adding doctor. Please try again.', 'danger');
        }
    });
});

// Edit doctor
function editDoctor(id) {
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'GET',
        data: { action: 'read', id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.data.length > 0) {
                const doctor = response.data[0];
                $('#edit_doctor_id').val(doctor.id);
                $('#edit_first_name').val(doctor.first_name);
                $('#edit_last_name').val(doctor.last_name);
                $('#edit_specialization').val(doctor.specialization);
                $('#edit_license_number').val(doctor.license_number);
                $('#edit_phone').val(doctor.phone);
                $('#edit_email').val(doctor.email);
                $('#edit_status').val(doctor.status);
                $('#edit_experience_years').val(doctor.experience_years);
                $('#edit_address').val(doctor.address);
                $('#edit_notes').val(doctor.notes);
                $('#editDoctorModal').modal('show');
            }
        }
    });
}

// Edit doctor form submission
$('#editDoctorForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=update',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#editDoctorModal').modal('hide');
                loadDoctors(currentPage, currentSearch, currentSpecialization, currentStatus);
                showAlert('Doctor updated successfully!', 'success');
            } else {
                showAlert('Error updating doctor: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error updating doctor. Please try again.', 'danger');
        }
    });
});

// View doctor details
function viewDoctor(id) {
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'GET',
        data: { action: 'read', id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.data.length > 0) {
                const doctor = response.data[0];
                const statusBadge = doctor.status === 'active' ? 
                    '<span class="badge badge-success">Active</span>' : 
                    '<span class="badge badge-secondary">Inactive</span>';
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Personal Information</h5>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td>Dr. ${doctor.first_name} ${doctor.last_name}</td></tr>
                                <tr><td><strong>Specialization:</strong></td><td>${doctor.specialization}</td></tr>
                                <tr><td><strong>License No:</strong></td><td>${doctor.license_number}</td></tr>
                                <tr><td><strong>Experience:</strong></td><td>${doctor.experience_years || 'N/A'} years</td></tr>
                                <tr><td><strong>Status:</strong></td><td>${statusBadge}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Contact Information</h5>
                            <table class="table table-sm">
                                <tr><td><strong>Phone:</strong></td><td>${doctor.phone}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${doctor.email}</td></tr>
                                <tr><td><strong>Address:</strong></td><td>${doctor.address || 'N/A'}</td></tr>
                                <tr><td><strong>Joined:</strong></td><td>${formatDate(doctor.created_at)}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                if(doctor.notes) {
                    html += `
                        <div class="row">
                            <div class="col-12">
                                <h5>Notes</h5>
                                <div class="alert alert-info">${doctor.notes}</div>
                            </div>
                        </div>
                    `;
                }
                
                $('#viewDoctorContent').html(html);
                $('#viewDoctorModal').modal('show');
            }
        }
    });
}

// Delete doctor
function deleteDoctor(id) {
    if(confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
        $.ajax({
            url: 'api/doctors_api.php',
            method: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    loadDoctors(currentPage, currentSearch, currentSpecialization, currentStatus);
                    showAlert('Doctor deleted successfully!', 'success');
                } else {
                    showAlert('Error deleting doctor: ' + response.message, 'danger');
                }
            },
            error: function() {
                showAlert('Error deleting doctor. Please try again.', 'danger');
            }
        });
    }
}

// Print doctor info function
function printDoctorInfo() {
    window.print();
}

// Utility functions
function formatDate(dateStr) {
    if(!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString();
}

function updatePagination(pagination) {
    let container = $('#pagination');
    container.empty();
    
    if(pagination.pages <= 1) return;
    
    // Previous button
    if(pagination.page > 1) {
        container.append(`
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadDoctors(${pagination.page - 1}, '${currentSearch}', '${currentSpecialization}', '${currentStatus}')">Previous</a>
            </li>
        `);
    }
    
    // Page numbers
    let startPage = Math.max(1, pagination.page - 2);
    let endPage = Math.min(pagination.pages, pagination.page + 2);
    
    if(startPage > 1) {
        container.append('<li class="page-item"><a class="page-link" href="#" onclick="loadDoctors(1, \'' + currentSearch + '\', \'' + currentSpecialization + '\', \'' + currentStatus + '\')">1</a></li>');
        if(startPage > 2) {
            container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
    }
    
    for(let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.page ? 'active' : '';
        container.append(`
            <li class="page-item ${activeClass}">
                <a class="page-link" href="#" onclick="loadDoctors(${i}, '${currentSearch}', '${currentSpecialization}', '${currentStatus}')">${i}</a>
            </li>
        `);
    }
    
    if(endPage < pagination.pages) {
        if(endPage < pagination.pages - 1) {
            container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
        container.append(`<li class="page-item"><a class="page-link" href="#" onclick="loadDoctors(${pagination.pages}, '${currentSearch}', '${currentSpecialization}', '${currentStatus}')">${pagination.pages}</a></li>`);
    }
    
    // Next button
    if(pagination.page < pagination.pages) {
        container.append(`
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadDoctors(${pagination.page + 1}, '${currentSearch}', '${currentSpecialization}', '${currentStatus}')">Next</a>
            </li>
        `);
    }
}

function updateTableInfo(pagination) {
    const start = (pagination.page - 1) * pagination.limit + 1;
    const end = Math.min(pagination.page * pagination.limit, pagination.total);
    $('#tableInfo').text(`Showing ${start} to ${end} of ${pagination.total} entries`);
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of content
    $('.content-wrapper .content').prepend(alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();    }, 5000);
}
</script>

<?php
// Include footer
include 'includes/footer.php';
?>

<?php
// Set page title and active menu
$page_title = 'Doctors';
$active_menu = 'doctors';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
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

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Stats Row -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalDoctors">0</h3>
              <p>Total Doctors</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-md"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="activeDoctors">0</h3>
              <p>Active Doctors</p>
            </div>
            <div class="icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('active')">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3 id="specializations">0</h3>
              <p>Specializations</p>
            </div>
            <div class="icon">
              <i class="fas fa-stethoscope"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="referralsToday">0</h3>
              <p>Referrals Today</p>
            </div>
            <div class="icon">
              <i class="fas fa-paper-plane"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Main Card -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Registered Doctors</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addDoctorModal">
                  <i class="fas fa-plus"></i> Add Doctor
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="exportDoctors()">
                  <i class="fas fa-download"></i> Export
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Filters -->
              <div class="row mb-3">
                <div class="col-md-3">
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search doctors...">
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" onclick="loadDoctors()">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-2">
                  <select class="form-control form-control-sm" id="specializationFilter" onchange="loadDoctors()">
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
                <div class="col-md-2">
                  <select class="form-control form-control-sm" id="statusFilter" onchange="loadDoctors()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <select class="form-control form-control-sm" id="sortBy" onchange="loadDoctors()">
                    <option value="name">Sort by Name</option>
                    <option value="created_at">Sort by Date Added</option>
                    <option value="referrals">Sort by Referrals</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Clear Filters
                  </button>
                  <button class="btn btn-success btn-sm ml-1" onclick="refreshDoctors()">
                    <i class="fas fa-sync"></i> Refresh
                  </button>
                </div>
              </div>

              <!-- Doctors Table -->
              <div class="table-responsive">
                <table id="doctorsTable" class="table table-bordered table-striped table-hover">
                  <thead>
                    <tr>
                      <th width="5%">Photo</th>
                      <th width="20%">Name</th>
                      <th width="15%">Specialization</th>
                      <th width="12%">License No</th>
                      <th width="12%">Phone</th>
                      <th width="15%">Email</th>
                      <th width="8%">Status</th>
                      <th width="8%">Referrals</th>
                      <th width="10%">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="doctorsTableBody">
                    <!-- Dynamic content will be loaded here -->
                  </tbody>
                </table>
              </div>

              <!-- Loading indicator -->
              <div id="loadingIndicator" class="text-center p-3" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading doctors...</p>
              </div>

              <!-- Pagination -->
              <div class="row mt-3">
                <div class="col-sm-12 col-md-5">
                  <div id="doctorsInfo" class="dataTables_info"></div>
                </div>
                <div class="col-sm-12 col-md-7">
                  <nav>
                    <ul class="pagination pagination-sm float-right" id="doctorsPagination">
                      <!-- Pagination will be loaded here -->
                    </ul>
                  </nav>
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
<div class="modal fade" id="addDoctorModal" tabindex="-1" role="dialog" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title" id="addDoctorModalLabel">
          <i class="fas fa-user-plus"></i> Add New Doctor
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addDoctorForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_first_name">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_first_name" name="first_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_last_name">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_last_name" name="last_name" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_specialization">Specialization <span class="text-danger">*</span></label>
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
                <label for="add_license_number">License Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_license_number" name="license_number" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_phone">Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="add_phone" name="phone" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_email">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="add_email" name="email" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="add_address">Address</label>
                <textarea class="form-control" id="add_address" name="address" rows="3" placeholder="Enter doctor's address..."></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_status">Status</label>
                <select class="form-control" id="add_status" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_commission_rate">Commission Rate (%)</label>
                <input type="number" class="form-control" id="add_commission_rate" name="commission_rate" min="0" max="100" step="0.01">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="add_notes">Notes</label>
            <textarea class="form-control" id="add_notes" name="notes" rows="3" placeholder="Additional notes about the doctor..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary" id="addDoctorBtn">
            <i class="fas fa-save"></i> Save Doctor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Doctor Modal -->
<div class="modal fade" id="editDoctorModal" tabindex="-1" role="dialog" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h4 class="modal-title" id="editDoctorModalLabel">
          <i class="fas fa-edit"></i> Edit Doctor
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editDoctorForm">
        <input type="hidden" id="edit_doctor_id" name="id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_first_name">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_last_name">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_specialization">Specialization <span class="text-danger">*</span></label>
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
                <label for="edit_license_number">License Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_license_number" name="license_number" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_phone">Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="edit_phone" name="phone" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_email">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="edit_email" name="email" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="edit_address">Address</label>
                <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_status">Status</label>
                <select class="form-control" id="edit_status" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_commission_rate">Commission Rate (%)</label>
                <input type="number" class="form-control" id="edit_commission_rate" name="commission_rate" min="0" max="100" step="0.01">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_notes">Notes</label>
            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-warning" id="editDoctorBtn">
            <i class="fas fa-save"></i> Update Doctor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Doctor Modal -->
<div class="modal fade" id="viewDoctorModal" tabindex="-1" role="dialog" aria-labelledby="viewDoctorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title" id="viewDoctorModalLabel">
          <i class="fas fa-eye"></i> Doctor Details
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewDoctorContent">
        <!-- Content will be loaded dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Close
        </button>
        <button type="button" class="btn btn-primary" onclick="printDoctorInfo()">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteDoctorModal" tabindex="-1" role="dialog" aria-labelledby="deleteDoctorModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h4 class="modal-title text-white" id="deleteDoctorModalLabel">
          <i class="fas fa-trash"></i> Confirm Delete
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this doctor?</p>
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i>
          <strong>Warning:</strong> This action cannot be undone and will affect all related test orders.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <i class="fas fa-trash"></i> Delete Doctor
        </button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Initialize page
    loadStats();
    loadDoctors();
    
    // Search on Enter key
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            loadDoctors();
        }
    });
    
    // Auto-refresh stats every 30 seconds
    setInterval(function() {
        if (!$('.modal').hasClass('show')) {
            loadStats();
        }
    }, 30000);
});

// Global variables
let currentPage = 1;
let doctorsPerPage = 10;
let currentFilters = {
    search: '',
    specialization: '',
    status: '',
    sortBy: 'name'
};

// Load statistics
function loadStats() {
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'GET',
        data: { action: 'stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#totalDoctors').text(stats.total || 0);
                $('#activeDoctors').text(stats.active || 0);
                $('#specializations').text(stats.specializations || 0);
                $('#referralsToday').text(stats.referrals_today || 0);
            }
        },
        error: function() {
            console.log('Error loading statistics');
        }
    });
}

// Load doctors with filters and pagination
function loadDoctors(page = 1) {
    currentPage = page;
    
    // Get current filters
    currentFilters.search = $('#searchInput').val().trim();
    currentFilters.specialization = $('#specializationFilter').val();
    currentFilters.status = $('#statusFilter').val();
    currentFilters.sortBy = $('#sortBy').val();
    
    // Show loading indicator
    $('#loadingIndicator').show();
    $('#doctorsTableBody').hide();
    
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'GET',
        data: {
            action: 'read',
            page: currentPage,
            limit: doctorsPerPage,
            search: currentFilters.search,
            specialization: currentFilters.specialization,
            status: currentFilters.status,
            sort_by: currentFilters.sortBy
        },
        dataType: 'json',
        success: function(response) {
            $('#loadingIndicator').hide();
            $('#doctorsTableBody').show();
            
            if (response.success) {
                displayDoctors(response.data);
                displayPagination(response.pagination);
                updateDoctorsInfo(response.pagination);
            } else {
                $('#doctorsTableBody').html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                            ${response.message || 'No doctors found'}
                        </td>
                    </tr>
                `);
                $('#doctorsPagination').empty();
                $('#doctorsInfo').text('Showing 0 to 0 of 0 entries');
            }
        },
        error: function() {
            $('#loadingIndicator').hide();
            $('#doctorsTableBody').show();
            $('#doctorsTableBody').html(`
                <tr>
                    <td colspan="9" class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                        Error loading doctors. Please try again.
                    </td>
                </tr>
            `);
        }
    });
}

// Display doctors in table
function displayDoctors(doctors) {
    let html = '';
    
    if (doctors.length === 0) {
        html = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-search fa-2x mb-2"></i><br>
                    No doctors found matching your criteria
                </td>
            </tr>
        `;
    } else {
        doctors.forEach(function(doctor) {
            // Status badge
            const statusBadge = doctor.status === 'active' 
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-secondary">Inactive</span>';
            
            // Doctor name with photo placeholder
            const doctorName = `
                <div class="d-flex align-items-center">
                    <div class="user-image mr-2">
                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                    </div>
                    <div>
                        <strong>Dr. ${doctor.first_name} ${doctor.last_name}</strong>
                    </div>
                </div>
            `;
            
            html += `
                <tr>
                    <td>
                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                    </td>
                    <td>
                        <strong>Dr. ${doctor.first_name} ${doctor.last_name}</strong>
                        <br><small class="text-muted">Added: ${formatDate(doctor.created_at)}</small>
                    </td>
                    <td>
                        <span class="badge badge-info">${doctor.specialization || 'N/A'}</span>
                    </td>
                    <td>
                        <code>${doctor.license_number || 'N/A'}</code>
                    </td>
                    <td>
                        <a href="tel:${doctor.phone}" class="text-primary">${doctor.phone || 'N/A'}</a>
                    </td>
                    <td>
                        <a href="mailto:${doctor.email}" class="text-primary">${doctor.email || 'N/A'}</a>
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <span class="badge badge-primary">${doctor.referrals_count || 0}</span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-info btn-sm" onclick="viewDoctor(${doctor.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="editDoctor(${doctor.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteDoctor(${doctor.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#doctorsTableBody').html(html);
}

// Display pagination
function displayPagination(pagination) {
    let html = '';
    
    if (pagination.pages > 1) {
        // Previous button
        if (pagination.page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadDoctors(${pagination.page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
        }
        
        // Page numbers
        const startPage = Math.max(1, pagination.page - 2);
        const endPage = Math.min(pagination.pages, pagination.page + 2);
        
        if (startPage > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadDoctors(1)">1</a>
            </li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const active = i === pagination.page ? 'active' : '';
            html += `<li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadDoctors(${i})">${i}</a>
            </li>`;
        }
        
        if (endPage < pagination.pages) {
            if (endPage < pagination.pages - 1) {
                html += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadDoctors(${pagination.pages})">${pagination.pages}</a>
            </li>`;
        }
        
        // Next button
        if (pagination.page < pagination.pages) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadDoctors(${pagination.page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
        }
    }
    
    $('#doctorsPagination').html(html);
}

// Update doctors info
function updateDoctorsInfo(pagination) {
    const start = (pagination.page - 1) * pagination.limit + 1;
    const end = Math.min(pagination.page * pagination.limit, pagination.total);
    $('#doctorsInfo').text(`Showing ${start} to ${end} of ${pagination.total} entries`);
}

// Filter functions
function filterByStatus(status) {
    $('#statusFilter').val(status);
    loadDoctors(1);
}

function clearFilters() {
    $('#searchInput').val('');
    $('#specializationFilter').val('');
    $('#statusFilter').val('');
    $('#sortBy').val('name');
    loadDoctors(1);
}

function refreshDoctors() {
    loadStats();
    loadDoctors(currentPage);
    showAlert('Doctors list refreshed successfully', 'success');
}

// Add doctor form submission
$('#addDoctorForm').submit(function(e) {
    e.preventDefault();
    
    const submitBtn = $('#addDoctorBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=create',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#addDoctorModal').modal('hide');
                $('#addDoctorForm')[0].reset();
                loadDoctors(currentPage);
                loadStats();
                showAlert('Doctor added successfully!', 'success');
            } else {
                showAlert('Error adding doctor: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error adding doctor. Please try again.', 'danger');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
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
            if (response.success && response.data.length > 0) {
                const doctor = response.data[0];
                $('#edit_doctor_id').val(doctor.id);
                $('#edit_first_name').val(doctor.first_name);
                $('#edit_last_name').val(doctor.last_name);
                $('#edit_specialization').val(doctor.specialization);
                $('#edit_license_number').val(doctor.license_number);
                $('#edit_phone').val(doctor.phone);
                $('#edit_email').val(doctor.email);
                $('#edit_address').val(doctor.address);
                $('#edit_status').val(doctor.status);
                $('#edit_commission_rate').val(doctor.commission_rate);
                $('#edit_notes').val(doctor.notes);
                $('#editDoctorModal').modal('show');
            } else {
                showAlert('Doctor not found', 'danger');
            }
        },
        error: function() {
            showAlert('Error loading doctor details', 'danger');
        }
    });
}

// Edit doctor form submission
$('#editDoctorForm').submit(function(e) {
    e.preventDefault();
    
    const submitBtn = $('#editDoctorBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
    
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=update',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editDoctorModal').modal('hide');
                loadDoctors(currentPage);
                loadStats();
                showAlert('Doctor updated successfully!', 'success');
            } else {
                showAlert('Error updating doctor: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error updating doctor. Please try again.', 'danger');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
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
            if (response.success && response.data.length > 0) {
                const doctor = response.data[0];
                
                let html = `
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-user-circle fa-5x text-muted mb-3"></i>
                            <h4>Dr. ${doctor.first_name} ${doctor.last_name}</h4>
                            <p class="text-muted">${doctor.specialization}</p>
                        </div>
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-info-circle"></i> Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr><td><strong>License Number:</strong></td><td>${doctor.license_number || 'N/A'}</td></tr>
                                        <tr><td><strong>Phone:</strong></td><td><a href="tel:${doctor.phone}">${doctor.phone || 'N/A'}</a></td></tr>
                                        <tr><td><strong>Email:</strong></td><td><a href="mailto:${doctor.email}">${doctor.email || 'N/A'}</a></td></tr>
                                        <tr><td><strong>Status:</strong></td><td>${doctor.status === 'active' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'}</td></tr>
                                        <tr><td><strong>Commission Rate:</strong></td><td>${doctor.commission_rate || 0}%</td></tr>
                                        <tr><td><strong>Total Referrals:</strong></td><td><span class="badge badge-primary">${doctor.referrals_count || 0}</span></td></tr>
                                        <tr><td><strong>Added Date:</strong></td><td>${formatDate(doctor.created_at)}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                if (doctor.address) {
                    html += `
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5><i class="fas fa-map-marker-alt"></i> Address</h5>
                            </div>
                            <div class="card-body">
                                <p>${doctor.address}</p>
                            </div>
                        </div>
                    `;
                }
                
                if (doctor.notes) {
                    html += `
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5><i class="fas fa-sticky-note"></i> Notes</h5>
                            </div>
                            <div class="card-body">
                                <p>${doctor.notes}</p>
                            </div>
                        </div>
                    `;
                }
                
                $('#viewDoctorContent').html(html);
                $('#viewDoctorModal').modal('show');
            } else {
                showAlert('Doctor not found', 'danger');
            }
        },
        error: function() {
            showAlert('Error loading doctor details', 'danger');
        }
    });
}

// Delete confirmation
let doctorToDelete = null;

function confirmDeleteDoctor(id) {
    doctorToDelete = id;
    $('#deleteDoctorModal').modal('show');
}

$('#confirmDeleteBtn').click(function() {
    if (doctorToDelete) {
        deleteDoctor(doctorToDelete);
        doctorToDelete = null;
    }
});

// Delete doctor
function deleteDoctor(id) {
    const deleteBtn = $('#confirmDeleteBtn');
    const originalText = deleteBtn.html();
    deleteBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);
    
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'POST',
        data: { action: 'delete', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#deleteDoctorModal').modal('hide');
                loadDoctors(currentPage);
                loadStats();
                showAlert('Doctor deleted successfully!', 'success');
            } else {
                showAlert('Error deleting doctor: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error deleting doctor. Please try again.', 'danger');
        },
        complete: function() {
            deleteBtn.html(originalText).prop('disabled', false);
        }
    });
}

// Export functions
function exportDoctors() {
    showAlert('Export feature will be available soon.', 'info');
}

function printDoctorInfo() {
    window.print();
}

// Utility functions
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString();
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('.content-wrapper .content').prepend(alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>

<?php
// Include footer
include 'includes/footer.php';
?>

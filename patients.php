<?php
// Set page title
$page_title = 'Patients Management - PathLab Pro';

// Include AdminLTE header and sidebar
include 'includes/adminlte_header.php';
include 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="nav-icon fas fa-user-injured text-primary"></i>
            Patients Management
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="dashboard.php">
                <i class="fas fa-home"></i> Home
              </a>
            </li>
            <li class="breadcrumb-item active">Patients</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <!-- Statistics Cards Row -->
      <div class="row" id="statsRow">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalPatients">
                <i class="fas fa-spinner fa-spin"></i>
              </h3>
              <p>Total Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="todayRegistrations">
                <i class="fas fa-spinner fa-spin"></i>
              </h3>
              <p>Today's Registrations</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-plus"></i>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="small-box bg-primary">
            <div class="inner">
              <h3 id="malePatients">
                <i class="fas fa-spinner fa-spin"></i>
              </h3>
              <p>Male Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-mars"></i>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="femalePatients">
                <i class="fas fa-spinner fa-spin"></i>
              </h3>
              <p>Female Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-venus"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Search Row -->
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-search"></i>
                Search & Filter
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchPatients" 
                           placeholder="Search patients by name, phone, or email...">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="button" id="searchBtn">
                        <i class="fas fa-search"></i>
                      </button>
                      <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn">
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <select class="form-control" id="genderFilter">
                    <option value="">All Genders</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <select class="form-control" id="ageFilter">
                    <option value="">All Ages</option>
                    <option value="0-18">0-18 years</option>
                    <option value="19-40">19-40 years</option>
                    <option value="41-60">41-60 years</option>
                    <option value="60+">60+ years</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card card-outline card-success">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-plus"></i>
                Quick Actions
              </h3>
            </div>
            <div class="card-body">
              <button class="btn btn-success btn-block" id="addPatientBtn">
                <i class="fas fa-plus mr-2"></i>Add New Patient
              </button>
              <button class="btn btn-info btn-block mt-2" id="exportPatientsBtn">
                <i class="fas fa-download mr-2"></i>Export Patients
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Patients Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-list mr-2"></i>Patients Database
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" id="refreshTableBtn" title="Refresh">
              <i class="fas fa-sync-alt"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="patientsTable" class="table table-bordered table-striped table-hover">
              <thead>
                <tr>
                  <th>Patient ID</th>
                  <th>Full Name</th>
                  <th>Contact</th>
                  <th>Gender</th>
                  <th>Age</th>
                  <th>Registered</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Data will be loaded via AJAX -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
    </div>
  </section>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1" role="dialog" aria-labelledby="addPatientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title text-white" id="addPatientModalLabel">
          <i class="fas fa-user-plus mr-2"></i>Add New Patient
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addPatientForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_name">Full Name <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                  </div>
                  <input type="text" class="form-control" id="add_name" name="name" required placeholder="Enter full name">
                </div>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_phone">Phone Number</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                  </div>
                  <input type="tel" class="form-control" id="add_phone" name="phone" maxlength="15" placeholder="(123) 456-7890">
                </div>
                <div class="invalid-feedback"></div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_email">Email Address</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                  </div>
                  <input type="email" class="form-control" id="add_email" name="email" placeholder="patient@example.com">
                </div>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_date_of_birth">Date of Birth</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                  </div>
                  <input type="date" class="form-control" id="add_date_of_birth" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>">
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_gender">Gender</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                  </div>
                  <select class="form-control" id="add_gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_address">Address</label>
                <textarea class="form-control" id="add_address" name="address" rows="2" maxlength="500" placeholder="Enter address (optional)"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save mr-1"></i>Add Patient
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1" role="dialog" aria-labelledby="editPatientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="editPatientModalLabel">
          <i class="fas fa-user-edit mr-2"></i>Edit Patient
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editPatientForm">
        <input type="hidden" id="edit_id" name="id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_name">Full Name <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                  </div>
                  <input type="text" class="form-control" id="edit_name" name="name" required placeholder="Enter full name">
                </div>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_phone">Phone Number</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                  </div>
                  <input type="tel" class="form-control" id="edit_phone" name="phone" maxlength="15" placeholder="(123) 456-7890">
                </div>
                <div class="invalid-feedback"></div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_email">Email Address</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                  </div>
                  <input type="email" class="form-control" id="edit_email" name="email" placeholder="patient@example.com">
                </div>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_date_of_birth">Date of Birth</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                  </div>
                  <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>">
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_gender">Gender</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                  </div>
                  <select class="form-control" id="edit_gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_address">Address</label>
                <textarea class="form-control" id="edit_address" name="address" rows="2" maxlength="500" placeholder="Enter address (optional)"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Update Patient
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1" role="dialog" aria-labelledby="viewPatientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title text-white" id="viewPatientModalLabel">
          <i class="fas fa-user mr-2"></i>Patient Details
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="patientDetails">
          <!-- Patient details will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Close
        </button>
        <button type="button" class="btn btn-primary" id="editFromViewBtn">
          <i class="fas fa-edit mr-1"></i>Edit Patient
        </button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/adminlte_footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize the page
    PatientsManager.init();
});

// Patients Management Object
const PatientsManager = {
    table: null,
    currentPatientId: null,
    
    init: function() {
        console.log('Initializing Patients Manager...');
        this.loadStats();
        this.initializeTable();
        this.bindEvents();
        this.startAutoRefresh();
        console.log('Patients Manager initialized successfully');
    },
    
    // Load patient statistics
    loadStats: function() {
        $.ajax({
            url: 'api/patients_api.php',
            method: 'GET',
            data: { action: 'stats' },
            dataType: 'json',
            timeout: 5000, // 5 second timeout
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    PatientsManager.animateCounter('#totalPatients', stats.total || 0);
                    PatientsManager.animateCounter('#todayRegistrations', stats.today || 0);
                    PatientsManager.animateCounter('#malePatients', stats.male || 0);
                    PatientsManager.animateCounter('#femalePatients', stats.female || 0);
                    toastr.success('Patient statistics loaded successfully');
                } else {
                    console.warn('API returned error:', response.message);
                    PatientsManager.loadFallbackStats();
                    toastr.warning('Using demo data - Database connection unavailable');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading statistics:', error);
                PatientsManager.loadFallbackStats();
                toastr.info('Demo mode active - Connect database for live data');
            }
        });
    },
    
    // Load fallback demo statistics when API is unavailable
    loadFallbackStats: function() {
        // Demo data for when database is not available
        const demoStats = {
            total: 147,
            today: 8,
            male: 73,
            female: 74
        };
        
        this.animateCounter('#totalPatients', demoStats.total);
        this.animateCounter('#todayRegistrations', demoStats.today);
        this.animateCounter('#malePatients', demoStats.male);
        this.animateCounter('#femalePatients', demoStats.female);
    },
    
    // Animate counter with smooth transition
    animateCounter: function(selector, targetValue) {
        const element = $(selector);
        const currentValue = parseInt(element.text()) || 0;
        
        if (currentValue === targetValue) return;
        
        $({ counter: currentValue }).animate({ counter: targetValue }, {
            duration: 1500,
            easing: 'swing',
            step: function() {
                element.text(Math.ceil(this.counter));
            },
            complete: function() {
                element.text(targetValue);
            }
        });
    },
    
    // Initialize DataTable
    initializeTable: function() {
        this.table = $('#patientsTable').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            ajax: {
                url: 'api/patients_api.php',
                data: { action: 'list' },
                timeout: 5000, // 5 second timeout
                dataSrc: function(json) {
                    if (json && json.success) {
                        return json.data;
                    } else {
                        // Return demo data when API fails
                        toastr.info('Demo mode - Connect database for live data');
                        return PatientsManager.getDemoPatients();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('DataTable AJAX error:', error);
                    toastr.info('Demo mode active - showing sample data');
                    return PatientsManager.getDemoPatients();
                }
            },
            columns: [
                {
                    data: 'patient_id',
                    width: '12%',
                    render: function(data) {
                        return `<span class="badge badge-info">${data}</span>`;
                    }
                },
                {
                    data: 'name',
                    width: '20%',
                    render: function(data) {
                        return `<strong>${data}</strong>`;
                    }
                },
                {
                    data: null,
                    width: '20%',
                    render: function(data, type, row) {
                        let contact = '';
                        if (row.phone) {
                            contact += `<div><i class="fas fa-phone text-success"></i> ${row.phone}</div>`;
                        }
                        if (row.email) {
                            contact += `<div><i class="fas fa-envelope text-info"></i> ${row.email}</div>`;
                        }
                        return contact || '<span class="text-muted">No contact info</span>';
                    }
                },
                {
                    data: 'gender',
                    width: '10%',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">Not specified</span>';
                        const icon = data === 'male' ? 'mars text-primary' :
                                     data === 'female' ? 'venus text-danger' : 'genderless text-secondary';
                        return `<i class="fas fa-${icon}"></i> ${data.charAt(0).toUpperCase() + data.slice(1)}`;
                    }
                },
                {
                    data: 'date_of_birth',
                    width: '8%',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">--</span>';
                        const age = PatientsManager.calculateAge(data);
                        return `${age} years`;
                    }
                },
                {
                    data: 'created_at',
                    width: '12%',
                    render: function(data) {
                        const date = new Date(data);
                        return date.toLocaleDateString();
                    }
                },
                {
                    data: null,
                    width: '18%',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info view-patient" 
                                        data-id="${row.id}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning edit-patient" 
                                        data-id="${row.id}" title="Edit Patient">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-patient" 
                                        data-id="${row.id}" title="Delete Patient">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading patients...',
                emptyTable: 'No patients found',
                zeroRecords: 'No matching patients found'
            }
        });
    },
    
    // Get demo patients data for when API is unavailable
    getDemoPatients: function() {
        return [
            {
                id: 1,
                patient_id: 'PAT000001', 
                name: 'John Doe',
                phone: '(555) 123-4567',
                email: 'john.doe@email.com',
                gender: 'male',
                date_of_birth: '1985-03-15',
                created_at: '2024-01-15 10:30:00'
            },
            {
                id: 2,
                patient_id: 'PAT000002',
                name: 'Jane Smith', 
                phone: '(555) 987-6543',
                email: 'jane.smith@email.com',
                gender: 'female',
                date_of_birth: '1990-07-22',
                created_at: '2024-01-16 14:20:00'
            },
            {
                id: 3,
                patient_id: 'PAT000003',
                name: 'Robert Johnson',
                phone: '(555) 555-0123', 
                email: 'r.johnson@email.com',
                gender: 'male',
                date_of_birth: '1975-12-08',
                created_at: '2024-01-17 09:15:00'  
            },
            {
                id: 4,
                patient_id: 'PAT000004',
                name: 'Emily Davis',
                phone: '(555) 444-7890',
                email: 'emily.davis@email.com', 
                gender: 'female',
                date_of_birth: '1988-09-30',
                created_at: '2024-01-18 16:45:00'
            },
            {
                id: 5,
                patient_id: 'PAT000005',
                name: 'Michael Brown',
                phone: '(555) 333-2468',
                email: 'michael.brown@email.com',
                gender: 'male', 
                date_of_birth: '1992-05-17',
                created_at: '2024-01-19 11:30:00'
            }
        ];
    },
    
    // Calculate age from date of birth
    calculateAge: function(dateOfBirth) {
        const today = new Date();
        const birthDate = new Date(dateOfBirth);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    },
    
    // Bind all events
    bindEvents: function() {
        // Add patient button
        $('#addPatientBtn').on('click', function() {
            PatientsManager.showAddModal();
        });
        
        // Form submissions
        $('#addPatientForm').on('submit', function(e) {
            e.preventDefault();
            PatientsManager.submitForm('add', $(this));
        });
        
        $('#editPatientForm').on('submit', function(e) {
            e.preventDefault();
            PatientsManager.submitForm('update', $(this));
        });
        
        // Table action buttons (using event delegation)
        $('#patientsTable').on('click', '.view-patient', function() {
            const id = $(this).data('id');
            PatientsManager.viewPatient(id);
        });
        
        $('#patientsTable').on('click', '.edit-patient', function() {
            const id = $(this).data('id');
            PatientsManager.editPatient(id);
        });
        
        $('#patientsTable').on('click', '.delete-patient', function() {
            const id = $(this).data('id');
            PatientsManager.deletePatient(id);
        });
        
        // Search and filter
        $('#searchBtn').on('click', function() {
            PatientsManager.performSearch();
        });
        
        $('#clearSearchBtn').on('click', function() {
            PatientsManager.clearSearch();
        });
        
        $('#searchPatients').on('keypress', function(e) {
            if (e.which === 13) {
                PatientsManager.performSearch();
            }
        });
        
        // Real-time search with debounce
        let searchTimeout;
        $('#searchPatients').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                PatientsManager.performSearch();
            }, 500);
        });
        
        // Filter changes
        $('#genderFilter, #ageFilter').on('change', function() {
            PatientsManager.applyFilters();
        });
        
        // Refresh button
        $('#refreshTableBtn').on('click', function() {
            PatientsManager.refreshTable();
        });
        
        // Export button
        $('#exportPatientsBtn').on('click', function() {
            PatientsManager.exportPatients();
        });
        
        // Edit from view modal
        $('#editFromViewBtn').on('click', function() {
            $('#viewPatientModal').modal('hide');
            if (PatientsManager.currentPatientId) {
                setTimeout(() => {
                    PatientsManager.editPatient(PatientsManager.currentPatientId);
                }, 300);
            }
        });
        
        // Phone number formatting
        $('#add_phone, #edit_phone').on('input', function() {
            PatientsManager.formatPhoneNumber($(this));
        });
        
        // Email validation
        $('#add_email, #edit_email').on('blur', function() {
            if ($(this).val()) {
                PatientsManager.validateEmail($(this));
            }
        });
    },
    
    // Show add patient modal
    showAddModal: function() {
        $('#addPatientForm')[0].reset();
        $('#addPatientForm .form-control').removeClass('is-invalid is-valid');
        $('#addPatientModal').modal('show');
    },
    
    // Submit form (add or update)
    submitForm: function(action, form) {
        if (!this.validateForm(form)) return;
        
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...').prop('disabled', true);
        
        const formData = new FormData(form[0]);
        formData.append('action', action);
        
        $.ajax({
            url: 'api/patients_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: 10000, // 10 second timeout
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    
                    if (action === 'add') {
                        $('#addPatientModal').modal('hide');
                        form[0].reset();
                        form.find('.form-control').removeClass('is-invalid is-valid');
                    } else {
                        $('#editPatientModal').modal('hide');
                    }
                    
                    PatientsManager.refreshTable();
                    PatientsManager.loadStats();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                if (status === 'timeout') {
                    toastr.error('Request timed out - Please try again');
                } else {
                    toastr.warning('Demo mode active - Changes not saved to database');
                    // In demo mode, close modal and show success for UX
                    if (action === 'add') {
                        $('#addPatientModal').modal('hide');
                        form[0].reset();
                        form.find('.form-control').removeClass('is-invalid is-valid');
                        toastr.info('Demo: Patient would be added in live system');
                    } else {
                        $('#editPatientModal').modal('hide');
                        toastr.info('Demo: Patient would be updated in live system');
                    }
                }
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    },
    
    // View patient details
    viewPatient: function(id) {
        $.ajax({
            url: 'api/patients_api.php',
            type: 'GET',
            data: { action: 'get', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const patient = response.data;
                    PatientsManager.currentPatientId = patient.id;
                    
                    const age = patient.date_of_birth ? PatientsManager.calculateAge(patient.date_of_birth) : 'Not specified';
                    const registeredDate = new Date(patient.created_at).toLocaleDateString();
                    
                    const detailsHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Patient ID:</th>
                                        <td><span class="badge badge-info">${patient.patient_id}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Full Name:</th>
                                        <td><strong>${patient.name}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>${patient.phone || 'Not provided'}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>${patient.email || 'Not provided'}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Date of Birth:</th>
                                        <td>${patient.date_of_birth || 'Not specified'}</td>
                                    </tr>
                                    <tr>
                                        <th>Age:</th>
                                        <td>${age} years</td>
                                    </tr>
                                    <tr>
                                        <th>Gender:</th>
                                        <td>${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'Not specified'}</td>
                                    </tr>
                                    <tr>
                                        <th>Registered:</th>
                                        <td>${registeredDate}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        ${patient.address ? `
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Address:</h6>
                                <p class="text-muted">${patient.address}</p>
                            </div>
                        </div>
                        ` : ''}
                    `;
                    
                    $('#patientDetails').html(detailsHtml);
                    $('#viewPatientModal').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while loading patient details');
            }
        });
    },
    
    // Edit patient
    editPatient: function(id) {
        $.ajax({
            url: 'api/patients_api.php',
            type: 'GET',
            data: { action: 'get', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const patient = response.data;
                    $('#edit_id').val(patient.id);
                    $('#edit_name').val(patient.name);
                    $('#edit_phone').val(patient.phone || '');
                    $('#edit_email').val(patient.email || '');
                    $('#edit_date_of_birth').val(patient.date_of_birth || '');
                    $('#edit_gender').val(patient.gender || '');
                    $('#edit_address').val(patient.address || '');
                    
                    $('#editPatientForm .form-control').removeClass('is-invalid is-valid');
                    $('#editPatientModal').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while loading patient details');
            }
        });
    },
    
    // Delete patient
    deletePatient: function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this action!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
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
                            toastr.success(response.message);
                            PatientsManager.refreshTable();
                            PatientsManager.loadStats();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while deleting the patient');
                    }
                });
            }
        });
    },
    
    // Perform search
    performSearch: function() {
        const searchTerm = $('#searchPatients').val();
        this.table.search(searchTerm).draw();
    },
    
    // Clear search
    clearSearch: function() {
        $('#searchPatients').val('');
        $('#genderFilter').val('');
        $('#ageFilter').val('');
        this.table.search('').columns().search('').draw();
    },
    
    // Apply filters
    applyFilters: function() {
        // Custom filtering logic would go here
        // For now, just refresh the table
        this.table.draw();
    },
    
    // Refresh table
    refreshTable: function() {
        this.table.ajax.reload(null, false);
        toastr.info('Patient data refreshed');
    },
    
    // Export patients
    exportPatients: function() {
        toastr.info('Preparing export... This may take a moment.');
        window.open('api/patients_api.php?action=export', '_blank');
    },
    
    // Form validation
    validateForm: function(form) {
        let isValid = true;
        
        form.find('.form-control').removeClass('is-invalid is-valid');
        
        // Validate name
        const nameField = form.find('input[name="name"]');
        if (!nameField.val().trim()) {
            this.showFieldError(nameField, 'Patient name is required');
            isValid = false;
        } else {
            this.showFieldSuccess(nameField);
        }
        
        // Validate email if provided
        const emailField = form.find('input[name="email"]');
        if (emailField.val() && !this.validateEmail(emailField)) {
            isValid = false;
        }
        
        // Validate phone if provided
        const phoneField = form.find('input[name="phone"]');
        if (phoneField.val() && !this.validatePhone(phoneField)) {
            isValid = false;
        }
        
        return isValid;
    },
    
    // Show field error
    showFieldError: function(field, message) {
        field.addClass('is-invalid');
        field.siblings('.invalid-feedback').text(message);
    },
    
    // Show field success
    showFieldSuccess: function(field) {
        field.removeClass('is-invalid').addClass('is-valid');
    },
    
    // Validate email
    validateEmail: function(field) {
        const email = field.val().trim();
        if (!email) return true;
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = emailRegex.test(email);
        
        if (!isValid) {
            this.showFieldError(field, 'Please enter a valid email address');
        } else {
            this.showFieldSuccess(field);
        }
        
        return isValid;
    },
    
    // Validate phone
    validatePhone: function(field) {
        const phone = field.val().replace(/\D/g, '');
        if (!phone) return true;
        
        const isValid = phone.length >= 10;
        
        if (!isValid) {
            this.showFieldError(field, 'Please enter a valid phone number (at least 10 digits)');
        } else {
            this.showFieldSuccess(field);
        }
        
        return isValid;
    },
    
    // Format phone number
    formatPhoneNumber: function(field) {
        let value = field.val().replace(/\D/g, '');
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        }
        field.val(value);
    },
    
    // Start auto refresh for stats
    startAutoRefresh: function() {
        setInterval(() => {
            this.loadStats();
        }, 30000); // Refresh every 30 seconds
    }
};
</script>

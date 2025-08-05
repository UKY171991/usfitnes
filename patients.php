<?php
// Set page title
$page_title = 'Patients Management';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?><!-- Debug CSS Test (remove after testing) -->
<div class="debug-test" style="position: fixed; top: 10px; right: 10px; z-index: 9999;">
  CSS is loading!
</div>

<?php
// Set page title
$page_title = 'Patients Management';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Debug CSS Test (remove after testing) -->
<div class="debug-test" style="position: fixed; top: 10px; right: 10px; z-index: 9999;">
  CSS is loading!
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-user-injured mr-2"></i>Patients Management
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Patients</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">            <!-- Statistics Cards -->      <div class="row mb-3">        <div class="col-lg-3 col-6">          <div class="small-box bg-info">            <div class="inner">              <h3 id="totalPatients">                <i class="fas fa-spinner fa-spin"></i>              </h3>              <p>Total Patients</p>            </div>            <div class="icon">              <i class="fas fa-users"></i>            </div>          </div>        </div>        <div class="col-lg-3 col-6">          <div class="small-box bg-success">            <div class="inner">              <h3 id="todayRegistrations">                <i class="fas fa-spinner fa-spin"></i>              </h3>              <p>Today's Registrations</p>            </div>            <div class="icon">              <i class="fas fa-user-plus"></i>            </div>          </div>        </div>        <div class="col-lg-3 col-6">          <div class="small-box bg-primary">            <div class="inner">              <h3 id="malePatients">                <i class="fas fa-spinner fa-spin"></i>              </h3>              <p>Male Patients</p>            </div>            <div class="icon">              <i class="fas fa-mars"></i>            </div>          </div>        </div>        <div class="col-lg-3 col-6">          <div class="small-box bg-danger">            <div class="inner">              <h3 id="femalePatients">                <i class="fas fa-spinner fa-spin"></i>              </h3>              <p>Female Patients</p>            </div>            <div class="icon">              <i class="fas fa-venus"></i>            </div>          </div>        </div>      </div>      <!-- Action Buttons and Filters -->      <div class="row mb-3">        <div class="col-md-6">          <div class="input-group">            <input type="text" class="form-control" id="searchPatients" placeholder="Search patients by name, phone, or email...">            <div class="input-group-append">              <button class="btn btn-outline-secondary" type="button" onclick="searchPatients()">                <i class="fas fa-search"></i>              </button>              <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">                <i class="fas fa-times"></i>              </button>            </div>          </div>        </div>        <div class="col-md-6">          <div class="row">            <div class="col-md-4">              <select class="form-control" id="genderFilter" onchange="filterPatients()">                <option value="">All Genders</option>                <option value="male">Male</option>                <option value="female">Female</option>                <option value="other">Other</option>              </select>            </div>            <div class="col-md-4">              <select class="form-control" id="ageFilter" onchange="filterPatients()">                <option value="">All Ages</option>                <option value="0-18">0-18 years</option>                <option value="19-40">19-40 years</option>                <option value="41-60">41-60 years</option>                <option value="60+">60+ years</option>              </select>            </div>            <div class="col-md-4">              <button class="btn btn-success btn-block" onclick="showAddPatientModal()">                <i class="fas fa-plus mr-1"></i>Add Patient              </button>            </div>          </div>        </div>      </div>      <!-- Patients Table -->      <div class="card">        <div class="card-header">          <h3 class="card-title">            <i class="fas fa-list mr-2"></i>Patients Database          </h3>          <div class="card-tools">            <button type="button" class="btn btn-tool" onclick="refreshPatientsTable()">              <i class="fas fa-sync-alt"></i>            </button>            <button type="button" class="btn btn-tool" onclick="exportPatients()">              <i class="fas fa-download"></i>            </button>          </div>        </div>        <div class="card-body">          <div class="table-responsive">            <table id="patientsTable" class="table table-bordered table-striped table-hover">              <thead class="thead-light">                <tr>                  <th>Patient ID</th>                  <th>Full Name</th>                  <th>Contact</th>                  <th>Gender</th>                  <th>Age</th>                  <th>Registered</th>                  <th>Actions</th>                </tr>              </thead>              <tbody>                <!-- Data will be loaded via AJAX -->              </tbody>            </table>          </div>        </div>      </div>    </div>  </section></div><!-- Add Patient Modal --><div class="modal fade" id="addPatientModal" tabindex="-1" role="dialog">  <div class="modal-dialog modal-lg" role="document">    <div class="modal-content">      <div class="modal-header bg-success text-white">        <h5 class="modal-title">          <i class="fas fa-user-plus mr-2"></i>Add New Patient        </h5>        <button type="button" class="close text-white" data-dismiss="modal">          <span>&times;</span>        </button>      </div>      <form id="addPatientForm">        <div class="modal-body">          <div class="row">            <div class="col-md-6">              <div class="form-group">                <label for="add_name">Full Name <span class="text-danger">*</span></label>                <input type="text" class="form-control" id="add_name" name="name" required>                <div class="invalid-feedback"></div>              </div>            </div>            <div class="col-md-6">              <div class="form-group">                <label for="add_phone">Phone Number</label>                <input type="tel" class="form-control" id="add_phone" name="phone" maxlength="15">                <div class="invalid-feedback"></div>              </div>            </div>          </div>          <div class="row">            <div class="col-md-6">              <div class="form-group">                <label for="add_email">Email Address</label>                <input type="email" class="form-control" id="add_email" name="email">                <div class="invalid-feedback"></div>              </div>            </div>            <div class="col-md-6">              <div class="form-group">                <label for="add_date_of_birth">Date of Birth</label>                <input type="date" class="form-control" id="add_date_of_birth" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>">              </div>            </div>          </div>          <div class="row">            <div class="col-md-6">              <div class="form-group">                <label for="add_gender">Gender</label>                <select class="form-control" id="add_gender" name="gender">                  <option value="">Select Gender</option>                  <option value="male">Male</option>                  <option value="female">Female</option>                  <option value="other">Other</option>                </select>              </div>            </div>            <div class="col-md-6">              <div class="form-group">                <label for="add_address">Address</label>                <textarea class="form-control" id="add_address" name="address" rows="2" maxlength="500"></textarea>              </div>            </div>          </div>        </div>        <div class="modal-footer">          <button type="button" class="btn btn-secondary" data-dismiss="modal">            <i class="fas fa-times mr-1"></i>Cancel          </button>          <button type="submit" class="btn btn-success">            <i class="fas fa-save mr-1"></i>Add Patient          </button>        </div>      </form>    </div>  </div></div><!-- Edit Patient Modal --><div class="modal fade" id="editPatientModal" tabindex="-1" role="dialog">  <div class="modal-dialog modal-lg" role="document">    <div class="modal-content">      <div class="modal-header bg-primary text-white">        <h5 class="modal-title">          <i class="fas fa-user-edit mr-2"></i>Edit Patient        </h5>        <button type="button" class="close text-white" data-dismiss="modal">          <span>&times;</span>        </button>      </div>      <form id="editPatientForm">        <input type="hidden" id="edit_id" name="id">        <div class="modal-body">          <div class="row">            <div class="col-md-6">              <div class="form-group">                <label for="edit_name">Full Name <span class="text-danger">*</span></label>                <input type="text" class="form-control" id="edit_name" name="name" required>                <div class="invalid-feedback"></div>              </div>            </div>            <div class="col-md-6">              <div class="form-group">                <label for="edit_phone">Phone Number</label>                <input type="tel" class="form-control" id="edit_phone" name="phone" maxlength="15">                <div class="invalid-feedback"></div>              </div>            </div>          </div>          <div class="row">            <div class="col-md-6">              <div class="form-group">                <label for="edit_email">Email Address</label>                <input type="email" class="form-control" id="edit_email" name="email">                <div class="invalid-feedback"></div>              </div>            </div>            <div class="col-md-6">              <div class="form-group">                <label for="edit_date_of_birth">Date of Birth</label>                <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>">              </div>            </div>          </div>          <div class="row">            <div class="col-md-6">              <div class="form-group">                <label for="edit_gender">Gender</label>                <select class="form-control" id="edit_gender" name="gender">                  <option value="">Select Gender</option>                  <option value="male">Male</option>                  <option value="female">Female</option>                  <option value="other">Other</option>                </select>              </div>            </div>            <div class="col-md-6">              <div class="form-group">                <label for="edit_address">Address</label>                <textarea class="form-control" id="edit_address" name="address" rows="2" maxlength="500"></textarea>              </div>            </div>          </div>        </div>        <div class="modal-footer">          <button type="button" class="btn btn-secondary" data-dismiss="modal">            <i class="fas fa-times mr-1"></i>Cancel          </button>          <button type="submit" class="btn btn-primary">            <i class="fas fa-save mr-1"></i>Update Patient          </button>        </div>      </form>    </div>  </div></div><!-- View Patient Modal --><div class="modal fade" id="viewPatientModal" tabindex="-1" role="dialog">  <div class="modal-dialog modal-lg" role="document">    <div class="modal-content">      <div class="modal-header bg-info text-white">        <h5 class="modal-title">          <i class="fas fa-user mr-2"></i>Patient Details        </h5>        <button type="button" class="close text-white" data-dismiss="modal">          <span>&times;</span>        </button>      </div>      <div class="modal-body">        <div id="patientDetails">          <!-- Patient details will be loaded here -->        </div>      </div>      <div class="modal-footer">        <button type="button" class="btn btn-secondary" data-dismiss="modal">          <i class="fas fa-times mr-1"></i>Close        </button>        <button type="button" class="btn btn-primary" id="editFromViewBtn">          <i class="fas fa-edit mr-1"></i>Edit Patient        </button>      </div>    </div>  </div></div><?php include 'includes/footer.php'; ?><!-- Patients Management Script --><script>let patientsTable;let currentPatientId = null;$(document).ready(function() {    console.log('Patients page loading...');        // Initialize page    loadPatientsStats();    initializePatientsTable();    setupFormValidation();    setupEventHandlers();        // Auto-refresh stats every 30 seconds    setInterval(loadPatientsStats, 30000);        console.log('Patients page loaded successfully');});// Setup event handlersfunction setupEventHandlers() {    // Search on Enter    $('#searchPatients').on('keypress', function(e) {        if (e.which === 13) {            searchPatients();        }    });        // Real-time search with debounce    let searchTimeout;    $('#searchPatients').on('input', function() {        clearTimeout(searchTimeout);        searchTimeout = setTimeout(searchPatients, 300);    });        // Phone number formatting    $('#add_phone, #edit_phone').on('input', function() {        formatPhoneNumber($(this));    });        // Email validation on blur    $('#add_email, #edit_email').on('blur', function() {        if ($(this).val()) {            validateEmail($(this));        }    });        // Edit from view modal    $('#editFromViewBtn').on('click', function() {        $('#viewPatientModal').modal('hide');        if (currentPatientId) {            setTimeout(() => editPatient(currentPatientId), 300);        }    });}// Load patients statisticsfunction loadPatientsStats() {    $.ajax({        url: 'api/patients_api.php',        method: 'GET',        data: { action: 'stats' },        dataType: 'json',        success: function(response) {            if (response.success) {                const stats = response.data;                animateCounter('#totalPatients', stats.total || 0);                animateCounter('#todayRegistrations', stats.today || 0);                animateCounter('#malePatients', stats.male || 0);                animateCounter('#femalePatients', stats.female || 0);            }        },        error: function(xhr, status, error) {            console.log('Error loading patients statistics:', error);            // Set default values on error            $('#totalPatients').text('--');            $('#todayRegistrations').text('--');            $('#malePatients').text('--');            $('#femalePatients').text('--');        }    });}// Animate counterfunction animateCounter(selector, targetValue) {    const element = $(selector);    const currentValue = parseInt(element.text()) || 0;        if (currentValue === targetValue) return;        $({ counter: currentValue }).animate({ counter: targetValue }, {        duration: 1000,        easing: 'swing',        step: function() {            element.text(Math.ceil(this.counter));        },        complete: function() {            element.text(targetValue);        }    });}// Initialize patients tablefunction initializePatientsTable() {    console.log('Initializing patients table...');        patientsTable = $('#patientsTable').DataTable({        processing: true,        serverSide: false,        responsive: true,        pageLength: 25,        order: [[0, 'desc']],        ajax: {            url: 'api/patients_api.php',            data: { action: 'list' },            dataSrc: function(json) {                console.log('DataTables response:', json);                if (json.success) {                    return json.data;                } else {                    toastr.error(json.message || 'Error loading patients data');                    return [];                }            }        },        columns: [            {                 data: 'patient_id',                 width: '12%',                render: function(data) {                    return `<span class="badge badge-info">${data}</span>`;                }            },            {                 data: 'name',                 width: '20%',                render: function(data, type, row) {                    return `<strong>${data}</strong>`;                }            },            {                 data: null,                width: '20%',                render: function(data, type, row) {                    let contact = '';                    if (row.phone) {                        contact += `<div><i class="fas fa-phone text-success"></i> ${row.phone}</div>`;                    }                    if (row.email) {                        contact += `<div><i class="fas fa-envelope text-info"></i> ${row.email}</div>`;                    }                    return contact || '<span class="text-muted">No contact info</span>';                }            },            {                 data: 'gender',                 width: '10%',                render: function(data) {                    if (!data) return '<span class="text-muted">Not specified</span>';                    const icon = data === 'male' ? 'mars text-primary' :                                 data === 'female' ? 'venus text-danger' : 'genderless text-secondary';                    return `<i class="fas fa-${icon}"></i> ${data.charAt(0).toUpperCase() + data.slice(1)}`;                }            },            {                 data: 'date_of_birth',                width: '8%',                render: function(data) {                    if (!data) return '<span class="text-muted">--</span>';                    const age = calculateAge(data);                    return `${age} years`;                }            },            {                 data: 'created_at',                width: '12%',                render: function(data) {                    const date = new Date(data);                    return date.toLocaleDateString();                }            },            {                 data: null,                width: '18%',                orderable: false,                render: function(data, type, row) {                    return `                        <div class="btn-group" role="group">                            <button type="button" class="btn btn-sm btn-info" onclick="viewPatient(${row.id})" title="View Details">                                <i class="fas fa-eye"></i>                            </button>                            <button type="button" class="btn btn-sm btn-warning" onclick="editPatient(${row.id})" title="Edit Patient">                                <i class="fas fa-edit"></i>                            </button>                            <button type="button" class="btn btn-sm btn-danger" onclick="deletePatient(${row.id})" title="Delete Patient">                                <i class="fas fa-trash"></i>                            </button>                        </div>                    `;                }            }        ],        language: {            processing: '<i class="fas fa-spinner fa-spin"></i> Loading patients...',            emptyTable: 'No patients found',            zeroRecords: 'No matching patients found'        }    });        console.log('Patients table initialized');}// Setup form validationfunction setupFormValidation() {    // Add patient form    $('#addPatientForm').on('submit', function(e) {        e.preventDefault();        if (validatePatientForm($(this))) {            submitPatientForm('add', $(this));        }    });        // Edit patient form    $('#editPatientForm').on('submit', function(e) {        e.preventDefault();        if (validatePatientForm($(this))) {            submitPatientForm('update', $(this));        }    });}// Validate patient formfunction validatePatientForm(form) {    let isValid = true;        // Clear previous validation    form.find('.form-control').removeClass('is-invalid is-valid');        // Validate name    const nameField = form.find('input[name="name"]');    if (!nameField.val().trim()) {        showFieldError(nameField, 'Patient name is required');        isValid = false;    } else {        showFieldSuccess(nameField);    }        // Validate email if provided    const emailField = form.find('input[name="email"]');    if (emailField.val() && !validateEmail(emailField)) {        isValid = false;    }        // Validate phone if provided    const phoneField = form.find('input[name="phone"]');    if (phoneField.val() && !validatePhone(phoneField)) {        isValid = false;    }        return isValid;}// Show field errorfunction showFieldError(field, message) {    field.addClass('is-invalid');    field.siblings('.invalid-feedback').text(message);}// Show field successfunction showFieldSuccess(field) {    field.removeClass('is-invalid').addClass('is-valid');}// Validate emailfunction validateEmail(field) {    const email = field.val().trim();    if (!email) return true;        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;    const isValid = emailRegex.test(email);        if (!isValid) {        showFieldError(field, 'Please enter a valid email address');    } else {        showFieldSuccess(field);    }        return isValid;}// Validate phonefunction validatePhone(field) {    const phone = field.val().replace(/\D/g, '');    if (!phone) return true;        const isValid = phone.length >= 10;        if (!isValid) {        showFieldError(field, 'Please enter a valid phone number (at least 10 digits)');    } else {        showFieldSuccess(field);    }        return isValid;}// Format phone numberfunction formatPhoneNumber(field) {    let value = field.val().replace(/\D/g, '');    if (value.length >= 6) {        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');    } else if (value.length >= 3) {        value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');    }    field.val(value);}// Calculate agefunction calculateAge(dateOfBirth) {    const today = new Date();    const birthDate = new Date(dateOfBirth);    let age = today.getFullYear() - birthDate.getFullYear();    const monthDiff = today.getMonth() - birthDate.getMonth();        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {        age--;    }        return age;}// Submit patient formfunction submitPatientForm(action, form) {    const submitBtn = form.find('button[type="submit"]');    const originalText = submitBtn.html();        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...').prop('disabled', true);        const formData = new FormData(form[0]);    formData.append('action', action);        $.ajax({        url: 'api/patients_api.php',        type: 'POST',        data: formData,        processData: false,        contentType: false,        dataType: 'json',        success: function(response) {            if (response.success) {                toastr.success(response.message);                                if (action === 'add') {                    $('#addPatientModal').modal('hide');                    form[0].reset();                    form.find('.form-control').removeClass('is-invalid is-valid');                } else {                    $('#editPatientModal').modal('hide');                }                                refreshPatientsTable();                loadPatientsStats();            } else {                toastr.error(response.message);            }        },        error: function(xhr, status, error) {            console.error('AJAX Error:', error);            toastr.error('An error occurred while saving the patient');        },        complete: function() {            submitBtn.html(originalText).prop('disabled', false);        }    });}// Show add patient modalfunction showAddPatientModal() {    $('#addPatientForm')[0].reset();    $('#addPatientForm .form-control').removeClass('is-invalid is-valid');    $('#addPatientModal').modal('show');}// View patient detailsfunction viewPatient(id) {    $.ajax({        url: 'api/patients_api.php',        type: 'GET',        data: { action: 'get', id: id },        dataType: 'json',        success: function(response) {            if (response.success) {                const patient = response.data;                currentPatientId = patient.id;                                const age = patient.date_of_birth ? calculateAge(patient.date_of_birth) : 'Not specified';                const registeredDate = new Date(patient.created_at).toLocaleDateString();                                const detailsHtml = `                    <div class="row">                        <div class="col-md-6">                            <table class="table table-borderless">                                <tr>                                    <th width="40%">Patient ID:</th>                                    <td><span class="badge badge-info">${patient.patient_id}</span></td>                                </tr>                                <tr>                                    <th>Full Name:</th>                                    <td><strong>${patient.name}</strong></td>                                </tr>                                <tr>                                    <th>Phone:</th>                                    <td>${patient.phone || 'Not provided'}</td>                                </tr>                                <tr>                                    <th>Email:</th>                                    <td>${patient.email || 'Not provided'}</td>                                </tr>                            </table>                        </div>                        <div class="col-md-6">                            <table class="table table-borderless">                                <tr>                                    <th width="40%">Date of Birth:</th>                                    <td>${patient.date_of_birth || 'Not specified'}</td>                                </tr>                                <tr>                                    <th>Age:</th>                                    <td>${age} years</td>                                </tr>                                <tr>                                    <th>Gender:</th>                                    <td>${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'Not specified'}</td>                                </tr>                                <tr>                                    <th>Registered:</th>                                    <td>${registeredDate}</td>                                </tr>                            </table>                        </div>                    </div>                    ${patient.address ? `                    <div class="row mt-3">
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
}

// Edit patient
function editPatient(id) {
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
}

// Delete patient
function deletePatient(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this action!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/patients_api.php',
                type: 'DELETE',
                data: JSON.stringify({ id: id }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        refreshPatientsTable();
                        loadPatientsStats();
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
}

// Search patients
function searchPatients() {
    const searchTerm = $('#searchPatients').val();
    patientsTable.search(searchTerm).draw();
    
    if (searchTerm) {
        toastr.info(`Searching for: "${searchTerm}"`);
    }
}

// Clear search
function clearSearch() {
    $('#searchPatients').val('');
    patientsTable.search('').draw();
    toastr.info('Search cleared');
}

// Filter patients
function filterPatients() {
    const genderFilter = $('#genderFilter').val();
    
    // Apply filters to DataTable
    if (genderFilter) {
        patientsTable.column(3).search(genderFilter).draw();
        toastr.info(`Filtered by gender: ${genderFilter}`);
    } else {
        patientsTable.column(3).search('').draw();
        toastr.info('Gender filter cleared');
    }
}

// Refresh patients table
function refreshPatientsTable() {
    patientsTable.ajax.reload(null, false);
    toastr.success('Patients table refreshed');
}

// Export patients
function exportPatients() {
    toastr.info('Preparing export...');
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'export' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Create download link
                const blob = new Blob([response.data], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                const filename = response.filename || `patients_export_${new Date().getTime()}.csv`;
                
                a.style.display = 'none';
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                toastr.success('Patients data exported successfully');
            } else {
                toastr.error('Export failed: ' + response.message);
            }
        },
        error: function() {
            toastr.error('Export failed due to server error');
        }
    });
}
</script>

<?php
require_once '../inc/branch-header.php'; // includes config, db, session check
?>
<div class="container mt-4">
    <h2 class="mb-4">Create New Test Report</h2>
    <div id="alert-area"></div>
    <form id="newReportForm" autocomplete="off">
        <div class="mb-3">
            <label for="patient_id" class="form-label">Select Patient</label>
            <select class="form-select" id="patient_id" name="patient_id" required>
                <option value="">Loading patients...</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="test_id" class="form-label">Select Test</label>
            <select class="form-select" id="test_id" name="test_id" required>
                <option value="">Loading tests...</option>
            </select>
        </div>
        <div id="test-parameters-area"></div>
        <div class="mb-3">
            <label for="comments" class="form-label">Comments/Notes</label>
            <textarea class="form-control" id="comments" name="comments" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Report</button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    // Load patients
    $.getJSON('../branch-admin/ajax/get-patients.php', function(data) {
        var $sel = $('#patient_id');
        $sel.empty();
        if(data.success && data.patients.length) {
            $sel.append('<option value="">Select Patient</option>');
            $.each(data.patients, function(i, p) {
                $sel.append('<option value="'+p.id+'">'+p.name+' ('+p.age+'y, '+p.gender+')</option>');
            });
        } else {
            $sel.append('<option value="">No patients found</option>');
        }
    });
    // Load tests and parameters
    var testParams = {};
    $.getJSON('../branch-admin/ajax/get-test-parameters.php', function(data) {
        var $sel = $('#test_id');
        $sel.empty();
        if(data.success && data.tests.length) {
            $sel.append('<option value="">Select Test</option>');
            $.each(data.tests, function(i, t) {
                $sel.append('<option value="'+t.id+'">'+t.test_name+' (₹'+t.price+')</option>');
            });
            testParams = data.parameters;
        } else {
            $sel.append('<option value="">No tests found</option>');
        }
    });
    // Show parameters when test selected
    $('#test_id').on('change', function() {
        var testId = $(this).val();
        var $area = $('#test-parameters-area');
        $area.empty();
        if(testId && testParams[testId]) {
            $area.append('<label class="form-label">Enter Test Results</label>');
            $.each(testParams[testId], function(i, param) {
                $area.append('<div class="mb-2"><label>'+param.parameter_name+' ('+param.default_unit+')'+(param.normal_value ? ' <small class="text-muted">[Normal: '+param.normal_value+']</small>' : '')+'</label><input type="text" class="form-control" name="result['+param.parameter_name+']" required></div>');
            });
        } else if(testId) {
            $area.append('<div class="mb-2"><label>Result</label><input type="text" class="form-control" name="result" required></div>');
        }
    });
    // Submit form
    $('#newReportForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.post('../branch-admin/ajax/add-report.php', formData, function(resp) {
            var alertType = resp.success ? 'success' : 'danger';
            $('#alert-area').html('<div class="alert alert-'+alertType+'">'+resp.message+'</div>');
            if(resp.success) {
                $('#newReportForm')[0].reset();
                $('#test-parameters-area').empty();
            }
        }, 'json');
    });
});
</script>
<?php require_once '../inc/footer.php'; ?>

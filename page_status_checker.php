<?php
/**
 * Quick Page Status Checker
 * Tests all main pages for accessibility and basic functionality
 */

// Include configuration
require_once 'config.php';

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set up a test session (temporary admin login)
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['full_name'] = 'Test Admin';
$_SESSION['user_type'] = 'admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Status Checker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">PathLab Pro - Page Status Checker</h1>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-check-circle"></i> Page Accessibility Test</h5>
                    </div>
                    <div class="card-body">
                        <div id="pageTests">
                            <div class="text-center">
                                <button class="btn btn-primary" onclick="runPageTests()">
                                    <i class="fas fa-play"></i> Test All Pages
                                </button>
                            </div>
                            <div id="testResults"></div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-link"></i> Quick Page Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Main Pages</h6>
                                <ul class="list-unstyled">
                                    <li><a href="dashboard.php" target="_blank"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <li><a href="patients.php" target="_blank"><i class="fas fa-users"></i> Patients</a></li>
                                    <li><a href="doctors.php" target="_blank"><i class="fas fa-user-md"></i> Doctors</a></li>
                                    <li><a href="tests.php" target="_blank"><i class="fas fa-flask"></i> Tests</a></li>
                                    <li><a href="results.php" target="_blank"><i class="fas fa-file-medical"></i> Results</a></li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6>Management Pages</h6>
                                <ul class="list-unstyled">
                                    <li><a href="test-orders.php" target="_blank"><i class="fas fa-clipboard-list"></i> Test Orders</a></li>
                                    <li><a href="equipment.php" target="_blank"><i class="fas fa-microscope"></i> Equipment</a></li>
                                    <li><a href="users.php" target="_blank"><i class="fas fa-user-cog"></i> Users</a></li>
                                    <li><a href="reports.php" target="_blank"><i class="fas fa-chart-bar"></i> Reports</a></li>
                                    <li><a href="settings.php" target="_blank"><i class="fas fa-cog"></i> Settings</a></li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6>Test Pages</h6>
                                <ul class="list-unstyled">
                                    <li><a href="comprehensive_function_test.php" target="_blank"><i class="fas fa-microscope"></i> Function Test</a></li>
                                    <li><a href="auto_fix_issues.php" target="_blank"><i class="fas fa-wrench"></i> Auto Fix</a></li>
                                    <li><a href="test_system.php" target="_blank"><i class="fas fa-cogs"></i> System Test</a></li>
                                    <li><a href="setup_test.php" target="_blank"><i class="fas fa-tools"></i> Setup Test</a></li>
                                    <li><a href="diagnostic.php" target="_blank"><i class="fas fa-stethoscope"></i> Diagnostics</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function runPageTests() {
        const pages = [
            {name: 'Dashboard', url: 'dashboard.php'},
            {name: 'Patients', url: 'patients.php'},
            {name: 'Doctors', url: 'doctors.php'},
            {name: 'Tests', url: 'tests.php'},
            {name: 'Test Orders', url: 'test-orders.php'},
            {name: 'Results', url: 'results.php'},
            {name: 'Equipment', url: 'equipment.php'},
            {name: 'Users', url: 'users.php'},
            {name: 'Reports', url: 'reports.php'},
            {name: 'Settings', url: 'settings.php'}
        ];
        
        const resultsDiv = document.getElementById('testResults');
        resultsDiv.innerHTML = '<div class="text-center mt-3"><i class="fas fa-spinner fa-spin"></i> Testing pages...</div>';
        
        let results = [];
        let completed = 0;
        
        pages.forEach(page => {
            fetch(page.url, {method: 'HEAD'})
                .then(response => {
                    const status = response.ok ? 'success' : 'error';
                    const statusCode = response.status;
                    results.push({
                        name: page.name,
                        url: page.url,
                        status: status,
                        statusCode: statusCode,
                        message: response.ok ? 'Page accessible' : `HTTP ${statusCode}`
                    });
                })
                .catch(error => {
                    results.push({
                        name: page.name,
                        url: page.url,
                        status: 'error',
                        statusCode: 0,
                        message: 'Connection failed'
                    });
                })
                .finally(() => {
                    completed++;
                    if (completed === pages.length) {
                        displayResults(results);
                    }
                });
        });
    }
    
    function displayResults(results) {
        const resultsDiv = document.getElementById('testResults');
        let html = '<div class="mt-4"><h6>Test Results:</h6>';
        
        results.forEach(result => {
            const iconClass = result.status === 'success' ? 'fas fa-check text-success' : 'fas fa-times text-danger';
            const statusClass = result.status === 'success' ? 'text-success' : 'text-danger';
            
            html += `
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <i class="${iconClass}"></i>
                        <strong>${result.name}</strong>
                        <small class="text-muted">(${result.url})</small>
                    </div>
                    <div class="${statusClass}">
                        ${result.message}
                        ${result.status === 'success' ? '<a href="' + result.url + '" target="_blank" class="btn btn-sm btn-outline-primary ml-2">Open</a>' : ''}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        // Add summary
        const successCount = results.filter(r => r.status === 'success').length;
        const totalCount = results.length;
        const percentage = Math.round((successCount / totalCount) * 100);
        
        html += `
            <div class="alert alert-info mt-3">
                <strong>Summary:</strong> ${successCount}/${totalCount} pages accessible (${percentage}%)
                ${percentage === 100 ? '<i class="fas fa-check-circle text-success ml-2"></i>' : ''}
            </div>
        `;
        
        resultsDiv.innerHTML = html;
    }
    </script>
</body>
</html>

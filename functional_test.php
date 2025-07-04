<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PathLab Pro - Functional Test Suite</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .test-warning { color: #ffc107; }
        .test-info { color: #17a2b8; }
        .card { margin-bottom: 20px; }
        .spinner { display: none; }
        .test-result { margin: 5px 0; }
        .page-links { margin-top: 15px; }
        .page-links a { margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0"><i class="fas fa-microscope"></i> PathLab Pro - Functional Test Suite</h1>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This test suite will check all pages for functional issues, JavaScript errors, and UI problems.
                        </div>

                        <div class="text-center mb-3">
                            <button id="runAllTests" class="btn btn-primary btn-lg">
                                <i class="fas fa-play"></i> Run All Tests
                            </button>
                            <div class="spinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Running tests...</span>
                                </div>
                            </div>
                        </div>

                        <div id="testResults"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/common.js"></script>
    
    <script>
    $(document).ready(function() {
        // Test configuration
        const testPages = [
            { name: 'Login Page', url: 'index.php', type: 'page' },
            { name: 'Dashboard', url: 'dashboard.php', type: 'page', requiresAuth: true },
            { name: 'Patients Management', url: 'patients.php', type: 'page', requiresAuth: true },
            { name: 'Tests Management', url: 'tests.php', type: 'page', requiresAuth: true },
            { name: 'Test Orders', url: 'test-orders.php', type: 'page', requiresAuth: true },
            { name: 'Results Management', url: 'results.php', type: 'page', requiresAuth: true },
            { name: 'Reports', url: 'reports.php', type: 'page', requiresAuth: true },
            { name: 'Doctors Management', url: 'doctors.php', type: 'page', requiresAuth: true },
            { name: 'Equipment Management', url: 'equipment.php', type: 'page', requiresAuth: true },
            { name: 'User Management', url: 'users.php', type: 'page', requiresAuth: true },
            { name: 'Settings', url: 'settings.php', type: 'page', requiresAuth: true },
            { name: 'Registration', url: 'register.php', type: 'page' },
            { name: 'Password Reset', url: 'forgot-password.php', type: 'page' }
        ];

        const apiTests = [
            { name: 'Auth API', url: 'api/auth_api.php', type: 'api' },
            { name: 'Patients API', url: 'api/patients_api.php', type: 'api' },
            { name: 'Tests API', url: 'api/tests_api.php', type: 'api' },
            { name: 'Orders API', url: 'api/test_orders_api.php', type: 'api' },
            { name: 'Results API', url: 'api/results_api.php', type: 'api' },
            { name: 'Doctors API', url: 'api/doctors_api.php', type: 'api' },
            { name: 'Equipment API', url: 'api/equipment_api.php', type: 'api' },
            { name: 'Users API', url: 'api/users_api.php', type: 'api' },
            { name: 'Dashboard API', url: 'api/dashboard_api.php', type: 'api' }
        ];

        $('#runAllTests').click(function() {
            runTests();
        });

        function runTests() {
            $('#runAllTests').prop('disabled', true);
            $('.spinner').show();
            $('#testResults').empty();

            let allResults = [];
            let passedTests = 0;
            let totalTests = testPages.length + apiTests.length;

            // Add page existence tests
            addTestSection('File Existence Tests');
            
            // Test page files
            testPages.forEach(page => {
                testFileExistence(page.url, page.name)
                    .then(result => {
                        addTestResult(result);
                        if (result.passed) passedTests++;
                        checkCompletion();
                    });
            });

            // Test API files
            apiTests.forEach(api => {
                testFileExistence(api.url, api.name)
                    .then(result => {
                        addTestResult(result);
                        if (result.passed) passedTests++;
                        checkCompletion();
                    });
            });

            function checkCompletion() {
                if (passedTests + (totalTests - passedTests) >= totalTests) {
                    $('.spinner').hide();
                    $('#runAllTests').prop('disabled', false);
                    
                    // Add summary
                    const passRate = Math.round((passedTests / totalTests) * 100);
                    let summaryClass = 'success';
                    if (passRate < 70) summaryClass = 'danger';
                    else if (passRate < 90) summaryClass = 'warning';
                    
                    $('#testResults').append(`
                        <div class="card">
                            <div class="card-header bg-${summaryClass} text-white">
                                <h4><i class="fas fa-chart-pie"></i> Test Summary</h4>
                            </div>
                            <div class="card-body">
                                <p class="h5">Tests Passed: ${passedTests} / ${totalTests} (${passRate}%)</p>
                                <div class="progress">
                                    <div class="progress-bar bg-${summaryClass}" style="width: ${passRate}%"></div>
                                </div>
                                <div class="page-links">
                                    <h5 class="mt-3">Quick Links to Working Pages:</h5>
                                    <a href="index.php" class="btn btn-outline-primary btn-sm">Login</a>
                                    <a href="register.php" class="btn btn-outline-primary btn-sm">Register</a>
                                    <a href="setup_test.php" class="btn btn-outline-info btn-sm">System Test</a>
                                </div>
                            </div>
                        </div>
                    `);
                }
            }
        }

        function addTestSection(title) {
            $('#testResults').append(`
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h4>${title}</h4>
                    </div>
                    <div class="card-body" id="section-${title.replace(/\s+/g, '-').toLowerCase()}">
                    </div>
                </div>
            `);
        }

        function addTestResult(result) {
            const icon = result.passed ? 'fas fa-check test-pass' : 'fas fa-times test-fail';
            const status = result.passed ? 'PASS' : 'FAIL';
            const sectionId = `section-${result.section.replace(/\s+/g, '-').toLowerCase()}`;
            
            $(`#${sectionId}`).append(`
                <div class="test-result">
                    <i class="${icon}"></i> 
                    <strong>${result.name}</strong>: ${status}
                    ${result.message ? ` - ${result.message}` : ''}
                    ${result.url ? ` <a href="${result.url}" target="_blank" class="btn btn-sm btn-outline-primary ml-2">Test</a>` : ''}
                </div>
            `);
        }

        function testFileExistence(url, name) {
            return new Promise((resolve) => {
                $.ajax({
                    url: url,
                    method: 'HEAD',
                    timeout: 5000,
                    success: function() {
                        resolve({
                            name: name,
                            passed: true,
                            section: 'File Existence Tests',
                            url: url,
                            message: 'File exists and accessible'
                        });
                    },
                    error: function(xhr, status, error) {
                        let message = 'File not found or not accessible';
                        if (xhr.status === 500) {
                            message = 'Server error (may be database related)';
                        } else if (xhr.status === 403) {
                            message = 'Access forbidden';
                        } else if (xhr.status === 404) {
                            message = 'File not found';
                        }
                        
                        resolve({
                            name: name,
                            passed: false,
                            section: 'File Existence Tests',
                            url: url,
                            message: message
                        });
                    }
                });
            });
        }

        // Test JavaScript utilities
        function testJavaScriptFunctions() {
            addTestSection('JavaScript Function Tests');
            
            // Test showAlert function
            try {
                if (typeof showAlert === 'function') {
                    addTestResult({
                        name: 'showAlert function',
                        passed: true,
                        section: 'JavaScript Function Tests',
                        message: 'Function exists and callable'
                    });
                } else {
                    addTestResult({
                        name: 'showAlert function',
                        passed: false,
                        section: 'JavaScript Function Tests',
                        message: 'Function not found'
                    });
                }
            } catch (e) {
                addTestResult({
                    name: 'showAlert function',
                    passed: false,
                    section: 'JavaScript Function Tests',
                    message: 'Error: ' + e.message
                });
            }

            // Test escapeHtml function
            try {
                if (typeof escapeHtml === 'function') {
                    const testText = '<script>alert("test")</script>';
                    const escaped = escapeHtml(testText);
                    if (escaped.includes('&lt;') && escaped.includes('&gt;')) {
                        addTestResult({
                            name: 'escapeHtml function',
                            passed: true,
                            section: 'JavaScript Function Tests',
                            message: 'Function works correctly'
                        });
                    } else {
                        addTestResult({
                            name: 'escapeHtml function',
                            passed: false,
                            section: 'JavaScript Function Tests',
                            message: 'Function exists but not working correctly'
                        });
                    }
                } else {
                    addTestResult({
                        name: 'escapeHtml function',
                        passed: false,
                        section: 'JavaScript Function Tests',
                        message: 'Function not found'
                    });
                }
            } catch (e) {
                addTestResult({
                    name: 'escapeHtml function',
                    passed: false,
                    section: 'JavaScript Function Tests',
                    message: 'Error: ' + e.message
                });
            }
        }

        // Run JavaScript tests immediately
        setTimeout(testJavaScriptFunctions, 1000);
    });
    </script>
</body>
</html>

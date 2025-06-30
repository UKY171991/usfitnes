<?php
// Set page title
$page_title = 'All Database Data';

// Include header
include 'includes/header.php';
include 'includes/sidebar.php';

// Include database config
require_once 'config.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Database Data Overview</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">All Data</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Quick Actions -->
      <div class="row mb-3">
        <div class="col-12">
          <div class="card bg-info">
            <div class="card-header">
              <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
              <a href="patients.php" class="btn btn-primary mr-2"><i class="fas fa-users"></i> View Patients</a>
              <a href="doctors.php" class="btn btn-success mr-2"><i class="fas fa-user-md"></i> View Doctors</a>
              <a href="tests.php" class="btn btn-warning mr-2"><i class="fas fa-flask"></i> View Tests</a>
              <a href="users.php" class="btn btn-info mr-2"><i class="fas fa-user-cog"></i> View Users</a>
              <button class="btn btn-secondary" onclick="location.reload()"><i class="fas fa-sync"></i> Refresh Data</button>
            </div>
          </div>
        </div>
      </div>

      <?php
      // Function to display table data
      function displayTableData($pdo, $tableName, $displayName, $limit = 10) {
          try {
              // Check if table exists
              $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
              $stmt->execute([$tableName]);
              if ($stmt->rowCount() == 0) {
                  echo "<div class='alert alert-warning'>Table '$tableName' does not exist.</div>";
                  return;
              }

              // Get table structure
              $stmt = $pdo->query("DESCRIBE $tableName");
              $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
              
              // Get data count
              $stmt = $pdo->query("SELECT COUNT(*) FROM $tableName");
              $count = $stmt->fetchColumn();
              
              // Get sample data
              $stmt = $pdo->query("SELECT * FROM $tableName ORDER BY id DESC LIMIT $limit");
              $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
              
              echo "<div class='card'>";
              echo "<div class='card-header'>";
              echo "<h3 class='card-title'>$displayName</h3>";
              echo "<div class='card-tools'>";
              echo "<span class='badge badge-primary'>$count records</span>";
              echo "</div>";
              echo "</div>";
              echo "<div class='card-body'>";
              
              if (empty($data)) {
                  echo "<div class='alert alert-info'>No data found in $tableName table.</div>";
              } else {
                  echo "<div class='table-responsive'>";
                  echo "<table class='table table-bordered table-striped table-sm'>";
                  echo "<thead><tr>";
                  
                  // Table headers
                  foreach ($columns as $column) {
                      echo "<th>" . htmlspecialchars($column['Field']) . "</th>";
                  }
                  echo "</tr></thead><tbody>";
                  
                  // Table data
                  foreach ($data as $row) {
                      echo "<tr>";
                      foreach ($row as $value) {
                          $displayValue = $value;
                          if (strlen($value) > 50) {
                              $displayValue = substr($value, 0, 50) . '...';
                          }
                          echo "<td>" . htmlspecialchars($displayValue) . "</td>";
                      }
                      echo "</tr>";
                  }
                  
                  echo "</tbody></table>";
                  echo "</div>";
                  
                  if ($count > $limit) {
                      echo "<div class='alert alert-info'>Showing latest $limit of $count records.</div>";
                  }
              }
              
              echo "</div></div>";
              
          } catch (PDOException $e) {
              echo "<div class='card'>";
              echo "<div class='card-header'><h3 class='card-title'>$displayName</h3></div>";
              echo "<div class='card-body'>";
              echo "<div class='alert alert-danger'>Error accessing $tableName: " . htmlspecialchars($e->getMessage()) . "</div>";
              echo "</div></div>";
          }
      }

      // Display all main tables
      echo "<div class='row'>";
      
      echo "<div class='col-md-6'>";
      displayTableData($pdo, 'users', 'System Users', 5);
      echo "</div>";
      
      echo "<div class='col-md-6'>";
      displayTableData($pdo, 'patients', 'Patients', 5);
      echo "</div>";
      
      echo "</div><div class='row'>";
      
      echo "<div class='col-md-6'>";
      displayTableData($pdo, 'doctors', 'Doctors', 5);
      echo "</div>";
      
      echo "<div class='col-md-6'>";
      displayTableData($pdo, 'tests', 'Lab Tests (from config)', 5);
      echo "</div>";
      
      echo "</div><div class='row'>";
      
      echo "<div class='col-md-6'>";
      displayTableData($pdo, 'lab_tests', 'Lab Tests (dynamic)', 5);
      echo "</div>";
      
      echo "<div class='col-md-6'>";
      displayTableData($pdo, 'test_categories', 'Test Categories', 5);
      echo "</div>";
      
      echo "</div><div class='row'>";
      
      echo "<div class='col-md-6'>";
      displayTableData($pdo, 'test_orders', 'Test Orders', 5);
      echo "</div>";
      
      echo "<div class='col-md-6'>";
      displayTableData($pdo, 'equipment', 'Equipment', 5);
      echo "</div>";
      
      echo "</div>";
      ?>

      <!-- Database Stats -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Database Statistics</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <?php
                $tables = ['users', 'patients', 'doctors', 'tests', 'lab_tests', 'test_categories', 'test_orders', 'equipment'];
                foreach ($tables as $table) {
                    try {
                        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                        $stmt->execute([$table]);
                        if ($stmt->rowCount() > 0) {
                            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                            $count = $stmt->fetchColumn();
                            echo "<div class='col-md-3 col-sm-6'>";
                            echo "<div class='info-box'>";
                            echo "<span class='info-box-icon bg-info'><i class='fas fa-table'></i></span>";
                            echo "<div class='info-box-content'>";
                            echo "<span class='info-box-text'>" . ucfirst($table) . "</span>";
                            echo "<span class='info-box-number'>$count</span>";
                            echo "</div></div></div>";
                        } else {
                            echo "<div class='col-md-3 col-sm-6'>";
                            echo "<div class='info-box'>";
                            echo "<span class='info-box-icon bg-warning'><i class='fas fa-exclamation-triangle'></i></span>";
                            echo "<div class='info-box-content'>";
                            echo "<span class='info-box-text'>" . ucfirst($table) . "</span>";
                            echo "<span class='info-box-number'>Missing</span>";
                            echo "</div></div></div>";
                        }
                    } catch (PDOException $e) {
                        echo "<div class='col-md-3 col-sm-6'>";
                        echo "<div class='info-box'>";
                        echo "<span class='info-box-icon bg-danger'><i class='fas fa-times'></i></span>";
                        echo "<div class='info-box-content'>";
                        echo "<span class='info-box-text'>" . ucfirst($table) . "</span>";
                        echo "<span class='info-box-number'>Error</span>";
                        echo "</div></div></div>";
                    }
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- API Testing -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">API Tests</h3>
            </div>
            <div class="card-body">
              <button class="btn btn-primary mr-2" onclick="testAPI('patients_api.php')">Test Patients API</button>
              <button class="btn btn-success mr-2" onclick="testAPI('doctors_api.php')">Test Doctors API</button>
              <button class="btn btn-warning mr-2" onclick="testAPI('tests_api.php?action=list')">Test Tests API</button>
              <button class="btn btn-info mr-2" onclick="testAPI('users_api.php?action=get_stats')">Test Users API</button>
              <div id="apiResults" class="mt-3"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
$(document).ready(function() {
    // Auto-refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
});

function testAPI(endpoint) {
    $('#apiResults').html('<div class="alert alert-info">Testing ' + endpoint + '...</div>');
    
    $.ajax({
        url: 'api/' + endpoint,
        method: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            var html = '<div class="alert alert-success">';
            html += '<strong>✓ ' + endpoint + ' - SUCCESS</strong><br>';
            html += '<small>Response: ' + JSON.stringify(response, null, 2).substring(0, 200) + '...</small>';
            html += '</div>';
            $('#apiResults').html(html);
        },
        error: function(xhr, status, error) {
            var html = '<div class="alert alert-danger">';
            html += '<strong>✗ ' + endpoint + ' - ERROR</strong><br>';
            html += '<small>Status: ' + status + ' | Error: ' + error + '</small>';
            if (xhr.responseText) {
                html += '<br><small>Response: ' + xhr.responseText.substring(0, 200) + '...</small>';
            }
            html += '</div>';
            $('#apiResults').html(html);
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>

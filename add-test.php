<?php
include('conn.php');
?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | Simple Tables</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="AdminLTE 4 | Simple Tables" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard"
    />
    <!--end::Primary Meta Tags-->
    <?php include('inc/head.php'); ?>
    <!--begin::Fonts-->
    
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <?php include('inc/top.php'); ?>
      
      <!--end::Header-->
      <!--begin::Sidebar-->
      <?php include('inc/sidebar.php'); ?>
      
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 mt-2">
                    <div class="col-sm-6">
                        <h3>Patient Management</h3>
                    </div>
                    <div class="col-sm-6 text-end">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#newPatientModal">
                            <i class="fas fa-user-plus"></i> New Patient
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-md-12">
                <div class="card mb-4">
                  <div class="card-header"><h3 class="card-title">Patient List</h3></div>
                  <!-- /.card-header -->
                  <div class="card-body">

                  	<?php

					if(isset($_SESSION['success'])){
					    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>"
					        .$_SESSION['success'].
					        "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>
					    </div>";
					    unset($_SESSION['success']);
					}

					if(isset($_SESSION['error'])){
					    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>"
					        .$_SESSION['error'].
					        "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>
					    </div>";
					    unset($_SESSION['error']);
					}
					?>


					<!-- Test Insert Form integrated with AdminLTE 4 Template -->
					<form action="includes/insert-test.php" method="POST">
					    <div class="card mb-4">
					        <div class="card-header bg-primary">
					            <h3 class="card-title text-white">Add New Test</h3>
					        </div>
					        <div class="card-body">
					            <div class="row">
					                <div class="col-md-6 mb-3">
					                    <label>Test Name</label>
					                    <input type="text" class="form-control" name="test_name" placeholder="Enter Test Name" required>
					                </div>
					                <div class="col-md-6 mb-3">
					                    <label>Category</label>
					                    <select class="form-control" name="category" required>
					                        <option value="">Select Category</option>
					                        <option>Blood Test</option>
					                        <option>Urine Test</option>
					                        <option>Imaging Test</option>
					                    </select>
					                </div>
					                <div class="col-md-12 mb-3">
					                    <label>Parameters</label>
					                    <textarea class="form-control" name="parameters" placeholder="Enter Parameters (comma separated)" required></textarea>
					                </div>
					                <div class="col-md-12 mb-3">
					                    <label>Reference Range</label>
					                    <textarea class="form-control" name="reference_range" placeholder="Reference Ranges"></textarea>
					                </div>
					                <div class="col-md-6 mb-3">
					                    <label>Price ($)</label>
					                    <input type="number" step="0.01" class="form-control" name="price" required placeholder="Price">
					                </div>
					            </div>
					        </div>
					        <div class="card-footer text-end">
					            <button type="submit" class="btn btn-success">
					                <i class="fa fa-save"></i> Save Test
					            </button>
					        </div>
					    </div>
					</form>
                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-end">
                      <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                      <li class="page-item"><a class="page-link" href="#">1</a></li>
                      <li class="page-item"><a class="page-link" href="#">2</a></li>
                      <li class="page-item"><a class="page-link" href="#">3</a></li>
                      <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                    </ul>
                  </div>
                </div>
                <!-- /.card -->
                
                <!-- /.card -->
              </div>
              <!-- /.col -->
              
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2014-2024&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <?php include('inc/js.php'); ?>
    
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand px-4 py-3 m-0" href="dashboard.php">
        <img src="assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
        <span class="ms-1 text-sm text-dark">Creative Tim</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active bg-gradient-dark text-white" href="dashboard.php">
            <i class="material-symbols-rounded opacity-5">dashboard</i>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-dark" href="view-income.php">
            <i class="material-symbols-rounded opacity-5">attach_money</i>
            <span class="nav-link-text ms-1">Income</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-dark" href="view-expenditure.php">
            <i class="material-symbols-rounded opacity-5">attach_money</i>
            <span class="nav-link-text ms-1">Expenditure</span>
          </a>
        </li>


     <li class="nav-item">
		  <a class="nav-link text-dark" data-bs-toggle="collapse" href="#incomeSubmenu" role="button" aria-expanded="false" aria-controls="incomeSubmenu">
		    <i class="material-symbols-rounded opacity-5">attach_money</i>
		    <span class="nav-link-text ms-1">Expenditure</span>
		    <i class="fa fa-angle-down ms-auto"></i>
		  </a>
		  <div class="collapse" id="incomeSubmenu">
		    <ul class="nav flex-column ms-3">
		      <li class="nav-item">
		        <a class="nav-link text-dark" href="expenditure-category.php">
		          <i class="material-symbols-rounded opacity-5">category</i>
		          <span class="nav-link-text ms-1">Expenditure Category</span>
		        </a>
		      </li>
		      <li class="nav-item">
		        <a class="nav-link text-dark" href="expenditure-subcategory.php">
		          <i class="material-symbols-rounded opacity-5">subdirectory_arrow_right</i>
		          <span class="nav-link-text ms-1">Expenditure Sub-category</span>
		        </a>
		      </li>
		      <li class="nav-item">
		        <a class="nav-link text-dark" href="add-expenditure.php">
		          <i class="material-symbols-rounded opacity-5">add_circle</i>
		          <span class="nav-link-text ms-1">Add Expenditure</span>
		        </a>
		      </li>
		      <li class="nav-item">
		        <a class="nav-link text-dark" href="view-expenditure.php">
		          <i class="material-symbols-rounded opacity-5">visibility</i>
		          <span class="nav-link-text ms-1">View Expenditure</span>
		        </a>
		      </li>
		    </ul>
		  </div>
		</li>


    <li class="nav-item">
      <a class="nav-link text-dark" data-bs-toggle="collapse" href="#incomeSubmenu" role="button" aria-expanded="false" aria-controls="incomeSubmenu">
        <i class="material-symbols-rounded opacity-5">attach_money</i>
        <span class="nav-link-text ms-1">Income</span>
        <i class="fa fa-angle-down ms-auto"></i>
      </a>
      <div class="collapse" id="incomeSubmenu">
        <ul class="nav flex-column ms-3">
          <li class="nav-item">
            <a class="nav-link text-dark" href="income-category.php">
              <i class="material-symbols-rounded opacity-5">category</i>
              <span class="nav-link-text ms-1">Income Category</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-dark" href="income-subcategory.php">
              <i class="material-symbols-rounded opacity-5">subdirectory_arrow_right</i>
              <span class="nav-link-text ms-1">Income Sub-category</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-dark" href="add-income.php">
              <i class="material-symbols-rounded opacity-5">add_circle</i>
              <span class="nav-link-text ms-1">Add Income</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-dark" href="view-income.php">
              <i class="material-symbols-rounded opacity-5">visibility</i>
              <span class="nav-link-text ms-1">View Income</span>
            </a>
          </li>
        </ul>
      </div>
    </li>



		<li class="nav-item">
		  <a class="nav-link text-dark" data-bs-toggle="collapse" href="#accountSubmenu" role="button" aria-expanded="false" aria-controls="accountSubmenu">
		    <i class="material-symbols-rounded opacity-5">person</i>
		    <span class="nav-link-text ms-1">Account</span>
		    <i class="fa fa-angle-down ms-auto"></i>
		  </a>
		  <div class="collapse" id="accountSubmenu">
		    <ul class="nav flex-column ms-3">
		      <li class="nav-item">
		        <a class="nav-link text-dark" href="profile.php">
		          <i class="material-symbols-rounded opacity-5">account_circle</i>
		          <span class="nav-link-text ms-1">Profile</span>
		        </a>
		      </li>
		      <li class="nav-item">
		        <a class="nav-link text-dark" href="sign-in.php">
		          <i class="material-symbols-rounded opacity-5">login</i>
		          <span class="nav-link-text ms-1">Sign In</span>
		        </a>
		      </li>
		      <li class="nav-item">
		        <a class="nav-link text-dark" href="sign-up.php">
		          <i class="material-symbols-rounded opacity-5">assignment</i>
		          <span class="nav-link-text ms-1">Sign Up</span>
		        </a>
		      </li>
		    </ul>
		  </div>
		</li>



        <li class="nav-item">
          <a class="nav-link text-dark" href="tables.php">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Tables</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="billing.php">
            <i class="material-symbols-rounded opacity-5">receipt_long</i>
            <span class="nav-link-text ms-1">Billing</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="virtual-reality.php">
            <i class="material-symbols-rounded opacity-5">view_in_ar</i>
            <span class="nav-link-text ms-1">Virtual Reality</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="rtl.php">
            <i class="material-symbols-rounded opacity-5">format_textdirection_r_to_l</i>
            <span class="nav-link-text ms-1">RTL</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="notifications.php">
            <i class="material-symbols-rounded opacity-5">notifications</i>
            <span class="nav-link-text ms-1">Notifications</span>
          </a>
        </li>
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Account pages</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="profile.php">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Profile</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="sign-in.php">
            <i class="material-symbols-rounded opacity-5">login</i>
            <span class="nav-link-text ms-1">Sign In</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="sign-up.php">
            <i class="material-symbols-rounded opacity-5">assignment</i>
            <span class="nav-link-text ms-1">Sign Up</span>
          </a>
        </li>
      </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0 ">
      <div class="mx-3">
        <a class="btn bg-gradient-dark w-100" href="logout.php" type="button">Logout</a>
      </div>
    </div>
  </aside>
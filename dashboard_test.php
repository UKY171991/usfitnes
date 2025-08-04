<?php
echo "Dashboard Test - Basic HTML Loading";

// Set page title
$page_title = 'Dashboard Test';

// Check if includes exist
$headerExists = file_exists('includes/header.php');
$sidebarExists = file_exists('includes/sidebar.php');
$footerExists = file_exists('includes/footer.php');

echo "<br>Header exists: " . ($headerExists ? 'YES' : 'NO');
echo "<br>Sidebar exists: " . ($sidebarExists ? 'YES' : 'NO');
echo "<br>Footer exists: " . ($footerExists ? 'YES' : 'NO');

if ($headerExists) {
    echo "<br>Including header...";
    include 'includes/header.php';
}

if ($sidebarExists) {
    echo "<br>Including sidebar...";
    include 'includes/sidebar.php';
}
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1>Dashboard Test Page</h1>
      <p>This is a test to see if the page loads correctly.</p>
    </div>
  </div>
</div>

<?php
if ($footerExists) {
    echo "<br>Including footer...";
    include 'includes/footer.php';
}
?>

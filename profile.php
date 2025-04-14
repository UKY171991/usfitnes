<?php
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict to Admin, Doctor, Technician
if (!in_array($_SESSION['role'], ['Admin', 'Doctor', 'Technician'])) {
    header("Location: index3.php");
    exit();
}

// Fetch user data
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->prepare("SELECT name, email, role, profile_image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {
    error_log("Profile Error: " . $e->getMessage());
    $error = "Failed to load profile data.";
}

$profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'assets/img/default-avatar.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <?php include('inc/head.php'); ?>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include('inc/top.php'); ?>
        <?php include('inc/sidebar.php'); ?>

        <main class="app-main">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Profile</h1>
                        </div>
                    </div>
                </div>
            </section>

            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">User Profile</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="<?php echo htmlspecialchars($profile_image); ?>" 
                                     class="img-fluid rounded-circle shadow" 
                                     alt="User Image" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Name</th>
                                        <td><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td><?php echo htmlspecialchars($user['role'] ?? 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include('inc/footer.php'); ?>
    </div>

    <?php include('inc/js.php'); ?>
</body>
</html>
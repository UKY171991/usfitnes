<?php
require_once '../helpers/auth.php';
require_once '../models/User.php';

class LoginController {
    public function handleLogin() {
        session_start();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            $this->redirectWithError('Email and password are required.', '/templates/login.php');
            return;
        }

        // Authenticate user
        $user = User::authenticate($email, $password);
        if (!$user) {
            $this->redirectWithError('Invalid credentials.', '/templates/login.php');
            return;
        }

        // Set session and redirect based on user type
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        switch ($user['role']) {
            case 'admin':
                header('Location: /admin/dashboard.php');
                break;
            case 'patient':
                header('Location: /patient/dashboard.php');
                break;
            default:
                header('Location: /templates/login.php');
                break;
        }
    }

    private function redirectWithError($message, $location) {
        $_SESSION['error'] = $message;
        header("Location: $location");
    }
}

// Handle login request
$controller = new LoginController();
$controller->handleLogin();
?>

<?php
/**
 * User Controller
 * Handles user registration, authentication, and profile management
 */

require_once 'BaseController.php';

class UserController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show login page
     */
    public function login() {
        if (Auth::isLoggedIn()) {
            $user = Auth::getCurrentUser();
            $this->redirect(Auth::getRedirectUrl($user['role']));
        }

        if ($this->isPost()) {
            return $this->handleLogin();
        }

        $this->render('auth/login', [
            'title' => 'Login',
            'csrf_token' => Auth::generateCSRFToken()
        ]);
    }

    /**
     * Handle login form submission
     */
    private function handleLogin() {
        $this->validateCSRF();

        $email = $this->input('email', '', 'email');
        $password = $this->input('password');
        $rememberMe = $this->input('remember_me') ? true : false;

        // Validate input
        $validator = new Validator([
            'email' => $email,
            'password' => $password
        ]);

        $validator->required('email', 'Email is required')
                 ->email('email', 'Please enter a valid email')
                 ->required('password', 'Password is required');

        if ($validator->fails()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $validator->getErrors()]);
            } else {
                $this->flash('error', $validator->getFirstError());
                $this->redirect(BASE_URL . 'login.php');
            }
        }

        // Attempt login
        $result = Auth::login($email, $password, $rememberMe);

        if ($this->isAjax()) {
            $this->json($result);
        } else {
            if ($result['success']) {
                $this->redirect($result['redirect']);
            } else {
                $this->flash('error', $result['message']);
                $this->redirect(BASE_URL . 'login.php');
            }
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        Auth::logout();
        $this->flash('success', 'You have been logged out successfully');
        $this->redirect(BASE_URL . 'login.php');
    }

    /**
     * Show registration page
     */
    public function register() {
        if (Auth::isLoggedIn()) {
            $this->redirect(BASE_URL . 'dashboard.php');
        }

        if ($this->isPost()) {
            return $this->handleRegistration();
        }

        $this->render('auth/register', [
            'title' => 'Register',
            'csrf_token' => Auth::generateCSRFToken()
        ]);
    }

    /**
     * Handle registration form submission
     */
    private function handleRegistration() {
        $this->validateCSRF();

        $data = [
            'name' => $this->input('name'),
            'email' => $this->input('email', '', 'email'),
            'phone' => $this->input('phone', '', 'phone'),
            'password' => $this->input('password'),
            'confirm_password' => $this->input('confirm_password'),
            'date_of_birth' => $this->input('date_of_birth'),
            'gender' => $this->input('gender'),
            'address' => $this->input('address'),
            'city' => $this->input('city'),
            'state' => $this->input('state'),
            'pincode' => $this->input('pincode')
        ];

        // Validate input
        $validator = new Validator($data);
        $validator->required('name', 'Name is required')
                 ->required('email', 'Email is required')
                 ->email('email', 'Please enter a valid email')
                 ->required('phone', 'Phone number is required')
                 ->phone('phone', 'Please enter a valid phone number')
                 ->required('password', 'Password is required')
                 ->minLength('password', PASSWORD_MIN_LENGTH, 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters')
                 ->required('confirm_password', 'Please confirm your password')
                 ->matches('confirm_password', 'password', 'Passwords do not match');

        if ($validator->fails()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $validator->getErrors()]);
            } else {
                $this->flash('error', $validator->getFirstError());
                $this->redirect(BASE_URL . 'register.php');
            }
        }

        // Hash password
        $data['password'] = Security::hashPassword($data['password']);
        unset($data['confirm_password']);

        // Set default role as patient
        $data['role'] = ROLE_PATIENT;

        // Register user
        $result = Auth::register($data);

        if ($this->isAjax()) {
            $this->json($result);
        } else {
            if ($result['success']) {
                $this->flash('success', 'Registration successful! Please log in.');
                $this->redirect(BASE_URL . 'login.php');
            } else {
                $this->flash('error', $result['message']);
                $this->redirect(BASE_URL . 'register.php');
            }
        }
    }

    /**
     * Show user profile
     */
    public function profile() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userDetails = $this->userModel->getById($user['id']);

        if ($this->isPost()) {
            return $this->updateProfile();
        }

        $this->render('user/profile', [
            'title' => 'Profile',
            'user' => $userDetails,
            'csrf_token' => Auth::generateCSRFToken()
        ]);
    }

    /**
     * Update user profile
     */
    private function updateProfile() {
        $this->validateCSRF();

        $user = $this->getCurrentUser();
        $data = [
            'name' => $this->input('name'),
            'phone' => $this->input('phone', '', 'phone'),
            'date_of_birth' => $this->input('date_of_birth'),
            'gender' => $this->input('gender'),
            'address' => $this->input('address'),
            'city' => $this->input('city'),
            'state' => $this->input('state'),
            'pincode' => $this->input('pincode'),
            'emergency_contact' => $this->input('emergency_contact', '', 'phone')
        ];

        // Validate input
        $validator = new Validator($data);
        $validator->required('name', 'Name is required')
                 ->phone('phone', 'Please enter a valid phone number');

        if ($validator->fails()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $validator->getErrors()]);
            } else {
                $this->flash('error', $validator->getFirstError());
                $this->redirect(BASE_URL . 'profile.php');
            }
        }

        // Update user
        $result = $this->userModel->update($user['id'], $data);

        if ($this->isAjax()) {
            $this->json(['success' => $result, 'message' => $result ? 'Profile updated successfully' : 'Failed to update profile']);
        } else {
            if ($result) {
                $this->flash('success', 'Profile updated successfully');
            } else {
                $this->flash('error', 'Failed to update profile');
            }
            $this->redirect(BASE_URL . 'profile.php');
        }
    }

    /**
     * Change password
     */
    public function changePassword() {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $this->validateCSRF();

        $currentPassword = $this->input('current_password');
        $newPassword = $this->input('new_password');
        $confirmPassword = $this->input('confirm_password');

        // Validate input
        $validator = new Validator([
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
            'confirm_password' => $confirmPassword
        ]);

        $validator->required('current_password', 'Current password is required')
                 ->required('new_password', 'New password is required')
                 ->minLength('new_password', PASSWORD_MIN_LENGTH, 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters')
                 ->required('confirm_password', 'Please confirm your new password')
                 ->matches('confirm_password', 'new_password', 'Passwords do not match');

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->getErrors()]);
        }

        // Verify current password
        $user = $this->getCurrentUser();
        $userDetails = $this->userModel->getById($user['id']);

        if (!Security::verifyPassword($currentPassword, $userDetails['password'])) {
            $this->json(['success' => false, 'message' => 'Current password is incorrect']);
        }

        // Update password
        $result = $this->userModel->changePassword($user['id'], $newPassword);

        $this->json([
            'success' => $result,
            'message' => $result ? 'Password changed successfully' : 'Failed to change password'
        ]);
    }

    /**
     * Dashboard redirect based on role
     */
    public function dashboard() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $redirectUrl = '';

        switch ($user['role']) {
            case ROLE_MASTER_ADMIN:
            case ROLE_ADMIN:
                $redirectUrl = BASE_URL . 'admin/dashboard.php';
                break;
            case ROLE_BRANCH_ADMIN:
                $redirectUrl = BASE_URL . 'branch-admin/dashboard.php';
                break;
            case ROLE_PATIENT:
                $redirectUrl = BASE_URL . 'patient/dashboard.php';
                break;
            default:
                $redirectUrl = BASE_URL . 'index.php';
        }

        $this->redirect($redirectUrl);
    }
}
?>

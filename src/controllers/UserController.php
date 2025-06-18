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

    /**
     * Show patient registration page
     */
    public function patientRegister() {
        if (Auth::isLoggedIn()) {
            $user = Auth::getCurrentUser();
            $this->redirect(Auth::getRedirectUrl($user['role']));
        }

        if ($this->isPost()) {
            return $this->handlePatientRegistration();
        }

        $this->render('patient/register', [
            'title' => 'Patient Registration',
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Handle patient registration
     */
    private function handlePatientRegistration() {
        $this->validateCSRF();

        $data = [
            'first_name' => $this->input('first_name', '', 'string'),
            'last_name' => $this->input('last_name', '', 'string'),
            'email' => $this->input('email', '', 'email'),
            'phone' => $this->input('phone', '', 'string'),
            'date_of_birth' => $this->input('date_of_birth', '', 'string'),
            'gender' => $this->input('gender', '', 'string'),
            'address' => $this->input('address', '', 'string'),
            'city' => $this->input('city', '', 'string'),
            'pincode' => $this->input('pincode', '', 'string'),
            'password' => $this->input('password'),
            'password_confirmation' => $this->input('password_confirmation'),
            'terms' => $this->input('terms') ? true : false
        ];

        // Validate input
        $errors = $this->validatePatientRegistration($data);
        
        if (!empty($errors)) {
            $this->setError(implode('<br>', $errors));
            $this->render('patient/register', [
                'title' => 'Patient Registration',
                'csrf_token' => csrf_token(),
                'old_data' => $data,
                'errors' => $errors
            ]);
            return;
        }

        // Check if email already exists
        if ($this->userModel->emailExists($data['email'])) {
            $this->setError('Email address is already registered.');
            $this->render('patient/register', [
                'title' => 'Patient Registration',
                'csrf_token' => csrf_token(),
                'old_data' => $data
            ]);
            return;
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password_confirmation'], $data['terms']);

        // Set patient role and status
        $data['role'] = USER_ROLE_PATIENT;
        $data['status'] = USER_STATUS_ACTIVE;

        try {
            $patientId = $this->userModel->create($data);
            
            if ($patientId) {
                // Send welcome email
                $this->sendWelcomeEmail($data['email'], $data['first_name']);
                
                // Auto login
                Auth::login($patientId, USER_ROLE_PATIENT, false);
                
                $this->setSuccess('Registration successful! Welcome to US Fitness Lab.');
                $this->redirect('/patient/dashboard');
            } else {
                throw new Exception('Failed to create patient account');
            }
        } catch (Exception $e) {
            Logger::error("Patient registration failed: " . $e->getMessage());
            $this->setError('Registration failed. Please try again.');
            $this->render('patient/register', [
                'title' => 'Patient Registration',
                'csrf_token' => csrf_token(),
                'old_data' => $data
            ]);
        }
    }

    /**
     * Show patient login page
     */
    public function patientLogin() {
        if (Auth::isLoggedIn()) {
            $user = Auth::getCurrentUser();
            if ($user['role'] === USER_ROLE_PATIENT) {
                $this->redirect('/patient/dashboard');
            } else {
                $this->redirect(Auth::getRedirectUrl($user['role']));
            }
        }

        if ($this->isPost()) {
            return $this->handlePatientLogin();
        }

        $this->render('patient/login', [
            'title' => 'Patient Login',
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Handle patient login
     */
    private function handlePatientLogin() {
        $this->validateCSRF();

        $email = $this->input('email', '', 'email');
        $password = $this->input('password');
        $rememberMe = $this->input('remember') ? true : false;

        // Validate input
        if (empty($email) || empty($password)) {
            $this->setError('Please provide both email and password.');
            $this->render('patient/login', [
                'title' => 'Patient Login',
                'csrf_token' => csrf_token(),
                'old_data' => ['email' => $email]
            ]);
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);
            
            if (!$user || $user['role'] !== USER_ROLE_PATIENT) {
                $this->setError('Invalid email or password.');
                $this->render('patient/login', [
                    'title' => 'Patient Login',
                    'csrf_token' => csrf_token(),
                    'old_data' => ['email' => $email]
                ]);
                return;
            }

            if ($user['status'] !== USER_STATUS_ACTIVE) {
                $this->setError('Your account is not active. Please contact support.');
                $this->render('patient/login', [
                    'title' => 'Patient Login',
                    'csrf_token' => csrf_token(),
                    'old_data' => ['email' => $email]
                ]);
                return;
            }

            if (!password_verify($password, $user['password'])) {
                $this->setError('Invalid email or password.');
                $this->render('patient/login', [
                    'title' => 'Patient Login',
                    'csrf_token' => csrf_token(),
                    'old_data' => ['email' => $email]
                ]);
                return;
            }

            // Login successful
            Auth::login($user['id'], $user['role'], $rememberMe);
            Logger::activity($user['id'], 'Patient Login', ['ip' => $_SERVER['REMOTE_ADDR']]);
            
            $this->setSuccess('Welcome back, ' . $user['first_name'] . '!');
            $this->redirect('/patient/dashboard');

        } catch (Exception $e) {
            Logger::error("Patient login failed: " . $e->getMessage());
            $this->setError('Login failed. Please try again.');
            $this->render('patient/login', [
                'title' => 'Patient Login',
                'csrf_token' => csrf_token(),
                'old_data' => ['email' => $email]
            ]);
        }
    }

    /**
     * Patient dashboard
     */
    public function patientDashboard() {
        Auth::requireAuth(USER_ROLE_PATIENT);
        
        $user = Auth::getCurrentUser();
        $patientId = $user['id'];
        
        try {
            // Get patient details
            $patient = $this->userModel->findById($patientId);
            
            // Get dashboard statistics
            $bookingModel = new Booking();
            $stats = [
                'total_bookings' => $bookingModel->countByPatient($patientId),
                'completed_tests' => $bookingModel->countByPatient($patientId, 'completed'),
                'pending_tests' => $bookingModel->countByPatient($patientId, ['pending', 'confirmed']),
                'available_reports' => $bookingModel->countReportsReady($patientId)
            ];
            
            // Get recent bookings
            $recent_bookings = $bookingModel->getPatientBookings($patientId, ['limit' => 5, 'order_by' => 'created_at DESC']);
            
            // Get upcoming appointments
            $upcoming_appointments = $bookingModel->getUpcomingAppointments($patientId);
            
            $this->render('patient/dashboard', [
                'title' => 'Patient Dashboard',
                'patient' => $patient,
                'stats' => $stats,
                'recent_bookings' => $recent_bookings,
                'upcoming_appointments' => $upcoming_appointments
            ]);
            
        } catch (Exception $e) {
            Logger::error("Patient dashboard error: " . $e->getMessage());
            $this->setError('Unable to load dashboard. Please try again.');
            $this->render('patient/dashboard', [
                'title' => 'Patient Dashboard',
                'patient' => ['first_name' => 'Patient', 'last_name' => ''],
                'stats' => ['total_bookings' => 0, 'completed_tests' => 0, 'pending_tests' => 0, 'available_reports' => 0],
                'recent_bookings' => [],
                'upcoming_appointments' => []
            ]);
        }
    }

    /**
     * Patient bookings history
     */
    public function patientBookings() {
        Auth::requireAuth(USER_ROLE_PATIENT);
        
        $user = Auth::getCurrentUser();
        $patientId = $user['id'];
        
        // Get filters
        $filters = [
            'status' => $this->input('status', '', 'string'),
            'date_from' => $this->input('date_from', '', 'string'),
            'date_to' => $this->input('date_to', '', 'string')
        ];
        
        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 12;
        
        try {
            $bookingModel = new Booking();
            
            // Get bookings with filters
            $bookings = $bookingModel->getPatientBookings($patientId, [
                'filters' => $filters,
                'page' => $page,
                'per_page' => $perPage,
                'with_details' => true
            ]);
            
            // Get total
            $totalBookings = $bookingModel->countPatientBookings($patientId, $filters);
            
            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalBookings,
                'total_pages' => ceil($totalBookings / $perPage)
            ];
            
            $this->render('patient/bookings', [
                'title' => 'My Bookings',
                'bookings' => $bookings,
                'filters' => $filters,
                'pagination' => $pagination
            ]);
            
        } catch (Exception $e) {
            Logger::error("Patient bookings error: " . $e->getMessage());
            $this->setError('Unable to load bookings. Please try again.');
            $this->render('patient/bookings', [
                'title' => 'My Bookings',
                'bookings' => [],
                'filters' => $filters,
                'pagination' => ['current_page' => 1, 'per_page' => $perPage, 'total' => 0, 'total_pages' => 0]
            ]);
        }
    }

    /**
     * Patient reports
     */
    public function patientReports() {
        Auth::requireAuth(USER_ROLE_PATIENT);
        
        $user = Auth::getCurrentUser();
        $patientId = $user['id'];
        
        // Get filters
        $filters = [
            'status' => $this->input('status', '', 'string'),
            'category' => $this->input('category', '', 'string'),
            'date_from' => $this->input('date_from', '', 'string'),
            'date_to' => $this->input('date_to', '', 'string')
        ];
        
        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 12;
        
        try {
            $reportModel = new Report();
            $testModel = new Test();
            
            // Get test categories for filter
            $categories = $testModel->getCategories();
            
            // Get reports with filters
            $reports = $reportModel->getPatientReports($patientId, [
                'filters' => $filters,
                'page' => $page,
                'per_page' => $perPage,
                'with_details' => true
            ]);
            
            // Get total count for pagination
            $totalReports = $reportModel->countPatientReports($patientId, $filters);
            
            // Get ready reports for bulk download
            $ready_reports = $reportModel->getPatientReports($patientId, [
                'filters' => ['status' => 'ready'],
                'with_details' => false
            ]);
            
            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalReports,
                'total_pages' => ceil($totalReports / $perPage)
            ];
            
            $this->render('patient/reports', [
                'title' => 'My Reports',
                'reports' => $reports,
                'categories' => $categories,
                'ready_reports' => $ready_reports,
                'filters' => $filters,
                'pagination' => $pagination
            ]);
            
        } catch (Exception $e) {
            Logger::error("Patient reports error: " . $e->getMessage());
            $this->setError('Unable to load reports. Please try again.');
            $this->render('patient/reports', [
                'title' => 'My Reports',
                'reports' => [],
                'categories' => [],
                'ready_reports' => [],
                'filters' => $filters,
                'pagination' => ['current_page' => 1, 'per_page' => $perPage, 'total' => 0, 'total_pages' => 0]
            ]);
        }
    }

    /**
     * Patient profile
     */
    public function patientProfile() {
        Auth::requireAuth(USER_ROLE_PATIENT);
        
        $user = Auth::getCurrentUser();
        $patientId = $user['id'];
        
        if ($this->isPost()) {
            return $this->handleProfileUpdate($patientId);
        }
        
        try {
            $patient = $this->userModel->findById($patientId);
            
            // Decode preferences if stored as JSON
            if (!empty($patient['preferences'])) {
                $patient['preferences'] = json_decode($patient['preferences'], true);
            } else {
                $patient['preferences'] = [];
            }
            
            $this->render('patient/profile', [
                'title' => 'My Profile',
                'patient' => $patient
            ]);
              } catch (Exception $e) {
            Logger::error("Patient profile error: " . $e->getMessage());
            $this->setError('Unable to load profile. Please try again.');
            $this->redirect('/patient/dashboard');
        }
    }

    /**
     * Handle profile update
     */
    private function handleProfileUpdate($patientId) {
        $this->validateCSRF();

        $data = [
            'first_name' => $this->input('first_name', '', 'string'),
            'last_name' => $this->input('last_name', '', 'string'),
            'email' => $this->input('email', '', 'email'),
            'phone' => $this->input('phone', '', 'string'),
            'date_of_birth' => $this->input('date_of_birth', '', 'string'),
            'gender' => $this->input('gender', '', 'string'),
            'address' => $this->input('address', '', 'string'),
            'city' => $this->input('city', '', 'string'),
            'pincode' => $this->input('pincode', '', 'string')
        ];

        // Validate input
        $errors = $this->validateProfileUpdate($data);
        
        if (!empty($errors)) {
            $this->setError(implode('<br>', $errors));
            $this->redirect('/patient/profile');
            return;
        }

        // Check if email is being changed and already exists
        $currentUser = $this->userModel->findById($patientId);
        if ($data['email'] !== $currentUser['email']) {
            if ($this->userModel->emailExists($data['email'])) {
                $this->setError('Email address is already registered.');
                $this->redirect('/patient/profile');
                return;
            }
        }

        try {
            $result = $this->userModel->update($patientId, $data);
            
            if ($result) {
                Logger::activity($patientId, 'Profile Updated', ['fields' => array_keys($data)]);
                $this->setSuccess('Profile updated successfully!');
            } else {
                $this->setError('Failed to update profile. Please try again.');
            }
            
        } catch (Exception $e) {
            Logger::error("Profile update failed: " . $e->getMessage());
            $this->setError('Failed to update profile. Please try again.');
        }
        
        $this->redirect('/patient/profile');
    }

    /**
     * Validate patient registration data
     */
    private function validatePatientRegistration($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required.';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required.';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required.';
        } elseif (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
            $errors[] = 'Please enter a valid 10-digit phone number.';
        }
        
        if (empty($data['date_of_birth'])) {
            $errors[] = 'Date of birth is required.';
        } elseif (!strtotime($data['date_of_birth'])) {
            $errors[] = 'Please enter a valid date of birth.';
        }
        
        if (empty($data['gender'])) {
            $errors[] = 'Gender is required.';
        } elseif (!in_array($data['gender'], ['male', 'female', 'other'])) {
            $errors[] = 'Please select a valid gender.';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'Password is required.';
        } elseif (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $data['password'])) {
            $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number.';
        }
        
        if ($data['password'] !== $data['password_confirmation']) {
            $errors[] = 'Password confirmation does not match.';
        }
        
        if (!$data['terms']) {
            $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
        }
        
        return $errors;
    }

    /**
     * Validate profile update data
     */
    private function validateProfileUpdate($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required.';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required.';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required.';
        } elseif (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
            $errors[] = 'Please enter a valid 10-digit phone number.';
        }
        
        if (empty($data['date_of_birth'])) {
            $errors[] = 'Date of birth is required.';
        } elseif (!strtotime($data['date_of_birth'])) {
            $errors[] = 'Please enter a valid date of birth.';
        }
        
        if (empty($data['gender'])) {
            $errors[] = 'Gender is required.';
        } elseif (!in_array($data['gender'], ['male', 'female', 'other'])) {
            $errors[] = 'Please select a valid gender.';
        }
        
        return $errors;
    }

    /**
     * Send welcome email to new patient
     */
    private function sendWelcomeEmail($email, $firstName) {
        try {
            // Email content
            $subject = 'Welcome to US Fitness Lab';
            $message = "
                <h2>Welcome to US Fitness Lab, {$firstName}!</h2>
                <p>Thank you for registering with us. Your account has been created successfully.</p>
                <p>You can now:</p>
                <ul>
                    <li>Book lab tests online</li>
                    <li>View and download your reports</li>
                    <li>Track your test history</li>
                    <li>Manage your profile</li>
                </ul>
                <p>If you have any questions, please don't hesitate to contact us.</p>
                <p>Best regards,<br>US Fitness Lab Team</p>
            ";
            
            // Send email (implement based on your email service)
            // mail($email, $subject, $message, $headers);
            
            Logger::info("Welcome email sent to: " . $email);
        } catch (Exception $e) {
            Logger::error("Failed to send welcome email: " . $e->getMessage());
        }
    }
}
?>

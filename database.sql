-- US Fitness Lab Database Schema
-- Pathology Lab Test Website Database as per project instructions

-- Create database
CREATE DATABASE IF NOT EXISTS usfitnes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE usfitnes_db;

-- Branches table (as per instructions)
CREATE TABLE branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    contact VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    branch_admin_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_branch_admin (branch_admin_id),
    INDEX idx_status (status)
);

-- Users table (as per instructions: master_admin, branch_admin, patient roles)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('master_admin', 'branch_admin', 'patient') NOT NULL,
    branch_id INT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address TEXT,
    city VARCHAR(100),
    pincode VARCHAR(10),
    emergency_contact_name VARCHAR(255),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(50),
    email_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    preferences JSON,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_branch (branch_id),
    INDEX idx_status (status),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Test categories table
CREATE TABLE test_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tests table (as per instructions)
CREATE TABLE tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50),
    description TEXT,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    branch_id INT NOT NULL,
    sample_type VARCHAR(100),
    preparation_instructions TEXT,
    turnaround_time VARCHAR(50),
    parameters JSON, -- Store test parameters as JSON
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category_id),
    INDEX idx_branch (branch_id),
    INDEX idx_status (status),
    FOREIGN KEY (category_id) REFERENCES test_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- Bookings table (as per instructions)
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    test_id INT NOT NULL,
    branch_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    appointment_date DATE,
    appointment_time TIME,
    amount DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    sample_collected_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_patient (patient_id),
    INDEX idx_test (test_id),
    INDEX idx_branch (branch_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_appointment_date (appointment_date),
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- Reports table (as per instructions)
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL UNIQUE,
    patient_id INT NOT NULL,
    branch_id INT NOT NULL,
    test_results JSON, -- Store test results as JSON (as per instructions)
    report_date DATE,
    pdf_path VARCHAR(500), -- Path to generated PDF
    status ENUM('pending', 'processing', 'ready', 'delivered') DEFAULT 'pending', -- as per instructions
    technician_id INT,
    doctor_id INT,
    verified_at TIMESTAMP NULL,
    generated_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking (booking_id),
    INDEX idx_patient (patient_id),
    INDEX idx_branch (branch_id),
    INDEX idx_status (status),
    INDEX idx_report_date (report_date),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Payments table (as per instructions for Instamojo integration)
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    patient_id INT NOT NULL,
    payment_request_id VARCHAR(255), -- Instamojo payment request ID
    payment_id VARCHAR(255), -- Instamojo payment ID (after completion)
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    payment_method VARCHAR(50),
    payment_gateway VARCHAR(50) DEFAULT 'instamojo',
    gateway_response JSON, -- Store complete gateway response
    transaction_id VARCHAR(255),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending', -- as per instructions
    payment_date TIMESTAMP NULL,
    refund_amount DECIMAL(10,2) DEFAULT 0,
    refund_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- as per instructions
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking (booking_id),
    INDEX idx_patient (patient_id),
    INDEX idx_payment_request (payment_request_id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_status (status),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Test parameters table (for storing individual test parameters)
CREATE TABLE test_parameters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_id INT NOT NULL,
    parameter_name VARCHAR(255) NOT NULL,
    unit VARCHAR(50),
    reference_range VARCHAR(255),
    method VARCHAR(255),
    specimen_type VARCHAR(100),
    is_bold BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_test (test_id),
    INDEX idx_sort_order (sort_order),
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
);

-- Report parameters table (for storing actual test results)
CREATE TABLE report_parameters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    parameter_name VARCHAR(255) NOT NULL,
    result_value VARCHAR(255),
    unit VARCHAR(50),
    reference_range VARCHAR(255),
    method VARCHAR(255),
    specimen_type VARCHAR(100),
    is_bold BOOLEAN DEFAULT FALSE,
    is_abnormal BOOLEAN DEFAULT FALSE,
    comments TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_report (report_id),
    INDEX idx_sort_order (sort_order),
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- email, sms, push
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    read_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Audit logs table
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- System settings table
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- Insert sample data

-- Insert test categories
INSERT INTO test_categories (category_name, description) VALUES
('Blood Tests', 'Blood analysis and pathology tests'),
('Urine Tests', 'Urine analysis and microscopy'),
('Stool Tests', 'Stool examination and culture'),
('Hormone Tests', 'Endocrine and hormone level tests'),
('Cardiac Tests', 'Heart function and cardiac markers'),
('Liver Function', 'Liver enzyme and function tests'),
('Kidney Function', 'Renal function and kidney tests'),
('Diabetes Tests', 'Blood sugar and diabetes markers'),
('Cancer Screening', 'Tumor markers and cancer screening'),
('Infectious Disease', 'Bacterial, viral and parasitic tests');

-- Insert sample branches
INSERT INTO branches (name, address, contact, email) VALUES
('Main Branch', '123 Medical Street, Downtown, City 110001', '+91-9876543210', 'main@usfitness.com'),
('North Branch', '456 Health Avenue, North District, City 110002', '+91-9876543211', 'north@usfitness.com'),
('South Branch', '789 Care Road, South Area, City 110003', '+91-9876543212', 'south@usfitness.com');

-- Insert master admin user
INSERT INTO users (name, first_name, last_name, email, phone, password, role, status) VALUES
('Master Admin', 'Master', 'Admin', 'admin@usfitness.com', '+91-9999999999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'master_admin', 'active');

-- Insert sample branch admin
INSERT INTO users (name, first_name, last_name, email, phone, password, role, branch_id, status) VALUES
('Branch Admin Main', 'John', 'Doe', 'john@usfitness.com', '+91-9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'branch_admin', 1, 'active');

-- Update branch admin reference
UPDATE branches SET branch_admin_id = 2 WHERE id = 1;

-- Insert sample tests
INSERT INTO tests (name, code, description, category_id, price, branch_id, sample_type, turnaround_time) VALUES
('Complete Blood Count', 'CBC001', 'Complete blood count with differential', 1, 500.00, 1, 'Blood', '24 hours'),
('Lipid Profile', 'LP001', 'Cholesterol and triglyceride levels', 1, 800.00, 1, 'Blood', '24 hours'),
('Liver Function Test', 'LFT001', 'Comprehensive liver function panel', 6, 1200.00, 1, 'Blood', '24 hours'),
('Thyroid Profile', 'THY001', 'T3, T4, and TSH levels', 4, 900.00, 1, 'Blood', '48 hours'),
('HbA1c', 'HBA001', 'Glycated hemoglobin for diabetes monitoring', 8, 600.00, 1, 'Blood', '24 hours');

-- Insert sample test parameters
INSERT INTO test_parameters (test_id, parameter_name, unit, reference_range, method, specimen_type, is_bold, sort_order) VALUES
(1, 'Hemoglobin', 'g/dL', '12.0-15.5', 'Automated Hematology', 'EDTA Blood', TRUE, 1),
(1, 'RBC Count', 'million/μL', '4.5-5.5', 'Automated Hematology', 'EDTA Blood', FALSE, 2),
(1, 'WBC Count', '/μL', '4000-11000', 'Automated Hematology', 'EDTA Blood', TRUE, 3),
(1, 'Platelet Count', '/μL', '150000-450000', 'Automated Hematology', 'EDTA Blood', FALSE, 4);

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'US Fitness Lab', 'string', 'Website name'),
('site_email', 'info@usfitness.com', 'string', 'Contact email address'),
('site_phone', '+91-9876543210', 'string', 'Contact phone number'),
('gst_number', '27AABCU9603R1ZX', 'string', 'GST registration number'),
('report_validity_days', '90', 'integer', 'Report validity in days'),
('auto_booking_confirmation', '1', 'boolean', 'Auto confirm bookings'),
('email_notifications', '1', 'boolean', 'Enable email notifications'),
('sms_notifications', '0', 'boolean', 'Enable SMS notifications');

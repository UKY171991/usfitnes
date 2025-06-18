# Pathology Lab Website - Project Status & Updated Instructions

## Current Project Status

### ✅ **Completed Components:**
1. **Admin Interface** - Comprehensive test management system
   - AJAX-based pagination and search
   - Test categories, test master, users, branches management
   - Dynamic test parameters with add/edit functionality
   - Modern UI with Bootstrap integration

2. **Database Structure** - Core tables implemented
   - `branches` - Multi-branch support
   - `users` - Role-based access (admin, branch_admin, etc.)
   - `tests` & `test_categories` - Test management
   - `test_parameters` - Dynamic test parameters
   - `patients`, `reports`, `payments` - Basic structure exists

3. **Authentication System** - Session-based authentication
   - Role-based access control
   - Branch-specific data isolation

### 🔄 **In Progress/Needs Updates:**
1. **File Structure** - Needs reorganization to match specifications
2. **Patient Interface** - Not yet implemented
3. **Payment Integration** - Instamojo integration pending
4. **Report Generation** - PDF generation system pending
5. **MVC Architecture** - Needs proper controller/model structure

## Updated Development Plan

### Phase 1: File Structure Reorganization ⏳
Reorganize current files to match specified structure:

```
/usfitnes/
├── index.php (main entry point)
├── .htaccess (URL rewriting)
├── thankyou.php (payment redirect)
├── webhook.php (payment webhook)
├── download-report.php (report download)
├── /src/
│   ├── /controllers/ (MVC controllers)
│   ├── /models/ (Database models)
│   ├── /helpers/ (Utility functions)
│   └── /lib/ (Third-party libraries)
├── /assets/ (Static files)
├── /templates/ (View templates)
├── /config/ (Configuration files)
├── /reports/ (PDF storage)
└── /logs/ (Application logs)
```

### Phase 2: Patient Interface Development 📝
Create patient-facing components:
- Patient registration/login
- Test booking system
- Payment integration
- Report viewing/download

### Phase 3: Payment Integration 💳
Implement Instamojo payment gateway:
- Payment request creation
- Webhook handling
- Payment status tracking

### Phase 4: Report Generation 📄
Implement PDF report system:
- mPDF integration
- Report templates
- Secure report access

## Updated Database Schema Requirements

### Missing Tables/Fields:
```sql
-- Add booking system
CREATE TABLE `bookings` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `booking_date` datetime NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `payment_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
  FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`),
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
);

-- Update users table for patients
ALTER TABLE `users` 
MODIFY `role` enum('master_admin','branch_admin','patient','technician') DEFAULT 'patient';

-- Update reports table structure
ALTER TABLE `reports` 
ADD COLUMN `booking_id` int(11) NOT NULL AFTER `id`,
ADD COLUMN `test_results` text DEFAULT NULL,
ADD COLUMN `pdf_path` varchar(255) DEFAULT NULL,
ADD FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`);

-- Update payments table for Instamojo
ALTER TABLE `payments`
ADD COLUMN `payment_request_id` varchar(100) DEFAULT NULL,
ADD COLUMN `instamojo_payment_id` varchar(100) DEFAULT NULL,
ADD COLUMN `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending';
```

## Implementation Steps

### Step 1: Reorganize File Structure
1. Create new directory structure
2. Move existing files to appropriate locations
3. Update include paths and references

### Step 2: Create MVC Architecture
1. Implement Controllers in `/src/controllers/`
2. Create Models in `/src/models/`
3. Set up routing in `index.php`

### Step 3: Patient Interface
1. Create patient templates
2. Implement booking system
3. Add patient dashboard

### Step 4: Payment Integration
1. Install Instamojo SDK
2. Create payment controller
3. Implement webhook handling

### Step 5: Report System
1. Install mPDF library
2. Create report templates
3. Implement PDF generation

## Dependencies to Install

### Required Libraries:
```bash
# Install via Composer
composer require instamojo/instamojo-php
composer require mpdf/mpdf

# Or manual installation in /src/lib/
```

### Configuration Files Needed:
1. `/config/db.php` - Database configuration
2. `/config/instamojo.php` - Payment gateway settings
3. `/config/constants.php` - Application constants

## Security Considerations

### Implemented:
- Password hashing with PHP's `password_hash()`
- Session-based authentication
- Role-based access control

### To Implement:
- Input sanitization helpers
- CSRF protection
- File upload security
- Report access control
- Payment webhook validation

## Next Immediate Actions

1. **Run Database Updates** - Execute `database_updates.sql`
2. **Test Current Admin System** - Verify all admin functions work
3. **Plan File Reorganization** - Map current files to new structure
4. **Create Patient Interface** - Start with basic registration/login

## Current Working Features

### Admin Dashboard:
- ✅ User management with AJAX pagination/search
- ✅ Branch management with full CRUD operations
- ✅ Test categories management
- ✅ Comprehensive test master with parameters
- ✅ Modern UI with Bootstrap integration

### Technical Features:
- ✅ AJAX-based data loading
- ✅ Search with highlighting
- ✅ Form validation
- ✅ Responsive design
- ✅ Role-based authentication

## Project URLs (Current)

### Admin Interface:
- `/admin/dashboard.php` - Main admin dashboard
- `/admin/users.php` - User management
- `/admin/branches.php` - Branch management  
- `/admin/test-categories.php` - Test categories
- `/admin/test-master.php` - Comprehensive test management

### Branch Admin Interface:
- `/branch-admin/dashboard.php` - Branch dashboard
- `/branch-admin/patients.php` - Patient management
- `/branch-admin/reports.php` - Report management

This project has a solid foundation with a working admin interface. The next phase involves reorganizing the structure and implementing the patient-facing components and payment system.

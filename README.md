# PathLab Pro - Pathology Laboratory Management System

A comprehensive laboratory management system built with PHP and AdminLTE 3, designed for pathology laboratories, diagnostic centers, and medical testing facilities to manage their operations efficiently.

## Features

### Authentication System
- **Login Page**: Secure login with session management
- **Registration**: New user registration  
- **Password Recovery**: Forgot password functionality
- **Session Management**: Secure session handling with role-based access

### Admin Dashboard
- **Modern UI**: Built with AdminLTE 3 for professional medical appearance
- **Statistics Overview**: Key lab metrics and performance indicators
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Interactive Charts**: Visual representation of test data and lab analytics

### Patient Management
- **Patient Registration**: Complete patient information management
- **Patient Profiles**: Detailed patient records with medical history
- **Patient Search**: Advanced search and filtering capabilities
- **Demographics**: Age, gender, contact information tracking
- **Medical History**: Track previous tests and results

### Core Laboratory Modules
- **Patients**: Complete patient lifecycle management
- **Test Orders**: Laboratory test ordering and tracking system
- **Lab Tests**: Test catalog with categories and pricing
- **Test Results**: Results entry, verification, and reporting
- **Doctors**: Referring physician management
- **Reports**: Comprehensive analytical reports with multiple types:
  - Test volume reports
  - Revenue reports
  - Doctor performance reports
  - Custom date range reports
  - Exportable in multiple formats (PDF, CSV, Excel)
- **Equipment**: Laboratory equipment tracking and maintenance:
  - Equipment inventory management
  - Maintenance scheduling and history
  - Warranty tracking
  - Service record management
- **Quality Control**: Ensure accuracy and compliance

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap 4
- **Backend**: PHP 7.4+, RESTful API endpoints for AJAX operations
- **Database**: MySQL with optimized schema for pathology workflows
- **UI Framework**: AdminLTE 3 with custom pathology-specific components
- **Icons**: Font Awesome 6 (Medical and Laboratory Icons)
- **Charts**: Chart.js for laboratory analytics and report visualization
- **DataTables**: Advanced table functionality with server-side processing
- **AJAX**: Asynchronous data operations for smooth user experience
- **SweetAlert2**: Enhanced user notifications and confirmations

## Installation

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Setup Instructions

1. **Clone/Download the project**
   ```
   Place the files in your web server directory (e.g., xampp/htdocs/usfitnes)
   ```

2. **Database Setup**
   - Start your web server (Apache) and MySQL
   - The system will automatically create the database and tables on first run
   - Default database name: `pathlab_pro`

3. **Access the Application**
   ```
   http://localhost/usfitnes
   ```

4. **Default Login Credentials**
   - Username: `admin`
   - Password: `password`

## File Structure

```
pathlab-pro/
├── index.php              # Login page
├── dashboard.php           # Main dashboard
├── register.php            # Registration page
├── forgot-password.php     # Password recovery
├── logout.php             # Logout handler
├── config.php             # Database configuration
├── patients.php           # Patient management
└── README.md              # This file
```

## Database Schema

### Core Tables
- **users** - System users (admin, lab technicians, doctors)
- **patients** - Patient information and demographics
- **doctors** - Referring physician database
- **test_categories** - Test classification system
- **tests** - Available laboratory tests
- **test_orders** - Test requisitions and orders
- **test_results** - Laboratory test results
- **reports** - Generated reports and documents
- **lab_equipment** - Equipment inventory and maintenance

## Features Overview

### Login System
- Clean, professional medical interface
- Session-based authentication with roles
- Medical-themed color scheme (blue gradient)
- Responsive design for all devices

### Dashboard
- Key performance indicators for labs
- Total patients count
- Pending tests tracking
- Daily completion statistics
- Critical results alerts
- Interactive charts for test volumes
- Calendar integration for scheduling

### Patient Management
- Comprehensive patient registration
- Advanced search and filtering
- Export functionality (CSV, Excel, PDF)
- Patient demographics tracking
- Medical history maintenance
- Emergency contact information

## Laboratory Workflow

1. **Patient Registration** - Register new patients or update existing records
2. **Test Ordering** - Create test orders from doctors or walk-in patients
3. **Sample Collection** - Track sample collection and processing
4. **Test Processing** - Enter and verify test results
5. **Report Generation** - Generate and deliver patient reports
6. **Quality Control** - Monitor equipment and test accuracy

## User Roles

- **Administrator**: Full system access and management
- **Lab Technician**: Test processing and result entry
- **Doctor**: View results and create orders
- **Receptionist**: Patient registration and order management

## Security Features

- Role-based access control
- Session management
- SQL injection prevention using PDO
- Password hashing (for production use)
- XSS protection
- CSRF protection ready
- Data encryption for sensitive information

## Customization

### Styling
The system uses AdminLTE 3 with medical-themed customization:
- Professional blue gradient backgrounds
- Medical iconography
- Clean, clinical interface design
- Responsive layouts for various devices

### Database Configuration
Edit `config.php` to modify:
- Database host and credentials
- Laboratory-specific settings
- Report templates
- Test categories and parameters

## Compliance & Standards

- Designed with medical data privacy in mind
- Supports laboratory quality standards
- Audit trail capabilities
- Backup and recovery procedures
- Data retention policies

## Browser Support

- Chrome (latest)
- Firefox (latest) 
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Future Enhancements

- **LIMS Integration**: Laboratory Information Management System
- **Barcode/QR Scanning**: Sample tracking and identification
- **Email/SMS Notifications**: Automated result delivery
- **Mobile App**: Mobile interface for lab technicians
- **Advanced Reporting**: Custom report builder
- **Equipment Integration**: Direct instrument connectivity
- **AI/ML**: Automated result analysis and flagging
- **Telemedicine**: Remote consultation capabilities

## Support

For support and questions:
- Review the comprehensive documentation
- Check code comments for implementation details
- Test with sample laboratory data

## License

This project is for educational and commercial use in healthcare settings. Modify as needed for your laboratory requirements.

## Version History

- **v1.0.0** - Initial release with core laboratory functionality
  - Patient management system
  - Test ordering and tracking
  - Results management
  - Dashboard with lab analytics
  - AdminLTE 3 medical interface
  - Responsive design for clinical environments

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes with proper testing
4. Ensure HIPAA compliance considerations
5. Submit a pull request

---

**PathLab Pro** - Professional Pathology Laboratory Management System

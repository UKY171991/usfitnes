# Pathology CRM System

A comprehensive web-based CRM system for pathology laboratories to manage patients, tests, reports, and branches.

## Features

- Multi-role access (Admin, Branch Admin, Receptionist, Technician)
- Patient management
- Test and category management
- Report generation and printing
- Branch management
- User management
- Payment tracking
- Dashboard with analytics

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependency management)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/pathology-crm.git
cd pathology-crm
```

2. Create a MySQL database and import the database schema:
```bash
mysql -u username -p database_name < database/schema.sql
```

3. Configure the database connection:
Edit `inc/config.php` and update the database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

4. Set up the web server:
- For Apache: Ensure mod_rewrite is enabled
- For Nginx: Configure the server block with proper rewrite rules

5. Set permissions:
```bash
chmod -R 755 .
chmod -R 777 assets/uploads/
```

6. Access the application:
Open your browser and navigate to `http://localhost/pathology-crm`

## Default Login Credentials

- Admin:
  - Username: admin
  - Password: admin123

## Directory Structure

```
/pathology-crm/
├── /admin/                      # Master admin module
├── /branch-admin/              # Branch admin module
├── /users/                     # Receptionist & Technician module
├── /auth/                      # Authentication
├── /inc/                       # Common includes
├── /assets/                    # Static files
├── /ajax/                      # AJAX handlers
├── /reports/                   # Report templates
└── /database/                  # Database schema and migrations
```

## Security

- All passwords are hashed using PHP's password_hash()
- SQL injection prevention using PDO prepared statements
- XSS protection through output escaping
- CSRF protection for forms
- Session security measures

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@example.com or create an issue in the GitHub repository. 
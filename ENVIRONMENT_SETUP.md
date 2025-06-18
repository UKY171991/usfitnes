# PathLab Pro - Environment Configuration

## Development Environment Setup

### Prerequisites
- XAMPP/WAMP/LAMP Server
- PHP 7.4+
- MySQL 5.7+
- Git (for version control)
- Composer (for dependency management)

### Git Installation (Windows)
1. Download Git from: https://git-scm.com/download/win
2. Install with default settings
3. Restart your command prompt/PowerShell
4. Verify: `git --version`

### Initialize Git Repository
```bash
# Navigate to project directory
cd c:\xampp\htdocs\usfitnes

# Initialize Git repository
git init

# Add all files
git add .

# Initial commit
git commit -m "Initial PathLab Pro setup - Pathology Management System"

# Add remote repository (if using GitHub/GitLab)
git remote add origin https://github.com/yourusername/pathlab-pro.git
```

### Create Development Branches
```bash
# Create and switch to develop branch
git checkout -b develop

# Create feature branches
git checkout -b feature/patient-management
git checkout -b feature/test-orders  
git checkout -b feature/lab-results
git checkout -b feature/doctor-management
git checkout -b feature/equipment-tracking
git checkout -b feature/report-generation

# Create environment branches
git checkout -b env/development
git checkout -b env/testing
git checkout -b env/staging

# Return to main branch
git checkout main
```

### Environment Configuration Files

#### Development Environment
Create `config/development.php`:
```php
<?php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'pathlab_dev',
        'username' => 'root',
        'password' => ''
    ],
    'debug' => true,
    'environment' => 'development',
    'base_url' => 'http://localhost/usfitnes',
    'log_level' => 'debug'
];
?>
```

#### Testing Environment  
Create `config/testing.php`:
```php
<?php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'pathlab_test',
        'username' => 'root',
        'password' => ''
    ],
    'debug' => true,
    'environment' => 'testing',
    'base_url' => 'http://test.pathlab.local',
    'log_level' => 'info'
];
?>
```

#### Staging Environment
Create `config/staging.php`:
```php
<?php
return [
    'database' => [
        'host' => 'staging-server',
        'dbname' => 'pathlab_staging',
        'username' => 'staging_user',
        'password' => 'secure_password'
    ],
    'debug' => false,
    'environment' => 'staging',
    'base_url' => 'https://staging.pathlab.com',
    'log_level' => 'warning'
];
?>
```

#### Production Environment
Create `config/production.php`:
```php
<?php
return [
    'database' => [
        'host' => 'production-server',
        'dbname' => 'pathlab_prod',
        'username' => 'prod_user',
        'password' => 'very_secure_password'
    ],
    'debug' => false,
    'environment' => 'production',
    'base_url' => 'https://pathlab.com',
    'log_level' => 'error',
    'ssl_required' => true,
    'encryption_key' => 'your-256-bit-encryption-key'
];
?>
```

### Branch-Specific Workflows

#### Feature Development Workflow
```bash
# Start new feature
git checkout develop
git pull origin develop
git checkout -b feature/new-feature-name

# Work on feature
# ... make changes ...

# Commit changes
git add .
git commit -m "Add new feature functionality"

# Push feature branch
git push origin feature/new-feature-name

# Create pull request to develop branch
```

#### Release Workflow
```bash
# Prepare release
git checkout develop
git pull origin develop
git checkout -b release/v1.1.0

# Final testing and bug fixes
git commit -m "Prepare v1.1.0 release"

# Merge to main
git checkout main
git merge release/v1.1.0
git tag v1.1.0
git push origin main --tags

# Merge back to develop
git checkout develop
git merge release/v1.1.0
git push origin develop

# Delete release branch
git branch -d release/v1.1.0
```

#### Hotfix Workflow
```bash
# Create hotfix from main
git checkout main
git checkout -b hotfix/critical-fix

# Fix the issue
git commit -m "Fix critical issue"

# Merge to main
git checkout main
git merge hotfix/critical-fix
git tag v1.0.1
git push origin main --tags

# Merge to develop
git checkout develop
git merge hotfix/critical-fix
git push origin develop

# Delete hotfix branch
git branch -d hotfix/critical-fix
```

### Database Management Per Branch

#### Development Database Setup
```sql
CREATE DATABASE pathlab_dev;
USE pathlab_dev;
-- Run your table creation scripts
```

#### Testing Database Setup
```sql
CREATE DATABASE pathlab_test;
USE pathlab_test;
-- Run your table creation scripts
-- Insert test data
```

#### Migration Scripts
Create `database/migrations/` directory for version control of database changes.

### Deployment Configuration

#### Apache Virtual Hosts (for multiple environments)
```apache
# Development
<VirtualHost *:80>
    ServerName dev.pathlab.local
    DocumentRoot "c:/xampp/htdocs/usfitnes"
    SetEnv APPLICATION_ENV "development"
</VirtualHost>

# Testing  
<VirtualHost *:80>
    ServerName test.pathlab.local
    DocumentRoot "c:/xampp/htdocs/usfitnes-test"
    SetEnv APPLICATION_ENV "testing"
</VirtualHost>

# Staging
<VirtualHost *:80>
    ServerName staging.pathlab.local
    DocumentRoot "c:/xampp/htdocs/usfitnes-staging"
    SetEnv APPLICATION_ENV "staging"
</VirtualHost>
```

### Team Collaboration Setup

#### Pre-commit Hooks
Create `.git/hooks/pre-commit`:
```bash
#!/bin/sh
# PHP syntax check
find . -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
if [ $? -eq 0 ]; then
    echo "PHP syntax errors found. Commit aborted."
    exit 1
fi

# Check for debugging code
if grep -r "var_dump\|print_r\|console.log" --include="*.php" .; then
    echo "Debug code found. Commit aborted."
    exit 1
fi

echo "Pre-commit checks passed."
```

#### Code Standards
- Follow PSR-12 coding standards
- Use meaningful commit messages
- Include tests for new features
- Update documentation

### Continuous Integration (Optional)

#### GitHub Actions Workflow
Create `.github/workflows/pathlab.yml`:
```yaml
name: PathLab Pro CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:5.7
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: pathlab_test
        ports:
          - 3306:3306

    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '7.4'
        
    - name: Install dependencies
      run: composer install
      
    - name: Run tests
      run: ./vendor/bin/phpunit
      
    - name: Check code style
      run: ./vendor/bin/phpcs
```

### Security Considerations

#### Environment Variables
Never commit sensitive data like:
- Database passwords
- API keys
- Encryption keys
- SSL certificates

#### File Permissions
```bash
# Secure file permissions
chmod 644 *.php
chmod 755 directories/
chmod 600 config/production.php
```

### Backup Strategy

#### Automated Backups
```bash
# Daily database backup
mysqldump -u root -p pathlab_prod > backups/pathlab_$(date +%Y%m%d).sql

# Weekly file backup
tar -czf backups/files_$(date +%Y%m%d).tar.gz /path/to/pathlab
```

This setup provides a robust multi-branch development environment for your PathLab Pro pathology management system.

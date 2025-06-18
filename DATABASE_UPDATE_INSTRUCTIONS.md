# Database Updates for Test Master Form

## Instructions

The test master form has been updated to include comprehensive fields matching the desktop application. To apply the database changes, run the following SQL commands:

### Option 1: Using MySQL Command Line
```bash
mysql -u your_username -p your_database_name < database_updates.sql
```

### Option 2: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your database (u902379465_fitness)
3. Go to "SQL" tab
4. Copy and paste the contents of `database_updates.sql`
5. Click "Go" to execute

### Option 3: Manual Execution
Copy each ALTER TABLE statement from `database_updates.sql` and run them individually in your database management tool.

## What's Updated

### Tests Table - New Fields Added:
- `test_code` - varchar(20) - Test identification code
- `shortcut` - varchar(10) - Quick reference shortcut
- `rate` - decimal(10,2) - Test rate/price
- `report_heading` - varchar(255) - Custom report heading
- `specimen` - varchar(100) - Required specimen type
- `default_result` - text - Default result value
- `min_value` - decimal(10,2) - Minimum normal range value
- `max_value` - decimal(10,2) - Maximum normal range value
- `individual_method` - text - Individual test method description
- `auto_suggestion` - tinyint(1) - Auto-suggestion flag
- `age_gender_wise_ref` - tinyint(1) - Age/gender specific reference ranges
- `print_new_page` - tinyint(1) - Print on new page flag
- `sub_heading` - tinyint(1) - Sub-heading flag

### Test Parameters Table - New Fields Added:
- `min_value` - decimal(10,2) - Parameter minimum value
- `max_value` - decimal(10,2) - Parameter maximum value
- `default_result` - text - Parameter default result
- `specimen` - varchar(100) - Parameter specimen type
- `testcode` - varchar(20) - Parameter test code

## Features

### Add Test Modal
- Comprehensive form with all fields from the desktop application
- Dynamic parameter table with add/remove functionality
- Auto-population of parameter fields from main form
- Form validation and error handling

### Edit Test Modal
- Updated to match the Add Test modal
- Loads existing test data including parameters
- Supports parameter editing and management
- Maintains all form validation

### AJAX Integration
- Paginated test listing
- Search functionality with highlighting
- Dynamic content loading
- Parameter management via AJAX

## File Changes
- `admin/test-master.php` - Updated with comprehensive form
- `admin/ajax/get_test_parameters.php` - New endpoint for loading parameters
- `database_updates.sql` - SQL script for adding new fields

## Usage
1. Run the database updates first
2. Access the admin test master page
3. Use "Add New Test" for comprehensive test entry
4. Use "Edit" buttons for modifying existing tests
5. Parameters are managed within each test form

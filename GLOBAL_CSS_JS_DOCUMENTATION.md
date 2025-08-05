# PathLab Pro - Global CSS & JavaScript Documentation

## Overview
This document describes the global CSS and JavaScript system implemented for PathLab Pro Laboratory Management System.

## Files Structure
```
css/
├── global.css          # Main global stylesheet
├── custom.css          # Existing custom styles (deprecated)
├── home.css           # Home page specific styles
└── navbar-fix.css     # Navbar fixes

js/
├── global.js          # Main global JavaScript file

includes/
├── header.php         # Updated with global CSS includes
└── footer.php         # Updated with global JS includes
```

## Global CSS Features

### 1. CSS Variables (Custom Properties)
The global CSS uses CSS variables for consistent theming:
```css
:root {
  --primary-color: #2c5aa0;
  --primary-dark: #1e3c72;
  --primary-light: #4b6cb7;
  --success-color: #28a745;
  --danger-color: #dc3545;
  /* ... and many more */
}
```

### 2. Component Classes

#### Layout Components
- `.content-wrapper` - Main content area with gradient background
- `.content-header` - Page header styling
- `.content` - Main content section padding

#### Navigation Components
- `.main-header` - Top navigation bar with gradient
- `.main-sidebar` - Sidebar with dark gradient
- `.nav-sidebar` - Sidebar navigation styling
- `.brand-link` - Logo/brand area styling

#### Card Components
- `.card` - Base card styling with hover effects
- `.card-primary`, `.card-success`, etc. - Colored card variants
- `.card-header`, `.card-body`, `.card-footer` - Card sections

#### Dashboard Components
- `.small-box` - Statistics boxes with animations
- `.small-box:hover` - Hover effects for stat boxes

#### Button Components
- `.btn` - Base button styling
- `.btn-primary`, `.btn-success`, etc. - Button variants
- `.btn-outline-*` - Outline button variants
- `.btn-sm`, `.btn-lg` - Button sizes

#### Form Components
- `.form-control` - Input field styling
- `.form-control:focus` - Focus states
- `.form-label` - Label styling
- `.required::after` - Required field indicator
- `.input-group` - Input group styling

#### Table Components
- `.table-responsive` - Responsive table wrapper
- `.table` - Base table styling
- `.table-striped`, `.table-hover` - Table variants
- DataTables customization

#### Modal Components
- `.modal-content` - Modal styling
- `.modal-header`, `.modal-body`, `.modal-footer` - Modal sections
- Color variants for modal headers

#### Alert Components
- `.alert` - Base alert styling with animations
- `.alert-success`, `.alert-danger`, etc. - Alert variants

#### Badge Components
- `.badge` - Base badge styling
- `.badge-primary`, `.badge-success`, etc. - Badge variants

### 3. Utility Classes

#### Spacing
- `.m-0` to `.m-5` - Margin utilities
- `.p-0` to `.p-5` - Padding utilities
- `.mt-*`, `.mr-*`, `.mb-*`, `.ml-*` - Directional margins
- `.pt-*`, `.pr-*`, `.pb-*`, `.pl-*` - Directional padding

#### Text Utilities
- `.text-primary`, `.text-success`, etc. - Text colors
- `.text-left`, `.text-center`, `.text-right` - Text alignment
- `.font-weight-*` - Font weight utilities

#### Background Utilities
- `.bg-primary`, `.bg-success`, etc. - Background colors
- `.bg-gradient-primary` - Gradient backgrounds

#### Display Utilities
- `.d-none`, `.d-block`, `.d-flex`, etc. - Display utilities
- `.justify-content-*`, `.align-items-*` - Flex utilities

### 4. Responsive Design
- Mobile-first approach
- Breakpoints: 576px, 768px, 992px, 1200px
- Responsive typography and spacing
- Sidebar collapse on mobile

### 5. Accessibility Features
- Focus indicators for keyboard navigation
- High contrast mode support
- Reduced motion support
- Screen reader friendly markup

### 6. Dark Mode Support
- Automatic dark mode detection
- Dark color scheme variables
- Component adaptations for dark mode

### 7. Custom Animations
- Fade animations (`fadeIn`, `fadeInUp`, `fadeInDown`)
- Slide animations (`slideInLeft`, `slideInRight`)
- Utility animations (`pulse`, `bounce`, `spin`)
- Animation utility classes (`.animate-fadeIn`, etc.)

### 8. Print Styles
- Print-optimized layouts
- Hide interactive elements
- Black and white styling
- Page break management

## Global JavaScript Features

### 1. Namespace: `PathLabPro`
All functionality is organized under the global `PathLabPro` object:
```javascript
window.PathLabPro = {
    config: {},    // Configuration options
    utils: {},     // Utility functions
    ui: {},        // UI components and helpers
    data: {},      // Data management functions
    events: {}     // Event handlers
};
```

### 2. Utility Functions (`PathLabPro.utils`)

#### Common Utilities
- `debounce(func, delay)` - Debounce function calls
- `formatPhoneNumber(phone)` - Format phone numbers
- `validateEmail(email)` - Email validation
- `calculateAge(dateOfBirth)` - Calculate age from date
- `formatCurrency(amount)` - Currency formatting
- `formatDate(date, options)` - Date formatting
- `formatRelativeTime(date)` - Relative time formatting
- `generateId(prefix)` - Generate unique IDs
- `deepClone(obj)` - Deep clone objects
- `isInViewport(element)` - Check element visibility

### 3. UI Components (`PathLabPro.ui`)

#### Loading States
- `showLoading(element, text)` - Show loading indicator
- `hideLoading(element)` - Hide loading indicator

#### Notifications
- `showToast(message, type, options)` - Show toast notifications
- `showConfirmation(options)` - Show confirmation dialogs
- `showAlert(message, type, options)` - Show alert dialogs

#### Animations
- `animateCounter(element, targetValue, duration)` - Animate counters
- `addRippleEffect(element)` - Add ripple effect to buttons

#### Form Helpers
- `autoResizeTextarea(element)` - Auto-resize textareas
- `formatFormInputs(container)` - Format form inputs

### 4. Data Management (`PathLabPro.data`)

#### AJAX Wrapper
- `request(options)` - Generic AJAX request wrapper
- `get(endpoint, params)` - GET requests
- `post(endpoint, data)` - POST requests
- `put(endpoint, data)` - PUT requests
- `delete(endpoint, data)` - DELETE requests
- `upload(endpoint, formData)` - File uploads

#### Local Storage
- `storage.set(key, value)` - Store data locally
- `storage.get(key, defaultValue)` - Retrieve stored data
- `storage.remove(key)` - Remove stored data
- `storage.clear()` - Clear all stored data

### 5. Event Handlers (`PathLabPro.events`)

#### Automatic Event Binding
- AJAX form submissions (`form[data-ajax="true"]`)
- Data action clicks (`[data-action]`)
- Confirmation dialogs (`[data-confirm]`)
- Search inputs (`[data-search]`)
- Sidebar toggle (`[data-widget="pushmenu"]`)
- Fullscreen toggle (`[data-widget="fullscreen"]`)
- Auto-save forms (`[data-autosave]`)
- File uploads (`input[type="file"][data-upload]`)

#### Custom Events
- `ajax:success` - Fired on successful AJAX form submission
- `ajax:error` - Fired on AJAX form error
- `ajax:fail` - Fired on AJAX request failure
- `search:perform` - Fired when search is performed
- `search:clear` - Fired when search is cleared
- `page:visible` - Fired when page becomes visible
- `connection:online` - Fired when connection is restored
- `connection:offline` - Fired when connection is lost

## Data Attributes

### Form Attributes
- `data-ajax="true"` - Enable AJAX form submission
- `data-endpoint="api/endpoint"` - API endpoint for form submission
- `data-reset="false"` - Prevent form reset after submission
- `data-autosave="key"` - Enable auto-save functionality

### Action Attributes
- `data-action="refresh"` - Refresh action
- `data-action="delete"` - Delete action with confirmation
- `data-action="export"` - Export action
- `data-target="functionName"` - Target function for action
- `data-params="{}"` - Parameters for action

### Confirmation Attributes
- `data-confirm="message"` - Confirmation message
- `data-confirm-title="title"` - Confirmation dialog title

### Search Attributes
- `data-search="#tableId"` - Search target (DataTable)
- `data-min-length="2"` - Minimum search length

### File Upload Attributes
- `data-upload="true"` - Enable file upload handling
- `data-max-size="5242880"` - Maximum file size in bytes
- `data-allowed-types="image/jpeg,image/png"` - Allowed MIME types

## Usage Examples

### 1. AJAX Form
```html
<form data-ajax="true" data-endpoint="patients_api.php" data-reset="true">
    <input type="hidden" name="action" value="add">
    <input type="text" name="name" required>
    <button type="submit">Submit</button>
</form>
```

### 2. Confirmation Dialog
```html
<a href="delete.php?id=1" data-confirm="Are you sure you want to delete this item?">
    Delete Item
</a>
```

### 3. Search Input
```html
<input type="text" data-search="#patientsTable" data-min-length="2" placeholder="Search patients...">
```

### 4. Auto-save Form
```html
<textarea data-autosave="patient_notes" placeholder="Patient notes..."></textarea>
```

### 5. Action Buttons
```html
<button data-action="refresh" data-target="refreshPatientsTable">Refresh</button>
<button data-action="delete" data-url="api/patients_api.php" data-params='{"id": 123}'>Delete</button>
```

### 6. File Upload
```html
<input type="file" data-upload="true" data-max-size="2097152" data-allowed-types="image/jpeg,image/png">
```

## JavaScript API Examples

### 1. Show Notifications
```javascript
// Success notification
PathLabPro.ui.showToast('Operation completed successfully!', 'success');

// Error notification
PathLabPro.ui.showToast('Something went wrong', 'error');

// Confirmation dialog
PathLabPro.ui.showConfirmation({
    title: 'Delete Patient',
    text: 'This will permanently delete the patient record.'
}).then((result) => {
    if (result.isConfirmed) {
        // Perform delete action
    }
});
```

### 2. AJAX Requests
```javascript
// GET request
PathLabPro.data.get('patients_api.php', { action: 'list' })
    .done(function(response) {
        console.log(response);
    });

// POST request
PathLabPro.data.post('patients_api.php', {
    action: 'add',
    name: 'John Doe',
    phone: '555-1234'
}).done(function(response) {
    if (response.success) {
        PathLabPro.ui.showToast(response.message, 'success');
    }
});
```

### 3. Utility Functions
```javascript
// Format phone number
const formatted = PathLabPro.utils.formatPhoneNumber('5551234567');
// Result: "(555) 123-4567"

// Calculate age
const age = PathLabPro.utils.calculateAge('1990-01-01');
// Result: 35 (as of 2025)

// Debounce function
const debouncedSearch = PathLabPro.utils.debounce(function(query) {
    // Perform search
}, 300);
```

### 4. Local Storage
```javascript
// Store data
PathLabPro.data.storage.set('user_preferences', {
    theme: 'dark',
    language: 'en'
});

// Retrieve data
const preferences = PathLabPro.data.storage.get('user_preferences', {});

// Remove data
PathLabPro.data.storage.remove('user_preferences');
```

## Browser Support
- Modern browsers (Chrome 60+, Firefox 60+, Safari 12+, Edge 79+)
- CSS Grid and Flexbox support required
- ES6+ features used in JavaScript
- Graceful degradation for older browsers

## Performance Considerations
- CSS variables reduce bundle size
- Debounced event handlers prevent excessive API calls
- Lazy loading of components
- Optimized animations with `transform` and `opacity`
- Efficient event delegation
- Local storage for caching preferences

## Integration Notes
- Must be loaded after jQuery, Bootstrap, and AdminLTE
- DataTables, SweetAlert2, and Toastr are required dependencies
- Font Awesome icons are used throughout
- Google Fonts (Inter) is the primary font family

## Customization
To customize the global styles:

1. **Colors**: Modify CSS variables in `:root`
2. **Spacing**: Adjust spacing variables
3. **Fonts**: Change font family variables
4. **Components**: Override specific component styles
5. **Breakpoints**: Modify responsive breakpoints

Example customization:
```css
:root {
    --primary-color: #your-brand-color;
    --border-radius: 0.5rem;
    --font-family: 'Your Font', sans-serif;
}
```

## Migration from Existing CSS
1. The new global.css replaces custom.css
2. Existing page-specific styles remain compatible
3. New utility classes provide more consistent spacing
4. Component classes follow BEM-like naming conventions
5. Legacy styles are maintained for backward compatibility

## Best Practices
1. Use CSS variables for consistent theming
2. Utilize utility classes for common styling patterns
3. Follow the established naming conventions
4. Use data attributes for JavaScript functionality
5. Leverage the PathLabPro namespace for custom scripts
6. Test responsive design across all breakpoints
7. Ensure accessibility compliance
8. Optimize for performance and maintainability

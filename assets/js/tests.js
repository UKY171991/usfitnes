// Initialize test management functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeTestForms();
    initializeTestSearch();
    initializeTestFilters();
    initializeTestDeletion();
});

// Initialize test forms with validation
function initializeTestForms() {
    const testForm = document.querySelector('.test-form');
    if (!testForm) return;

    testForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateTestForm(this)) {
            showAlert('Please fill in all required fields correctly.', 'error');
            return;
        }

        try {
            const formData = new FormData(this);
            const response = await makeRequest('includes/process_test.php', {
                method: 'POST',
                body: formData
            });

            if (response.success) {
                showAlert(response.message, 'success');
                this.reset();
                if (typeof updateTestList === 'function') {
                    updateTestList();
                }
            } else {
                showAlert(response.message || 'An error occurred while processing the test.', 'error');
            }
        } catch (error) {
            showAlert('An error occurred while processing the test.', 'error');
            console.error('Test processing error:', error);
        }
    });
}

// Validate test form fields
function validateTestForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // Validate numeric fields
    const numericFields = form.querySelectorAll('[type="number"]');
    numericFields.forEach(field => {
        const value = parseFloat(field.value);
        const min = parseFloat(field.getAttribute('min'));
        const max = parseFloat(field.getAttribute('max'));

        if (isNaN(value) || (min !== null && value < min) || (max !== null && value > max)) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });

    return isValid;
}

// Initialize test search functionality
function initializeTestSearch() {
    const searchInput = document.querySelector('.test-search');
    if (!searchInput) return;

    const debounceSearch = debounce(function(value) {
        updateTestList(1, value);
    }, 300);

    searchInput.addEventListener('input', function() {
        debounceSearch(this.value);
    });
}

// Initialize test filters
function initializeTestFilters() {
    const filterForm = document.querySelector('.test-filters');
    if (!filterForm) return;

    filterForm.addEventListener('change', function() {
        const formData = new FormData(this);
        const filters = Object.fromEntries(formData);
        updateTestList(1, null, filters);
    });
}

// Update test listing
async function updateTestList(page = 1, search = null, filters = null) {
    try {
        const params = new URLSearchParams({
            page: page,
            search: search || '',
            ...filters
        });

        const response = await makeRequest(`includes/fetch_tests.php?${params}`);
        
        if (response.success) {
            updateTestTable(response.tests);
            updatePagination(response.pagination);
        } else {
            showAlert('Error fetching test list.', 'error');
        }
    } catch (error) {
        showAlert('An error occurred while fetching tests.', 'error');
        console.error('Test list update error:', error);
    }
}

// Update test table with new data
function updateTestTable(tests) {
    const tableBody = document.querySelector('.test-table tbody');
    if (!tableBody) return;

    tableBody.innerHTML = tests.map(test => `
        <tr>
            <td>${escapeHtml(test.id)}</td>
            <td>${escapeHtml(test.name)}</td>
            <td>${escapeHtml(test.category)}</td>
            <td>${escapeHtml(test.price)}</td>
            <td>
                <span class="badge ${test.status === 'Active' ? 'bg-success' : 'bg-danger'}">
                    ${escapeHtml(test.status)}
                </span>
            </td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary" onclick="editTest(${test.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTest(${test.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Initialize test deletion functionality
function initializeTestDeletion() {
    window.deleteTest = async function(testId) {
        if (!confirm('Are you sure you want to delete this test?')) {
            return;
        }

        try {
            const response = await makeRequest('includes/delete_test.php', {
                method: 'POST',
                body: JSON.stringify({ test_id: testId })
            });

            if (response.success) {
                showAlert('Test deleted successfully.', 'success');
                updateTestList();
            } else {
                showAlert(response.message || 'Error deleting test.', 'error');
            }
        } catch (error) {
            showAlert('An error occurred while deleting the test.', 'error');
            console.error('Test deletion error:', error);
        }
    };
}

// Edit test function
window.editTest = function(testId) {
    window.location.href = `edit_test.php?id=${testId}`;
}; 
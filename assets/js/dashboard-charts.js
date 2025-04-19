// Initialize charts when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeMonthlyTestsChart();
    initializeCategoryDistributionChart();
});

// Function to initialize monthly tests chart
function initializeMonthlyTestsChart() {
    const ctx = document.getElementById('monthlyTestsChart').getContext('2d');
    window.monthlyTestsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Tests Performed',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
}

// Function to initialize category distribution chart
function initializeCategoryDistributionChart() {
    const ctx = document.getElementById('categoryDistributionChart').getContext('2d');
    window.categoryDistributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 206, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Function to update charts with new data
function updateCharts(data) {
    if (!data.monthly_stats || !data.category_distribution) return;

    // Update monthly tests chart
    const monthlyChart = window.monthlyTestsChart;
    if (monthlyChart) {
        monthlyChart.data.labels = data.monthly_stats.map(stat => stat.month);
        monthlyChart.data.datasets[0].data = data.monthly_stats.map(stat => stat.count);
        monthlyChart.update();
    }

    // Update category distribution chart
    const categoryChart = window.categoryDistributionChart;
    if (categoryChart) {
        categoryChart.data.labels = data.category_distribution.map(cat => cat.name);
        categoryChart.data.datasets[0].data = data.category_distribution.map(cat => cat.count);
        categoryChart.update();
    }
}

// Function to handle chart resizing
function handleChartResize() {
    if (window.monthlyTestsChart) {
        window.monthlyTestsChart.resize();
    }
    if (window.categoryDistributionChart) {
        window.categoryDistributionChart.resize();
    }
}

// Add window resize listener
window.addEventListener('resize', handleChartResize); 
<!-- daily-sales.blade.php - Complete updated file -->
<div class="card mb-4 daily-sales-card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa-solid fa-chart-line mr-2"></i> Daily Sales
        </h5>
        <div class="date-selector">
            <div class="input-group">
                <input type="date" id="salesDate" class="form-control form-control-sm" value="{{ $data['selectedDate'] }}">
                <div class="input-group-append">
                    <button class="btn btn-light btn-sm" type="button" id="updateSalesBtn">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0" id="dailySalesContent">
        @if(empty($data['dailySales']['by_category']))
            <div class="text-center p-4">
                <i class="fa-solid fa-receipt text-muted" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">No sales recorded for {{ \Carbon\Carbon::parse($data['selectedDate'])->format('M d, Y') }}</p>
            </div>
        @else
            <div class="daily-sales-summary p-3 bg-light border-bottom">
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($data['selectedDate'])->format('M d, Y') }} | 
                <strong>Total Items Sold:</strong> {{ $data['dailySales']['total_items'] }}
            </div>
            <div class="daily-sales-list">
                @foreach($data['dailySales']['by_category'] as $categoryId => $category)
                    <div class="category-section">
                        <div class="category-header d-flex justify-content-between align-items-center p-2 bg-light border-bottom">
                            <span class="font-weight-bold">{{ $category['name'] }}</span>
                            <span class="badge badge-primary">{{ $category['total'] }} items</span>
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach($category['items'] as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <span class="item-name">{{ $item['name'] }}</span>
                                        <small class="text-muted d-block">by {{ $item['user'] }}</small>
                                    </div>
                                    <span class="badge badge-pill badge-secondary">{{ $item['quantity'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    <div class="card-footer text-center">
        <a href="#" class="btn btn-sm btn-outline-primary" id="printDailySalesBtn">
            <i class="fa-solid fa-print mr-1"></i> Print Report
        </a>
    </div>
</div>

<style>
.daily-sales-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.daily-sales-list {
    max-height: 500px;
    overflow-y: auto;
}

.category-section {
    border-bottom: 1px solid #eee;
}

.category-section:last-child {
    border-bottom: none;
}

.category-header {
    background-color: #f8f9fa;
}

.list-group-item {
    padding: 0.5rem 1rem;
    border-left: none;
    border-right: none;
}

.item-name {
    font-weight: 500;
}

.badge-pill {
    min-width: 30px;
}

#dailySalesContent {
    position: relative;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255,255,255,0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.error-message {
    color: #dc3545;
    padding: 20px;
    text-align: center;
}

@media print {
    body * {
        visibility: hidden;
    }
    
    .daily-sales-card, .daily-sales-card * {
        visibility: visible;
    }
    
    .daily-sales-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    .card-footer {
        display: none;
    }
}
</style>

<script>
// Make sure jQuery is properly loaded before running this script
$(document).ready(function() {
    console.log('Daily sales script initialized');
    
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Update sales data when search button is clicked
    $('#updateSalesBtn').click(function() {
        updateDailySales();
    });
    
    // Also update when pressing Enter in the date field
    $('#salesDate').keypress(function(e) {
        if(e.which == 13) { // Enter key
            updateDailySales();
        }
    });
    
    // Function to update daily sales data
    function updateDailySales() {
        var selectedDate = $('#salesDate').val();
        
        // Validate date format
        if (!selectedDate || !selectedDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
            alert('Please select a valid date');
            return;
        }
        
        console.log('Searching for date:', selectedDate);
        
        // Show loading overlay
        $('#dailySalesContent').append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        // Clear any previous error messages
        $('.error-message').remove();
        
        // Method 1: Try direct page refresh (most reliable)
        window.location.href = '/inventory/stock?date=' + selectedDate;
        return;
        
        // Method 2: Use AJAX with multiple fallback URLs (uncomment if you want to use AJAX)
        /*
        // Try first URL
        tryAjaxRequest('/inventory/stock/daily-sales', selectedDate, function(success) {
            if (!success) {
                // Try second URL if first fails
                tryAjaxRequest('/api/daily-sales', selectedDate, function(success) {
                    if (!success) {
                        // Try third URL if second fails
                        tryAjaxRequest('/daily-sales', selectedDate, function(success) {
                            if (!success) {
                                // If all URLs fail, show the error
                                showError('All URL attempts failed. Try reloading the page.');
                            }
                        });
                    }
                });
            }
        });
        */
    }
    
    // Helper function to try an AJAX request with multiple URLs
    function tryAjaxRequest(url, selectedDate, callback) {
        console.log('Trying URL:', url);
        
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                date: selectedDate
            },
            dataType: 'json',
            success: function(response) {
                console.log('Success response from ' + url + ':', response);
                renderDailySales(response, selectedDate);
                callback(true);
            },
            error: function(xhr, status, error) {
                console.error('Error from ' + url + ':', {
                    status: status,
                    error: error,
                    status_code: xhr.status,
                    response_text: xhr.responseText && xhr.responseText.substring(0, 100) + '...'
                });
                callback(false);
            }
        });
    }
    
    // Function to render daily sales data
    function renderDailySales(data, selectedDate) {
        var content = '';
        var formattedDate = '';
        
        try {
            formattedDate = new Date(selectedDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch (e) {
            console.error('Error formatting date:', e);
            formattedDate = selectedDate || 'selected date';
        }
        
        // Remove loading overlay
        $('.loading-overlay').remove();
        
        // Safety check for data structure
        if (!data || !data.by_category || Object.keys(data.by_category).length === 0) {
            content = `
                <div class="text-center p-4">
                    <i class="fa-solid fa-receipt text-muted" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">No sales recorded for ${formattedDate}</p>
                </div>
            `;
        } else {
            // Calculate total items safely
            var totalItems = data.total_items || 0;
            
            content = `
                <div class="daily-sales-summary p-3 bg-light border-bottom">
                    <strong>Date:</strong> ${formattedDate} | 
                    <strong>Total Items Sold:</strong> ${totalItems}
                </div>
                <div class="daily-sales-list">
            `;
            
            for (const [categoryId, category] of Object.entries(data.by_category)) {
                // Skip invalid categories
                if (!category || !category.items) {
                    continue;
                }
                
                content += `
                    <div class="category-section">
                        <div class="category-header d-flex justify-content-between align-items-center p-2 bg-light border-bottom">
                            <span class="font-weight-bold">${category.name || 'Uncategorized'}</span>
                            <span class="badge badge-primary">${category.total || 0} items</span>
                        </div>
                        <ul class="list-group list-group-flush">
                `;
                
                // Only process items if they exist
                if (Array.isArray(category.items)) {
                    category.items.forEach(item => {
                        if (!item) return; // Skip null items
                        
                        content += `
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <span class="item-name">${item.name || 'Unknown Item'}</span>
                                    <small class="text-muted d-block">by ${item.user || 'Unknown'}</small>
                                </div>
                                <span class="badge badge-pill badge-secondary">${item.quantity || 0}</span>
                            </li>
                        `;
                    });
                }
                
                content += `
                        </ul>
                    </div>
                `;
            }
            
            content += `</div>`;
        }
        
        // Update the content
        $('#dailySalesContent').html(content);
    }
    
    // Function to show error message
    function showError(message) {
        // Remove loading overlay
        $('.loading-overlay').remove();
        
        $('#dailySalesContent').html(
            '<div class="error-message">' +
            '<i class="fa-solid fa-exclamation-circle fa-2x mb-3"></i>' +
            '<p>' + message + '</p>' +
            '<button class="btn btn-sm btn-outline-primary retry-btn mt-3">Try Again</button>' +
            '</div>'
        );
        
        // Add retry button functionality
        $('.retry-btn').click(function(e) {
            e.preventDefault();
            updateDailySales();
        });
    }
    
    // Handle print button
    $('#printDailySalesBtn').click(function(e) {
        e.preventDefault();
        window.print();
    });
});
</script>
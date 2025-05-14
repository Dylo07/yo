<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kitchen Display System - {{ config('app.name', 'Hotel Soba Lanka') }}</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        /* Style for the status buttons */
        .status-button {
            transition: all 0.2s ease-in-out;
        }

        .status-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.1);
        }

        .status-button:active {
            transform: translateY(1px);
        }

        .active-tab { background-color: #2563eb; }
        .tab-content { transition: all 0.3s ease-in-out; }
        .tab-content.hidden { display: none; }
        
        /* Status styles */
        .status-badge {
            font-weight: bold;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        
        .status-new { background-color: #ef4444; color: white; }
        .status-cooking { background-color: #f59e0b; color: white; }
        .status-ready { background-color: #10b981; color: white; }
        
        /* Order card styles */
        .order-card {
            transition: all 0.2s ease-in-out;
            border-radius: 0.5rem;
            border-width: 1px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Special request highlight */
        .special-request {
            border-left: 4px solid #f59e0b;
            background-color: #fef3c7;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border-radius: 0.25rem;
        }
        
        /* Auto-refresh indicator */
        .refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: rgba(37, 99, 235, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            z-index: 50;
        }
        
        .refresh-indicator i {
            animation: spin 2s linear infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Food menu summary styles */
        .food-menu-summary {
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col h-screen">
        <!-- Top Navigation -->
        <nav class="bg-white px-4 py-2 shadow">
            <div class="flex justify-between items-center">
                <a href="{{ url('/home') }}" class="flex items-center text-xl font-bold text-blue-600">
                    <i class="fas fa-utensils mr-2"></i> Hotel Soba Lanka
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/home') }}" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <a href="{{ url('/cashier') }}" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-cash-register mr-1"></i> Cashier
                    </a>
                    <a href="{{ url('/food-menu') }}" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-utensils mr-1"></i> Food Menus
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

    
        <!-- Header with time and date -->
        <header class="bg-blue-700 text-white p-4">
            <div class="flex justify-between items-center">
                <div class="text-2xl font-bold flex items-center">
                    <i class="fas fa-utensils mr-2"></i>
                    Hotel Kitchen Display
                </div>
                <div class="flex items-center gap-6">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span id="current-date"></span>
                    </div>
                    <div class="flex items-center text-2xl">
                        <i class="fas fa-clock mr-2"></i>
                        <span id="current-time"></span>
                    </div>
                    <button id="refresh-btn" class="bg-blue-800 hover:bg-blue-900 text-white px-4 py-2 rounded flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh
                    </button>
                </div>
            </div>
        </header>
        
       <!-- Navigation Tabs -->
<div class="bg-blue-800 text-white">
    <div class="flex flex-wrap">
        <button class="tab-button active-tab flex items-center px-4 py-3" data-tab="orders">
            <i class="fas fa-clipboard-list mr-2"></i>
            Active Orders
        </button>
        
        <button class="tab-button flex items-center px-4 py-3" data-tab="today">
            <i class="fas fa-calendar-day mr-2"></i>
            Today
        </button>
        <button class="tab-button flex items-center px-4 py-3" data-tab="tomorrow">
            <i class="fas fa-calendar-plus mr-2"></i>
            Tomorrow
        </button>
        <button class="tab-button flex items-center px-4 py-3" data-tab="day3">
            <i class="fas fa-calendar-week mr-2"></i>
            {{ now()->addDays(2)->format('D, M j') }}
        </button>
        <button class="tab-button flex items-center px-4 py-3" data-tab="day4">
            <i class="fas fa-calendar-week mr-2"></i>
            {{ now()->addDays(3)->format('D, M j') }}
        </button>
        <button class="tab-button flex items-center px-4 py-3" data-tab="day5">
            <i class="fas fa-calendar-week mr-2"></i>
            {{ now()->addDays(4)->format('D, M j') }}
        </button>
        <button class="tab-button flex items-center px-4 py-3" data-tab="day6">
            <i class="fas fa-calendar-week mr-2"></i>
            {{ now()->addDays(5)->format('D, M j') }}
        </button>

        <button class="tab-button flex items-center px-4 py-3" data-tab="yesterday">
            <i class="fas fa-calendar-minus mr-2"></i>
            Yesterday
        </button>
        <button class="tab-button flex items-center px-4 py-3" data-tab="analytics">
            <i class="fas fa-chart-bar mr-2"></i>
            Analytics
        </button>
    </div>
</div>
        
        <!-- Filter Bar - Only show for orders tab -->
        <div id="orders-filter" class="bg-white border-b border-gray-200 p-2">
            <div class="flex items-center justify-between">
                <div class="flex space-x-1">
                    <button class="source-filter px-3 py-1 rounded-md bg-blue-600 text-white" data-source="all">
                        All Orders
                    </button>
                    
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Auto-refresh:</span>
                    <select id="refresh-interval" class="form-select rounded-md border-gray-300 shadow-sm">
    <option value="0">Off</option>
    <option value="10">10 sec</option>
    <option value="30" selected>30 sec</option>
    <option value="60">1 min</option>
</select>
                </div>
            </div>
        </div>
        
        <!-- Main content area -->
        <div class="flex-1 p-6 overflow-auto">
            <!-- Orders Tab -->
            <div id="orders-tab" class="tab-content">
                <h2 class="text-2xl font-bold mb-4">Active Kitchen Orders</h2>
                
                @if(count($activeOrders) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="orders-container">
                    @foreach($activeOrders as $order)
                    <div class="border rounded-lg shadow-md p-4 order-card {{ $order->getCardBackgroundAttribute() ?? 'bg-gray-100 border-gray-300' }}" data-order-id="{{ $order->id }}">
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center">
                                <span class="font-bold text-xl mr-3">#{{ $order->order_id ?? 'New' }}</span>
                                <span class="text-lg">{{ $order->table_name ?? 'Table' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span>{{ $order->time ?? now()->format('H:i') }}</span>
                                <span class="status-badge {{ $order->status === 'NEW' ? 'status-new' : 
                          ($order->status === 'COOKING' ? 'status-cooking' : 
                          'status-ready') }}">
                                    {{ $order->status ?? 'NEW' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 mb-3 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-users mr-1"></i>
                                {{ $order->guests ?? '0' }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $order->source ?? 'Restaurant' }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                Est: {{ $order->estimated_complete ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <table class="w-full mb-3">
                            <thead>
                                <tr class="border-b border-gray-300">
                                    <th class="text-left py-2">Item</th>
                                    <th class="text-center py-2 w-12">Qty</th>
                                    <th class="text-center py-2 w-24">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($order->items as $item)
                            <tr class="border-b border-gray-200 last:border-0" data-item-id="{{ $item->id }}">
                                <td class="py-2">
                                    <div class="font-medium">{{ $item->menu_name ?? 'Unknown Item' }}</div>
                                    <div class="text-xs text-gray-500">
                                        Added: {{ isset($item->updated_at) ? \Carbon\Carbon::parse($item->updated_at)->format('H:i:s') : 'N/A' }}
                                    </div>
                                    @if(!empty($item->notes))
                                    <div class="text-xs text-gray-500">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-2 text-center">{{ $item->qty ?? '1' }}</td>
                                <td class="py-2 text-center">
                                    <button 
                                        class="status-button w-full py-1 rounded-md text-white {{ $item->status == 'ready' ? 'bg-green-500' : 'bg-yellow-500' }}"
                                        data-item-id="{{ $item->id }}"
                                        data-current-status="{{ $item->status }}"
                                        onclick="toggleItemStatus(this)"
                                    >
                                        {{ ucfirst($item->status == 'ready' ? 'Ready' : 'Cooking') }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">No items in this order</td>
                            </tr>
                            @endforelse
                            </tbody>
                        </table>
                        
                        @php
                            $allItemsReady = $order->items->every(function($item) { return $item->status == 'ready'; });
                        @endphp

                        <div class="flex justify-between">
                            <button class="text-blue-600 flex items-center text-sm action-button message-order-btn" data-order-id="{{ $order->id }}">
                                <i class="fas fa-comment-alt mr-1"></i>
                                Send Message
                            </button>
                            
                            @if(!$allItemsReady)
                            
                            @else
                            <div>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded text-sm">All Items Ready</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <i class="fas fa-clipboard-check text-gray-400 text-5xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Active Orders</h3>
                    <p class="text-gray-500">There are currently no active orders in the kitchen.</p>
                </div>
                @endif
            </div>
            
            <!-- Today's Events Tab - Using New Separate View -->
            <div id="today-tab" class="tab-content hidden">
                @include('kitchen.today-events', ['todayBookings' => $todayBookings])
            </div>
            
            <!-- Tomorrow's Events Tab - Using New Separate View -->
            <div id="tomorrow-tab" class="tab-content hidden">
                @include('kitchen.tomorrow-events', ['tomorrowBookings' => $tomorrowBookings])
            </div>
             <!-- Yesterday's Events Tab -->
    <div id="yesterday-tab" class="tab-content hidden">
        @include('kitchen.yesterday-events', ['yesterdayBookings' => $yesterdayBookings])
    </div>
    <!-- Day 3 Tab -->
    <div id="day3-tab" class="tab-content hidden">
        @include('kitchen.day3-events', ['day3Bookings' => $day3Bookings])
    </div>
    
    <!-- Day 4 Tab -->
    <div id="day4-tab" class="tab-content hidden">
        @include('kitchen.day4-events', ['day4Bookings' => $day4Bookings])
    </div>
    
    <!-- Day 5 Tab -->
    <div id="day5-tab" class="tab-content hidden">
        @include('kitchen.day5-events', ['day5Bookings' => $day5Bookings])
    </div>
    
    <!-- Day 6 Tab -->
    <div id="day6-tab" class="tab-content hidden">
        @include('kitchen.day6-events', ['day6Bookings' => $day6Bookings])
    </div>
            
            <!-- Analytics Tab - Simplified -->
            <div id="analytics-tab" class="tab-content hidden">
                <h2 class="text-2xl font-bold mb-4">Kitchen Analytics</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Order Count Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Orders Today</h3>
                                <div class="text-3xl font-bold mt-2" id="orders-count">--</div>
                                <div class="text-sm text-green-500 mt-2" id="orders-trend">+0% from yesterday</div>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-clipboard-list text-blue-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prep Time Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Avg. Prep Time</h3>
                                <div class="text-3xl font-bold mt-2" id="avg-prep-time">--</div>
                                <div class="text-sm text-red-500 mt-2" id="prep-time-trend">+0 min from target</div>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-clock text-yellow-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Late Orders Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Late Orders</h3>
                                <div class="text-3xl font-bold mt-2" id="late-orders">--</div>
                                <div class="text-sm text-green-500 mt-2" id="late-orders-trend">+0 from yesterday</div>
                            </div>
                            <div class="bg-red-100 p-3 rounded-full">
                                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Coming Soon Message -->
                <div class="bg-white rounded-lg shadow-md p-8 text-center mt-6">
                    <i class="fas fa-chart-line text-gray-400 text-5xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Detailed Analytics Coming Soon</h3>
                    <p class="text-gray-500">Expanded analytics features are currently under development.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Auto-refresh indicator (hidden by default) -->
    <div id="refresh-indicator" class="refresh-indicator hidden">
        <i class="fas fa-sync-alt"></i>
        <span>Auto-refreshing in <span id="countdown">10</span>s</span>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cache DOM elements
        const dateElement = document.getElementById('current-date');
        const timeElement = document.getElementById('current-time');
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        const ordersFilter = document.getElementById('orders-filter');
        const sourceFilters = document.querySelectorAll('.source-filter');
        const refreshBtn = document.getElementById('refresh-btn');
        const refreshIntervalSelect = document.getElementById('refresh-interval');
        const refreshIndicator = document.getElementById('refresh-indicator');
        const countdownElement = document.getElementById('countdown');
        
        // Update date and time
        function updateDateTime() {
            const now = new Date();
            dateElement.textContent = now.toLocaleDateString('en-US', { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
            });
            timeElement.textContent = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', minute: '2-digit', second: '2-digit' 
            });
        }
        
        // Initial update
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Tab switching
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Hide all tabs
                tabContents.forEach(tab => tab.classList.add('hidden'));
                
                // Remove active class from all buttons
                tabButtons.forEach(btn => btn.classList.remove('active-tab', 'bg-blue-600'));
                
                // Show the selected tab
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.remove('hidden');
                
                // Add active class to clicked button
                this.classList.add('active-tab', 'bg-blue-600');
                
                // Show/hide orders filter
                if (tabId === 'orders') {
                    ordersFilter.style.display = 'block';
                } else {
                    ordersFilter.style.display = 'none';
                }
                
                // Reset auto-refresh if tab changed
                resetAutoRefresh();
                
                // If analytics tab is active, fetch analytics data
                if (tabId === 'analytics') {
                    fetchAnalytics();
                }
            });
        });
        
        // Order source filtering
        sourceFilters.forEach(filter => {
            filter.addEventListener('click', function() {
                // Remove active class from all filters
                sourceFilters.forEach(f => {
                    f.classList.remove('bg-blue-600', 'text-white');
                    f.classList.add('bg-gray-200', 'text-gray-700');
                });
                
                // Add active class to clicked filter
                this.classList.remove('bg-gray-200', 'text-gray-700');
                this.classList.add('bg-blue-600', 'text-white');
                
                // Get the selected source
                const source = this.getAttribute('data-source');
                
                // Filter orders
                filterOrdersBySource(source);
            });
        });
        
        // Filter orders by source
        function filterOrdersBySource(source) {
            // Show loading state
            const ordersContainer = document.getElementById('orders-container');
            const loadingHtml = '<div class="col-span-full flex justify-center py-8"><i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i></div>';
            ordersContainer.innerHTML = loadingHtml;
            
            // Make AJAX call to get filtered orders
            fetch(`/kitchen/orders/filter?source=${source}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Reload the page to show the filtered data
                window.location.reload();
            })
            .catch(error => {
                console.error('Error filtering orders:', error);
                // Fallback - just reload the page
                window.location.reload();
            });
        }
        
        // Handle order actions
        document.addEventListener('click', function(event) {
            // Bump order button
            if (event.target.closest('.bump-order-btn')) {
                const button = event.target.closest('.bump-order-btn');
                const orderId = button.getAttribute('data-order-id');
                updateOrderStatus(orderId, 'READY');
            }
            
            // Modify order button
            if (event.target.closest('.modify-order-btn')) {
                const button = event.target.closest('.modify-order-btn');
                const orderId = button.getAttribute('data-order-id');
                // This would open a modal or redirect to a page to modify the order
                alert('Modify order ' + orderId + ' (not implemented)');
            }
            
            // Send message button
            if (event.target.closest('.message-order-btn')) {
                const button = event.target.closest('.message-order-btn');
                const orderId = button.getAttribute('data-order-id');
                // This would open a modal to send a message
                alert('Send message for order ' + orderId + ' (not implemented)');
            }
        });
        
        // Handle the manual refresh button
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
        
        // Auto-refresh functionality
        let refreshInterval = parseInt(refreshIntervalSelect.value);
        let countdownInterval;
        let countdown = refreshInterval;
        
        function startAutoRefresh() {
            // Stop any existing countdown
            clearInterval(countdownInterval);
            
            // Reset countdown
            countdown = refreshInterval;
            countdownElement.textContent = countdown;
            
            // Show indicator if auto-refresh is on
            if (refreshInterval > 0) {
                refreshIndicator.classList.remove('hidden');
                
                // Start countdown
                countdownInterval = setInterval(() => {
                    countdown--;
                    countdownElement.textContent = countdown;
                    
                    if (countdown <= 0) {
                        // Refresh the page
                        window.location.reload();
                    }
                }, 1000);
            } else {
                refreshIndicator.classList.add('hidden');
            }
        }
        
        function resetAutoRefresh() {
            if (refreshInterval > 0) {
                clearInterval(countdownInterval);
                startAutoRefresh();
            }
        }
        
        // Handle changes to the refresh interval
        refreshIntervalSelect.addEventListener('change', function() {
            refreshInterval = parseInt(this.value);
            clearInterval(countdownInterval);
            
            if (refreshInterval > 0) {
                startAutoRefresh();
            } else {
                refreshIndicator.classList.add('hidden');
            }
        });
        
        // Start auto-refresh on page load if enabled
        if (refreshInterval > 0) {
            startAutoRefresh();
        }
        
        // Update order status
        function updateOrderStatus(orderId, status) {
            fetch(`/kitchen/orders/${orderId}/status`, {
                method: 'PUT',
                headers: {
                   'X-Requested-With': 'XMLHttpRequest',
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
               },
               body: JSON.stringify({ status: status })
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   // Refresh the page to show the updated status
                   window.location.reload();
               }
           })
           .catch(error => {
               console.error('Error updating order status:', error);
               alert('Error updating order status. Please try again.');
           });
       }
       
       // Function to toggle item status between cooking and ready
      /**
 * Fixed Item Status Toggle Function
 * This function properly updates and persists item status changes
 */
window.toggleItemStatus = function(button) {
    const itemId = button.getAttribute('data-item-id');
    const currentStatus = button.getAttribute('data-current-status');
    const newStatus = currentStatus === 'ready' ? 'cooking' : 'ready';
    const orderId = button.closest('.order-card').getAttribute('data-order-id');
    
    // Show loading state
    const originalText = button.textContent;
    button.textContent = 'Updating...';
    button.disabled = true;
    
    // Update the item status via AJAX
    fetch(`/kitchen/items/${itemId}/status`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            status: newStatus,
            order_id: orderId  // Pass the order ID so the controller can update the kitchen_orders table
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            if (newStatus === 'ready') {
                button.classList.remove('bg-yellow-500');
                button.classList.add('bg-green-500');
                button.textContent = 'Ready';
            } else {
                button.classList.remove('bg-green-500');
                button.classList.add('bg-yellow-500');
                button.textContent = 'Cooking';
            }
            
            // Update data attribute
            button.setAttribute('data-current-status', newStatus);
            
            // Check if all items are ready, and if so, update the order card
            const orderCard = button.closest('.order-card');
            const allButtons = orderCard.querySelectorAll('[data-current-status]');
            const allItemsReady = Array.from(allButtons).every(btn => 
                btn.getAttribute('data-current-status') === 'ready'
            );
            
            if (allItemsReady) {
                // Update order status badge
                const statusBadge = orderCard.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = 'status-badge status-ready';
                    statusBadge.textContent = 'READY';
                }
                
                // Hide the modify and bump order buttons
                const actionButtons = orderCard.querySelector('.flex.gap-2');
                if (actionButtons) {
                    actionButtons.innerHTML = `
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded text-sm">All Items Ready</span>
                    `;
                }
                
                // Update order card background
                orderCard.className = orderCard.className.replace(/bg-\w+-\d+/g, 'bg-green-100');
                orderCard.className = orderCard.className.replace(/border-\w+-\d+/g, 'border-green-500');
            }
        } else {
            // Restore original state if there was an error
            button.textContent = originalText;
            alert('Error updating item status. Please try again.');
        }
        
        button.disabled = false;
    })
    .catch(error => {
        console.error('Error updating item status:', error);
        button.textContent = originalText;
        button.disabled = false;
        alert('Error updating item status. Please try again.');
    });
};

        /**
 * Tab Persistence Script for Kitchen Display System
 * 
 * This script maintains the active tab selection after page refresh
 * by storing the active tab in localStorage and restoring it on page load.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Cache DOM elements
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    const ordersFilter = document.getElementById('orders-filter');
    
    // Get saved tab from localStorage (default to 'orders' if not found)
    const savedTab = localStorage.getItem('kds_active_tab') || 'orders';
    
    // Function to activate a specific tab
    function activateTab(tabId) {
        // Hide all tabs
        tabContents.forEach(tab => tab.classList.add('hidden'));
        
        // Remove active class from all buttons
        tabButtons.forEach(btn => btn.classList.remove('active-tab', 'bg-blue-600'));
        
        // Show the selected tab
        document.getElementById(tabId + '-tab').classList.remove('hidden');
        
        // Find and activate the corresponding button
        const targetButton = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        if (targetButton) {
            targetButton.classList.add('active-tab', 'bg-blue-600');
        }
        
        // Show/hide orders filter
        if (tabId === 'orders') {
            ordersFilter.style.display = 'block';
        } else {
            ordersFilter.style.display = 'none';
        }
        
        // If analytics tab is active, fetch analytics data
        if (tabId === 'analytics') {
            if (typeof fetchAnalytics === 'function') {
                fetchAnalytics();
            }
        }
        
        // Save active tab to localStorage
        localStorage.setItem('kds_active_tab', tabId);
    }
    
    // Restore the saved tab on page load
    activateTab(savedTab);
    
    // Modify tab button click handlers to store active tab
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            activateTab(tabId);
            
            // Reset auto-refresh if needed
            if (typeof resetAutoRefresh === 'function') {
                resetAutoRefresh();
            }
        });
    });
    
    // Modify the refresh button to preserve current tab
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            // Don't add this if we're using an AJAX refresh
            // If using full page refresh, prevent default and use our custom reload
            e.preventDefault();
            window.location.href = window.location.pathname + '?tab=' + localStorage.getItem('kds_active_tab');
        });
    }
});












        
        // Fetch analytics data for the analytics tab
        function fetchAnalytics() {
            fetch('/kitchen/analytics', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update analytics cards
                document.getElementById('orders-count').textContent = data.ordersToday || 0;
                document.getElementById('orders-trend').textContent = 
                    (data.ordersTrend >= 0 ? '+' : '') + data.ordersTrend + '% from yesterday';
                document.getElementById('orders-trend').className = 
                    data.ordersTrend >= 0 ? 'text-sm text-green-500 mt-2' : 'text-sm text-red-500 mt-2';
                
                document.getElementById('avg-prep-time').textContent = (data.averagePrepTime || 0) + ' min';
                document.getElementById('prep-time-trend').textContent = 
                    (data.prepTimeTrend >= 0 ? '+' : '') + data.prepTimeTrend + ' min from target';
                document.getElementById('prep-time-trend').className = 
                    data.prepTimeTrend <= 0 ? 'text-sm text-green-500 mt-2' : 'text-sm text-red-500 mt-2';
                
                document.getElementById('late-orders').textContent = data.lateOrders || 0;
                document.getElementById('late-orders-trend').textContent = 
                    (data.lateOrdersTrend >= 0 ? '+' : '') + Math.abs(data.lateOrdersTrend) + ' from yesterday';
                document.getElementById('late-orders-trend').className = 
                    data.lateOrdersTrend <= 0 ? 'text-sm text-green-500 mt-2' : 'text-sm text-red-500 mt-2';
            })
            .catch(error => {
                console.error('Error fetching analytics:', error);
            });
        }
        
        // Load analytics on page load if analytics tab is active
        if (document.querySelector('.tab-button[data-tab="analytics"]').classList.contains('active-tab')) {
            fetchAnalytics();
        }
   });
   </script>
</body>
</html>
/**
 * Kitchen Display System - Main JavaScript File
 * Handles real-time updates between cashier and kitchen systems
 */

// Initialize the KDS module
const KDS = {
    // Config
    config: {
        refreshInterval: 30000, // 30 seconds
        apiEndpoints: {
            orders: '/kitchen/orders/filter',
            todayEvents: '/kitchen/events/today',
            tomorrowEvents: '/kitchen/events/tomorrow',
            analytics: '/kitchen/analytics',
            updateOrderStatus: '/kitchen/orders/{id}/status',
            updateItemStatus: '/kitchen/items/{id}/status'
        }
    },
    
    // DOM elements
    elements: {},
    
    // State
    state: {
        activeTab: 'orders',
        activeSource: 'all',
        orders: [],
        todayEvents: [],
        tomorrowEvents: [],
        analytics: {}
    },
    
    // Initialize the KDS
    init: function() {
        this.cacheElements();
        this.bindEvents();
        this.loadInitialData();
        this.startAutoRefresh();
    },
    
    // Cache DOM elements
    cacheElements: function() {
        this.elements = {
            dateElement: document.getElementById('current-date'),
            timeElement: document.getElementById('current-time'),
            tabButtons: document.querySelectorAll('.tab-button'),
            tabContents: document.querySelectorAll('.tab-content'),
            ordersFilter: document.getElementById('orders-filter'),
            sourceFilters: document.querySelectorAll('.source-filter'),
            ordersContainer: document.getElementById('orders-container'),
            todayEventsContainer: document.getElementById('today-events-container'),
            tomorrowEventsContainer: document.getElementById('tomorrow-events-container'),
            
            // Analytics elements
            ordersCount: document.getElementById('orders-count'),
            ordersTrend: document.getElementById('orders-trend'),
            avgPrepTime: document.getElementById('avg-prep-time'),
            prepTimeTrend: document.getElementById('prep-time-trend'),
            lateOrders: document.getElementById('late-orders'),
            lateOrdersTrend: document.getElementById('late-orders-trend')
        };
    },
    
    // Bind events
    bindEvents: function() {
        // Tab switching
        this.elements.tabButtons.forEach(button => {
            button.addEventListener('click', this.handleTabClick.bind(this));
        });
        
        // Source filtering
        this.elements.sourceFilters.forEach(filter => {
            filter.addEventListener('click', this.handleSourceFilterClick.bind(this));
        });
        
        // Order actions - using event delegation
        document.addEventListener('click', this.handleOrderActions.bind(this));
    },
    
    // Handle tab click
    handleTabClick: function(event) {
        const tabId = event.currentTarget.getAttribute('data-tab');
        
        // Update UI
        this.elements.tabContents.forEach(tab => tab.classList.add('hidden'));
        this.elements.tabButtons.forEach(btn => btn.classList.remove('active-tab'));
        
        document.getElementById(tabId + '-tab').classList.remove('hidden');
        event.currentTarget.classList.add('active-tab');
        
        // Show/hide orders filter
        this.elements.ordersFilter.style.display = tabId === 'orders' ? 'block' : 'none';
        
        // Update state
        this.state.activeTab = tabId;
    },
    
    // Handle source filter click
    handleSourceFilterClick: function(event) {
        const source = event.currentTarget.getAttribute('data-source');
        
        // Update UI
        this.elements.sourceFilters.forEach(filter => {
            filter.classList.remove('bg-blue-600', 'text-white');
            filter.classList.add('bg-gray-200', 'text-gray-700');
        });
        
        event.currentTarget.classList.remove('bg-gray-200', 'text-gray-700');
        event.currentTarget.classList.add('bg-blue-600', 'text-white');
        
        // Update state
        this.state.activeSource = source;
        
        // Fetch orders by source
        this.fetchOrdersBySource(source);
    },
    
    // Handle order actions
    handleOrderActions: function(event) {
        // Bump order
        if (event.target.closest('.bump-order-btn')) {
            const button = event.target.closest('.bump-order-btn');
            const orderId = button.getAttribute('data-order-id');
            this.updateOrderStatus(orderId, 'READY');
        }
        
        // Modify order - placeholder
        if (event.target.closest('.modify-order-btn')) {
            const button = event.target.closest('.modify-order-btn');
            const orderId = button.getAttribute('data-order-id');
            console.log(`Modify order ${orderId} - functionality not implemented`);
        }
        
        // Send message - placeholder
        if (event.target.closest('.message-order-btn')) {
            const button = event.target.closest('.message-order-btn');
            const orderId = button.getAttribute('data-order-id');
            console.log(`Send message about order ${orderId} - functionality not implemented`);
        }
    },
    
    // Load initial data
    loadInitialData: function() {
        // Update date and time
        this.updateDateTime();
        
        // Load data
        this.fetchOrdersBySource(this.state.activeSource);
        this.fetchTodayEvents();
        this.fetchTomorrowEvents();
        this.fetchAnalytics();
    },
    
    // Start auto-refresh
    startAutoRefresh: function() {
        // Update date and time every second
        setInterval(this.updateDateTime.bind(this), 1000);
        
        // Refresh active data based on tab
        setInterval(() => {
            switch (this.state.activeTab) {
                case 'orders':
                    this.fetchOrdersBySource(this.state.activeSource);
                    break;
                case 'today':
                    this.fetchTodayEvents();
                    break;
                case 'tomorrow':
                    this.fetchTomorrowEvents();
                    break;
                case 'analytics':
                    this.fetchAnalytics();
                    break;
            }
        }, this.config.refreshInterval);
    },
    
    // Update date and time
    updateDateTime: function() {
        const now = new Date();
        this.elements.dateElement.textContent = now.toLocaleDateString('en-US', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        });
        this.elements.timeElement.textContent = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', minute: '2-digit', second: '2-digit' 
        });
    },
    
    // Fetch orders by source
    fetchOrdersBySource: function(source) {
        fetch(this.config.apiEndpoints.orders + `?source=${source}`)
            .then(response => response.json())
            .then(data => {
                this.state.orders = data;
                this.renderOrders();
            })
            .catch(error => {
                console.error("Error fetching orders:", error);
            });
    },
    
    // Fetch today's events
    fetchTodayEvents: function() {
        fetch(this.config.apiEndpoints.todayEvents)
            .then(response => response.json())
            .then(data => {
                this.state.todayEvents = data;
                this.renderEvents(data, this.elements.todayEventsContainer);
            })
            .catch(error => {
                console.error("Error fetching today's events:", error);
            });
    },
    
    // Fetch tomorrow's events
    fetchTomorrowEvents: function() {
        fetch(this.config.apiEndpoints.tomorrowEvents)
            .then(response => response.json())
            .then(data => {
                this.state.tomorrowEvents = data;
                this.renderEvents(data, this.elements.tomorrowEventsContainer);
            })
            .catch(error => {
                console.error("Error fetching tomorrow's events:", error);
            });
    },
    
    // Fetch analytics data
    fetchAnalytics: function() {
        fetch(this.config.apiEndpoints.analytics)
            .then(response => response.json())
            .then(data => {
                this.state.analytics = data;
                this.renderAnalytics();
            })
            .catch(error => {
                console.error("Error fetching analytics:", error);
            });
    },
    
    // Update order status
    updateOrderStatus: function(orderId, status) {
        const url = this.config.apiEndpoints.updateOrderStatus.replace('{id}', orderId);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh orders
                this.fetchOrdersBySource(this.state.activeSource);
            }
        })
        .catch(error => {
            console.error("Error updating order status:", error);
        });
    },
    
    // Render orders
    renderOrders: function() {
        const orders = this.state.orders;
        this.elements.ordersContainer.innerHTML = '';
        
        if (orders.length === 0) {
            this.elements.ordersContainer.innerHTML = 
                '<div class="col-span-full text-center py-8 text-gray-500">No active orders</div>';
            return;
        }
        
        orders.forEach(order => {
            const orderHTML = this.createOrderCard(order);
            this.elements.ordersContainer.innerHTML += orderHTML;
        });
        
        // Animate progress bars
        setTimeout(() => {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                bar.style.width = bar.getAttribute('data-width') + '%';
            });
        }, 100);
    },
    
    // Create order card HTML
    createOrderCard: function(order) {
        let statusClass;
        switch(order.status) {
            case 'NEW': statusClass = 'bg-red-100 border-red-500'; break;
            case 'COOKING': statusClass = 'bg-yellow-100 border-yellow-500'; break;
            case 'READY': statusClass = 'bg-green-100 border-green-500'; break;
            default: statusClass = 'bg-gray-100 border-gray-500';
        }
        
        let itemsHTML = '';
        order.items.forEach(item => {
            let progressColor;
            let progressWidth;
            
            switch(item.status) {
                case 'ready': 
                    progressColor = 'bg-green-500';
                    progressWidth = 100;
                    break;
                case 'cooking': 
                    progressColor = 'bg-yellow-500';
                    progressWidth = 50;
                    break;
                default: 
                    progressColor = 'bg-gray-400';
                    progressWidth = 0;
            }
            
            itemsHTML += `
            <tr class="border-b border-gray-200 last:border-0" data-item-id="${item.id}">
                <td class="py-2">
                    <div class="font-medium">${item.name}</div>
                    ${item.notes ? `<div class="text-xs text-gray-500">${item.notes}</div>` : ''}
                </td>
                <td class="py-2 text-center">${item.qty}</td>
                <td class="py-2">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full progress-bar ${progressColor}" 
                            data-width="${progressWidth}" style="width: 0%">
                        </div>
                    </div>
                    <div class="text-xs text-center mt-1 capitalize">${item.status}</div>
                </td>
            </tr>`;
        });
        
        return `
        <div class="border rounded-lg shadow-md p-4 order-card ${statusClass}" data-order-id="${order.id}">
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center">
                    <span class="font-bold text-xl mr-3">#${order.order_id}</span>
                    <span class="text-lg">${order.table}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span>${order.time}</span>
                    <span class="status-badge ${order.status === 'NEW' ? 'status-new' : 
                                           order.status === 'COOKING' ? 'status-cooking' : 
                                           'status-ready'}">
                        ${order.status}
                    </span>
                </div>
            </div>
            
            <div class="flex items-center gap-3 mb-3 text-sm text-gray-600">
                <div class="flex items-center">
                    <i class="fas fa-users mr-1"></i>
                    ${order.guests}
                </div>
                <div class="flex items-center">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    ${order.source}
                </div>
                <div class="flex items-center">
                    <i class="fas fa-clock mr-1"></i>
                    Est: ${order.estimated_complete}
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
                    ${itemsHTML}
                </tbody>
            </table>
            
            <div class="flex justify-between">
                <button class="text-blue-600 flex items-center text-sm action-button message-order-btn" data-order-id="${order.id}">
                    <i class="fas fa-comment-alt mr-1"></i>
                    Send Message
                </button>
                <div class="flex gap-2">
                    <button class="bg-yellow-500 text-white px-3 py-1 rounded text-sm action-button modify-order-btn" data-order-id="${order.id}">
                        Modify
                    </button>
                    <button class="bg-green-500 text-white px-3 py-1 rounded text-sm action-button bump-order-btn" data-order-id="${order.id}">
                        Bump Order
                    </button>
                </div>
            </div>
        </div>`;
    },
    
    // Render events
    renderEvents: function(events, container) {
        container.innerHTML = '';
        
        if (events.length === 0) {
            container.innerHTML = 
                '<div class="col-span-full text-center py-8 text-gray-500">No events scheduled</div>';
            return;
        }
        
        events.forEach(event => {
            container.innerHTML += `
            <div class="border rounded-lg shadow-md p-4 bg-white">
                <div class="flex justify-between items-center mb-2">
                    <div class="text-xl font-bold">${event.name}</div>
                    <div class="text-lg">${event.start_time} - ${event.end_time}</div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-users mr-2"></i>
                        <span>${event.guests} guests</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <span>${event.location}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-file-alt mr-2"></i>
                        <span>${event.menu}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="capitalize ${event.status === 'pending' ? 'text-yellow-600' : 'text-green-600'}">
                            ${event.status}
                        </span>
                    </div>
                </div>
                ${event.special_requests ? `
                <div class="special-request">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>
                        <span class="text-sm">${event.special_requests}</span>
                    </div>
                </div>
                ` : ''}
                <div class="flex justify-end gap-2">
                    <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm action-button">
                        View Details
                    </button>
                    <button class="bg-green-500 text-white px-3 py-1 rounded text-sm action-button">
                        Prep Checklist
                    </button>
                </div>
            </div>`;
        });
    },
    
    // Render analytics
    renderAnalytics: function() {
        const analytics = this.state.analytics;
        
        // Update order count
        this.elements.ordersCount.textContent = analytics.ordersToday || 0;
        this.elements.ordersTrend.textContent = 
            (analytics.ordersTrend >= 0 ? '+' : '') + analytics.ordersTrend + '% from yesterday';
        this.elements.ordersTrend.className = 
            analytics.ordersTrend >= 0 ? 'text-sm text-green-500 mt-2' : 'text-sm text-red-500 mt-2';
        
        // Update prep time
        this.elements.avgPrepTime.textContent = (analytics.averagePrepTime || 0) + ' min';
        this.elements.prepTimeTrend.textContent = 
            (analytics.prepTimeTrend >= 0 ? '+' : '') + analytics.prepTimeTrend + ' min from target';
        this.elements.prepTimeTrend.className = 
            analytics.prepTimeTrend <= 0 ? 'text-sm text-green-500 mt-2' : 'text-sm text-red-500 mt-2';
        
        // Update late orders
        this.elements.lateOrders.textContent = analytics.lateOrders || 0;
        this.elements.lateOrdersTrend.textContent = 
            (analytics.lateOrdersTrend >= 0 ? '+' : '') + Math.abs(analytics.lateOrdersTrend) + ' from yesterday';
        this.elements.lateOrdersTrend.className = 
            analytics.lateOrdersTrend <= 0 ? 'text-sm text-green-500 mt-2' : 'text-sm text-red-500 mt-2';
    }
};

// Initialize KDS when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    KDS.init();
});
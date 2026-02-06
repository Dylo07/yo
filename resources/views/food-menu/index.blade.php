@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Food Menu Generator</h5>
                        <div>
                            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Date Selection -->
                    <div class="mb-4">
                        <h5>Select Date</h5>
                        
                        <form action="{{ route('food-menu.index') }}" method="GET" class="mb-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" name="date" id="date" class="form-control" 
                                        value="{{ request('date', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Load Bookings</button>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <a href="{{ route('food-menu.print-daily', ['date' => request('date', date('Y-m-d'))]) }}" 
                                       target="_blank" class="btn btn-info">
                                        <i class="fas fa-print me-1"></i> Print All Menus for This Date
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Bookings for Selected Date -->
                    @if(isset($bookings) && $bookings->count() > 0)
                        <h5>Bookings for {{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->format('F j, Y') }}</h5>
                        
                        <div class="row g-3 mb-4">
                            @foreach($bookings as $booking)
                                <div class="col-md-4">
                                    <div class="card {{ isset($selectedBooking) && $selectedBooking->id == $booking->id ? 'border-primary' : '' }}" 
                                         style="cursor: pointer;"
                                         onclick="window.location.href='{{ route('food-menu.index', ['date' => request('date', date('Y-m-d')), 'booking_id' => $booking->id]) }}'">
                                        <div class="card-header">
                                            <strong>{{ $booking->function_type }}</strong>
                                            <span class="badge {{ $booking->food_menu_exists ? 'bg-success' : 'bg-warning' }} float-end">
                                                {{ $booking->food_menu_exists ? 'Menu Created' : 'No Menu' }}
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div><strong>ID:</strong> {{ $booking->id }}</div>
                                            <div><strong>Guest Count:</strong> {{ $booking->guest_count }}</div>
                                            <div><strong>Rooms:</strong> {{ is_array($booking->room_numbers) ? implode(', ', $booking->room_numbers) : $booking->room_numbers }}</div>
                                            <div><strong>Time:</strong> {{ $booking->start->format('g:i A') }} - {{ $booking->end ? $booking->end->format('g:i A') : 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(isset($bookings))
                        <div class="alert alert-info">
                            No bookings found for {{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->format('F j, Y') }}.
                        </div>
                    @endif
                    
                    <!-- Selected Booking Details -->
                    @if(isset($selectedBooking))
                        <div class="alert alert-info">
                            <h6>Booking Details:</h6>
                            <p><strong>ID:</strong> {{ $selectedBooking->id }}</p>
                            <p><strong>Function Type:</strong> {{ $selectedBooking->function_type }}</p>
                            <p><strong>Guest Count:</strong> {{ $selectedBooking->guest_count }}</p>
                            <p><strong>Rooms:</strong> {{ is_array($selectedBooking->room_numbers) ? implode(', ', $selectedBooking->room_numbers) : $selectedBooking->room_numbers }}</p>
                            <p><strong>Start:</strong> {{ $selectedBooking->start->format('Y-m-d g:i A') }}</p>
                            <p><strong>End:</strong> {{ $selectedBooking->end ? $selectedBooking->end->format('Y-m-d g:i A') : 'N/A' }}</p>
                        </div>
                        
                        <!-- Import from Package Section -->
                        @if(isset($packages) && $packages->count() > 0)
                        <div class="mt-4 mb-4 p-3 bg-light rounded border">
                            <h5><i class="fas fa-file-import me-2"></i>Import Menu from Package</h5>
                            <p class="text-muted small">Select a package to quickly populate the menu fields below.</p>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Select Package</label>
                                    <select id="packageSelect" class="form-select">
                                        <option value="">-- Select a Package --</option>
                                        @foreach($packages->groupBy('category.name') as $categoryName => $categoryPackages)
                                            <optgroup label="{{ $categoryName ?: 'Uncategorized' }}">
                                                @foreach($categoryPackages as $package)
                                                    <option value="{{ $package->id }}" 
                                                            data-menu="{{ json_encode($package->menu_items) }}"
                                                            data-name="{{ $package->name }}">
                                                        {{ $package->name }} - Rs. {{ number_format($package->price, 2) }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Import Mode</label>
                                    <select id="importMode" class="form-select">
                                        <option value="smart">🪄 Smart Import (Auto-distribute)</option>
                                        <option value="single">📥 Import to Single Field</option>
                                    </select>
                                </div>
                                <div class="col-md-2" id="targetFieldContainer" style="display: none;">
                                    <label class="form-label">Target Field</label>
                                    <select id="targetMealField" class="form-select">
                                        <option value="lunch">Lunch</option>
                                        <option value="dinner">Dinner</option>
                                        <option value="breakfast">Breakfast</option>
                                        <option value="bed_tea">Bed Tea</option>
                                        <option value="morning_snack">Morning Snack</option>
                                        <option value="evening_snack">Evening Snack</option>
                                        <option value="bites">Bites</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" id="importPackageBtn" class="btn btn-success w-100">
                                        <i class="fas fa-download me-1"></i> Import
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted" id="importModeHelp">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Smart Import:</strong> Automatically distributes items to Breakfast, Lunch, Dinner, Evening Snack, etc. based on topic names.
                                </small>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Menu Form -->
                        <div class="mt-4">
                            <h5>Food Menu @if($selectedBooking->function_type == 'Wedding')<span class="badge bg-pink">Wedding Menu</span>@endif</h5>
                            <form action="{{ route('food-menu.save') }}" method="POST">
                                @csrf
                                <input type="hidden" name="booking_id" value="{{ $selectedBooking->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                
                                @if($selectedBooking->function_type == 'Wedding')
                                <!-- ========== WEDDING MENU FIELDS ========== -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_welcome_drink" class="form-label">Welcome Drink</label>
                                            <textarea name="wedding_welcome_drink" id="wedding_welcome_drink" class="form-control" rows="2">{{ $menu->wedding_welcome_drink ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_appetizer" class="form-label">Appetizer</label>
                                            <textarea name="wedding_appetizer" id="wedding_appetizer" class="form-control" rows="2">{{ $menu->wedding_appetizer ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_shooters" class="form-label">Shooters</label>
                                            <textarea name="wedding_shooters" id="wedding_shooters" class="form-control" rows="2">{{ $menu->wedding_shooters ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_salad_bar" class="form-label">Salad Bar</label>
                                            <textarea name="wedding_salad_bar" id="wedding_salad_bar" class="form-control" rows="2">{{ $menu->wedding_salad_bar ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_salad_dressing" class="form-label">Salad Dressing</label>
                                            <textarea name="wedding_salad_dressing" id="wedding_salad_dressing" class="form-control" rows="2">{{ $menu->wedding_salad_dressing ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_soup" class="form-label">Soup</label>
                                            <textarea name="wedding_soup" id="wedding_soup" class="form-control" rows="2">{{ $menu->wedding_soup ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_bread_corner" class="form-label">Bread Corner</label>
                                            <textarea name="wedding_bread_corner" id="wedding_bread_corner" class="form-control" rows="2">{{ $menu->wedding_bread_corner ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_rice_noodle" class="form-label">Rice & Noodle</label>
                                            <textarea name="wedding_rice_noodle" id="wedding_rice_noodle" class="form-control" rows="2">{{ $menu->wedding_rice_noodle ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_meat_items" class="form-label">Meat Items</label>
                                            <textarea name="wedding_meat_items" id="wedding_meat_items" class="form-control" rows="2">{{ $menu->wedding_meat_items ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_seafood_items" class="form-label">Seafood Items</label>
                                            <textarea name="wedding_seafood_items" id="wedding_seafood_items" class="form-control" rows="2">{{ $menu->wedding_seafood_items ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_vegetables" class="form-label">Vegetables</label>
                                            <textarea name="wedding_vegetables" id="wedding_vegetables" class="form-control" rows="2">{{ $menu->wedding_vegetables ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_condiments" class="form-label">Condiments</label>
                                            <textarea name="wedding_condiments" id="wedding_condiments" class="form-control" rows="2">{{ $menu->wedding_condiments ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_desserts" class="form-label">Desserts</label>
                                            <textarea name="wedding_desserts" id="wedding_desserts" class="form-control" rows="2">{{ $menu->wedding_desserts ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wedding_beverages" class="form-label">Beverages</label>
                                            <textarea name="wedding_beverages" id="wedding_beverages" class="form-control" rows="2">{{ $menu->wedding_beverages ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                @else
                                <!-- ========== REGULAR MENU FIELDS ========== -->
                                <!-- 1. Welcome Drink -->
                                <div class="mb-3">
                                    <label for="welcome_drink" class="form-label">Welcome Drink</label>
                                    <textarea name="welcome_drink" id="welcome_drink" class="form-control" rows="2">{{ $menu->welcome_drink ?? '' }}</textarea>
                                </div>
                                
                                <!-- 2. Evening Snack -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="evening_snack" class="form-label">Evening Snack</label>
                                            <textarea name="evening_snack" id="evening_snack" class="form-control" rows="2">{{ $menu->evening_snack ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="evening_snack_time" class="form-label">Time</label>
                                            <input type="time" name="evening_snack_time" id="evening_snack_time" class="form-control" 
                                                value="{{ $menu && $menu->evening_snack_time ? $menu->evening_snack_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 3. Dinner -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="dinner" class="form-label">Dinner</label>
                                            <textarea name="dinner" id="dinner" class="form-control" rows="3">{{ $menu->dinner ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="dinner_time" class="form-label">Time</label>
                                            <input type="time" name="dinner_time" id="dinner_time" class="form-control" 
                                                value="{{ $menu && $menu->dinner_time ? $menu->dinner_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 4. Dessert (after Dinner) -->
                                <div class="mb-3">
                                    <label for="dessert_after_dinner" class="form-label">Dessert (after Dinner)</label>
                                    <textarea name="dessert_after_dinner" id="dessert_after_dinner" class="form-control" rows="2">{{ $menu->dessert_after_dinner ?? '' }}</textarea>
                                </div>
                                
                                <!-- 5. Bed Tea -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="bed_tea" class="form-label">Bed Tea</label>
                                            <textarea name="bed_tea" id="bed_tea" class="form-control" rows="2">{{ $menu->bed_tea ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="bed_tea_time" class="form-label">Time</label>
                                            <input type="time" name="bed_tea_time" id="bed_tea_time" class="form-control" 
                                                value="{{ $menu && $menu->bed_tea_time ? $menu->bed_tea_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 6. Breakfast -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="breakfast" class="form-label">Breakfast</label>
                                            <textarea name="breakfast" id="breakfast" class="form-control" rows="3">{{ $menu->breakfast ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="breakfast_time" class="form-label">Time</label>
                                            <input type="time" name="breakfast_time" id="breakfast_time" class="form-control" 
                                                value="{{ $menu && $menu->breakfast_time ? $menu->breakfast_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 7. Dessert (after Breakfast) -->
                                <div class="mb-3">
                                    <label for="dessert_after_breakfast" class="form-label">Dessert (after Breakfast)</label>
                                    <textarea name="dessert_after_breakfast" id="dessert_after_breakfast" class="form-control" rows="2">{{ $menu->dessert_after_breakfast ?? '' }}</textarea>
                                </div>
                                
                                <!-- 8. Lunch -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="lunch" class="form-label">Lunch</label>
                                            <textarea name="lunch" id="lunch" class="form-control" rows="3">{{ $menu->lunch ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="lunch_time" class="form-label">Time</label>
                                            <input type="time" name="lunch_time" id="lunch_time" class="form-control" 
                                                value="{{ $menu && $menu->lunch_time ? $menu->lunch_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 9. Dessert (after Lunch) -->
                                <div class="mb-3">
                                    <label for="dessert_after_lunch" class="form-label">Dessert (after Lunch)</label>
                                    <textarea name="dessert_after_lunch" id="dessert_after_lunch" class="form-control" rows="2">{{ $menu->dessert_after_lunch ?? '' }}</textarea>
                                </div>
                                @endif
                                
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-success">Save Menu</button>
                                    <a href="{{ route('food-menu.print', ['booking' => $selectedBooking->id, 'date' => $date]) }}" 
                                       target="_blank" class="btn btn-info ms-2">Print Menu</a>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const importBtn = document.getElementById('importPackageBtn');
    const packageSelect = document.getElementById('packageSelect');
    const targetMealField = document.getElementById('targetMealField');
    const importMode = document.getElementById('importMode');
    const targetFieldContainer = document.getElementById('targetFieldContainer');
    const importModeHelp = document.getElementById('importModeHelp');
    
    // Toggle target field visibility based on import mode
    if (importMode && targetFieldContainer) {
        importMode.addEventListener('change', function() {
            if (this.value === 'single') {
                targetFieldContainer.style.display = 'block';
                importModeHelp.innerHTML = '<i class="fas fa-info-circle me-1"></i><strong>Single Field:</strong> All menu items will be imported to the selected field.';
            } else {
                targetFieldContainer.style.display = 'none';
                importModeHelp.innerHTML = '<i class="fas fa-info-circle me-1"></i><strong>Smart Import:</strong> Automatically distributes items to Breakfast, Lunch, Dinner, Evening Snack, etc. based on topic names.';
            }
        });
    }
    
    // Check if this is a Wedding booking
    const isWedding = {{ isset($selectedBooking) && $selectedBooking->function_type == 'Wedding' ? 'true' : 'false' }};
    
    // Wedding topic mapping
    const weddingTopicMapping = {
        'welcome drink': 'wedding_welcome_drink',
        'appetizer': 'wedding_appetizer',
        'shooters': 'wedding_shooters',
        'salad bar': 'wedding_salad_bar',
        'salad dressing': 'wedding_salad_dressing',
        'soup': 'wedding_soup',
        'bread corner': 'wedding_bread_corner',
        'rice & noodle': 'wedding_rice_noodle',
        'rice and noodle': 'wedding_rice_noodle',
        'meat items': 'wedding_meat_items',
        'seafood items': 'wedding_seafood_items',
        'vegetables': 'wedding_vegetables',
        'condiments': 'wedding_condiments',
        'desserts': 'wedding_desserts',
        'dessert': 'wedding_desserts',
        'beverages': 'wedding_beverages'
    };
    
    // Regular topic mapping for smart import
    const exactTopicMapping = {
        'welcome drink': 'welcome_drink',
        'evening snack': 'evening_snack',
        'dinner': 'dinner',
        'bed tea': 'bed_tea',
        'breakfast': 'breakfast',
        'lunch': 'lunch'
    };
    
    // Map meal to its dessert field
    const dessertFieldMapping = {
        'dinner': 'dessert_after_dinner',
        'breakfast': 'dessert_after_breakfast',
        'lunch': 'dessert_after_lunch'
    };
    
    function detectMealCategory(topic, previousCategory) {
        const lowerTopic = topic.toLowerCase().trim();
        
        // For Wedding bookings, use wedding mapping
        if (isWedding) {
            for (const [keyword, field] of Object.entries(weddingTopicMapping)) {
                if (lowerTopic === keyword || lowerTopic.startsWith(keyword)) {
                    return { field: field, isDessert: false };
                }
            }
            // Default to wedding_welcome_drink if no match
            return { field: 'wedding_welcome_drink', isDessert: false };
        }
        
        // Check for exact match first (regular bookings)
        for (const [keyword, meal] of Object.entries(exactTopicMapping)) {
            if (lowerTopic === keyword || lowerTopic.startsWith(keyword)) {
                return { field: meal, isDessert: false };
            }
        }
        
        // Handle "Dessert" - map to the correct dessert field based on previous meal
        if (lowerTopic === 'dessert' || lowerTopic.startsWith('dessert')) {
            const dessertField = dessertFieldMapping[previousCategory] || 'dessert_after_lunch';
            return { field: dessertField, isDessert: true };
        }
        
        // Default to lunch if no match found
        return { field: 'lunch', isDessert: false };
    }
    
    function highlightField(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.style.backgroundColor = '#d4edda';
            setTimeout(function() {
                field.style.backgroundColor = '';
            }, 2000);
        }
    }
    
    if (importBtn && packageSelect) {
        importBtn.addEventListener('click', function() {
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            
            if (!selectedOption.value) {
                alert('Please select a package first.');
                return;
            }
            
            const menuData = selectedOption.getAttribute('data-menu');
            const packageName = selectedOption.getAttribute('data-name');
            const mode = importMode ? importMode.value : 'smart';
            
            if (!menuData || menuData === 'null') {
                alert('This package has no menu items.');
                return;
            }
            
            try {
                const menuItems = JSON.parse(menuData);
                
                if (mode === 'smart') {
                    // Smart Import: distribute items based on topic keywords
                    let categorizedItems;
                    
                    if (isWedding) {
                        // Wedding menu fields
                        categorizedItems = {
                            'wedding_welcome_drink': [],
                            'wedding_appetizer': [],
                            'wedding_shooters': [],
                            'wedding_salad_bar': [],
                            'wedding_salad_dressing': [],
                            'wedding_soup': [],
                            'wedding_bread_corner': [],
                            'wedding_rice_noodle': [],
                            'wedding_meat_items': [],
                            'wedding_seafood_items': [],
                            'wedding_vegetables': [],
                            'wedding_condiments': [],
                            'wedding_desserts': [],
                            'wedding_beverages': []
                        };
                    } else {
                        // Regular menu fields
                        categorizedItems = {
                            'welcome_drink': [],
                            'evening_snack': [],
                            'dinner': [],
                            'dessert_after_dinner': [],
                            'bed_tea': [],
                            'breakfast': [],
                            'dessert_after_breakfast': [],
                            'lunch': [],
                            'dessert_after_lunch': []
                        };
                    }
                    
                    if (Array.isArray(menuItems)) {
                        let previousMeal = 'lunch'; // Track previous meal for Dessert handling
                        
                        menuItems.forEach(function(item) {
                            let topic = '';
                            let description = '';
                            
                            if (typeof item === 'object' && item.topic) {
                                topic = item.topic;
                                description = item.description || '';
                            } else if (typeof item === 'string') {
                                topic = item;
                            }
                            
                            const result = detectMealCategory(topic, previousMeal);
                            const itemText = description ? description : topic;
                            
                            // Initialize array if it doesn't exist
                            if (!categorizedItems[result.field]) {
                                categorizedItems[result.field] = [];
                            }
                            categorizedItems[result.field].push(itemText);
                            
                            // Update previous meal (but not for dessert, so desserts chain correctly)
                            if (!result.isDessert) {
                                previousMeal = result.field;
                            }
                        });
                    }
                    
                    // Populate each field
                    let fieldsUpdated = [];
                    for (const [fieldId, items] of Object.entries(categorizedItems)) {
                        if (items.length > 0) {
                            const textarea = document.getElementById(fieldId);
                            if (textarea) {
                                const newContent = items.join('\n');
                                if (textarea.value.trim() !== '') {
                                    textarea.value = textarea.value + '\n\n--- ' + packageName + ' ---\n' + newContent;
                                } else {
                                    textarea.value = newContent;
                                }
                                highlightField(fieldId);
                                fieldsUpdated.push(fieldId.replace('_', ' '));
                            }
                        }
                    }
                    
                    if (fieldsUpdated.length > 0) {
                        alert('Menu imported successfully!\n\nUpdated fields: ' + fieldsUpdated.join(', '));
                    } else {
                        alert('No items were imported. The package may be empty.');
                    }
                    
                } else {
                    // Single Field Import
                    const targetField = targetMealField.value;
                    let menuText = '';
                    
                    if (Array.isArray(menuItems)) {
                        menuItems.forEach(function(item) {
                            if (typeof item === 'object' && item.topic) {
                                menuText += item.topic + ': ' + (item.description || '') + '\n';
                            } else if (typeof item === 'string') {
                                menuText += item + '\n';
                            }
                        });
                    }
                    
                    const targetTextarea = document.getElementById(targetField);
                    if (targetTextarea) {
                        if (targetTextarea.value.trim() !== '') {
                            if (confirm('The ' + targetField.replace('_', ' ') + ' field already has content. Do you want to replace it?\n\nClick OK to replace, Cancel to append.')) {
                                targetTextarea.value = menuText.trim();
                            } else {
                                targetTextarea.value = targetTextarea.value + '\n\n--- ' + packageName + ' ---\n' + menuText.trim();
                            }
                        } else {
                            targetTextarea.value = menuText.trim();
                        }
                        
                        highlightField(targetField);
                        targetTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        targetTextarea.focus();
                    }
                }
            } catch (e) {
                console.error('Error parsing menu data:', e);
                alert('Error importing menu. Please try again.');
            }
        });
    }
});
</script>
@endpush
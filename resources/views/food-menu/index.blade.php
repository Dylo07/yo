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
                            <h5>Food Menu</h5>
                            <form action="{{ route('food-menu.save') }}" method="POST">
                                @csrf
                                <input type="hidden" name="booking_id" value="{{ $selectedBooking->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                
                                <!-- Bed Tea (New) -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="bed_tea" class="form-label">Bed Tea Menu</label>
                                            <textarea name="bed_tea" id="bed_tea" class="form-control" rows="3">{{ $menu->bed_tea ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="bed_tea_time" class="form-label">Bed Tea Time</label>
                                            <input type="time" name="bed_tea_time" id="bed_tea_time" class="form-control" 
                                                value="{{ $menu && $menu->bed_tea_time ? $menu->bed_tea_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Breakfast -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="breakfast" class="form-label">Breakfast Menu</label>
                                            <textarea name="breakfast" id="breakfast" class="form-control" rows="3">{{ $menu->breakfast ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="breakfast_time" class="form-label">Breakfast Time</label>
                                            <input type="time" name="breakfast_time" id="breakfast_time" class="form-control" 
                                                value="{{ $menu && $menu->breakfast_time ? $menu->breakfast_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Morning Snack (New) -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="morning_snack" class="form-label">Morning Snack Menu</label>
                                            <textarea name="morning_snack" id="morning_snack" class="form-control" rows="3">{{ $menu->morning_snack ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="morning_snack_time" class="form-label">Morning Snack Time</label>
                                            <input type="time" name="morning_snack_time" id="morning_snack_time" class="form-control" 
                                                value="{{ $menu && $menu->morning_snack_time ? $menu->morning_snack_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lunch -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="lunch" class="form-label">Lunch Menu</label>
                                            <textarea name="lunch" id="lunch" class="form-control" rows="3">{{ $menu->lunch ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="lunch_time" class="form-label">Lunch Time</label>
                                            <input type="time" name="lunch_time" id="lunch_time" class="form-control" 
                                                value="{{ $menu && $menu->lunch_time ? $menu->lunch_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Evening Snack -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="evening_snack" class="form-label">Evening Snack Menu</label>
                                            <textarea name="evening_snack" id="evening_snack" class="form-control" rows="3">{{ $menu->evening_snack ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="evening_snack_time" class="form-label">Evening Snack Time</label>
                                            <input type="time" name="evening_snack_time" id="evening_snack_time" class="form-control" 
                                                value="{{ $menu && $menu->evening_snack_time ? $menu->evening_snack_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Dinner -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="dinner" class="form-label">Dinner Menu</label>
                                            <textarea name="dinner" id="dinner" class="form-control" rows="3">{{ $menu->dinner ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="dinner_time" class="form-label">Dinner Time</label>
                                            <input type="time" name="dinner_time" id="dinner_time" class="form-control" 
                                                value="{{ $menu && $menu->dinner_time ? $menu->dinner_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                <!-- Bites Menu Section (NEW) -->
        <div class="row mb-4">
            <div class="col-md-9">
                <label for="bites" class="form-label">Bites Menu</label>
                <textarea name="bites" id="bites" class="form-control" rows="3">{{ $menu->bites ?? '' }}</textarea>
            </div>
            <div class="col-md-3">
                <label for="bites_time" class="form-label">Bites Time</label>
                <div class="input-group">
                    <input type="time" name="bites_time" id="bites_time" class="form-control" 
                        value="{{ $menu && $menu->bites_time ? $menu->bites_time->format('H:i') : '' }}">
                    <span class="input-group-text">
                        <i class="fas fa-clock"></i>
                    </span>
                </div>
            </div>
        </div>
                                
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
    
    // Keyword mapping for smart import
    const mealKeywords = {
        'bed_tea': ['bed tea', 'tea'],
        'breakfast': ['breakfast', 'morning meal'],
        'morning_snack': ['morning snack', 'mid morning'],
        'lunch': ['lunch', 'main dish', 'main dishes', 'rice', 'noodle', 'curry', 'chicken', 'fish', 'vegetable', 'vegetables', 'condiment', 'condiments', 'welcome drink', 'salad', 'soup'],
        'evening_snack': ['evening snack', 'evening snacks', 'snack', 'snacks', 'tea time', 'dessert', 'desserts', 'sweet', 'cake'],
        'dinner': ['dinner', 'supper'],
        'bites': ['bites', 'appetizer', 'appetizers', 'starter', 'starters', 'beverage', 'beverages', 'drink', 'drinks']
    };
    
    function detectMealCategory(topic) {
        const lowerTopic = topic.toLowerCase();
        
        // Check each meal category for keyword matches
        for (const [meal, keywords] of Object.entries(mealKeywords)) {
            for (const keyword of keywords) {
                if (lowerTopic.includes(keyword)) {
                    return meal;
                }
            }
        }
        
        // Default to lunch if no match found
        return 'lunch';
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
                    const categorizedItems = {
                        'bed_tea': [],
                        'breakfast': [],
                        'morning_snack': [],
                        'lunch': [],
                        'evening_snack': [],
                        'dinner': [],
                        'bites': []
                    };
                    
                    if (Array.isArray(menuItems)) {
                        menuItems.forEach(function(item) {
                            let topic = '';
                            let description = '';
                            
                            if (typeof item === 'object' && item.topic) {
                                topic = item.topic;
                                description = item.description || '';
                            } else if (typeof item === 'string') {
                                topic = item;
                            }
                            
                            const category = detectMealCategory(topic);
                            const itemText = description ? topic + ': ' + description : topic;
                            categorizedItems[category].push(itemText);
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
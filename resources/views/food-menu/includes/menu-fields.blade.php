{{-- Menu Fields Component --}}
{{-- Parameters: $menu, $dayIndex, $functionType --}}

@if($functionType == 'Wedding')
    {{-- WEDDING MENU FIELDS --}}
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_welcome_drink_{{ $dayIndex }}" class="form-label">Welcome Drink</label>
                <textarea name="wedding_welcome_drink" id="wedding_welcome_drink_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_welcome_drink ?? '' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_appetizer_{{ $dayIndex }}" class="form-label">Appetizer</label>
                <textarea name="wedding_appetizer" id="wedding_appetizer_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_appetizer ?? '' }}</textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_shooters_{{ $dayIndex }}" class="form-label">Shooters</label>
                <textarea name="wedding_shooters" id="wedding_shooters_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_shooters ?? '' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_salad_bar_{{ $dayIndex }}" class="form-label">Salad Bar</label>
                <textarea name="wedding_salad_bar" id="wedding_salad_bar_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_salad_bar ?? '' }}</textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_salad_dressing_{{ $dayIndex }}" class="form-label">Salad Dressing</label>
                <textarea name="wedding_salad_dressing" id="wedding_salad_dressing_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_salad_dressing ?? '' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_soup_{{ $dayIndex }}" class="form-label">Soup</label>
                <textarea name="wedding_soup" id="wedding_soup_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_soup ?? '' }}</textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_bread_corner_{{ $dayIndex }}" class="form-label">Bread Corner</label>
                <textarea name="wedding_bread_corner" id="wedding_bread_corner_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_bread_corner ?? '' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_rice_noodle_{{ $dayIndex }}" class="form-label">Rice & Noodle</label>
                <textarea name="wedding_rice_noodle" id="wedding_rice_noodle_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_rice_noodle ?? '' }}</textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_meat_items_{{ $dayIndex }}" class="form-label">Meat Items</label>
                <textarea name="wedding_meat_items" id="wedding_meat_items_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_meat_items ?? '' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_seafood_items_{{ $dayIndex }}" class="form-label">Seafood Items</label>
                <textarea name="wedding_seafood_items" id="wedding_seafood_items_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_seafood_items ?? '' }}</textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_vegetables_{{ $dayIndex }}" class="form-label">Vegetables</label>
                <textarea name="wedding_vegetables" id="wedding_vegetables_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_vegetables ?? '' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_condiments_{{ $dayIndex }}" class="form-label">Condiments</label>
                <textarea name="wedding_condiments" id="wedding_condiments_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_condiments ?? '' }}</textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_desserts_{{ $dayIndex }}" class="form-label">Desserts</label>
                <textarea name="wedding_desserts" id="wedding_desserts_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_desserts ?? '' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="wedding_beverages_{{ $dayIndex }}" class="form-label">Beverages</label>
                <textarea name="wedding_beverages" id="wedding_beverages_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->wedding_beverages ?? '' }}</textarea>
            </div>
        </div>
    </div>

@else
    {{-- REGULAR MENU FIELDS --}}
    <div class="mb-3">
        <label for="welcome_drink_{{ $dayIndex }}" class="form-label">Welcome Drink</label>
        <textarea name="welcome_drink" id="welcome_drink_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->welcome_drink ?? '' }}</textarea>
    </div>
    
    <div class="mb-3">
        <div class="row">
            <div class="col-md-9">
                <label for="evening_snack_{{ $dayIndex }}" class="form-label">Evening Snack</label>
                <textarea name="evening_snack" id="evening_snack_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->evening_snack ?? '' }}</textarea>
            </div>
            <div class="col-md-3">
                <label for="evening_snack_time_{{ $dayIndex }}" class="form-label">Time</label>
                <input type="time" name="evening_snack_time" id="evening_snack_time_{{ $dayIndex }}" class="form-control" 
                    value="{{ $menu && $menu->evening_snack_time ? $menu->evening_snack_time->format('H:i') : '' }}">
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <div class="row">
            <div class="col-md-9">
                <label for="dinner_{{ $dayIndex }}" class="form-label">Dinner</label>
                <textarea name="dinner" id="dinner_{{ $dayIndex }}" class="form-control" rows="3">{{ $menu->dinner ?? '' }}</textarea>
            </div>
            <div class="col-md-3">
                <label for="dinner_time_{{ $dayIndex }}" class="form-label">Time</label>
                <input type="time" name="dinner_time" id="dinner_time_{{ $dayIndex }}" class="form-control" 
                    value="{{ $menu && $menu->dinner_time ? $menu->dinner_time->format('H:i') : '' }}">
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="dessert_after_dinner_{{ $dayIndex }}" class="form-label">Dessert (after Dinner)</label>
        <textarea name="dessert_after_dinner" id="dessert_after_dinner_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->dessert_after_dinner ?? '' }}</textarea>
    </div>
    
    <div class="mb-3">
        <div class="row">
            <div class="col-md-9">
                <label for="bed_tea_{{ $dayIndex }}" class="form-label">Bed Tea</label>
                <textarea name="bed_tea" id="bed_tea_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->bed_tea ?? '' }}</textarea>
            </div>
            <div class="col-md-3">
                <label for="bed_tea_time_{{ $dayIndex }}" class="form-label">Time</label>
                <input type="time" name="bed_tea_time" id="bed_tea_time_{{ $dayIndex }}" class="form-control" 
                    value="{{ $menu && $menu->bed_tea_time ? $menu->bed_tea_time->format('H:i') : '' }}">
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <div class="row">
            <div class="col-md-9">
                <label for="breakfast_{{ $dayIndex }}" class="form-label">Breakfast</label>
                <textarea name="breakfast" id="breakfast_{{ $dayIndex }}" class="form-control" rows="3">{{ $menu->breakfast ?? '' }}</textarea>
            </div>
            <div class="col-md-3">
                <label for="breakfast_time_{{ $dayIndex }}" class="form-label">Time</label>
                <input type="time" name="breakfast_time" id="breakfast_time_{{ $dayIndex }}" class="form-control" 
                    value="{{ $menu && $menu->breakfast_time ? $menu->breakfast_time->format('H:i') : '' }}">
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="dessert_after_breakfast_{{ $dayIndex }}" class="form-label">Dessert (after Breakfast)</label>
        <textarea name="dessert_after_breakfast" id="dessert_after_breakfast_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->dessert_after_breakfast ?? '' }}</textarea>
    </div>
    
    <div class="mb-3">
        <div class="row">
            <div class="col-md-9">
                <label for="lunch_{{ $dayIndex }}" class="form-label">Lunch</label>
                <textarea name="lunch" id="lunch_{{ $dayIndex }}" class="form-control" rows="3">{{ $menu->lunch ?? '' }}</textarea>
            </div>
            <div class="col-md-3">
                <label for="lunch_time_{{ $dayIndex }}" class="form-label">Time</label>
                <input type="time" name="lunch_time" id="lunch_time_{{ $dayIndex }}" class="form-control" 
                    value="{{ $menu && $menu->lunch_time ? $menu->lunch_time->format('H:i') : '' }}">
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="dessert_after_lunch_{{ $dayIndex }}" class="form-label">Dessert (after Lunch)</label>
        <textarea name="dessert_after_lunch" id="dessert_after_lunch_{{ $dayIndex }}" class="form-control" rows="2">{{ $menu->dessert_after_lunch ?? '' }}</textarea>
    </div>
@endif

<div class="mb-3">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> Save Menu for This Day
    </button>
</div>

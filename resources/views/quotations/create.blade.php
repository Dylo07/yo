@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-black text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Create New Quotation</h5>
                <a href="{{ route('quotations.index') }}" class="btn btn-sm btn-outline-light">
                    Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('quotations.store') }}" method="POST">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Client Name</label>
                            <input type="text" name="client_name" class="form-control @error('client_name') is-invalid @enderror" 
                                   value="{{ old('client_name') }}" required>
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Client Address</label>
                            <textarea name="client_address" class="form-control @error('client_address') is-invalid @enderror" 
                                      rows="3" required>{{ old('client_address') }}</textarea>
                            @error('client_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Quotation Date</label>
                            <input type="date" name="quotation_date" class="form-control @error('quotation_date') is-invalid @enderror" 
                                   value="{{ old('quotation_date', date('Y-m-d')) }}" required>
                            @error('quotation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Schedule Date</label>
                            <input type="date" name="schedule" class="form-control @error('schedule') is-invalid @enderror" 
                                   value="{{ old('schedule') }}" required>
                            @error('schedule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Menu Selection Section -->
                <div class="mb-4">
                    <h6 class="mb-3 d-flex align-items-center">
                        <i class="bi bi-menu-button-wide me-2"></i> Menu Selection
                    </h6>
                    
                    <!-- Welcome Drink -->
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <strong>WELCOME DRINK</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[welcome_drink]" class="form-control" rows="2" placeholder="e.g., Fresh Fruit Juice">{{ old('menu_items.welcome_drink') }}</textarea>
                        </div>
                    </div>

                    <!-- Evening Snack -->
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <strong>EVENING SNACK</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[evening_snack]" class="form-control" rows="3" placeholder="e.g., Tea and Cake Pieces & Sandwiches / Pan Cake / Fish Roll">{{ old('menu_items.evening_snack') }}</textarea>
                        </div>
                    </div>

                    <!-- Dinner -->
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <strong>DINNER</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[dinner]" class="form-control" rows="6" placeholder="e.g., Macaroni / Spaghetti&#10;Hoppers (Live Action) / Kottu (Live Action)&#10;Egg Fried Rice & Egg Fried Noodles&#10;Chicken Kurma / Chicken Devilled / Chicken Masala&#10;Vegetable Chopsuey&#10;Desserts: Melons, Pineapple, Papaya, Ice Cream, Jelly">{{ old('menu_items.dinner') }}</textarea>
                        </div>
                    </div>

                    <!-- Live BBQ Experience (Optional) -->
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning text-dark py-2">
                            <strong>LIVE BBQ EXPERIENCE (Optional)</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[live_bbq]" class="form-control" rows="2" placeholder="e.g., Live BBQ Experience (5KG) with Musical Entertainment (DJ/Calipso) in Campfire Setting">{{ old('menu_items.live_bbq') }}</textarea>
                        </div>
                    </div>

                    <!-- Bed Tea -->
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <strong>BED TEA</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[bed_tea]" class="form-control" rows="2" placeholder="e.g., Tea & Kola Kanda with Hakuru (Hathawariya)">{{ old('menu_items.bed_tea') }}</textarea>
                        </div>
                    </div>

                    <!-- Breakfast -->
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <strong>BREAKFAST</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[breakfast]" class="form-control" rows="5" placeholder="e.g., White Rice & Noodles / Milk Rice&#10;Bread & String Hoppers&#10;Boiled Egg & Fish Curry / Sprats Fried&#10;Pol Sambal / Dried Shrimp Sambal&#10;Dhall Curry / Potato Curry&#10;Desserts: Ambon Banana">{{ old('menu_items.breakfast') }}</textarea>
                        </div>
                    </div>

                    <!-- Morning Snack -->
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <strong>MORNING SNACK</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[morning_snack]" class="form-control" rows="2" placeholder="e.g., Fresh Fruits / Juice / Short Eats">{{ old('menu_items.morning_snack') }}</textarea>
                        </div>
                    </div>

                    <!-- Lunch -->
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <strong>LUNCH</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[lunch]" class="form-control" rows="6" placeholder="e.g., White Rice & Vegetable Rice / Red Rice&#10;Savory Rice with Sultana / Noodles / Biryani&#10;Chicken Curry / Lake Fish Curry&#10;Fried Egg&#10;Dhall Curry / Potato Tempered&#10;Cutlets&#10;Three Vegetable Curry&#10;Mixed Salads&#10;Mallung & Papadam&#10;Desserts: Ice Cream, Melons, Pineapple, Papaya, Watalappan / Jelly">{{ old('menu_items.lunch') }}</textarea>
                        </div>
                    </div>

                    <!-- Desserts (Additional) -->
                    <div class="card border-info mb-3">
                        <div class="card-header bg-info text-white py-2">
                            <strong>DESSERTS (Additional)</strong>
                        </div>
                        <div class="card-body py-2">
                            <textarea name="menu_items[desserts]" class="form-control" rows="2" placeholder="e.g., Ice Cream, Melons, Pineapple, Papaya, Watalappan, Cream Caramel & Jelly">{{ old('menu_items.desserts') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="items-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Price Per Item</th>
                                    <th>Pax</th>
                                    <th>Quantity</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" name="items[0][description]" class="form-control" required>
                                    </td>
                                    <td>
                                    <input type="text" name="items[0][price_per_item]" class="form-control calc-input price-input" 
                                    onchange="calculateAmount(this)">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][pax]" class="form-control calc-input pax-input" 
                                               onchange="calculateAmount(this)">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][quantity]" class="form-control calc-input qty-input" 
                                               onchange="calculateAmount(this)">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][amount]" class="form-control amount-input" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addRow()">Add Item</button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Service Charge</label>
                            <input type="number" name="service_charge" class="form-control" value="5000" 
                                   onchange="calculateTotal()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" name="total_amount" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Comments</h6>
                    <div id="comments-container">
                        <div class="mb-2">
                            <input type="text" name="comments[]" class="form-control" 
                                   value="Cash payment or Online bank Transfer only accepted.">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="comments[]" class="form-control" 
                                   value="Please provide the confirmed guest count to the hotel at least two days in advance.">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="comments[]" class="form-control" 
                                   value="All meals are served buffet-style.">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="comments[]" class="form-control" 
                                   value="All hotel packages include complimentary access to indoor games (Badminton, Carrom, Darts, Chess, etc.) and outdoor games (Cricket, Volleyball, and custom team activities).">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="comments[]" class="form-control" 
                                   value="All rooms are fully air-conditioned for your comfort.">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="comments[]" class="form-control" 
                                   value="Complimentary bottled water is provided in all rooms.">
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addComment()">Add Comment</button>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Create Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addRow() {
    const tbody = document.querySelector('#items-table tbody');
    const rowCount = tbody.children.length;
    const newRow = tbody.children[0].cloneNode(true);
    
    // Update input names
    newRow.querySelectorAll('input').forEach(input => {
        input.value = '';
        input.name = input.name.replace('[0]', `[${rowCount}]`);
    });
    
    tbody.appendChild(newRow);
}

function removeRow(button) {
    const tbody = document.querySelector('#items-table tbody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
    }
}

function calculateAmount(input) {
    const row = input.closest('tr');
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const pax = parseFloat(row.querySelector('.pax-input').value) || 1;
    const qty = parseFloat(row.querySelector('.qty-input').value) || 1;
    
    const amount = price * pax * qty;
    row.querySelector('.amount-input').value = amount.toFixed(2);
    
    calculateTotal();
}

function calculateTotal() {
    const amounts = [...document.querySelectorAll('.amount-input')]
        .map(input => parseFloat(input.value) || 0);
    const serviceCharge = parseFloat(document.querySelector('input[name="service_charge"]').value) || 0;
    
    const total = amounts.reduce((sum, amount) => sum + amount, 0) + serviceCharge;
    document.querySelector('input[name="total_amount"]').value = total.toFixed(2);
}

function addComment() {
    const container = document.getElementById('comments-container');
    const div = document.createElement('div');
    div.className = 'mb-2';
    div.innerHTML = `<input type="text" name="comments[]" class="form-control">`;
    container.appendChild(div);
}
</script>
@endpush

<style>
.bg-black {
    background-color: #000000;
}

.form-label {
    font-weight: 500;
}

.table-light {
    background-color: #f8f9fa;
}
</style>
@endsection
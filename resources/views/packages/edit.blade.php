@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-black text-white p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Package</h5>
            <a href="{{ route('packages.show', $package) }}" class="btn btn-outline-light btn-sm">
                Back to Package Details
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('packages.update', $package) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $package->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Package Name</label>
                        <input type="text" 
                               name="name" 
                               class="form-control" 
                               value="{{ $package->name }}" 
                               required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" 
                                  class="form-control" 
                                  rows="3" 
                                  required>{{ $package->description }}</textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price (Rs.)</label>
                        <input type="number" 
                               name="price" 
                               class="form-control" 
                               step="0.01" 
                               value="{{ $package->price }}" 
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Package Image</label>
                        @if($package->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $package->image) }}" 
                                     alt="Current package image" 
                                     class="img-thumbnail" 
                                     style="height: 100px;">
                            </div>
                        @endif
                        <input type="file" 
                               name="image" 
                               class="form-control" 
                               accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Menu Items (Optional)</label>
                        <div id="editMenuItemsContainer">
                            @if($package->menu_items && is_array($package->menu_items) && count($package->menu_items) > 0)
                                @foreach($package->menu_items as $index => $item)
                                    <div class="menu-item-row mb-2 row">
                                        <div class="col-md-4">
                                            <input type="text" name="menu_topics[]" class="form-control" 
                                                   placeholder="Topic (e.g., Welcome Drink)"
                                                   value="{{ is_array($item) ? ($item['topic'] ?? '') : '' }}">
                                        </div>
                                        <div class="col-md-7">
                                            <input type="text" name="menu_descriptions[]" class="form-control" 
                                                   placeholder="Description (e.g., Orange Juice, Lime Juice)"
                                                   value="{{ is_array($item) ? ($item['description'] ?? '') : $item }}">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm remove-menu-item" style="{{ count($package->menu_items) > 1 ? '' : 'display:none;' }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="menu-item-row mb-2 row">
                                    <div class="col-md-4">
                                        <input type="text" name="menu_topics[]" class="form-control" placeholder="Topic (e.g., Welcome Drink)">
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" name="menu_descriptions[]" class="form-control" placeholder="Description (e.g., Orange Juice, Lime Juice)">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm remove-menu-item" style="display:none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="editAddMenuItemBtn">
                            <i class="fas fa-plus me-1"></i> Add Menu Item
                        </button>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Additional Information (Optional)</label>
                        <textarea name="additional_info" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Enter in key: value format&#10;Example:&#10;Duration: 3 hours&#10;Max Guests: 10">{{ $package->additional_info_as_string }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('packages.show', $package) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image validation
    const imageInput = document.querySelector('input[name="image"]');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                if (this.files[0].size > 2 * 1024 * 1024) { // 2MB
                    alert('Image size should not exceed 2MB');
                    this.value = '';
                    return;
                }
                
                const fileTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!fileTypes.includes(this.files[0].type)) {
                    alert('Please upload an image file (JPG, JPEG, PNG)');
                    this.value = '';
                    return;
                }
            }
        });
    }

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const price = this.querySelector('input[name="price"]').value;
        if (price <= 0) {
            e.preventDefault();
            alert('Price must be greater than 0');
            return false;
        }
    });

    // Dynamic Menu Items functionality for Edit page
    const editMenuItemsContainer = document.getElementById('editMenuItemsContainer');
    const editAddMenuItemBtn = document.getElementById('editAddMenuItemBtn');
    
    if (editAddMenuItemBtn && editMenuItemsContainer) {
        editAddMenuItemBtn.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'menu-item-row mb-2 row';
            newRow.innerHTML = `
                <div class="col-md-4">
                    <input type="text" name="menu_topics[]" class="form-control" placeholder="Topic (e.g., Welcome Drink)">
                </div>
                <div class="col-md-7">
                    <input type="text" name="menu_descriptions[]" class="form-control" placeholder="Description (e.g., Orange Juice, Lime Juice)">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-menu-item">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            editMenuItemsContainer.appendChild(newRow);
            updateEditRemoveButtons();
        });

        editMenuItemsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-menu-item')) {
                e.target.closest('.menu-item-row').remove();
                updateEditRemoveButtons();
            }
        });

        function updateEditRemoveButtons() {
            const rows = editMenuItemsContainer.querySelectorAll('.menu-item-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-menu-item');
                if (removeBtn) {
                    removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
                }
            });
        }
        
        // Initialize remove buttons visibility
        updateEditRemoveButtons();
    }
});
</script>
@endpush
@endsection
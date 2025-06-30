@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">
        <i class="fas fa-utensils text-primary me-2"></i>
        Menu Recipes Management
    </h2>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Menu Items List -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Menu Items</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @foreach($menus as $menu)
                        <div class="menu-item mb-2 p-2 border rounded {{ isset($recipes[$menu->id]) ? 'bg-light' : '' }}" 
                             style="cursor: pointer;" 
                             onclick="selectMenu({{ $menu->id }}, '{{ addslashes($menu->name) }}')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $menu->name }}</strong>
                                    <br><small class="text-muted">
                                        {{ $menu->category ? $menu->category->name : 'No Category' }}
                                    </small>
                                </div>
                                <div>
                                    @if(isset($recipes[$menu->id]))
                                        <span class="badge bg-success">Has Recipe ({{ count($recipes[$menu->id]) }} items)</span>
                                    @else
                                        <span class="badge bg-warning">No Recipe</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recipe Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recipe for: <span id="selectedMenuName">Select a menu item</span></h5>
                        <div id="recipeActions" style="display: none;">
                            <button type="button" class="btn btn-outline-light btn-sm" onclick="addIngredient()">
                                <i class="fas fa-plus me-1"></i> Add Ingredient
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="recipeForm" action="{{ route('recipes.save') }}" method="POST">
                        @csrf
                        <input type="hidden" id="menu_id" name="menu_id">
                        
                        <div id="ingredientsList">
                            <div class="text-center p-4 text-muted">
                                <i class="fas fa-arrow-left me-2"></i>
                                Select a menu item from the left to add ingredients
                            </div>
                        </div>

                        <div id="recipeFormActions" style="display: none;">
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="addIngredient()">
                                    <i class="fas fa-plus me-1"></i> Add Another Ingredient
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Save Recipe
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearRecipe()">
                                    <i class="fas fa-times me-1"></i> Clear All
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Recipe Display -->
            <div class="card mt-3" id="currentRecipeCard" style="display: none;">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Current Recipe Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div id="currentRecipeDisplay"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedMenuId = null;
let ingredientCounter = 0;
const kitchenItems = @json($kitchenItems);

function selectMenu(menuId, menuName) {
    selectedMenuId = menuId;
    document.getElementById('menu_id').value = menuId;
    document.getElementById('selectedMenuName').textContent = menuName;
    document.getElementById('recipeActions').style.display = 'block';
    document.getElementById('recipeFormActions').style.display = 'block';
    
    // Highlight selected menu
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('bg-primary', 'text-white');
    });
    event.target.closest('.menu-item').classList.add('bg-primary', 'text-white');
    
    // Load existing recipe
    loadExistingRecipe(menuId);
}

function loadExistingRecipe(menuId) {
    fetch(`/recipes/get/${menuId}`)
        .then(response => response.json())
        .then(data => {
            const ingredientsList = document.getElementById('ingredientsList');
            ingredientsList.innerHTML = '';
            
            // Reset counter
            ingredientCounter = 0;
            
            if (data.length > 0) {
                // Show existing recipe - add each ingredient
                data.forEach(ingredient => {
                    addIngredientRow(ingredient.item_id, ingredient.required_quantity, ingredient.preparation_notes);
                });
                
                // Show current recipe summary
                showCurrentRecipe(data);
            } else {
                // No recipe exists, add one empty row
                addIngredientRow();
            }
        })
        .catch(error => {
            console.error('Error loading recipe:', error);
            // Add empty row on error
            addIngredientRow();
        });
}

function addIngredient() {
    addIngredientRow();
}

function addIngredientRow(selectedItemId = null, quantity = '', notes = '') {
    ingredientCounter++;
    
    const ingredientsList = document.getElementById('ingredientsList');
    const row = document.createElement('div');
    row.className = 'ingredient-row mb-3 p-3 border rounded bg-light';
    row.setAttribute('data-ingredient-id', ingredientCounter);
    
    row.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label class="form-label fw-bold">Kitchen Item *</label>
                <select class="form-select" name="ingredients[${ingredientCounter}][item_id]" required>
                    <option value="">Select item...</option>
                    ${kitchenItems.map(item => 
                        `<option value="${item.id}" ${selectedItemId == item.id ? 'selected' : ''}>
                            ${item.name} (${item.kitchen_current_stock} ${item.kitchen_unit})
                        </option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Quantity *</label>
                <input type="number" class="form-control" 
                       name="ingredients[${ingredientCounter}][quantity]" 
                       step="0.001" min="0.001" value="${quantity}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Notes</label>
                <input type="text" class="form-control" 
                       name="ingredients[${ingredientCounter}][notes]" 
                       value="${notes}" placeholder="Optional preparation notes">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeIngredient(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                    <span class="badge bg-primary align-self-center">#${ingredientCounter}</span>
                </div>
            </div>
        </div>
    `;
    
    ingredientsList.appendChild(row);
    
    console.log(`Added ingredient row ${ingredientCounter}. Total rows: ${ingredientsList.children.length}`);
}

function removeIngredient(button) {
    const row = button.closest('.ingredient-row');
    const rowId = row.getAttribute('data-ingredient-id');
    
    console.log(`Removing ingredient row ${rowId}`);
    
    row.remove();
    
    // Check if any rows remain, if not add one empty row
    const ingredientsList = document.getElementById('ingredientsList');
    if (ingredientsList.children.length === 0) {
        addIngredientRow();
    }
}

function clearRecipe() {
    if (confirm('Are you sure you want to clear all ingredients?')) {
        const ingredientsList = document.getElementById('ingredientsList');
        ingredientsList.innerHTML = '';
        ingredientCounter = 0;
        addIngredientRow();
        
        // Hide current recipe display
        document.getElementById('currentRecipeCard').style.display = 'none';
    }
}

function showCurrentRecipe(recipe) {
    const currentRecipeCard = document.getElementById('currentRecipeCard');
    const currentRecipeDisplay = document.getElementById('currentRecipeDisplay');
    
    if (recipe.length > 0) {
        let html = '<div class="table-responsive"><table class="table table-sm table-striped">';
        html += '<thead class="table-dark"><tr><th>Ingredient</th><th>Quantity</th><th>Stock</th><th>Status</th></tr></thead><tbody>';
        
        recipe.forEach(ingredient => {
            const status = ingredient.kitchen_current_stock >= ingredient.required_quantity ? 
                '<span class="badge bg-success">Available</span>' : 
                '<span class="badge bg-danger">Low Stock</span>';
            
            html += `
                <tr>
                    <td><strong>${ingredient.item_name}</strong></td>
                    <td>${ingredient.required_quantity} ${ingredient.kitchen_unit}</td>
                    <td>${ingredient.kitchen_current_stock} ${ingredient.kitchen_unit}</td>
                    <td>${status}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        
        // Add recipe stats
        const totalIngredients = recipe.length;
        const availableIngredients = recipe.filter(i => i.kitchen_current_stock >= i.required_quantity).length;
        
        html += `
            <div class="row mt-2">
                <div class="col-md-6">
                    <small class="text-muted">
                        <strong>Total Ingredients:</strong> ${totalIngredients}
                    </small>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">
                        <strong>Available:</strong> ${availableIngredients}/${totalIngredients}
                    </small>
                </div>
            </div>
        `;
        
        currentRecipeDisplay.innerHTML = html;
        currentRecipeCard.style.display = 'block';
    } else {
        currentRecipeCard.style.display = 'none';
    }
}

// Form submission handler with validation
document.addEventListener('DOMContentLoaded', function() {
    const recipeForm = document.getElementById('recipeForm');
    
    if (recipeForm) {
        recipeForm.addEventListener('submit', function(e) {
            // Check if we have at least one ingredient
            const ingredientRows = document.querySelectorAll('.ingredient-row');
            
            if (ingredientRows.length === 0) {
                e.preventDefault();
                alert('Please add at least one ingredient to the recipe.');
                return false;
            }
            
            // Check if all required fields are filled
            let hasEmptyFields = false;
            let emptyFieldCount = 0;
            
            ingredientRows.forEach(row => {
                const select = row.querySelector('select[name*="[item_id]"]');
                const quantity = row.querySelector('input[name*="[quantity]"]');
                
                if (!select.value || !quantity.value || parseFloat(quantity.value) <= 0) {
                    hasEmptyFields = true;
                    emptyFieldCount++;
                }
            });
            
            if (hasEmptyFields) {
                e.preventDefault();
                alert(`Please fill in all required fields (Kitchen Item and Quantity) for all ingredients. ${emptyFieldCount} incomplete row(s) found.`);
                return false;
            }
            
            console.log('Form submitted with', ingredientRows.length, 'ingredients');
        });
    }
    
    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
});
</script>

<style>
.menu-item:hover {
    background-color: #f8f9fa !important;
}

.menu-item.bg-primary:hover {
    background-color: #0b5ed7 !important;
}

.ingredient-row {
    background-color: #f8f9fa;
    border-left: 4px solid #28a745;
}

.ingredient-row:hover {
    background-color: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card {
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.badge {
    font-size: 0.75em;
}

.form-label.fw-bold {
    font-weight: 600;
    color: #495057;
}

#ingredientsList:empty::after {
    content: "No ingredients added yet. Click 'Add Ingredient' to start.";
    display: block;
    text-align: center;
    padding: 2rem;
    color: #6c757d;
    font-style: italic;
}
</style>
@endsection
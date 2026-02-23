@extends('layouts.app')

@section('content')
<div class="container-fluid">
<div class="row">
    @include('management.inc.sidebar')

    <div class="col-md-10">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-bowl-rice text-primary me-2"></i> Menu Management</h4>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="openLogModal()">
                    <i class="fas fa-history me-1"></i> Log History
                </button>
                <a href="/management/menu/create" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> Create Menu
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(Session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            {{ Session()->get('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Search & Filters -->
        <div class="card mb-3">
            <div class="card-body py-2 px-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="menuSearch" class="form-control" placeholder="Search by name..." oninput="filterMenus()">
                            <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('menuSearch').value=''; filterMenus();">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap gap-1" id="catFilters">
                            <button class="btn btn-sm btn-primary cat-btn active" data-cat="all" onclick="filterByCat('all', this)">
                                All <span class="badge bg-light text-primary ms-1">{{ $menus->count() }}</span>
                            </button>
                            @foreach($categories as $category)
                                <button class="btn btn-sm btn-outline-secondary cat-btn" data-cat="{{ $category->id }}" onclick="filterByCat('{{ $category->id }}', this)">
                                    {{ $category->name }} <span class="badge bg-secondary text-white ms-1">{{ $menus->where('category_id', $category->id)->count() }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Count -->
        <div class="mb-2">
            <small class="text-muted">Showing <strong id="visibleCount">{{ $menus->count() }}</strong> of {{ $menus->count() }} menus</small>
        </div>

        <!-- Bulk Action Bar (hidden by default) -->
        <div id="bulkBar" class="card mb-2" style="display:none; position:sticky; top:0; z-index:100;">
            <div class="card-body py-2 px-3 bg-dark text-white d-flex align-items-center gap-3 flex-wrap">
                <span><strong id="selectedCount">0</strong> selected</span>
                <div class="d-flex align-items-center gap-2">
                    <select id="moveCategorySelect" class="form-select form-select-sm" style="width:auto; min-width:160px;">
                        <option value="">Move to category...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-info" onclick="bulkMove()">
                        <i class="fas fa-arrows-alt me-1"></i> Move
                    </button>
                </div>
                @if($isAdmin)
                    <button class="btn btn-sm btn-warning" onclick="bulkLock()">
                        <i class="fas fa-lock me-1"></i> Lock Selected
                    </button>
                    <button class="btn btn-sm btn-success" onclick="bulkUnlock()">
                        <i class="fas fa-lock-open me-1"></i> Unlock Selected
                    </button>
                @endif
                <button class="btn btn-sm btn-danger" onclick="bulkDelete()">
                    <i class="fas fa-trash me-1"></i> Delete Selected
                </button>
                <button class="btn btn-sm btn-outline-light ms-auto" onclick="clearSelection()">
                    <i class="fas fa-times me-1"></i> Clear
                </button>
            </div>
        </div>

        <!-- Menu Table -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0" style="font-size:0.85rem;">
                <thead class="table-dark">
                    <tr>
                        <th style="width:35px;" class="text-center">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" title="Select All">
                        </th>
                        <th style="width:40px;">#</th>
                        <th style="width:50px;">Img</th>
                        <th>Name</th>
                        <th style="width:90px;">Price</th>
                        <th>Category</th>
                        <th>Ingredients</th>
                        <th style="width:70px;" class="text-center">Recipe</th>
                        <th style="width:80px;" class="text-center">S/C Inc.</th>
                        <th style="width:80px;" class="text-center">Lock</th>
                        <th style="width:110px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="menuTableBody">
                    @php $currentCat = null; @endphp
                    @foreach($menus as $menu)
                        @if($menu->category_id !== $currentCat)
                            @php $currentCat = $menu->category_id; @endphp
                            <tr class="cat-separator" data-cat-id="{{ $menu->category_id }}">
                                <td colspan="10" style="background:#e9ecef; font-weight:700; font-size:0.8rem; padding:4px 10px; color:#495057;">
                                    <input type="checkbox" class="cat-select-all me-2" data-cat-id="{{ $menu->category_id }}" onclick="toggleCatSelect(this)" title="Select all in this category">
                                    {{ $menu->category ? $menu->category->name : 'No Category' }}
                                    <span class="badge bg-secondary ms-1">{{ $menus->where('category_id', $menu->category_id)->count() }}</span>
                                </td>
                            </tr>
                        @endif
                        <tr class="menu-row" 
                            data-cat-id="{{ $menu->category_id }}" 
                            data-name="{{ strtolower($menu->name) }}"
                            data-id="{{ $menu->id }}">
                            <td class="text-center">
                                <input type="checkbox" class="menu-check" value="{{ $menu->id }}" data-cat-id="{{ $menu->category_id }}" onclick="updateSelection()">
                            </td>
                            <td class="text-center text-muted" style="font-size:0.75rem;">{{ $menu->id }}</td>
                            <td class="text-center p-1">
                                <img src="{{ asset('menu_images') }}/{{ $menu->image }}" 
                                     alt="{{ $menu->name }}" 
                                     width="40" height="40" 
                                     style="object-fit:cover; border-radius:4px;">
                            </td>
                            <td><strong>{{ $menu->name }}</strong></td>
                            <td class="text-end">Rs {{ number_format($menu->price, 2) }}</td>
                            <td>
                                <span class="badge bg-info text-dark" style="font-size:0.7rem;">
                                    {{ $menu->category ? $menu->category->name : 'N/A' }}
                                </span>
                            </td>
                            <td style="font-size:0.72rem; color:#555; max-width:250px;">
                                @if(isset($recipeIngredients[$menu->id]))
                                    {{ implode(', ', $recipeIngredients[$menu->id]) }}
                                @else
                                    <span class="text-muted fst-italic">No recipe</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($recipeCounts[$menu->id]))
                                    <button class="btn btn-sm btn-success py-0 px-1" style="font-size:0.7rem;" onclick="openRecipeModal({{ $menu->id }}, '{{ addslashes($menu->name) }}')" title="Edit Recipe">
                                        <i class="fas fa-utensils"></i> {{ $recipeCounts[$menu->id] }}
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-1" style="font-size:0.7rem;" onclick="openRecipeModal({{ $menu->id }}, '{{ addslashes($menu->name) }}')" title="Add Recipe">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isAdmin)
                                    <button class="btn btn-sm {{ $menu->fixed_service_charge ? 'btn-primary' : ($menu->service_charge_included ? 'btn-success' : 'btn-outline-secondary') }} py-0 px-1" 
                                            style="font-size:0.7rem;" 
                                            onclick="openServiceChargeModal({{ $menu->id }}, '{{ addslashes($menu->name) }}', {{ $menu->fixed_service_charge ?? 'null' }}, {{ $menu->service_charge_included ? 'true' : 'false' }})" 
                                            title="Manage Service Charge">
                                        @if($menu->fixed_service_charge)
                                            <i class="fas fa-dollar-sign"></i> Rs {{ number_format($menu->fixed_service_charge, 0) }}
                                        @elseif($menu->service_charge_included)
                                            <i class="fas fa-check"></i> Inc.
                                        @else
                                            <i class="fas fa-plus"></i> Add
                                        @endif
                                    </button>
                                @else
                                    @if($menu->fixed_service_charge)
                                        <span class="badge bg-primary" style="font-size:0.65rem;" title="Fixed Service Charge">Rs {{ number_format($menu->fixed_service_charge, 0) }}</span>
                                    @elseif($menu->service_charge_included)
                                        <span class="badge bg-success" style="font-size:0.65rem;" title="Service charge included"><i class="fas fa-check"></i> Inc.</span>
                                    @else
                                        <span class="text-muted" style="font-size:0.65rem;">-</span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isAdmin)
                                    <button class="btn btn-sm {{ $menu->is_locked ? 'btn-danger' : 'btn-outline-secondary' }} py-0 px-2" 
                                            style="font-size:0.7rem;" 
                                            onclick="toggleLock({{ $menu->id }}, '{{ addslashes($menu->name) }}')" 
                                            id="lock-btn-{{ $menu->id }}"
                                            title="{{ $menu->is_locked ? 'Locked - Click to unlock' : 'Unlocked - Click to lock' }}">
                                        <i class="fas fa-{{ $menu->is_locked ? 'lock' : 'lock-open' }}" id="lock-icon-{{ $menu->id }}"></i>
                                    </button>
                                @else
                                    @if($menu->is_locked)
                                        <span class="badge bg-danger" title="Locked by admin"><i class="fas fa-lock"></i> Locked</span>
                                    @else
                                        <span class="text-muted" style="font-size:0.7rem;"><i class="fas fa-lock-open"></i></span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @if($menu->is_locked && !$isAdmin)
                                        <button class="btn btn-outline-secondary" disabled title="Locked - Only admin can edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <a href="/management/menu/{{ $menu->id }}/edit" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <form action="/management/menu/{{ $menu->id }}" method="post" style="display:inline;" 
                                          onsubmit="return confirm('Delete {{ addslashes($menu->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- Recipe Modal -->
<div class="modal fade" id="recipeModal" tabindex="-1" aria-labelledby="recipeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title" id="recipeModalLabel"><i class="fas fa-utensils me-2"></i>Recipe for: <span id="recipeMenuName"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="recipeMenuId">
                <div id="recipeIngredientsList"></div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addRecipeRow()">
                    <i class="fas fa-plus me-1"></i> Add Ingredient
                </button>
            </div>
            <div class="modal-footer py-2">
                <span id="recipeSaveStatus" class="text-muted me-auto" style="font-size:0.8rem;"></span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveRecipe()">
                    <i class="fas fa-save me-1"></i> Save Recipe
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Service Charge Modal -->
<div class="modal fade" id="serviceChargeModal" tabindex="-1" aria-labelledby="serviceChargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title" id="serviceChargeModalLabel"><i class="fas fa-dollar-sign me-2"></i>Service Charge: <span id="scMenuName"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="scMenuId">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Service Charge Type</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="scType" id="scTypeStandard" value="standard" checked onchange="toggleScInputs()">
                        <label class="form-check-label" for="scTypeStandard">
                            Standard Percentage (10%)
                        </label>
                        <div class="form-text text-muted small">Calculated automatically as 10% of the item price.</div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="scType" id="scTypeFixed" value="fixed" onchange="toggleScInputs()">
                        <label class="form-check-label" for="scTypeFixed">
                            Fixed Amount
                        </label>
                    </div>
                </div>

                <div id="fixedAmountGroup" class="mb-3" style="display:none;">
                    <label for="scFixedAmount" class="form-label">Fixed Amount (Rs)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs</span>
                        <input type="number" class="form-control" id="scFixedAmount" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-text text-muted">Enter the exact service charge amount for this item.</div>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="scIncluded">
                        <label class="form-check-label fw-bold" for="scIncluded">Service Charge Included in Price?</label>
                    </div>
                    <div class="form-text text-muted" id="scIncludedHelp">
                        If checked, the system considers the service charge is <strong>already inside</strong> the menu price.<br>
                        If unchecked, the service charge is added <strong>on top</strong> of the menu price.
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveServiceCharge()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Log History Modal -->
<div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white py-2">
                <h5 class="modal-title" id="logModalLabel"><i class="fas fa-history me-2"></i>Menu Activity Log</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-2 bg-light border-bottom d-flex gap-2 align-items-center flex-wrap">
                    <input type="text" id="logSearch" class="form-control form-control-sm" style="width:200px;" placeholder="Search logs..." oninput="filterLogs()">
                    <select id="logActionFilter" class="form-select form-select-sm" style="width:160px;" onchange="filterLogs()">
                        <option value="">All Actions</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                        <option value="recipe_updated">Recipe Updated</option>
                        <option value="bulk_deleted">Bulk Deleted</option>
                        <option value="bulk_moved">Bulk Moved</option>
                    </select>
                    <small class="text-muted ms-auto" id="logCount"></small>
                </div>
                <div class="table-responsive" style="max-height:60vh;">
                    <table class="table table-sm table-striped table-hover mb-0" style="font-size:0.8rem;">
                        <thead class="table-dark" style="position:sticky; top:0;">
                            <tr>
                                <th style="width:140px;">Date/Time</th>
                                <th style="width:100px;">User</th>
                                <th style="width:100px;">Action</th>
                                <th style="width:140px;">Menu</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody id="logTableBody">
                            <tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.menu-row:hover { background-color: #f8f9fa !important; }
.menu-row input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
.cat-select-all { width: 14px; height: 14px; cursor: pointer; }
#selectAll { width: 16px; height: 16px; cursor: pointer; }
.cat-btn.active { font-weight: 600; }
.btn-group-sm .btn { padding: 0.2rem 0.5rem; }
.recipe-ing-row select, .recipe-ing-row input { font-size: 0.8rem; }
</style>
@endpush

@push('scripts')
<script>
let activeCat = 'all';
const csrfToken = '{{ csrf_token() }}';
const kitchenItems = @json($kitchenItems);
const popularItemIds = @json($popularItemIds ?? []);
let recipeRowCounter = 0;

// ===== Filtering =====
function filterByCat(cat, btn) {
    activeCat = cat;
    document.querySelectorAll('.cat-btn').forEach(b => {
        b.classList.remove('active', 'btn-primary');
        if (!b.classList.contains('btn-outline-secondary')) b.classList.add('btn-outline-secondary');
    });
    btn.classList.add('active', 'btn-primary');
    btn.classList.remove('btn-outline-secondary');
    filterMenus();
}

function filterMenus() {
    const search = (document.getElementById('menuSearch').value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.menu-row');
    const separators = document.querySelectorAll('.cat-separator');
    let count = 0;
    const visibleCats = new Set();

    rows.forEach(row => {
        const catId = row.dataset.catId;
        const name = row.dataset.name || '';
        let show = true;

        if (activeCat !== 'all' && catId !== activeCat) show = false;
        if (show && search && !name.includes(search)) show = false;

        row.style.display = show ? '' : 'none';
        if (show) { count++; visibleCats.add(catId); }
    });

    separators.forEach(s => {
        s.style.display = visibleCats.has(s.dataset.catId) ? '' : 'none';
    });

    document.getElementById('visibleCount').textContent = count;
}

// ===== Selection =====
function getSelectedIds() {
    return Array.from(document.querySelectorAll('.menu-check:checked')).map(cb => parseInt(cb.value));
}

function updateSelection() {
    const ids = getSelectedIds();
    const bulkBar = document.getElementById('bulkBar');
    const selectedCount = document.getElementById('selectedCount');

    if (ids.length > 0) {
        bulkBar.style.display = 'block';
        selectedCount.textContent = ids.length;
    } else {
        bulkBar.style.display = 'none';
    }

    // Update select-all checkbox state
    const allChecks = document.querySelectorAll('.menu-check');
    const visibleChecks = Array.from(allChecks).filter(cb => cb.closest('.menu-row').style.display !== 'none');
    const visibleChecked = visibleChecks.filter(cb => cb.checked);
    document.getElementById('selectAll').checked = visibleChecks.length > 0 && visibleChecked.length === visibleChecks.length;
    document.getElementById('selectAll').indeterminate = visibleChecked.length > 0 && visibleChecked.length < visibleChecks.length;

    // Update category-level checkboxes
    document.querySelectorAll('.cat-select-all').forEach(catCb => {
        const catId = catCb.dataset.catId;
        const catChecks = Array.from(document.querySelectorAll(`.menu-check[data-cat-id="${catId}"]`)).filter(cb => cb.closest('.menu-row').style.display !== 'none');
        const catChecked = catChecks.filter(cb => cb.checked);
        catCb.checked = catChecks.length > 0 && catChecked.length === catChecks.length;
        catCb.indeterminate = catChecked.length > 0 && catChecked.length < catChecks.length;
    });

    // Highlight selected rows
    document.querySelectorAll('.menu-row').forEach(row => {
        const cb = row.querySelector('.menu-check');
        if (cb && cb.checked) {
            row.style.backgroundColor = '#d4edda';
        } else {
            row.style.backgroundColor = '';
        }
    });
}

function toggleSelectAll(masterCb) {
    const visibleChecks = Array.from(document.querySelectorAll('.menu-check')).filter(cb => cb.closest('.menu-row').style.display !== 'none');
    visibleChecks.forEach(cb => cb.checked = masterCb.checked);
    updateSelection();
}

function toggleCatSelect(catCb) {
    const catId = catCb.dataset.catId;
    const catChecks = Array.from(document.querySelectorAll(`.menu-check[data-cat-id="${catId}"]`)).filter(cb => cb.closest('.menu-row').style.display !== 'none');
    catChecks.forEach(cb => cb.checked = catCb.checked);
    updateSelection();
}

function clearSelection() {
    document.querySelectorAll('.menu-check').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    document.querySelectorAll('.cat-select-all').forEach(cb => { cb.checked = false; cb.indeterminate = false; });
    updateSelection();
}

// ===== Bulk Actions =====
function bulkDelete() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;

    if (!confirm(`Are you sure you want to DELETE ${ids.length} menu(s)? This cannot be undone.`)) return;

    fetch('{{ route("management.menu.bulk-delete") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ ids: ids })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Request failed: ' + err.message));
}

function bulkMove() {
    const ids = getSelectedIds();
    const categoryId = document.getElementById('moveCategorySelect').value;

    if (ids.length === 0) { alert('No menus selected.'); return; }
    if (!categoryId) { alert('Please select a category to move to.'); return; }

    if (!confirm(`Move ${ids.length} menu(s) to the selected category?`)) return;

    fetch('{{ route("management.menu.bulk-move") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ ids: ids, category_id: parseInt(categoryId) })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Request failed: ' + err.message));
}

function bulkLock() {
    const ids = getSelectedIds();
    if (ids.length === 0) { alert('No menus selected.'); return; }

    if (!confirm(`Lock ${ids.length} menu(s)? Staff won't be able to edit them.`)) return;

    fetch('{{ route("management.menu.bulk-lock") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ ids: ids })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Request failed: ' + err.message));
}

function bulkUnlock() {
    const ids = getSelectedIds();
    if (ids.length === 0) { alert('No menus selected.'); return; }

    if (!confirm(`Unlock ${ids.length} menu(s)? Staff will be able to edit them.`)) return;

    fetch('{{ route("management.menu.bulk-unlock") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ ids: ids })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Request failed: ' + err.message));
}

// ===== Recipe Modal =====
function buildRecipeItemOptions(selectedItemId) {
    let html = '';
    const popularIds = Object.keys(popularItemIds).map(Number);
    if (popularIds.length > 0) {
        const popularItems = kitchenItems.filter(i => popularIds.includes(i.id));
        popularItems.sort((a, b) => (popularItemIds[b.id] || 0) - (popularItemIds[a.id] || 0));
        if (popularItems.length > 0) {
            html += '<optgroup label="--- Popular (most used) ---">';
            popularItems.forEach(item => {
                const used = popularItemIds[item.id] || 0;
                html += `<option value="${item.id}" ${selectedItemId == item.id ? 'selected' : ''}>${item.name} (${item.kitchen_current_stock} ${item.kitchen_unit}) [${used} menus]</option>`;
            });
            html += '</optgroup>';
        }
    }
    html += '<optgroup label="--- All Items ---">';
    kitchenItems.forEach(item => {
        html += `<option value="${item.id}" ${selectedItemId == item.id ? 'selected' : ''}>${item.name} (${item.kitchen_current_stock} ${item.kitchen_unit})</option>`;
    });
    html += '</optgroup>';
    return html;
}

function addRecipeRow(itemId = null, qty = '', notes = '') {
    recipeRowCounter++;
    const list = document.getElementById('recipeIngredientsList');
    const row = document.createElement('div');
    row.className = 'recipe-ing-row mb-2 p-2 border rounded bg-light';
    row.innerHTML = `
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label mb-0" style="font-size:0.75rem; font-weight:600;">Kitchen Item *</label>
                <select class="form-select form-select-sm" name="r_item_id" required>
                    <option value="">Select item...</option>
                    ${buildRecipeItemOptions(itemId)}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-0" style="font-size:0.75rem; font-weight:600;">Qty *</label>
                <input type="number" class="form-control form-control-sm" name="r_qty" step="0.001" min="0.001" value="${qty}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label mb-0" style="font-size:0.75rem; font-weight:600;">Notes</label>
                <input type="text" class="form-control form-control-sm" name="r_notes" value="${notes || ''}" placeholder="Optional">
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.recipe-ing-row').remove()" title="Remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    list.appendChild(row);
}

function updateMenuRecipeDisplay(menuId, ingredients) {
    // Find the table row for this menu
    const menuRow = document.querySelector(`tr.menu-row[data-id="${menuId}"]`);
    if (!menuRow) return;
    
    // Get ingredient names from kitchenItems global array
    const ingredientNames = ingredients.map(ing => {
        const item = kitchenItems.find(ki => ki.id == ing.item_id);
        return item ? item.name : 'Unknown';
    }).join(', ');
    
    // Update the ingredients column (6th td)
    const ingredientCell = menuRow.querySelectorAll('td')[5];
    if (ingredientCell) {
        ingredientCell.innerHTML = `<span style="font-size:0.72rem; color:#555;">${ingredientNames}</span>`;
    }
    
    // Update the recipe button (7th td)
    const recipeButtonCell = menuRow.querySelectorAll('td')[6];
    if (recipeButtonCell) {
        const menuName = menuRow.querySelector('td:nth-child(4) strong').textContent;
        recipeButtonCell.innerHTML = `
            <button class="btn btn-sm btn-success py-0 px-1" style="font-size:0.7rem;" onclick="openRecipeModal(${menuId}, '${menuName.replace(/'/g, "\\'")}')" title="Edit Recipe">
                <i class="fas fa-utensils"></i> ${ingredients.length}
            </button>
        `;
    }
}

function openRecipeModal(menuId, menuName) {
    document.getElementById('recipeMenuId').value = menuId;
    document.getElementById('recipeMenuName').textContent = menuName;
    document.getElementById('recipeIngredientsList').innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading recipe...</div>';
    document.getElementById('recipeSaveStatus').textContent = '';
    recipeRowCounter = 0;

    const modal = new bootstrap.Modal(document.getElementById('recipeModal'));
    modal.show();

    fetch(`/recipes/get/${menuId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('recipeIngredientsList').innerHTML = '';
            if (data.length > 0) {
                data.forEach(ing => addRecipeRow(ing.item_id, ing.required_quantity, ing.preparation_notes));
            } else {
                addRecipeRow();
            }
        })
        .catch(err => {
            document.getElementById('recipeIngredientsList').innerHTML = '<div class="text-danger">Failed to load recipe.</div>';
        });
}

function saveRecipe() {
    const menuId = document.getElementById('recipeMenuId').value;
    const rows = document.querySelectorAll('.recipe-ing-row');
    const ingredients = [];
    let valid = true;

    rows.forEach((row, i) => {
        const itemId = row.querySelector('[name="r_item_id"]').value;
        const qty = row.querySelector('[name="r_qty"]').value;
        const notes = row.querySelector('[name="r_notes"]').value;
        if (!itemId || !qty || parseFloat(qty) <= 0) { valid = false; return; }
        ingredients.push({ item_id: itemId, quantity: qty, notes: notes });
    });

    if (!valid || ingredients.length === 0) {
        alert('Please fill in all required fields (Item and Quantity) for every ingredient.');
        return;
    }

    document.getElementById('recipeSaveStatus').textContent = 'Saving...';

    fetch('/recipes/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ menu_id: menuId, ingredients: ingredients })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('recipeSaveStatus').innerHTML = '<span class="text-success"><i class="fas fa-check me-1"></i>' + data.message + '</span>';
            
            // Update UI without page reload
            setTimeout(() => {
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('recipeModal'));
                if (modal) modal.hide();
                
                // Update the table row for this menu
                updateMenuRecipeDisplay(menuId, ingredients);
            }, 600);
        } else {
            document.getElementById('recipeSaveStatus').innerHTML = '<span class="text-danger">' + (data.message || 'Error saving recipe.') + '</span>';
        }
    })
    .catch(err => {
        document.getElementById('recipeSaveStatus').innerHTML = '<span class="text-danger">Request failed: ' + err.message + '</span>';
    });
}

// ===== Log History =====
let allLogs = [];

function openLogModal() {
    const modal = new bootstrap.Modal(document.getElementById('logModal'));
    modal.show();
    document.getElementById('logTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';
    document.getElementById('logSearch').value = '';
    document.getElementById('logActionFilter').value = '';

    fetch('{{ route("management.menu.logs") }}')
        .then(res => res.json())
        .then(data => {
            allLogs = data;
            renderLogs(data);
        })
        .catch(err => {
            document.getElementById('logTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Failed to load logs.</td></tr>';
        });
}

function getActionBadge(action) {
    const map = {
        'created': '<span class="badge bg-success">Created</span>',
        'updated': '<span class="badge bg-warning text-dark">Updated</span>',
        'deleted': '<span class="badge bg-danger">Deleted</span>',
        'recipe_updated': '<span class="badge bg-info text-dark">Recipe</span>',
        'bulk_deleted': '<span class="badge bg-danger">Bulk Delete</span>',
        'bulk_moved': '<span class="badge bg-primary">Bulk Move</span>'
    };
    return map[action] || `<span class="badge bg-secondary">${action}</span>`;
}

function formatLogDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function renderLogs(logs) {
    const tbody = document.getElementById('logTableBody');
    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No logs found.</td></tr>';
        document.getElementById('logCount').textContent = '0 entries';
        return;
    }
    let html = '';
    logs.forEach(log => {
        html += `<tr>
            <td class="text-nowrap">${formatLogDate(log.created_at)}</td>
            <td><strong>${log.user_name || 'System'}</strong></td>
            <td>${getActionBadge(log.action)}</td>
            <td>${log.menu_name || '-'}</td>
            <td style="font-size:0.75rem; color:#555;">${log.details || '-'}</td>
        </tr>`;
    });
    tbody.innerHTML = html;
    document.getElementById('logCount').textContent = logs.length + ' entries';
}

function filterLogs() {
    const search = (document.getElementById('logSearch').value || '').toLowerCase().trim();
    const action = document.getElementById('logActionFilter').value;
    const filtered = allLogs.filter(log => {
        if (action && log.action !== action) return false;
        if (search) {
            const text = ((log.menu_name || '') + ' ' + (log.details || '') + ' ' + (log.user_name || '')).toLowerCase();
            if (!text.includes(search)) return false;
        }
        return true;
    });
    renderLogs(filtered);
}

// Toggle service charge included status (Admin only)
function toggleServiceCharge(menuId, isChecked) {
    $.ajax({
        url: `/management/menu/${menuId}/toggle-service-charge`,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            service_charge_included: isChecked
        },
        success: function(response) {
            if (response.success) {
                console.log(response.message);
            }
        },
        error: function(xhr) {
            alert('Failed to update service charge status');
            // Revert checkbox on error
            $('#sc-switch-' + menuId).prop('checked', !isChecked);
        }
    });
}

// Toggle lock/unlock for menu items (Admin only)
function toggleLock(menuId, menuName) {
    if (confirm(`Are you sure you want to toggle lock status for "${menuName}"?`)) {
        $.ajax({
            url: `/management/menu/${menuId}/toggle-lock`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update button appearance
                    const btn = $('#lock-btn-' + menuId);
                    const icon = $('#lock-icon-' + menuId);
                    
                    if (response.is_locked) {
                        btn.removeClass('btn-outline-secondary').addClass('btn-danger');
                        btn.attr('title', 'Locked - Click to unlock');
                        icon.removeClass('fa-lock-open').addClass('fa-lock');
                    } else {
                        btn.removeClass('btn-danger').addClass('btn-outline-secondary');
                        btn.attr('title', 'Unlocked - Click to lock');
                        icon.removeClass('fa-lock').addClass('fa-lock-open');
                    }
                    
                    // Show success message
                    alert(response.message);
                    
                    // Reload page to update edit button status
                    location.reload();
                } else {
                    alert(response.message || 'Failed to toggle lock status');
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Failed to toggle lock status'));
            }
        });
    }
}
// Service Charge Functions
function openServiceChargeModal(id, name, fixedAmount, isIncluded) {
    $('#scMenuId').val(id);
    $('#scMenuName').text(name);
    
    // Reset form
    $('#scIncluded').prop('checked', isIncluded);
    
    if(fixedAmount !== null) {
        $('#scTypeFixed').prop('checked', true);
        $('#scFixedAmount').val(fixedAmount);
        $('#fixedAmountGroup').show();
    } else {
        $('#scTypeStandard').prop('checked', true);
        $('#scFixedAmount').val('');
        $('#fixedAmountGroup').hide();
    }
    
    var modal = new bootstrap.Modal(document.getElementById('serviceChargeModal'));
    modal.show();
}

function toggleScInputs() {
    if($('#scTypeFixed').is(':checked')) {
        $('#fixedAmountGroup').slideDown();
        // Force "Included" to be checked and disabled for Fixed Amount
        $('#scIncluded').prop('checked', true).prop('disabled', true);
        $('#scIncludedHelp').html('<strong>Note:</strong> Fixed service charges are always treated as <em>internal allocations</em> (included) and are never added to the customer\'s bill.');
    } else {
        $('#fixedAmountGroup').slideUp();
        // Restore "Included" control
        $('#scIncluded').prop('disabled', false);
        $('#scIncludedHelp').html('If checked, the system considers the service charge is <strong>already inside</strong> the menu price.<br>If unchecked, the service charge is added <strong>on top</strong> of the menu price.');
    }
}

function saveServiceCharge() {
    const id = $('#scMenuId').val();
    const isFixed = $('#scTypeFixed').is(':checked');
    const fixedAmount = isFixed ? $('#scFixedAmount').val() : null;
    // If fixed, it's always included. Otherwise check the box.
    const isIncluded = isFixed ? true : $('#scIncluded').is(':checked');

    if(isFixed && (fixedAmount === '' || fixedAmount < 0)) {
        alert('Please enter a valid fixed amount');
        return;
    }

    $.ajax({
        url: `/management/menu/${id}/update-service-charge`,
        method: 'POST',
        data: {
            _token: csrfToken,
            fixed_service_charge: fixedAmount,
            service_charge_included: isIncluded ? 1 : 0
        },
        success: function(response) {
            if(response.success) {
                location.reload(); // Reload to update the button state
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Failed to update service charge settings');
            console.error(xhr.responseText);
        }
    });
}
</script>
@endpush
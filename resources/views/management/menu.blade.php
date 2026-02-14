@extends('layouts.app')

@section('content')
<div class="container-fluid">
<div class="row">
    @include('management.inc.sidebar')

    <div class="col-md-10">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-bowl-rice text-primary me-2"></i> Menu Management</h4>
            <a href="/management/menu/create" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i> Create Menu
            </a>
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
                        <th>Description</th>
                        <th style="width:110px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="menuTableBody">
                    @php $currentCat = null; @endphp
                    @foreach($menus as $menu)
                        @if($menu->category_id !== $currentCat)
                            @php $currentCat = $menu->category_id; @endphp
                            <tr class="cat-separator" data-cat-id="{{ $menu->category_id }}">
                                <td colspan="8" style="background:#e9ecef; font-weight:700; font-size:0.8rem; padding:4px 10px; color:#495057;">
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
                            <td style="font-size:0.8rem; color:#666; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ $menu->description ?? '-' }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="/management/menu/{{ $menu->id }}/edit" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
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

<script>
let activeCat = 'all';
const csrfToken = '{{ csrf_token() }}';

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
</script>

<style>
.menu-row:hover { background-color: #f8f9fa !important; }
.menu-row input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
.cat-select-all { width: 14px; height: 14px; cursor: pointer; }
#selectAll { width: 16px; height: 16px; cursor: pointer; }
.cat-btn.active { font-weight: 600; }
.btn-group-sm .btn { padding: 0.2rem 0.5rem; }
</style>
@endsection
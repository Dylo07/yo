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

        <!-- Menu Table -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0" style="font-size:0.85rem;">
                <thead class="table-dark">
                    <tr>
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
                                <td colspan="7" style="background:#e9ecef; font-weight:700; font-size:0.8rem; padding:4px 10px; color:#495057;">
                                    {{ $menu->category ? $menu->category->name : 'No Category' }}
                                    <span class="badge bg-secondary ms-1">{{ $menus->where('category_id', $menu->category_id)->count() }}</span>
                                </td>
                            </tr>
                        @endif
                        <tr class="menu-row" 
                            data-cat-id="{{ $menu->category_id }}" 
                            data-name="{{ strtolower($menu->name) }}">
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
</script>

<style>
.menu-row:hover { background-color: #f8f9fa !important; }
.cat-btn.active { font-weight: 600; }
.btn-group-sm .btn { padding: 0.2rem 0.5rem; }
</style>
@endsection
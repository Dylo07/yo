@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Main Functions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kitchen.daily-stock') }}">Kitchen</a></li>
            <li class="breadcrumb-item active">Daily Stock Settings</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-cog text-primary mr-2"></i>
                Daily Stock Sheet Settings
            </h2>
            <p class="text-muted mb-0">Select which kitchen items to track on the daily stock sheet</p>
        </div>
        <div>
            <a href="{{ route('kitchen.daily-stock') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Stock Sheet
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($items->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            No kitchen items found. Please add kitchen items in the Kitchen Inventory section first.
        </div>
    @else
    <form action="{{ route('kitchen.daily-stock.settings.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                <i class="fas fa-check-double mr-1"></i> Select All
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                <i class="fas fa-times mr-1"></i> Deselect All
            </button>
            <span class="ml-3 text-muted" id="selectedCount">0 items selected</span>
        </div>

        @foreach($items as $groupName => $groupItems)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-folder mr-1"></i> {{ $groupName }}
                    <span class="badge badge-secondary ml-1">{{ $groupItems->count() }}</span>
                </h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary group-select-all" data-group="{{ $groupName }}">
                        Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary group-deselect-all" data-group="{{ $groupName }}">
                        Deselect All
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($groupItems as $item)
                    <div class="col-md-4 col-lg-3 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   class="custom-control-input item-checkbox"
                                   id="item_{{ $item->id }}"
                                   name="tracked_items[]"
                                   value="{{ $item->id }}"
                                   data-group="{{ $groupName }}"
                                   {{ in_array($item->id, $trackedIds) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="item_{{ $item->id }}">
                                {{ $item->name }}
                                @if($item->kitchen_unit)
                                    <small class="text-muted">({{ $item->kitchen_unit }})</small>
                                @endif
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save mr-1"></i> Save Settings
                </button>
                <a href="{{ route('kitchen.daily-stock') }}" class="btn btn-outline-secondary btn-lg ml-2">
                    Cancel
                </a>
            </div>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    function updateCount() {
        var count = $('.item-checkbox:checked').length;
        $('#selectedCount').text(count + ' items selected');
    }

    updateCount();

    $('.item-checkbox').on('change', function() {
        updateCount();
    });

    $('#selectAllBtn').on('click', function() {
        $('.item-checkbox').prop('checked', true);
        updateCount();
    });

    $('#deselectAllBtn').on('click', function() {
        $('.item-checkbox').prop('checked', false);
        updateCount();
    });

    $('.group-select-all').on('click', function() {
        var group = $(this).data('group');
        $('.item-checkbox[data-group="' + group + '"]').prop('checked', true);
        updateCount();
    });

    $('.group-deselect-all').on('click', function() {
        var group = $(this).data('group');
        $('.item-checkbox[data-group="' + group + '"]').prop('checked', false);
        updateCount();
    });
});
</script>
@endpush
@endsection

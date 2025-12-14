@extends('layouts.app')

@section('styles')
<style>
    .create-task-container {
        max-width: 700px;
        margin: 0 auto;
        padding: 20px;
    }
    .create-task-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 30px;
    }
    .create-task-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    .create-task-header h1 {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        color: #1a1a1a;
    }
    .form-section {
        margin-bottom: 24px;
    }
    .form-section-title {
        font-size: 14px;
        font-weight: 600;
        color: #6b6b6b;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-group label {
        font-weight: 500;
        color: #333;
        margin-bottom: 6px;
        display: block;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 10px 14px;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }
    .btn-create {
        background: #28a745;
        border: none;
        padding: 12px 24px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .btn-create:hover {
        background: #218838;
        transform: translateY(-1px);
    }
    .btn-back {
        background: #f5f3f0;
        border: 1px solid #e8e6e3;
        color: #333;
        padding: 12px 24px;
        font-weight: 500;
        border-radius: 8px;
    }
    .btn-back:hover {
        background: #e8e6e3;
    }
    .staff-select-group {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 16px;
        margin-top: 8px;
    }
    @media (max-width: 576px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="create-task-container">
    <div class="create-task-card">
        <div class="create-task-header">
            <h1><i class="fas fa-tasks text-muted mr-2"></i> Create New Task</h1>
            <a href="{{ route('tasks.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf
            
            <!-- Task Details Section -->
            <div class="form-section">
                <div class="form-section-title">Task Details</div>
                
                <div class="form-group">
                    <label for="task"><i class="fas fa-clipboard-list text-muted mr-1"></i> Task Description</label>
                    <textarea class="form-control" name="task" id="task" rows="3" placeholder="Enter task description..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="task_category_id"><i class="fas fa-folder text-muted mr-1"></i> Task Category</label>
                        <select class="form-control" name="task_category_id" id="task_category_id" required>
                            <option value="">Select category...</option>
                            @foreach($taskCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priority_order"><i class="fas fa-flag text-muted mr-1"></i> Priority</label>
                        <select class="form-control" name="priority_order" id="priority_order" required>
                            <option value="Low">🟢 Low</option>
                            <option value="Medium" selected>🟡 Medium</option>
                            <option value="High">🔴 High</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Assignment Section -->
            <div class="form-section">
                <div class="form-section-title">Assignment</div>
                
                <div class="staff-select-group">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="staff_category"><i class="fas fa-building text-muted mr-1"></i> Department</label>
                            <select class="form-control" name="staff_category" id="staff_category" onchange="filterStaffByCategory()">
                                <option value="">All Departments</option>
                                @foreach($categoryTypes as $category)
                                    <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="assigned_to"><i class="fas fa-user text-muted mr-1"></i> Assign To Staff</label>
                            <select class="form-control" name="assigned_to" id="assigned_to">
                                <option value="">Select staff member...</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" data-category="{{ $staff->staffCategory->category ?? '' }}">
                                        {{ $staff->name }} 
                                        @if($staff->staffCategory)
                                            ({{ ucfirst(str_replace('_', ' ', $staff->staffCategory->category)) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Select a department to filter staff, or choose directly from the staff list.
                    </small>
                </div>
            </div>

            <!-- Dates Section -->
            <div class="form-section">
                <div class="form-section-title">Schedule</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date"><i class="fas fa-calendar-plus text-muted mr-1"></i> Start Date</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="end_date"><i class="fas fa-calendar-check text-muted mr-1"></i> Due Date</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="user" value="{{ Auth::user()->name ?? 'Admin' }}">
            <input type="hidden" name="date_added" value="{{ date('Y-m-d') }}">

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                <a href="{{ route('tasks.index') }}" class="btn btn-back">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success btn-create">
                    <i class="fas fa-plus"></i> Create Task
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function filterStaffByCategory() {
        const categorySelect = document.getElementById('staff_category');
        const staffSelect = document.getElementById('assigned_to');
        const selectedCategory = categorySelect.value;
        
        // Show/hide options based on category
        Array.from(staffSelect.options).forEach(option => {
            if (option.value === '') {
                option.style.display = ''; // Always show placeholder
                return;
            }
            
            const staffCategory = option.dataset.category;
            if (!selectedCategory || staffCategory === selectedCategory) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset selection if current selection is hidden
        const currentOption = staffSelect.options[staffSelect.selectedIndex];
        if (currentOption && currentOption.style.display === 'none') {
            staffSelect.value = '';
        }
    }

    // Auto-select department when staff is selected
    document.getElementById('assigned_to').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const staffCategory = selectedOption.dataset.category;
        if (staffCategory) {
            document.getElementById('staff_category').value = staffCategory;
        }
    });
</script>
@endsection

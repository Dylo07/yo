@extends('layouts.app')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --bg-primary: #f8faf8;
        --bg-secondary: #ffffff;
        --bg-tertiary: #f0f5f0;
        --text-primary: #1a1a1a;
        --text-secondary: #6b6b6b;
        --text-muted: #9a9a9a;
        --accent: #28a745;
        --accent-light: #e8f5e9;
        --accent-hover: #218838;
        --border: #e3e8e3;
        --border-light: #eef3ee;
        --success: #28a745;
        --warning: #e5a63d;
        --danger: #d64545;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
        --radius-sm: 6px;
        --radius-md: 10px;
        --radius-lg: 14px;
    }

    .task-manager {
        font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--bg-primary);
        min-height: 100vh;
        margin: -20px;
        padding: 0;
    }

    .task-layout {
        display: flex;
        min-height: 100vh;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Sidebar */
    .task-sidebar {
        width: 280px;
        background: var(--bg-secondary);
        border-right: 1px solid var(--border);
        padding: 20px 0;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
    }

    .sidebar-header {
        padding: 0 20px 20px;
        border-bottom: 1px solid var(--border-light);
        margin-bottom: 16px;
    }

    .sidebar-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .sidebar-subtitle {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .add-task-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        margin: 0 12px 16px;
        background: var(--accent);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        width: calc(100% - 24px);
        justify-content: center;
    }

    .add-task-btn:hover {
        background: var(--accent-hover);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .nav-section {
        margin-bottom: 24px;
        padding: 0 12px;
    }

    .nav-section-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0 8px;
        margin-bottom: 8px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.15s ease;
        margin-bottom: 2px;
        color: var(--text-primary);
        text-decoration: none;
    }

    .nav-item:hover {
        background: var(--bg-tertiary);
        text-decoration: none;
        color: var(--text-primary);
    }

    .nav-item.active {
        background: var(--accent-light);
        color: var(--accent);
    }

    .nav-item i {
        width: 18px;
        opacity: 0.7;
    }

    .nav-item.active i {
        opacity: 1;
    }

    .nav-item span {
        font-size: 14px;
        font-weight: 500;
        flex: 1;
    }

    .nav-badge {
        background: var(--accent);
        color: white;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 10px;
    }

    .nav-badge.danger {
        background: var(--danger);
    }

    /* Main Content */
    .task-main {
        flex: 1;
        padding: 24px 32px;
        padding-right: 80px;
        overflow-y: auto;
        max-width: 1000px;
        margin: 0 auto;
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .panel-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .panel-stats {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .stat-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: var(--bg-secondary);
        border: 1px solid var(--border);
        border-radius: 20px;
        font-size: 13px;
        color: var(--text-secondary);
    }

    .stat-badge .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .stat-badge .dot.pending { background: var(--warning); }
    .stat-badge .dot.completed { background: var(--success); }
    .stat-badge .dot.overdue { background: var(--danger); }

    /* Department Sections */
    .department-section {
        background: var(--bg-secondary);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        margin-bottom: 16px;
        overflow: hidden;
    }

    .department-header {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .department-header:hover {
        background: var(--bg-tertiary);
    }

    .department-toggle {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        transition: transform 0.2s ease;
    }

    .department-toggle.collapsed {
        transform: rotate(-90deg);
    }

    .department-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        font-size: 18px;
        color: white;
    }

    .department-info {
        flex: 1;
    }

    .department-name {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .department-meta {
        font-size: 12px;
        color: var(--text-muted);
    }

    .department-count {
        padding: 4px 12px;
        background: var(--bg-tertiary);
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .department-content {
        border-top: 1px solid var(--border-light);
        display: none;
    }

    .department-content.expanded {
        display: block;
    }

    /* Staff Members */
    .staff-member {
        border-bottom: 1px solid var(--border-light);
    }

    .staff-member:last-child {
        border-bottom: none;
    }

    .staff-header {
        display: flex;
        align-items: center;
        padding: 12px 20px 12px 52px;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .staff-header:hover {
        background: var(--bg-tertiary);
    }

    .staff-toggle {
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        transition: transform 0.2s ease;
    }

    .staff-toggle.collapsed {
        transform: rotate(-90deg);
    }

    .staff-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e8e6e3 0%, #d4d2cf 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .staff-name {
        font-size: 14px;
        font-weight: 500;
        flex: 1;
        color: var(--text-primary);
    }

    .staff-task-count {
        font-size: 12px;
        color: var(--text-muted);
        margin-right: 8px;
    }

    .staff-content {
        display: none;
        padding-left: 94px;
        padding-right: 20px;
        padding-bottom: 12px;
    }

    .staff-content.expanded {
        display: block;
    }

    /* Tasks */
    .task-item {
        display: flex;
        align-items: flex-start;
        padding: 10px 12px;
        margin-bottom: 6px;
        background: var(--bg-tertiary);
        border-radius: var(--radius-sm);
        transition: all 0.15s ease;
    }

    .task-item:hover {
        background: var(--border-light);
    }

    .task-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid var(--border);
        border-radius: 50%;
        margin-right: 12px;
        margin-top: 2px;
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .task-checkbox:hover {
        border-color: var(--accent);
    }

    .task-checkbox.completed {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .task-info {
        flex: 1;
        min-width: 0;
    }

    .task-title {
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 4px;
        color: var(--text-primary);
    }

    .task-title.completed {
        text-decoration: line-through;
        color: var(--text-muted);
    }

    .task-dates {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 11px;
        color: var(--text-muted);
    }

    .task-date {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .task-date.overdue {
        color: var(--danger);
    }

    .task-priority {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-left: auto;
        flex-shrink: 0;
    }

    .task-priority.High { background: var(--danger); }
    .task-priority.Medium { background: var(--warning); }
    .task-priority.Low { background: var(--success); }

    .task-category-badge {
        font-size: 10px;
        padding: 2px 6px;
        background: var(--bg-secondary);
        border-radius: 4px;
        color: var(--text-muted);
    }

    /* Department Colors */
    .dept-front_office { background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%); }
    .dept-kitchen { background: linear-gradient(135deg, #fc8181 0%, #f56565 100%); }
    .dept-restaurant { background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); }
    .dept-maintenance { background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%); }
    .dept-garden { background: linear-gradient(135deg, #68d391 0%, #48bb78 100%); }
    .dept-housekeeping { background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%); }
    .dept-pool { background: linear-gradient(135deg, #63b3ed 0%, #4299e1 100%); }
    .dept-laundry { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }

    /* Category Icons */
    .category-icon {
        font-size: 16px;
    }

    /* Animations */
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .department-content.expanded,
    .staff-content.expanded {
        animation: slideDown 0.2s ease;
    }

    /* Quick Add Task */
    .quick-add-form {
        margin-top: 8px;
    }

    .quick-add-wrapper {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .quick-add-input {
        flex: 1;
        padding: 8px 12px;
        border: 1px dashed var(--border);
        border-radius: var(--radius-sm);
        font-size: 13px;
        background: var(--bg-secondary);
        transition: all 0.15s ease;
    }

    .quick-add-input:focus {
        outline: none;
        border-color: var(--accent);
        border-style: solid;
        background: white;
    }

    .quick-add-input::placeholder {
        color: var(--text-muted);
    }

    .quick-add-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: var(--accent);
        color: white;
        border-radius: var(--radius-sm);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }

    .quick-add-btn:hover {
        background: var(--accent-hover);
        transform: scale(1.05);
    }

    /* Department Quick Add */
    .dept-quick-add {
        padding: 12px 20px;
        background: var(--accent-light);
        border-bottom: 1px solid var(--border-light);
    }

    .dept-quick-add-wrapper {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .dept-quick-icon {
        color: var(--accent);
        font-size: 16px;
    }

    .dept-quick-add-input {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 13px;
        background: white;
        transition: all 0.15s ease;
    }

    .dept-quick-add-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }

    .dept-quick-add-btn {
        padding: 10px 16px;
        border: none;
        background: var(--accent);
        color: white;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }

    .dept-quick-add-btn:hover {
        background: var(--accent-hover);
    }

    /* Common Tasks Section */
    .common-tasks-section {
        border-bottom: 1px solid var(--border-light);
        padding-bottom: 12px;
        margin-bottom: 8px;
    }

    .common-tasks-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 600;
        color: var(--accent);
        background: var(--accent-light);
        border-radius: var(--radius-sm);
        margin: 0 16px 8px 16px;
    }

    .common-tasks-header i {
        font-size: 14px;
    }

    .common-tasks-list {
        padding: 0 16px;
    }

    .common-tasks-list .task-item {
        background: #fffef5;
        border-left: 3px solid var(--accent);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .task-sidebar {
            width: 220px;
        }
    }

    @media (max-width: 768px) {
        .task-layout {
            flex-direction: column;
        }
        .task-sidebar {
            width: 100%;
            height: auto;
            position: relative;
            border-right: none;
            border-bottom: 1px solid var(--border);
        }
        .task-main {
            padding: 16px;
        }
    }

    /* View Toggle */
    .view-toggle {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
    }

    .view-btn {
        padding: 8px 16px;
        border: 1px solid var(--border);
        background: var(--bg-secondary);
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .view-btn:hover {
        background: var(--bg-tertiary);
    }

    .view-btn.active {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    /* Legacy Table View */
    .table-view {
        display: none;
    }

    .table-view.active {
        display: block;
    }

    .modern-view {
        display: none;
    }

    .modern-view.active {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="task-manager">
    <div class="task-layout">
        <!-- Sidebar -->
        <aside class="task-sidebar">
            <div class="sidebar-header">
                <h1 class="sidebar-title">Soba Lanka</h1>
                <span class="sidebar-subtitle">Task Manager</span>
            </div>

            <a href="{{ route('tasks.create') }}" class="add-task-btn">
                <i class="fas fa-plus"></i> Add Task
            </a>

            <div class="nav-section">
                <div class="nav-item active" data-filter="all">
                    <i class="fas fa-inbox"></i>
                    <span>All Tasks</span>
                    <div class="nav-badge">{{ $stats['pending'] }}</div>
                </div>
                <div class="nav-item" data-filter="today">
                    <i class="fas fa-calendar-day"></i>
                    <span>Today</span>
                    @if($stats['today'] > 0)
                        <div class="nav-badge">{{ $stats['today'] }}</div>
                    @endif
                </div>
                <div class="nav-item" data-filter="overdue">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Overdue</span>
                    @if($stats['overdue'] > 0)
                        <div class="nav-badge danger">{{ $stats['overdue'] }}</div>
                    @endif
                </div>
                <div class="nav-item" data-filter="completed">
                    <i class="fas fa-check-circle"></i>
                    <span>Completed</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Departments</div>
                @foreach($categoryTypes as $category)
                    @php
                        $categoryIcons = [
                            'front_office' => '👔',
                            'kitchen' => '👨‍🍳',
                            'restaurant' => '🍽️',
                            'maintenance' => '🔧',
                            'garden' => '🌿',
                            'housekeeping' => '🛏️',
                            'pool' => '🏊',
                            'laundry' => '🧺',
                            'welding' => '⚙️',
                            'carpentry' => '🪚',
                            'groundskeeper' => '🧹',
                            'gardener' => '🌱',
                            'mile' => '🏠',
                            'poola' => '🏊‍♂️',
                            'so' => '🛡️',
                        ];
                        $icon = $categoryIcons[$category->slug] ?? '📋';
                    @endphp
                    <div class="nav-item" data-category="{{ $category->slug }}">
                        <span class="category-icon">{{ $icon }}</span>
                        <span>{{ $category->name }}</span>
                    </div>
                @endforeach
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Quick Links</div>
                <a href="{{ route('task-categories.create') }}" class="nav-item">
                    <i class="fas fa-folder-plus"></i>
                    <span>Add Category</span>
                </a>
                <a href="{{ route('attendance.manual.index') }}" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Staff Attendance</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="task-main">
            <div class="panel-header">
                <h2 class="panel-title">Staff Tasks</h2>
                <div class="panel-stats">
                    <div class="stat-badge">
                        <span class="dot pending"></span>
                        {{ $stats['pending'] }} Pending
                    </div>
                    <div class="stat-badge">
                        <span class="dot completed"></span>
                        {{ $stats['completed'] }} Completed
                    </div>
                    @if($stats['overdue'] > 0)
                        <div class="stat-badge">
                            <span class="dot overdue"></span>
                            {{ $stats['overdue'] }} Overdue
                        </div>
                    @endif
                </div>
            </div>

            <!-- View Toggle -->
            <div class="view-toggle">
                <button class="view-btn active" data-view="modern">
                    <i class="fas fa-th-large"></i> Department View
                </button>
                <button class="view-btn" data-view="table">
                    <i class="fas fa-table"></i> Table View
                </button>
            </div>

            <!-- Modern Department View -->
            <div class="modern-view active" id="modernView">
                @foreach($categoryTypes as $category)
                    @php
                        $categoryIcons = [
                            'front_office' => '👔',
                            'kitchen' => '👨‍🍳',
                            'restaurant' => '🍽️',
                            'maintenance' => '🔧',
                            'garden' => '🌿',
                            'housekeeping' => '🛏️',
                            'pool' => '🏊',
                            'laundry' => '🧺',
                            'welding' => '⚙️',
                            'carpentry' => '🪚',
                            'groundskeeper' => '🧹',
                            'gardener' => '🌱',
                            'mile' => '🏠',
                            'poola' => '🏊‍♂️',
                            'so' => '🛡️',
                        ];
                        $icon = $categoryIcons[$category->slug] ?? '📋';
                        $staffInCategory = $staffByCategory[$category->slug] ?? collect();
                        $categoryTasks = $pendingTasks->where('staff_category', $category->slug);
                    @endphp
                    
                    @if($staffInCategory->count() > 0)
                    <div class="department-section" data-category="{{ $category->slug }}">
                        <div class="department-header" onclick="toggleDepartment('{{ $category->slug }}')">
                            <div class="department-toggle" id="toggle-{{ $category->slug }}">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="department-icon dept-{{ $category->slug }}">{{ $icon }}</div>
                            <div class="department-info">
                                <div class="department-name">{{ $category->name }}</div>
                                <div class="department-meta">{{ $staffInCategory->count() }} staff members</div>
                            </div>
                            <div class="department-count">{{ $categoryTasks->count() }} tasks</div>
                        </div>
                        <div class="department-content" id="content-{{ $category->slug }}">
                            <!-- Department Quick Add Task (Common Work) -->
                            <div class="dept-quick-add">
                                <form action="{{ route('tasks.store') }}" method="POST" class="dept-quick-add-form">
                                    @csrf
                                    <input type="hidden" name="user" value="{{ Auth::user()->name ?? 'Admin' }}">
                                    <input type="hidden" name="date_added" value="{{ date('Y-m-d') }}">
                                    <input type="hidden" name="staff_category" value="{{ $category->slug }}">
                                    <input type="hidden" name="task_category_id" value="1">
                                    <input type="hidden" name="priority_order" value="Medium">
                                    <div class="dept-quick-add-wrapper">
                                        <i class="fas fa-users-cog dept-quick-icon"></i>
                                        <input type="text" name="task" class="dept-quick-add-input" placeholder="Add common task for {{ $category->name }} department..." required>
                                        <button type="submit" class="dept-quick-add-btn">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Common Tasks (No specific person assigned) -->
                            @php
                                $commonTasks = $pendingTasks->where('staff_category', $category->slug)->whereNull('assigned_to');
                            @endphp
                            @if($commonTasks->count() > 0)
                            <div class="common-tasks-section">
                                <div class="common-tasks-header">
                                    <i class="fas fa-users"></i>
                                    <span>Common Tasks ({{ $commonTasks->count() }})</span>
                                </div>
                                <div class="common-tasks-list">
                                    @foreach($commonTasks as $task)
                                        <div class="task-item">
                                            <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="is_done" value="1">
                                                <button type="submit" class="task-checkbox" title="Mark as done">
                                                    <i class="fas fa-check" style="display: none;"></i>
                                                </button>
                                            </form>
                                            <div class="task-info">
                                                <div class="task-title">{{ $task->task }}</div>
                                                <div class="task-dates">
                                                    @if($task->end_date)
                                                        <span class="task-date {{ $task->isOverdue() ? 'overdue' : '' }}">
                                                            <i class="fas fa-calendar"></i>
                                                            {{ \Carbon\Carbon::parse($task->end_date)->format('M d') }}
                                                        </span>
                                                    @endif
                                                    @if($task->taskCategory)
                                                        <span class="task-category-badge">{{ $task->taskCategory->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="task-priority {{ $task->priority_order }}"></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            @foreach($staffInCategory as $staff)
                                @php
                                    $staffTasks = $pendingTasks->where('assigned_to', $staff->id);
                                    $initials = collect(explode(' ', $staff->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                                @endphp
                                <div class="staff-member">
                                    <div class="staff-header" onclick="toggleStaff({{ $staff->id }})">
                                        <div class="staff-toggle" id="staff-toggle-{{ $staff->id }}">
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                        <div class="staff-avatar">{{ $initials }}</div>
                                        <div class="staff-name">{{ $staff->name }}</div>
                                        <div class="staff-task-count">{{ $staffTasks->count() }} tasks</div>
                                    </div>
                                    <div class="staff-content" id="staff-content-{{ $staff->id }}">
                                        @foreach($staffTasks as $task)
                                            <div class="task-item">
                                                <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="is_done" value="1">
                                                    <button type="submit" class="task-checkbox" title="Mark as done">
                                                        <i class="fas fa-check" style="display: none;"></i>
                                                    </button>
                                                </form>
                                                <div class="task-info">
                                                    <div class="task-title">{{ $task->task }}</div>
                                                    <div class="task-dates">
                                                        @if($task->end_date)
                                                            <span class="task-date {{ $task->isOverdue() ? 'overdue' : '' }}">
                                                                <i class="fas fa-calendar"></i>
                                                                {{ \Carbon\Carbon::parse($task->end_date)->format('M d') }}
                                                                @if($task->isOverdue())
                                                                    (Overdue)
                                                                @endif
                                                            </span>
                                                        @endif
                                                        @if($task->taskCategory)
                                                            <span class="task-category-badge">{{ $task->taskCategory->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="task-priority {{ $task->priority_order }}"></div>
                                            </div>
                                        @endforeach
                                        
                                        <!-- Quick Add Task -->
                                        <form action="{{ route('tasks.store') }}" method="POST" class="quick-add-form">
                                            @csrf
                                            <input type="hidden" name="user" value="{{ Auth::user()->name ?? 'Admin' }}">
                                            <input type="hidden" name="date_added" value="{{ date('Y-m-d') }}">
                                            <input type="hidden" name="assigned_to" value="{{ $staff->id }}">
                                            <input type="hidden" name="task_category_id" value="1">
                                            <input type="hidden" name="priority_order" value="Medium">
                                            <div class="quick-add-wrapper">
                                                <input type="text" name="task" class="quick-add-input" placeholder="Add a task for {{ $staff->name }}..." required>
                                                <button type="submit" class="quick-add-btn">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach

                <!-- Unassigned Tasks -->
                @php
                    $unassignedTasks = $pendingTasks->whereNull('assigned_to');
                @endphp
                @if($unassignedTasks->count() > 0)
                <div class="department-section">
                    <div class="department-header" onclick="toggleDepartment('unassigned')">
                        <div class="department-toggle" id="toggle-unassigned">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="department-icon" style="background: linear-gradient(135deg, #718096 0%, #4a5568 100%);">📋</div>
                        <div class="department-info">
                            <div class="department-name">Unassigned Tasks</div>
                            <div class="department-meta">Tasks without staff assignment</div>
                        </div>
                        <div class="department-count">{{ $unassignedTasks->count() }} tasks</div>
                    </div>
                    <div class="department-content" id="content-unassigned">
                        <div style="padding: 12px 20px 12px 52px;">
                            @foreach($unassignedTasks as $task)
                                <div class="task-item">
                                    <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="is_done" value="1">
                                        <button type="submit" class="task-checkbox" title="Mark as done">
                                            <i class="fas fa-check" style="display: none;"></i>
                                        </button>
                                    </form>
                                    <div class="task-info">
                                        <div class="task-title">{{ $task->task }}</div>
                                        <div class="task-dates">
                                            <span class="task-date">
                                                <i class="fas fa-user"></i>
                                                {{ $task->person_incharge ?? 'Not assigned' }}
                                            </span>
                                            @if($task->taskCategory)
                                                <span class="task-category-badge">{{ $task->taskCategory->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="task-priority {{ $task->priority_order }}"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if($pendingTasks->count() == 0)
                    <div class="empty-state">
                        <i class="fas fa-clipboard-check"></i>
                        <h4>No pending tasks</h4>
                        <p>All tasks have been completed. Add a new task to get started.</p>
                    </div>
                @endif
            </div>

            <!-- Table View (Legacy) -->
            <div class="table-view" id="tableView">
                <h3 class="mb-3">Pending Tasks</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Task</th>
                                <th>Category</th>
                                <th>Assigned To</th>
                                <th>Due Date</th>
                                <th>Priority</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingTasks as $task)
                            <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                                <td>{{ $task->id }}</td>
                                <td style="font-weight: 500;">{{ $task->task }}</td>
                                <td>{{ $task->taskCategory->name ?? '-' }}</td>
                                <td>{{ $task->assignedPerson->name ?? $task->person_incharge ?? '-' }}</td>
                                <td>
                                    @if($task->end_date)
                                        {{ \Carbon\Carbon::parse($task->end_date)->format('M d, Y') }}
                                        @if($task->isOverdue())
                                            <span class="badge badge-danger">Overdue</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($task->priority_order == 'High')
                                        <span class="badge badge-danger">High</span>
                                    @elseif($task->priority_order == 'Medium')
                                        <span class="badge badge-warning">Medium</span>
                                    @else
                                        <span class="badge badge-success">Low</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="is_done" value="1">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Done
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No pending tasks</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h3 class="mt-4 mb-3">Completed Tasks</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Task</th>
                                <th>Category</th>
                                <th>Completed By</th>
                                <th>Completed On</th>
                                <th>Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedTasks as $task)
                            <tr>
                                <td>{{ $task->id }}</td>
                                <td style="text-decoration: line-through; color: #6c757d;">{{ $task->task }}</td>
                                <td>{{ $task->taskCategory->name ?? '-' }}</td>
                                <td>{{ $task->assignedPerson->name ?? $task->person_incharge ?? '-' }}</td>
                                <td>{{ $task->updated_at->format('M d, Y') }}</td>
                                <td>
                                    @if($task->priority_order == 'High')
                                        <span class="badge badge-danger">High</span>
                                    @elseif($task->priority_order == 'Medium')
                                        <span class="badge badge-warning">Medium</span>
                                    @else
                                        <span class="badge badge-success">Low</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No completed tasks</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Toggle department sections
    function toggleDepartment(slug) {
        const content = document.getElementById('content-' + slug);
        const toggle = document.getElementById('toggle-' + slug);
        
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            toggle.classList.add('collapsed');
        } else {
            content.classList.add('expanded');
            toggle.classList.remove('collapsed');
        }
    }

    // Toggle staff sections
    function toggleStaff(staffId) {
        const content = document.getElementById('staff-content-' + staffId);
        const toggle = document.getElementById('staff-toggle-' + staffId);
        
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            toggle.classList.add('collapsed');
        } else {
            content.classList.add('expanded');
            toggle.classList.remove('collapsed');
        }
    }

    // View toggle
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.dataset.view;
            if (view === 'modern') {
                document.getElementById('modernView').classList.add('active');
                document.getElementById('tableView').classList.remove('active');
            } else {
                document.getElementById('modernView').classList.remove('active');
                document.getElementById('tableView').classList.add('active');
            }
        });
    });

    // Sidebar navigation
    document.querySelectorAll('.nav-item[data-filter]').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            // Filter logic can be added here
        });
    });

    document.querySelectorAll('.nav-item[data-category]').forEach(item => {
        item.addEventListener('click', function() {
            const category = this.dataset.category;
            const section = document.querySelector(`.department-section[data-category="${category}"]`);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Expand the section
                const content = document.getElementById('content-' + category);
                const toggle = document.getElementById('toggle-' + category);
                content.classList.add('expanded');
                toggle.classList.remove('collapsed');
            }
        });
    });

    // All departments start collapsed by default (no auto-expand)
</script>
@endsection

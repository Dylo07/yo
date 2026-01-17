@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-tasks text-primary"></i> Assign Daily Tasks</h2>
            <p class="text-muted mb-0">Assign tasks to staff members for {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('duty.roster.index') }}?date={{ $date }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Roster
            </a>
            <input type="date" id="taskDate" class="form-control" value="{{ $date }}" onchange="changeDate(this.value)">
        </div>
    </div>

    <div class="row">
        <!-- Location Assignments -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Location Assignments</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @if($allocations->isEmpty())
                        <p class="text-muted text-center py-4">No staff assigned to locations for this date.</p>
                    @else
                        @foreach($allocations->groupBy('section_name') as $sectionName => $sectionStaff)
                            <div class="mb-3">
                                <h6 class="text-primary fw-bold">{{ $sectionName }}</h6>
                                @foreach($sectionStaff as $allocation)
                                    @if($allocation->person)
                                        <div class="staff-task-item border rounded p-3 mb-2 bg-light">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <strong>{{ $allocation->person->name }}</strong>
                                            </div>
                                            <div class="task-list" id="tasks-{{ $allocation->person_id }}">
                                                <p class="text-muted small mb-2">Loading tasks...</p>
                                            </div>
                                            <button class="btn btn-sm btn-success" onclick="addTask({{ $allocation->person_id }}, '{{ $allocation->person->name }}')">
                                                <i class="fas fa-plus"></i> Add Task
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Function Assignments -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Function/Event Assignments</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @if($functionAssignments->isEmpty())
                        <p class="text-muted text-center py-4">No staff assigned to functions for this date.</p>
                    @else
                        @foreach($functionAssignments as $bookingId => $assignments)
                            <div class="mb-3">
                                <h6 class="text-success fw-bold">Booking #{{ $bookingId }}</h6>
                                @foreach($assignments as $assignment)
                                    @if($assignment->person)
                                        <div class="staff-task-item border rounded p-3 mb-2 bg-light">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <strong>{{ $assignment->person->name }}</strong>
                                            </div>
                                            <div class="task-list" id="tasks-{{ $assignment->person_id }}">
                                                <p class="text-muted small mb-2">Loading tasks...</p>
                                            </div>
                                            <button class="btn btn-sm btn-success" onclick="addTask({{ $assignment->person_id }}, '{{ $assignment->person->name }}')">
                                                <i class="fas fa-plus"></i> Add Task
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Task for <span id="modalStaffName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Task Description</label>
                    <textarea class="form-control" id="taskDescription" rows="3" placeholder="Enter task description..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTask()">Save Task</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentDate = '{{ $date }}';
let currentPersonId = null;
let allTasks = {};

// Load all tasks on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAllTasks();
});

async function loadAllTasks() {
    try {
        const response = await fetch(`/api/duty-roster/tasks?date=${currentDate}`);
        const data = await response.json();
        
        allTasks = {};
        data.tasks.forEach(task => {
            if (!allTasks[task.person_id]) {
                allTasks[task.person_id] = [];
            }
            allTasks[task.person_id].push(task);
        });
        
        // Update all task lists
        document.querySelectorAll('.task-list').forEach(el => {
            const personId = el.id.replace('tasks-', '');
            updateTaskList(personId);
        });
    } catch (error) {
        console.error('Error loading tasks:', error);
    }
}

function updateTaskList(personId) {
    const taskListEl = document.getElementById(`tasks-${personId}`);
    if (!taskListEl) return;
    
    const tasks = allTasks[personId] || [];
    
    if (tasks.length === 0) {
        taskListEl.innerHTML = '<p class="text-muted small mb-0"><i>No tasks assigned</i></p>';
    } else {
        taskListEl.innerHTML = tasks.map(task => `
            <div class="task-item d-flex justify-content-between align-items-start mb-1 p-2 bg-white rounded border">
                <span class="small">• ${task.task}</span>
                <button class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="deleteTask(${task.id}, ${personId})" title="Delete">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }
}

function addTask(personId, personName) {
    currentPersonId = personId;
    document.getElementById('modalStaffName').textContent = personName;
    document.getElementById('taskDescription').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('addTaskModal'));
    modal.show();
}

async function saveTask() {
    const taskDescription = document.getElementById('taskDescription').value.trim();
    
    if (!taskDescription) {
        alert('Please enter a task description');
        return;
    }
    
    try {
        const response = await fetch('/api/duty-roster/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                person_id: currentPersonId,
                task: taskDescription,
                date: currentDate,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Add to local state
            if (!allTasks[currentPersonId]) {
                allTasks[currentPersonId] = [];
            }
            allTasks[currentPersonId].push(data.task);
            
            // Update UI
            updateTaskList(currentPersonId);
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addTaskModal')).hide();
        }
    } catch (error) {
        console.error('Error saving task:', error);
        alert('Error saving task');
    }
}

async function deleteTask(taskId, personId) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
        const response = await fetch(`/api/duty-roster/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove from local state
            allTasks[personId] = allTasks[personId].filter(t => t.id !== taskId);
            
            // Update UI
            updateTaskList(personId);
        }
    } catch (error) {
        console.error('Error deleting task:', error);
        alert('Error deleting task');
    }
}

function changeDate(newDate) {
    window.location.href = `{{ route('duty.roster.assign.tasks') }}?date=${newDate}`;
}
</script>
@endsection

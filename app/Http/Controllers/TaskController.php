<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\Person;
use App\Models\CategoryType;
use App\Models\StaffCode;
use App\Models\StaffCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch all tasks with their relationships
        $tasks = Task::with(['taskCategory', 'assignedPerson', 'categoryType'])->get();

        // Separate pending and completed tasks
        $pendingTasks = $tasks->where('is_done', false);
        $completedTasks = $tasks->where('is_done', true);
        
        // Get staff categories for the sidebar
        $categoryTypes = CategoryType::getActiveCategories();
        
        // Get active staff members grouped by category
        $staffByCategory = [];
        foreach ($categoryTypes as $category) {
            $staffByCategory[$category->slug] = Person::whereHas('staffCode', function($query) {
                    $query->where('is_active', 1);
                })
                ->whereHas('staffCategory', function($query) use ($category) {
                    $query->where('category', $category->slug);
                })
                ->with(['staffCode', 'staffCategory'])
                ->orderBy('name')
                ->get();
        }
        
        // Calculate stats
        $stats = [
            'pending' => $pendingTasks->count(),
            'completed' => $completedTasks->count(),
            'overdue' => $pendingTasks->filter(fn($t) => $t->isOverdue())->count(),
            'today' => $pendingTasks->filter(fn($t) => $t->isDueToday())->count(),
        ];

        // Return the index view with tasks
        return view('tasks.index', compact('pendingTasks', 'completedTasks', 'categoryTypes', 'staffByCategory', 'stats'));
    }

    /**
     * Show the form for creating a new task.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Fetch all task categories
        $taskCategories = TaskCategory::all();
        
        // Get staff categories and staff members
        $categoryTypes = CategoryType::getActiveCategories();
        
        // Get active staff members
        $staffMembers = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->with(['staffCode', 'staffCategory'])
            ->orderBy('name')
            ->get();

        // Return the create view with categories
        return view('tasks.create', compact('taskCategories', 'categoryTypes', 'staffMembers'));
    }

    /**
     * Store a newly created task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user' => 'required|string',
            'date_added' => 'required|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'task' => 'required|string',
            'task_category_id' => 'required|exists:task_categories,id',
            'person_incharge' => 'nullable|string',
            'assigned_to' => 'nullable|exists:persons,id',
            'staff_category' => 'nullable|string',
            'priority_order' => 'required|in:High,Medium,Low',
            'is_done' => 'nullable|boolean',
        ]);

        // Get staff category if assigned_to is set
        $data = $request->all();
        if ($request->assigned_to) {
            $staffCategory = StaffCategory::where('person_id', $request->assigned_to)->first();
            if ($staffCategory) {
                $data['staff_category'] = $staffCategory->category;
            }
            // Set person_incharge from the assigned person's name
            $person = Person::find($request->assigned_to);
            if ($person) {
                $data['person_incharge'] = $person->name;
            }
        } else {
            // For common/department tasks without assigned person
            $data['person_incharge'] = $request->staff_category 
                ? ucfirst(str_replace('_', ' ', $request->staff_category)) . ' Team' 
                : 'Unassigned';
        }

        // Create the task
        Task::create($data);

        // Redirect back with a success message
        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    /**
     * Update the status of a task (mark as done).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        // Find the task by ID
        $task = Task::findOrFail($id);

        // Update the 'is_done' status
        $task->update(['is_done' => $request->has('is_done')]);

        // Redirect back with a success message
        return redirect()->route('tasks.index')->with('success', 'Task status updated successfully!');
    }
}

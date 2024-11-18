<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\CompletedTask;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
{
    // Existing code to retrieve tasks
    $tasks = Task::with('taskCategory')
            ->where('user', Auth::user()->name)
            ->get();
    
            $completedTasks = CompletedTask::with('taskCategory')
            ->where('user', Auth::user()->name)
            ->get();
    // Assign logged-in user's information to $log
    $log = ['user' => Auth::user()->name];

    // Pass $log to the view
    return view('tasks.index', compact('tasks', 'completedTasks', 'log'));
}
    public function create()
    {
        $taskCategories = TaskCategory::all();
        return view('tasks.create', compact('taskCategories'));
    }

    public function store(Request $request)
{
    $request->validate([
        'date_added' => 'required|date',
        'task' => 'required|string|max:500',
        'task_category_id' => 'required|exists:task_categories,id',
        'person_incharge' => 'required|string|max:255',
        'priority_order' => 'required|in:High,Medium,Low',
    ]);

    Task::create([
        'user' => Auth::user()->name,
        'date_added' => $request->date_added,
        'task' => $request->task,
        'task_category_id' => $request->task_category_id,
        'person_incharge' => $request->person_incharge,
        'priority_order' => $request->priority_order,
        'is_done' => false,
    ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    public function updateStatus(Request $request, $id)
{
    $task = Task::findOrFail($id);

    if ($request->has('is_done')) {
        // Move task to completed_tasks table
        $completedTask = $task->replicate();
        $completedTask->setTable('completed_tasks');
        $completedTask->save();

        // Delete task from tasks table
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task marked as done and moved to completed tasks!');
    } else {
        return redirect()->route('tasks.index')->with('error', 'Invalid operation.');
    }
}

}

<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'completed' => $task->completed,
                    'labels' => is_string($task->labels) ? explode(',', $task->labels) : $task->labels,
                    'created_at' => $task->created_at->format('M d, Y'),
                    'parent' => $this->formatParentTask($task->parent)
                ];
            });

        return Inertia::render('Tasks/List', [
            'tasks' => $tasks
        ]);
    }

    private function formatParentTask($task)
    {
        if (!$task) return null;

        return [
            'id' => $task->id,
            'title' => $task->title,
            'parent' => $this->formatParentTask($task->parent)
        ];
    }

    public function toggleStatus(Task $task)
    {

        $task->update([
            'completed' => !$task->completed
        ]);
    
        return back();
    }

    public function create()
    {
        return Inertia::render('Tasks/Create', [
            'tasks' => Auth::user()->tasks()
                ->select('id', 'title', 'parent_id')
                ->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable',
            'labels' => 'nullable|array|max:255',
            'priority' => 'required|string|max:255',
            'labels' => 'nullable|array|max:255',
            'completed' => 'boolean',
            'parent_id' => 'nullable',
        ]);
        $validated['labels'] ? '[' . implode(',', $validated['labels']) . ']' : '';

 
        $request->user()->tasks()->create($validated);
 
        return redirect(route('tasks.index'));
    }

    public function show(Task $task)
    {
        //
    }

    public function edit(Task $task)
    {
        $tasks = Task::where('id', '!=', $task->id)
        ->where('user_id', auth()->id())
        ->get();
        
        return Inertia::render('Tasks/Update', [
            'task' => $task,
            'tasks' => $tasks,
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'completed' => 'boolean',
            'labels' => 'nullable|array|max:255',
            'parent_id' => 'nullable|exists:tasks,id'
        ]);
    
        $validated['labels'] ? '[' . implode(',', $validated['labels']) . ']' : '';
    
        $task->update($validated);
    
        return redirect(route('tasks.index'));
    }

    public function destroy(Task $task)
    {
       $task->delete();
       
       return redirect()->route('tasks.index');
    }
}

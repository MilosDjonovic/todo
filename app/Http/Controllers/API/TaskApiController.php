<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class TaskApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->where('user_id', auth()->id())  // Only get current user's tasks
            ->when($request->priority, function ($query, $priority) {
                return $query->where('priority', $priority);
            })
            ->when($request->completed !== null, function ($query) use ($request) {
                return $query->where('completed', $request->completed);
            })
            ->when($request->search !== '', function ($query) use ($request) {
                return $query->whereLike('title', '%' . $request->search . '%');
            })
            ->when($request->parent_id, function ($query, $parentId) {
                return $query->where('parent_id', $parentId);
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'completed' => (bool) $task->completed,
                    'labels' => $task->labels,
                    'parent_id' => $task->parent_id,
                    'created_at' => $task->created_at,
                    'updated_at' => $task->updated_at,
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'labels' => 'nullable|array',
            'completed' => 'boolean'
        ]);
        $validated['labels'] = $validated['labels'] ?? [];

        $task = new Task($validated);
        $task->user_id = auth()->id();
        $task->save();

        return response()->json($task, 201);
    }

    public function show(Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Task not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'completed' => (bool) $task->completed,
                'labels' => $task->labels,
                'parent_id' => $task->parent_id,
                'parent' => $task->parent,
                'subtasks' => $task->children()->where('user_id', auth()->id())->get(), // Only get authorized subtasks
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
            ]
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Task not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high',
            'completed' => 'boolean',
            'labels' => 'array',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);
        $validated['labels'] ? '[' . implode(',', $validated['labels']) . ']' : '';

        if (!empty($validated['parent_id'])) {
            $parentTask = Task::where('user_id', auth()->id())
                ->where('id', $validated['parent_id'])
                ->first();

            if (!$parentTask) {
                return response()->json([
                    'message' => 'Parent task not found or not authorized',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Task not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ], Response::HTTP_NO_CONTENT);
    }
}
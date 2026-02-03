<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Jobs\ProcessFileJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    // GET /api/tasks (Pagination, Filter, Sort)
    public function index(Request $request)
    {
        // FIX 1: Tambahkan 'attachments' agar file muncul di frontend
        $query = Task::with(['assignee', 'creator', 'attachments']);

        // FIX 2: Gunakan 'filled' bukan 'has'.
        // 'filled' mengecek apakah parameter ada DAN tidak kosong.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Pencarian Text (Opsional, karena di frontend ada search box)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDir);

        return response()->json($query->paginate(8));
    }

    // POST /api/tasks
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed',
            'priority' => 'in:low,medium,high',
            'assigned_user_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'status' => $validated['status'] ?? 'pending',
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        // Background Job: Email Notification (Req 1.4)
        // \App\Jobs\SendTaskAssignedEmail::dispatch($task);

        return response()->json($task, 201);
    }

    // PUT /api/tasks/{id}
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);
        return response()->json($task);
    }

    // DELETE /api/tasks/{id}
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete(); // Attachments will cascade delete based on DB setup
        return response()->json(['message' => 'Task deleted']);
    }

    // ----------------------------------------------------
    // 1.3 FILE UPLOAD SYSTEM & ENDPOINTS
    // ----------------------------------------------------

    // POST /api/tasks/{id}/attachments
    public function uploadAttachment(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            // 1.3: Type restrictions (Images, Docs, Videos) & Size Limit (10MB)
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4|max:10240',
        ]);

        $file = $request->file('file');

        // 1.3: Store files securely (in 'public' disk, usually symlinked)
        $path = $file->store('attachments/' . $task->id, 'public');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now(),
        ]);

        // 1.4: Background Job Processing (Thumbnail & Virus Scan)
        ProcessFileJob::dispatch($attachment);

        return response()->json($attachment, 201);
    }

    // GET /api/attachments/{id}/download
    public function downloadAttachment($id)
    {
        $attachment = TaskAttachment::findOrFail($id);
        $path = $attachment->file_path;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($path, $attachment->file_name);
    }

    // DELETE /api/attachments/{id}
    public function deleteAttachment($id)
    {
        $attachment = TaskAttachment::findOrFail($id);

        // Delete physical file
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();
        return response()->json(['message' => 'Attachment deleted']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\TaskReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskDetailController extends Controller
{
    /**
     * Get complete detail of a task as JSON for the interactive drawer/modal.
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        $task->load([
            'project',
            'milestone.phase',
            'assignedUser',
            'checklists',
            'attachments.user',
            'reviews.user',
            'comments.user',
            'dependencies.prerequisiteTask',
            'prerequisiteTasks',
        ]);

        return response()->json([
            'task' => $task,
            'checklist_progress' => $task->checklist_progress,
            'references' => $task->attachments->where('attachment_type', 'reference')->values(),
            'deliverables' => $task->attachments->where('attachment_type', 'deliverable')->values(),
        ]);
    }

    /**
     * Update task detail fields (instructions, deliverable, priority, dates, extension fields, etc.)
     */
    public function update(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'output_deliverable' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:todo,in_progress,done',
            'priority' => 'required|in:low,medium,high',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'weight' => 'nullable|integer|min:1|max:10',
            'stage_gate_status' => 'nullable|in:none,pending_review,approved,changes_requested',
            'extension_fields' => 'nullable|array',
        ]);

        $task->update($validated);
        $task->project->recalculateProgress();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Detail tugas berhasil disimpan.', 'task' => $task]);
        }

        return back()->with('success', 'Detail tugas berhasil diperbarui.');
    }

    /**
     * Add a checklist item (Acceptance Criteria).
     */
    public function storeChecklist(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'item' => 'required|string|max:255',
        ]);

        $order = ($task->checklists()->max('order') ?? 0) + 1;

        $checklist = TaskChecklist::create([
            'task_id' => $task->id,
            'item' => $validated['item'],
            'is_completed' => false,
            'order' => $order,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['checklist' => $checklist, 'checklist_progress' => $task->checklist_progress]);
        }

        return back()->with('success', 'Kriteria selesai ditambahkan.');
    }

    /**
     * Toggle checklist item status.
     */
    public function toggleChecklist(Request $request, TaskChecklist $checklist): RedirectResponse|JsonResponse
    {
        if (! $checklist->task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        $checklist->update([
            'is_completed' => ! $checklist->is_completed,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'checklist' => $checklist,
                'checklist_progress' => $checklist->task->checklist_progress,
            ]);
        }

        return back()->with('success', 'Status kriteria diperbarui.');
    }

    /**
     * Remove a checklist item.
     */
    public function destroyChecklist(Request $request, TaskChecklist $checklist): RedirectResponse|JsonResponse
    {
        if (! $checklist->task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        $task = $checklist->task;
        $checklist->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kriteria dihapus.', 'checklist_progress' => $task->checklist_progress]);
        }

        return back()->with('success', 'Kriteria selesai dihapus.');
    }

    /**
     * Upload an attachment or submit a link (Reference vs Deliverable).
     */
    public function storeAttachment(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'attachment_type' => 'required|in:reference,deliverable',
            'link_url' => 'nullable|url|max:2048',
            'file' => 'nullable|file|max:20480', // max 20MB
        ]);

        $filePath = null;
        $fileType = 'link';
        $fileSize = null;

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $filePath = $uploadedFile->store('task-attachments/'.$task->project_id, 'public');
            $fileType = $uploadedFile->getClientOriginalExtension();
            $fileSize = $uploadedFile->getSize();
        } elseif (! empty($validated['link_url'])) {
            $filePath = null;
            $fileType = 'link';
        } else {
            return back()->withErrors(['file' => 'Wajib mengunggah berkas atau mencantumkan tautan URL.']);
        }

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'file_path' => $filePath,
            'link_url' => $validated['link_url'] ?? null,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'attachment_type' => $validated['attachment_type'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['attachment' => $attachment]);
        }

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

    /**
     * Delete an attachment.
     */
    public function destroyAttachment(Request $request, TaskAttachment $attachment): RedirectResponse|JsonResponse
    {
        if (! $attachment->task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Lampiran dihapus.']);
        }

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    /**
     * Add a review / verification for the task (Stage Gate Approval).
     */
    public function storeReview(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,changes_requested,pending',
            'comment' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $review = TaskReview::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'status' => $validated['status'],
            'comment' => $validated['comment'] ?? null,
            'rating' => $validated['rating'] ?? null,
        ]);

        // Update task's stage gate status based on review
        $task->update([
            'stage_gate_status' => $validated['status'],
            'status' => $validated['status'] === 'approved' ? 'done' : ($task->status === 'done' ? 'in_progress' : $task->status),
        ]);

        $task->project->recalculateProgress();

        if ($request->wantsJson()) {
            return response()->json(['review' => $review->load('user'), 'task' => $task]);
        }

        return back()->with('success', 'Review tugas berhasil dicatat.');
    }

    /**
     * Add a discussion comment to a task.
     */
    public function storeComment(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['comment' => $comment->load('user')]);
        }

        return back()->with('success', 'Komentar ditambahkan.');
    }
}


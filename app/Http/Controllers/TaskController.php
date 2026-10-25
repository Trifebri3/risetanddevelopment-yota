<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Milestone;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function storePhase(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $order = ($project->phases()->max('order') ?? 0) + 1;

        Phase::create([
            'project_id' => $project->id,
            'name' => $validated['name'],
            'order' => $order,
        ]);

        return back()->with('success', 'Fase baru ditambahkan.');
    }

    public function storeMilestone(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'phase_id' => 'nullable|exists:phases,id',
            'due_date' => 'nullable|date',
            'definition_of_done' => 'nullable|string',
            'is_public' => 'nullable|boolean',
        ]);

        Milestone::create([
            'project_id' => $project->id,
            'phase_id' => $validated['phase_id'] ?? null,
            'title' => $validated['title'],
            'due_date' => $validated['due_date'] ?? null,
            'definition_of_done' => $validated['definition_of_done'] ?? null,
            'status' => 'pending',
            'is_public' => $validated['is_public'] ?? true,
        ]);

        $project->recalculateProgress();

        return back()->with('success', 'Milestone baru berhasil ditambahkan.');
    }

    public function updateMilestone(Request $request, Milestone $milestone): RedirectResponse
    {
        if (! $milestone->project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'phase_id' => 'nullable|exists:phases,id',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed',
            'definition_of_done' => 'nullable|string',
            'is_public' => 'required|boolean',
        ]);

        $wasCompleted = $milestone->status === 'completed';
        $milestone->update($validated);

        // Auto-log event when milestone is completed
        if (! $wasCompleted && $validated['status'] === 'completed') {
            JournalEntry::create([
                'project_id' => $milestone->project_id,
                'user_id' => $request->user()->id,
                'phase_id' => $milestone->phase_id,
                'date' => now()->toDateString(),
                'title' => 'Milestone Tercapai: '.$milestone->title,
                'activity' => 'Milestone "'.$milestone->title.'" telah berhasil diselesaikan.',
                'results' => 'Definisi selesai terpenuhi: '.($milestone->definition_of_done ?: 'Semua target fase tercapai.'),
                'entry_type' => 'system',
                'status' => 'finalized',
                'is_public' => true,
            ]);
        }

        $milestone->project->recalculateProgress();

        return back()->with('success', 'Milestone diperbarui.');
    }

    public function destroyMilestone(Request $request, Milestone $milestone): RedirectResponse
    {
        if (! $milestone->project->hasAccess($request->user())) {
            abort(403);
        }

        $project = $milestone->project;
        $milestone->delete();
        $project->recalculateProgress();

        return back()->with('success', 'Milestone berhasil dihapus.');
    }

    public function storeTask(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'milestone_id' => 'nullable|exists:milestones,id',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'weight' => 'nullable|integer|min:1|max:10',
        ]);

        Task::create([
            'project_id' => $project->id,
            'milestone_id' => $validated['milestone_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'weight' => $validated['weight'] ?? 1,
            'status' => 'todo',
        ]);

        return back()->with('success', 'Tugas baru berhasil ditambahkan.');
    }

    public function updateTask(Request $request, Task $task): RedirectResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'milestone_id' => 'nullable|exists:milestones,id',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'weight' => 'nullable|integer|min:1|max:10',
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $task->update($validated);

        return back()->with('success', 'Tugas diperbarui.');
    }

    public function toggleTaskStatus(Request $request, Task $task): RedirectResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403);
        }

        $newStatus = match ($task->status) {
            'todo' => 'in_progress',
            'in_progress' => 'done',
            'done' => 'todo',
            default => 'todo',
        };

        $task->update(['status' => $newStatus]);

        return back()->with('success', 'Status tugas diperbarui.');
    }

    public function destroyTask(Request $request, Task $task): RedirectResponse
    {
        if (! $task->project->hasAccess($request->user())) {
            abort(403);
        }

        $task->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}

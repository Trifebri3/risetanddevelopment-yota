<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JournalEntryController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'activity' => 'required|string',
            'results' => 'nullable|string',
            'challenges' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'phase_id' => 'nullable|exists:phases,id',
            'task_id' => 'nullable|exists:tasks,id',
            'status' => 'required|in:draft,reviewed,finalized',
            'is_public' => 'nullable|boolean',
            'files.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,docx,xlsx,csv,zip',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $attachments = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $captions = $request->input('captions', []);

            foreach ($files as $index => $file) {
                $path = $file->store('journal_attachments', 'public');
                $isImage = str_starts_with($file->getMimeType(), 'image/');

                $attachments[] = [
                    'url' => Storage::url($path),
                    'name' => $file->getClientOriginalName(),
                    'caption' => $captions[$index] ?? $file->getClientOriginalName(),
                    'file_type' => $isImage ? 'image' : 'document',
                    'size_bytes' => $file->getSize(),
                ];
            }
        }

        JournalEntry::create([
            'project_id' => $project->id,
            'user_id' => $request->user()->id,
            'phase_id' => $validated['phase_id'] ?? null,
            'task_id' => $validated['task_id'] ?? null,
            'date' => $validated['date'],
            'title' => $validated['title'],
            'activity' => $validated['activity'],
            'results' => $validated['results'] ?? null,
            'challenges' => $validated['challenges'] ?? null,
            'next_steps' => $validated['next_steps'] ?? null,
            'entry_type' => 'manual',
            'status' => $validated['status'],
            'is_public' => $validated['is_public'] ?? false,
            'attachments' => $attachments,
        ]);

        return back()->with('success', 'Catatan Logbook berhasil disimpan!');
    }

    public function update(Request $request, JournalEntry $journalEntry): RedirectResponse
    {
        if (! $journalEntry->project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'activity' => 'required|string',
            'results' => 'nullable|string',
            'challenges' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'phase_id' => 'nullable|exists:phases,id',
            'task_id' => 'nullable|exists:tasks,id',
            'status' => 'required|in:draft,reviewed,finalized',
            'is_public' => 'required|boolean',
        ]);

        $journalEntry->update($validated);

        return back()->with('success', 'Logbook berhasil diperbarui.');
    }

    public function togglePublic(Request $request, JournalEntry $journalEntry): RedirectResponse
    {
        if (! $journalEntry->project->hasAccess($request->user())) {
            abort(403);
        }

        $journalEntry->update([
            'is_public' => ! $journalEntry->is_public,
        ]);

        return back()->with('success', 'Visibilitas publik logbook berhasil diubah.');
    }

    public function destroy(Request $request, JournalEntry $journalEntry): RedirectResponse
    {
        if (! $journalEntry->project->hasAccess($request->user())) {
            abort(403);
        }

        $journalEntry->delete();

        return back()->with('success', 'Catatan logbook berhasil dihapus.');
    }
}

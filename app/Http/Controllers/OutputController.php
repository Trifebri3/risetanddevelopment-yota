<?php

namespace App\Http\Controllers;

use App\Models\Output;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OutputController extends Controller
{
    public function storeOutput(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'output_type' => 'required|in:paper,prototype,dataset,patent,book_module,impact_report',
            'status' => 'required|in:idea,drafting,review,submitted,published_done',
            'target_date' => 'nullable|date',
            'venue_or_journal' => 'nullable|string|max:255',
            'doi_or_url' => 'nullable|string|max:255',
            'evidence_link' => 'nullable|string|max:255',
            'is_public' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        Output::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'output_type' => $validated['output_type'],
            'status' => $validated['status'],
            'target_date' => $validated['target_date'] ?? null,
            'venue_or_journal' => $validated['venue_or_journal'] ?? null,
            'doi_or_url' => $validated['doi_or_url'] ?? null,
            'evidence_link' => $validated['evidence_link'] ?? null,
            'is_public' => $validated['is_public'] ?? true,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Target luaran baru berhasil ditambahkan.');
    }

    public function updateOutput(Request $request, Output $output): RedirectResponse
    {
        if (! $output->project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'output_type' => 'required|in:paper,prototype,dataset,patent,book_module,impact_report',
            'status' => 'required|in:idea,drafting,review,submitted,published_done',
            'target_date' => 'nullable|date',
            'venue_or_journal' => 'nullable|string|max:255',
            'doi_or_url' => 'nullable|string|max:255',
            'evidence_link' => 'nullable|string|max:255',
            'is_public' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $output->update($validated);

        return back()->with('success', 'Target luaran diperbarui.');
    }

    public function destroyOutput(Request $request, Output $output): RedirectResponse
    {
        if (! $output->project->hasAccess($request->user())) {
            abort(403);
        }

        $output->delete();

        return back()->with('success', 'Target luaran dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMetric;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectMetricController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_value' => 'required|string|max:255',
            'current_value' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'status' => 'required|in:not_started,in_progress,achieved,at_risk',
            'notes' => 'nullable|string',
        ]);

        $project->metrics()->create($validated);
        $project->recalculateProgress();

        return back()->with('success', 'Indikator capaian hasil berhasil ditambahkan.');
    }

    public function update(Request $request, ProjectMetric $metric): RedirectResponse
    {
        if (! $metric->project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_value' => 'required|string|max:255',
            'current_value' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'status' => 'required|in:not_started,in_progress,achieved,at_risk',
            'notes' => 'nullable|string',
        ]);

        $metric->update($validated);
        $metric->project->recalculateProgress();

        return back()->with('success', 'Indikator capaian hasil diperbarui.');
    }

    public function destroy(Request $request, ProjectMetric $metric): RedirectResponse
    {
        if (! $metric->project->hasAccess($request->user())) {
            abort(403);
        }

        $project = $metric->project;
        $metric->delete();
        $project->recalculateProgress();

        return back()->with('success', 'Indikator capaian hasil dihapus.');
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WorkflowTemplate;
use App\Services\WorkflowTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkflowTemplateController extends Controller
{
    public function __construct(
        protected WorkflowTemplateService $templateService
    ) {}

    /**
     * List all available templates with stages and tasks.
     */
    public function index(): JsonResponse
    {
        $templates = WorkflowTemplate::with(['stages.templateTasks'])->get();

        return response()->json($templates);
    }

    /**
     * Apply a workflow template to a project.
     */
    public function applyToProject(Request $request, Project $project): RedirectResponse
    {
        if (! $project->isOwner($request->user())) {
            abort(403, 'Hanya pemilik proyek yang dapat menerapkan template metodologi.');
        }

        $validated = $request->validate([
            'workflow_template_id' => 'required|exists:workflow_templates,id',
        ]);

        $template = WorkflowTemplate::findOrFail($validated['workflow_template_id']);
        $this->templateService->applyTemplateToProject($project, $template);

        return back()->with('success', 'Metodologi & tahapan operasional "'.$template->name.'" berhasil diterapkan pada proyek.');
    }
}


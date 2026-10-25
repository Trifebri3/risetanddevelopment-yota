<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Phase;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class IdeaController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $ideas = Idea::where('user_id', $userId)
            ->with('project:id,title,slug')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Ideas/Index', [
            'ideas' => $ideas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_tag' => 'nullable|string|max:50',
            'suggested_mode' => 'required|in:research,product,social,competition',
        ]);

        Idea::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category_tag' => $validated['category_tag'] ?? null,
            'suggested_mode' => $validated['suggested_mode'],
            'status' => 'draft',
        ]);

        return back()->with('success', 'Ide berhasil dicatat di Taman Ide!');
    }

    public function update(Request $request, Idea $idea): RedirectResponse
    {
        if ($idea->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_tag' => 'nullable|string|max:50',
            'suggested_mode' => 'required|in:research,product,social,competition',
            'status' => 'required|in:draft,converted,archived',
        ]);

        $idea->update($validated);

        return back()->with('success', 'Ide berhasil diperbarui.');
    }

    public function destroy(Request $request, Idea $idea): RedirectResponse
    {
        if ($idea->user_id !== $request->user()->id) {
            abort(403);
        }

        $idea->delete();

        return back()->with('success', 'Ide berhasil dihapus.');
    }

    public function convertToProject(Request $request, Idea $idea): RedirectResponse
    {
        if ($idea->user_id !== $request->user()->id) {
            abort(403);
        }

        $project = Project::create([
            'user_id' => $request->user()->id,
            'title' => $idea->title,
            'description' => $idea->content,
            'mode' => $idea->suggested_mode,
            'problem_statement' => 'Dikembangkan dari ide: '.$idea->title,
            'target_outcome' => 'Validasi dan implementasi prototipe/hasil riset.',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'share_token' => Str::random(32),
            'is_public' => false,
            'public_settings' => [
                'show_overview' => true,
                'show_milestones' => true,
                'show_journal' => true,
                'show_outputs' => true,
                'show_budget_summary' => true,
                'allow_feedback' => true,
            ],
        ]);

        Phase::create([
            'project_id' => $project->id,
            'name' => 'Fase 1: Inisiasi & Perencanaan',
            'order' => 1,
        ]);

        $idea->update([
            'status' => 'converted',
            'project_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Ide berhasil dikonversi menjadi Proyek baru!');
    }
}

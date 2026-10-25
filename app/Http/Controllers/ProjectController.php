<?php

namespace App\Http\Controllers;

use App\Models\Phase;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $scope = $request->get('scope', 'all');

        $query = Project::query();

        if ($scope === 'owned') {
            $query->where('user_id', $user->id);
        } elseif ($scope === 'team') {
            $query->where('user_id', '!=', $user->id)
                ->whereHas('members', function ($mq) use ($user) {
                    $mq->where('user_id', $user->id)
                        ->orWhere('email', Str::lower($user->email));
                });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('members', function ($mq) use ($user) {
                        $mq->where('user_id', $user->id)
                            ->orWhere('email', Str::lower($user->email));
                    });
            });
        }

        if ($request->filled('mode') && $request->mode !== 'all') {
            $query->where('mode', $request->mode);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('problem_statement', 'like', "%{$search}%");
            });
        }

        $projects = $query->with(['phases', 'milestones', 'tasks', 'outputs', 'user', 'members.user'])
            ->orderByDesc('updated_at')
            ->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'scope' => $scope,
                'mode' => $request->mode ?? 'all',
                'status' => $request->status ?? 'all',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'mode' => 'required|in:research,product,social,competition',
            'description' => 'nullable|string',
            'problem_statement' => 'nullable|string',
            'target_outcome' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'mode' => $validated['mode'],
            'description' => $validated['description'] ?? null,
            'problem_statement' => $validated['problem_statement'] ?? null,
            'target_outcome' => $validated['target_outcome'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => 'active',
            'share_token' => Str::random(32),
            'team_invite_token' => Str::random(32),
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

        // Create initial default phase
        $phaseName = match ($project->mode) {
            'research' => 'Fase 1: Eksplorasi Teori & Tinjauan Pustaka',
            'product' => 'Fase 1: Validasi Kebutuhan & Desain Awal',
            'social' => 'Fase 1: Pemetaan Masalah & Stakeholder Lapangan',
            'competition' => 'Fase 1: Analisis Panduan & Gagasan Lomba',
            default => 'Fase 1: Perencanaan & Inisiasi',
        };

        Phase::create([
            'project_id' => $project->id,
            'name' => $phaseName,
            'order' => 1,
        ]);

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Proyek berhasil dibuat!');
    }

    public function show(Request $request, Project $project): Response
    {
        if (! $project->hasAccess($request->user())) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }

        if (empty($project->team_invite_token)) {
            $project->update(['team_invite_token' => Str::random(32)]);
        }

        $project->load([
            'user',
            'phases.milestones.tasks',
            'milestones.tasks',
            'tasks',
            'journalEntries',
            'budgetItems.expenses',
            'expenses',
            'outputs',
            'feedbacks',
            'members.user',
        ]);

        $totalEstimatedBudget = (float) $project->budgetItems->sum('total_estimated_price');
        $totalActualExpense = (float) $project->expenses->sum('amount');
        $remainingBudget = $totalEstimatedBudget - $totalActualExpense;

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'isOwner' => $project->isOwner($request->user()),
            'teamInviteUrl' => route('projects.join', $project->team_invite_token),
            'budgetSummary' => [
                'total_estimated' => $totalEstimatedBudget,
                'total_actual' => $totalActualExpense,
                'remaining' => $remainingBudget,
                'percent_used' => $totalEstimatedBudget > 0 ? round(($totalActualExpense / $totalEstimatedBudget) * 100, 1) : 0,
            ],
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        if (! $project->isOwner($request->user())) {
            abort(403, 'Hanya ketua/pemilik proyek yang dapat mengubah informasi utama proyek.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'mode' => 'required|in:research,product,social,competition',
            'description' => 'nullable|string',
            'problem_statement' => 'nullable|string',
            'target_outcome' => 'nullable|string',
            'status' => 'required|in:draft,active,completed,on_hold',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $project->update($validated);

        return back()->with('success', 'Informasi proyek diperbarui.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        if (! $project->isOwner($request->user())) {
            abort(403, 'Hanya ketua/pemilik proyek yang dapat menghapus proyek.');
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus.');
    }

    public function updateSharing(Request $request, Project $project): RedirectResponse
    {
        if (! $project->isOwner($request->user())) {
            abort(403, 'Hanya ketua/pemilik proyek yang dapat mengubah pengaturan berbagi.');
        }

        $validated = $request->validate([
            'is_public' => 'required|boolean',
            'public_settings' => 'required|array',
        ]);

        $project->update([
            'is_public' => $validated['is_public'],
            'public_settings' => $validated['public_settings'],
        ]);

        return back()->with('success', 'Pengaturan berbagi publik berhasil disimpan.');
    }

    public function regenerateShareToken(Request $request, Project $project): RedirectResponse
    {
        if (! $project->isOwner($request->user())) {
            abort(403);
        }

        $project->update([
            'share_token' => Str::random(32),
        ]);

        return back()->with('success', 'Tautan publik berhasil diperbarui dengan token acak baru.');
    }
}

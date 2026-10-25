<?php

namespace App\Http\Controllers;

use App\Models\BudgetItem;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $projects = Project::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('members', function ($mq) use ($user) {
                    $mq->where('user_id', $user->id)
                        ->orWhere('email', Str::lower($user->email));
                });
        })
            ->with(['phases', 'milestones', 'tasks', 'outputs', 'user', 'members.user'])
            ->orderByDesc('updated_at')
            ->get();

        $stats = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'total_budget' => (float) BudgetItem::whereIn('project_id', $projects->pluck('id'))->sum('total_estimated_price'),
            'total_expenses' => (float) Expense::whereIn('project_id', $projects->pluck('id'))->sum('amount'),
            'total_tasks' => Task::whereIn('project_id', $projects->pluck('id'))->count(),
            'completed_tasks' => Task::whereIn('project_id', $projects->pluck('id'))->where('status', 'done')->count(),
            'total_journals' => JournalEntry::whereIn('project_id', $projects->pluck('id'))->count(),
        ];

        $recentJournals = JournalEntry::whereIn('project_id', $projects->pluck('id'))
            ->with('project:id,title,slug,mode')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $upcomingMilestones = Milestone::whereIn('project_id', $projects->pluck('id'))
            ->where('status', '!=', 'completed')
            ->with('project:id,title,slug,mode')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'projects' => $projects,
            'recentJournals' => $recentJournals,
            'upcomingMilestones' => $upcomingMilestones,
        ]);
    }
}

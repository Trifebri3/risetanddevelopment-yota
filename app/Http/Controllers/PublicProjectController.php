<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PublicFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicProjectController extends Controller
{
    public function show(string $token): Response
    {
        $project = Project::where('share_token', $token)->first();

        if (! $project || ! $project->is_public) {
            abort(404, 'Halaman publik proyek ini tidak ditemukan atau dinonaktifkan oleh pemilik.');
        }

        $settings = $project->public_settings ?? [];

        // Build server-side allowlist DTO
        $publicData = [
            'id' => $project->id,
            'title' => $project->title,
            'mode' => $project->mode,
            'status' => $project->status,
            'start_date' => $project->start_date?->format('Y-m-d'),
            'end_date' => $project->end_date?->format('Y-m-d'),
            'progress_percent' => $project->progress_percent,
            'owner_name' => $project->user->name,
            'updated_at' => $project->updated_at->diffForHumans(),
            'share_token' => $project->share_token,
            'public_settings' => $settings,
        ];

        if (! empty($settings['show_overview'])) {
            $publicData['description'] = $project->description;
            $publicData['problem_statement'] = $project->problem_statement;
            $publicData['target_outcome'] = $project->target_outcome;
        }

        if (! empty($settings['show_milestones'])) {
            $publicData['milestones'] = $project->milestones()
                ->where('is_public', true)
                ->orderBy('due_date')
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'due_date' => $m->due_date?->format('d M Y'),
                    'status' => $m->status,
                    'definition_of_done' => $m->definition_of_done,
                ]);
        }

        if (! empty($settings['show_journal'])) {
            $publicData['journal_entries'] = $project->journalEntries()
                ->where('is_public', true)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($j) => [
                    'id' => $j->id,
                    'date' => $j->date->format('d M Y'),
                    'title' => $j->title,
                    'activity' => $j->activity,
                    'results' => $j->results,
                    'challenges' => $j->challenges,
                    'next_steps' => $j->next_steps,
                    'attachments' => $j->attachments,
                ]);
        }

        if (! empty($settings['show_outputs'])) {
            $publicData['outputs'] = $project->outputs()
                ->where('is_public', true)
                ->orderBy('target_date')
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'title' => $o->title,
                    'output_type' => $o->output_type,
                    'status' => $o->status,
                    'target_date' => $o->target_date?->format('d M Y'),
                    'venue_or_journal' => $o->venue_or_journal,
                    'doi_or_url' => $o->doi_or_url,
                    'evidence_link' => $o->evidence_link,
                    'notes' => $o->notes,
                ]);
        }

        if (! empty($settings['show_budget_summary'])) {
            $totalEst = (float) $project->budgetItems()->sum('total_estimated_price');
            $totalAct = (float) $project->expenses()->sum('amount');

            $publicData['budget_summary'] = [
                'total_estimated' => $totalEst,
                'total_actual' => $totalAct,
                'remaining' => $totalEst - $totalAct,
                'percent_used' => $totalEst > 0 ? round(($totalAct / $totalEst) * 100, 1) : 0,
            ];
        }

        $feedbacks = [];
        if (! empty($settings['allow_feedback'])) {
            $feedbacks = $project->feedbacks()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'reviewer_name' => $f->reviewer_name,
                    'reviewer_role' => $f->reviewer_role,
                    'comment' => $f->comment,
                    'rating' => $f->rating,
                    'created_at' => $f->created_at->diffForHumans(),
                ]);
        }

        return Inertia::render('Public/ProjectShare', [
            'project' => $publicData,
            'feedbacks' => $feedbacks,
        ]);
    }

    public function storeFeedback(Request $request, string $token): RedirectResponse
    {
        $project = Project::where('share_token', $token)->first();

        if (! $project || ! $project->is_public) {
            abort(404);
        }

        if (empty($project->public_settings['allow_feedback'])) {
            abort(403, 'Fitur masukan reviewer dinonaktifkan oleh pemilik proyek.');
        }

        $validated = $request->validate([
            'reviewer_name' => 'required|string|max:100',
            'reviewer_role' => 'required|string|max:100',
            'comment' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        PublicFeedback::create([
            'project_id' => $project->id,
            'reviewer_name' => $validated['reviewer_name'],
            'reviewer_role' => $validated['reviewer_role'],
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
        ]);

        return back()->with('success', 'Terima kasih! Catatan masukan Anda telah terkirim kepada pemilik proyek.');
    }
}

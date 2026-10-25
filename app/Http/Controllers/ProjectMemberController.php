<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectMemberController extends Controller
{
    /**
     * Add or invite a team member to the project.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        if (! $project->isOwner($request->user())) {
            abort(403, 'Hanya ketua/pemilik proyek yang dapat mengundang anggota tim.');
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|string|max:100',
        ]);

        $email = Str::lower(trim($validated['email']));

        if (Str::lower($project->user->email) === $email) {
            return back()->withErrors([
                'email' => 'Pemilik proyek sudah otomatis menjadi ketua proyek.',
            ]);
        }

        if ($project->members()->where('email', $email)->exists()) {
            return back()->withErrors([
                'email' => 'Pengguna dengan email ini sudah terdaftar dalam tim proyek.',
            ]);
        }

        $existingUser = User::where('email', $email)->first();

        $project->members()->create([
            'user_id' => $existingUser?->id,
            'email' => $email,
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        return back()->with('success', 'Anggota tim baru berhasil ditambahkan.');
    }

    /**
     * Remove a member from the project.
     */
    public function destroy(Request $request, Project $project, ProjectMember $member): RedirectResponse
    {
        if ($member->project_id !== $project->id) {
            abort(404);
        }

        $currentUser = $request->user();
        $isOwner = $project->isOwner($currentUser);
        $isSelf = (int) $member->user_id === (int) $currentUser->id || Str::lower($member->email) === Str::lower($currentUser->email);

        if (! $isOwner && ! $isSelf) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus anggota ini.');
        }

        $member->delete();

        if ($isSelf && ! $isOwner) {
            return redirect()->route('projects.index')->with('success', 'Anda telah keluar dari tim proyek.');
        }

        return back()->with('success', 'Anggota berhasil dikeluarkan dari tim proyek.');
    }

    /**
     * Join project team via invite token.
     */
    public function joinViaToken(Request $request, string $token): RedirectResponse
    {
        $project = Project::where('team_invite_token', $token)->firstOrFail();
        $user = $request->user();

        if ($project->isOwner($user)) {
            return redirect()->route('projects.show', $project->id)
                ->with('info', 'Anda adalah ketua/pemilik proyek ini.');
        }

        $existingMember = $project->members()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('email', Str::lower($user->email));
            })
            ->first();

        if ($existingMember) {
            if (! $existingMember->user_id) {
                $existingMember->update(['user_id' => $user->id]);
            }

            return redirect()->route('projects.show', $project->id)
                ->with('success', 'Anda sudah menjadi anggota tim di proyek ini.');
        }

        $project->members()->create([
            'user_id' => $user->id,
            'email' => Str::lower($user->email),
            'role' => 'Anggota Tim Kolaborator',
            'status' => 'active',
        ]);

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Selamat! Anda berhasil bergabung ke dalam tim proyek "'.$project->title.'".');
    }

    /**
     * Regenerate team invite token.
     */
    public function regenerateInviteToken(Request $request, Project $project): RedirectResponse
    {
        if (! $project->isOwner($request->user())) {
            abort(403);
        }

        $project->update([
            'team_invite_token' => Str::random(32),
        ]);

        return back()->with('success', 'Tautan undangan tim berhasil diperbarui dengan token baru.');
    }
}

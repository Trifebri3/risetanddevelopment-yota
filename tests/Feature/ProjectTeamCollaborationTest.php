<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTeamCollaborationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_team_member_by_email(): void
    {
        $owner = User::factory()->create();
        $colleague = User::factory()->create(['email' => 'colleague@example.com']);
        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Inovasi Panel Surya Murah',
            'mode' => 'product',
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)->post(route('projects.members.store', $project->id), [
            'email' => 'colleague@example.com',
            'role' => 'Spesialis Teknis',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $colleague->id,
            'email' => 'colleague@example.com',
            'role' => 'Spesialis Teknis',
        ]);
    }

    public function test_user_can_join_team_using_invite_link_token(): void
    {
        $owner = User::factory()->create();
        $newUser = User::factory()->create(['email' => 'member@example.com']);
        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Riset Komposit Serat Alam',
            'mode' => 'research',
            'status' => 'active',
        ]);

        $this->assertNotNull($project->team_invite_token);

        $response = $this->actingAs($newUser)->get(route('projects.join', $project->team_invite_token));

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $newUser->id,
            'email' => 'member@example.com',
            'status' => 'active',
        ]);
    }

    public function test_team_member_can_access_workspace_and_create_task(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Platform Pengolahan Sampah Organik',
            'mode' => 'social',
            'status' => 'active',
        ]);

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'email' => $member->email,
            'role' => 'Anggota Tim Kolaborator',
            'status' => 'active',
        ]);

        // Member can view project
        $viewResponse = $this->actingAs($member)->get(route('projects.show', $project));
        $viewResponse->assertStatus(200);

        // Member can add a task
        $taskResponse = $this->actingAs($member)->post(route('tasks.store', $project), [
            'title' => 'Survei awal bank sampah RW 05',
            'priority' => 'high',
        ]);

        $taskResponse->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Survei awal bank sampah RW 05',
        ]);
    }

    public function test_non_team_member_cannot_access_project_workspace(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Proyek Rahasia Lab',
            'mode' => 'research',
            'status' => 'active',
        ]);

        $response = $this->actingAs($stranger)->get(route('projects.show', $project));
        $response->assertStatus(403);
    }

    public function test_owner_can_remove_team_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Pengembangan Sistem Sensor',
            'mode' => 'product',
            'status' => 'active',
        ]);

        $membership = ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'email' => $member->email,
            'role' => 'Peneliti / Riset',
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)->delete(route('projects.members.destroy', [$project->id, $membership->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('project_members', [
            'id' => $membership->id,
        ]);
    }
}

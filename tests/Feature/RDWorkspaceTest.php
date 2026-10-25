<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RDWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('welcome'));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_dashboard_and_projects(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        $projectsResponse = $this->actingAs($user)->get(route('projects.index'));
        $projectsResponse->assertStatus(200);
    }

    public function test_user_can_create_project_and_initial_phase_is_generated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'title' => 'Riset Bio-Material Sekam Padi',
            'mode' => 'research',
            'problem_statement' => 'Limbah sekam belum termanfaatkan optimal.',
            'target_outcome' => 'Komposit biodegradasi berdaya tahan tinggi.',
            'start_date' => now()->toDateString(),
        ]);

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertEquals('Riset Bio-Material Sekam Padi', $project->title);
        $this->assertCount(1, $project->phases);
        $response->assertRedirect(route('projects.show', $project->id));
    }

    public function test_public_project_can_be_viewed_by_guest_without_login(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Sensor Tanah Nirkabel',
            'mode' => 'product',
            'status' => 'active',
            'is_public' => true,
            'share_token' => 'guest-token-sensor-2026',
            'public_settings' => [
                'show_overview' => true,
                'show_milestones' => true,
                'show_journal' => true,
                'show_outputs' => true,
                'show_budget_summary' => true,
                'allow_feedback' => true,
            ],
        ]);

        $response = $this->get(route('public.share', $project->share_token));
        $response->assertStatus(200);
        $response->assertSee('Sensor Tanah Nirkabel');
    }

    public function test_guest_can_submit_mentor_feedback_on_public_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Sensor Tanah Nirkabel',
            'mode' => 'product',
            'status' => 'active',
            'is_public' => true,
            'share_token' => 'guest-token-feedback-test',
            'public_settings' => [
                'allow_feedback' => true,
            ],
        ]);

        $response = $this->post(route('public.feedback', $project->share_token), [
            'reviewer_name' => 'Dr. Hendra Gunawan',
            'reviewer_role' => 'Reviewer Ahli',
            'comment' => 'Eksperimen awal sangat menjanjikan, perhatikan kalibrasi sensor di kadar air tinggi.',
            'rating' => 5,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('public_feedbacks', [
            'project_id' => $project->id,
            'reviewer_name' => 'Dr. Hendra Gunawan',
        ]);
    }

    public function test_private_project_cannot_be_viewed_by_guest(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Rahasia R&D',
            'mode' => 'product',
            'status' => 'active',
            'is_public' => false,
            'share_token' => 'private-token-xyz',
        ]);

        $response = $this->get(route('public.share', $project->share_token));
        $response->assertStatus(404);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Idea;
use App\Models\Phase;
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
        $response->assertRedirect(route('projects.show', $project));
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

    public function test_user_can_create_competition_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'title' => 'Inovasi Robot SAR - Kontes Robot Indonesia 2026',
            'mode' => 'competition',
            'problem_statement' => 'Kebutuhan robot evakuasi mandiri di area bencana sempit.',
            'target_outcome' => 'Juara 1 KRI Regional & Prototipe Handal.',
            'start_date' => now()->toDateString(),
        ]);

        $project = Project::where('mode', 'competition')->first();
        $this->assertNotNull($project);
        $this->assertEquals('Inovasi Robot SAR - Kontes Robot Indonesia 2026', $project->title);
        $this->assertEquals('Fase 1: Analisis Panduan & Gagasan Lomba', $project->phases->first()->name);
        $response->assertRedirect(route('projects.show', $project));
    }

    public function test_user_can_crud_ideas_including_competition(): void
    {
        $user = User::factory()->create();

        // Create
        $response = $this->actingAs($user)->post(route('ideas.store'), [
            'title' => 'Ide Robot Detektor Korban Gempa',
            'content' => 'Gunakan sensor thermal dan LiDAR mini.',
            'category_tag' => 'Robotics',
            'suggested_mode' => 'competition',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('ideas', [
            'title' => 'Ide Robot Detektor Korban Gempa',
            'suggested_mode' => 'competition',
        ]);

        $idea = Idea::where('title', 'Ide Robot Detektor Korban Gempa')->first();

        // Update
        $updateResponse = $this->actingAs($user)->put(route('ideas.update', $idea->id), [
            'title' => 'Ide Robot Detektor Korban Gempa (Revisi)',
            'content' => 'Gunakan sensor thermal dan LiDAR mini + AI vision.',
            'category_tag' => 'Robotics AI',
            'suggested_mode' => 'competition',
            'status' => 'draft',
        ]);
        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'title' => 'Ide Robot Detektor Korban Gempa (Revisi)',
        ]);

        // Delete
        $deleteResponse = $this->actingAs($user)->delete(route('ideas.destroy', $idea->id));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('ideas', ['id' => $idea->id]);
    }

    public function test_user_can_manage_phases_and_expenses(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Test CRUD',
            'mode' => 'research',
            'status' => 'active',
        ]);

        // Phase Create
        $this->actingAs($user)->post(route('phases.store', $project->id), [
            'name' => 'Fase 2: Eksperimen',
        ]);
        $phase = Phase::where('name', 'Fase 2: Eksperimen')->first();
        $this->assertNotNull($phase);

        // Phase Update
        $this->actingAs($user)->put(route('phases.update', $phase->id), [
            'name' => 'Fase 2: Eksperimen & Validasi',
        ]);
        $this->assertDatabaseHas('phases', ['id' => $phase->id, 'name' => 'Fase 2: Eksperimen & Validasi']);

        // Expense Create & Update
        $this->actingAs($user)->post(route('budget.expenses.store', $project->id), [
            'date' => now()->toDateString(),
            'description' => 'Beli sensor',
            'category' => 'Alat & Bahan',
            'amount' => 150000,
        ]);
        $expense = Expense::where('description', 'Beli sensor')->first();
        $this->assertNotNull($expense);

        $this->actingAs($user)->put(route('budget.expenses.update', $expense->id), [
            'date' => now()->toDateString(),
            'description' => 'Beli sensor ultrasonic (Updated)',
            'category' => 'Alat & Bahan',
            'amount' => 175000,
        ]);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 175000]);

        // Delete Phase
        $this->actingAs($user)->delete(route('phases.destroy', $phase->id));
        $this->assertDatabaseMissing('phases', ['id' => $phase->id]);
    }

    public function test_project_accessible_via_slug_and_numeric_id_redirects_to_slug(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Sistem Deteksi Hama',
            'mode' => 'research',
            'status' => 'active',
        ]);

        $this->assertNotNull($project->slug);
        $this->assertStringContainsString('sistem-deteksi-hama', $project->slug);

        // Access via slug directly returns 200 OK
        $slugResponse = $this->actingAs($user)->get(route('projects.show', $project));
        $slugResponse->assertStatus(200);

        // Access via numeric ID redirects to the slug URL
        $idResponse = $this->actingAs($user)->get('/projects/'.$project->id);
        $idResponse->assertRedirect(route('projects.show', $project));
    }
}

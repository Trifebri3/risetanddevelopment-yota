<?php

namespace Tests\Feature;

use App\Models\Phase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_pin_and_unpin_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Smart Soil',
            'mode' => 'product',
            'status' => 'active',
            'is_pinned' => false,
        ]);

        // Pin project
        $response = $this->actingAs($user)->post(route('projects.toggle_pin', $project));
        $response->assertStatus(302);

        $project->refresh();
        $this->assertTrue($project->is_pinned);
        $this->assertNotNull($project->pinned_at);

        // Unpin project
        $response2 = $this->actingAs($user)->post(route('projects.toggle_pin', $project));
        $response2->assertStatus(302);

        $project->refresh();
        $this->assertFalse($project->is_pinned);
        $this->assertNull($project->pinned_at);
    }

    public function test_pinned_project_appears_at_top_of_list(): void
    {
        $user = User::factory()->create();

        $project1 = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Normal Terbaru',
            'mode' => 'research',
            'status' => 'active',
            'is_pinned' => false,
            'updated_at' => now()->addMinutes(10),
        ]);

        $project2 = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Lama Tapi Disematkan',
            'mode' => 'research',
            'status' => 'active',
            'is_pinned' => true,
            'pinned_at' => now(),
            'updated_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($user)->get(route('projects.index'));
        $response->assertStatus(200);

        $projects = $response->viewData('page')['props']['projects'];
        $this->assertEquals($project2->id, $projects[0]['id']);
    }

    public function test_user_can_update_project_status_to_completed_and_filter_folder_sudah(): void
    {
        $user = User::factory()->create();

        $project1 = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Selesai 1',
            'mode' => 'product',
            'status' => 'active',
        ]);

        $project2 = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Masih Berjalan',
            'mode' => 'social',
            'status' => 'active',
        ]);

        // Update status of project 1 to completed
        $response = $this->actingAs($user)->patch(route('projects.update_status', $project1), [
            'status' => 'completed',
        ]);
        $response->assertStatus(302);

        $project1->refresh();
        $this->assertEquals('completed', $project1->status);

        // Filter status = completed (Folder Sudah)
        $filterResponse = $this->actingAs($user)->get(route('projects.index', ['status' => 'completed']));
        $filterResponse->assertStatus(200);

        $completedProjects = $filterResponse->viewData('page')['props']['projects'];
        $this->assertCount(1, $completedProjects);
        $this->assertEquals($project1->id, $completedProjects[0]['id']);

        // Check statusCounts prop
        $statusCounts = $filterResponse->viewData('page')['props']['statusCounts'];
        $this->assertEquals(2, $statusCounts['all']);
        $this->assertEquals(1, $statusCounts['active']);
        $this->assertEquals(1, $statusCounts['completed']);
    }

    public function test_user_can_delete_project_crud(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Untuk Dihapus',
            'mode' => 'competition',
            'status' => 'draft',
        ]);

        Phase::create([
            'project_id' => $project->id,
            'name' => 'Fase 1',
            'order' => 1,
        ]);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);

        $response = $this->actingAs($user)->delete(route('projects.destroy', $project));
        $response->assertStatus(302);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}

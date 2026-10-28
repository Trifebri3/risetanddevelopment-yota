<?php

namespace Tests\Feature;

use App\Models\Milestone;
use App\Models\Phase;
use App\Models\Project;
use App\Models\ProjectMetric;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Services\WorkflowTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModularMethodologyAndTaskEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the 4 core methodologies
        app(WorkflowTemplateService::class)->syncDefaultTemplates();
    }

    public function test_default_modular_workflow_templates_are_seeded(): void
    {
        $templates = WorkflowTemplate::with('stages.templateTasks')->get();

        $this->assertCount(4, $templates);
        $categories = $templates->pluck('category')->toArray();
        $this->assertContains('research', $categories);
        $this->assertContains('product', $categories);
        $this->assertContains('social', $categories);
        $this->assertContains('competition', $categories);

        // Research workflow check
        $researchTpl = $templates->where('category', 'research')->first();
        $this->assertEquals('Scientific Research Workflow', $researchTpl->name);
        $this->assertCount(6, $researchTpl->stages);
    }

    public function test_creating_project_applies_modular_template_automatically(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'title' => 'Pengujian Sensor IoT Smart Soil',
            'mode' => 'product',
            'problem_statement' => 'Petani membutuhkan informasi kelembaban tanah real-time.',
            'target_outcome' => 'MVP sensor teruji dengan latency rendah dan dashboard analitik.',
        ]);

        $project = Project::where('title', 'Pengujian Sensor IoT Smart Soil')->first();
        $this->assertNotNull($project);
        $response->assertRedirect(route('projects.show', $project));

        // Verify that product template stages and tasks were populated
        $this->assertGreaterThanOrEqual(6, $project->phases()->count());
        $this->assertGreaterThanOrEqual(6, $project->tasks()->count());
        $this->assertGreaterThanOrEqual(1, $project->metrics()->count());

        // Verify task has instructions and checklists
        $firstTask = $project->tasks()->first();
        $this->assertNotNull($firstTask->instructions);
        $this->assertNotNull($firstTask->output_deliverable);
    }

    public function test_user_can_view_task_detail_with_operational_context(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Studi Kualitatif Partisipasi Warga',
            'mode' => 'social',
            'status' => 'active',
        ]);

        $phase = Phase::create(['project_id' => $project->id, 'name' => '01. Pemetaan Lapangan', 'order' => 1]);
        $milestone = Milestone::create(['project_id' => $project->id, 'phase_id' => $phase->id, 'title' => 'Milestone 1']);

        $task = Task::create([
            'project_id' => $project->id,
            'milestone_id' => $milestone->id,
            'title' => 'Menyusun Matriks Literatur',
            'instructions' => "1. Cari artikel Scopus.\n2. Ekstrak metode & hasil.\n3. Rumuskan novelty.",
            'output_deliverable' => 'Matriks Literatur Terstruktur (Excel)',
            'status' => 'todo',
            'priority' => 'high',
            'weight' => 2,
            'extension_fields' => ['research_question' => 'Faktor apa yang mempengaruhi adopsi?'],
        ]);

        TaskChecklist::create([
            'task_id' => $task->id,
            'item' => 'Minimal 10 jurnal bereputasi terindeks',
            'is_completed' => false,
            'order' => 1,
        ]);

        TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'title' => 'Panduan Pencarian Jurnal.pdf',
            'link_url' => 'https://example.com/panduan.pdf',
            'attachment_type' => 'reference',
        ]);

        $response = $this->actingAs($user)->getJson(route('tasks.detail.show', $task));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('Menyusun Matriks Literatur', $data['task']['title']);
        $this->assertEquals('Matriks Literatur Terstruktur (Excel)', $data['task']['output_deliverable']);
        $this->assertCount(1, $data['task']['checklists']);
        $this->assertCount(1, $data['references']);
        $this->assertEquals('Panduan Pencarian Jurnal.pdf', $data['references'][0]['title']);
    }

    public function test_user_can_update_task_details_and_extension_fields(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Riset AI',
            'mode' => 'research',
        ]);
        $phase = Phase::create(['project_id' => $project->id, 'name' => 'Fase 1', 'order' => 1]);
        $milestone = Milestone::create(['project_id' => $project->id, 'phase_id' => $phase->id, 'title' => 'Milestone 1']);
        $task = Task::create([
            'project_id' => $project->id,
            'milestone_id' => $milestone->id,
            'title' => 'Draft Awal',
            'priority' => 'medium',
            'status' => 'todo',
        ]);

        $response = $this->actingAs($user)->putJson(route('tasks.detail.update', $task), [
            'title' => 'Draft Manuskrip Jurnal Q2',
            'description' => 'Penulisan naskah lengkap format IMRAD',
            'instructions' => '1. Tulis intro. 2. Metodologi. 3. Hasil & Diskusi.',
            'output_deliverable' => 'Manuskrip_Final.docx',
            'priority' => 'high',
            'status' => 'in_progress',
            'extension_fields' => [
                'research_question' => 'Apakah arsitektur baru meningkatkan akurasi?',
                'target_journal' => 'IEEE Transactions on Agriculture',
            ],
        ]);

        $response->assertStatus(200);
        $task->refresh();
        $this->assertEquals('Draft Manuskrip Jurnal Q2', $task->title);
        $this->assertEquals('Manuskrip_Final.docx', $task->output_deliverable);
        $this->assertEquals('high', $task->priority);
        $this->assertEquals('in_progress', $task->status);
        $this->assertEquals('IEEE Transactions on Agriculture', $task->extension_fields['target_journal']);
    }

    public function test_acceptance_criteria_checklist_lifecycle(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'title' => 'Proyek Alpha', 'mode' => 'product']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Sprint 1', 'status' => 'todo', 'priority' => 'medium']);

        // 1. Add checklist
        $addResponse = $this->actingAs($user)->postJson(route('tasks.checklists.store', $task), [
            'item' => 'Database migrasi berjalan tanpa error',
        ]);
        $addResponse->assertStatus(200);
        $checklistId = $addResponse->json('checklist.id');
        $this->assertDatabaseHas('task_checklists', ['id' => $checklistId, 'item' => 'Database migrasi berjalan tanpa error', 'is_completed' => false]);

        // 2. Toggle checklist
        $checklist = TaskChecklist::find($checklistId);
        $toggleResponse = $this->actingAs($user)->postJson(route('checklists.toggle', $checklist));
        $toggleResponse->assertStatus(200);
        $this->assertTrue(TaskChecklist::find($checklistId)->is_completed);

        // 3. Delete checklist
        $deleteResponse = $this->actingAs($user)->deleteJson(route('checklists.destroy', $checklist));
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('task_checklists', ['id' => $checklistId]);
    }

    public function test_task_review_and_stage_gate_approval(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'title' => 'Proyek PKM', 'mode' => 'competition']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Simulasi Rubrik', 'status' => 'in_progress', 'priority' => 'high']);

        $response = $this->actingAs($user)->postJson(route('tasks.reviews.store', $task), [
            'status' => 'approved',
            'comment' => 'Karya memenuhi standar bobot rubrik > 85%, siap submit.',
            'rating' => 5,
        ]);

        $response->assertStatus(200);
        $task->refresh();
        $this->assertEquals('approved', $task->stage_gate_status);
        $this->assertEquals('done', $task->status);
        $this->assertDatabaseHas('task_reviews', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'rating' => 5,
        ]);
    }

    public function test_three_tier_progress_calculation(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Proyek Uji Tiga Progres',
            'mode' => 'research',
        ]);

        $phase = Phase::create(['project_id' => $project->id, 'name' => 'Fase 1', 'order' => 1]);

        // Milestones: 2 total, 1 completed
        $m1 = Milestone::create(['project_id' => $project->id, 'phase_id' => $phase->id, 'title' => 'M1', 'status' => 'completed']);
        $m2 = Milestone::create(['project_id' => $project->id, 'phase_id' => $phase->id, 'title' => 'M2', 'status' => 'pending']);

        // Tasks: 2 total, 1 done (weight 3), 1 todo (weight 1)
        $t1 = Task::create(['project_id' => $project->id, 'milestone_id' => $m1->id, 'title' => 'T1', 'weight' => 3, 'status' => 'done']);
        $t2 = Task::create(['project_id' => $project->id, 'milestone_id' => $m2->id, 'title' => 'T2', 'weight' => 1, 'status' => 'todo']);

        // Outcome Metrics: 2 total, 1 achieved
        ProjectMetric::create(['project_id' => $project->id, 'name' => 'Akurasi', 'target_value' => '90%', 'current_value' => '92%', 'category' => 'outcome', 'status' => 'achieved']);
        ProjectMetric::create(['project_id' => $project->id, 'name' => 'Publikasi', 'target_value' => '1', 'current_value' => '0', 'category' => 'outcome', 'status' => 'in_progress']);

        $project->recalculateProgress();
        $project->refresh();

        // Task progress: 3 done / 4 total weight = 75%
        $this->assertEquals(75, $project->task_progress_percent);

        // Milestone progress: 1 completed / 2 total = 50%
        $this->assertEquals(50, $project->milestone_progress_percent);

        // Outcome progress: 1 achieved / 2 total = 50%
        $this->assertEquals(50, $project->outcome_progress_percent);
    }
}


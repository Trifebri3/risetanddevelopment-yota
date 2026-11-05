<?php

namespace Tests\Feature;

use App\Models\BudgetItem;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDocumentAndLabelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_project_with_label_and_filter_by_label(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'title' => 'Smart Soil Agronex',
            'mode' => 'product',
            'label' => 'Startup Agronex',
            'description' => 'IoT sensor tanah cerdas.',
        ]);

        $projectAgronex = Project::where('title', 'Smart Soil Agronex')->first();
        $this->assertNotNull($projectAgronex);
        $this->assertEquals('Startup Agronex', $projectAgronex->label);

        // Create second project with different label
        $projectYota = Project::create([
            'user_id' => $user->id,
            'title' => 'Microvista NLP Engine',
            'slug' => 'microvista-nlp',
            'mode' => 'research',
            'label' => 'Ristek Yota',
            'status' => 'active',
        ]);

        // Filter projects by label
        $filterResponse = $this->actingAs($user)->get(route('projects.index', ['label' => 'Startup Agronex']));
        $filterResponse->assertOk();
        $filterResponse->assertInertia(fn ($page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.title', 'Smart Soil Agronex')
            ->where('projects.0.label', 'Startup Agronex')
        );
    }

    public function test_can_update_project_label(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Inovasi Desa',
            'slug' => 'inovasi-desa',
            'mode' => 'social',
            'label' => 'Komunitas',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->put(route('projects.update', $project->slug), [
            'title' => 'Inovasi Desa Mandiri',
            'mode' => 'social',
            'label' => 'Ristek Yota',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $project->refresh();
        $this->assertEquals('Ristek Yota', $project->label);
    }

    public function test_can_store_and_update_project_proposal(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Smart Soil Agronex',
            'slug' => 'smart-soil-agronex',
            'mode' => 'product',
            'label' => 'Startup Agronex',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('projects.proposal.update', $project->slug), [
            'title' => 'Proposal Hibah Inovasi Agronex',
            'target_funder' => 'Hibah Dikti PKM-KC 2026',
            'submission_deadline' => '2026-12-31',
            'status' => 'in_review',
            'executive_summary' => 'Ringkasan eksekutif inovasi sensor tanah cerdas.',
            'problem_statement' => 'Tingginya kegagalan panen akibat minimnya pemantauan nutrisi tanah.',
            'solution_statement' => 'Solusi sensor IoT berbiaya rendah dengan analitik AI.',
            'novelty_and_innovation' => 'Metode kalibrasi adaptif berbasis edge computing.',
            'methodology' => 'Fase 1 Prototipe, Fase 2 Uji Lapangan.',
            'market_and_impact' => 'Peningkatan hasil tani 25% untuk 500 mitra petani.',
            'budget_summary' => 'Total kebutuhan Rp 15.000.000 untuk 3 modul.',
            'document_url' => 'https://docs.google.com/document/d/example',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('project_proposals', [
            'project_id' => $project->id,
            'title' => 'Proposal Hibah Inovasi Agronex',
            'target_funder' => 'Hibah Dikti PKM-KC 2026',
            'status' => 'in_review',
        ]);
    }

    public function test_can_store_and_update_project_pitch_deck(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Smart Soil Agronex',
            'slug' => 'smart-soil-agronex',
            'mode' => 'product',
            'label' => 'Startup Agronex',
            'status' => 'active',
        ]);

        $slides = [
            [
                'id' => 1,
                'number' => 1,
                'title' => 'Agronex: Smart Soil',
                'subtitle' => 'Masa Depan Pertanian Presisi',
                'bullets' => ['Solusi IoT AI', 'Teruji di lapangan'],
                'metric' => 'Seed Ready',
            ],
            [
                'id' => 2,
                'number' => 2,
                'title' => 'The Problem',
                'subtitle' => '70% Petani kekurangan data tanah real-time',
                'bullets' => ['Inefisiensi pupuk', 'Kerugian panen'],
                'metric' => 'Critical',
            ],
        ];

        $response = $this->actingAs($user)->post(route('projects.pitch_deck.update', $project->slug), [
            'title' => 'Pitch Deck Agronex 2026',
            'tagline' => 'Inisiatif Startup Agronex',
            'audience' => 'Investor Angel & VC',
            'status' => 'ready',
            'theme' => 'emerald',
            'presentation_url' => 'https://canva.com/design/example',
            'slides' => $slides,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('project_pitch_decks', [
            'project_id' => $project->id,
            'title' => 'Pitch Deck Agronex 2026',
            'audience' => 'Investor Angel & VC',
            'status' => 'ready',
            'theme' => 'emerald',
        ]);

        $project->refresh();
        $this->assertNotNull($project->pitchDeck);
        $this->assertCount(2, $project->pitchDeck->slides);
    }

    public function test_can_sync_documents_from_project_data(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Smart Soil Agronex',
            'slug' => 'smart-soil-agronex',
            'mode' => 'product',
            'label' => 'Startup Agronex',
            'problem_statement' => 'Kadar pH tanah sulit dipantau secara berkala.',
            'target_outcome' => 'MVP sensor cerdas terkalibrasi.',
            'status' => 'active',
            'progress_percent' => 35,
        ]);

        Milestone::create([
            'project_id' => $project->id,
            'title' => 'Fabrikasi Sensor Hardware',
            'status' => 'completed',
        ]);

        BudgetItem::create([
            'project_id' => $project->id,
            'item_name' => 'Sensor pH Probe',
            'qty' => 5,
            'unit' => 'Pcs',
            'estimated_unit_price' => 150000,
            'total_estimated_price' => 750000,
        ]);

        $response = $this->actingAs($user)->post(route('projects.documents.sync', $project->slug));
        $response->assertRedirect();

        $this->assertDatabaseHas('project_proposals', [
            'project_id' => $project->id,
            'problem_statement' => 'Kadar pH tanah sulit dipantau secara berkala.',
        ]);

        $this->assertDatabaseHas('project_pitch_decks', [
            'project_id' => $project->id,
            'title' => 'Pitch Deck: Smart Soil Agronex',
            'tagline' => 'Inisiatif Startup Agronex',
        ]);

        $project->refresh();
        $this->assertCount(10, $project->pitchDeck->slides);
    }

    public function test_documents_index_page_is_accessible(): void
    {
        $user = User::factory()->create();
        Project::create([
            'user_id' => $user->id,
            'title' => 'Smart Soil Agronex',
            'slug' => 'smart-soil-agronex',
            'mode' => 'product',
            'label' => 'Startup Agronex',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('documents.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Documents/Index')
            ->has('projects', 1)
            ->has('stats')
            ->has('availableLabels')
        );
    }
}

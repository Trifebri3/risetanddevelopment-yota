<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatisticsAndMobileTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_provides_category_and_ideas_statistics(): void
    {
        $user = User::factory()->create();

        // Create projects in different categories
        Project::create([
            'user_id' => $user->id,
            'title' => 'Riset NLP Medis',
            'slug' => 'riset-nlp-medis',
            'mode' => 'research',
            'status' => 'active',
        ]);

        Project::create([
            'user_id' => $user->id,
            'title' => 'Smart Bin IoT',
            'slug' => 'smart-bin-iot',
            'mode' => 'product',
            'status' => 'active',
        ]);

        Project::create([
            'user_id' => $user->id,
            'title' => 'Pemberdayaan Tani Desa',
            'slug' => 'pemberdayaan-tani',
            'mode' => 'social',
            'status' => 'active',
        ]);

        Project::create([
            'user_id' => $user->id,
            'title' => 'PKM-KC Agronex',
            'slug' => 'pkm-kc-agronex',
            'mode' => 'competition',
            'status' => 'completed',
        ]);

        // Create ideas
        Idea::create([
            'user_id' => $user->id,
            'title' => 'Ide Sensor pH Tanah',
            'content' => 'Gagasan sensor tanah mandiri.',
            'status' => 'draft',
        ]);

        Idea::create([
            'user_id' => $user->id,
            'title' => 'Ide Klasifikasi Citra',
            'content' => 'Gagasan model AI.',
            'status' => 'converted',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.research_projects', 1)
            ->where('stats.product_projects', 1)
            ->where('stats.social_projects', 1)
            ->where('stats.competition_projects', 1)
            ->where('stats.total_ideas', 2)
            ->where('stats.converted_ideas', 1)
            ->where('stats.total_projects', 4)
            ->where('stats.completed_projects', 1)
        );
    }
}

<?php

namespace Database\Seeders;

use App\Services\WorkflowTemplateService;
use Illuminate\Database\Seeder;

class WorkflowTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(WorkflowTemplateService::class);
        $service->syncDefaultTemplates();
    }
}


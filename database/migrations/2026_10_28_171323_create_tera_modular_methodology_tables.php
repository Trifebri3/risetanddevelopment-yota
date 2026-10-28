<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Workflow Templates & Master Metodologi
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category'); // 'research', 'product', 'social', 'competition'
            $table->string('framework')->nullable(); // e.g., 'Scientific Research Workflow', 'HCD + Agile', 'ABCD + HCD', 'Competition Rubric'
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('template_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(1);
            $table->json('gate_requirements')->nullable();
            $table->timestamps();
        });

        Schema::create('template_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_stage_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->text('output_deliverable')->nullable();
            $table->string('priority')->default('medium');
            $table->unsignedInteger('weight')->default(1);
            $table->unsignedInteger('order')->default(1);
            $table->json('checklists')->nullable();
            $table->json('reference_attachments')->nullable();
            $table->json('extension_fields')->nullable();
            $table->timestamps();
        });

        // 2. Extend projects table with 3-tier progress and template link
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('workflow_template_id')->nullable()->after('mode')->constrained()->nullOnDelete();
            $table->unsignedInteger('task_progress_percent')->default(0)->after('progress_percent');
            $table->unsignedInteger('milestone_progress_percent')->default(0)->after('task_progress_percent');
            $table->unsignedInteger('outcome_progress_percent')->default(0)->after('milestone_progress_percent');
        });

        // 3. Extend tasks table with operational execution fields
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
            $table->longText('instructions')->nullable()->after('description');
            $table->text('output_deliverable')->nullable()->after('instructions');
            $table->date('start_date')->nullable()->after('output_deliverable');
            $table->string('stage_gate_status')->default('none')->after('status'); // 'none', 'pending_review', 'approved', 'changes_requested'
            $table->json('extension_fields')->nullable()->after('stage_gate_status');
        });

        // 4. Task Checklists (Kriteria Selesai / Acceptance Criteria)
        Schema::create('task_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('item');
            $table->boolean('is_completed')->default(false);
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
        });

        // 5. Task Attachments (Pemisahan Lampiran Referensi vs Deliverable Hasil Kerja)
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('link_url')->nullable();
            $table->string('file_type')->nullable(); // pdf, xlsx, docx, image, zip, link
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('attachment_type')->default('deliverable'); // 'reference' (instruksi/template) vs 'deliverable' (hasil kerja pelaksana)
            $table->timestamps();
        });

        // 6. Task Reviews & Stage-gate evaluations
        Schema::create('task_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'changes_requested'
            $table->text('comment')->nullable();
            $table->unsignedTinyInteger('rating')->nullable(); // 1 - 5
            $table->timestamps();
        });

        // 7. Task Comments & Activity discussion
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->timestamps();
        });

        // 8. Task Dependencies (Relasi tugas yang mendahului tugas lain)
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('dependency_type')->default('finish_to_start');
            $table->timestamps();
        });

        // 9. Project Metrics (Outcome Progress indicators)
        Schema::create('project_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('target_value');
            $table->string('current_value')->default('0');
            $table->string('unit')->nullable(); // %, skor, orang, prototype, publikasi
            $table->string('category')->default('outcome'); // 'technical', 'impact', 'competition_rubric', 'validation'
            $table->string('status')->default('in_progress'); // 'not_started', 'in_progress', 'achieved', 'at_risk'
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_metrics');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_reviews');
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_checklists');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'assigned_to',
                'instructions',
                'output_deliverable',
                'start_date',
                'stage_gate_status',
                'extension_fields',
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['workflow_template_id']);
            $table->dropColumn([
                'workflow_template_id',
                'task_progress_percent',
                'milestone_progress_percent',
                'outcome_progress_percent',
            ]);
        });

        Schema::dropIfExists('template_tasks');
        Schema::dropIfExists('template_stages');
        Schema::dropIfExists('workflow_templates');
    }
};

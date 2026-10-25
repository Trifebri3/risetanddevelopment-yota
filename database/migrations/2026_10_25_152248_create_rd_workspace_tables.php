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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('mode')->default('research'); // 'research', 'product', 'social'
            $table->text('problem_statement')->nullable();
            $table->text('target_outcome')->nullable();
            $table->string('status')->default('active'); // 'draft', 'active', 'completed', 'on_hold'
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('progress_percent')->default(0);
            $table->string('share_token', 64)->unique()->nullable();
            $table->boolean('is_public')->default(false);
            $table->json('public_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('category_tag')->nullable();
            $table->string('suggested_mode')->default('research');
            $table->string('status')->default('draft'); // 'draft', 'converted', 'archived'
            $table->timestamps();
        });

        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
        });

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'in_progress', 'completed'
            $table->text('definition_of_done')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo'); // 'todo', 'in_progress', 'done'
            $table->string('priority')->default('medium'); // 'low', 'medium', 'high'
            $table->date('due_date')->nullable();
            $table->unsignedInteger('weight')->default(1);
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('title');
            $table->text('activity');
            $table->text('results')->nullable();
            $table->text('challenges')->nullable();
            $table->text('next_steps')->nullable();
            $table->string('entry_type')->default('manual'); // 'manual', 'system'
            $table->string('status')->default('finalized'); // 'draft', 'reviewed', 'finalized'
            $table->boolean('is_public')->default(false);
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('category')->default('Alat & Bahan');
            $table->string('item_name');
            $table->unsignedInteger('qty')->default(1);
            $table->string('unit')->default('unit');
            $table->decimal('estimated_unit_price', 15, 2)->default(0);
            $table->decimal('total_estimated_price', 15, 2)->default(0);
            $table->string('procurement_status')->default('planned'); // 'planned', 'to_purchase', 'ordered', 'received'
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('budget_item_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('description');
            $table->string('category')->default('Operasional');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('receipt_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('output_type')->default('paper'); // 'paper', 'prototype', 'dataset', 'patent', 'book_module', 'impact_report'
            $table->string('status')->default('drafting'); // 'idea', 'drafting', 'review', 'submitted', 'published_done'
            $table->date('target_date')->nullable();
            $table->string('venue_or_journal')->nullable();
            $table->string('doi_or_url')->nullable();
            $table->string('evidence_link')->nullable();
            $table->boolean('is_public')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('public_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('reviewer_name');
            $table->string('reviewer_role')->default('Reviewer Ahli / Mentor');
            $table->text('comment');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_feedbacks');
        Schema::dropIfExists('outputs');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('phases');
        Schema::dropIfExists('ideas');
        Schema::dropIfExists('projects');
    }
};

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
        Schema::create('project_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('target_funder')->nullable();
            $table->date('submission_deadline')->nullable();
            $table->string('status')->default('draft'); // 'draft', 'in_review', 'final', 'submitted'
            $table->text('executive_summary')->nullable();
            $table->text('problem_statement')->nullable();
            $table->text('solution_statement')->nullable();
            $table->text('novelty_and_innovation')->nullable();
            $table->text('methodology')->nullable();
            $table->text('market_and_impact')->nullable();
            $table->text('budget_summary')->nullable();
            $table->string('document_url')->nullable();
            $table->string('file_path')->nullable();
            $table->json('custom_sections')->nullable();
            $table->timestamps();
        });

        Schema::create('project_pitch_decks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->string('audience')->nullable();
            $table->string('status')->default('draft'); // 'draft', 'ready', 'presented'
            $table->string('theme')->default('emerald');
            $table->string('presentation_url')->nullable();
            $table->string('file_path')->nullable();
            $table->json('slides')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_pitch_decks');
        Schema::dropIfExists('project_proposals');
    }
};

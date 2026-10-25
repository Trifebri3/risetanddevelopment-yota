<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\OutputController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\PublicProjectController;
use App\Http\Controllers\TaskController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Landing Page
Route::get('/', function () {
    $featuredPublicProjects = Project::where('is_public', true)
        ->select('id', 'title', 'slug', 'mode', 'progress_percent', 'share_token', 'description')
        ->limit(3)
        ->get();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'featuredProjects' => $featuredPublicProjects,
    ]);
})->name('welcome');

// Public Project Share (Reviewer / Pembimbing / Mitra tanpa login)
Route::get('/share/{token}', [PublicProjectController::class, 'show'])->name('public.share');
Route::post('/share/{token}/feedback', [PublicProjectController::class, 'storeFeedback'])->name('public.feedback');

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects & Team Collaboration
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/join/{token}', [ProjectMemberController::class, 'joinViaToken'])->name('projects.join');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/share', [ProjectController::class, 'updateSharing'])->name('projects.share.update');
    Route::post('/projects/{project}/regenerate-token', [ProjectController::class, 'regenerateShareToken'])->name('projects.share.regenerate');
    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');
    Route::post('/projects/{project}/members/regenerate-invite', [ProjectMemberController::class, 'regenerateInviteToken'])->name('projects.members.regenerate_invite');

    // Ideas (Taman Ide)
    Route::get('/ideas', [IdeaController::class, 'index'])->name('ideas.index');
    Route::post('/ideas', [IdeaController::class, 'store'])->name('ideas.store');

    Route::put('/ideas/{idea}', [IdeaController::class, 'update'])->name('ideas.update');
    Route::delete('/ideas/{idea}', [IdeaController::class, 'destroy'])->name('ideas.destroy');
    Route::post('/ideas/{idea}/convert', [IdeaController::class, 'convertToProject'])->name('ideas.convert');

    // Milestones & Tasks
    Route::post('/projects/{project}/phases', [TaskController::class, 'storePhase'])->name('phases.store');
    Route::post('/projects/{project}/milestones', [TaskController::class, 'storeMilestone'])->name('milestones.store');
    Route::put('/milestones/{milestone}', [TaskController::class, 'updateMilestone'])->name('milestones.update');
    Route::delete('/milestones/{milestone}', [TaskController::class, 'destroyMilestone'])->name('milestones.destroy');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'storeTask'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'updateTask'])->name('tasks.update');
    Route::post('/tasks/{task}/toggle', [TaskController::class, 'toggleTaskStatus'])->name('tasks.toggle');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroyTask'])->name('tasks.destroy');

    // Auto-Logbook & Development Journal
    Route::post('/projects/{project}/journal', [JournalEntryController::class, 'store'])->name('journal.store');
    Route::put('/journal/{journalEntry}', [JournalEntryController::class, 'update'])->name('journal.update');
    Route::post('/journal/{journalEntry}/toggle-public', [JournalEntryController::class, 'togglePublic'])->name('journal.toggle_public');
    Route::delete('/journal/{journalEntry}', [JournalEntryController::class, 'destroy'])->name('journal.destroy');

    // Budget & Expenses
    Route::post('/projects/{project}/budget-items', [BudgetController::class, 'storeBudgetItem'])->name('budget.items.store');
    Route::put('/budget-items/{budgetItem}', [BudgetController::class, 'updateBudgetItem'])->name('budget.items.update');
    Route::delete('/budget-items/{budgetItem}', [BudgetController::class, 'destroyBudgetItem'])->name('budget.items.destroy');
    Route::post('/projects/{project}/expenses', [BudgetController::class, 'storeExpense'])->name('budget.expenses.store');
    Route::delete('/expenses/{expense}', [BudgetController::class, 'destroyExpense'])->name('budget.expenses.destroy');
    Route::get('/projects/{project}/export-csv', [BudgetController::class, 'exportCsv'])->name('budget.export');

    // Outputs & Publications
    Route::post('/projects/{project}/outputs', [OutputController::class, 'storeOutput'])->name('outputs.store');
    Route::put('/outputs/{output}', [OutputController::class, 'updateOutput'])->name('outputs.update');
    Route::delete('/outputs/{output}', [OutputController::class, 'destroyOutput'])->name('outputs.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

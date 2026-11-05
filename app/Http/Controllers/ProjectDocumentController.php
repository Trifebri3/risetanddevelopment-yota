<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPitchDeck;
use App\Models\ProjectProposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectDocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Project::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('members', function ($mq) use ($user) {
                    $mq->where('user_id', $user->id)
                        ->orWhere('email', Str::lower($user->email));
                });
        })
            ->with(['proposal', 'pitchDeck', 'tasks', 'milestones', 'budgetItems', 'outputs']);

        if ($request->filled('label') && $request->label !== 'all') {
            $query->where('label', $request->label);
        }

        if ($request->filled('mode') && $request->mode !== 'all') {
            $query->where('mode', $request->mode);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('problem_statement', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get();

        // Calculate statistics
        $allUserProjects = Project::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('members', function ($mq) use ($user) {
                    $mq->where('user_id', $user->id)
                        ->orWhere('email', Str::lower($user->email));
                });
        })->with(['proposal', 'pitchDeck'])->get();

        $stats = [
            'total_projects' => $allUserProjects->count(),
            'total_proposals' => $allUserProjects->filter(fn ($p) => $p->proposal !== null)->count(),
            'submitted_proposals' => $allUserProjects->filter(fn ($p) => optional($p->proposal)->status === 'submitted' || optional($p->proposal)->status === 'final')->count(),
            'total_pitch_decks' => $allUserProjects->filter(fn ($p) => $p->pitchDeck !== null)->count(),
            'ready_pitch_decks' => $allUserProjects->filter(fn ($p) => optional($p->pitchDeck)->status === 'ready' || optional($p->pitchDeck)->status === 'presented')->count(),
        ];

        $availableLabels = Project::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('members', function ($mq) use ($user) {
                    $mq->where('user_id', $user->id)
                        ->orWhere('email', Str::lower($user->email));
                });
        })
            ->whereNotNull('label')
            ->where('label', '!=', '')
            ->distinct()
            ->pluck('label')
            ->values();

        return Inertia::render('Documents/Index', [
            'projects' => $projects,
            'stats' => $stats,
            'availableLabels' => $availableLabels,
            'filters' => [
                'label' => $request->label ?? 'all',
                'mode' => $request->mode ?? 'all',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function storeOrUpdateProposal(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'target_funder' => 'nullable|string|max:255',
            'submission_deadline' => 'nullable|date',
            'status' => 'required|in:draft,in_review,final,submitted',
            'executive_summary' => 'nullable|string',
            'problem_statement' => 'nullable|string',
            'solution_statement' => 'nullable|string',
            'novelty_and_innovation' => 'nullable|string',
            'methodology' => 'nullable|string',
            'market_and_impact' => 'nullable|string',
            'budget_summary' => 'nullable|string',
            'document_url' => 'nullable|url|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx,odt,rtf|max:20480',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('proposals/'.$project->id, 'public');
        }

        $updateData = [
            'title' => $validated['title'],
            'target_funder' => $validated['target_funder'] ?? null,
            'submission_deadline' => $validated['submission_deadline'] ?? null,
            'status' => $validated['status'],
            'executive_summary' => $validated['executive_summary'] ?? null,
            'problem_statement' => $validated['problem_statement'] ?? null,
            'solution_statement' => $validated['solution_statement'] ?? null,
            'novelty_and_innovation' => $validated['novelty_and_innovation'] ?? null,
            'methodology' => $validated['methodology'] ?? null,
            'market_and_impact' => $validated['market_and_impact'] ?? null,
            'budget_summary' => $validated['budget_summary'] ?? null,
            'document_url' => $validated['document_url'] ?? null,
        ];

        if ($filePath) {
            $updateData['file_path'] = $filePath;
        }

        ProjectProposal::updateOrCreate(
            ['project_id' => $project->id],
            $updateData
        );

        return redirect()->back()->with('success', 'Dokumen proposal berhasil disimpan!');
    }

    public function storeOrUpdatePitchDeck(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',
            'status' => 'required|in:draft,ready,presented',
            'theme' => 'nullable|string|max:50',
            'presentation_url' => 'nullable|url|max:255',
            'slides' => 'nullable|array',
            'file' => 'nullable|file|mimes:pdf,ppt,pptx,odp|max:30720',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('pitch_decks/'.$project->id, 'public');
        }

        $updateData = [
            'title' => $validated['title'],
            'tagline' => $validated['tagline'] ?? null,
            'audience' => $validated['audience'] ?? null,
            'status' => $validated['status'],
            'theme' => $validated['theme'] ?? 'emerald',
            'presentation_url' => $validated['presentation_url'] ?? null,
            'slides' => $validated['slides'] ?? null,
        ];

        if ($filePath) {
            $updateData['file_path'] = $filePath;
        }

        ProjectPitchDeck::updateOrCreate(
            ['project_id' => $project->id],
            $updateData
        );

        return redirect()->back()->with('success', 'Slide Pitch Deck berhasil diperbarui!');
    }

    public function syncFromProject(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $project->load(['budgetItems', 'milestones', 'outputs', 'members.user']);

        $totalBudget = (float) $project->budgetItems->sum('total_estimated_price');
        $milestonesList = $project->milestones->map(fn ($m) => "• {$m->title} (".($m->due_date ? date('d M Y', strtotime($m->due_date)) : 'TBD').") - {$m->status}")->implode("\n");
        $outputsList = $project->outputs->map(fn ($o) => "• [{$o->output_type}] {$o->title} (".($o->venue_or_journal ?? 'Venue TBD').')')->implode("\n");

        // 1. Sync Proposal
        $proposalData = [
            'title' => 'Proposal: '.$project->title,
            'target_funder' => match ($project->mode) {
                'competition' => 'Kompetisi / Dewan Juri',
                'product' => 'Investor Pre-Seed / Inkubator',
                'social' => 'Mitra CSR / Program Hibah Sosial',
                default => 'Hibah Riset / Publikasi Ilmiah',
            },
            'status' => 'draft',
            'executive_summary' => $project->description ?: "Proyek {$project->title} bertujuan untuk menyelesaikan tantangan kritis dengan pendekatan terukur.",
            'problem_statement' => $project->problem_statement ?: 'Belum ada rumusan masalah spesifik.',
            'solution_statement' => $project->target_outcome ?: 'Solusi komprehensif berbasis inovasi terintegrasi.',
            'novelty_and_innovation' => "Keunggulan kompetitif dan kebaruan proyek {$project->title} berfokus pada efisiensi, keterlacakan, dan dampak terukur.",
            'methodology' => "Rencana Tahapan & Milestone:\n".($milestonesList ?: "• Tahap 1: Eksplorasi & Desain\n• Tahap 2: Pengembangan & Validasi\n• Tahap 3: Evaluasi & Diseminasi"),
            'market_and_impact' => "Target Capaian & Dampak:\n".($outputsList ?: '• Terwujudnya luaran produk teruji dan dampak nyata bagi penerima manfaat.'),
            'budget_summary' => 'Total Rencana Anggaran Biaya (RAB): Rp '.number_format($totalBudget, 0, ',', '.')."\nAlokasi dirancang untuk pengadaan alat bahan, operasional, dan pengujian lapangan.",
        ];

        ProjectProposal::updateOrCreate(
            ['project_id' => $project->id],
            $proposalData
        );

        // 2. Sync Pitch Deck 10 Slides Standard
        $defaultSlides = [
            [
                'id' => 1,
                'number' => 1,
                'type' => 'cover',
                'title' => $project->title,
                'subtitle' => $project->label ? "Inisiatif {$project->label}" : 'Solusi Inovatif Masa Depan',
                'bullets' => [
                    $project->description ?: 'Platform inovasi masa depan berbasis data dan riset terapan.',
                    'Dipersiapkan oleh Tim Pengembang Tera R&D',
                ],
                'notes' => 'Buka dengan hook yang kuat, kenalkan visi utama dalam 15 detik pertama.',
                'metric' => $project->progress_percent.'% Progres',
            ],
            [
                'id' => 2,
                'number' => 2,
                'type' => 'problem',
                'title' => 'The Problem (Masalah Nyata)',
                'subtitle' => 'Tantangan kritis yang dihadapi saat ini',
                'bullets' => [
                    $project->problem_statement ?: 'Kesenjangan antara kondisi lapangan dengan kebutuhan solusi ideal.',
                    'Tingginya inefisiensi dan minimnya alat bantu yang terintegrasi.',
                    'Kebutuhan mendesak untuk solusi terstandarisasi dan mudah diakses.',
                ],
                'notes' => 'Fokus pada rasa sakit (pain point) pengguna atau objek riset.',
                'metric' => 'Urgent Need',
            ],
            [
                'id' => 3,
                'number' => 3,
                'type' => 'solution',
                'title' => 'The Solution & Product',
                'subtitle' => 'Pendekatan kami dalam menyelesaikan masalah',
                'bullets' => [
                    $project->target_outcome ?: 'Membangun solusi tepat guna dengan metodologi teruji.',
                    'Fitur utama yang membedakan dengan alternatif yang ada.',
                    'Validasi cepat dan iterasi berbasis bukti empiris.',
                ],
                'notes' => 'Tunjukkan bagaimana produk/prototipe bekerja secara elegan.',
                'metric' => '100% Solutif',
            ],
            [
                'id' => 4,
                'number' => 4,
                'type' => 'market',
                'title' => 'Target Pengguna & Pasar',
                'subtitle' => 'Siapa yang membutuhkan dan seberapa besar dampaknya',
                'bullets' => [
                    'Segmentasi penerima manfaat / pengguna prioritas (Early Adopters).',
                    'Potensi adopsi dan skalabilitas di berbagai sektor atau wilayah.',
                    'Validasi ketertarikan dari wawancara dan riset kebutuhan pengguna.',
                ],
                'notes' => 'Jelaskan target pasar atau komunitas sasaran secara spesifik.',
                'metric' => 'Tinggi',
            ],
            [
                'id' => 5,
                'number' => 5,
                'type' => 'novelty',
                'title' => 'Kebaruan & Keunggulan Kompetitif',
                'subtitle' => 'Mengapa solusi ini lebih unggul dan unik (Unfair Advantage)',
                'bullets' => [
                    'Arsitektur modular dan keterlacakan tahapan yang jelas.',
                    'Efisiensi biaya dan performa lebih tinggi dibanding opsi eksisting.',
                    'Dukungan metodologi saintifik dan teknologi terkini.',
                ],
                'notes' => 'Tunjukkan matriks perbandingan dengan kompetitor atau metode konvensional.',
                'metric' => 'Novelty High',
            ],
            [
                'id' => 6,
                'number' => 6,
                'type' => 'business_model',
                'title' => 'Model Pelaksanaan & Keberlanjutan',
                'subtitle' => 'Bagaimana inovasi ini bertahan dan berkembang secara mandiri',
                'bullets' => [
                    'Model keberlanjutan: Kemitraan strategis, lisensi, hibah, atau unit usaha.',
                    'Rencana implementasi bertahap untuk memastikan penerimaan stakeholder.',
                    'Pilar keberlanjutan program dan transfer teknologi.',
                ],
                'notes' => 'Beri kepastian bahwa proyek tidak berhenti saat dana awal habis.',
                'metric' => 'Sustainable',
            ],
            [
                'id' => 7,
                'number' => 7,
                'type' => 'traction',
                'title' => 'Traksi, Roadmap & Milestone',
                'subtitle' => 'Pencapaian nyata dan tahapan selanjutnya',
                'bullets' => [
                    "Progres saat ini: {$project->progress_percent}% penyelesaian target.",
                    $milestonesList ? Str::limit($milestonesList, 200) : 'Milestone 1 selesai, persiapan pilot test tahap 2.',
                    'Bukti logbook dan evaluasi berkala yang terdokumentasi.',
                ],
                'notes' => 'Tunjukkan bahwa tim sudah bergerak dan memiliki rekam jejak eksekusi.',
                'metric' => $project->progress_percent.'%',
            ],
            [
                'id' => 8,
                'number' => 8,
                'type' => 'financial',
                'title' => 'Rencana Anggaran & Kebutuhan Dana',
                'subtitle' => 'Alokasi sumber daya untuk mencapai milestone kunci',
                'bullets' => [
                    'Estimasi total anggaran: Rp '.number_format($totalBudget, 0, ',', '.'),
                    'Alokasi utama: 40% Prototipe/Bahan, 35% Validasi Lapangan, 25% Operasional.',
                    'Transparansi pencatatan anggaran dan pembelanjaan real-time.',
                ],
                'notes' => 'Sampaikan dana yang diminta (The Ask) dan ekspektasi milestone yang dicapai.',
                'metric' => 'Rp '.number_format($totalBudget, 0, ',', '.'),
            ],
            [
                'id' => 9,
                'number' => 9,
                'type' => 'team',
                'title' => 'Tim Pengembang & Peneliti',
                'subtitle' => 'Keahlian multidisiplin yang mengeksekusi proyek',
                'bullets' => [
                    'Ketua / Lead: '.$project->user->name.' (Inisiator Proyek)',
                    'Anggota Tim: '.($project->members->count() > 0 ? $project->members->pluck('email')->implode(', ') : 'Kolaborasi multidisiplin'),
                    'Keahlian: Desain sistem, metodologi riset, implementasi teknis, dan manajemen program.',
                ],
                'notes' => 'Tunjukkan kredibilitas dan komitmen anggota tim.',
                'metric' => ($project->members->count() + 1).' Orang',
            ],
            [
                'id' => 10,
                'number' => 10,
                'type' => 'closing',
                'title' => 'Visi & Call to Action',
                'subtitle' => 'Mari berkolaborasi mewujudkan dampak nyata',
                'bullets' => [
                    'Kami mengundang mitra, investor, dan reviewer untuk bergabung bersama kami.',
                    'Informasi detail & logbook publik: tera.app/share/'.$project->share_token,
                    'Hubungi kami untuk demonstrasi langsung dan kolaborasi strategis.',
                ],
                'notes' => 'Tutup dengan ajakan bertindak yang lugas dan berkesan.',
                'metric' => 'Siap Kolaborasi',
            ],
        ];

        ProjectPitchDeck::updateOrCreate(
            ['project_id' => $project->id],
            [
                'title' => 'Pitch Deck: '.$project->title,
                'tagline' => $project->label ? "Inisiatif {$project->label}" : 'Solusi Inovasi R&D',
                'audience' => match ($project->mode) {
                    'competition' => 'Juri Kompetisi & Reviewer',
                    'product' => 'Investor VC / Angel & Akselerator',
                    'social' => 'Mitra Komunitas & Lembaga Donor',
                    default => 'Reviewer Akademik & Dewan Riset',
                },
                'status' => 'draft',
                'theme' => 'emerald',
                'slides' => $defaultSlides,
            ]
        );

        return redirect()->back()->with('success', 'Dokumen Proposal dan 10 Slide Pitch Deck berhasil disinkronkan dari data proyek!');
    }
}

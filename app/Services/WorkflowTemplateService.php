<?php

namespace App\Services;

use App\Models\Milestone;
use App\Models\Phase;
use App\Models\Project;
use App\Models\ProjectMetric;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TemplateStage;
use App\Models\TemplateTask;
use App\Models\WorkflowTemplate;
use Illuminate\Support\Facades\DB;

class WorkflowTemplateService
{
    /**
     * Seed or sync default workflow templates for all 4 main methodologies.
     */
    public function syncDefaultTemplates(): void
    {
        $templatesData = $this->getDefaultTemplatesData();

        foreach ($templatesData as $tpl) {
            $template = WorkflowTemplate::updateOrCreate(
                ['code' => $tpl['code']],
                [
                    'name' => $tpl['name'],
                    'category' => $tpl['category'],
                    'framework' => $tpl['framework'],
                    'description' => $tpl['description'],
                    'icon' => $tpl['icon'] ?? null,
                    'is_default' => $tpl['is_default'] ?? true,
                ]
            );

            // Sync stages and tasks
            foreach ($tpl['stages'] as $sIndex => $stg) {
                $stage = TemplateStage::updateOrCreate(
                    [
                        'workflow_template_id' => $template->id,
                        'name' => $stg['name'],
                    ],
                    [
                        'description' => $stg['description'] ?? null,
                        'order' => $sIndex + 1,
                        'gate_requirements' => $stg['gate_requirements'] ?? null,
                    ]
                );

                foreach ($stg['tasks'] as $tIndex => $tsk) {
                    TemplateTask::updateOrCreate(
                        [
                            'template_stage_id' => $stage->id,
                            'title' => $tsk['title'],
                        ],
                        [
                            'description' => $tsk['description'] ?? null,
                            'instructions' => $tsk['instructions'] ?? null,
                            'output_deliverable' => $tsk['output_deliverable'] ?? null,
                            'priority' => $tsk['priority'] ?? 'medium',
                            'weight' => $tsk['weight'] ?? 1,
                            'order' => $tIndex + 1,
                            'checklists' => $tsk['checklists'] ?? [],
                            'reference_attachments' => $tsk['reference_attachments'] ?? [],
                            'extension_fields' => $tsk['extension_fields'] ?? [],
                        ]
                    );
                }
            }
        }
    }

    /**
     * Apply a workflow template to a project.
     * Generates Phases (Stages), Milestones, and actionable pre-populated Tasks.
     */
    public function applyTemplateToProject(Project $project, WorkflowTemplate $template): void
    {
        DB::transaction(function () use ($project, $template) {
            $project->update([
                'workflow_template_id' => $template->id,
                'mode' => $template->category,
            ]);

            $stages = $template->stages()->with('templateTasks')->orderBy('order')->get();

            foreach ($stages as $stageIndex => $stage) {
                // Create or find Phase
                $phase = Phase::create([
                    'project_id' => $project->id,
                    'name' => $stage->name,
                    'order' => $stageIndex + 1,
                ]);

                // Create primary Milestone for this stage
                $milestone = Milestone::create([
                    'project_id' => $project->id,
                    'phase_id' => $phase->id,
                    'title' => 'Selesai: '.$stage->name,
                    'definition_of_done' => $stage->description ?: 'Semua kriteria dan luaran tahap terpenuhi.',
                    'status' => 'pending',
                    'is_public' => true,
                    'due_date' => $project->end_date ? now()->addDays(($stageIndex + 1) * 7)->toDateString() : null,
                ]);

                // Create pre-populated instructional Tasks
                foreach ($stage->templateTasks as $templateTask) {
                    $task = Task::create([
                        'project_id' => $project->id,
                        'milestone_id' => $milestone->id,
                        'title' => $templateTask->title,
                        'description' => $templateTask->description,
                        'instructions' => $templateTask->instructions,
                        'output_deliverable' => $templateTask->output_deliverable,
                        'status' => 'todo',
                        'priority' => $templateTask->priority,
                        'weight' => $templateTask->weight,
                        'stage_gate_status' => 'none',
                        'extension_fields' => $templateTask->extension_fields,
                    ]);

                    // Add Checklists (Acceptance Criteria)
                    if (! empty($templateTask->checklists) && is_array($templateTask->checklists)) {
                        foreach ($templateTask->checklists as $cIndex => $item) {
                            TaskChecklist::create([
                                'task_id' => $task->id,
                                'item' => is_array($item) ? ($item['item'] ?? '') : $item,
                                'is_completed' => false,
                                'order' => $cIndex + 1,
                            ]);
                        }
                    }

                    // Add Reference attachments if provided
                    if (! empty($templateTask->reference_attachments) && is_array($templateTask->reference_attachments)) {
                        foreach ($templateTask->reference_attachments as $ref) {
                            TaskAttachment::create([
                                'task_id' => $task->id,
                                'user_id' => $project->user_id,
                                'title' => $ref['title'] ?? 'Dokumen Referensi',
                                'link_url' => $ref['url'] ?? null,
                                'file_type' => $ref['type'] ?? 'link',
                                'attachment_type' => 'reference',
                            ]);
                        }
                    }
                }
            }

            // Seed initial default outcome metrics for the category if none exist
            $this->seedInitialMetricsForCategory($project, $template->category);

            $project->recalculateProgress();
        });
    }

    /**
     * Default outcome metrics based on project category.
     */
    protected function seedInitialMetricsForCategory(Project $project, string $category): void
    {
        if ($project->metrics()->count() > 0) {
            return;
        }

        $metrics = match ($category) {
            'research' => [
                ['name' => 'Validitas & Signifikansi Hasil', 'target_value' => 'p < 0.05 / Tervalidasi', 'current_value' => 'Dalam Eksperimen', 'unit' => 'indikator', 'category' => 'scientific'],
                ['name' => 'Draft Manuskrip Jurnal', 'target_value' => '1 Manuskrip Siap Submit', 'current_value' => '0', 'unit' => 'dokumen', 'category' => 'publication'],
            ],
            'product' => [
                ['name' => 'System Usability Scale (SUS)', 'target_value' => '> 75.0', 'current_value' => '0', 'unit' => 'skor', 'category' => 'usability'],
                ['name' => 'Crash-Free Users / Keandalan', 'target_value' => '99%', 'current_value' => 'Dalam Uji', 'unit' => '%', 'category' => 'technical'],
                ['name' => 'Pengguna Aktif Penguji Pilot', 'target_value' => '50 Pengguna', 'current_value' => '0', 'unit' => 'orang', 'category' => 'adoption'],
            ],
            'social' => [
                ['name' => 'Partisipasi Warga Lapangan', 'target_value' => '100 Warga / 20 KK', 'current_value' => '0', 'unit' => 'partisipan', 'category' => 'impact'],
                ['name' => 'Kader Komunitas Terlatih', 'target_value' => '10 Kader Mandiri', 'current_value' => '0', 'unit' => 'orang', 'category' => 'sustainability'],
            ],
            'competition' => [
                ['name' => 'Skor Simulasi Rubrik Penilaian', 'target_value' => '85.0 / 100', 'current_value' => '0', 'unit' => 'skor', 'category' => 'rubric'],
                ['name' => 'Kelengkapan Dokumen Administrasi', 'target_value' => '100%', 'current_value' => '25%', 'unit' => '%', 'category' => 'compliance'],
            ],
            default => [
                ['name' => 'Capaian Output Kunci', 'target_value' => '100%', 'current_value' => '0%', 'unit' => '%', 'category' => 'outcome'],
            ],
        };

        foreach ($metrics as $metric) {
            ProjectMetric::create([
                'project_id' => $project->id,
                'name' => $metric['name'],
                'target_value' => $metric['target_value'],
                'current_value' => $metric['current_value'],
                'unit' => $metric['unit'],
                'category' => $metric['category'],
                'status' => 'in_progress',
            ]);
        }
    }

    /**
     * Complete operational definitions for the 4 core methodologies.
     */
    public function getDefaultTemplatesData(): array
    {
        return [
            // ==========================================
            // 1. RISET (RESEARCH WORKFLOW)
            // ==========================================
            [
                'code' => 'research_scientific',
                'name' => 'Scientific Research Workflow',
                'category' => 'research',
                'framework' => 'Scientific Method, Novelty & Dissemination',
                'description' => 'Tahapan riset sistematis yang menjaga keterlacakan ilmiah dari identifikasi fenomena, kajian kebaruan, protokol eksperimen, hingga publikasi jurnal.',
                'icon' => 'FlaskConical',
                'is_default' => true,
                'stages' => [
                    [
                        'name' => '01. Identifikasi Masalah & Fenomena',
                        'description' => 'Tentukan fenomena, urgensi, target objek, dan kesenjangan kondisi saat ini vs ideal.',
                        'tasks' => [
                            [
                                'title' => 'Perumusan Masalah, Urgensi & Target Objek',
                                'description' => 'Mendefinisikan fenomena spesifik dan kesenjangan nyata yang menjadi alasan ilmiah pelaksanaan riset.',
                                'instructions' => "1. Amati dan catat fenomena lapangan/teoretis yang menunjukkan adanya masalah.\n2. Rumuskan 1-3 pertanyaan penelitian (Research Questions) yang spesifik dan terukur.\n3. Uraikan urgensi mengapa penelitian ini harus dilakukan sekarang dan siapa penerima manfaatnya.",
                                'output_deliverable' => 'Draft Rumusan Masalah, Latar Belakang & Bukti Awal',
                                'priority' => 'high',
                                'weight' => 2,
                                'checklists' => [
                                    'Fenomena riil dan kesenjangan kondisi terdefinisi jelas',
                                    'Research question terformulasikan secara spesifik',
                                    'Bukti empiris awal atau data lapangan terdokumentasi',
                                ],
                                'extension_fields' => [
                                    'research_question' => 'Apa pengaruh atau mekanisme utama yang diselidiki?',
                                    'hypothesis' => 'Dugaan awal peneliti terhadap hubungan variabel',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '02. Kajian Literatur & Kebaruan (Novelty)',
                        'description' => 'Kumpulkan studi terdahulu, petakan perbandingan metode, identifikasi research gap, dan tegaskan kebaruan.',
                        'tasks' => [
                            [
                                'title' => 'Penyusunan Matriks Literatur & Pemetaan Riset Terdahulu',
                                'description' => 'Mengekstraksi artikel ilmiah bereputasi untuk menemukan kelebihan, limitasi, dan celah penelitian.',
                                'instructions' => "1. Cari artikel ilmiah relevan dari indeks Scopus / SINTA / PubMed / IEEE.\n2. Masukkan ke matriks literatur: Penulis, Tahun, Metode, Sampel, dan Temuan Kunci.\n3. Analisis limitasi dari studi-studi terdahulu untuk merumuskan research gap.",
                                'output_deliverable' => 'Matriks Kajian Literatur & Peta State-of-the-Art',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Minimal 10-15 artikel jurnal bereputasi 5 tahun terakhir',
                                    'Research gap teridentifikasi dan tertulis gamblang',
                                    'Kebaruan (novelty) dan kontribusi ilmiah dirumuskan tegas',
                                ],
                                'reference_attachments' => [
                                    ['title' => 'Panduan & Template Matriks Literatur (Excel)', 'type' => 'template', 'url' => 'https://drive.google.com'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '03. Desain Penelitian & Protokol',
                        'description' => 'Pilih metodologi, tentukan variabel, instrumen, sampel, serta rencana analisis data.',
                        'tasks' => [
                            [
                                'title' => 'Penyusunan Protokol Desain Penelitian & Instrumen',
                                'description' => 'Menetapkan rancangan kualitatif, kuantitatif, eksperimen laboratorium, atau DSR.',
                                'instructions' => "1. Tetapkan pendekatan penelitian yang paling tepat menjawab research question.\n2. Definisikan variabel bebas, terikat, dan kontrol (jika kuantitatif/eksperimen).\n3. Rancang instrumen pengambilan data (kuesioner, sensor, pedoman wawancara, dsb).\n4. Siapkan persetujuan etik / stage-gate clearance jika melibatkan subjek manusia.",
                                'output_deliverable' => 'Dokumen Metodologi Riset & Instrumen Tervalidasi',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Pendekatan dan desain metodologi disetujui tim/pembimbing',
                                    'Instrumen pengambilan data lolos uji validitas/kalibrasi',
                                    'Protokol keselamatan laboratorium atau etika riset terpenuhi',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '04. Pengumpulan Data & Eksperimen',
                        'description' => 'Jalankan protokol, kumpulkan dataset, catat anomali, dan dokumentasikan perubahan metode.',
                        'tasks' => [
                            [
                                'title' => 'Eksekusi Eksperimen & Pencatatan Logbook Lapangan',
                                'description' => 'Menjalankan pengumpulan data sesuai protokol dengan pencatatan anomali secara disiplin.',
                                'instructions' => "1. Lakukan pengumpulan data sesuai protokol baku secara berulang (replikasi).\n2. Catat seluruh parameter kondisi pengujian di logbook penelitian harian.\n3. Dokumentasikan segera jika ada anomali atau modifikasi langkah di tengah proses.",
                                'output_deliverable' => 'Dataset Mentah (Raw Data) & Logbook Eksperimen',
                                'priority' => 'high',
                                'weight' => 4,
                                'checklists' => [
                                    'Jumlah sampel / replikasi memenuhi batas statistik minimal',
                                    'Dataset mentah disimpan dengan format baku dan ter-backup',
                                    'Log anomali atau deviasi prosedur tercatat lengkap',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '05. Analisis Temuan & Validasi',
                        'description' => 'Analisis data, evaluasi validitas/reliabilitas, uji signifikansi, dan visualisasi hasil.',
                        'tasks' => [
                            [
                                'title' => 'Analisis Statistik / Tematik & Visualisasi Hasil',
                                'description' => 'Mengolah data mentah menjadi wawasan ilmiah yang menjawab pertanyaan penelitian.',
                                'instructions' => "1. Lakukan pengolahan data menggunakan metode yang ditetapkan (SPSS, R, Python, NVivo).\n2. Buat visualisasi grafik, tabel komparasi, atau diagram alur.\n3. Bahas keselarasan temuan terhadap hipotesis dan teori yang dikaji pada tahap 02.",
                                'output_deliverable' => 'Laporan Hasil Analisis, Grafik Komparasi & Pembahasan',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Uji signifikansi statistik atau triangulasi data tuntas',
                                    'Grafik dan visualisasi data memiliki resolusi tinggi dan label jelas',
                                    'Pembahasan mengaitkan temuan dengan literatur terdahulu',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '06. Publikasi & Diseminasi',
                        'description' => 'Susun manuskrip, pilih venue/jurnal, submit, tangani peer review, dan sebarkan dampak.',
                        'tasks' => [
                            [
                                'title' => 'Penyusunan Manuskrip & Proses Submission Jurnal',
                                'description' => 'Menulis naskah ilmiah sesuai gaya selingkung jurnal sasaran dan merespons reviewer.',
                                'instructions' => "1. Susun manuskrip mengikuti IMRAD (Introduction, Methods, Results, and Discussion).\n2. Lakukan cek plagiarisme/similarity dengan batas toleransi institusi.\n3. Submit ke jurnal target dan simpan bukti acknowledgement serta respons revisi reviewer.",
                                'output_deliverable' => 'Draft Manuskrip, Bukti Submission, atau Publikasi Terbit',
                                'priority' => 'medium',
                                'weight' => 3,
                                'checklists' => [
                                    'Format manuskrip 100% mematuhi Author Guidelines',
                                    'Hasil cek similaritas (Turnitin) < 20%',
                                    'Bukti submission resmi atau feedback peer-review terdokumentasi',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ==========================================
            // 2. PRODUK (PRODUCT DEVELOPMENT WORKFLOW)
            // ==========================================
            [
                'code' => 'product_agile_hcd',
                'name' => 'Product Development Workflow (HCD + Agile)',
                'category' => 'product',
                'framework' => 'HCD + Agile Sprint + QA Lifecycle',
                'description' => 'Menggabungkan Human-Centered Design untuk validasi empati pengguna, Agile Sprint untuk pengembangan iteratif, dan QA untuk keandalan rilis produk.',
                'icon' => 'Layers',
                'is_default' => true,
                'stages' => [
                    [
                        'name' => '01. Discovery & Validasi Masalah (HCD)',
                        'description' => 'Eksplorasi kebutuhan pengguna, pemetaan pain points, dan validasi peluang pasar.',
                        'tasks' => [
                            [
                                'title' => 'Riset Pengguna, Wawancara Empati & Persona',
                                'description' => 'Memahami realitas pengguna sesungguhnya sebelum menulis sebaris kode atau membuat desain.',
                                'instructions' => "1. Wawancarai minimal 5-10 calon pengguna potensial di lingkungan aslinya.\n2. Petakan pekerjaan yang ingin diselesaikan (*Jobs-to-be-Done*), *pains*, dan *gains*.\n3. Susun User Persona representatif dan ringkasan problem validation.",
                                'output_deliverable' => 'Dokumen User Persona, Empathy Map & Pain Point Matrix',
                                'priority' => 'high',
                                'weight' => 2,
                                'checklists' => [
                                    'Wawancara pengguna nyata dengan bukti transkrip/catatan',
                                    'Top 3 pain point utama tervalidasi bukan sekadar asumsi',
                                    'User Persona selesai dan disepakati tim',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '02. Define & Spesifikasi Solusi (PRD)',
                        'description' => 'Rancang requirement, arsitektur teknis, user flow, dan desain wireframe/mockup.',
                        'tasks' => [
                            [
                                'title' => 'Penyusunan PRD, User Flow & Wireframe Mockup',
                                'description' => 'Menerjemahkan kebutuhan pengguna menjadi spesifikasi teknis dan desain antarmuka solusi.',
                                'instructions' => "1. Tulis Product Requirement Document (PRD) yang memuat cakupan fitur MVP 1.\n2. Gambar arsitektur sistem (Database, Backend API, Frontend / Komponen Hardware).\n3. Buat wireframe/mockup interaktif alur utama (*happy path*).",
                                'output_deliverable' => 'PRD Lengkap, Diagram Arsitektur & Link Desain Figma',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Cakupan MVP 1 dikunci dan disepakati (hindari scope creep)',
                                    'User flow dari onboarding sampai *core action* jelas',
                                    'Spesifikasi teknis dan dependensi stack tervalidasi',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '03. Build MVP (Sprint Development)',
                        'description' => 'Membangun fitur minimum yang esensial untuk menguji asumsi terpenting solusi.',
                        'tasks' => [
                            [
                                'title' => 'Sprint 1: Pengembangan Fondasi & Fitur Inti MVP 1',
                                'description' => 'Mengembangkan fungsionalitas inti solusi agar dapat dicoba langsung oleh pengguna.',
                                'instructions' => "1. Setup repositori, boilerplate, dan struktur basis data.\n2. Implementasikan fitur terpenting yang menjadi pembeda utama produk.\n3. Jalankan automated test dasar untuk memastikan reliabilitas fitur inti.",
                                'output_deliverable' => 'Working Build / Prototipe Berfungsi (MVP v1.0)',
                                'priority' => 'high',
                                'weight' => 4,
                                'checklists' => [
                                    'Fitur utama dapat dioperasikan dari awal hingga akhir',
                                    'Repositori kode bersih dan terdokumentasi (README)',
                                    'Build dapat dijalankan di lingkungan staging/perangkat uji',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '04. Test & Validate (QA & Usability)',
                        'description' => 'Uji usability pada pengguna nyata, uji performa, pengujian bug, dan keamanan.',
                        'tasks' => [
                            [
                                'title' => 'Usability Testing & Audit Kualitas Teknis (QA)',
                                'description' => 'Memvalidasi apakah pengguna bisa memakai produk dengan lancar tanpa hambatan kritis.',
                                'instructions' => "1. Berikan skenario tugas kepada pengguna penguji tanpa dibantu.\n2. Catat waktu penyelesaian, kebingungan antarmuka, dan tingkat kegagalan.\n3. Lakukan stress test atau pengujian fungsional untuk menemukan bug/error.",
                                'output_deliverable' => 'Laporan Usability Testing & Daftar Bug Tuntas',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Sesi uji usability dengan minimal 5 pengguna selesai',
                                    'Semua blocker / critical bugs berhasil diperbaiki',
                                    'Skor System Usability Scale (SUS) terukur',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '05. Release & Measure (Pilot Launch)',
                        'description' => 'Rilis produk ke audiens terbatas, ukur adopsi, dan rekam feedback nyata.',
                        'tasks' => [
                            [
                                'title' => 'Peluncuran Pilot Terbatas & Pemantauan Metrik Adopsi',
                                'description' => 'Menyebarkan produk ke pengguna pilot dan memantau analitik penggunaan secara real-time.',
                                'instructions' => "1. Deploy versi stabil ke server produksi atau serahkan unit hardware ke user pilot.\n2. Pasang sistem analitik / log monitoring untuk memantau aktivitas.\n3. Kumpulkan masukan spontan melalui form umpan balik atau wawancara singkat.",
                                'output_deliverable' => 'Dashboard Metrik Penggunaan & Log Masukan Pilot',
                                'priority' => 'medium',
                                'weight' => 3,
                                'checklists' => [
                                    'Produk aktif digunakan oleh target audiens pilot',
                                    'Metrik retensi / penyelesaian tugas terpantau stabil',
                                    'Umpan balik langsung tercatat di jurnal/logbook',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '06. Iterate / Scale / Enhance',
                        'description' => 'Perbaiki kelemahan MVP, lanjutkan ke iterasi versi berikutnya (MVP 2) atau tingkatkan skala.',
                        'tasks' => [
                            [
                                'title' => 'Evaluasi Feedback Pilot & Perencanaan Roadmap MVP 2',
                                'description' => 'Menganalisis hasil pilot untuk menentukan apakah produk perlu pivot, perbaikan, atau scale-up.',
                                'instructions' => "1. Evaluasi apakah hipotesis nilai produk terbukti benar berdasarkan data pilot.\n2. Prioritaskan perbaikan fitur berdasar tingkat dampak vs usaha (*Impact vs Effort Matrix*).\n3. Susun daftar backlog untuk pengembangan iterasi berikutnya.",
                                'output_deliverable' => 'Rencana Roadmap MVP 2 & Keputusan Strategis Produk',
                                'priority' => 'medium',
                                'weight' => 2,
                                'checklists' => [
                                    'Analisis kesenjangan harapan vs kenyataan terdokumentasi',
                                    'Product backlog versi berikutnya telah diprioritaskan',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ==========================================
            // 3. SOSIAL (COMMUNITY DEVELOPMENT WORKFLOW)
            // ==========================================
            [
                'code' => 'social_abcd_hcd',
                'name' => 'Community Development Workflow (ABCD + HCD)',
                'category' => 'social',
                'framework' => 'ABCD, HCD & Theory of Change',
                'description' => 'Pendekatan pemberdayaan masyarakat yang berakar pada pemetaan aset lokal (ABCD), empati partisipatif warga (HCD), serta keberlanjutan dampak mandiri.',
                'icon' => 'Users',
                'is_default' => true,
                'stages' => [
                    [
                        'name' => '01. Community Listening & Konteks Lapangan',
                        'description' => 'Mendengar aspirasi, memetakan kelompok sasaran, dan memahami konteks kearifan lokal.',
                        'tasks' => [
                            [
                                'title' => 'Observasi Partisipatif & Pemetaan Konteks Sosial',
                                'description' => 'Mendalami dinamika sosial, tantangan warga, dan tokoh kunci komunitas.',
                                'instructions' => "1. Kunjungi lokasi komunitas dan bangun silaturahmi dengan tokoh informal/formal.\n2. Amati keseharian kelompok rentan dan dengarkan langsung keluhan warga.\n3. Catat norma lokal yang harus dihormati agar program tidak memicu resistensi.",
                                'output_deliverable' => 'Catatan Lapangan & Profil Konteks Komunitas',
                                'priority' => 'high',
                                'weight' => 2,
                                'checklists' => [
                                    'Silaturahmi dengan minimal 3 tokoh kunci komunitas',
                                    'Profil target sasaran dan kelompok rentan terpetakan',
                                    'Catatan observasi lapangan tersimpan di jurnal',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '02. Asset Mapping (Metodologi ABCD)',
                        'description' => 'Memetakan keterampilan, tokoh, fasilitas, sumber daya alam, dan jejaring yang sudah dimiliki warga.',
                        'tasks' => [
                            [
                                'title' => 'Inventarisasi Aset & Potensi Kekuatan Komunitas',
                                'description' => 'Mengidentifikasi apa yang sudah dimiliki komunitas daripada hanya berfokus pada apa yang kurang.',
                                'instructions' => "1. Petakan 5 jenis modal: Sumber Daya Manusia (keterampilan warga), Fisik (balai, alat), Alam (lahan, air), Finansial (iuran warga), dan Sosial (gotong royong, arisan).\n2. Susun matriks aset komunitas yang siap dimobilisasi bersama.",
                                'output_deliverable' => 'Matriks Peta Aset Komunitas (ABCD Framework)',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Daftar keterampilan individu warga teridentifikasi',
                                    'Aset kelembagaan lokal (karang taruna, PKK, dll) tercatat',
                                    'Potensi sumber daya lokal terdata dan terverifikasi',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '03. Co-Design Bersama Komunitas',
                        'description' => 'Menyusun masalah prioritas dan merancang solusi secara partisipatif bersama warga.',
                        'tasks' => [
                            [
                                'title' => 'Musyawarah Rembug Solusi Partisipatif',
                                'description' => 'Memfasilitasi forum warga agar solusi dirumuskan dan dimiliki oleh mereka sendiri.',
                                'instructions' => "1. Selenggarakan forum diskusi terarah (FGD) atau rembug desa.\n2. Sepakati satu masalah prioritas yang paling mendesak untuk diselesaikan bersama.\n3. Rancang kegiatan secara kolaboratif dengan memanfaatkan aset lokal tahap 02.",
                                'output_deliverable' => 'Berita Acara Rembug Warga & Kanvas Solusi Partisipatif',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Keterlibatan aktif perwakilan warga dalam rembug',
                                    'Solusi lahir dari aspirasi bersama bukan paksaan luar',
                                    'Pembagian peran awal disepakati',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '04. Planning & Mobilisasi Sumber Daya',
                        'description' => 'Menentukan indikator dampak, RAB gotong royong, mitra, dan peran warga.',
                        'tasks' => [
                            [
                                'title' => 'Penyusunan Rencana Aksi, RAB & Mobilisasi Relawan',
                                'description' => 'Mempersiapkan segala kebutuhan operasional, pembagian tugas warga, dan jadwal kegiatan.',
                                'instructions' => "1. Rumuskan Teori Perubahan (*Theory of Change*): Aktivitas -> Output -> Outcome -> Impact.\n2. Buat Rencana Anggaran Biaya (RAB) transparan dengan memprioritaskan swadaya lokal.\n3. Bentuk tim pelaksana gotong royong dengan kader lokal sebagai penggerak utama.",
                                'output_deliverable' => 'Rencana Operasional, RAB Transparan & Jadwal Aksi',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'RAB disetujui bersama dan bebas konflik kepentingan',
                                    'Kader penanggung jawab lokal terkonfirmasi',
                                    'Indikator keberhasilan dampak terukur',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '05. Pelaksanaan Aksi Lapangan',
                        'description' => 'Melaksanakan kegiatan dengan pembagian peran yang jelas serta dokumentasi partisipasi.',
                        'tasks' => [
                            [
                                'title' => 'Eksekusi Program Bersama & Pencatatan Partisipasi',
                                'description' => 'Menjalankan rangkaian aksi dengan melibatkan warga secara aktif di lapangan.',
                                'instructions' => "1. Jalankan aksi lapangan secara berkala sesuai rencana kerja.\n2. Catat presensi dan jumlah keterlibatan warga (laki-laki, perempuan, pemuda, lansia).\n3. Dokumentasikan foto/video proses kegiatan secara transparan.",
                                'output_deliverable' => 'Dokumentasi Aksi Lapangan & Lembar Kehadiran Warga',
                                'priority' => 'high',
                                'weight' => 4,
                                'checklists' => [
                                    'Kegiatan terlaksana sesuai standar keselamatan dan etika',
                                    'Tingkat partisipasi warga terdokumentasi akurat',
                                    'Pengeluaran anggaran tercatat sesuai bukti riil',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '06. Monitoring & Evaluasi Dampak (M&E)',
                        'description' => 'Mengukur output, outcome, perubahan perilaku, efisiensi biaya, dan umpan balik warga.',
                        'tasks' => [
                            [
                                'title' => 'Pengukuran Outcome Nyata & Evaluasi Pasca-Aksi',
                                'description' => 'Menilai dampak nyata program terhadap peningkatan taraf hidup atau perubahan perilaku komunitas.',
                                'instructions' => "1. Evaluasi luaran fisik/kegiatan vs target awal yang direncanakan.\n2. Wawancarai penerima manfaat untuk mengukur perubahan riil yang dirasakan.\n3. Hitung efisiensi pemanfaatan dana program.",
                                'output_deliverable' => 'Laporan Hasil Monitoring & Evaluasi Dampak Sosial',
                                'priority' => 'medium',
                                'weight' => 3,
                                'checklists' => [
                                    'Data outcome terukur (bukan hanya daftar hadir)',
                                    'Umpan balik evaluasi warga dicatat secara obyektif',
                                    'Laporan pertanggungjawaban dana dipublikasikan terbuka',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '07. Keberlanjutan & Handover Komunitas',
                        'description' => 'Memperkuat kepemilikan lokal, kader mandiri, sumber dana rutin, dan alih kelola.',
                        'tasks' => [
                            [
                                'title' => 'Transfer Tata Kelola Mandiri & Pelembagaan Kader',
                                'description' => 'Memastikan kegiatan terus berjalan mandiri tanpa ketergantungan pada inisiator awal.',
                                'instructions' => "1. Lakukan pelatihan intensif penguatan kapasitas kepada kader penggerak lokal.\n2. Serahkan seluruh dokumen operasional, aset, dan tata cara pemeliharaan program.\n3. Sepakati mekanisme pengawasan rutin antar warga.",
                                'output_deliverable' => 'Berita Acara Alih Kelola & SOP Keberlanjutan Warga',
                                'priority' => 'medium',
                                'weight' => 2,
                                'checklists' => [
                                    'Kader komunitas mampu menjalankan kegiatan secara mandiri',
                                    'Mekanisme pembiayaan / perawatan swadaya terbentuk',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ==========================================
            // 4. LOMBA (COMPETITION WORKFLOW)
            // ==========================================
            [
                'code' => 'competition_lifecycle',
                'name' => 'Competition Preparation Workflow',
                'category' => 'competition',
                'framework' => 'Rubric-Driven Strategy & Submission Engine',
                'description' => 'Kerangka kerja persiapan lomba akademik/inovasi (PKM, Hackathon, Business Plan, Karya Tulis) berbasis pemenuhan rubrik berbobot dan checklist presisi.',
                'icon' => 'Trophy',
                'is_default' => true,
                'stages' => [
                    [
                        'name' => '01. Opportunity Discovery & Eligibilitas',
                        'description' => 'Cari lomba, periksa penyelenggara, kategori, syarat eligibilitas, dan manfaat strategis.',
                        'tasks' => [
                            [
                                'title' => 'Skrining Panduan Resmi, Batas Waktu & Kriteria Peserta',
                                'description' => 'Memastikan seluruh anggota tim memenuhi syarat dan timeline lomba tercatat jelas.',
                                'instructions' => "1. Unduh buku panduan resmi lomba (*rulebook/guidebook*) dari situs resmi penyelenggara.\n2. Periksa batas usia, status keanggotaan (mahasiswa aktif, asal instansi, dsb).\n3. Catat milestone deadline penting: registrasi, submisi berkas, pengumuman, dan pitching.",
                                'output_deliverable' => 'Lembar Ringkasan Panduan & Matriks Eligibilitas Tim',
                                'priority' => 'high',
                                'weight' => 2,
                                'checklists' => [
                                    'Buku panduan resmi tersimpan di lampiran referensi',
                                    'Eligibilitas seluruh anggota tim terverifikasi lolos syarat',
                                    'Tenggat akhir pengiriman dicatat dengan pengingat waktu',
                                ],
                                'extension_fields' => [
                                    'organizer' => 'Penyelenggara Resmi Kompetisi',
                                    'registration_url' => 'https://portal-lomba.id/register',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '02. Analisis Kebutuhan & Rubrik Penilaian',
                        'description' => 'Ekstrak panduan, rubrik penilaian, format berkas, aturan tim, dan ketentuan administrasi.',
                        'tasks' => [
                            [
                                'title' => 'Ekstraksi Rubrik Juri & Penentuan Bobot Nilai',
                                'description' => 'Membedah kriteria penilaian juri agar strategi pembuatan karya fokus pada poin terbesar.',
                                'instructions' => "1. Rinci seluruh aspek penilaian (Inovasi, Dampak, Metodologi/Kelayakan, Format, Presentasi).\n2. Cantumkan persentase bobot masing-masing aspek.\n3. Susun checklist kelengkapan administrasi wajib yang menggugurkan peserta jika tidak ada.",
                                'output_deliverable' => 'Matriks Bobot Rubrik Penilaian & Checklist Administrasi',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Bobot persentase rubrik juri terpetakan 100%',
                                    'Daftar berkas administrasi wajib tersusun',
                                    'Aturan format margin, font, dan halaman dicatat rapi',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '03. Strategi & Konsep Pembeda (Novelty)',
                        'description' => 'Pilih ide, rumuskan diferensiasi, susun strategi menjawab rubrik, dan tetapkan peran tim.',
                        'tasks' => [
                            [
                                'title' => 'Perumusan Gagasan Pembeda & Strategi Nilai Maksimal',
                                'description' => 'Merumuskan keunggulan komparatif karya dibanding karya umum lainnya.',
                                'instructions' => "1. Brainstorm ide yang paling tajam menjawab kriteria bobot terbesar pada rubrik.\n2. Susun *Unique Value Proposition* (UVP) gagasan dalam 1 kalimat penarik perhatian.\n3. Bagi peran spesifik pengerjaan anggota tim (Leader, Penulis, Desain/Dev, Presenter).",
                                'output_deliverable' => 'Dokumen Konsep Gagasan, UVP & Pembagian Peran Tim',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Ide memiliki diferensiasi jelas dan nilai kebaruan kuat',
                                    'Gagasan menargetkan poin tertinggi pada rubrik inovasi & dampak',
                                    'Setiap anggota tim memegang tanggung jawab komponen karya',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '04. Pengembangan Berkas & Karya',
                        'description' => 'Buat proposal, paper, pitch deck, prototipe, video, atau dokumen sesuai ketentuan.',
                        'tasks' => [
                            [
                                'title' => 'Penyusunan Proposal / Paper & Pembuatan Prototipe/Slide',
                                'description' => 'Menulis naskah karya dan materi presentasi dengan kualitas tinggi.',
                                'instructions' => "1. Tulis naskah karya sesuai sistematika bab panduan resmi tanpa toleransi salah format.\n2. Buat pitch deck visual menarik atau prototipe/video simulasi pendukung karya.\n3. Masukkan data pendukung riil, kutipan ilmiah, atau skema rancangan visual.",
                                'output_deliverable' => 'Draft Lengkap Proposal Karya, Pitch Deck & Lampiran',
                                'priority' => 'high',
                                'weight' => 4,
                                'checklists' => [
                                    'Struktur naskah 100% mematuhi panduan penulisan',
                                    'Pitch deck atau prototipe siap uji',
                                    'Seluruh lampiran pendukung terkumpul lengkap',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '05. Review & Simulasi Penilaian Juri',
                        'description' => 'Pemeriksaan substansi, simulasi penilaian berdasarkan bobot rubrik, proofreading.',
                        'tasks' => [
                            [
                                'title' => 'Simulasi Skoring Rubrik & Review Mentor/Pembimbing',
                                'description' => 'Menghitung estimasi nilai berdasarkan rubrik juri untuk mendeteksi kelemahan sebelum dikirim.',
                                'instructions' => "1. Lakukan audit mandiri dan minta mentor menilai karya menggunakan lembar rubrik tahap 02.\n2. Hitung total nilai tertimbang simulasi (misal: Inovasi 30% x 90, Kelayakan 25% x 85, dll).\n3. Perbaiki kelemahan pada aspek yang masih memiliki skor rendah.",
                                'output_deliverable' => 'Lembar Hasil Simulasi Skor & Catatan Revisi Mentor',
                                'priority' => 'high',
                                'weight' => 3,
                                'checklists' => [
                                    'Review substansi oleh dosen/mentor pembimbing tuntas',
                                    'Skor simulasi internal mencapai target passing grade (> 85%)',
                                    'Proofreading tata bahasa dan ejaan selesai tanpa typo',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '06. Finalisasi & Pengiriman (Submission)',
                        'description' => 'Finalisasi berkas, periksa format, kirim melalui kanal resmi, dan simpan bukti submission.',
                        'tasks' => [
                            [
                                'title' => 'Pengecekan Akhir Format & Pengiriman Berkas Resmi',
                                'description' => 'Mengirim berkas sebelum batas waktu dan mengamankan bukti tanda terima pendaftaran.',
                                'instructions' => "1. Cek ukuran file (MB), format (PDF), nama file (sesuai format panitia), dan kelengkapan tanda tangan.\n2. Unggah berkas ke formulir/portal resmi lomba sebelum H-1 untuk menghindari server down.\n3. Unduh dan simpan bukti konfirmasi submission resmi (screenshot / surel tanda terima).",
                                'output_deliverable' => 'Bukti Tanda Terima Pengiriman Resmi & Berkas Final Terkirim',
                                'priority' => 'high',
                                'weight' => 4,
                                'checklists' => [
                                    'Semua persyaratan administrasi dan tanda tangan lengkap',
                                    'File terunggah sukses di portal resmi sebelum batas waktu',
                                    'Bukti screenshot/email submission tersimpan aman di berkas luaran',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '07. Pitching, Tanya Jawab & Tindak Lanjut',
                        'description' => 'Siapkan presentasi, simulasi tanya jawab dewan juri, dan tindak lanjut hasil lomba.',
                        'tasks' => [
                            [
                                'title' => 'Gladi Bersih Presentasi, Q&A Juri & Monitoring Pengumuman',
                                'description' => 'Menyiapkan performa tim di hadapan dewan juri dan menyusun strategi jawaban tajam.',
                                'instructions' => "1. Latih penyampaian slide dengan mematuhi batas durasi waktu presentasi (misal: 7-10 menit).\n2. Susun daftar 10 pertanyaan tersulit yang mungkin diajukan juri beserta jawaban taktisnya.\n3. Pantau pengumuman pemenang dan dokumentasikan masukan juri untuk perbaikan karya ke depan.",
                                'output_deliverable' => 'Materi Presentasi Final, Lembar Antisipasi Q&A & Catatan Juri',
                                'priority' => 'medium',
                                'weight' => 2,
                                'checklists' => [
                                    'Gladi bersih durasi presentasi tepat waktu tuntas',
                                    'Pembagian peran menjawab pertanyaan juri disepakati tim',
                                    'Hasil akhir dan masukan juri terdokumentasi',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}


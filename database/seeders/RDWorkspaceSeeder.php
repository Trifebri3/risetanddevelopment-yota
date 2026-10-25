<?php

namespace Database\Seeders;

use App\Models\BudgetItem;
use App\Models\Expense;
use App\Models\Idea;
use App\Models\JournalEntry;
use App\Models\Milestone;
use App\Models\Output;
use App\Models\Phase;
use App\Models\Project;
use App\Models\PublicFeedback;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RDWorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        // Create or find default user
        $user = User::firstOrCreate(
            ['email' => 'researcher@rdworkspace.id'],
            [
                'name' => 'Dr. Aris Setiawan, M.T.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // ==========================================
        // 1. PROJECT: RESEARCH DEVELOPMENT
        // ==========================================
        $researchProject = Project::create([
            'user_id' => $user->id,
            'title' => 'Pengembangan Model AI Deteksi Dini Penyakit Daun Padi Berbasis Computer Vision',
            'slug' => 'ai-deteksi-penyakit-padi',
            'description' => 'Penelitian terapan untuk mengidentifikasi 4 jenis penyakit utama pada daun tanaman padi (Blast, Bacterial Blight, Brown Spot, Tungro) secara real-time melalui kamera ponsel menggunakan arsitektur lightweight YOLOv8 dan MobileNet.',
            'mode' => 'research',
            'problem_statement' => 'Keterlambatan diagnosis penyakit padi di tingkat petani menyebabkan kerugian panen rata-rata 25-35%. Metode inspeksi visual manual lambat dan rentan bias.',
            'target_outcome' => 'Model deep learning dengan akurasi mAP@50 > 92% dan latency < 150ms pada perangkat mobile, dipublikasikan pada jurnal bereputasi Q2/Sinta 1.',
            'status' => 'active',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-15',
            'progress_percent' => 65,
            'share_token' => 'rice-vision-ai-demo-2026',
            'is_public' => true,
            'public_settings' => [
                'show_overview' => true,
                'show_milestones' => true,
                'show_journal' => true,
                'show_outputs' => true,
                'show_budget_summary' => true,
                'allow_feedback' => true,
            ],
        ]);

        $phase1 = Phase::create(['project_id' => $researchProject->id, 'name' => 'Fase 1: Literature Review & Pengumpulan Dataset', 'order' => 1]);
        $phase2 = Phase::create(['project_id' => $researchProject->id, 'name' => 'Fase 2: Preprocessing, Augmentasi & Pelatihan Model', 'order' => 2]);
        $phase3 = Phase::create(['project_id' => $researchProject->id, 'name' => 'Fase 3: Validasi Lapangan & Penyusunan Manuskrip', 'order' => 3]);

        $m1 = Milestone::create([
            'project_id' => $researchProject->id,
            'phase_id' => $phase1->id,
            'title' => 'Koleksi 2.500 Sampel Foto Daun Padi Lapangan',
            'due_date' => '2026-08-30',
            'status' => 'completed',
            'definition_of_done' => 'Dataset tersertifikasi oleh ahli proteksi tanaman dan beranotasi bounding box format YOLO.',
            'is_public' => true,
        ]);

        $m2 = Milestone::create([
            'project_id' => $researchProject->id,
            'phase_id' => $phase2->id,
            'title' => 'Pelatihan Model Baseline & Fine Tuning Arsitektur YOLOv8n',
            'due_date' => '2026-10-15',
            'status' => 'completed',
            'definition_of_done' => 'Model mencapai akurasi mAP@50 minimal 90% pada data pengujian validasi silang (cross-validation).',
            'is_public' => true,
        ]);

        $m3 = Milestone::create([
            'project_id' => $researchProject->id,
            'phase_id' => $phase3->id,
            'title' => 'Submission Manuskrip ke Jurnal Internasional',
            'due_date' => '2026-11-30',
            'status' => 'in_progress',
            'definition_of_done' => 'Naskah lengkap beserta data pendukung siap submit ke IEEE Access / Computers and Electronics in Agriculture.',
            'is_public' => true,
        ]);

        Task::create([
            'project_id' => $researchProject->id,
            'milestone_id' => $m1->id,
            'title' => 'Survei lapangan ke sentra sawah Subang & Karawang',
            'status' => 'done',
            'priority' => 'high',
            'due_date' => '2026-08-15',
            'weight' => 2,
        ]);
        Task::create([
            'project_id' => $researchProject->id,
            'milestone_id' => $m1->id,
            'title' => 'Anotasi 2.500 citra menggunakan Roboflow',
            'status' => 'done',
            'priority' => 'high',
            'due_date' => '2026-08-28',
            'weight' => 3,
        ]);
        Task::create([
            'project_id' => $researchProject->id,
            'milestone_id' => $m2->id,
            'title' => 'Eksperimen augmentasi data (rotasi, blur, exposure)',
            'status' => 'done',
            'priority' => 'medium',
            'due_date' => '2026-09-20',
            'weight' => 2,
        ]);
        Task::create([
            'project_id' => $researchProject->id,
            'milestone_id' => $m2->id,
            'title' => 'Hyperparameter tuning & evaluasi confusion matrix',
            'status' => 'done',
            'priority' => 'high',
            'due_date' => '2026-10-10',
            'weight' => 3,
        ]);
        Task::create([
            'project_id' => $researchProject->id,
            'milestone_id' => $m3->id,
            'title' => 'Pengujian offline inference pada smartphone Android mid-range',
            'status' => 'in_progress',
            'priority' => 'high',
            'due_date' => '2026-11-10',
            'weight' => 2,
        ]);
        Task::create([
            'project_id' => $researchProject->id,
            'milestone_id' => $m3->id,
            'title' => 'Finalisasi draf artikel & grafik komparasi akurasi',
            'status' => 'in_progress',
            'priority' => 'high',
            'due_date' => '2026-11-20',
            'weight' => 3,
        ]);

        JournalEntry::create([
            'project_id' => $researchProject->id,
            'user_id' => $user->id,
            'phase_id' => $phase2->id,
            'date' => '2026-10-12',
            'title' => 'Eksperimen Epoch 150 YOLOv8n: Akurasi mAP Tembus 93.4%',
            'activity' => 'Melakukan pengujian komputasi di GPU cluster dengan batch size 32, resolusi input 640x640, dan optimizer AdamW.',
            'results' => 'Model berhasil mendeteksi Blast dan Brown Spot dengan presisi 94.1%, recall 92.8%. Ukuran model terkompresi hanya 6.2 MB.',
            'challenges' => 'Sedikit false positive pada daun padi dengan gejala Tungro stadium awal karena kemiripan warna dengan defisiensi nitrogen.',
            'next_steps' => 'Menambahkan data augmentasi khusus defisiensi hara daun untuk membedakan secara spesifik.',
            'entry_type' => 'manual',
            'status' => 'finalized',
            'is_public' => true,
            'attachments' => [
                [
                    'name' => 'confusion_matrix_v2.png',
                    'caption' => 'Confusion Matrix klasifikasi 4 kelas penyakit padi (YOLOv8n)',
                    'file_type' => 'image',
                ],
            ],
        ]);

        JournalEntry::create([
            'project_id' => $researchProject->id,
            'user_id' => $user->id,
            'phase_id' => $phase1->id,
            'date' => '2026-08-25',
            'title' => 'Validasi Anotasi Bersama Pakar Tanaman Balitpa',
            'activity' => 'Diskusi verifikasi sampel citra bersama Dr. Ir. Hendra (peneliti penyakit tanaman Balai Besar Penelitian Tanaman Padi).',
            'results' => '120 citra ambigu telah dikoreksi label anotasi dan disepakati kriteria bounding box daun terserang.',
            'challenges' => 'Cuaca mendung saat pengambilan foto menyebabkan variasi pencahayaan tajam.',
            'next_steps' => 'Terapkan teknik normalisasi histogram warna sebelum feeding ke CNN/YOLO.',
            'entry_type' => 'manual',
            'status' => 'finalized',
            'is_public' => true,
        ]);

        $b1 = BudgetItem::create([
            'project_id' => $researchProject->id,
            'category' => 'Alat & Bahan',
            'item_name' => 'Sewa Cloud GPU Instance (NVIDIA A100 80GB - 150 Jam)',
            'qty' => 150,
            'unit' => 'jam',
            'estimated_unit_price' => 25000,
            'total_estimated_price' => 3750000,
            'procurement_status' => 'received',
        ]);
        $b2 = BudgetItem::create([
            'project_id' => $researchProject->id,
            'category' => 'Transportasi & Lapangan',
            'item_name' => 'Transportasi & Akomodasi Pengambilan Sampel Daun Sawah',
            'qty' => 3,
            'unit' => 'kunjungan',
            'estimated_unit_price' => 1500000,
            'total_estimated_price' => 4500000,
            'procurement_status' => 'received',
        ]);
        $b3 = BudgetItem::create([
            'project_id' => $researchProject->id,
            'category' => 'Jasa & Publikasi',
            'item_name' => 'Biaya Article Processing Charge (APC) Open Access',
            'qty' => 1,
            'unit' => 'artikel',
            'estimated_unit_price' => 12000000,
            'total_estimated_price' => 12000000,
            'procurement_status' => 'planned',
        ]);

        Expense::create([
            'project_id' => $researchProject->id,
            'budget_item_id' => $b1->id,
            'date' => '2026-09-15',
            'description' => 'Pembayaran Sewa Cloud Run GPU untuk Training Model Batch 1',
            'category' => 'Alat & Bahan',
            'amount' => 2450000,
            'notes' => 'Invoice #CR-88912 lunas via transfer virtual account.',
        ]);
        Expense::create([
            'project_id' => $researchProject->id,
            'budget_item_id' => $b2->id,
            'date' => '2026-08-20',
            'description' => 'BBM & Operasional Tim Lapangan Karawang - Subang',
            'category' => 'Transportasi & Lapangan',
            'amount' => 1420000,
            'notes' => 'Termasuk konsumsi petani pemandu lapangan.',
        ]);

        Output::create([
            'project_id' => $researchProject->id,
            'title' => 'Manuskrip: Real-time Rice Leaf Disease Identification using Optimized YOLOv8 Architecture on Edge Mobile Devices',
            'output_type' => 'paper',
            'status' => 'drafting',
            'target_date' => '2026-11-30',
            'venue_or_journal' => 'Computers and Electronics in Agriculture (Elsevier Q1)',
            'is_public' => true,
            'notes' => 'Draf bab metodologi dan hasil telah 80% selesai disusun.',
        ]);
        Output::create([
            'project_id' => $researchProject->id,
            'title' => 'Open Dataset: 2.500 Citra Daun Padi Teranotasi dengan Ground Truth Lapangan',
            'output_type' => 'dataset',
            'status' => 'published_done',
            'target_date' => '2026-09-01',
            'doi_or_url' => 'https://doi.org/10.5281/zenodo.riceleaf2026',
            'is_public' => true,
            'notes' => 'Telah diunggah di repositori Zenodo dengan lisensi CC-BY 4.0.',
        ]);

        PublicFeedback::create([
            'project_id' => $researchProject->id,
            'reviewer_name' => 'Prof. Dr. Budi Santoso',
            'reviewer_role' => 'Reviewer Ahli / Mentor',
            'comment' => 'Progres sangat memuaskan, kurva mAP sudah stabil di epoch 120 ke atas. Pastikan evaluasi perbandingan F1-score dengan Faster-RCNN dimasukkan di tabel pembahasan manuskrip.',
            'rating' => 5,
        ]);

        // ==========================================
        // 2. PROJECT: PRODUCT DEVELOPMENT
        // ==========================================
        $productProject = Project::create([
            'user_id' => $user->id,
            'title' => 'AgriNode: Smart IoT Soil Sensor & Irigasi Otomatis Bertenaga Surya',
            'slug' => 'agrinode-iot-smart-sensor',
            'description' => 'Pengembangan perangkat keras IoT portabel berdaya ultra-rendah untuk mengukur kelembaban tanah, suhu, dan konduktivitas elektrik (EC) dengan integrasi katup solenoid irigasi otomatis berbasis LoRaWAN.',
            'mode' => 'product',
            'problem_statement' => 'Sistem irigasi sawah konvensional menggunakan jadwal waktu tetap, membuang hingga 40% air dan pupuk saat tanah masih basah.',
            'target_outcome' => 'Prototipe MVP hardware tersertifikasi uji lapangan beroperasi tanpa isi ulang baterai selama minimal 6 bulan.',
            'status' => 'active',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-20',
            'progress_percent' => 45,
            'share_token' => 'agrinode-product-hardware-mvp',
            'is_public' => true,
            'public_settings' => [
                'show_overview' => true,
                'show_milestones' => true,
                'show_journal' => true,
                'show_outputs' => true,
                'show_budget_summary' => true,
                'allow_feedback' => true,
            ],
        ]);

        $pphase1 = Phase::create(['project_id' => $productProject->id, 'name' => 'Fase 1: Desain Skematik & Prototyping PCB v1.0', 'order' => 1]);
        $pphase2 = Phase::create(['project_id' => $productProject->id, 'name' => 'Fase 2: Firmware Low-Power & Uji Gateway LoRa', 'order' => 2]);

        $pm1 = Milestone::create([
            'project_id' => $productProject->id,
            'phase_id' => $pphase1->id,
            'title' => 'Fabrikasi 5 Unit PCB Board Prototipe v1.0',
            'due_date' => '2026-10-05',
            'status' => 'completed',
            'definition_of_done' => 'Komponen tersolder rapi dan lolos continuity test serta uji tegangan power rails 3.3V.',
            'is_public' => true,
        ]);
        $pm2 = Milestone::create([
            'project_id' => $productProject->id,
            'phase_id' => $pphase2->id,
            'title' => 'Konsumsi Daya Deep-Sleep di bawah 15 mikro-Ampere',
            'due_date' => '2026-11-15',
            'status' => 'in_progress',
            'definition_of_done' => 'Pengukuran dengan osiloskop / current shunt monitor memastikan node tidur dengan arus < 15uA.',
            'is_public' => true,
        ]);

        Task::create([
            'project_id' => $productProject->id,
            'milestone_id' => $pm1->id,
            'title' => 'Routing trace PCB pada KiCad & pesan di JLCPCB',
            'status' => 'done',
            'priority' => 'high',
            'due_date' => '2026-09-18',
            'weight' => 2,
        ]);
        Task::create([
            'project_id' => $productProject->id,
            'milestone_id' => $pm1->id,
            'title' => 'Soldering komponen SMD & sensor capacitive probe',
            'status' => 'done',
            'priority' => 'medium',
            'due_date' => '2026-10-02',
            'weight' => 2,
        ]);
        Task::create([
            'project_id' => $productProject->id,
            'milestone_id' => $pm2->id,
            'title' => 'Optimasi sleep routine mikrokontroler ESP32-C3 / STM32',
            'status' => 'in_progress',
            'priority' => 'high',
            'due_date' => '2026-11-05',
            'weight' => 3,
        ]);

        JournalEntry::create([
            'project_id' => $productProject->id,
            'user_id' => $user->id,
            'phase_id' => $pphase1->id,
            'date' => '2026-10-04',
            'title' => 'Soldering Selesai: Uji Daya Sensor & Kalibrasi Nilai Analog',
            'activity' => 'Perakitan komponen 5 papan PCB prototipe. Pengujian pembacaan ADC dari sensor kelembaban kapasitif pada kondisi tanah kering vs tanah basah jenuh air.',
            'results' => 'Diferensiasi tegangan analog terbaca jelas (kering: 2.8V, jenuh air: 1.1V). Linearisasi kurva berjalan mulus.',
            'challenges' => 'Kabel probe rentan terhadap interferensi elektromagnetik motor katup pompa air.',
            'next_steps' => 'Tambahkan optocoupler isolasi dan filter kapasitor 100nF pada jalur ADC.',
            'entry_type' => 'manual',
            'status' => 'finalized',
            'is_public' => true,
        ]);

        BudgetItem::create([
            'project_id' => $productProject->id,
            'category' => 'Alat & Bahan',
            'item_name' => 'Fabrikasi PCB Double Layer & Komponen SMD SMT (5 Unit)',
            'qty' => 5,
            'unit' => 'set',
            'estimated_unit_price' => 350000,
            'total_estimated_price' => 1750000,
            'procurement_status' => 'received',
        ]);
        BudgetItem::create([
            'project_id' => $productProject->id,
            'category' => 'Alat & Bahan',
            'item_name' => 'Solar Panel 5V 2W + Solar Charger IC CN3065 + LiFePO4 Battery',
            'qty' => 5,
            'unit' => 'unit',
            'estimated_unit_price' => 180000,
            'total_estimated_price' => 900000,
            'procurement_status' => 'ordered',
        ]);

        Output::create([
            'project_id' => $productProject->id,
            'title' => 'Prototipe Fisik AgriNode v1.0 (Enclosure IP67 + Solar Probe)',
            'output_type' => 'prototype',
            'status' => 'review',
            'target_date' => '2026-11-20',
            'is_public' => true,
            'notes' => 'Enclosure 3D print bahan PETG tahan sinar UV cuaca sawah.',
        ]);

        // ==========================================
        // 3. PROJECT: SOCIAL PROJECT DEVELOPMENT
        // ==========================================
        $socialProject = Project::create([
            'user_id' => $user->id,
            'title' => 'Desa Tani Digital: Pemberdayaan Petani Milenial & Akses Pasar Langsung',
            'slug' => 'desa-tani-digital-sukatani',
            'description' => 'Program pendampingan dan implementasi platform digital pencatatan panen serta pembentukan koperasi digital bagi 40 kelompok petani muda di Desa Sukatani, Jawa Barat.',
            'mode' => 'social',
            'problem_statement' => 'Ketergantungan petani muda pada tengkulak dengan rantai distribusi 4 tingkat menekan harga jual gabah hingga 30% di bawah harga acuan pemerintah.',
            'target_outcome' => 'Terbentuknya koperasi digital desa berbadan hukum dan peningkatan rata-rata margin pendapatan petani dampingan sebesar 20%.',
            'status' => 'active',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'progress_percent' => 70,
            'share_token' => 'desa-tani-digital-impact-2026',
            'is_public' => true,
            'public_settings' => [
                'show_overview' => true,
                'show_milestones' => true,
                'show_journal' => true,
                'show_outputs' => true,
                'show_budget_summary' => true,
                'allow_feedback' => true,
            ],
        ]);

        $sphase1 = Phase::create(['project_id' => $socialProject->id, 'name' => 'Fase 1: Asesmen Kebutuhan & Sosialisasi Komunitas', 'order' => 1]);
        $sphase2 = Phase::create(['project_id' => $socialProject->id, 'name' => 'Fase 2: Pelatihan Digital Literacy & Uji Coba Koperasi', 'order' => 2]);

        $sm1 = Milestone::create([
            'project_id' => $socialProject->id,
            'phase_id' => $sphase1->id,
            'title' => 'Pemetaan 45 Petani Milenial & Profil Lahan Garapan',
            'due_date' => '2026-08-10',
            'status' => 'completed',
            'definition_of_done' => 'Database petani lengkap dengan luas petak sawah dan komoditas tanam.',
            'is_public' => true,
        ]);
        $sm2 = Milestone::create([
            'project_id' => $socialProject->id,
            'phase_id' => $sphase2->id,
            'title' => 'Workshop Pembukuan Digital & Pemasaran B2B Restoran',
            'due_date' => '2026-09-25',
            'status' => 'completed',
            'definition_of_done' => '35 dari 45 petani aktif menggunakan aplikasi catatan panen mandiri.',
            'is_public' => true,
        ]);

        Task::create([
            'project_id' => $socialProject->id,
            'milestone_id' => $sm1->id,
            'title' => 'Focus Group Discussion (FGD) dengan tokoh pemuda tani desa',
            'status' => 'done',
            'priority' => 'high',
            'due_date' => '2026-07-20',
            'weight' => 2,
        ]);
        Task::create([
            'project_id' => $socialProject->id,
            'milestone_id' => $sm2->id,
            'title' => 'Penyusunan modul pelatihan bahasa sederhana bergambar',
            'status' => 'done',
            'priority' => 'medium',
            'due_date' => '2026-09-10',
            'weight' => 2,
        ]);

        JournalEntry::create([
            'project_id' => $socialProject->id,
            'user_id' => $user->id,
            'phase_id' => $sphase2->id,
            'date' => '2026-09-26',
            'title' => 'Workshop Hari ke-2: Antusiasme Tinggi Simulasi Transaksi Digital',
            'activity' => 'Melatih 42 petani muda mencatat biaya operasional benih, pupuk, dan ongkos tenaga kerja ke dalam dashboard ponsel.',
            'results' => 'Semua peserta berhasil membuat laporan rugi-laba simulasi musim tanam pertama.',
            'challenges' => 'Koneksi sinyal di balai desa sempat drop saat jam 14.00 siang.',
            'next_steps' => 'Menyediakan fitur offline-first pada formulir pencatatan harian.',
            'entry_type' => 'manual',
            'status' => 'finalized',
            'is_public' => true,
        ]);

        Output::create([
            'project_id' => $socialProject->id,
            'title' => 'Laporan Dampak Sosial (Social Impact Assessment) Musim Tanam 2026',
            'output_type' => 'impact_report',
            'status' => 'review',
            'target_date' => '2026-12-15',
            'is_public' => true,
            'notes' => 'Mendokumentasikan peningkatan pendapatan 18.5% pada 35 petani percontohan.',
        ]);

        // ==========================================
        // 4. IDEA PLAYGROUND (TAMAN IDE)
        // ==========================================
        Idea::create([
            'user_id' => $user->id,
            'title' => 'Drone Spektral Murah untuk Monitoring Biomasa Padi',
            'content' => 'Gagasan memodifikasi kamera drone action cam biasa dengan filter inframerah 720nm untuk menghitung indeks NDVI secara ekonomis tanpa perlu sensor multispektral puluhan juta rupiah.',
            'category_tag' => 'Remote Sensing',
            'suggested_mode' => 'research',
            'status' => 'draft',
        ]);
        Idea::create([
            'user_id' => $user->id,
            'title' => 'Kemasan Biodegradable dari Sekam Padi dan Jamur Miselium',
            'content' => 'Pemanfaatan limbah sekam melimpah di penggilingan padi desa sebagai media substrat jamur miselium untuk mencetak packaging pengganti styrofoam tahan benturan.',
            'category_tag' => 'Bio-Material',
            'suggested_mode' => 'product',
            'status' => 'draft',
        ]);
        Idea::create([
            'user_id' => $user->id,
            'title' => 'Platform Bank Sampah Organik Sawah & Barter Pupuk Kompos',
            'content' => 'Inisiatif sirkular di mana petani menyetorkan jerami sisa panen untuk dikomposkan terpusat oleh pemuda karang taruna, lalu ditukar dengan pupuk organik fermentasi padat.',
            'category_tag' => 'Circular Economy',
            'suggested_mode' => 'social',
            'status' => 'draft',
        ]);
    }
}

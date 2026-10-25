<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AppIcon from '@/Components/AppIcon.vue';
import PwaInstallPrompt from '@/Components/PwaInstallPrompt.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    featuredProjects: {
        type: Array,
        default: () => [],
    },
});

const getModeBadge = (mode) => {
    switch (mode) {
        case 'research':
            return { label: 'Research Dev', icon: 'research', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
        case 'product':
            return { label: 'Product Dev', icon: 'product', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
        case 'social':
            return { label: 'Social Project', icon: 'social', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
        default:
            return { label: 'R&D', icon: 'folder', color: 'bg-slate-100 text-slate-800 border-slate-200' };
    }
};
</script>

<template>
    <Head title="Research & Development Workspace - Platform Inovasi & Riset Terpadu" />

    <div class="min-h-screen bg-slate-50 text-slate-800 antialiased selection:bg-emerald-600 selection:text-white flex flex-col justify-between">
        <!-- Navigation Header -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <ApplicationLogo class="h-8 sm:h-10 w-auto" />
                </div>

                <nav v-if="canLogin" class="flex items-center space-x-2 sm:space-x-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-emerald-600 text-white font-semibold text-xs sm:text-sm hover:bg-emerald-700 shadow-xs transition-all hover:shadow"
                    >
                        <span>Buka Dashboard</span>
                        <AppIcon name="arrow-right" :size="14" class="ml-1.5 hidden sm:inline-block" />
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-3.5 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:text-emerald-700 transition-colors"
                        >
                            Masuk
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-4 sm:px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold text-xs sm:text-sm hover:bg-emerald-700 shadow-xs transition-all hover:shadow"
                        >
                            Daftar Akun
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-10 sm:pt-16 pb-14 sm:pb-24 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <div class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-6 shadow-2xs">
                    <AppIcon name="activity" :size="14" class="mr-2 text-emerald-700" />
                    <span>Research & Development Workspace Versi 1.0</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-tight max-w-4xl mx-auto">
                    Platform Terpadu Mengubah Ide Menjadi <span class="text-emerald-700">Riset, Produk, & Dampak</span>
                </h1>

                <p class="mt-4 sm:mt-6 text-sm sm:text-base lg:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                    Satu workspace personal yang menghubungkan perencanaan, roadmap, task checklist, anggaran RAB, 
                    <strong class="text-slate-900">Auto-Logbook berbukti visual</strong>, hingga <strong class="text-slate-900">Tautan Publik Reviewer & Mitra tanpa perlu registrasi</strong>.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 max-w-md sm:max-w-none mx-auto">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('login')"
                        class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 shadow-md transition-all hover:shadow-lg flex items-center justify-center"
                    >
                        <span>Mulai Eksplorasi Sekarang</span>
                        <AppIcon name="arrow-right" :size="16" class="ml-2" />
                    </Link>
                    <Link
                        v-else
                        :href="route('dashboard')"
                        class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 shadow-md transition-all hover:shadow-lg flex items-center justify-center"
                    >
                        <span>Buka Dashboard Anda</span>
                        <AppIcon name="arrow-right" :size="16" class="ml-2" />
                    </Link>

                    <a
                        href="#demo-public"
                        class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white border border-slate-300 text-slate-700 font-bold text-sm hover:bg-slate-50 shadow-xs transition-all flex items-center justify-center"
                    >
                        <AppIcon name="eye" :size="16" class="mr-2 text-slate-500" />
                        <span>Lihat Contoh Tautan Publik</span>
                    </a>
                </div>

                <!-- Principle Badges -->
                <div class="mt-8 sm:mt-12 flex flex-wrap justify-center gap-2.5 sm:gap-4 text-xs font-semibold text-slate-700">
                    <span class="flex items-center bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-2xs">
                        <AppIcon name="check" :size="14" class="text-emerald-700 mr-2" />
                        <span>Simple by Default</span>
                    </span>
                    <span class="flex items-center bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-2xs">
                        <AppIcon name="check" :size="14" class="text-emerald-700 mr-2" />
                        <span>Evidence Over Claims</span>
                    </span>
                    <span class="flex items-center bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-2xs">
                        <AppIcon name="check" :size="14" class="text-emerald-700 mr-2" />
                        <span>Privacy by Design</span>
                    </span>
                    <span class="flex items-center bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-2xs">
                        <AppIcon name="check" :size="14" class="text-emerald-700 mr-2" />
                        <span>Bebas Administrasi Rumit</span>
                    </span>
                </div>
            </div>
        </section>

        <!-- 3 Modes of Innovation Section -->
        <section class="py-12 sm:py-16 bg-white border-y border-slate-200/70">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-12">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        3 Mode Proyek Terarah Sesuai Karakter Luaran
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-2">
                        Setiap mode dilengkapi field spesifik, fase kerja terstandar, dan checklist luaran yang relevan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Mode 1 -->
                    <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:shadow-md transition-all space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                            <AppIcon name="research" :size="24" />
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Research Development</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Bagi peneliti independen atau akademisi: kelola pertanyaan riset, hipotesis, metodologi, dataset anotasi, hingga target submit artikel jurnal bereputasi.
                        </p>
                        <div class="pt-2 text-xs font-semibold text-emerald-800 flex items-center">
                            <AppIcon name="award" :size="14" class="mr-1.5" />
                            <span>Luaran: Paper Jurnal, Dataset, Protokol</span>
                        </div>
                    </div>

                    <!-- Mode 2 -->
                    <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:shadow-md transition-all space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                            <AppIcon name="product" :size="24" />
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Product Development</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Bagi product builder dan engineer: hubungkan riset dengan Bill of Materials (BOM), fabrikasi hardware, firmware, pengujian, dan prototipe MVP.
                        </p>
                        <div class="pt-2 text-xs font-semibold text-emerald-800 flex items-center">
                            <AppIcon name="cpu" :size="14" class="mr-1.5" />
                            <span>Luaran: Prototipe Fisik, MVP, Dokumen Teknis</span>
                        </div>
                    </div>

                    <!-- Mode 3 -->
                    <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:shadow-md transition-all space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                            <AppIcon name="social" :size="24" />
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Social Project Development</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Bagi inovator sosial dan penggerak komunitas: kelola pohon masalah, penerima manfaat, kegiatan pendampingan lapangan, dan indikator dampak sosial terukur.
                        </p>
                        <div class="pt-2 text-xs font-semibold text-emerald-800 flex items-center">
                            <AppIcon name="users" :size="14" class="mr-1.5" />
                            <span>Luaran: Program Komunitas, Modul, Laporan Dampak</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2 Flagship Differentiators -->
        <section class="py-12 sm:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16">
                <!-- Differentiator 1: Auto-Logbook -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <div class="space-y-4">
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <AppIcon name="logbook" :size="14" class="mr-1.5" />
                            <span>Pembeda Utama #1</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                            Auto-Logbook & Development Journal
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Pencatatan proses riset sering tertunda karena form yang terlalu berbelit. 
                            Di sini, Anda dapat melakukan <strong>Quick Capture dalam waktu kurang dari 2 menit</strong>: catat kegiatan harian, hasil, kendala, dan unggah foto lapangan langsung dari kamera ponsel.
                        </p>
                        <ul class="space-y-2 text-xs font-medium text-slate-700">
                            <li class="flex items-center"><AppIcon name="check" :size="14" class="text-emerald-700 mr-2" /> <span>Memisahkan catatan peneliti dan event otomatis sistem.</span></li>
                            <li class="flex items-center"><AppIcon name="check" :size="14" class="text-emerald-700 mr-2" /> <span>Bukti visual foto/dokumen tersimpan privat dan aman.</span></li>
                            <li class="flex items-center"><AppIcon name="check" :size="14" class="text-emerald-700 mr-2" /> <span>Kontrol fleksibel: pilih catatan mana yang boleh tampil di publik.</span></li>
                        </ul>
                    </div>

                    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200/90 shadow-md space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-400 pb-2 border-b border-slate-100">
                            <span class="font-bold text-emerald-800 flex items-center">
                                <AppIcon name="logbook" :size="13" class="mr-1.5 text-emerald-700" />
                                <span>Contoh Entri Logbook Cepat</span>
                            </span>
                            <span>Hari ini, 15:30 WIB</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Eksperimen Model Deteksi Daun Padi Epoch 150</h4>
                        <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 leading-relaxed">
                            Pengujian inferensi model pada smartphone mid-range menghasilkan latency 112ms dan akurasi deteksi mAP@50 mencapai 93.4%.
                        </p>
                        <div class="bg-emerald-50 p-2.5 rounded-xl text-xs text-emerald-900 font-medium flex items-center">
                            <AppIcon name="sparkles" :size="14" class="mr-1.5 text-emerald-700 shrink-0" />
                            <span>Hasil: Lolos kriteria performa untuk implementasi offline di sentra sawah.</span>
                        </div>
                        <div class="text-[11px] text-slate-400 flex items-center">
                            <AppIcon name="paperclip" :size="12" class="mr-1.5" />
                            <span>1 Lampiran Foto Bukti (confusion_matrix_v2.png)</span>
                        </div>
                    </div>
                </div>

                <!-- Differentiator 2: Public Project Share -->
                <div id="demo-public" class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200/90 shadow-md space-y-4 lg:order-1 order-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tautan Demonstrasi Tamu & Reviewer</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Tanpa Perlu Login</span>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/70 hover:border-emerald-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 block">1. AI Deteksi Penyakit Daun Padi</span>
                                    <span class="text-emerald-800 font-semibold text-[11px]">Progres 65%</span>
                                </div>
                                <span class="text-slate-500 text-[11px] block mt-0.5">Mode: Research Development</span>
                                <div class="mt-2.5">
                                    <a
                                        :href="route('public.share', 'rice-vision-ai-demo-2026')"
                                        target="_blank"
                                        class="inline-flex items-center font-bold text-emerald-700 hover:underline"
                                    >
                                        <span>Buka Tautan Tamu</span>
                                        <AppIcon name="arrow-right" :size="13" class="ml-1" />
                                    </a>
                                </div>
                            </div>

                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/70 hover:border-emerald-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 block">2. AgriNode Smart IoT Soil Sensor</span>
                                    <span class="text-emerald-800 font-semibold text-[11px]">Progres 45%</span>
                                </div>
                                <span class="text-slate-500 text-[11px] block mt-0.5">Mode: Product Development</span>
                                <div class="mt-2.5">
                                    <a
                                        :href="route('public.share', 'agrinode-product-hardware-mvp')"
                                        target="_blank"
                                        class="inline-flex items-center font-bold text-emerald-700 hover:underline"
                                    >
                                        <span>Buka Tautan Tamu</span>
                                        <AppIcon name="arrow-right" :size="13" class="ml-1" />
                                    </a>
                                </div>
                            </div>

                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/70 hover:border-emerald-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 block">3. Desa Tani Digital Sukatani</span>
                                    <span class="text-emerald-800 font-semibold text-[11px]">Progres 70%</span>
                                </div>
                                <span class="text-slate-500 text-[11px] block mt-0.5">Mode: Social Project Development</span>
                                <div class="mt-2.5">
                                    <a
                                        :href="route('public.share', 'desa-tani-digital-impact-2026')"
                                        target="_blank"
                                        class="inline-flex items-center font-bold text-emerald-700 hover:underline"
                                    >
                                        <span>Buka Tautan Tamu</span>
                                        <AppIcon name="arrow-right" :size="13" class="ml-1" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 lg:order-2 order-1">
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <AppIcon name="share" :size="14" class="mr-1.5 text-emerald-700" />
                            <span>Pembeda Utama #2</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                            Public Project Share: Pantau Tanpa Buat Akun
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Reviewer ahli, mentor profesional, atau mitra kolaborator tidak perlu repot membuat akun baru untuk sekadar memantau perkembangan proyek. 
                            Cukup berikan <strong>satu tautan publik ber-token acak</strong>.
                        </p>
                        <ul class="space-y-2 text-xs font-medium text-slate-700">
                            <li class="flex items-center"><AppIcon name="check" :size="14" class="text-emerald-700 mr-2" /> <span>Read-only secara aman tanpa risiko data internal terubah.</span></li>
                            <li class="flex items-center"><AppIcon name="check" :size="14" class="text-emerald-700 mr-2" /> <span>Server-side allowlist memastikan nota belanja dan dokumen privat tetap tertutup.</span></li>
                            <li class="flex items-center"><AppIcon name="check" :size="14" class="text-emerald-700 mr-2" /> <span>Reviewer & mitra dapat memberi catatan masukan & rating tanpa registrasi.</span></li>
                        </ul>
                    </div>

                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-10 sm:py-14 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Initiators Attribution Row -->
                <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-50 border border-slate-200/80 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-1">
                            Inisiator Platform
                        </span>
                        <p class="text-xs text-slate-600 max-w-md">
                            Dikembangkan dan diinisiasi untuk memajukan ekosistem riset, transformasi teknologi terapan, dan inovasi sosial berdampak.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-8">
                        <a
                            href="https://yotainovasi.id/"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="PT Yota Inovasi Nusantara (YOIN) | Transformasi Digital, Jasa Website & Ekosistem Teknologi"
                            class="inline-flex items-center transition-transform hover:scale-105"
                        >
                            <img
                                src="https://yotainovasi.id/logotulisan.png"
                                alt="PT Yota Inovasi Nusantara (YOIN) | Transformasi Digital, Jasa Website & Ekosistem Teknologi"
                                class="h-7 sm:h-8 w-auto object-contain"
                            />
                        </a>

                        <div class="hidden sm:block w-px h-7 bg-slate-200"></div>

                        <a
                            href="https://siyota.org/"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="Beranda | Yota Adiwidya Center"
                            class="inline-flex items-center transition-transform hover:scale-105"
                        >
                            <img
                                src="https://siyota.org/image/logo.png"
                                alt="Beranda | Yota Adiwidya Center"
                                class="h-7 sm:h-8 w-auto object-contain"
                            />
                        </a>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 pt-2 border-t border-slate-100">
                    <div class="flex items-center space-x-3">
                        <ApplicationLogo class="h-7 w-auto" />
                        <span>&copy; 2026 Research & Development Workspace. All rights reserved.</span>
                    </div>
                    <div class="flex items-center space-x-6 font-medium">
                        <Link :href="route('login')" class="hover:text-emerald-700">Masuk</Link>
                        <Link v-if="canRegister" :href="route('register')" class="hover:text-emerald-700">Daftar Akun</Link>
                    </div>
                </div>
            </div>
        </footer>

        <!-- PWA Install Prompt Banner -->
        <PwaInstallPrompt />
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AppIcon from '@/Components/AppIcon.vue';

const props = defineProps({
    project: Object,
    feedbacks: Array,
});

const feedbackForm = useForm({
    reviewer_name: '',
    reviewer_role: 'Reviewer Ahli',
    comment: '',
    rating: 5,
});


const submitFeedback = () => {
    feedbackForm.post(route('public.feedback', props.project.share_token), {
        onSuccess: () => {
            feedbackForm.reset('comment');
            alert('Catatan masukan Anda telah berhasil dikirimkan ke pemilik proyek!');
        },
    });
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

// Strict 2-Color Palette: Emerald + Slate
const getModeInfo = (mode) => {
    switch (mode) {
        case 'research':
            return { label: 'Research Development', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'research' };
        case 'product':
            return { label: 'Product Development', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'product' };
        case 'social':
            return { label: 'Social Project Development', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'social' };
        case 'competition':
            return { label: 'Lomba & Kompetisi', color: 'bg-amber-50 text-amber-800 border-amber-200', icon: 'competition' };
        default:
            return { label: 'Proyek R&D', color: 'bg-slate-50 text-slate-700 border-slate-200', icon: 'folder' };
    }
};
</script>

<template>
    <Head :title="`${project.title} - Laporan Progres Publik`" />

    <div class="min-h-screen bg-slate-50 text-slate-800 antialiased selection:bg-emerald-500 selection:text-white">
        <!-- Top Public Navbar -->
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur-md shadow-xs">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-2 sm:space-x-3 overflow-hidden">
                    <ApplicationLogo class="h-7 sm:h-8 w-auto shrink-0" />
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">
                        Public Project Share
                    </span>
                </div>

                <div class="flex items-center space-x-2 shrink-0">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-600 mr-1.5 animate-pulse"></span>
                        <span class="hidden xs:inline">Akses Tamu </span>(Tanpa Akun)
                    </span>
                </div>
            </div>
        </header>

        <!-- Main Container -->
        <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-10 space-y-6 sm:space-y-8">
            <!-- Project Overview Header Card -->
            <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-8 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="space-y-2 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                :class="getModeInfo(project.mode).color"
                            >
                                <AppIcon :name="getModeInfo(project.mode).icon" :size="12" class="mr-1.5" />
                                {{ getModeInfo(project.mode).label }}
                            </span>
                            <span class="text-xs text-slate-400">
                                Diperbarui {{ project.updated_at }}
                            </span>
                        </div>

                        <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                            {{ project.title }}
                        </h1>

                        <p class="text-xs text-slate-500 flex items-center gap-1.5">
                            <AppIcon name="users" :size="13" class="text-slate-400" />
                            <span>Pemilik Proyek: <strong class="text-slate-800">{{ project.owner_name }}</strong></span>
                        </p>
                    </div>

                    <div class="shrink-0 bg-slate-50 p-4 rounded-2xl border border-slate-200/70 text-left sm:text-right w-full sm:w-auto min-w-[150px]">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Capaian Progres</span>
                        <div class="text-3xl font-black text-emerald-800 mt-0.5">{{ project.progress_percent }}%</div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5 mt-2 overflow-hidden">
                            <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-500" :style="{ width: `${project.progress_percent}%` }"></div>
                        </div>
                    </div>
                </div>

                <!-- Problem Statement & Description (If Allowlisted) -->
                <div v-if="project.public_settings?.show_overview" class="pt-4 border-t border-slate-100 space-y-4 text-xs sm:text-sm">
                    <div v-if="project.problem_statement" class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100 space-y-1.5">
                        <h3 class="font-bold text-slate-900 flex items-center text-xs sm:text-sm">
                            <AppIcon name="target" :size="16" class="mr-2 text-emerald-700 shrink-0" />
                            Masalah & Latar Belakang
                        </h3>
                        <p class="text-slate-600 leading-relaxed text-xs sm:text-sm">{{ project.problem_statement }}</p>
                    </div>

                    <div v-if="project.target_outcome" class="bg-emerald-50/40 p-4 sm:p-5 rounded-2xl border border-emerald-100/70 space-y-1.5">
                        <h3 class="font-bold text-emerald-900 flex items-center text-xs sm:text-sm">
                            <AppIcon name="award" :size="16" class="mr-2 text-emerald-700 shrink-0" />
                            Target Capaian Luaran
                        </h3>
                        <p class="text-emerald-950 leading-relaxed text-xs sm:text-sm">{{ project.target_outcome }}</p>
                    </div>
                </div>
            </div>

            <!-- Milestones Section (If Allowlisted) -->
            <div v-if="project.public_settings?.show_milestones && project.milestones?.length > 0" class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-8 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center">
                        <AppIcon name="target" :size="18" class="mr-2 text-emerald-700 shrink-0" />
                        Target Milestone Perkembangan
                    </h2>
                    <span class="text-xs text-slate-400">{{ project.milestones.length }} Milestone</span>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="m in project.milestones"
                        :key="m.id"
                        class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60 flex flex-col sm:flex-row sm:items-start justify-between gap-3"
                    >
                        <div class="flex items-start space-x-3">
                            <span
                                :class="m.status === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500'"
                                class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                            >
                                <AppIcon v-if="m.status === 'completed'" name="check" :size="13" />
                                <span v-else class="w-2 h-2 rounded-full bg-slate-400"></span>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">{{ m.title }}</h4>
                                <p v-if="m.definition_of_done" class="text-xs text-slate-500 mt-1">
                                    Definisi Selesai: {{ m.definition_of_done }}
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-200/50">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase"
                                :class="m.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'"
                            >
                                {{ m.status === 'completed' ? 'Tercapai' : 'Proses' }}
                            </span>
                            <span v-if="m.due_date" class="block text-[11px] text-slate-400 sm:mt-1">
                                Target: {{ m.due_date }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Journal & Logbook (If Allowlisted) -->
            <div v-if="project.public_settings?.show_journal && project.journal_entries?.length > 0" class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-8 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center">
                            <AppIcon name="logbook" :size="18" class="mr-2 text-emerald-700 shrink-0" />
                            Dokumentasi & Jurnal Pengembangan Terpilih
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Catatan aktivitas dan temuan yang telah diverifikasi oleh pemilik proyek.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="entry in project.journal_entries"
                        :key="entry.id"
                        class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 border border-slate-200/70 space-y-3"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs text-slate-400">
                            <span class="font-bold text-slate-800 text-sm">{{ entry.title }}</span>
                            <span class="inline-flex items-center gap-1">
                                <AppIcon name="calendar" :size="12" />
                                {{ entry.date }}
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-white p-3.5 rounded-xl border border-slate-100 whitespace-pre-line">
                            {{ entry.activity }}
                        </p>

                        <div v-if="entry.results" class="bg-emerald-50 text-emerald-900 p-3 sm:p-3.5 rounded-xl text-xs font-medium flex items-start gap-2">
                            <AppIcon name="sparkles" :size="15" class="text-emerald-700 shrink-0 mt-0.5" />
                            <div>
                                <strong class="font-bold">Temuan / Hasil:</strong> {{ entry.results }}
                            </div>
                        </div>

                        <div v-if="entry.attachments?.length > 0" class="pt-2 flex flex-wrap gap-2 text-xs">
                            <div
                                v-for="(att, i) in entry.attachments"
                                :key="i"
                                class="p-2 bg-white rounded-lg border border-slate-200 flex items-center space-x-1.5 text-slate-600 font-medium text-xs shadow-xs"
                            >
                                <AppIcon name="paperclip" :size="13" class="text-slate-400" />
                                <span>{{ att.caption || att.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outputs & Publications (If Allowlisted) -->
            <div v-if="project.public_settings?.show_outputs && project.outputs?.length > 0" class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-8 shadow-xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center">
                        <AppIcon name="award" :size="18" class="mr-2 text-emerald-700 shrink-0" />
                        Target Luaran & Publikasi
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div
                        v-for="out in project.outputs"
                        :key="out.id"
                        class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 flex flex-col justify-between space-y-3"
                    >
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    {{ out.output_type }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase" :class="out.status === 'published_done' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">
                                    {{ out.status }}
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900">{{ out.title }}</h4>
                            <p v-if="out.venue_or_journal" class="text-xs text-slate-500 mt-1">
                                Venue: {{ out.venue_or_journal }}
                            </p>
                        </div>

                        <div v-if="out.doi_or_url" class="pt-2 border-t border-slate-200/60 text-xs">
                            <a :href="out.doi_or_url" target="_blank" rel="noopener noreferrer" class="text-emerald-700 hover:underline font-semibold inline-flex items-center gap-1">
                                <span>Buka Tautan Bukti</span>
                                <AppIcon name="external-link" :size="12" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Summary Aggregate (If Allowlisted) -->
            <div v-if="project.public_settings?.show_budget_summary && project.budget_summary" class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-8 shadow-xs space-y-4">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center">
                    <AppIcon name="budget" :size="18" class="mr-2 text-emerald-700 shrink-0" />
                    Ringkasan Anggaran Agregat
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Estimasi Rencana</span>
                        <div class="text-base sm:text-lg font-bold text-slate-900 mt-1">{{ formatRupiah(project.budget_summary.total_estimated) }}</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Realisasi Pengeluaran</span>
                        <div class="text-base sm:text-lg font-bold text-slate-900 mt-1">{{ formatRupiah(project.budget_summary.total_actual) }}</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Persentase Penyerapan</span>
                        <div class="text-base sm:text-lg font-bold text-emerald-800 mt-1">{{ project.budget_summary.percent_used }}%</div>
                    </div>
                </div>
            </div>

            <!-- Reviewer / Mentor Feedback Section (If Allowlisted) -->
            <div v-if="project.public_settings?.allow_feedback" class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-8 shadow-xs space-y-6">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center">
                        <AppIcon name="feedback" :size="18" class="mr-2 text-emerald-700 shrink-0" />
                        Beri Masukan / Catatan Evaluasi
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Reviewer, mentor, pembimbing, atau mitra kolaborator dapat mengirimkan saran langsung tanpa perlu mendaftar akun.
                    </p>
                </div>

                <!-- Existing Feedbacks -->
                <div v-if="feedbacks?.length > 0" class="space-y-3">
                    <div
                        v-for="f in feedbacks"
                        :key="f.id"
                        class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60 text-xs space-y-1.5"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <strong class="text-slate-900">{{ f.reviewer_name }}</strong>
                                <span class="text-slate-400 ml-1.5">({{ f.reviewer_role }})</span>
                            </div>
                            <div class="flex items-center space-x-0.5 text-emerald-500">
                                <AppIcon
                                    v-for="s in f.rating"
                                    :key="s"
                                    name="star"
                                    :size="13"
                                    class="fill-emerald-500 text-emerald-500"
                                />
                            </div>
                        </div>
                        <p class="text-slate-700 leading-relaxed">{{ f.comment }}</p>
                    </div>
                </div>

                <!-- Feedback Form -->
                <form @submit.prevent="submitFeedback" class="pt-4 border-t border-slate-100 space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Nama Anda <span class="text-rose-500">*</span></label>
                            <input
                                v-model="feedbackForm.reviewer_name"
                                type="text"
                                required
                                placeholder="Contoh: Dr. Ir. Suryo / Prof. Hendra"
                                class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Peran / Hubungan</label>
                            <select v-model="feedbackForm.reviewer_role" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="Reviewer Ahli">Reviewer Ahli</option>
                                <option value="Mentor / Advisor">Mentor / Advisor</option>
                                <option value="Mitra Lapangan">Mitra Lapangan</option>
                                <option value="Donor / Sponsor">Donor / Sponsor</option>
                                <option value="Penguji / Evaluator">Penguji / Evaluator</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Evaluasi / Saran Pengembangan <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="feedbackForm.comment"
                            rows="3"
                            required
                            placeholder="Tuliskan catatan, rekomendasi perbaikan, atau apresiasi untuk peneliti/pengembang..."
                            class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold text-slate-700">Rating:</span>
                            <div class="flex items-center space-x-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="feedbackForm.rating = star"
                                    class="p-2 focus:outline-none transition-transform hover:scale-110 active:scale-95"
                                    :title="`${star} Bintang`"
                                >
                                    <AppIcon
                                        name="star"
                                        :size="20"
                                        :class="star <= feedbackForm.rating ? 'fill-emerald-500 text-emerald-500' : 'text-slate-300'"
                                    />
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="feedbackForm.processing"
                            class="inline-flex items-center justify-center gap-1.5 px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-xs disabled:opacity-50 w-full sm:w-auto active:scale-95"
                        >
                            <AppIcon name="feedback" :size="15" />
                            <span>{{ feedbackForm.processing ? 'Mengirim...' : 'Kirim Masukan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200/80 bg-white py-10 text-xs text-slate-500">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Initiators Attribution -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/70 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-center sm:text-left">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">
                            Inisiator Platform
                        </span>
                        <span class="text-slate-600 text-xs">Research & Development Workspace Ecosystem</span>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-5 sm:gap-6">
                        <a
                            href="https://yotainovasi.id/"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="PT Yota Inovasi Nusantara (YOIN) | Transformasi Digital, Jasa Website & Ekosistem Teknologi"
                            class="inline-flex items-center transition-transform hover:scale-105"
                        >
                            <img
                                src="https://yotainovasi.id/logotulisan.png"
                                alt="PT Yota Inovasi Nusantara (YOIN)"
                                class="h-6 w-auto object-contain"
                            />
                        </a>

                        <div class="w-px h-5 bg-slate-200"></div>

                        <a
                            href="https://siyota.org/"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="Beranda | Yota Adiwidya Center"
                            class="inline-flex items-center transition-transform hover:scale-105"
                        >
                            <img
                                src="https://siyota.org/image/logo.png"
                                alt="Yota Adiwidya Center"
                                class="h-6 w-auto object-contain"
                            />
                        </a>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-slate-400">
                    <div class="flex items-center space-x-2">
                        <ApplicationLogo class="h-5 w-auto" />
                        <span>&copy; 2026 Research & Development Workspace</span>
                    </div>
                    <span>Platform terpadu untuk mengubah ide menjadi riset, produk, dan dampak nyata.</span>
                </div>
            </div>
        </footer>
    </div>
</template>

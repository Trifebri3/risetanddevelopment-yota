<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    projects: Array,
    recentJournals: Array,
    upcomingMilestones: Array,
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

// Strict 2-Color Palette: Emerald + Slate
const getModeBadge = (mode) => {
    switch (mode) {
        case 'research':
            return { label: 'Research Dev', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'research' };
        case 'product':
            return { label: 'Product Dev', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'product' };
        case 'social':
            return { label: 'Social Project', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'social' };
        default:
            return { label: 'Umum', color: 'bg-slate-50 text-slate-700 border-slate-200', icon: 'folder' };
    }
};
</script>

<template>
    <Head title="Dashboard Workspace" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                        Dashboard Workspace R&D
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Satu platform personal untuk mengubah ide menjadi riset, produk nyata, dan dampak sosial yang teruji.
                    </p>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <Link
                        :href="route('ideas.index')"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs transition-all active:scale-95"
                    >
                        <AppIcon name="idea" :size="15" class="mr-1.5 text-slate-600" />
                        <span>Catat Ide</span>
                    </Link>
                    <Link
                        :href="route('projects.index', { create: 'true' })"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition-all hover:shadow active:scale-95"
                    >
                        <AppIcon name="plus" :size="15" class="mr-1.5" />
                        <span>Proyek Baru</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-5 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">
                <!-- 4 Summary KPI Metric Cards (Strict Emerald + Slate) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    <!-- Card 1: Active Projects -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Proyek Aktif</span>
                                <div class="p-2.5 bg-slate-100 text-slate-700 rounded-xl">
                                    <AppIcon name="folder" :size="18" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-baseline justify-between">
                                <div class="text-3xl font-extrabold text-slate-900">{{ stats.active_projects }}</div>
                                <span class="text-xs text-slate-500 font-medium">dari {{ stats.total_projects }} total</span>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-500">
                            Kelola siklus hidup ide hingga luaran.
                        </div>
                    </div>

                    <!-- Card 2: Tasks Completion -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Aktivitas Selesai</span>
                                <div class="p-2.5 bg-emerald-50 text-emerald-800 rounded-xl">
                                    <AppIcon name="task" :size="18" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-baseline justify-between">
                                <div class="text-3xl font-extrabold text-slate-900">{{ stats.completed_tasks }}</div>
                                <span class="text-xs text-slate-500 font-medium">{{ stats.total_tasks }} total tugas</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div
                                    class="bg-emerald-600 h-1.5 rounded-full transition-all duration-500"
                                    :style="{ width: `${stats.total_tasks > 0 ? (stats.completed_tasks / stats.total_tasks) * 100 : 0}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Auto-Logbook Entries -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jurnal & Logbook</span>
                                <div class="p-2.5 bg-emerald-50 text-emerald-800 rounded-xl">
                                    <AppIcon name="logbook" :size="18" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-baseline justify-between">
                                <div class="text-3xl font-extrabold text-slate-900">{{ stats.total_journals }}</div>
                                <span class="text-xs text-emerald-800 font-semibold">Terdokumentasi</span>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-500">
                            Catat proses harian dengan bukti foto.
                        </div>
                    </div>

                    <!-- Card 4: Budget Realization -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Realisasi Anggaran</span>
                                <div class="p-2.5 bg-slate-100 text-slate-700 rounded-xl">
                                    <AppIcon name="budget" :size="18" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-xl font-extrabold text-slate-900 truncate">{{ formatRupiah(stats.total_expenses) }}</div>
                                <span class="text-xs text-slate-400 font-medium">RAB: {{ formatRupiah(stats.total_budget) }}</span>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-slate-500 truncate">
                            Terkontrol dan transparan.
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Left (2 cols): Active Projects List & Milestones -->
                    <div class="lg:col-span-2 space-y-6 sm:space-y-8">
                        <!-- Projects Section -->
                        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
                            <div class="flex items-center justify-between mb-5 sm:mb-6">
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Proyek Aktif & Berjalan</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">Pilih proyek untuk membuka Roadmap, Logbook, RAB, dan Tautan Publik.</p>
                                </div>
                                <Link
                                    :href="route('projects.index')"
                                    class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition-colors flex items-center"
                                >
                                    <span>Lihat Semua</span>
                                    <AppIcon name="chevron-right" :size="14" class="ml-1" />
                                </Link>
                            </div>

                            <div v-if="projects.length === 0" class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <AppIcon name="folder" :size="32" class="text-slate-400 mx-auto" />
                                <p class="text-sm font-semibold text-slate-700 mt-2">Belum ada proyek yang dibuat</p>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Mulai dengan mencatat ide atau langsung buat proyek pertama Anda.</p>
                                <Link
                                    :href="route('projects.index', { create: 'true' })"
                                    class="inline-flex items-center mt-4 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-xs"
                                >
                                    <AppIcon name="plus" :size="13" class="mr-1.5" />
                                    <span>Buat Proyek Pertama</span>
                                </Link>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="project in projects.slice(0, 4)"
                                    :key="project.id"
                                    class="group p-4 sm:p-5 rounded-2xl border border-slate-200/90 hover:border-emerald-300 hover:shadow-md transition-all bg-white relative"
                                >
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                        <div class="space-y-1.5 flex-1 sm:pr-4">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                                    :class="getModeBadge(project.mode).color"
                                                >
                                                    <AppIcon :name="getModeBadge(project.mode).icon" :size="12" class="mr-1.5" />
                                                    <span>{{ getModeBadge(project.mode).label }}</span>
                                                </span>

                                                <span v-if="project.user_id !== $page.props.auth.user.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                    <AppIcon name="users" :size="11" class="mr-1 text-slate-500" />
                                                    <span>Tim Kolaborator</span>
                                                </span>

                                                <span v-if="project.is_public" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                    <AppIcon name="share" :size="11" class="mr-1" />
                                                    <span>Link Publik Aktif</span>
                                                </span>

                                            </div>

                                            <h3 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">
                                                <Link :href="route('projects.show', project.id)">
                                                    {{ project.title }}
                                                </Link>
                                            </h3>

                                            <p class="text-xs text-slate-500 line-clamp-2">
                                                {{ project.problem_statement || project.description }}
                                            </p>
                                        </div>

                                        <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                            <div class="text-left sm:text-right">
                                                <span class="text-sm font-extrabold text-slate-900">{{ project.progress_percent }}%</span>
                                                <span class="text-[11px] text-slate-400 block">Progres</span>
                                            </div>
                                            <Link
                                                :href="route('projects.show', project.id)"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 group-hover:bg-emerald-600 group-hover:text-white transition-all active:scale-95"
                                            >
                                                <span>Buka Workspace</span>
                                                <AppIcon name="arrow-right" :size="12" class="ml-1" />
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500">
                                        <div class="w-full sm:w-1/2 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div
                                                class="bg-emerald-600 h-1.5 rounded-full transition-all"
                                                :style="{ width: `${project.progress_percent}%` }"
                                            ></div>
                                        </div>
                                        <div class="flex items-center space-x-3 text-slate-400 text-[11px]">
                                            <span>{{ project.tasks?.length || 0 }} Tugas</span>
                                            <span>•</span>
                                            <span>{{ project.milestones?.length || 0 }} Milestone</span>
                                            <span>•</span>
                                            <span>{{ project.outputs?.length || 0 }} Luaran</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Milestones -->
                        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 mb-4 flex items-center">
                                <AppIcon name="target" :size="18" class="mr-2 text-emerald-700" />
                                <span>Milestone Mendatang (Target Terdekat)</span>
                            </h2>
                            <div v-if="upcomingMilestones.length === 0" class="text-xs text-slate-400 py-4 text-center">
                                Belum ada milestone terjadwal.
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="m in upcomingMilestones"
                                    :key="m.id"
                                    class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200/60"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></div>
                                        <div>
                                            <h4 class="text-xs sm:text-sm font-semibold text-slate-900">{{ m.title }}</h4>
                                            <p class="text-[11px] text-slate-500 mt-0.5">
                                                Proyek: <span class="font-medium text-slate-700">{{ m.project?.title }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-200 text-slate-700">
                                            {{ m.due_date ? new Date(m.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : 'No date' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Recent Journals & Guide -->
                    <div class="space-y-6 sm:space-y-8">
                        <!-- Auto-Logbook Feed -->
                        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center">
                                    <AppIcon name="logbook" :size="17" class="mr-2 text-emerald-700" />
                                    <span>Jurnal Terbaru</span>
                                </h2>
                                <span class="text-[11px] text-slate-400">Bukti Proses</span>
                            </div>

                            <div v-if="recentJournals.length === 0" class="text-center py-6 text-xs text-slate-400">
                                Belum ada entri logbook.
                            </div>

                            <div v-else class="space-y-3 sm:space-y-4">
                                <div
                                    v-for="entry in recentJournals"
                                    :key="entry.id"
                                    class="p-3.5 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors"
                                >
                                    <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                                        <span class="font-semibold text-emerald-800 truncate max-w-[150px]">{{ entry.project?.title }}</span>
                                        <span>{{ new Date(entry.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}</span>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800">{{ entry.title }}</h4>
                                    <p class="text-xs text-slate-600 mt-1 line-clamp-2">
                                        {{ entry.activity }}
                                    </p>
                                    <div v-if="entry.results" class="mt-2 text-[11px] bg-emerald-50 text-emerald-800 p-2 rounded-lg font-medium flex items-center">
                                        <AppIcon name="sparkles" :size="12" class="mr-1.5 shrink-0 text-emerald-700" />
                                        <span class="truncate">Hasil: {{ entry.results }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clean Workspace Guide -->
                        <div class="bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 text-white rounded-2xl sm:rounded-3xl p-6 shadow-md">
                            <div class="flex items-center space-x-2 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                                <AppIcon name="lightbulb" :size="14" />
                                <span>Filosofi Produk R&D</span>
                            </div>
                            <h3 class="text-sm sm:text-base font-bold mt-2 text-white">Simple by Default & Evidence-Led</h3>
                            <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                                Didesain khusus agar fokus pada riset dan pembuatan produk:
                            </p>
                            <ul class="mt-3 space-y-2 text-xs text-slate-300">
                                <li class="flex items-start">
                                    <AppIcon name="check" :size="14" class="text-emerald-400 mr-2 shrink-0 mt-0.5" />
                                    <span><strong>Auto-Logbook:</strong> Catat aktivitas dalam 2 menit dengan lampiran foto bukti.</span>
                                </li>
                                <li class="flex items-start">
                                    <AppIcon name="check" :size="14" class="text-emerald-400 mr-2 shrink-0 mt-0.5" />
                                    <span><strong>Public Share:</strong> Reviewer/mitra cukup buka 1 tautan tanpa perlu registrasi akun.</span>
                                </li>
                                <li class="flex items-start">
                                    <AppIcon name="check" :size="14" class="text-emerald-400 mr-2 shrink-0 mt-0.5" />
                                    <span><strong>Privat by Default:</strong> Kontrol penuh atas informasi yang dibuka ke publik.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

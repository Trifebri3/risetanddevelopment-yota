<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    projects: Array,
    recentJournals: Array,
    upcomingMilestones: Array,
});

const dashboardFilter = ref('active'); // 'active', 'completed', 'pinned', 'all'

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

// 1-Click Pin
const togglePin = (project, event) => {
    if (event) event.stopPropagation();
    router.post(route('projects.toggle_pin', project.slug || project.id), {}, { preserveScroll: true });
};

// 1-Click Status Change
const activeStatusDropdownId = ref(null);
const toggleStatusDropdown = (projectId, event) => {
    event.stopPropagation();
    activeStatusDropdownId.value = activeStatusDropdownId.value === projectId ? null : projectId;
};

if (typeof window !== 'undefined') {
    window.addEventListener('click', () => {
        activeStatusDropdownId.value = null;
    });
}

const changeProjectStatus = (project, newStatus, event) => {
    if (event) event.stopPropagation();
    activeStatusDropdownId.value = null;
    router.patch(route('projects.update_status', project.slug || project.id), { status: newStatus }, { preserveScroll: true });
};

// Edit Project Modal
const isEditModalOpen = ref(false);
const editForm = useForm({
    id: null,
    slug: '',
    title: '',
    mode: 'research',
    label: '',
    status: 'active',
    description: '',
    problem_statement: '',
    target_outcome: '',
    start_date: '',
    end_date: '',
});

const openEditModal = (project, event) => {
    if (event) event.stopPropagation();
    editForm.id = project.id;
    editForm.slug = project.slug;
    editForm.title = project.title;
    editForm.mode = project.mode;
    editForm.label = project.label || '';
    editForm.status = project.status;
    editForm.description = project.description || '';
    editForm.problem_statement = project.problem_statement || '';
    editForm.target_outcome = project.target_outcome || '';
    editForm.start_date = project.start_date || '';
    editForm.end_date = project.end_date || '';
    isEditModalOpen.value = true;
};

const submitEditProject = () => {
    editForm.put(route('projects.update', editForm.slug || editForm.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
        },
    });
};

// Delete Project Modal
const isDeleteModalOpen = ref(false);
const projectToDelete = ref(null);

const openDeleteModal = (project, event) => {
    if (event) event.stopPropagation();
    projectToDelete.value = project;
    isDeleteModalOpen.value = true;
};

const confirmDeleteProject = () => {
    if (!projectToDelete.value) return;
    router.delete(route('projects.destroy', projectToDelete.value.slug || projectToDelete.value.id), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            projectToDelete.value = null;
        },
    });
};

// Filtered projects computed
const selectedCategoryFilter = ref('all'); // 'all', 'research', 'product', 'social', 'competition'

const toggleCategoryFilter = (category) => {
    if (selectedCategoryFilter.value === category) {
        selectedCategoryFilter.value = 'all';
    } else {
        selectedCategoryFilter.value = category;
    }
};

const filteredProjects = computed(() => {
    let list = props.projects || [];
    switch (dashboardFilter.value) {
        case 'active':
            list = list.filter(p => p.status === 'active');
            break;
        case 'completed':
            list = list.filter(p => p.status === 'completed');
            break;
        case 'pinned':
            list = list.filter(p => p.is_pinned);
            break;
    }
    if (selectedCategoryFilter.value !== 'all') {
        list = list.filter(p => p.mode === selectedCategoryFilter.value);
    }
    return list;
});

// Strict 2-Color Palette: Emerald + Slate
const getModeBadge = (mode) => {
    switch (mode) {
        case 'research':
            return { label: 'Research Dev', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'research' };
        case 'product':
            return { label: 'Product Dev', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'product' };
        case 'social':
            return { label: 'Social Project', color: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: 'social' };
        case 'competition':
            return { label: 'Lomba & Kompetisi', color: 'bg-amber-50 text-amber-800 border-amber-200', icon: 'competition' };
        default:
            return { label: 'Umum', color: 'bg-slate-50 text-slate-700 border-slate-200', icon: 'folder' };
    }
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'completed':
            return {
                label: 'Sudah Selesai',
                color: 'bg-emerald-600 text-white border-emerald-600 font-extrabold shadow-2xs',
                dot: 'bg-white',
            };
        case 'active':
            return {
                label: 'Aktif & Berjalan',
                color: 'bg-emerald-50 text-emerald-800 border-emerald-200 font-bold',
                dot: 'bg-emerald-500 animate-pulse',
            };
        case 'draft':
            return {
                label: 'Draf',
                color: 'bg-slate-100 text-slate-700 border-slate-300 font-semibold',
                dot: 'bg-slate-400',
            };
        case 'on_hold':
            return {
                label: 'Tertunda',
                color: 'bg-amber-50 text-amber-800 border-amber-200 font-semibold',
                dot: 'bg-amber-500',
            };
        default:
            return {
                label: status,
                color: 'bg-slate-100 text-slate-700 border-slate-200',
                dot: 'bg-slate-400',
            };
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
                <!-- 4 Summary KPI Metric Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    <!-- Card 1: Active Projects & Folder Selesai -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Proyek Aktif</span>
                                <div class="p-2.5 bg-emerald-50 text-emerald-800 rounded-xl">
                                    <AppIcon name="folder" :size="18" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-baseline justify-between">
                                <div class="text-3xl font-extrabold text-slate-900">{{ stats.active_projects }}</div>
                                <span class="text-xs text-slate-500 font-medium">dari {{ stats.total_projects }} total</span>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-500 flex items-center justify-between">
                            <span>Folder Selesai: <strong>{{ stats.completed_projects ?? 0 }}</strong></span>
                            <span v-if="stats.pinned_projects" class="text-amber-700 font-semibold">📌 {{ stats.pinned_projects }} disematkan</span>
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
                                <div class="p-2.5 bg-slate-100 text-slate-700 rounded-xl">
                                    <AppIcon name="logbook" :size="18" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-baseline justify-between">
                                <div class="text-3xl font-extrabold text-slate-900">{{ stats.total_journals }}</div>
                                <span class="text-xs text-emerald-700 font-bold">Terorganisir</span>
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

                <!-- Statistik Kategori & Pipeline Inovasi (Riset, Produk, Sosial, Lomba, Ide) -->
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                                <AppIcon name="layers" :size="16" class="text-emerald-700" />
                                <span>Distribusi Inovasi: Riset, Produk, Sosial, Lomba & Ide</span>
                            </h2>
                            <p class="text-xs text-slate-500">Statistik pipeline ekosistem R&D berdasarkan kategori proyek dan ide.</p>
                        </div>
                        <Link
                            :href="route('projects.index')"
                            class="text-xs font-bold text-emerald-700 hover:text-emerald-900 hidden sm:flex items-center gap-1"
                        >
                            <span>Semua Proyek</span>
                            <AppIcon name="arrow-right" :size="12" />
                        </Link>
                    </div>

                    <!-- 5 Category Metric Cards Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                        <!-- 1. Riset Ilmiah (Research Dev) -->
                        <div
                            @click="toggleCategoryFilter('research')"
                            :class="selectedCategoryFilter === 'research' ? 'border-emerald-600 bg-emerald-100/70 ring-2 ring-emerald-500/30 shadow-xs' : 'border-emerald-200/80 bg-emerald-50/50 hover:bg-emerald-50 hover:border-emerald-300'"
                            class="p-4 rounded-2xl border transition-all cursor-pointer group flex flex-col justify-between"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-2xs font-extrabold uppercase tracking-wider text-emerald-800">Riset Ilmiah</span>
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center">
                                    <AppIcon name="research" :size="14" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-black text-emerald-950">{{ stats.research_projects ?? 0 }}</div>
                                <span class="text-2xs text-emerald-700 font-medium">Proyek Riset</span>
                            </div>
                        </div>

                        <!-- 2. Produk & MVP (Product Dev) -->
                        <div
                            @click="toggleCategoryFilter('product')"
                            :class="selectedCategoryFilter === 'product' ? 'border-blue-600 bg-blue-100/70 ring-2 ring-blue-500/30 shadow-xs' : 'border-blue-200/80 bg-blue-50/50 hover:bg-blue-50 hover:border-blue-300'"
                            class="p-4 rounded-2xl border transition-all cursor-pointer group flex flex-col justify-between"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-2xs font-extrabold uppercase tracking-wider text-blue-800">Produk & MVP</span>
                                <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center">
                                    <AppIcon name="product" :size="14" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-black text-blue-950">{{ stats.product_projects ?? 0 }}</div>
                                <span class="text-2xs text-blue-700 font-medium">Pengembangan Produk</span>
                            </div>
                        </div>

                        <!-- 3. Sosial & Komunitas -->
                        <div
                            @click="toggleCategoryFilter('social')"
                            :class="selectedCategoryFilter === 'social' ? 'border-teal-600 bg-teal-100/70 ring-2 ring-teal-500/30 shadow-xs' : 'border-teal-200/80 bg-teal-50/50 hover:bg-teal-50 hover:border-teal-300'"
                            class="p-4 rounded-2xl border transition-all cursor-pointer group flex flex-col justify-between"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-2xs font-extrabold uppercase tracking-wider text-teal-800">Proyek Sosial</span>
                                <div class="w-7 h-7 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center">
                                    <AppIcon name="social" :size="14" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-black text-teal-950">{{ stats.social_projects ?? 0 }}</div>
                                <span class="text-2xs text-teal-700 font-medium">Dampak Komunitas</span>
                            </div>
                        </div>

                        <!-- 4. Lomba & Kompetisi -->
                        <div
                            @click="toggleCategoryFilter('competition')"
                            :class="selectedCategoryFilter === 'competition' ? 'border-amber-600 bg-amber-100/70 ring-2 ring-amber-500/30 shadow-xs' : 'border-amber-200/80 bg-amber-50/50 hover:bg-amber-50 hover:border-amber-300'"
                            class="p-4 rounded-2xl border transition-all cursor-pointer group flex flex-col justify-between"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-2xs font-extrabold uppercase tracking-wider text-amber-800">Lomba & Hibah</span>
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center">
                                    <AppIcon name="competition" :size="14" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-black text-amber-950">{{ stats.competition_projects ?? 0 }}</div>
                                <span class="text-2xs text-amber-700 font-medium">Kompetisi & Prestasi</span>
                            </div>
                        </div>

                        <!-- 5. Taman Ide -->
                        <Link
                            :href="route('ideas.index')"
                            class="p-4 rounded-2xl bg-purple-50/50 border border-purple-200/80 hover:bg-purple-50 hover:border-purple-300 transition-all group flex flex-col justify-between col-span-2 sm:col-span-1"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-2xs font-extrabold uppercase tracking-wider text-purple-800">Taman Ide</span>
                                <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-800 flex items-center justify-center">
                                    <AppIcon name="idea" :size="14" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-black text-purple-950">{{ stats.total_ideas ?? 0 }}</div>
                                <span class="text-2xs text-purple-700 font-medium">{{ stats.converted_ideas ?? 0 }} telah dikonversi &rarr;</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Category Filter Reset Notification if filtered -->
                    <div
                        v-if="selectedCategoryFilter !== 'all'"
                        class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs text-slate-700 animate-fade-in"
                    >
                        <span>Menampilkan filter kategori: <strong class="capitalize">{{ selectedCategoryFilter }}</strong></span>
                        <button
                            type="button"
                            @click="selectedCategoryFilter = 'all'"
                            class="text-xs font-bold text-rose-600 hover:text-rose-800 underline"
                        >
                            Reset Filter Kategori &times;
                        </button>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Left (2 cols): Projects Section (With Folder Selesai & Filter) -->
                    <div class="lg:col-span-2 space-y-6 sm:space-y-8">
                        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-5">
                            <!-- Section Header & Filter Tabs -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center space-x-2">
                                        <AppIcon name="folder" :size="18" class="text-emerald-700" />
                                        <span>Proyek R&D Workspace</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Pilih proyek untuk membuka Roadmap, Logbook, RAB, atau ubah status dan sematkan proyek favorit.
                                    </p>
                                </div>

                                <Link
                                    :href="route('projects.index')"
                                    class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition-colors flex items-center self-start sm:self-auto"
                                >
                                    <span>Buka Semua Proyek</span>
                                    <AppIcon name="chevron-right" :size="14" class="ml-1" />
                                </Link>
                            </div>

                            <!-- Dashboard Quick View Filter Tabs -->
                            <div class="flex items-center space-x-1.5 overflow-x-auto text-xs pb-1">
                                <button
                                    @click="dashboardFilter = 'active'"
                                    :class="dashboardFilter === 'active' ? 'bg-emerald-700 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap active:scale-95"
                                >
                                    ⚡ Aktif & Berjalan ({{ (projects || []).filter(p => p.status === 'active').length }})
                                </button>

                                <button
                                    @click="dashboardFilter = 'completed'"
                                    :class="dashboardFilter === 'completed' ? 'bg-emerald-800 text-white font-extrabold shadow-xs ring-2 ring-emerald-500/30' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100 border border-emerald-200 font-bold'"
                                    class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap active:scale-95"
                                >
                                    📁 Folder Sudah Selesai ({{ (projects || []).filter(p => p.status === 'completed').length }})
                                </button>

                                <button
                                    @click="dashboardFilter = 'pinned'"
                                    :class="dashboardFilter === 'pinned' ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap active:scale-95"
                                >
                                    📌 Disematkan ({{ (projects || []).filter(p => p.is_pinned).length }})
                                </button>

                                <button
                                    @click="dashboardFilter = 'all'"
                                    :class="dashboardFilter === 'all' ? 'bg-slate-800 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap active:scale-95"
                                >
                                    Semua ({{ projects.length }})
                                </button>
                            </div>

                            <!-- Empty List -->
                            <div v-if="filteredProjects.length === 0" class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <AppIcon name="folder" :size="32" class="text-slate-400 mx-auto" />
                                <p class="text-sm font-semibold text-slate-700 mt-2">
                                    {{ dashboardFilter === 'completed' ? 'Belum ada proyek di Folder Sudah Selesai' : 'Tidak ada proyek pada tampilan ini' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                    {{ dashboardFilter === 'completed' ? 'Ubah status proyek menjadi "Sudah Selesai" untuk mengarsipkannya di sini.' : 'Buat proyek baru atau pilih tab filter lain.' }}
                                </p>
                            </div>

                            <!-- Projects List Cards -->
                            <div v-else class="space-y-4">
                                <div
                                    v-for="project in filteredProjects.slice(0, 6)"
                                    :key="project.id"
                                    :class="{
                                        'border-amber-300 ring-1 ring-amber-300/40 bg-gradient-to-r from-amber-50/20 via-white to-white': project.is_pinned,
                                        'border-slate-200/90 hover:border-emerald-300 hover:shadow-md bg-white': !project.is_pinned
                                    }"
                                    class="p-4 sm:p-5 rounded-2xl border transition-all relative group space-y-3"
                                >
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                        <div class="space-y-1.5 flex-1 sm:pr-4">
                                            <!-- Meta Badges & Pin Icon -->
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border"
                                                    :class="getModeBadge(project.mode).color"
                                                >
                                                    <AppIcon :name="getModeBadge(project.mode).icon" :size="12" class="mr-1.5" />
                                                    <span>{{ getModeBadge(project.mode).label }}</span>
                                                </span>

                                                <span
                                                    v-if="project.label"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200"
                                                >
                                                    <span>🏷️ {{ project.label }}</span>
                                                </span>

                                                <span
                                                    v-if="project.is_pinned"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300"
                                                >
                                                    <AppIcon name="pin" :size="10" class="mr-1 text-amber-600" />
                                                    <span>Disematkan</span>
                                                </span>

                                                <!-- Status Badge with Dropdown Trigger -->
                                                <div class="relative inline-block">
                                                    <button
                                                        @click="toggleStatusDropdown(project.id, $event)"
                                                        :class="getStatusBadge(project.status).color"
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] border transition-all active:scale-95"
                                                        title="Klik untuk mengubah status"
                                                    >
                                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="getStatusBadge(project.status).dot"></span>
                                                        <span>{{ getStatusBadge(project.status).label }}</span>
                                                        <span class="ml-1 text-[9px]">▾</span>
                                                    </button>

                                                    <!-- Dropdown Status -->
                                                    <div
                                                        v-if="activeStatusDropdownId === project.id"
                                                        class="absolute left-0 mt-1 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-30 animate-fade-in text-xs space-y-0.5"
                                                        @click.stop
                                                    >
                                                        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                                            Ubah Status Proyek
                                                        </div>
                                                        <button
                                                            @click="changeProjectStatus(project, 'active', $event)"
                                                            :class="project.status === 'active' ? 'bg-emerald-50 text-emerald-900 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                                            class="w-full text-left px-3 py-1.5 flex items-center space-x-2"
                                                        >
                                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                            <span>⚡ Aktif & Berjalan</span>
                                                        </button>
                                                        <button
                                                            @click="changeProjectStatus(project, 'completed', $event)"
                                                            :class="project.status === 'completed' ? 'bg-emerald-50 text-emerald-900 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                                            class="w-full text-left px-3 py-1.5 flex items-center space-x-2"
                                                        >
                                                            <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                                            <span>📁 Sudah Selesai (Folder Selesai)</span>
                                                        </button>
                                                        <button
                                                            @click="changeProjectStatus(project, 'draft', $event)"
                                                            :class="project.status === 'draft' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                                            class="w-full text-left px-3 py-1.5 flex items-center space-x-2"
                                                        >
                                                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                                            <span>📝 Draf</span>
                                                        </button>
                                                        <button
                                                            @click="changeProjectStatus(project, 'on_hold', $event)"
                                                            :class="project.status === 'on_hold' ? 'bg-amber-50 text-amber-900 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                                            class="w-full text-left px-3 py-1.5 flex items-center space-x-2"
                                                        >
                                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                            <span>⏸️ Tertunda</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <h3 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">
                                                <Link :href="route('projects.show', project.slug || project.id)">
                                                    {{ project.title }}
                                                </Link>
                                            </h3>

                                            <p class="text-xs text-slate-500 line-clamp-2">
                                                {{ project.problem_statement || project.description || 'Belum ada ringkasan deskripsi proyek.' }}
                                            </p>
                                        </div>

                                        <!-- Right Controls: Pin, Edit, Delete, Workspace -->
                                        <div class="shrink-0 flex items-center sm:flex-col sm:items-end justify-between gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                            <div class="flex items-center space-x-1">
                                                <!-- Pin Button -->
                                                <button
                                                    @click="togglePin(project, $event)"
                                                    :class="project.is_pinned ? 'bg-amber-100 text-amber-700 border-amber-300' : 'text-slate-400 hover:text-amber-600 hover:bg-amber-50 border-transparent'"
                                                    class="p-1.5 rounded-lg border transition-all"
                                                    :title="project.is_pinned ? 'Lepas Sematan' : 'Sematkan ke Teratas (Pin)'"
                                                >
                                                    <AppIcon name="pin" :size="13" :class="project.is_pinned ? 'fill-amber-500' : ''" />
                                                </button>

                                                <!-- Quick Edit -->
                                                <button
                                                    v-if="project.user_id === $page.props.auth.user.id"
                                                    @click="openEditModal(project, $event)"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-700 hover:bg-slate-100"
                                                    title="Edit Proyek"
                                                >
                                                    <AppIcon name="edit" :size="13" />
                                                </button>

                                                <!-- Quick Delete -->
                                                <button
                                                    v-if="project.user_id === $page.props.auth.user.id"
                                                    @click="openDeleteModal(project, $event)"
                                                    class="p-1.5 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50"
                                                    title="Hapus Proyek"
                                                >
                                                    <AppIcon name="trash" :size="13" />
                                                </button>
                                            </div>

                                            <Link
                                                :href="route('projects.show', project.slug || project.id)"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 group-hover:bg-emerald-600 group-hover:text-white transition-all active:scale-95"
                                            >
                                                <span>Buka Workspace</span>
                                                <AppIcon name="arrow-right" :size="12" class="ml-1" />
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500">
                                        <div class="flex items-center space-x-2 w-full sm:w-1/2">
                                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div
                                                    class="bg-emerald-600 h-1.5 rounded-full transition-all"
                                                    :style="{ width: `${project.progress_percent}%` }"
                                                ></div>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-700 shrink-0">{{ project.progress_percent }}%</span>
                                        </div>
                                        <div class="flex items-center space-x-3 text-slate-400 text-[11px]">
                                            <span>{{ project.tasks?.length || 0 }} Tugas</span>
                                            <span>•</span>
                                            <Link
                                                :href="route('projects.show', { project: project.slug || project.id, tab: 'pitch_deck' })"
                                                class="hover:text-emerald-700 hover:underline flex items-center gap-1 font-semibold text-slate-500"
                                                title="Buka Dokumen Proposal & Slide Pitch Deck"
                                            >
                                                <AppIcon name="presentation" :size="11" class="text-emerald-600" />
                                                <span>Proposal & Deck</span>
                                            </Link>
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
                                    <span><strong>Pin Proyek:</strong> Sematkan proyek utama di teratas untuk akses cepat.</span>
                                </li>
                                <li class="flex items-start">
                                    <AppIcon name="check" :size="14" class="text-emerald-400 mr-2 shrink-0 mt-0.5" />
                                    <span><strong>Folder Sudah:</strong> Proyek yang telah selesai langsung terarsip rapi.</span>
                                </li>
                                <li class="flex items-start">
                                    <AppIcon name="check" :size="14" class="text-emerald-400 mr-2 shrink-0 mt-0.5" />
                                    <span><strong>Public Share:</strong> Reviewer/mitra cukup buka 1 link tanpa perlu login.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: EDIT PROYEK DARI DASHBOARD -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-2xl w-full p-5 sm:p-8 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto animate-fade-in">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Edit Informasi Proyek</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Perbarui nama proyek, kategori, atau status pengerjaan.</p>
                    </div>
                    <button
                        @click="isEditModalOpen = false"
                        class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100"
                    >
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>

                <form @submit.prevent="submitEditProject" class="mt-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Proyek</label>
                            <select
                                v-model="editForm.status"
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                            >
                                <option value="active">⚡ Aktif & Berjalan</option>
                                <option value="completed">📁 Sudah Selesai (Folder Selesai)</option>
                                <option value="draft">📝 Draf</option>
                                <option value="on_hold">⏸️ Tertunda (On Hold)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori / Mode</label>
                            <select
                                v-model="editForm.mode"
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                            >
                                <option value="research">Research Development</option>
                                <option value="product">Product Development</option>
                                <option value="social">Social Project</option>
                                <option value="competition">Lomba & Kompetisi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Proyek</label>
                        <input
                            v-model="editForm.title"
                            type="text"
                            required
                            class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Label Proyek / Inisiatif <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input
                            v-model="editForm.label"
                            type="text"
                            placeholder="Misal: Startup Agronex, Ristek Yota, Smart Soil, PKM 2026..."
                            class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-emerald-500"
                        />
                        <div class="flex items-center gap-1.5 mt-1.5 flex-wrap text-2xs text-slate-500">
                            <span>Saran:</span>
                            <button
                                v-for="preset in ['Startup Agronex', 'Ristek Yota', 'Smart Soil', 'Inovasi Desa', 'PKM Dikti']"
                                :key="preset"
                                type="button"
                                @click="editForm.label = preset"
                                class="px-2 py-0.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 transition-colors"
                            >
                                + {{ preset }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Rumusan Masalah</label>
                        <textarea
                            v-model="editForm.problem_statement"
                            rows="2"
                            class="w-full text-xs px-4 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Target Luaran</label>
                        <input
                            v-model="editForm.target_outcome"
                            type="text"
                            class="w-full text-xs px-4 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="isEditModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="px-5 py-2.5 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs disabled:opacity-50"
                        >
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: HAPUS PROYEK DARI DASHBOARD -->
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-fade-in space-y-4">
                <div class="flex items-center space-x-3 text-rose-600">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 flex items-center justify-center shrink-0">
                        <AppIcon name="trash" :size="20" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Hapus Proyek?</h3>
                        <p class="text-xs text-slate-500">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>

                <p class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    Yakin ingin menghapus <strong class="text-slate-900">"{{ projectToDelete?.title }}"</strong>? Seluruh data roadmap, tugas, dan logbook di dalamnya akan dihapus permanen.
                </p>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button
                        type="button"
                        @click="isDeleteModalOpen = false; projectToDelete = null"
                        class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmDeleteProject"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 shadow-xs active:scale-95 transition-all"
                    >
                        Ya, Hapus Proyek
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

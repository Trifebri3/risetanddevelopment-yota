<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    projects: Array,
    filters: Object,
    statusCounts: Object,
    availableLabels: {
        type: Array,
        default: () => [],
    },
});

const isModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const projectToDelete = ref(null);

const searchInput = ref(props.filters?.search || '');
const selectedScope = ref(props.filters?.scope || 'all');
const selectedMode = ref(props.filters?.mode || 'all');
const selectedStatus = ref(props.filters?.status || 'all');
const selectedLabel = ref(props.filters?.label || 'all');

// Status Dropdown State for cards
const activeStatusDropdownId = ref(null);
const toggleStatusDropdown = (projectId, event) => {
    event.stopPropagation();
    activeStatusDropdownId.value = activeStatusDropdownId.value === projectId ? null : projectId;
};

// Close dropdown on click outside
if (typeof window !== 'undefined') {
    window.addEventListener('click', () => {
        activeStatusDropdownId.value = null;
    });
}

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('create') === 'true') {
        isModalOpen.value = true;
    }
});

// Create Project Form
const form = useForm({
    title: '',
    mode: 'research',
    label: '',
    description: '',
    problem_statement: '',
    target_outcome: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
});

const submitCreateProject = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

// Edit Project Form
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

// Delete Project
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

// 1-Click Pin / Unpin Project
const togglePin = (project, event) => {
    if (event) event.stopPropagation();
    router.post(route('projects.toggle_pin', project.slug || project.id), {}, { preserveScroll: true });
};

// 1-Click Status Change
const changeProjectStatus = (project, newStatus, event) => {
    if (event) event.stopPropagation();
    activeStatusDropdownId.value = null;
    router.patch(route('projects.update_status', project.slug || project.id), { status: newStatus }, { preserveScroll: true });
};

const filterByLabel = (label) => {
    selectedLabel.value = label;
    applyFilter();
};

const applyFilter = (newStatus = null) => {
    if (newStatus !== null) {
        selectedStatus.value = newStatus;
    }
    router.get(
        route('projects.index'),
        {
            search: searchInput.value || undefined,
            scope: selectedScope.value !== 'all' ? selectedScope.value : undefined,
            mode: selectedMode.value !== 'all' ? selectedMode.value : undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            label: selectedLabel.value !== 'all' ? selectedLabel.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

// Mode & Status styling helpers
const getModeInfo = (mode) => {
    switch (mode) {
        case 'research':
            return {
                label: 'Research Development',
                badge: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'research',
            };
        case 'product':
            return {
                label: 'Product Development',
                badge: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'product',
            };
        case 'social':
            return {
                label: 'Social Project Development',
                badge: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'social',
            };
        case 'competition':
            return {
                label: 'Lomba & Kompetisi',
                badge: 'bg-amber-50 text-amber-800 border-amber-200',
                icon: 'competition',
            };
        default:
            return {
                label: 'Umum',
                badge: 'bg-slate-50 text-slate-700 border-slate-200',
                icon: 'folder',
            };
    }
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'completed':
            return {
                label: 'Sudah Selesai',
                color: 'bg-emerald-600 text-white border-emerald-600 shadow-2xs font-extrabold',
                dot: 'bg-white',
                folderTag: 'Folder Sudah',
            };
        case 'active':
            return {
                label: 'Aktif & Berjalan',
                color: 'bg-emerald-50 text-emerald-800 border-emerald-200 font-bold',
                dot: 'bg-emerald-500 animate-pulse',
                folderTag: 'Aktif',
            };
        case 'draft':
            return {
                label: 'Draf',
                color: 'bg-slate-100 text-slate-700 border-slate-300 font-semibold',
                dot: 'bg-slate-400',
                folderTag: 'Draf',
            };
        case 'on_hold':
            return {
                label: 'Tertunda (On Hold)',
                color: 'bg-amber-50 text-amber-800 border-amber-200 font-semibold',
                dot: 'bg-amber-500',
                folderTag: 'Tertunda',
            };
        default:
            return {
                label: status,
                color: 'bg-slate-100 text-slate-700 border-slate-200',
                dot: 'bg-slate-400',
                folderTag: status,
            };
    }
};
</script>

<template>
    <Head title="Daftar Proyek R&D" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                        Proyek Riset & Pengembangan
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Kelola seluruh inisiatif Anda dengan pemantauan terstruktur, pin proyek favorit, dan folder status.
                    </p>
                </div>
                <div>
                    <button
                        @click="isModalOpen = true"
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-all hover:shadow active:scale-95"
                    >
                        <AppIcon name="plus" :size="16" class="mr-2" />
                        <span>Buat Proyek Baru</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-5 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- 1. Folder & Status Tabs (Folder Sudah / Aktif / Pin / Semua) -->
                <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-none text-xs">
                    <button
                        @click="applyFilter('all')"
                        :class="selectedStatus === 'all' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'"
                        class="px-4 py-2 rounded-xl transition-all flex items-center space-x-2 shrink-0 active:scale-95"
                    >
                        <AppIcon name="folder" :size="14" />
                        <span>Semua Proyek</span>
                        <span :class="selectedStatus === 'all' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                            {{ statusCounts?.all ?? projects.length }}
                        </span>
                    </button>

                    <button
                        @click="applyFilter('active')"
                        :class="selectedStatus === 'active' ? 'bg-emerald-700 text-white font-bold shadow-xs ring-2 ring-emerald-500/30' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'"
                        class="px-4 py-2 rounded-xl transition-all flex items-center space-x-2 shrink-0 active:scale-95"
                    >
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Aktif & Berjalan</span>
                        <span :class="selectedStatus === 'active' ? 'bg-emerald-800 text-emerald-100' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                            {{ statusCounts?.active ?? 0 }}
                        </span>
                    </button>

                    <!-- FOLDER SUDAH (COMPLETED PROJECTS) -->
                    <button
                        @click="applyFilter('completed')"
                        :class="selectedStatus === 'completed' ? 'bg-emerald-800 text-white font-extrabold shadow-sm ring-2 ring-emerald-600/40' : 'bg-emerald-50/70 text-emerald-900 hover:bg-emerald-100/70 border border-emerald-300 font-bold'"
                        class="px-4 py-2 rounded-xl transition-all flex items-center space-x-2 shrink-0 active:scale-95"
                        title="Buka Folder Proyek Sudah Selesai"
                    >
                        <AppIcon name="folder-check" :size="14" class="text-emerald-300" />
                        <span>📁 Folder Sudah (Selesai)</span>
                        <span :class="selectedStatus === 'completed' ? 'bg-emerald-900 text-white' : 'bg-emerald-200/80 text-emerald-900'" class="px-2 py-0.2 rounded-full text-[10px] font-extrabold">
                            {{ statusCounts?.completed ?? 0 }}
                        </span>
                    </button>

                    <!-- PINNED PROJECTS TAB -->
                    <button
                        @click="applyFilter('pinned')"
                        :class="selectedStatus === 'pinned' ? 'bg-amber-600 text-white font-bold shadow-xs ring-2 ring-amber-500/30' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'"
                        class="px-4 py-2 rounded-xl transition-all flex items-center space-x-2 shrink-0 active:scale-95"
                    >
                        <AppIcon name="pin" :size="14" class="text-amber-300" />
                        <span>📌 Disematkan</span>
                        <span :class="selectedStatus === 'pinned' ? 'bg-amber-700 text-white' : 'bg-amber-50 text-amber-800 border border-amber-200'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
                            {{ statusCounts?.pinned ?? 0 }}
                        </span>
                    </button>

                    <button
                        @click="applyFilter('draft')"
                        :class="selectedStatus === 'draft' ? 'bg-slate-800 text-white font-bold shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'"
                        class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 shrink-0 active:scale-95"
                    >
                        <span>Draf</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-600 font-semibold">
                            {{ statusCounts?.draft ?? 0 }}
                        </span>
                    </button>

                    <button
                        @click="applyFilter('on_hold')"
                        :class="selectedStatus === 'on_hold' ? 'bg-amber-700 text-white font-bold shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'"
                        class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 shrink-0 active:scale-95"
                    >
                        <span>Tertunda</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-600 font-semibold">
                            {{ statusCounts?.on_hold ?? 0 }}
                        </span>
                    </button>
                </div>

                <!-- 2. Folder Sudah Banner (When inside completed folder) -->
                <div
                    v-if="selectedStatus === 'completed'"
                    class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-white border border-emerald-200/90 shadow-2xs flex items-center justify-between gap-3 animate-fade-in"
                >
                    <div class="flex items-center space-x-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-xs shrink-0">
                            <AppIcon name="folder-check" :size="20" />
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-emerald-950 flex items-center space-x-2">
                                <span>Folder Proyek Sudah Selesai</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-200 text-emerald-900 font-bold uppercase tracking-wider">Arsip Tervalidasi</span>
                            </h3>
                            <p class="text-xs text-emerald-800/80 mt-0.5">
                                Seluruh proyek di folder ini telah rampung. Anda dapat meninjau kembali ringkasan luaran, logbook, atau mengaktifkannya kembali kapan saja.
                            </p>
                        </div>
                    </div>

                    <button
                        @click="applyFilter('all')"
                        class="px-3 py-1.5 text-xs font-semibold text-emerald-800 bg-white border border-emerald-200 rounded-xl hover:bg-emerald-50 shrink-0"
                    >
                        Tampilkan Semua &rarr;
                    </button>
                </div>

                <!-- 3. Scope Tabs & Search / Filter Controls -->
                <div class="flex items-center space-x-2 text-xs font-semibold overflow-x-auto pb-1">
                    <button
                        @click="selectedScope = 'all'; applyFilter()"
                        :class="selectedScope === 'all' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap active:scale-95"
                    >
                        Semua Kepemilikan
                    </button>
                    <button
                        @click="selectedScope = 'owned'; applyFilter()"
                        :class="selectedScope === 'owned' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap active:scale-95"
                    >
                        Milik Saya
                    </button>
                    <button
                        @click="selectedScope = 'team'; applyFilter()"
                        :class="selectedScope === 'team' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl transition-all flex items-center whitespace-nowrap active:scale-95"
                    >
                        <AppIcon name="users" :size="13" class="mr-1.5" />
                        <span>Kolaborasi Tim</span>
                    </button>
                </div>

                <!-- Filters & Search Bar -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input
                            v-model="searchInput"
                            @keyup.enter="applyFilter()"
                            type="text"
                            placeholder="Cari judul proyek, rumusan masalah, atau luaran..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                        />
                        <AppIcon name="search" :size="16" class="text-slate-400 absolute left-3.5 top-3" />
                    </div>

                    <!-- Mode Category Buttons -->
                    <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold overflow-x-auto max-w-full">
                        <button
                            @click="selectedMode = 'all'; applyFilter()"
                            :class="selectedMode === 'all' ? 'bg-white shadow-xs text-slate-900 font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap"
                        >
                            Semua Kategori
                        </button>
                        <button
                            @click="selectedMode = 'research'; applyFilter()"
                            :class="selectedMode === 'research' ? 'bg-white shadow-xs text-emerald-800 font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-lg transition-all flex items-center whitespace-nowrap"
                        >
                            <AppIcon name="research" :size="13" class="mr-1" />
                            <span>Riset</span>
                        </button>
                        <button
                            @click="selectedMode = 'product'; applyFilter()"
                            :class="selectedMode === 'product' ? 'bg-white shadow-xs text-emerald-800 font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-lg transition-all flex items-center whitespace-nowrap"
                        >
                            <AppIcon name="product" :size="13" class="mr-1" />
                            <span>Produk</span>
                        </button>
                        <button
                            @click="selectedMode = 'social'; applyFilter()"
                            :class="selectedMode === 'social' ? 'bg-white shadow-xs text-emerald-800 font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-lg transition-all flex items-center whitespace-nowrap"
                        >
                            <AppIcon name="social" :size="13" class="mr-1" />
                            <span>Sosial</span>
                        </button>
                        <button
                            @click="selectedMode = 'competition'; applyFilter()"
                            :class="selectedMode === 'competition' ? 'bg-white shadow-xs text-amber-800 font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-lg transition-all flex items-center whitespace-nowrap"
                        >
                            <AppIcon name="competition" :size="13" class="mr-1" />
                            <span>Lomba</span>
                        </button>
                    </div>
                </div>

                <!-- Label Filter Bar (Startup Agronex, Ristek Yota, etc.) -->
                <div v-if="availableLabels && availableLabels.length > 0" class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1 mr-1">
                        <AppIcon name="tag" :size="12" class="text-slate-400" />
                        <span>Filter Label:</span>
                    </span>
                    <button
                        type="button"
                        @click="filterByLabel('all')"
                        class="px-2.5 py-1 rounded-xl text-xs font-semibold transition-all border"
                        :class="selectedLabel === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-2xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                    >
                        Semua Label
                    </button>
                    <button
                        v-for="lbl in availableLabels"
                        :key="lbl"
                        type="button"
                        @click="filterByLabel(lbl)"
                        class="px-2.5 py-1 rounded-xl text-xs font-semibold transition-all border inline-flex items-center gap-1"
                        :class="selectedLabel === lbl ? 'bg-emerald-700 text-white border-emerald-700 shadow-2xs' : 'bg-emerald-50/60 text-emerald-800 border-emerald-200 hover:bg-emerald-100'"
                    >
                        <span>🏷️</span>
                        <span>{{ lbl }}</span>
                    </button>
                </div>

                <!-- 4. Projects Cards Grid -->
                <div v-if="projects.length === 0" class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center">
                    <AppIcon name="folder" :size="36" class="text-slate-300 mx-auto" />
                    <h3 class="text-sm sm:text-base font-bold text-slate-800 mt-3">
                        {{ selectedStatus === 'completed' ? 'Folder Sudah Selesai masih kosong' : 'Tidak ada proyek yang sesuai filter' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        {{ selectedStatus === 'completed' ? 'Proyek yang telah selesai akan secara otomatis terarsipkan di folder ini.' : 'Coba ubah kata kunci pencarian atau buat proyek baru sesuai kebutuhan Anda.' }}
                    </p>
                    <button
                        @click="isModalOpen = true"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-xs"
                    >
                        <AppIcon name="plus" :size="14" class="mr-1.5" />
                        <span>Buat Proyek Baru</span>
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    <div
                        v-for="project in projects"
                        :key="project.id"
                        :class="{
                            'border-amber-300 ring-2 ring-amber-400/20 shadow-md bg-gradient-to-b from-amber-50/25 to-white': project.is_pinned,
                            'border-emerald-200/90 bg-emerald-50/10': project.status === 'completed',
                            'border-slate-200/90 bg-white hover:border-emerald-300 hover:shadow-md': !project.is_pinned && project.status !== 'completed'
                        }"
                        class="rounded-2xl sm:rounded-3xl border transition-all flex flex-col justify-between p-5 sm:p-6 relative group"
                    >
                        <!-- Top Header on Card: Mode & Pin & Actions -->
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border"
                                        :class="getModeInfo(project.mode).badge"
                                    >
                                        <AppIcon :name="getModeInfo(project.mode).icon" :size="12" class="mr-1.5" />
                                        <span>{{ getModeInfo(project.mode).label }}</span>
                                    </span>

                                    <!-- Label Badge (Startup Agronex, Ristek Yota, etc.) -->
                                    <span
                                        v-if="project.label"
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200"
                                    >
                                        <span>🏷️ {{ project.label }}</span>
                                    </span>

                                    <!-- Pinned Badge if active -->
                                    <span
                                        v-if="project.is_pinned"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs"
                                        title="Proyek disematkan ke daftar teratas"
                                    >
                                        <AppIcon name="pin" :size="10" class="mr-1 text-amber-600" />
                                        <span>Disematkan</span>
                                    </span>
                                </div>

                                <!-- Action Buttons: 1-Click Pin + 3-Dots Menu -->
                                <div class="flex items-center space-x-1">
                                    <!-- 1-Click Pin Toggle Button -->
                                    <button
                                        @click="togglePin(project, $event)"
                                        :class="project.is_pinned ? 'bg-amber-100 text-amber-700 hover:bg-amber-200 border-amber-300' : 'text-slate-400 hover:text-amber-600 hover:bg-amber-50 border-transparent'"
                                        class="p-1.5 rounded-lg border transition-all"
                                        :title="project.is_pinned ? 'Lepas Sematan' : 'Sematkan Proyek ke Teratas (Pin)'"
                                    >
                                        <AppIcon name="pin" :size="14" :class="project.is_pinned ? 'fill-amber-500' : ''" />
                                    </button>

                                    <!-- Quick Edit Modal Trigger -->
                                    <button
                                        v-if="project.user_id === $page.props.auth.user.id"
                                        @click="openEditModal(project, $event)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-700 hover:bg-slate-100 transition-colors"
                                        title="Edit Informasi Proyek"
                                    >
                                        <AppIcon name="edit" :size="14" />
                                    </button>

                                    <!-- Delete Button Trigger -->
                                    <button
                                        v-if="project.user_id === $page.props.auth.user.id"
                                        @click="openDeleteModal(project, $event)"
                                        class="p-1.5 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Hapus Proyek"
                                    >
                                        <AppIcon name="trash" :size="14" />
                                    </button>
                                </div>
                            </div>

                            <!-- Title & Problem Statement -->
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-2">
                                <Link :href="route('projects.show', project.slug || project.id)">
                                    {{ project.title }}
                                </Link>
                            </h3>

                            <p class="text-xs text-slate-500 mt-2 line-clamp-3 leading-relaxed">
                                {{ project.problem_statement || project.description || 'Belum ada ringkasan deskripsi proyek.' }}
                            </p>
                        </div>

                        <!-- Bottom Section: Status Selector & Progress -->
                        <div class="mt-6 pt-4 border-t border-slate-100 space-y-3.5">
                            <!-- Status Selector Dropdown (Ubah Status 1-Klik) -->
                            <div class="flex items-center justify-between text-xs relative">
                                <span class="text-slate-400 text-[11px] font-medium">Status Proyek:</span>

                                <div class="relative">
                                    <button
                                        @click="toggleStatusDropdown(project.id, $event)"
                                        :class="getStatusBadge(project.status).color"
                                        class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs border transition-all hover:opacity-90 active:scale-95 shadow-2xs"
                                        title="Klik untuk mengubah status proyek"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="getStatusBadge(project.status).dot"></span>
                                        <span>{{ getStatusBadge(project.status).label }}</span>
                                        <span class="ml-1 text-[10px]">▾</span>
                                    </button>

                                    <!-- Status Switcher Dropdown Menu -->
                                    <div
                                        v-if="activeStatusDropdownId === project.id"
                                        class="absolute right-0 bottom-full mb-1 w-52 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-1.5 z-30 animate-fade-in text-xs space-y-0.5"
                                        @click.stop
                                    >
                                        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                            Ubah Status Proyek
                                        </div>
                                        <button
                                            @click="changeProjectStatus(project, 'active', $event)"
                                            :class="project.status === 'active' ? 'bg-emerald-50 text-emerald-900 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                            class="w-full text-left px-3 py-2 flex items-center space-x-2 transition-colors"
                                        >
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>⚡ Aktif & Berjalan</span>
                                        </button>
                                        <button
                                            @click="changeProjectStatus(project, 'completed', $event)"
                                            :class="project.status === 'completed' ? 'bg-emerald-50 text-emerald-900 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                            class="w-full text-left px-3 py-2 flex items-center space-x-2 transition-colors"
                                        >
                                            <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                            <span>📁 Sudah Selesai (Folder Selesai)</span>
                                        </button>
                                        <button
                                            @click="changeProjectStatus(project, 'draft', $event)"
                                            :class="project.status === 'draft' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                            class="w-full text-left px-3 py-2 flex items-center space-x-2 transition-colors"
                                        >
                                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                            <span>📝 Draf</span>
                                        </button>
                                        <button
                                            @click="changeProjectStatus(project, 'on_hold', $event)"
                                            :class="project.status === 'on_hold' ? 'bg-amber-50 text-amber-900 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                            class="w-full text-left px-3 py-2 flex items-center space-x-2 transition-colors"
                                        >
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span>⏸️ Tertunda (On Hold)</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="text-slate-500 font-medium text-[11px]">Progres</span>
                                    <span class="font-extrabold text-slate-900 text-xs">{{ project.progress_percent }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div
                                        class="bg-emerald-600 h-2 rounded-full transition-all duration-500"
                                        :style="{ width: `${project.progress_percent}%` }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Counts and Action Button -->
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center space-x-2 text-[11px] text-slate-400">
                                    <span>{{ project.tasks?.length || 0 }} Tugas</span>
                                    <span>•</span>
                                    <Link
                                        :href="route('projects.show', { project: project.slug || project.id, tab: 'pitch_deck' })"
                                        class="hover:text-emerald-700 hover:underline flex items-center gap-1 font-medium text-slate-500"
                                        title="Buka Dokumen Proposal & Slide Pitch Deck"
                                    >
                                        <AppIcon name="presentation" :size="12" class="text-emerald-600" />
                                        <span>Proposal & Deck</span>
                                    </Link>
                                </div>

                                <Link
                                    :href="route('projects.show', project.slug || project.id)"
                                    class="inline-flex items-center px-3.5 py-1.5 text-xs font-bold rounded-xl bg-slate-100 text-slate-700 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-xs active:scale-95"
                                >
                                    <span>Workspace</span>
                                    <AppIcon name="arrow-right" :size="12" class="ml-1" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 1: BUAT PROYEK BARU -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-2xl w-full p-5 sm:p-8 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto animate-fade-in">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Buat Proyek Baru</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Isi detail penting di bawah. Anda bisa melengkapinya nanti kapan saja.</p>
                    </div>
                    <button
                        @click="isModalOpen = false"
                        class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100"
                    >
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>

                <form @submit.prevent="submitCreateProject" class="mt-5 space-y-4 sm:space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Pilih Mode Proyek</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                            <label
                                :class="form.mode === 'research' ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50'"
                                class="border rounded-2xl p-3 sm:p-3.5 cursor-pointer transition-all flex flex-col justify-between"
                            >
                                <input type="radio" v-model="form.mode" value="research" class="sr-only" />
                                <div>
                                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center mb-2">
                                        <AppIcon name="research" :size="18" />
                                    </div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-900">Research Dev</div>
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Metodologi, data & manuskrip.</p>
                                </div>
                            </label>

                            <label
                                :class="form.mode === 'product' ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50'"
                                class="border rounded-2xl p-3 sm:p-3.5 cursor-pointer transition-all flex flex-col justify-between"
                            >
                                <input type="radio" v-model="form.mode" value="product" class="sr-only" />
                                <div>
                                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center mb-2">
                                        <AppIcon name="product" :size="18" />
                                    </div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-900">Product Dev</div>
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Kebutuhan teknis & rilis MVP.</p>
                                </div>
                            </label>

                            <label
                                :class="form.mode === 'social' ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50'"
                                class="border rounded-2xl p-3 sm:p-3.5 cursor-pointer transition-all flex flex-col justify-between"
                            >
                                <input type="radio" v-model="form.mode" value="social" class="sr-only" />
                                <div>
                                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center mb-2">
                                        <AppIcon name="social" :size="18" />
                                    </div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-900">Social Project</div>
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Aset warga & dampak sosial.</p>
                                </div>
                            </label>

                            <label
                                :class="form.mode === 'competition' ? 'border-amber-600 bg-amber-50/70 ring-2 ring-amber-500/20' : 'border-slate-200 hover:bg-slate-50'"
                                class="border rounded-2xl p-3 sm:p-3.5 cursor-pointer transition-all flex flex-col justify-between"
                            >
                                <input type="radio" v-model="form.mode" value="competition" class="sr-only" />
                                <div>
                                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mb-2">
                                        <AppIcon name="competition" :size="18" />
                                    </div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-900">Lomba & Inovasi</div>
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Rubrik penilaian & submisi.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Proyek <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="Contoh: Pengembangan Model AI Deteksi Dini Penyakit Padi"
                            class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Label Proyek / Inisiatif <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input
                            v-model="form.label"
                            type="text"
                            placeholder="Misal: Startup Agronex, Ristek Yota, Smart Soil, PKM 2026..."
                            class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                        <div class="flex items-center gap-1.5 mt-1.5 flex-wrap text-2xs text-slate-500">
                            <span>Saran:</span>
                            <button
                                v-for="preset in ['Startup Agronex', 'Ristek Yota', 'Smart Soil', 'Inovasi Desa', 'PKM Dikti']"
                                :key="preset"
                                type="button"
                                @click="form.label = preset"
                                class="px-2 py-0.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 transition-colors"
                            >
                                + {{ preset }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Masalah / Latar Belakang (Problem Statement)</label>
                        <textarea
                            v-model="form.problem_statement"
                            rows="2"
                            placeholder="Jelaskan masalah utama yang ingin dijawab atau diselesaikan..."
                            class="w-full text-xs sm:text-sm px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Target Luaran Utama</label>
                        <input
                            v-model="form.target_outcome"
                            type="text"
                            placeholder="Contoh: Manuskrip Scopus Q2, Prototipe Hardware MVP, atau Laporan Dampak"
                            class="w-full text-xs sm:text-sm px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Target Selesai</label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-xs disabled:opacity-50 active:scale-95"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan & Buka Workspace' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: EDIT PROYEK (CRUD LANGSUNG DARI CARD) -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-2xl w-full p-5 sm:p-8 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto animate-fade-in">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Edit Informasi Proyek</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Perbarui nama proyek, kategori, status pengerjaan, atau target luaran.</p>
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
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Rumusan Masalah (Problem Statement)</label>
                        <textarea
                            v-model="editForm.problem_statement"
                            rows="2"
                            class="w-full text-xs px-4 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Target Luaran Akhir</label>
                        <input
                            v-model="editForm.target_outcome"
                            type="text"
                            class="w-full text-xs px-4 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                            <input
                                v-model="editForm.start_date"
                                type="date"
                                class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Target Selesai</label>
                            <input
                                v-model="editForm.end_date"
                                type="date"
                                class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500"
                            />
                        </div>
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

        <!-- MODAL 3: KONFIRMASI HAPUS PROYEK -->
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
                    Yakin ingin menghapus <strong class="text-slate-900">"{{ projectToDelete?.title }}"</strong>? Seluruh roadmap, tugas, berkas luaran, dan catatan logbook di dalamnya akan dihapus permanen.
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

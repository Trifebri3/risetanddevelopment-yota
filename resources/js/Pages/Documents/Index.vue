<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';

const props = defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total_projects: 0,
            total_proposals: 0,
            submitted_proposals: 0,
            total_pitch_decks: 0,
            ready_pitch_decks: 0,
        }),
    },
    availableLabels: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            label: 'all',
            mode: 'all',
            search: '',
        }),
    },
});

const search = ref(props.filters.search || '');
const selectedLabel = ref(props.filters.label || 'all');
const selectedMode = ref(props.filters.mode || 'all');
const activeDocTypeTab = ref('all'); // 'all', 'proposals', 'pitch_decks'

const applyFilters = () => {
    router.get(
        route('documents.index'),
        {
            search: search.value || undefined,
            label: selectedLabel.value !== 'all' ? selectedLabel.value : undefined,
            mode: selectedMode.value !== 'all' ? selectedMode.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const filterByLabel = (label) => {
    selectedLabel.value = label;
    applyFilters();
};

const filterByMode = (mode) => {
    selectedMode.value = mode;
    applyFilters();
};

const quickSyncProject = (project) => {
    if (confirm(`Sinkronkan data proyek "${project.title}" ke Dokumen Proposal & 10-Slide Pitch Deck?`)) {
        router.post(route('projects.documents.sync', project.slug || project.id), {}, {
            preserveScroll: true,
        });
    }
};

const getProposalStatusBadge = (status) => {
    switch (status) {
        case 'submitted':
            return { label: 'Sudah Diajukan', bg: 'bg-emerald-100 text-emerald-800 border-emerald-300' };
        case 'final':
            return { label: 'Final Siap Kirim', bg: 'bg-blue-100 text-blue-800 border-blue-300' };
        case 'in_review':
            return { label: 'Sedang Review', bg: 'bg-amber-100 text-amber-800 border-amber-300' };
        default:
            return { label: 'Draf Proposal', bg: 'bg-slate-100 text-slate-700 border-slate-300' };
    }
};

const getPitchDeckStatusBadge = (status) => {
    switch (status) {
        case 'presented':
            return { label: 'Sudah Dipresentasikan', bg: 'bg-purple-100 text-purple-800 border-purple-300' };
        case 'ready':
            return { label: 'Siap Pitching (Ready)', bg: 'bg-emerald-100 text-emerald-800 border-emerald-300' };
        default:
            return { label: 'Draf Slide Deck', bg: 'bg-slate-100 text-slate-700 border-slate-300' };
    }
};

const filteredProjects = computed(() => {
    return props.projects.filter(project => {
        if (activeDocTypeTab.value === 'proposals') {
            return project.proposal !== null;
        }
        if (activeDocTypeTab.value === 'pitch_decks') {
            return project.pitchDeck !== null;
        }
        return true;
    });
});
</script>

<template>
    <Head title="Dokumen Proposal & Pitch Deck" />

    <AuthenticatedLayout>
        <div class="py-8 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Page Header Banner -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-44 h-44 bg-emerald-50 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-3">
                                <AppIcon name="presentation" :size="13" />
                                <span>Proposal & Pitch Hub</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                Dokumen Proposal & Pitch Deck
                            </h1>
                            <p class="mt-1.5 text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                                Pusat pengelolaan proposal hibah, riset, dan presentasi pitch deck investor terstruktur. Terintegrasi langsung dengan roadmap, anggaran, dan logbook proyek.
                            </p>
                        </div>

                        <!-- Stats Highlights -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 shrink-0">
                            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-center">
                                <span class="text-xs font-medium text-slate-500 block">Total Proyek</span>
                                <span class="text-xl font-bold text-slate-900">{{ stats.total_projects }}</span>
                            </div>
                            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-center">
                                <span class="text-xs font-medium text-slate-500 block">Proposal</span>
                                <span class="text-xl font-bold text-emerald-700">{{ stats.total_proposals }}</span>
                            </div>
                            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-center">
                                <span class="text-xs font-medium text-slate-500 block">Pitch Deck</span>
                                <span class="text-xl font-bold text-blue-700">{{ stats.total_pitch_decks }}</span>
                            </div>
                            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 text-center">
                                <span class="text-xs font-medium text-emerald-700 block">Siap / Diajukan</span>
                                <span class="text-xl font-bold text-emerald-800">{{ stats.submitted_proposals + stats.ready_pitch_decks }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Label Filter Bar (Startup Agronex, Ristek Yota, dll.) -->
                <div v-if="availableLabels && availableLabels.length > 0" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 mr-2 flex items-center gap-1.5">
                        <AppIcon name="tag" :size="13" class="text-slate-400" />
                        <span>Filter Label:</span>
                    </span>
                    <button
                        type="button"
                        @click="filterByLabel('all')"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border"
                        :class="selectedLabel === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                    >
                        Semua Label ({{ projects.length }})
                    </button>
                    <button
                        v-for="lbl in availableLabels"
                        :key="lbl"
                        type="button"
                        @click="filterByLabel(lbl)"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border inline-flex items-center gap-1.5"
                        :class="selectedLabel === lbl ? 'bg-emerald-700 text-white border-emerald-700 shadow-xs' : 'bg-emerald-50/60 text-emerald-800 border-emerald-200 hover:bg-emerald-100/70'"
                    >
                        <span>🏷️</span>
                        <span>{{ lbl }}</span>
                    </button>
                </div>

                <!-- Search & Type Filter Tabs -->
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
                    <!-- Doc Type Tabs -->
                    <div class="flex items-center gap-1.5 bg-white p-1 rounded-2xl border border-slate-200/80 shadow-2xs self-start">
                        <button
                            type="button"
                            @click="activeDocTypeTab = 'all'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all"
                            :class="activeDocTypeTab === 'all' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        >
                            Semua Dokumen
                        </button>
                        <button
                            type="button"
                            @click="activeDocTypeTab = 'proposals'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5"
                            :class="activeDocTypeTab === 'proposals' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        >
                            <AppIcon name="file-text" :size="13" />
                            <span>Proposal Saja ({{ stats.total_proposals }})</span>
                        </button>
                        <button
                            type="button"
                            @click="activeDocTypeTab = 'pitch_decks'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5"
                            :class="activeDocTypeTab === 'pitch_decks' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        >
                            <AppIcon name="presentation" :size="13" />
                            <span>Pitch Deck Saja ({{ stats.total_pitch_decks }})</span>
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-72">
                        <AppIcon name="search" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Cari proposal / deck / label..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"
                        />
                    </div>
                </div>

                <!-- Projects Documents Grid -->
                <div v-if="filteredProjects.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto text-slate-400 mb-3">
                        <AppIcon name="presentation" :size="28" />
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum ada dokumen yang sesuai filter</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Pilih proyek di bawah atau buka Proyek Saya untuk mulai menyusun proposal dan 10-slide pitch deck terstruktur.
                    </p>
                    <Link
                        :href="route('projects.index')"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-xs"
                    >
                        <span>Lihat Semua Proyek</span>
                        <AppIcon name="arrow-right" :size="14" class="ml-1.5" />
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="project in filteredProjects"
                        :key="project.id"
                        class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden"
                    >
                        <div class="p-5 sm:p-6 space-y-4">
                            <!-- Card Badges (Label & Mode) -->
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span v-if="project.label" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-2xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        <span>🏷️</span>
                                        <span>{{ project.label }}</span>
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-2xs font-semibold bg-slate-100 text-slate-700 capitalize">
                                        {{ project.mode }}
                                    </span>
                                </div>
                                <span class="text-2xs font-semibold text-slate-400">
                                    {{ project.progress_percent }}% Progres
                                </span>
                            </div>

                            <!-- Title & Description -->
                            <div>
                                <h3 class="text-base font-bold text-slate-900 line-clamp-1 hover:text-emerald-700 transition-colors">
                                    <Link :href="route('projects.show', { project: project.slug || project.id, tab: 'pitch_deck' })">
                                        {{ project.title }}
                                    </Link>
                                </h3>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ project.problem_statement || project.description || 'Belum ada deskripsi masalah.' }}
                                </p>
                            </div>

                            <!-- Proposal Status Box -->
                            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                        <AppIcon name="file-text" :size="14" class="text-emerald-600" />
                                        <span>Proposal Hibah / Riset</span>
                                    </div>
                                    <span
                                        v-if="project.proposal"
                                        class="px-2 py-0.5 text-2xs font-semibold rounded-md border"
                                        :class="getProposalStatusBadge(project.proposal.status).bg"
                                    >
                                        {{ getProposalStatusBadge(project.proposal.status).label }}
                                    </span>
                                    <span v-else class="text-2xs font-semibold text-slate-400">
                                        Belum disusun
                                    </span>
                                </div>
                                <div v-if="project.proposal" class="text-2xs text-slate-500 space-y-0.5">
                                    <div v-if="project.proposal.target_funder" class="truncate">
                                        Target: <strong class="text-slate-700">{{ project.proposal.target_funder }}</strong>
                                    </div>
                                    <div v-if="project.proposal.submission_deadline">
                                        Tenggat: <span class="text-slate-700">{{ project.proposal.submission_deadline }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-2xs text-slate-400 italic">
                                    Sinkronkan dari data proyek untuk membuat draf proposal instan.
                                </div>
                            </div>

                            <!-- Pitch Deck Status Box -->
                            <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                        <AppIcon name="presentation" :size="14" class="text-blue-600" />
                                        <span>10-Slide Pitch Deck</span>
                                    </div>
                                    <span
                                        v-if="project.pitchDeck"
                                        class="px-2 py-0.5 text-2xs font-semibold rounded-md border"
                                        :class="getPitchDeckStatusBadge(project.pitchDeck.status).bg"
                                    >
                                        {{ getPitchDeckStatusBadge(project.pitchDeck.status).label }}
                                    </span>
                                    <span v-else class="text-2xs font-semibold text-slate-400">
                                        Belum ada deck
                                    </span>
                                </div>
                                <div v-if="project.pitchDeck" class="text-2xs text-slate-500 space-y-0.5">
                                    <div v-if="project.pitchDeck.audience" class="truncate">
                                        Target Audiens: <strong class="text-slate-700">{{ project.pitchDeck.audience }}</strong>
                                    </div>
                                    <div>
                                        Slide: <span class="font-semibold text-slate-700">{{ project.pitchDeck.slides ? project.pitchDeck.slides.length : 10 }} Slide Presentation</span>
                                    </div>
                                </div>
                                <div v-else class="text-2xs text-slate-400 italic">
                                    Buat slide deck investor & dewan juri format 16:9.
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Actions -->
                        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                            <button
                                type="button"
                                @click="quickSyncProject(project)"
                                class="inline-flex items-center text-2xs font-bold text-slate-600 hover:text-emerald-700 transition-colors py-1 px-2 rounded-lg hover:bg-slate-200/70"
                                title="Sinkronkan otomatis dari judul, rumusan masalah, roadmap, dan RAB proyek"
                            >
                                <AppIcon name="sparkles" :size="13" class="mr-1 text-emerald-600" />
                                <span>Sinkronkan Data</span>
                            </button>

                            <Link
                                :href="route('projects.show', { project: project.slug || project.id, tab: 'pitch_deck' })"
                                class="inline-flex items-center px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-xs"
                            >
                                <span>Buka Dokumen</span>
                                <AppIcon name="arrow-right" :size="13" class="ml-1" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Template & Standard Deck Guidance Section -->
                <div class="mt-12 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <AppIcon name="book" :size="16" />
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">
                                Standar Format Dokumen & Pitch Deck di Tera
                            </h2>
                            <p class="text-xs text-slate-500">
                                Kerangka kerja yang dioptimalkan untuk memenangkan hibah penelitian dan pendanaan investor.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <span class="text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                                <AppIcon name="presentation" :size="14" class="text-emerald-600" />
                                <span>10-Slide Standard Pitch Deck</span>
                            </span>
                            <p class="text-2xs text-slate-600 leading-relaxed">
                                Format Guy Kawasaki & Sequoia: Cover, Problem, Solution, Market, Novelty, Business Model, Traction/Roadmap, Budget Ask, Team, Call to Action.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <span class="text-xs font-bold text-blue-800 flex items-center gap-1.5">
                                <AppIcon name="file-text" :size="14" class="text-blue-600" />
                                <span>Standar Proposal Riset & Hibah</span>
                            </span>
                            <p class="text-2xs text-slate-600 leading-relaxed">
                                Ringkasan Eksekutif, Tinjauan Masalah & Novelty, Metodologi Ilmiah, Pembagian Tugas Tim, Rencana Anggaran Biaya (RAB), dan Target Luaran Jurnal/HAKI.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <span class="text-xs font-bold text-amber-800 flex items-center gap-1.5">
                                <AppIcon name="sparkles" :size="14" class="text-amber-600" />
                                <span>Auto-Sync Data Proyek</span>
                            </span>
                            <p class="text-2xs text-slate-600 leading-relaxed">
                                Cukup klik "Sinkronkan Data", sistem otomatis menarik latar belakang, milestone, RAB terhitung, dan profil tim menjadi dokumen terformat rapi.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

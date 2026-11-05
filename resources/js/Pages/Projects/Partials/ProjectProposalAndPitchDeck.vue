<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppIcon from '@/Components/AppIcon.vue';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    budgetSummary: {
        type: Object,
        default: () => ({
            total_estimated: 0,
            total_actual: 0,
            remaining: 0,
        }),
    },
    isOwner: {
        type: Boolean,
        default: false,
    },
});

const projectKey = computed(() => props.project.slug || props.project.id);
const publicShareUrl = computed(() => {
    return `${window.location.origin}/share/${props.project.share_token}`;
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

// ==========================================
// STATE & SUB-TABS
// ==========================================
const activeDocSubTab = ref('deck'); // 'deck' or 'proposal'
const currentSlideIndex = ref(0);
const isPresentingFullscreen = ref(false);
const isPreviewProposalModalOpen = ref(false);
const isEditSlideModalOpen = ref(false);

// ==========================================
// PROPOSAL FORM
// ==========================================
const currentProposal = computed(() => props.project.proposal);

const proposalForm = useForm({
    title: currentProposal.value?.title || `Proposal: ${props.project.title}`,
    target_funder: currentProposal.value?.target_funder || '',
    submission_deadline: currentProposal.value?.submission_deadline ? currentProposal.value.submission_deadline.split('T')[0] : '',
    status: currentProposal.value?.status || 'draft',
    executive_summary: currentProposal.value?.executive_summary || props.project.description || '',
    problem_statement: currentProposal.value?.problem_statement || props.project.problem_statement || '',
    solution_statement: currentProposal.value?.solution_statement || props.project.target_outcome || '',
    novelty_and_innovation: currentProposal.value?.novelty_and_innovation || '',
    methodology: currentProposal.value?.methodology || '',
    market_and_impact: currentProposal.value?.market_and_impact || '',
    budget_summary: currentProposal.value?.budget_summary || '',
    document_url: currentProposal.value?.document_url || '',
    file: null,
});

const handleProposalFileUpload = (e) => {
    proposalForm.file = e.target.files[0] || null;
};

const submitProposal = () => {
    proposalForm.post(route('projects.proposal.update', projectKey.value), {
        preserveScroll: true,
    });
};

// ==========================================
// PITCH DECK FORM & SLIDES
// ==========================================
const rawPitchDeck = computed(() => props.project.pitch_deck || props.project.pitchDeck);

const defaultInitialSlides = [
    {
        id: 1,
        number: 1,
        type: 'cover',
        title: props.project.title,
        subtitle: props.project.label ? `Inisiatif ${props.project.label}` : 'Solusi Inovasi Riset & Pengembangan',
        bullets: [
            props.project.description || 'Platform inovasi masa depan berbasis data dan riset terapan.',
            'Dipersiapkan oleh Tim Pengembang Tera R&D',
        ],
        notes: 'Buka dengan hook yang kuat, sampaikan visi utama dalam 15 detik pertama.',
        metric: `${props.project.progress_percent}% Progres`,
    },
    {
        id: 2,
        number: 2,
        type: 'problem',
        title: 'The Problem (Masalah Nyata)',
        subtitle: 'Tantangan kritis yang dihadapi lapangan saat ini',
        bullets: [
            props.project.problem_statement || 'Kesenjangan antara kondisi lapangan dengan kebutuhan solusi ideal.',
            'Tingginya inefisiensi dan minimnya alat bantu yang terintegrasi.',
        ],
        notes: 'Fokus pada pain points pengguna atau masyarakat terdampak.',
        metric: 'Urgent',
    },
    {
        id: 3,
        number: 3,
        type: 'solution',
        title: 'The Solution & Product Value',
        subtitle: 'Pendekatan solusi kami yang terukur dan inovatif',
        bullets: [
            props.project.target_outcome || 'Membangun solusi tepat guna dengan metodologi teruji.',
            'Kemudahan adopsi dan efisiensi biaya pelaksanaan.',
        ],
        notes: 'Perlihatkan demo visual atau hasil prototype yang menarik.',
        metric: '100% Solutif',
    },
    {
        id: 4,
        number: 4,
        type: 'market',
        title: 'Target Pengguna & Analisis Pasar',
        subtitle: 'Siapa yang terdampak dan potensi skalabilitas',
        bullets: [
            'Early adopters dan komunitas sasaran utama.',
            'Skalabilitas implementasi di berbagai sektor atau daerah.',
        ],
        notes: 'Tunjukkan TAM/SAM/SOM atau profil demografi pengguna.',
        metric: 'High Impact',
    },
    {
        id: 5,
        number: 5,
        type: 'novelty',
        title: 'Kebaruan & Keunggulan Kompetitif',
        subtitle: 'Unfair advantage dan pembeda utama dari solusi lain',
        bullets: [
            'Arsitektur inovatif dan metodologi yang dapat dilacak secara ilmiah.',
            'Biaya lebih terjangkau dengan keandalan lebih tinggi.',
        ],
        notes: 'Tegaskan kebaruan teknologi atau keunggulan komparatif.',
        metric: 'Novelty High',
    },
    {
        id: 6,
        number: 6,
        type: 'business_model',
        title: 'Model Keberlanjutan & Implementasi',
        subtitle: 'Strategi operasional dan kemandirian program',
        bullets: [
            'Kemitraan strategis, transfer teknologi, dan dukungan komunitas.',
            'Rencana tindak lanjut paska pendanaan awal.',
        ],
        notes: 'Yakinkan juri/investor bahwa inisiatif ini berkelanjutan.',
        metric: 'Sustainable',
    },
    {
        id: 7,
        number: 7,
        type: 'traction',
        title: 'Traksi & Roadmap Pencapaian',
        subtitle: 'Pencapaian milestone nyata hingga saat ini',
        bullets: [
            `Progres eksekusi proyek: ${props.project.progress_percent}%.`,
            `Total tugas terencana: ${props.project.tasks?.length || 0} aktivitas.`,
        ],
        notes: 'Paparkan bukti uji coba atau validasi awal.',
        metric: `${props.project.progress_percent}%`,
    },
    {
        id: 8,
        number: 8,
        type: 'financial',
        title: 'Kebutuhan Dana & Rencana Anggaran (Ask)',
        subtitle: 'Alokasi penggunaan sumber daya untuk mencapai tujuan',
        bullets: [
            `Total Rencana Anggaran (RAB): ${formatRupiah(props.budgetSummary?.total_estimated || 0)}`,
            'Alokasi: Alat/Bahan, Uji Coba Lapangan, dan Operasional Riset.',
        ],
        notes: 'Sampaikan secara jelas nilai ask dan hasil yang dijamin terwujud.',
        metric: formatRupiah(props.budgetSummary?.total_estimated || 0),
    },
    {
        id: 9,
        number: 9,
        type: 'team',
        title: 'Tim Peneliti & Pengembang',
        subtitle: 'Kolaborasi keahlian dalam mengeksekusi proyek',
        bullets: [
            `Lead: ${props.project.user?.name || 'Inisiator Proyek'}`,
            `Kolaborator: ${(props.project.members?.length || 0) + 1} Orang Anggota Tim`,
        ],
        notes: 'Highlight rekam jejak dan keahlian spesifik anggota tim.',
        metric: `${(props.project.members?.length || 0) + 1} Tim`,
    },
    {
        id: 10,
        number: 10,
        type: 'closing',
        title: 'Visi Masa Depan & Call to Action',
        subtitle: 'Mari berkolaborasi mewujudkan transformasi nyata',
        bullets: [
            'Kami siap untuk demonstrasi langsung dan kemitraan strategis.',
            `Tautan Logbook & Workspace Publik: ${publicShareUrl.value}`,
        ],
        notes: 'Akhiri dengan ajakan bertindak yang mantap dan ucapan terima kasih.',
        metric: 'Siap Pitch',
    },
];

const deckSlides = ref(
    rawPitchDeck.value?.slides && rawPitchDeck.value.slides.length > 0
        ? JSON.parse(JSON.stringify(rawPitchDeck.value.slides))
        : defaultInitialSlides
);

const pitchDeckForm = useForm({
    title: rawPitchDeck.value?.title || `Pitch Deck: ${props.project.title}`,
    tagline: rawPitchDeck.value?.tagline || (props.project.label ? `Inisiatif ${props.project.label}` : 'Solusi Inovasi R&D'),
    audience: rawPitchDeck.value?.audience || 'Dewan Juri & Investor',
    status: rawPitchDeck.value?.status || 'draft',
    theme: rawPitchDeck.value?.theme || 'emerald',
    presentation_url: rawPitchDeck.value?.presentation_url || '',
    slides: deckSlides.value,
    file: null,
});

const handleDeckFileUpload = (e) => {
    pitchDeckForm.file = e.target.files[0] || null;
};

const currentSlide = computed(() => {
    if (!deckSlides.value || deckSlides.value.length === 0) return null;
    return deckSlides.value[currentSlideIndex.value] || deckSlides.value[0];
});

const nextSlide = () => {
    if (currentSlideIndex.value < deckSlides.value.length - 1) {
        currentSlideIndex.value++;
    }
};

const prevSlide = () => {
    if (currentSlideIndex.value > 0) {
        currentSlideIndex.value--;
    }
};

const jumpToSlide = (idx) => {
    currentSlideIndex.value = idx;
};

// Slide Editing
const editingSlide = ref({
    title: '',
    subtitle: '',
    bullets: [],
    bulletsText: '',
    notes: '',
    metric: '',
});

const openEditSlideModal = () => {
    if (!currentSlide.value) return;
    editingSlide.value = {
        title: currentSlide.value.title || '',
        subtitle: currentSlide.value.subtitle || '',
        bullets: currentSlide.value.bullets || [],
        bulletsText: (currentSlide.value.bullets || []).join('\n'),
        notes: currentSlide.value.notes || '',
        metric: currentSlide.value.metric || '',
    };
    isEditSlideModalOpen.value = true;
};

const saveSlideChanges = () => {
    if (!currentSlide.value) return;
    const bulletsArray = editingSlide.value.bulletsText
        .split('\n')
        .map(b => b.trim())
        .filter(b => b.length > 0);

    currentSlide.value.title = editingSlide.value.title;
    currentSlide.value.subtitle = editingSlide.value.subtitle;
    currentSlide.value.bullets = bulletsArray;
    currentSlide.value.notes = editingSlide.value.notes;
    currentSlide.value.metric = editingSlide.value.metric;

    pitchDeckForm.slides = deckSlides.value;
    isEditSlideModalOpen.value = false;
};

const addNewSlide = () => {
    const newId = deckSlides.value.length + 1;
    deckSlides.value.push({
        id: Date.now(),
        number: newId,
        type: 'custom',
        title: `Slide Baru #${newId}`,
        subtitle: 'Tambahkan subjudul slide di sini',
        bullets: ['Poin kunci pertama', 'Poin kunci kedua'],
        notes: 'Catatan pembicara untuk slide ini.',
        metric: 'Point',
    });
    pitchDeckForm.slides = deckSlides.value;
    currentSlideIndex.value = deckSlides.value.length - 1;
};

const removeCurrentSlide = () => {
    if (deckSlides.value.length <= 1) {
        alert('Minimal harus ada 1 slide dalam deck.');
        return;
    }
    if (confirm('Hapus slide ini dari pitch deck?')) {
        deckSlides.value.splice(currentSlideIndex.value, 1);
        if (currentSlideIndex.value >= deckSlides.value.length) {
            currentSlideIndex.value = deckSlides.value.length - 1;
        }
        pitchDeckForm.slides = deckSlides.value;
    }
};

const submitPitchDeck = () => {
    pitchDeckForm.slides = deckSlides.value;
    pitchDeckForm.post(route('projects.pitch_deck.update', projectKey.value), {
        preserveScroll: true,
    });
};

const syncFromProjectAction = () => {
    if (confirm('Sinkronkan proposal & 10 slide pitch deck dari rumusan masalah, roadmap, dan RAB proyek?')) {
        router.post(route('projects.documents.sync', projectKey.value), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            },
        });
    }
};

const printProposal = () => {
    window.print();
};
</script>

<template>
    <div class="space-y-6">
        <!-- Top Hub Navigation & Quick Sync Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <AppIcon name="presentation" :size="20" />
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Dokumen Proposal & Pitch Deck</h2>
                    <p class="text-xs text-slate-500">Susun presentasi investor/juri dan naskah proposal ilmiah terstruktur.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Sub-tab Switcher -->
                <div class="bg-slate-100 p-1 rounded-xl flex items-center gap-1 border border-slate-200/80">
                    <button
                        type="button"
                        @click="activeDocSubTab = 'deck'"
                        :class="activeDocSubTab === 'deck' ? 'bg-white text-emerald-800 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-lg text-xs transition-all inline-flex items-center gap-1.5"
                    >
                        <AppIcon name="presentation" :size="13" />
                        <span>Pitch Deck (16:9)</span>
                    </button>
                    <button
                        type="button"
                        @click="activeDocSubTab = 'proposal'"
                        :class="activeDocSubTab === 'proposal' ? 'bg-white text-emerald-800 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-lg text-xs transition-all inline-flex items-center gap-1.5"
                    >
                        <AppIcon name="file-text" :size="13" />
                        <span>Naskah Proposal</span>
                    </button>
                </div>

                <!-- 1-Click Sync Button -->
                <button
                    v-if="isOwner"
                    type="button"
                    @click="syncFromProjectAction"
                    class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100/80 transition-all shadow-2xs active:scale-95"
                    title="Otomatis isi draf proposal & 10 slide pitch deck dari rumusan masalah, roadmap, dan RAB proyek"
                >
                    <AppIcon name="sparkles" :size="13" class="mr-1.5 text-emerald-600" />
                    <span>Sinkronkan Data Proyek</span>
                </button>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SUB-TAB A: 10-SLIDE PITCH DECK PRESENTATION                     -->
        <!-- ============================================================== -->
        <div v-if="activeDocSubTab === 'deck'" class="space-y-6">
            <!-- Pitch Deck Controls Bar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div>
                        <span class="text-2xs font-semibold text-slate-400 block uppercase tracking-wider">Judul Presentasi</span>
                        <span class="text-sm font-bold text-slate-900">{{ pitchDeckForm.title }}</span>
                    </div>
                    <span v-if="project.label" class="inline-flex items-center px-2 py-0.5 rounded-md text-2xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        🏷️ {{ project.label }}
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Theme Selector -->
                    <select
                        v-model="pitchDeckForm.theme"
                        class="text-xs px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 focus:ring-emerald-500"
                    >
                        <option value="emerald">Tema: Emerald Modern</option>
                        <option value="slate">Tema: Dark Minimal</option>
                        <option value="ocean">Tema: Ocean Blue</option>
                        <option value="amber">Tema: Executive Amber</option>
                    </select>

                    <!-- Fullscreen Presentation Button -->
                    <button
                        type="button"
                        @click="isPresentingFullscreen = true"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 transition-colors shadow-xs"
                    >
                        <AppIcon name="play" :size="13" class="mr-1.5 text-emerald-400" />
                        <span>Mulai Presentasi</span>
                    </button>

                    <button
                        v-if="isOwner"
                        type="button"
                        @click="submitPitchDeck"
                        :disabled="pitchDeckForm.processing"
                        class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-xs"
                    >
                        <AppIcon name="check" :size="13" class="mr-1.5" />
                        <span>Simpan Pitch Deck</span>
                    </button>
                </div>
            </div>

            <!-- 16:9 Interactive Slide Presentation Viewer -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-7 shadow-xs space-y-4">
                <!-- 16:9 Aspect Ratio Slide Canvas -->
                <div
                    class="w-full rounded-2xl overflow-hidden shadow-xl border relative flex flex-col justify-between p-6 sm:p-10 transition-all select-none"
                    :class="{
                        'bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-white border-emerald-800/40': pitchDeckForm.theme === 'emerald',
                        'bg-gradient-to-br from-slate-950 via-slate-900 to-slate-900 text-white border-slate-800': pitchDeckForm.theme === 'slate',
                        'bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 text-white border-blue-900/40': pitchDeckForm.theme === 'ocean',
                        'bg-gradient-to-br from-slate-950 via-amber-950 to-slate-900 text-white border-amber-900/40': pitchDeckForm.theme === 'amber',
                    }"
                    style="min-height: 380px;"
                >
                    <!-- Slide Top Header -->
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-extrabold tracking-wider uppercase bg-white/10 backdrop-blur-md text-emerald-300 border border-white/10">
                                Slide {{ currentSlideIndex + 1 }} dari {{ deckSlides.length }}
                            </span>
                            <span v-if="project.label" class="px-2 py-0.5 rounded-full text-2xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                🏷️ {{ project.label }}
                            </span>
                        </div>
                        <div v-if="currentSlide?.metric" class="px-3 py-1 rounded-xl text-xs font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 shadow-inner">
                            {{ currentSlide.metric }}
                        </div>
                    </div>

                    <!-- Slide Main Body Content -->
                    <div class="my-6 space-y-4 max-w-3xl">
                        <div>
                            <h3 class="text-xl sm:text-3xl font-black tracking-tight text-white leading-tight">
                                {{ currentSlide?.title }}
                            </h3>
                            <p v-if="currentSlide?.subtitle" class="text-xs sm:text-base font-medium text-emerald-400 mt-1">
                                {{ currentSlide.subtitle }}
                            </p>
                        </div>

                        <!-- Bullets list -->
                        <div class="space-y-2.5 pt-2">
                            <div
                                v-for="(bullet, bIdx) in (currentSlide?.bullets || [])"
                                :key="bIdx"
                                class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-200 leading-relaxed"
                            >
                                <div class="w-2 h-2 rounded-full bg-emerald-400 mt-1.5 shrink-0"></div>
                                <span>{{ bullet }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Slide Footer & Speaker Notes Preview -->
                    <div class="pt-4 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-2xs text-slate-400">
                        <div class="flex items-center gap-1.5">
                            <span class="text-amber-400">💡</span>
                            <span class="italic font-medium">{{ currentSlide?.notes || 'Tidak ada catatan pembicara untuk slide ini.' }}</span>
                        </div>
                        <div class="font-bold tracking-wider text-slate-500 uppercase">
                            TERA Research & Innovation OS
                        </div>
                    </div>
                </div>

                <!-- Slide Player Controls & Thumbnails -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                    <!-- Navigation Buttons -->
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="prevSlide"
                            :disabled="currentSlideIndex === 0"
                            class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center gap-1"
                        >
                            <AppIcon name="arrow-left" :size="13" />
                            <span>Sebelumnya</span>
                        </button>

                        <button
                            type="button"
                            @click="nextSlide"
                            :disabled="currentSlideIndex === deckSlides.length - 1"
                            class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center gap-1"
                        >
                            <span>Berikutnya</span>
                            <AppIcon name="arrow-right" :size="13" />
                        </button>
                    </div>

                    <!-- Slide Number Chips -->
                    <div class="flex items-center gap-1 overflow-x-auto max-w-full pb-1 no-scrollbar">
                        <button
                            v-for="(slide, sIdx) in deckSlides"
                            :key="slide.id"
                            type="button"
                            @click="jumpToSlide(sIdx)"
                            class="w-7 h-7 rounded-lg text-xs font-bold transition-all shrink-0 flex items-center justify-center"
                            :class="currentSlideIndex === sIdx ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            :title="slide.title"
                        >
                            {{ sIdx + 1 }}
                        </button>
                    </div>

                    <!-- Slide CRUD Actions -->
                    <div v-if="isOwner" class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="openEditSlideModal"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 inline-flex items-center gap-1 shadow-2xs"
                        >
                            <AppIcon name="edit" :size="13" class="text-slate-500" />
                            <span>Edit Slide</span>
                        </button>
                        <button
                            type="button"
                            @click="addNewSlide"
                            class="px-3 py-1.5 rounded-xl border border-emerald-200 bg-emerald-50 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 inline-flex items-center gap-1 shadow-2xs"
                        >
                            <AppIcon name="plus" :size="13" class="text-emerald-600" />
                            <span>Tambah Slide</span>
                        </button>
                        <button
                            type="button"
                            @click="removeCurrentSlide"
                            class="p-1.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 shadow-2xs"
                            title="Hapus slide ini"
                        >
                            <AppIcon name="trash" :size="13" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pitch Deck Links & Presentation File Upload -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <AppIcon name="paperclip" :size="15" class="text-emerald-600" />
                    <span>Tautan Slide Eksternal & Berkas Presentasi</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tautan Slide (Canva, Google Slides, Pitch.com)</label>
                        <input
                            v-model="pitchDeckForm.presentation_url"
                            type="url"
                            placeholder="https://docs.google.com/presentation/... atau https://canva.com/..."
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                        />
                        <a
                            v-if="pitchDeckForm.presentation_url"
                            :href="pitchDeckForm.presentation_url"
                            target="_blank"
                            class="mt-1.5 inline-flex items-center text-2xs text-emerald-700 font-bold hover:underline"
                        >
                            <AppIcon name="external-link" :size="12" class="mr-1" />
                            <span>Buka Slide Eksternal di Tab Baru</span>
                        </a>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Upload Berkas PPTX / PDF</label>
                        <input
                            type="file"
                            accept=".pdf,.ppt,.pptx,.odp"
                            @change="handleDeckFileUpload"
                            class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                        />
                        <span v-if="rawPitchDeck?.file_path" class="text-2xs text-slate-500 block mt-1">
                            Berkas tersimpan: <strong class="text-slate-700">{{ rawPitchDeck.file_path.split('/').pop() }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SUB-TAB B: PROPOSAL NASKAH HIBAH / RISET                       -->
        <!-- ============================================================== -->
        <div v-if="activeDocSubTab === 'proposal'" class="space-y-6">
            <!-- Proposal Header Bar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Naskah Dokumen Proposal</h3>
                    <p class="text-xs text-slate-500">Standar proposal hibah riset, PKM, dan pengembangan produk terstruktur.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="isPreviewProposalModalOpen = true"
                        class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300 transition-colors shadow-2xs"
                    >
                        <AppIcon name="eye" :size="13" class="mr-1.5 text-slate-600" />
                        <span>Pratinjau Dokumen Siap Cetak</span>
                    </button>

                    <button
                        v-if="isOwner"
                        type="button"
                        @click="submitProposal"
                        :disabled="proposalForm.processing"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-xs"
                    >
                        <AppIcon name="check" :size="13" class="mr-1.5" />
                        <span>Simpan Proposal</span>
                    </button>
                </div>
            </div>

            <!-- Proposal Form Content -->
            <form @submit.prevent="submitProposal" class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6 text-xs">
                <!-- Metadata Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Target Pendanaan / Hibah / Investor</label>
                        <input
                            v-model="proposalForm.target_funder"
                            type="text"
                            placeholder="Misal: Hibah Riset Kemdikbud, LPDP, Pre-Seed VC..."
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tenggat Pengajuan (Deadline)</label>
                        <input
                            v-model="proposalForm.submission_deadline"
                            type="date"
                            class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status Dokumen</label>
                        <select
                            v-model="proposalForm.status"
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="draft">Draf (Penyusunan)</option>
                            <option value="in_review">Sedang Direview</option>
                            <option value="final">Final (Siap Kirim)</option>
                            <option value="submitted">Sudah Diajukan (Submitted)</option>
                        </select>
                    </div>
                </div>

                <!-- Proposal Structured Sections -->
                <div class="space-y-4">
                    <div>
                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1">Judul Dokumen Proposal</label>
                        <input
                            v-model="proposalForm.title"
                            type="text"
                            required
                            class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 font-semibold focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">1. Ringkasan Eksekutif (Executive Summary / Abstrak)</label>
                        <textarea
                            v-model="proposalForm.executive_summary"
                            rows="3"
                            placeholder="Uraikan intisari proyek, urgensi, metodologi ringkas, dan target luaran dalam 150-250 kata."
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">2. Latar Belakang & Rumusan Masalah (Problem Statement)</label>
                        <textarea
                            v-model="proposalForm.problem_statement"
                            rows="3"
                            placeholder="Fenomena lapangan, fakta empiris, research gap, dan alasan mengapa masalah ini mendesak untuk diselesaikan."
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">3. Solusi & Value Proposition</label>
                            <textarea
                                v-model="proposalForm.solution_statement"
                                rows="3"
                                placeholder="Deskripsi solusi produk / intervensi sosial yang dikembangkan dan manfaat langsung bagi target sasaran."
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">4. Kebaruan & Keunggulan Kompetitif (Novelty)</label>
                            <textarea
                                v-model="proposalForm.novelty_and_innovation"
                                rows="3"
                                placeholder="Perbandingan dengan riset/produk sejenis sebelumnya dan kontribusi orisinal tim Anda."
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">5. Metodologi & Alur Kerja (Workflow / Roadmap)</label>
                        <textarea
                            v-model="proposalForm.methodology"
                            rows="4"
                            placeholder="Tahapan pelaksanaan, metode ilmiah, instrumen, alur eksperimen, dan timeline pencapaian milestone."
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-2xs focus:ring-2 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">6. Target Luaran & Dampak Terukur</label>
                            <textarea
                                v-model="proposalForm.market_and_impact"
                                rows="3"
                                placeholder="Luaran wajib (prototipe, jurnal terindeks, paten HKI, laporan) dan dampak sosial/ekonomi."
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">7. Rencana Anggaran Biaya (RAB Summary)</label>
                            <textarea
                                v-model="proposalForm.budget_summary"
                                rows="3"
                                placeholder="Rekapitulasi total anggaran belanja alat bahan, operasional, dan justifikasi penggunaan dana."
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>
                    </div>

                    <!-- External Links & Proposal File Upload -->
                    <div class="pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tautan Naskah Online (Google Docs / Overleaf)</label>
                            <input
                                v-model="proposalForm.document_url"
                                type="url"
                                placeholder="https://docs.google.com/document/..."
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Upload Berkas PDF / DOCX Proposal</label>
                            <input
                                type="file"
                                accept=".pdf,.doc,.docx"
                                @change="handleProposalFileUpload"
                                class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                            />
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button
                        type="submit"
                        :disabled="proposalForm.processing"
                        class="px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-xs"
                    >
                        Simpan Perubahan Dokumen Proposal
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL: EDIT SLIDE PITCH DECK                                    -->
        <!-- ============================================================== -->
        <div v-if="isEditSlideModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto text-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        Edit Slide #{{ currentSlideIndex + 1 }}
                    </h3>
                    <button @click="isEditSlideModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="16" />
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Slide</label>
                        <input
                            v-model="editingSlide.title"
                            type="text"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500 text-xs font-semibold"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Subjudul / Tagline Slide</label>
                        <input
                            v-model="editingSlide.subtitle"
                            type="text"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500 text-xs"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Chip Metrik Kunci (Kanan Atas Slide)</label>
                        <input
                            v-model="editingSlide.metric"
                            type="text"
                            placeholder="Misal: 100% Solutif, Rp 2.500.000, 500 Petani..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500 text-xs"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Poin-Poin Utama (1 Poin Per Baris)</label>
                        <textarea
                            v-model="editingSlide.bulletsText"
                            rows="4"
                            placeholder="Tulis setiap poin dalam baris baru..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500 text-xs"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Pembicara (Speaker Notes)</label>
                        <textarea
                            v-model="editingSlide.notes"
                            rows="2"
                            placeholder="Panduan apa yang harus dikatakan presenter di slide ini..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-emerald-500 text-xs"
                        ></textarea>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="isEditSlideModalOpen = false"
                        class="px-4 py-2 text-slate-600 rounded-xl hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="saveSlideChanges"
                        class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs"
                    >
                        Terapkan ke Slide
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL: FULLSCREEN PRESENTATION MODE                             -->
        <!-- ============================================================== -->
        <div
            v-if="isPresentingFullscreen"
            class="fixed inset-0 z-50 bg-slate-950 flex flex-col justify-between p-4 sm:p-10 select-none"
            tabindex="0"
            @keydown.left="prevSlide"
            @keydown.right="nextSlide"
            @keydown.esc="isPresentingFullscreen = false"
        >
            <!-- Fullscreen Header -->
            <div class="flex items-center justify-between text-white/70">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-emerald-300">
                        Slide {{ currentSlideIndex + 1 }} / {{ deckSlides.length }}
                    </span>
                    <span class="text-sm font-semibold text-white truncate max-w-md">{{ project.title }}</span>
                </div>
                <button
                    @click="isPresentingFullscreen = false"
                    class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors"
                    title="Keluar Fullscreen (ESC)"
                >
                    <AppIcon name="close" :size="20" />
                </button>
            </div>

            <!-- Fullscreen Slide Body -->
            <div class="my-auto max-w-5xl mx-auto w-full px-4 text-center sm:text-left space-y-6">
                <div>
                    <span v-if="currentSlide?.metric" class="inline-block px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 mb-3">
                        {{ currentSlide.metric }}
                    </span>
                    <h2 class="text-3xl sm:text-6xl font-black text-white tracking-tight leading-tight">
                        {{ currentSlide?.title }}
                    </h2>
                    <p v-if="currentSlide?.subtitle" class="text-lg sm:text-2xl font-semibold text-emerald-400 mt-2">
                        {{ currentSlide.subtitle }}
                    </p>
                </div>

                <div class="space-y-4 pt-4 max-w-3xl">
                    <div
                        v-for="(bullet, bIdx) in (currentSlide?.bullets || [])"
                        :key="bIdx"
                        class="flex items-start gap-4 text-base sm:text-xl text-slate-200"
                    >
                        <div class="w-3 h-3 rounded-full bg-emerald-400 mt-2 shrink-0"></div>
                        <span>{{ bullet }}</span>
                    </div>
                </div>
            </div>

            <!-- Fullscreen Navigation Controls -->
            <div class="flex items-center justify-between text-white/70 pt-4 border-t border-white/10">
                <button
                    type="button"
                    @click="prevSlide"
                    :disabled="currentSlideIndex === 0"
                    class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 text-xs font-bold text-white transition-all inline-flex items-center gap-2"
                >
                    <AppIcon name="arrow-left" :size="14" />
                    <span>Sebelumnya (←)</span>
                </button>

                <div class="text-xs text-slate-400">
                    Gunakan tombol panah <strong>← / →</strong> atau klik untuk berpindah slide
                </div>

                <button
                    type="button"
                    @click="nextSlide"
                    :disabled="currentSlideIndex === deckSlides.length - 1"
                    class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 text-xs font-bold text-white transition-all inline-flex items-center gap-2"
                >
                    <span>Berikutnya (→)</span>
                    <AppIcon name="arrow-right" :size="14" />
                </button>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL: PRATINJAU DOKUMEN PROPOSAL SIAP CETAK                   -->
        <!-- ============================================================== -->
        <div v-if="isPreviewProposalModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-900/70 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-4xl w-full p-6 sm:p-10 shadow-2xl max-h-[95vh] overflow-y-auto space-y-6">
                <!-- Modal Top Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <AppIcon name="file-text" :size="20" class="text-emerald-700" />
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Pratinjau Dokumen Naskah Proposal</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="printProposal"
                            class="px-3.5 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-colors inline-flex items-center gap-1.5 shadow-xs"
                        >
                            <AppIcon name="printer" :size="14" />
                            <span>Cetak / Simpan PDF</span>
                        </button>
                        <button
                            @click="isPreviewProposalModalOpen = false"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
                        >
                            <AppIcon name="close" :size="18" />
                        </button>
                    </div>
                </div>

                <!-- Printable Document Sheet -->
                <div class="bg-white p-6 sm:p-10 border border-slate-200 rounded-2xl shadow-inner space-y-6 text-slate-900 font-serif leading-relaxed">
                    <!-- Title & Header -->
                    <div class="text-center pb-6 border-b-2 border-slate-900 space-y-2">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-500 font-sans block">
                            PROPOSAL PENELITIAN & PENGEMBANGAN INOVASI
                        </span>
                        <h1 class="text-xl sm:text-2xl font-black font-sans uppercase text-slate-900">
                            {{ proposalForm.title }}
                        </h1>
                        <div v-if="project.label" class="text-sm font-bold font-sans text-emerald-800">
                            Inisiatif: {{ project.label }}
                        </div>
                        <div class="text-xs font-sans text-slate-500 pt-1">
                            Disusun oleh Tim: <strong>{{ project.user?.name }}</strong> & Rekan Kolaborator
                        </div>
                    </div>

                    <!-- Ringkasan Eksekutif -->
                    <div class="space-y-1.5">
                        <h4 class="font-sans font-bold text-sm text-slate-900 uppercase tracking-wider">I. Ringkasan Eksekutif</h4>
                        <p class="text-xs sm:text-sm text-justify text-slate-700 whitespace-pre-line">{{ proposalForm.executive_summary }}</p>
                    </div>

                    <!-- Rumusan Masalah -->
                    <div class="space-y-1.5">
                        <h4 class="font-sans font-bold text-sm text-slate-900 uppercase tracking-wider">II. Latar Belakang & Rumusan Masalah</h4>
                        <p class="text-xs sm:text-sm text-justify text-slate-700 whitespace-pre-line">{{ proposalForm.problem_statement }}</p>
                    </div>

                    <!-- Solusi & Value Proposition -->
                    <div class="space-y-1.5">
                        <h4 class="font-sans font-bold text-sm text-slate-900 uppercase tracking-wider">III. Solusi & Value Proposition</h4>
                        <p class="text-xs sm:text-sm text-justify text-slate-700 whitespace-pre-line">{{ proposalForm.solution_statement }}</p>
                    </div>

                    <!-- Kebaruan -->
                    <div class="space-y-1.5">
                        <h4 class="font-sans font-bold text-sm text-slate-900 uppercase tracking-wider">IV. Kebaruan & Keunggulan Kompetitif (Novelty)</h4>
                        <p class="text-xs sm:text-sm text-justify text-slate-700 whitespace-pre-line">{{ proposalForm.novelty_and_innovation }}</p>
                    </div>

                    <!-- Metodologi -->
                    <div class="space-y-1.5">
                        <h4 class="font-sans font-bold text-sm text-slate-900 uppercase tracking-wider">V. Metodologi & Alur Kerja (Roadmap)</h4>
                        <p class="text-xs sm:text-sm text-justify text-slate-700 whitespace-pre-line font-sans">{{ proposalForm.methodology }}</p>
                    </div>

                    <!-- Target Luaran & Dampak -->
                    <div class="space-y-1.5">
                        <h4 class="font-sans font-bold text-sm text-slate-900 uppercase tracking-wider">VI. Target Luaran & Dampak</h4>
                        <p class="text-xs sm:text-sm text-justify text-slate-700 whitespace-pre-line">{{ proposalForm.market_and_impact }}</p>
                    </div>

                    <!-- RAB Summary -->
                    <div class="space-y-1.5">
                        <h4 class="font-sans font-bold text-sm text-slate-900 uppercase tracking-wider">VII. Rekapitulasi Rencana Anggaran Biaya (RAB)</h4>
                        <p class="text-xs sm:text-sm text-justify text-slate-700 whitespace-pre-line font-sans">{{ proposalForm.budget_summary }}</p>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        @click="isPreviewProposalModalOpen = false"
                        class="px-5 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl text-xs hover:bg-slate-300"
                    >
                        Tutup Pratinjau
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>


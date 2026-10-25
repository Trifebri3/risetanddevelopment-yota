<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    project: Object,
    budgetSummary: Object,
    isOwner: Boolean,
    teamInviteUrl: String,
});

const activeTab = ref('overview'); // 'overview', 'roadmap', 'journal', 'budget', 'outputs', 'team', 'share'

// Modals State (Create & Edit)
const isProjectModalOpen = ref(false);
const isPhaseModalOpen = ref(false);
const isEditPhaseModalOpen = ref(false);
const isMilestoneModalOpen = ref(false);
const isEditMilestoneModalOpen = ref(false);
const isTaskModalOpen = ref(false);
const isEditTaskModalOpen = ref(false);
const isJournalModalOpen = ref(false);
const isEditJournalModalOpen = ref(false);
const isBudgetItemModalOpen = ref(false);
const isEditBudgetItemModalOpen = ref(false);
const isExpenseModalOpen = ref(false);
const isEditExpenseModalOpen = ref(false);
const isOutputModalOpen = ref(false);
const isEditOutputModalOpen = ref(false);
const isTeamModalOpen = ref(false);

const copiedShareLink = ref(false);
const copiedTeamInviteLink = ref(false);
const projectKey = computed(() => props.project.slug || props.project.id);

const publicShareUrl = computed(() => {
    return `${window.location.origin}/share/${props.project.share_token}`;
});

const copyShareLink = () => {
    navigator.clipboard.writeText(publicShareUrl.value);
    copiedShareLink.value = true;
    setTimeout(() => {
        copiedShareLink.value = false;
    }, 2500);
};

const copyTeamInviteLink = () => {
    if (!props.teamInviteUrl) return;
    navigator.clipboard.writeText(props.teamInviteUrl);
    copiedTeamInviteLink.value = true;
    setTimeout(() => {
        copiedTeamInviteLink.value = false;
    }, 2500);
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

// Mode Badges (Strict 2-Color Palette + Amber for Competition)
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
            return { label: 'Umum', color: 'bg-slate-50 text-slate-700 border-slate-200', icon: 'folder' };
    }
};

// ==========================================
// 1. PROJECT OVERVIEW CRUD
// ==========================================
const projectForm = useForm({
    title: props.project.title,
    mode: props.project.mode,
    status: props.project.status,
    start_date: props.project.start_date || '',
    end_date: props.project.end_date || '',
    description: props.project.description || '',
    problem_statement: props.project.problem_statement || '',
    target_outcome: props.project.target_outcome || '',
});

const submitProjectEdit = () => {
    projectForm.put(route('projects.update', projectKey.value), {
        onSuccess: () => {
            isProjectModalOpen.value = false;
        },
    });
};

const deleteProject = () => {
    if (confirm(`Yakin ingin menghapus proyek "${props.project.title}"? Semua data fase, milestone, tugas, logbook, dan anggaran akan terhapus permanen.`)) {
        router.delete(route('projects.destroy', projectKey.value));
    }
};

// ==========================================
// 2. PHASES CRUD
// ==========================================
const phaseForm = useForm({
    name: '',
});

const submitPhase = () => {
    phaseForm.post(route('phases.store', projectKey.value), {
        onSuccess: () => {
            isPhaseModalOpen.value = false;
            phaseForm.reset();
        },
    });
};

const editPhaseForm = useForm({
    id: null,
    name: '',
});

const openEditPhaseModal = (phase) => {
    editPhaseForm.id = phase.id;
    editPhaseForm.name = phase.name;
    isEditPhaseModalOpen.value = true;
};

const submitEditPhase = () => {
    editPhaseForm.put(route('phases.update', editPhaseForm.id), {
        onSuccess: () => {
            isEditPhaseModalOpen.value = false;
        },
    });
};

const deletePhase = (phase) => {
    if (confirm(`Hapus fase "${phase.name}"? Milestone dan tugas di fase ini akan terlepas.`)) {
        router.delete(route('phases.destroy', phase.id));
    }
};

// ==========================================
// 3. MILESTONES CRUD
// ==========================================
const milestoneForm = useForm({
    title: '',
    phase_id: '',
    due_date: '',
    definition_of_done: '',
    is_public: true,
});

const submitMilestone = () => {
    milestoneForm.post(route('milestones.store', projectKey.value), {
        onSuccess: () => {
            isMilestoneModalOpen.value = false;
            milestoneForm.reset();
        },
    });
};

const editMilestoneForm = useForm({
    id: null,
    title: '',
    phase_id: '',
    due_date: '',
    definition_of_done: '',
    status: 'pending',
    is_public: true,
});

const openEditMilestoneModal = (m) => {
    editMilestoneForm.id = m.id;
    editMilestoneForm.title = m.title;
    editMilestoneForm.phase_id = m.phase_id || '';
    editMilestoneForm.due_date = m.due_date || '';
    editMilestoneForm.definition_of_done = m.definition_of_done || '';
    editMilestoneForm.status = m.status || 'pending';
    editMilestoneForm.is_public = Boolean(m.is_public);
    isEditMilestoneModalOpen.value = true;
};

const submitEditMilestone = () => {
    editMilestoneForm.put(route('milestones.update', editMilestoneForm.id), {
        onSuccess: () => {
            isEditMilestoneModalOpen.value = false;
        },
    });
};

const completeMilestone = (m) => {
    router.put(route('milestones.update', m.id), {
        title: m.title,
        phase_id: m.phase_id,
        due_date: m.due_date,
        definition_of_done: m.definition_of_done,
        is_public: m.is_public,
        status: m.status === 'completed' ? 'in_progress' : 'completed',
    }, { preserveScroll: true });
};

const deleteMilestone = (m) => {
    if (confirm(`Hapus milestone "${m.title}"?`)) {
        router.delete(route('milestones.destroy', m.id), { preserveScroll: true });
    }
};

// ==========================================
// 4. TASKS CRUD
// ==========================================
const taskForm = useForm({
    title: '',
    milestone_id: '',
    description: '',
    priority: 'medium',
    due_date: '',
    weight: 1,
});

const submitTask = () => {
    taskForm.post(route('tasks.store', projectKey.value), {
        onSuccess: () => {
            isTaskModalOpen.value = false;
            taskForm.reset();
        },
    });
};

const editTaskForm = useForm({
    id: null,
    title: '',
    milestone_id: '',
    description: '',
    priority: 'medium',
    due_date: '',
    weight: 1,
    status: 'todo',
});

const openEditTaskModal = (task) => {
    editTaskForm.id = task.id;
    editTaskForm.title = task.title;
    editTaskForm.milestone_id = task.milestone_id || '';
    editTaskForm.description = task.description || '';
    editTaskForm.priority = task.priority || 'medium';
    editTaskForm.due_date = task.due_date || '';
    editTaskForm.weight = task.weight || 1;
    editTaskForm.status = task.status || 'todo';
    isEditTaskModalOpen.value = true;
};

const submitEditTask = () => {
    editTaskForm.put(route('tasks.update', editTaskForm.id), {
        onSuccess: () => {
            isEditTaskModalOpen.value = false;
        },
    });
};

const toggleTask = (task) => {
    router.post(route('tasks.toggle', task.id), {}, { preserveScroll: true });
};

const deleteTask = (task) => {
    if (confirm(`Hapus tugas "${task.title}"?`)) {
        router.delete(route('tasks.destroy', task.id), { preserveScroll: true });
    }
};

// ==========================================
// 5. JOURNAL & LOGBOOK CRUD
// ==========================================
const journalForm = useForm({
    title: '',
    date: new Date().toISOString().split('T')[0],
    activity: '',
    results: '',
    challenges: '',
    next_steps: '',
    phase_id: '',
    task_id: '',
    status: 'finalized',
    is_public: false,
    files: [],
});

const handleJournalFileUpload = (e) => {
    journalForm.files = Array.from(e.target.files);
};

const submitJournal = () => {
    journalForm.post(route('journal.store', projectKey.value), {
        onSuccess: () => {
            isJournalModalOpen.value = false;
            journalForm.reset();
        },
    });
};

const editJournalForm = useForm({
    id: null,
    title: '',
    date: '',
    activity: '',
    results: '',
    challenges: '',
    next_steps: '',
    phase_id: '',
    task_id: '',
    status: 'finalized',
    is_public: false,
});

const openEditJournalModal = (entry) => {
    editJournalForm.id = entry.id;
    editJournalForm.title = entry.title;
    editJournalForm.date = entry.date ? entry.date.split('T')[0] : '';
    editJournalForm.activity = entry.activity;
    editJournalForm.results = entry.results || '';
    editJournalForm.challenges = entry.challenges || '';
    editJournalForm.next_steps = entry.next_steps || '';
    editJournalForm.phase_id = entry.phase_id || '';
    editJournalForm.task_id = entry.task_id || '';
    editJournalForm.status = entry.status || 'finalized';
    editJournalForm.is_public = Boolean(entry.is_public);
    isEditJournalModalOpen.value = true;
};

const submitEditJournal = () => {
    editJournalForm.put(route('journal.update', editJournalForm.id), {
        onSuccess: () => {
            isEditJournalModalOpen.value = false;
        },
    });
};

const toggleJournalPublic = (entry) => {
    router.post(route('journal.toggle_public', entry.id), {}, { preserveScroll: true });
};

const deleteJournal = (entry) => {
    if (confirm(`Hapus catatan logbook "${entry.title}"?`)) {
        router.delete(route('journal.destroy', entry.id), { preserveScroll: true });
    }
};

// ==========================================
// 6. BUDGET ITEMS (RAB) CRUD
// ==========================================
const budgetItemForm = useForm({
    category: 'Alat & Bahan',
    item_name: '',
    qty: 1,
    unit: 'unit',
    estimated_unit_price: 0,
    procurement_status: 'planned',
});

const submitBudgetItem = () => {
    budgetItemForm.post(route('budget.items.store', projectKey.value), {
        onSuccess: () => {
            isBudgetItemModalOpen.value = false;
            budgetItemForm.reset();
        },
    });
};

const editBudgetItemForm = useForm({
    id: null,
    category: 'Alat & Bahan',
    item_name: '',
    qty: 1,
    unit: 'unit',
    estimated_unit_price: 0,
    procurement_status: 'planned',
});

const openEditBudgetItemModal = (item) => {
    editBudgetItemForm.id = item.id;
    editBudgetItemForm.category = item.category;
    editBudgetItemForm.item_name = item.item_name;
    editBudgetItemForm.qty = item.qty;
    editBudgetItemForm.unit = item.unit;
    editBudgetItemForm.estimated_unit_price = item.estimated_unit_price;
    editBudgetItemForm.procurement_status = item.procurement_status;
    isEditBudgetItemModalOpen.value = true;
};

const submitEditBudgetItem = () => {
    editBudgetItemForm.put(route('budget.items.update', editBudgetItemForm.id), {
        onSuccess: () => {
            isEditBudgetItemModalOpen.value = false;
        },
    });
};

const deleteBudgetItem = (item) => {
    if (confirm(`Hapus item anggaran "${item.item_name}"?`)) {
        router.delete(route('budget.items.destroy', item.id), { preserveScroll: true });
    }
};

// ==========================================
// 7. EXPENSES (PENGELUARAN) CRUD
// ==========================================
const expenseForm = useForm({
    budget_item_id: '',
    date: new Date().toISOString().split('T')[0],
    description: '',
    category: 'Operasional',
    amount: 0,
    notes: '',
});

const submitExpense = () => {
    expenseForm.post(route('budget.expenses.store', projectKey.value), {
        onSuccess: () => {
            isExpenseModalOpen.value = false;
            expenseForm.reset();
        },
    });
};

const editExpenseForm = useForm({
    id: null,
    budget_item_id: '',
    date: '',
    description: '',
    category: 'Operasional',
    amount: 0,
    notes: '',
});

const openEditExpenseModal = (exp) => {
    editExpenseForm.id = exp.id;
    editExpenseForm.budget_item_id = exp.budget_item_id || '';
    editExpenseForm.date = exp.date ? exp.date.split('T')[0] : '';
    editExpenseForm.description = exp.description;
    editExpenseForm.category = exp.category;
    editExpenseForm.amount = exp.amount;
    editExpenseForm.notes = exp.notes || '';
    isEditExpenseModalOpen.value = true;
};

const submitEditExpense = () => {
    editExpenseForm.put(route('budget.expenses.update', editExpenseForm.id), {
        onSuccess: () => {
            isEditExpenseModalOpen.value = false;
        },
    });
};

const deleteExpense = (exp) => {
    if (confirm(`Hapus catatan belanja "${exp.description}"?`)) {
        router.delete(route('budget.expenses.destroy', exp.id), { preserveScroll: true });
    }
};

// ==========================================
// 8. OUTPUTS (LUARAN) CRUD
// ==========================================
const outputForm = useForm({
    title: '',
    output_type: 'paper',
    status: 'drafting',
    target_date: '',
    venue_or_journal: '',
    doi_or_url: '',
    evidence_link: '',
    is_public: true,
    notes: '',
});

const submitOutput = () => {
    outputForm.post(route('outputs.store', projectKey.value), {
        onSuccess: () => {
            isOutputModalOpen.value = false;
            outputForm.reset();
        },
    });
};

const editOutputForm = useForm({
    id: null,
    title: '',
    output_type: 'paper',
    status: 'drafting',
    target_date: '',
    venue_or_journal: '',
    doi_or_url: '',
    evidence_link: '',
    is_public: true,
    notes: '',
});

const openEditOutputModal = (out) => {
    editOutputForm.id = out.id;
    editOutputForm.title = out.title;
    editOutputForm.output_type = out.output_type;
    editOutputForm.status = out.status;
    editOutputForm.target_date = out.target_date ? out.target_date.split('T')[0] : '';
    editOutputForm.venue_or_journal = out.venue_or_journal || '';
    editOutputForm.doi_or_url = out.doi_or_url || '';
    editOutputForm.evidence_link = out.evidence_link || '';
    editOutputForm.is_public = Boolean(out.is_public);
    editOutputForm.notes = out.notes || '';
    isEditOutputModalOpen.value = true;
};

const submitEditOutput = () => {
    editOutputForm.put(route('outputs.update', editOutputForm.id), {
        onSuccess: () => {
            isEditOutputModalOpen.value = false;
        },
    });
};

const deleteOutput = (out) => {
    if (confirm(`Hapus luaran "${out.title}"?`)) {
        router.delete(route('outputs.destroy', out.id), { preserveScroll: true });
    }
};

// ==========================================
// 9. PUBLIC SHARING SETTINGS
// ==========================================
const sharingForm = useForm({
    is_public: props.project.is_public,
    public_settings: {
        show_overview: props.project.public_settings?.show_overview ?? true,
        show_milestones: props.project.public_settings?.show_milestones ?? true,
        show_journal: props.project.public_settings?.show_journal ?? true,
        show_outputs: props.project.public_settings?.show_outputs ?? true,
        show_budget_summary: props.project.public_settings?.show_budget_summary ?? true,
        allow_feedback: props.project.public_settings?.allow_feedback ?? true,
    },
});

const saveSharingSettings = () => {
    sharingForm.post(route('projects.share.update', projectKey.value), {
        preserveScroll: true,
    });
};

const regenerateToken = () => {
    if (confirm('Tautan publik lama akan dinonaktifkan dan diganti dengan token baru. Lanjutkan?')) {
        router.post(route('projects.share.regenerate', projectKey.value), {}, { preserveScroll: true });
    }
};

// ==========================================
// 10. TEAM COLLABORATION CRUD
// ==========================================
const memberForm = useForm({
    email: '',
    role: 'Anggota Tim Kolaborator',
});

const submitMember = () => {
    memberForm.post(route('projects.members.store', projectKey.value), {
        onSuccess: () => {
            isTeamModalOpen.value = false;
            memberForm.reset();
        },
    });
};

const removeMember = (member) => {
    const name = member.user?.name || member.email;
    if (confirm(`Hapus ${name} dari tim proyek ini?`)) {
        router.delete(route('projects.members.destroy', [projectKey.value, member.id]), {
            preserveScroll: true,
        });
    }
};

const regenerateTeamInvite = () => {
    if (confirm('Perbarui tautan undangan tim? Tautan lama akan kadaluarsa dan tidak bisa digunakan.')) {
        router.post(route('projects.members.regenerate_invite', projectKey.value), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="project.title" />

    <AuthenticatedLayout>
        <!-- Project Header Bar -->
        <template #header>
            <div class="space-y-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="space-y-1.5 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <Link :href="route('projects.index')" class="text-xs text-emerald-700 hover:text-emerald-900 font-semibold flex items-center">
                                <AppIcon name="arrow-left" :size="13" class="mr-1" />
                                <span>Semua Proyek</span>
                            </Link>
                            <span class="text-slate-300">•</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                :class="getModeInfo(project.mode).color"
                            >
                                <AppIcon :name="getModeInfo(project.mode).icon" :size="12" class="mr-1.5" />
                                <span>{{ getModeInfo(project.mode).label }}</span>
                            </span>
                            <span v-if="project.is_public" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <AppIcon name="share" :size="11" class="mr-1" />
                                <span>Tautan Publik Aktif</span>
                            </span>
                        </div>

                        <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight">
                            {{ project.title }}
                        </h1>
                    </div>

                    <!-- Action Buttons (Desktop & Tablet) -->
                    <div class="hidden sm:flex flex-wrap items-center gap-2">
                        <button
                            v-if="isOwner"
                            @click="isProjectModalOpen = true"
                            type="button"
                            class="inline-flex items-center px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs transition-all active:scale-95"
                        >
                            <AppIcon name="file-text" :size="13" class="mr-1.5 text-slate-500" />
                            <span>Edit Proyek</span>
                        </button>
                        <button
                            @click="isJournalModalOpen = true"
                            type="button"
                            class="inline-flex items-center px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition-all active:scale-95"
                        >
                            <AppIcon name="logbook" :size="14" class="mr-1.5" />
                            <span>Catat Logbook</span>
                        </button>
                        <button
                            @click="isTaskModalOpen = true"
                            type="button"
                            class="inline-flex items-center px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs transition-all active:scale-95"
                        >
                            <AppIcon name="plus" :size="14" class="mr-1.5" />
                            <span>Tambah Tugas</span>
                        </button>
                        <button
                            @click="activeTab = 'share'"
                            type="button"
                            class="inline-flex items-center px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs transition-all active:scale-95"
                        >
                            <AppIcon name="share" :size="14" class="mr-1.5" />
                            <span>Bagikan</span>
                        </button>
                    </div>
                </div>

                <!-- Progress Header Strip -->
                <div class="bg-slate-50 p-3 sm:p-4 rounded-2xl border border-slate-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center space-x-3">
                        <span class="font-bold text-slate-700">Progres:</span>
                        <div class="w-32 sm:w-44 bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-500" :style="{ width: `${project.progress_percent}%` }"></div>
                        </div>
                        <span class="font-extrabold text-emerald-800 text-sm">{{ project.progress_percent }}%</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-slate-600 text-[11px] sm:text-xs">
                        <span><strong>{{ project.tasks?.filter(t => t.status === 'done').length }}</strong> dari <strong>{{ project.tasks?.length }}</strong> Tugas Selesai</span>
                        <span>•</span>
                        <span>Realisasi: <strong>{{ formatRupiah(budgetSummary.total_actual) }}</strong></span>
                    </div>
                </div>

                <!-- Navigation Tabs (Horizontally scrollable with smooth touch targets on mobile) -->
                <div class="-mx-4 px-4 sm:mx-0 sm:px-0 flex items-center space-x-1 sm:space-x-2 border-b border-slate-200 overflow-x-auto pb-1 text-xs sm:text-sm font-semibold no-scrollbar">
                    <button
                        @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'text-emerald-800 border-emerald-600 bg-emerald-50/70 font-bold rounded-t-xl' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50 rounded-t-xl'"
                        class="py-2.5 px-3.5 border-b-2 transition-all shrink-0 flex items-center whitespace-nowrap"
                    >
                        <AppIcon name="file-text" :size="14" class="mr-1.5" />
                        <span>Ringkasan</span>
                    </button>
                    <button
                        @click="activeTab = 'roadmap'"
                        :class="activeTab === 'roadmap' ? 'text-emerald-800 border-emerald-600 bg-emerald-50/70 font-bold rounded-t-xl' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50 rounded-t-xl'"
                        class="py-2.5 px-3.5 border-b-2 transition-all shrink-0 flex items-center whitespace-nowrap"
                    >
                        <AppIcon name="target" :size="14" class="mr-1.5" />
                        <span>Roadmap & Tugas ({{ project.tasks?.length || 0 }})</span>
                    </button>
                    <button
                        @click="activeTab = 'journal'"
                        :class="activeTab === 'journal' ? 'text-emerald-800 border-emerald-600 bg-emerald-50/70 font-bold rounded-t-xl' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50 rounded-t-xl'"
                        class="py-2.5 px-3.5 border-b-2 transition-all shrink-0 flex items-center whitespace-nowrap"
                    >
                        <AppIcon name="logbook" :size="14" class="mr-1.5" />
                        <span>Jurnal & Logbook ({{ project.journal_entries?.length || 0 }})</span>
                    </button>
                    <button
                        @click="activeTab = 'budget'"
                        :class="activeTab === 'budget' ? 'text-emerald-800 border-emerald-600 bg-emerald-50/70 font-bold rounded-t-xl' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50 rounded-t-xl'"
                        class="py-2.5 px-3.5 border-b-2 transition-all shrink-0 flex items-center whitespace-nowrap"
                    >
                        <AppIcon name="budget" :size="14" class="mr-1.5" />
                        <span>Anggaran & Belanja</span>
                    </button>
                    <button
                        @click="activeTab = 'outputs'"
                        :class="activeTab === 'outputs' ? 'text-emerald-800 border-emerald-600 bg-emerald-50/70 font-bold rounded-t-xl' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50 rounded-t-xl'"
                        class="py-2.5 px-3.5 border-b-2 transition-all shrink-0 flex items-center whitespace-nowrap"
                    >
                        <AppIcon name="award" :size="14" class="mr-1.5" />
                        <span>Luaran ({{ project.outputs?.length || 0 }})</span>
                    </button>
                    <button
                        @click="activeTab = 'team'"
                        :class="activeTab === 'team' ? 'text-emerald-800 border-emerald-600 bg-emerald-50/70 font-bold rounded-t-xl' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50 rounded-t-xl'"
                        class="py-2.5 px-3.5 border-b-2 transition-all shrink-0 flex items-center whitespace-nowrap"
                    >
                        <AppIcon name="users" :size="14" class="mr-1.5" />
                        <span>Tim ({{ (project.members?.length || 0) + 1 }})</span>
                    </button>
                    <button
                        @click="activeTab = 'share'"
                        :class="activeTab === 'share' ? 'text-emerald-800 border-emerald-600 bg-emerald-50/70 font-bold rounded-t-xl' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50 rounded-t-xl'"
                        class="py-2.5 px-3.5 border-b-2 transition-all shrink-0 flex items-center whitespace-nowrap"
                    >
                        <AppIcon name="share" :size="14" class="mr-1.5" />
                        <span>Tautan Publik</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Main Workspace Body -->
        <div class="py-5 sm:py-8 pb-28 sm:pb-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- ============================================================== -->
                <!-- TAB 1: OVERVIEW & RINGKASAN                                     -->
                <!-- ============================================================== -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Ringkasan & Informasi Proyek</h2>
                            <p class="text-xs text-slate-500">Gambaran komprehensif latar belakang, target capaian, dan progres utama.</p>
                        </div>
                        <div v-if="isOwner" class="flex items-center space-x-2">
                            <button
                                @click="isProjectModalOpen = true"
                                class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center shadow-xs transition-colors"
                            >
                                <AppIcon name="file-text" :size="13" class="mr-1.5 text-slate-500" />
                                <span>Edit Informasi Proyek</span>
                            </button>
                            <button
                                @click="deleteProject"
                                class="px-3 py-2 text-xs font-semibold rounded-xl text-rose-600 hover:bg-rose-50 flex items-center transition-colors"
                                title="Hapus Proyek"
                            >
                                <AppIcon name="trash" :size="13" class="mr-1" />
                                <span>Hapus Proyek</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left 2 Cols: Problem Statement & Target Outcome -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center">
                                    <AppIcon name="target" :size="17" class="mr-2 text-emerald-700" />
                                    <span>Rumusan Masalah & Latar Belakang</span>
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100 whitespace-pre-line">
                                    {{ project.problem_statement || 'Belum diisi. Tambahkan latar belakang masalah proyek ini.' }}
                                </p>

                                <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center pt-2">
                                    <AppIcon name="award" :size="17" class="mr-2 text-emerald-700" />
                                    <span>Target Luaran Akhir yang Diharapkan</span>
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-800 leading-relaxed bg-emerald-50/40 p-4 rounded-xl border border-emerald-100 whitespace-pre-line">
                                    {{ project.target_outcome || 'Belum diisi. Masukkan target publikasi, prototipe, atau indikator dampak.' }}
                                </p>

                                <div v-if="project.description" class="pt-2">
                                    <h4 class="text-xs font-bold uppercase text-slate-400 mb-1">Deskripsi Tambahan</h4>
                                    <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ project.description }}</p>
                                </div>
                            </div>

                            <!-- Highlights: Latest Logbook -->
                            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center">
                                        <AppIcon name="logbook" :size="17" class="mr-2 text-emerald-700" />
                                        <span>Catatan Progres Lapangan Terkini</span>
                                    </h3>
                                    <button @click="activeTab = 'journal'" class="text-xs font-semibold text-emerald-700 hover:underline">
                                        Lihat Semua Jurnal &rarr;
                                    </button>
                                </div>

                                <div v-if="project.journal_entries?.length === 0" class="text-xs text-slate-400 py-3 text-center">
                                    Belum ada catatan logbook. Klik tombol di atas untuk mencatat kegiatan hari ini.
                                </div>
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="entry in project.journal_entries.slice(0, 3)"
                                        :key="entry.id"
                                        class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5"
                                    >
                                        <div class="flex items-center justify-between text-slate-400 text-[11px]">
                                            <span class="font-bold text-slate-700">{{ entry.title }}</span>
                                            <span>{{ new Date(entry.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</span>
                                        </div>
                                        <p class="text-slate-600 line-clamp-2 leading-relaxed">{{ entry.activity }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right 1 Col: Quick Metadata Card -->
                        <div class="space-y-6">
                            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4 text-xs">
                                <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">Metadata Workspace</h3>
                                
                                <div>
                                    <span class="text-slate-400 block mb-1">Mode & Kategori:</span>
                                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <AppIcon :name="getModeInfo(project.mode).icon" :size="14" class="text-emerald-700" />
                                        {{ getModeInfo(project.mode).label }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-slate-400 block mb-1">Status Proyek:</span>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase inline-block" :class="project.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
                                        {{ project.status }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-slate-400 block mb-1">Jadwal Pelaksanaan:</span>
                                    <span class="font-semibold text-slate-800">
                                        {{ project.start_date || 'Belum diatur' }} s/d {{ project.end_date || 'Selesai' }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-slate-400 block mb-1">Ketua Peneliti / Owner:</span>
                                    <span class="font-bold text-slate-900 block">{{ project.user?.name }}</span>
                                    <span class="text-slate-500 text-[11px]">{{ project.user?.email }}</span>
                                </div>

                                <div class="pt-3 border-t border-slate-100">
                                    <button
                                        @click="copyShareLink"
                                        class="w-full py-2.5 px-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold hover:bg-emerald-100 transition-colors flex items-center justify-center"
                                    >
                                        <AppIcon name="share" :size="14" class="mr-1.5" />
                                        <span>{{ copiedShareLink ? 'Tautan Tersalin ke Clipboard!' : 'Salin Tautan Publik Proyek' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 2: ROADMAP, FASE & TUGAS                                   -->
                <!-- ============================================================== -->
                <div v-if="activeTab === 'roadmap'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Roadmap, Fase & Daftar Tugas</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Kelola target milestone dan centang tugas harian untuk memperbarui progres otomatis.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                @click="isPhaseModalOpen = true"
                                class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center justify-center active:scale-95"
                            >
                                <AppIcon name="layers" :size="13" class="mr-1.5" />
                                <span>+ Fase Baru</span>
                            </button>
                            <button
                                @click="isMilestoneModalOpen = true"
                                class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center justify-center active:scale-95"
                            >
                                <AppIcon name="target" :size="13" class="mr-1.5" />
                                <span>+ Milestone Baru</span>
                            </button>
                            <button
                                @click="isTaskModalOpen = true"
                                class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs flex items-center justify-center active:scale-95"
                            >
                                <AppIcon name="plus" :size="13" class="mr-1.5" />
                                <span>+ Tugas Baru</span>
                            </button>
                        </div>
                    </div>

                    <!-- Milestones and Tasks by Phase -->
                    <div class="space-y-6">
                        <div
                            v-for="phase in project.phases"
                            :key="phase.id"
                            class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4"
                        >
                            <!-- Phase Header with Edit and Delete Buttons -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center text-xs font-extrabold mr-2.5 border border-emerald-200">
                                        {{ phase.order }}
                                    </span>
                                    <span>{{ phase.name }}</span>
                                </h3>

                                <div class="flex items-center space-x-1 text-xs">
                                    <button
                                        @click="openEditPhaseModal(phase)"
                                        class="px-2.5 py-1 text-slate-500 hover:text-emerald-700 hover:bg-slate-50 rounded-lg font-medium transition-colors"
                                        title="Ubah nama fase"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deletePhase(phase)"
                                        class="px-2 py-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                        title="Hapus fase"
                                    >
                                        <AppIcon name="trash" :size="13" />
                                    </button>
                                </div>
                            </div>

                            <!-- Milestones in this phase -->
                            <div v-if="project.milestones.filter(m => m.phase_id === phase.id).length === 0" class="text-xs text-slate-400 py-2">
                                Belum ada milestone pada fase ini.
                            </div>
                            <div
                                v-for="milestone in project.milestones.filter(m => m.phase_id === phase.id)"
                                :key="milestone.id"
                                class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/60 space-y-3"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start space-x-3">
                                        <button
                                            @click="completeMilestone(milestone)"
                                            :class="milestone.status === 'completed' ? 'bg-emerald-600 text-white' : 'border-2 border-slate-300 bg-white'"
                                            class="w-6 h-6 rounded-lg flex items-center justify-center mt-0.5 hover:border-emerald-600 transition-colors shrink-0"
                                            title="Tandai Milestone Selesai"
                                        >
                                            <AppIcon v-if="milestone.status === 'completed'" name="check" :size="13" />
                                        </button>
                                        <div>
                                            <h4
                                                :class="milestone.status === 'completed' ? 'line-through text-slate-400' : 'text-slate-900'"
                                                class="text-xs sm:text-sm font-bold"
                                            >
                                                {{ milestone.title }}
                                            </h4>
                                            <p v-if="milestone.definition_of_done" class="text-xs text-slate-500 mt-0.5">
                                                Definisi Selesai: {{ milestone.definition_of_done }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 shrink-0 text-xs">
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-slate-200 text-slate-700">
                                            {{ milestone.due_date ? new Date(milestone.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : 'No date' }}
                                        </span>
                                        <button
                                            @click="openEditMilestoneModal(milestone)"
                                            class="text-slate-400 hover:text-emerald-700 p-1 rounded hover:bg-slate-200"
                                            title="Edit milestone"
                                        >
                                            <AppIcon name="file-text" :size="13" />
                                        </button>
                                        <button
                                            @click="deleteMilestone(milestone)"
                                            class="text-slate-300 hover:text-rose-600 p-1 rounded hover:bg-rose-50"
                                            title="Hapus milestone"
                                        >
                                            <AppIcon name="trash" :size="13" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Tasks attached to milestone -->
                                <div class="pl-4 sm:pl-8 pt-2 space-y-2 border-t border-slate-200/40">
                                    <div
                                        v-for="task in project.tasks.filter(t => t.milestone_id === milestone.id)"
                                        :key="task.id"
                                        class="flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200/60 hover:border-emerald-300 transition-colors"
                                    >
                                        <div class="flex items-center space-x-3 flex-1 min-w-0 pr-2">
                                            <button
                                                @click="toggleTask(task)"
                                                :class="task.status === 'done' ? 'bg-emerald-600 text-white' : 'border-2 border-slate-300 bg-white hover:border-emerald-600'"
                                                class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 transition-all cursor-pointer active:scale-95"
                                                title="Centang tugas"
                                            >
                                                <AppIcon v-if="task.status === 'done'" name="check" :size="13" />
                                            </button>
                                            <span
                                                :class="task.status === 'done' ? 'line-through text-slate-400' : 'text-slate-800'"
                                                class="text-xs font-medium truncate"
                                            >
                                                {{ task.title }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2 text-xs shrink-0">
                                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                {{ task.priority }}
                                            </span>
                                            <button
                                                @click="openEditTaskModal(task)"
                                                class="text-slate-400 hover:text-emerald-700 p-1 rounded hover:bg-slate-100"
                                                title="Edit tugas"
                                            >
                                                <AppIcon name="file-text" :size="13" />
                                            </button>
                                            <button
                                                @click="deleteTask(task)"
                                                class="text-slate-300 hover:text-rose-600 p-1 rounded hover:bg-rose-50"
                                                title="Hapus tugas"
                                            >
                                                <AppIcon name="trash" :size="13" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 3: AUTO-LOGBOOK & JURNAL                                    -->
                <!-- ============================================================== -->
                <div v-if="activeTab === 'journal'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Auto-Logbook & Development Journal</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Catat proses, foto lapangan, dan bukti pengujian secara konsisten tanpa beban administratif.</p>
                        </div>
                        <button
                            @click="isJournalModalOpen = true"
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs flex items-center justify-center active:scale-95"
                        >
                            <AppIcon name="logbook" :size="14" class="mr-1.5" />
                            <span>+ Tulis Catatan Baru</span>
                        </button>
                    </div>

                    <!-- Journal Timeline Feed -->
                    <div class="space-y-4">
                        <div v-if="project.journal_entries?.length === 0" class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center">
                            <AppIcon name="logbook" :size="36" class="text-slate-300 mx-auto" />
                            <h3 class="text-sm font-bold text-slate-800 mt-2">Belum ada catatan logbook</h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Catat kegiatan Anda hari ini dalam kurang dari 2 menit!</p>
                            <button
                                @click="isJournalModalOpen = true"
                                class="mt-4 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700"
                            >
                                Catat Sekarang
                            </button>
                        </div>

                        <div
                            v-for="entry in project.journal_entries"
                            :key="entry.id"
                            class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-3"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400 mb-1">
                                        <span class="px-2 py-0.5 rounded-full font-bold flex items-center bg-slate-100 text-slate-700">
                                            <AppIcon :name="entry.entry_type === 'system' ? 'activity' : 'file-text'" :size="11" class="mr-1" />
                                            <span>{{ entry.entry_type === 'system' ? 'Otomatis Sistem' : 'Catatan Peneliti' }}</span>
                                        </span>
                                        <span>{{ new Date(entry.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
                                    </div>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ entry.title }}</h3>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="toggleJournalPublic(entry)"
                                        :class="entry.is_public ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors flex items-center"
                                        title="Ubah visibilitas publik"
                                    >
                                        <AppIcon :name="entry.is_public ? 'unlock' : 'lock'" :size="12" class="mr-1" />
                                        <span>{{ entry.is_public ? 'Tampil Publik' : 'Privat' }}</span>
                                    </button>

                                    <button
                                        v-if="entry.entry_type !== 'system'"
                                        @click="openEditJournalModal(entry)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-700 hover:bg-slate-100 transition-colors"
                                        title="Edit logbook"
                                    >
                                        <AppIcon name="file-text" :size="14" />
                                    </button>
                                    <button
                                        @click="deleteJournal(entry)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Hapus logbook"
                                    >
                                        <AppIcon name="trash" :size="14" />
                                    </button>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50/60 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line">
                                {{ entry.activity }}
                            </p>

                            <!-- Results & Challenges Badges -->
                            <div v-if="entry.results || entry.challenges" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                <div v-if="entry.results" class="bg-emerald-50 border border-emerald-100 text-emerald-900 p-3 rounded-xl text-xs flex items-start">
                                    <AppIcon name="sparkles" :size="14" class="mr-1.5 shrink-0 mt-0.5 text-emerald-700" />
                                    <span><strong>Hasil:</strong> {{ entry.results }}</span>
                                </div>
                                <div v-if="entry.challenges" class="bg-slate-100 border border-slate-200 text-slate-800 p-3 rounded-xl text-xs flex items-start">
                                    <AppIcon name="alert" :size="14" class="mr-1.5 shrink-0 mt-0.5 text-slate-600" />
                                    <span><strong>Hambatan:</strong> {{ entry.challenges }}</span>
                                </div>
                            </div>

                            <!-- Attachments Gallery -->
                            <div v-if="entry.attachments?.length > 0" class="pt-3 border-t border-slate-100 flex flex-wrap gap-2 text-xs">
                                <div
                                    v-for="(att, i) in entry.attachments"
                                    :key="i"
                                    class="p-2 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center space-x-1.5 text-xs text-slate-700"
                                >
                                    <AppIcon name="paperclip" :size="12" class="text-slate-400" />
                                    <span class="font-medium">{{ att.caption || att.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 4: ANGGARAN & BELANJA (CRUD Lengkap)                       -->
                <!-- ============================================================== -->
                <div v-if="activeTab === 'budget'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Rencana Anggaran Biaya (RAB) & Realisasi Belanja</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Kelola estimasi biaya pengadaan dan pembelanjaan aktual secara rapi.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a
                                :href="route('budget.export', project.slug || project.id)"
                                class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center justify-center active:scale-95"
                            >
                                <AppIcon name="download" :size="13" class="mr-1.5" />
                                <span>Ekspor CSV</span>
                            </a>
                            <button
                                @click="isBudgetItemModalOpen = true"
                                class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center justify-center active:scale-95"
                            >
                                <AppIcon name="plus" :size="13" class="mr-1.5" />
                                <span>+ Item RAB</span>
                            </button>
                            <button
                                @click="isExpenseModalOpen = true"
                                class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs flex items-center justify-center active:scale-95"
                            >
                                <AppIcon name="budget" :size="13" class="mr-1.5" />
                                <span>+ Catat Belanja</span>
                            </button>
                        </div>
                    </div>

                    <!-- 3 Cards: Estimated, Actual, Remaining -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                            <span class="text-[11px] font-bold uppercase text-slate-400">Total Rencana (RAB)</span>
                            <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1">{{ formatRupiah(budgetSummary.total_estimated) }}</div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                            <span class="text-[11px] font-bold uppercase text-slate-400">Realisasi Aktual</span>
                            <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1">{{ formatRupiah(budgetSummary.total_actual) }}</div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                            <span class="text-[11px] font-bold uppercase text-slate-400">Sisa Anggaran</span>
                            <div class="text-xl sm:text-2xl font-extrabold text-emerald-800 mt-1">{{ formatRupiah(budgetSummary.remaining) }}</div>
                        </div>
                    </div>

                    <!-- Budget Items Table -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Rencana Kebutuhan Pengadaan (RAB)</h3>
                            <span class="text-xs text-slate-500">{{ project.budget_items?.length || 0 }} Item</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-100 whitespace-nowrap">
                                    <tr>
                                        <th class="p-3.5">Kategori</th>
                                        <th class="p-3.5">Item Belanja</th>
                                        <th class="p-3.5 text-center">Jumlah</th>
                                        <th class="p-3.5 text-right">Harga Satuan</th>
                                        <th class="p-3.5 text-right">Total Estimasi</th>
                                        <th class="p-3.5 text-center">Status</th>
                                        <th class="p-3.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-if="project.budget_items?.length === 0">
                                        <td colspan="7" class="p-6 text-center text-slate-400">Belum ada rencana anggaran.</td>
                                    </tr>
                                    <tr v-for="item in project.budget_items" :key="item.id" class="hover:bg-slate-50/60">
                                        <td class="p-3.5 font-semibold text-slate-700 whitespace-nowrap">{{ item.category }}</td>
                                        <td class="p-3.5 font-bold text-slate-900">{{ item.item_name }}</td>
                                        <td class="p-3.5 text-center whitespace-nowrap">{{ item.qty }} {{ item.unit }}</td>
                                        <td class="p-3.5 text-right whitespace-nowrap">{{ formatRupiah(item.estimated_unit_price) }}</td>
                                        <td class="p-3.5 text-right font-bold text-slate-900 whitespace-nowrap">{{ formatRupiah(item.total_estimated_price) }}</td>
                                        <td class="p-3.5 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="item.procurement_status === 'received' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
                                                {{ item.procurement_status }}
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center space-x-1">
                                                <button
                                                    @click="openEditBudgetItemModal(item)"
                                                    class="p-1 rounded text-slate-400 hover:text-emerald-700 hover:bg-slate-100"
                                                    title="Edit item"
                                                >
                                                    <AppIcon name="file-text" :size="13" />
                                                </button>
                                                <button
                                                    @click="deleteBudgetItem(item)"
                                                    class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                                    title="Hapus item"
                                                >
                                                    <AppIcon name="trash" :size="13" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Expenses (Realisasi Belanja Aktual) Table -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Realisasi Pengeluaran Aktual</h3>
                            <span class="text-xs text-slate-500">{{ project.expenses?.length || 0 }} Transaksi</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-100 whitespace-nowrap">
                                    <tr>
                                        <th class="p-3.5">Tanggal</th>
                                        <th class="p-3.5">Kategori</th>
                                        <th class="p-3.5">Deskripsi</th>
                                        <th class="p-3.5 text-right">Jumlah Realisasi</th>
                                        <th class="p-3.5">Catatan / Bukti</th>
                                        <th class="p-3.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-if="project.expenses?.length === 0">
                                        <td colspan="6" class="p-6 text-center text-slate-400">Belum ada catatan pengeluaran aktual.</td>
                                    </tr>
                                    <tr v-for="exp in project.expenses" :key="exp.id" class="hover:bg-slate-50/60">
                                        <td class="p-3.5 whitespace-nowrap">{{ new Date(exp.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</td>
                                        <td class="p-3.5 font-semibold text-slate-700 whitespace-nowrap">{{ exp.category }}</td>
                                        <td class="p-3.5 font-bold text-slate-900">{{ exp.description }}</td>
                                        <td class="p-3.5 text-right font-bold text-emerald-800 whitespace-nowrap">{{ formatRupiah(exp.amount) }}</td>
                                        <td class="p-3.5 text-slate-500 max-w-xs truncate">{{ exp.notes || '-' }}</td>
                                        <td class="p-3.5 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center space-x-1">
                                                <button
                                                    @click="openEditExpenseModal(exp)"
                                                    class="p-1 rounded text-slate-400 hover:text-emerald-700 hover:bg-slate-100"
                                                    title="Edit belanja"
                                                >
                                                    <AppIcon name="file-text" :size="13" />
                                                </button>
                                                <button
                                                    @click="deleteExpense(exp)"
                                                    class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                                    title="Hapus belanja"
                                                >
                                                    <AppIcon name="trash" :size="13" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 5: OUTPUTS & PUBLIKASI (CRUD Lengkap)                      -->
                <!-- ============================================================== -->
                <div v-if="activeTab === 'outputs'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Pelacak Target Luaran & Publikasi</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Pantau artikel jurnal, prototipe fisik, dataset, paten, laporan dampak, dan bukti kejuaraan lomba.</p>
                        </div>
                        <button
                            @click="isOutputModalOpen = true"
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs flex items-center justify-center active:scale-95"
                        >
                            <AppIcon name="plus" :size="14" class="mr-1.5" />
                            <span>+ Tambah Target Luaran</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="project.outputs?.length === 0" class="col-span-2 bg-white rounded-2xl border border-slate-200/80 p-8 text-center text-xs text-slate-400">
                            Belum ada target luaran yang ditambahkan.
                        </div>

                        <div
                            v-for="out in project.outputs"
                            :key="out.id"
                            class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4"
                        >
                            <div>
                                <div class="flex items-center justify-between text-xs mb-2">
                                    <span class="px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {{ out.output_type }}
                                    </span>
                                    <div class="flex items-center space-x-1.5">
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase" :class="out.status === 'published_done' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
                                            {{ out.status }}
                                        </span>
                                        <button
                                            @click="openEditOutputModal(out)"
                                            class="p-1 rounded text-slate-400 hover:text-emerald-700 hover:bg-slate-100"
                                            title="Edit Luaran"
                                        >
                                            <AppIcon name="file-text" :size="13" />
                                        </button>
                                        <button
                                            @click="deleteOutput(out)"
                                            class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                            title="Hapus Luaran"
                                        >
                                            <AppIcon name="trash" :size="13" />
                                        </button>
                                    </div>
                                </div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ out.title }}</h3>
                                <p v-if="out.venue_or_journal" class="text-xs text-slate-500 mt-1">
                                    Target Venue: <strong class="text-slate-700">{{ out.venue_or_journal }}</strong>
                                </p>
                                <p v-if="out.notes" class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    {{ out.notes }}
                                </p>
                            </div>

                            <div v-if="out.doi_or_url" class="pt-3 border-t border-slate-100 text-xs">
                                <a :href="out.doi_or_url" target="_blank" class="text-emerald-700 hover:underline font-semibold flex items-center">
                                    <span>Buka Tautan DOI / Repositori</span>
                                    <AppIcon name="external-link" :size="12" class="ml-1" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 6: TIM & KOLABORASI                                         -->
                <!-- ============================================================== -->
                <div v-if="activeTab === 'team'" class="space-y-6">
                    <!-- Team Quick Link Card -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-8 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center">
                                    <AppIcon name="users" :size="20" class="mr-2 text-emerald-700" />
                                    <span>Tautan Undangan Tim (1-Click Join)</span>
                                </h2>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Bagikan tautan ini ke rekan satu tim. Begitu mereka membuka tautan ini, mereka langsung otomatis terdaftar sebagai anggota kolaborator di workspace proyek ini.
                                </p>
                            </div>
                            <span v-if="isOwner" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                                <AppIcon name="check" :size="12" class="mr-1.5" />
                                <span>Akses Ketua Proyek</span>
                            </span>
                            <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                                <span>Anggota Kolaborator</span>
                            </span>
                        </div>

                        <!-- Invite Link Box -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold uppercase text-slate-400">Tautan Gabung Tim</label>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <input
                                    type="text"
                                    readonly
                                    :value="teamInviteUrl"
                                    class="flex-1 text-xs font-mono bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 truncate"
                                />
                                <button
                                    @click="copyTeamInviteLink"
                                    type="button"
                                    class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-xs shrink-0 flex items-center justify-center active:scale-95"
                                >
                                    <AppIcon name="paperclip" :size="14" class="mr-1.5" />
                                    <span>{{ copiedTeamInviteLink ? 'Tautan Tersalin!' : 'Salin Tautan Tim' }}</span>
                                </button>
                                <button
                                    v-if="isOwner"
                                    @click="regenerateTeamInvite"
                                    type="button"
                                    class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-all shrink-0 active:scale-95"
                                    title="Perbarui Tautan Undangan"
                                >
                                    <span>Reset Token</span>
                                </button>
                            </div>
                        </div>

                        <!-- Owner Invite by Email Section -->
                        <div v-if="isOwner" class="pt-6 border-t border-slate-100 space-y-4">
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900">Undang Anggota Baru Melalui Email</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Pengguna yang diundang dapat langsung masuk dan berkontribusi.</p>
                            </div>
                            <form @submit.prevent="submitMember" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                <div class="sm:col-span-6">
                                    <input
                                        v-model="memberForm.email"
                                        type="email"
                                        required
                                        placeholder="Alamat email anggota (contoh: rekan@tim.id)"
                                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                                    />
                                </div>
                                <div class="sm:col-span-4">
                                    <select
                                        v-model="memberForm.role"
                                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
                                    >
                                        <option value="Co-Lead / Wakil Ketua">Co-Lead / Wakil Ketua</option>
                                        <option value="Peneliti / Riset">Peneliti / Riset</option>
                                        <option value="Spesialis Teknis">Spesialis Teknis</option>
                                        <option value="Anggota Tim Kolaborator">Anggota Tim Kolaborator</option>
                                        <option value="Reviewer Tim Internal">Reviewer Tim Internal</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <button
                                        type="submit"
                                        :disabled="memberForm.processing"
                                        class="w-full py-2.5 px-4 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-xs flex items-center justify-center disabled:opacity-50 active:scale-95"
                                    >
                                        <AppIcon name="plus" :size="13" class="mr-1" />
                                        <span>Undang</span>
                                    </button>
                                </div>
                            </form>
                            <p v-if="memberForm.errors.email" class="text-xs text-rose-600 font-medium">{{ memberForm.errors.email }}</p>
                        </div>
                    </div>

                    <!-- Team Members List -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center">
                                <AppIcon name="users" :size="17" class="mr-2 text-emerald-700" />
                                <span>Daftar Anggota Tim ({{ (project.members?.length || 0) + 1 }} Orang)</span>
                            </h3>
                            <span class="text-xs text-slate-400 font-medium">Kolaborasi Aktif</span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            <!-- Project Owner Row -->
                            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                                        {{ (project.user?.name || 'P').charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900">{{ project.user?.name }}</h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                Ketua Proyek / Owner
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ project.user?.email }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-xs font-semibold text-slate-400">Pemilik Workspace</span>
                                </div>
                            </div>

                            <!-- Team Members Rows -->
                            <div
                                v-for="member in project.members"
                                :key="member.id"
                                class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0 border border-slate-200">
                                        {{ (member.user?.name || member.email).charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900">
                                                {{ member.user?.name || member.email.split('@')[0] }}
                                            </h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ member.role }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ member.email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 shrink-0">
                                    <span class="text-[11px] text-slate-400">
                                        {{ member.user_id ? 'Terhubung' : 'Diundang' }}
                                    </span>
                                    <button
                                        v-if="isOwner || member.user_id === $page.props.auth.user.id"
                                        @click="removeMember(member)"
                                        type="button"
                                        class="px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 rounded-lg font-semibold transition-colors"
                                    >
                                        {{ member.user_id === $page.props.auth.user.id && !isOwner ? 'Keluar' : 'Hapus' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 7: PUBLIC SHARE                                            -->
                <!-- ============================================================== -->
                <div v-if="activeTab === 'share'" class="space-y-6">
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-8 shadow-xs space-y-6">
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center">
                                <AppIcon name="share" :size="20" class="mr-2 text-emerald-700" />
                                <span>Fitur Berbagi Publik Tanpa Akun</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Bagikan perkembangan proyek kepada reviewer ahli, mentor industri, atau mitra kolaborator tanpa meminta mereka registrasi akun. 
                                Informasi bersifat <strong>Privat secara Default</strong>, hanya bagian yang Anda centang yang akan terlihat.
                            </p>
                        </div>

                        <!-- Toggle Share On/Off -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs sm:text-sm font-bold text-slate-900">Aktifkan Tautan Publik</div>
                                <div class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Bila dinonaktifkan, tautan publik langsung tertutup dan menolak akses pengunjung.</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input
                                    type="checkbox"
                                    v-model="sharingForm.is_public"
                                    @change="saveSharingSettings"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <!-- Public Link Box -->
                        <div v-if="sharingForm.is_public" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Tautan Publik Acak</label>
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                    <input
                                        type="text"
                                        readonly
                                        :value="publicShareUrl"
                                        class="flex-1 text-xs font-mono bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 truncate"
                                    />
                                    <button
                                        @click="copyShareLink"
                                        type="button"
                                        class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-xs shrink-0 flex items-center justify-center active:scale-95"
                                    >
                                        <AppIcon name="paperclip" :size="13" class="mr-1.5" />
                                        <span>{{ copiedShareLink ? 'Tersalin!' : 'Salin Tautan' }}</span>
                                    </button>
                                    <a
                                        :href="route('public.share', project.share_token)"
                                        target="_blank"
                                        class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-all text-center shrink-0 flex items-center justify-center"
                                    >
                                        <span>Pratinjau Halaman</span>
                                        <AppIcon name="external-link" :size="13" class="ml-1.5" />
                                    </a>
                                </div>
                            </div>

                            <!-- Allowlist Sections Configuration -->
                            <div class="pt-4 border-t border-slate-100">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 mb-3">Pilih Bagian yang Boleh Ditampilkan (Allowlist)</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" v-model="sharingForm.public_settings.show_overview" @change="saveSharingSettings" class="rounded text-emerald-600 focus:ring-emerald-500" />
                                        <span class="font-medium text-slate-800">Tampilkan Ringkasan & Rumusan Masalah</span>
                                    </label>

                                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" v-model="sharingForm.public_settings.show_milestones" @change="saveSharingSettings" class="rounded text-emerald-600 focus:ring-emerald-500" />
                                        <span class="font-medium text-slate-800">Tampilkan Target Milestone Terpilih</span>
                                    </label>

                                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" v-model="sharingForm.public_settings.show_journal" @change="saveSharingSettings" class="rounded text-emerald-600 focus:ring-emerald-500" />
                                        <span class="font-medium text-slate-800">Tampilkan Catatan Logbook yang Disetujui</span>
                                    </label>

                                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" v-model="sharingForm.public_settings.show_outputs" @change="saveSharingSettings" class="rounded text-emerald-600 focus:ring-emerald-500" />
                                        <span class="font-medium text-slate-800">Tampilkan Target Luaran & Publikasi</span>
                                    </label>

                                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" v-model="sharingForm.public_settings.show_budget_summary" @change="saveSharingSettings" class="rounded text-emerald-600 focus:ring-emerald-500" />
                                        <span class="font-medium text-slate-800">Tampilkan Ringkasan Agregat Anggaran</span>
                                    </label>

                                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" v-model="sharingForm.public_settings.allow_feedback" @change="saveSharingSettings" class="rounded text-emerald-600 focus:ring-emerald-500" />
                                        <span class="font-medium text-slate-800">Izinkan Reviewer & Mitra Memberikan Masukan</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Regenerate Token Security -->
                            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-slate-500">
                                <span>Perlu mencabut akses pengunjung lama?</span>
                                <button
                                    @click="regenerateToken"
                                    type="button"
                                    class="text-slate-600 hover:text-slate-900 underline font-semibold"
                                >
                                    Perbarui Token & Cabut Tautan Lama
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Feedbacks from Reviewers -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center">
                            <AppIcon name="feedback" :size="17" class="mr-2 text-emerald-700" />
                            <span>Catatan Masukan dari Reviewer & Mitra ({{ project.feedbacks?.length || 0 }})</span>
                        </h3>
                        <div v-if="project.feedbacks?.length === 0" class="text-xs text-slate-400 py-3">
                            Belum ada catatan masukan dari pengunjung publik.
                        </div>
                        <div
                            v-for="f in project.feedbacks"
                            :key="f.id"
                            class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 space-y-2"
                        >
                            <div class="flex items-center justify-between text-xs">
                                <div>
                                    <strong class="text-slate-900">{{ f.reviewer_name }}</strong>
                                    <span class="text-slate-400 ml-1.5">({{ f.reviewer_role }})</span>
                                </div>
                                <div class="flex items-center space-x-0.5 text-emerald-600">
                                    <AppIcon v-for="star in f.rating" :key="star" name="star" :size="12" class="fill-emerald-500 text-emerald-500" />
                                </div>
                            </div>
                            <p class="text-xs text-slate-700 leading-relaxed">{{ f.comment }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- ALL MODALS (CREATE & UPDATE DIALOGS)                            -->
        <!-- ============================================================== -->

        <!-- 1. MODAL EDIT PROYEK (Overview) -->
        <div v-if="isProjectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-xl w-full p-5 sm:p-7 shadow-2xl max-h-[92vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Edit Informasi Proyek</h2>
                    <button @click="isProjectModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitProjectEdit" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Proyek <span class="text-rose-500">*</span></label>
                        <input v-model="projectForm.title" type="text" required class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori / Mode Proyek</label>
                            <select v-model="projectForm.mode" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="research">Research Development (Riset)</option>
                                <option value="product">Product Development (Produk)</option>
                                <option value="social">Social Project (Sosial)</option>
                                <option value="competition">Lomba & Kompetisi Inovasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Status Proyek</label>
                            <select v-model="projectForm.status" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="active">Aktif (Active)</option>
                                <option value="completed">Selesai (Completed)</option>
                                <option value="draft">Draf (Draft)</option>
                                <option value="on_hold">Tertunda (On Hold)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                            <input v-model="projectForm.start_date" type="date" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Target Selesai</label>
                            <input v-model="projectForm.end_date" type="date" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Latar Belakang / Rumusan Masalah</label>
                        <textarea v-model="projectForm.problem_statement" rows="3" class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Target Luaran Akhir</label>
                        <textarea v-model="projectForm.target_outcome" rows="2" class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2 border-t border-slate-100">
                        <button type="button" @click="isProjectModalOpen = false" class="px-4 py-2 text-slate-600 rounded-xl hover:bg-slate-100">Batal</button>
                        <button type="submit" :disabled="projectForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. MODAL TAMBAH FASE -->
        <div v-if="isPhaseModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Tambah Fase Baru</h2>
                    <button @click="isPhaseModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitPhase" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Fase <span class="text-rose-500">*</span></label>
                        <input v-model="phaseForm.name" type="text" required placeholder="Contoh: Fase 2: Pengujian Lapangan & Uji Coba" class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isPhaseModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="phaseForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Simpan Fase</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. MODAL EDIT FASE -->
        <div v-if="isEditPhaseModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Edit Nama Fase</h2>
                    <button @click="isEditPhaseModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitEditPhase" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Fase <span class="text-rose-500">*</span></label>
                        <input v-model="editPhaseForm.name" type="text" required class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isEditPhaseModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="editPhaseForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Perbarui Fase</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4. MODAL TAMBAH MILESTONE -->
        <div v-if="isMilestoneModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Tambah Milestone Baru</h2>
                    <button @click="isMilestoneModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitMilestone" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Milestone <span class="text-rose-500">*</span></label>
                        <input v-model="milestoneForm.title" type="text" required placeholder="Contoh: Fabrikasi 5 Unit PCB v1.0" class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fase Terkait</label>
                            <select v-model="milestoneForm.phase_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="">(Pilih Fase)</option>
                                <option v-for="p in project.phases" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Target Selesai</label>
                            <input v-model="milestoneForm.due_date" type="date" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Definisi Selesai (Definition of Done)</label>
                        <textarea v-model="milestoneForm.definition_of_done" rows="2" placeholder="Kriteria jelas kapan milestone ini dianggap berhasil..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isMilestoneModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="milestoneForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Simpan Milestone</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 5. MODAL EDIT MILESTONE -->
        <div v-if="isEditMilestoneModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Edit Milestone</h2>
                    <button @click="isEditMilestoneModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitEditMilestone" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Milestone <span class="text-rose-500">*</span></label>
                        <input v-model="editMilestoneForm.title" type="text" required class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fase Terkait</label>
                            <select v-model="editMilestoneForm.phase_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="">(Pilih Fase)</option>
                                <option v-for="p in project.phases" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Status</label>
                            <select v-model="editMilestoneForm.status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="pending">Belum Dimulai (Pending)</option>
                                <option value="in_progress">Sedang Berjalan (In Progress)</option>
                                <option value="completed">Selesai (Completed)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Target Selesai</label>
                        <input v-model="editMilestoneForm.due_date" type="date" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Definisi Selesai (DoD)</label>
                        <textarea v-model="editMilestoneForm.definition_of_done" rows="2" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isEditMilestoneModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="editMilestoneForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Perbarui Milestone</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 6. MODAL TAMBAH TUGAS -->
        <div v-if="isTaskModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Tambah Tugas Baru</h2>
                    <button @click="isTaskModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitTask" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Tugas <span class="text-rose-500">*</span></label>
                        <input v-model="taskForm.title" type="text" required placeholder="Contoh: Evaluasi confusion matrix" class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kaitkan ke Milestone</label>
                        <select v-model="taskForm.milestone_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                            <option value="">(Tanpa Milestone)</option>
                            <option v-for="m in project.milestones" :key="m.id" :value="m.id">{{ m.title }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Prioritas</label>
                            <select v-model="taskForm.priority" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="low">Rendah (Low)</option>
                                <option value="medium">Sedang (Medium)</option>
                                <option value="high">Tinggi (High)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tenggat Waktu</label>
                            <input v-model="taskForm.due_date" type="date" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isTaskModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="taskForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Simpan Tugas</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 7. MODAL EDIT TUGAS -->
        <div v-if="isEditTaskModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Edit Tugas</h2>
                    <button @click="isEditTaskModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitEditTask" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Tugas <span class="text-rose-500">*</span></label>
                        <input v-model="editTaskForm.title" type="text" required class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kaitkan ke Milestone</label>
                        <select v-model="editTaskForm.milestone_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                            <option value="">(Tanpa Milestone)</option>
                            <option v-for="m in project.milestones" :key="m.id" :value="m.id">{{ m.title }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Status</label>
                            <select v-model="editTaskForm.status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="todo">Belum (To Do)</option>
                                <option value="in_progress">Sedang Dikerjakan</option>
                                <option value="done">Selesai (Done)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Prioritas</label>
                            <select v-model="editTaskForm.priority" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="low">Rendah (Low)</option>
                                <option value="medium">Sedang (Medium)</option>
                                <option value="high">Tinggi (High)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tenggat Waktu</label>
                        <input v-model="editTaskForm.due_date" type="date" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isEditTaskModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="editTaskForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Perbarui Tugas</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 8. MODAL CATAT LOGBOOK CEPAT -->
        <div v-if="isJournalModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-xl w-full p-5 sm:p-8 shadow-2xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">Catat Aktivitas Logbook</h2>
                        <p class="text-xs text-slate-500">Pencatatan cepat bukti progres (&lt; 2 menit).</p>
                    </div>
                    <button @click="isJournalModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>

                <form @submit.prevent="submitJournal" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Kegiatan <span class="text-rose-500">*</span></label>
                        <input
                            v-model="journalForm.title"
                            type="text"
                            required
                            placeholder="Contoh: Pengujian Akurasi Model & Kalibrasi Sensor"
                            class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input
                                v-model="journalForm.date"
                                type="date"
                                required
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fase Terkait</label>
                            <select v-model="journalForm.phase_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="">(Pilih Fase)</option>
                                <option v-for="p in project.phases" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Uraian Aktivitas <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="journalForm.activity"
                            rows="3"
                            required
                            placeholder="Apa yang dikerjakan hari ini..."
                            class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Hasil / Temuan</label>
                            <textarea
                                v-model="journalForm.results"
                                rows="2"
                                placeholder="Temuan positif atau data..."
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Hambatan / Kendala</label>
                            <textarea
                                v-model="journalForm.challenges"
                                rows="2"
                                placeholder="Kendala yang dihadapi..."
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Lampiran Foto Bukti / File</label>
                        <input
                            type="file"
                            multiple
                            @change="handleJournalFileUpload"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100"
                        />
                    </div>

                    <div class="flex items-center space-x-2 pt-1">
                        <input type="checkbox" v-model="journalForm.is_public" id="j_public" class="rounded text-emerald-600 focus:ring-emerald-500" />
                        <label for="j_public" class="text-xs font-medium text-slate-700">Izinkan tampil pada tautan publik proyek</label>
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
                        <button type="button" @click="isJournalModalOpen = false" class="px-4 py-2 text-slate-600 rounded-xl hover:bg-slate-100">Batal</button>
                        <button type="submit" :disabled="journalForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">
                            {{ journalForm.processing ? 'Menyimpan...' : 'Simpan Logbook' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 9. MODAL EDIT LOGBOOK -->
        <div v-if="isEditJournalModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-xl w-full p-5 sm:p-8 shadow-2xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Edit Catatan Logbook</h2>
                    <button @click="isEditJournalModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>

                <form @submit.prevent="submitEditJournal" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Kegiatan <span class="text-rose-500">*</span></label>
                        <input
                            v-model="editJournalForm.title"
                            type="text"
                            required
                            class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input
                                v-model="editJournalForm.date"
                                type="date"
                                required
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fase Terkait</label>
                            <select v-model="editJournalForm.phase_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="">(Pilih Fase)</option>
                                <option v-for="p in project.phases" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Uraian Aktivitas <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="editJournalForm.activity"
                            rows="4"
                            required
                            class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Hasil / Temuan</label>
                            <textarea
                                v-model="editJournalForm.results"
                                rows="2"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Hambatan / Kendala</label>
                            <textarea
                                v-model="editJournalForm.challenges"
                                rows="2"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-1">
                        <input type="checkbox" v-model="editJournalForm.is_public" id="edit_j_public" class="rounded text-emerald-600 focus:ring-emerald-500" />
                        <label for="edit_j_public" class="text-xs font-medium text-slate-700">Izinkan tampil pada tautan publik proyek</label>
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
                        <button type="button" @click="isEditJournalModalOpen = false" class="px-4 py-2 text-slate-600 rounded-xl hover:bg-slate-100">Batal</button>
                        <button type="submit" :disabled="editJournalForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">
                            Perbarui Logbook
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 10. MODAL TAMBAH ITEM RAB -->
        <div v-if="isBudgetItemModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Tambah Item Rencana Anggaran (RAB)</h2>
                    <button @click="isBudgetItemModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitBudgetItem" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Item <span class="text-rose-500">*</span></label>
                        <input v-model="budgetItemForm.item_name" type="text" required placeholder="Contoh: Modul ESP32-C3 & Sensor Soil" class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                            <select v-model="budgetItemForm.category" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="Alat & Bahan">Alat & Bahan</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Jasa & Publikasi">Jasa & Publikasi</option>
                                <option value="Transportasi & Lapangan">Transportasi & Lapangan</option>
                                <option value="Pendaftaran Lomba">Pendaftaran Lomba</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Status Pengadaan</label>
                            <select v-model="budgetItemForm.procurement_status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="planned">Direncanakan (Planned)</option>
                                <option value="to_purchase">Siap Beli (To Purchase)</option>
                                <option value="ordered">Dipesan (Ordered)</option>
                                <option value="received">Diterima (Received)</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah</label>
                            <input v-model.number="budgetItemForm.qty" type="number" min="1" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Satuan</label>
                            <input v-model="budgetItemForm.unit" type="text" placeholder="unit/set" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                            <input v-model.number="budgetItemForm.estimated_unit_price" type="number" min="0" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isBudgetItemModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="budgetItemForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Simpan Item</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 11. MODAL EDIT ITEM RAB -->
        <div v-if="isEditBudgetItemModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Edit Item RAB</h2>
                    <button @click="isEditBudgetItemModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitEditBudgetItem" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Item <span class="text-rose-500">*</span></label>
                        <input v-model="editBudgetItemForm.item_name" type="text" required class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                            <select v-model="editBudgetItemForm.category" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="Alat & Bahan">Alat & Bahan</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Jasa & Publikasi">Jasa & Publikasi</option>
                                <option value="Transportasi & Lapangan">Transportasi & Lapangan</option>
                                <option value="Pendaftaran Lomba">Pendaftaran Lomba</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Status Pengadaan</label>
                            <select v-model="editBudgetItemForm.procurement_status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="planned">Direncanakan (Planned)</option>
                                <option value="to_purchase">Siap Beli (To Purchase)</option>
                                <option value="ordered">Dipesan (Ordered)</option>
                                <option value="received">Diterima (Received)</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah</label>
                            <input v-model.number="editBudgetItemForm.qty" type="number" min="1" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Satuan</label>
                            <input v-model="editBudgetItemForm.unit" type="text" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                            <input v-model.number="editBudgetItemForm.estimated_unit_price" type="number" min="0" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isEditBudgetItemModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="editBudgetItemForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Perbarui Item</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 12. MODAL CATAT BELANJA (Expense) -->
        <div v-if="isExpenseModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Catat Belanja / Pengeluaran Aktual</h2>
                    <button @click="isExpenseModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitExpense" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Deskripsi Pengeluaran <span class="text-rose-500">*</span></label>
                        <input v-model="expenseForm.description" type="text" required placeholder="Contoh: Pembelian komponen resistor & sensor" class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input v-model="expenseForm.date" type="date" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah Realisasi (Rp)</label>
                            <input v-model.number="expenseForm.amount" type="number" min="0" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                            <select v-model="expenseForm.category" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="Alat & Bahan">Alat & Bahan</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Jasa & Publikasi">Jasa & Publikasi</option>
                                <option value="Transportasi & Lapangan">Transportasi & Lapangan</option>
                                <option value="Pendaftaran Lomba">Pendaftaran Lomba</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Item RAB Terkait</label>
                            <select v-model="expenseForm.budget_item_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="">(Non-RAB / Langsung)</option>
                                <option v-for="b in project.budget_items" :key="b.id" :value="b.id">{{ b.item_name }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan / No. Bukti Nota</label>
                        <textarea v-model="expenseForm.notes" rows="2" placeholder="Nomor kuitansi, toko pembelian..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isExpenseModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="expenseForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Simpan Belanja</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 13. MODAL EDIT BELANJA (Expense) -->
        <div v-if="isEditExpenseModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Edit Catatan Belanja</h2>
                    <button @click="isEditExpenseModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitEditExpense" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Deskripsi Pengeluaran <span class="text-rose-500">*</span></label>
                        <input v-model="editExpenseForm.description" type="text" required class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input v-model="editExpenseForm.date" type="date" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah Realisasi (Rp)</label>
                            <input v-model.number="editExpenseForm.amount" type="number" min="0" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                            <select v-model="editExpenseForm.category" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="Alat & Bahan">Alat & Bahan</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Jasa & Publikasi">Jasa & Publikasi</option>
                                <option value="Transportasi & Lapangan">Transportasi & Lapangan</option>
                                <option value="Pendaftaran Lomba">Pendaftaran Lomba</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Item RAB Terkait</label>
                            <select v-model="editExpenseForm.budget_item_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="">(Non-RAB / Langsung)</option>
                                <option v-for="b in project.budget_items" :key="b.id" :value="b.id">{{ b.item_name }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan / No. Bukti Nota</label>
                        <textarea v-model="editExpenseForm.notes" rows="2" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isEditExpenseModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="editExpenseForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Perbarui Belanja</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 14. MODAL TAMBAH LUARAN -->
        <div v-if="isOutputModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Tambah Target Luaran Proyek</h2>
                    <button @click="isOutputModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitOutput" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Luaran <span class="text-rose-500">*</span></label>
                        <input v-model="outputForm.title" type="text" required placeholder="Contoh: Artikel Manuskrip Q1 / Juara 1 Nasional" class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipe Luaran</label>
                            <select v-model="outputForm.output_type" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="paper">Artikel / Paper Jurnal</option>
                                <option value="prototype">Prototipe / Produk</option>
                                <option value="dataset">Dataset Terbuka</option>
                                <option value="patent">HKI / Paten</option>
                                <option value="book_module">Buku / Modul</option>
                                <option value="impact_report">Laporan Dampak / Prestasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Status</label>
                            <select v-model="outputForm.status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="idea">Ide / Outline</option>
                                <option value="drafting">Drafting</option>
                                <option value="review">Review Internal</option>
                                <option value="submitted">Submitted</option>
                                <option value="published_done">Selesai / Terbit / Juara</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Target Tanggal</label>
                            <input v-model="outputForm.target_date" type="date" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Venue / Penyelenggara</label>
                            <input v-model="outputForm.venue_or_journal" type="text" placeholder="IEEE / Kemendikbud / UI" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">DOI / Tautan Luaran</label>
                        <input v-model="outputForm.doi_or_url" type="url" placeholder="https://..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                        <textarea v-model="outputForm.notes" rows="2" placeholder="Catatan luaran..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isOutputModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="outputForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Simpan Luaran</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 15. MODAL EDIT LUARAN -->
        <div v-if="isEditOutputModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Edit Target Luaran</h2>
                    <button @click="isEditOutputModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitEditOutput" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Luaran <span class="text-rose-500">*</span></label>
                        <input v-model="editOutputForm.title" type="text" required class="w-full text-xs sm:text-sm px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipe Luaran</label>
                            <select v-model="editOutputForm.output_type" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="paper">Artikel / Paper Jurnal</option>
                                <option value="prototype">Prototipe / Produk</option>
                                <option value="dataset">Dataset Terbuka</option>
                                <option value="patent">HKI / Paten</option>
                                <option value="book_module">Buku / Modul</option>
                                <option value="impact_report">Laporan Dampak / Prestasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Status</label>
                            <select v-model="editOutputForm.status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="idea">Ide / Outline</option>
                                <option value="drafting">Drafting</option>
                                <option value="review">Review Internal</option>
                                <option value="submitted">Submitted</option>
                                <option value="published_done">Selesai / Terbit / Juara</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Target Tanggal</label>
                            <input v-model="editOutputForm.target_date" type="date" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Venue / Penyelenggara</label>
                            <input v-model="editOutputForm.venue_or_journal" type="text" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">DOI / Tautan Luaran</label>
                        <input v-model="editOutputForm.doi_or_url" type="url" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                        <textarea v-model="editOutputForm.notes" rows="2" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="isEditOutputModalOpen = false" class="px-4 py-2 text-slate-600">Batal</button>
                        <button type="submit" :disabled="editOutputForm.processing" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs">Perbarui Luaran</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

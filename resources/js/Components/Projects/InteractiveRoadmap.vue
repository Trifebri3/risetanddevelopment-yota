<script setup>
import { ref, computed } from 'vue';
import AppIcon from '@/Components/AppIcon.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    project: Object,
    isOwner: Boolean,
});

const emit = defineEmits([
    'openTaskDetail',
    'openCreatePhase',
    'openCreateMilestone',
    'openCreateTask',
    'editPhase',
    'deletePhase',
    'editMilestone',
    'deleteMilestone',
    'deleteTask',
]);

// Expanded Phases State
const expandedPhases = ref({});

// Auto-expand all phases initially
if (props.project?.phases) {
    props.project.phases.forEach(p => {
        expandedPhases.value[p.id] = true;
    });
}

const togglePhaseAccordion = (phaseId) => {
    expandedPhases.value[phaseId] = !expandedPhases.value[phaseId];
};

const expandAll = () => {
    props.project?.phases?.forEach(p => {
        expandedPhases.value[p.id] = true;
    });
};

const collapseAll = () => {
    props.project?.phases?.forEach(p => {
        expandedPhases.value[p.id] = false;
    });
};

// Calculate stage progress
const getPhaseProgress = (phase) => {
    const milestoneIds = (props.project?.milestones || [])
        .filter(m => m.phase_id === phase.id)
        .map(m => m.id);

    const tasksInPhase = (props.project?.tasks || [])
        .filter(t => milestoneIds.includes(t.milestone_id));

    if (tasksInPhase.length === 0) {
        const milestones = props.project?.milestones?.filter(m => m.phase_id === phase.id) || [];
        if (milestones.length === 0) return 0;
        const done = milestones.filter(m => m.status === 'completed').length;
        return Math.round((done / milestones.length) * 100);
    }

    const totalWeight = tasksInPhase.reduce((acc, t) => acc + (t.weight || 1), 0);
    const doneWeight = tasksInPhase.filter(t => t.status === 'done').reduce((acc, t) => acc + (t.weight || 1), 0);

    return totalWeight > 0 ? Math.round((doneWeight / totalWeight) * 100) : 0;
};

// Quick Toggle Task Status
const toggleTask = (task, event) => {
    event.stopPropagation();
    router.post(route('tasks.toggle', task.id), {}, { preserveScroll: true });
};

// Complete Milestone
const completeMilestone = (m, event) => {
    event.stopPropagation();
    router.put(route('milestones.update', m.id), {
        title: m.title,
        phase_id: m.phase_id,
        due_date: m.due_date,
        definition_of_done: m.definition_of_done,
        is_public: m.is_public,
        status: m.status === 'completed' ? 'in_progress' : 'completed',
    }, { preserveScroll: true });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header & Action Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center space-x-2">
                    <AppIcon name="target" :size="18" class="text-emerald-600" />
                    <span>Roadmap Operasional & Hierarki Tugas</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Buka setiap tahapan untuk melihat panduan langkah pengerjaan, target luaran, checklist verifikasi, dan bukti kerja.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-xl p-1 bg-slate-100 border border-slate-200 text-xs mr-2">
                    <button
                        @click="expandAll"
                        class="px-2.5 py-1 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-white transition-all text-[11px] font-semibold"
                    >
                        Buka Semua
                    </button>
                    <button
                        @click="collapseAll"
                        class="px-2.5 py-1 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-white transition-all text-[11px] font-semibold"
                    >
                        Tutup Semua
                    </button>
                </div>

                <button
                    @click="emit('openCreatePhase')"
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center space-x-1.5 active:scale-95 transition-all"
                >
                    <AppIcon name="layers" :size="13" />
                    <span>+ Tahap Baru</span>
                </button>
                <button
                    @click="emit('openCreateMilestone', null)"
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center space-x-1.5 active:scale-95 transition-all"
                >
                    <AppIcon name="target" :size="13" />
                    <span>+ Milestone</span>
                </button>
                <button
                    @click="emit('openCreateTask', null)"
                    class="px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs flex items-center space-x-1.5 active:scale-95 transition-all"
                >
                    <AppIcon name="plus" :size="13" />
                    <span>+ Tugas Baru</span>
                </button>
            </div>
        </div>

        <!-- Hierarchical Stages Container -->
        <div class="space-y-4">
            <div
                v-if="!project.phases || project.phases.length === 0"
                class="p-12 text-center bg-white rounded-3xl border border-dashed border-slate-300 text-slate-400 space-y-3"
            >
                <AppIcon name="target" :size="36" class="mx-auto text-slate-300" />
                <h3 class="text-sm font-bold text-slate-700">Belum ada tahapan dalam roadmap ini</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Mulai dengan menambahkan tahap pertama atau terapkan template metodologi siap pakai.
                </p>
                <button
                    @click="emit('openCreatePhase')"
                    class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs"
                >
                    + Buat Tahap Pertama
                </button>
            </div>

            <div
                v-for="(phase, pIndex) in project.phases"
                :key="phase.id"
                class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden transition-all duration-200"
            >
                <!-- Stage Header Bar (Collapsible Accordion Trigger) -->
                <div
                    @click="togglePhaseAccordion(phase.id)"
                    class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer bg-gradient-to-r from-slate-50/90 via-white to-white hover:bg-slate-50/80 transition-colors select-none"
                >
                    <div class="flex items-center space-x-3.5 flex-1 min-w-0">
                        <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0 shadow-xs">
                            {{ String(pIndex + 1).padStart(2, '0') }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 truncate">
                                    {{ phase.name }}
                                </h3>
                                <span class="text-[11px] font-semibold text-slate-400">
                                    ({{ project.tasks?.filter(t => (project.milestones?.filter(m => m.phase_id === phase.id) || []).map(m => m.id).includes(t.milestone_id)).length || 0 }} tugas)
                                </span>
                            </div>
                            <div class="flex items-center space-x-3 text-xs text-slate-500 mt-1">
                                <!-- Stage Progress Bar -->
                                <div class="w-24 sm:w-36 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div
                                        class="bg-emerald-600 h-1.5 rounded-full transition-all"
                                        :style="{ width: `${getPhaseProgress(phase)}%` }"
                                    ></div>
                                </div>
                                <span class="font-bold text-slate-700 text-[11px]">{{ getPhaseProgress(phase) }}% tuntas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Controls for Stage -->
                    <div class="flex items-center space-x-2 shrink-0 self-end sm:self-center" @click.stop>
                        <button
                            @click="emit('openCreateMilestone', phase.id)"
                            class="px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-colors"
                            title="Tambah milestone di tahap ini"
                        >
                            + Milestone
                        </button>
                        <button
                            @click="emit('editPhase', phase)"
                            class="px-2 py-1 text-slate-400 hover:text-emerald-700 hover:bg-slate-100 rounded-lg text-xs"
                            title="Ubah nama tahap"
                        >
                            <AppIcon name="file-text" :size="13" />
                        </button>
                        <button
                            @click="emit('deletePhase', phase)"
                            class="px-2 py-1 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-lg text-xs"
                            title="Hapus tahap"
                        >
                            <AppIcon name="trash" :size="13" />
                        </button>
                        <div class="w-px h-4 bg-slate-200 mx-1"></div>
                        <button
                            @click="togglePhaseAccordion(phase.id)"
                            class="p-1 rounded-lg text-slate-400 hover:text-slate-700"
                        >
                            <span :class="expandedPhases[phase.id] ? 'rotate-90' : ''" class="inline-block transition-transform duration-200">
                                ▶
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Stage Body (Milestones & Tasks) -->
                <div v-show="expandedPhases[phase.id]" class="p-4 sm:p-6 pt-2 border-t border-slate-100 space-y-4 bg-slate-50/30">
                    <div
                        v-if="(project.milestones || []).filter(m => m.phase_id === phase.id).length === 0"
                        class="text-xs text-slate-400 py-4 text-center border border-dashed border-slate-200 rounded-2xl bg-white"
                    >
                        Belum ada milestone pada tahap ini.
                        <button
                            @click="emit('openCreateMilestone', phase.id)"
                            class="text-emerald-600 font-bold hover:underline ml-1"
                        >
                            Tambah Milestone
                        </button>
                    </div>

                    <!-- Milestones Loop -->
                    <div
                        v-for="milestone in (project.milestones || []).filter(m => m.phase_id === phase.id)"
                        :key="milestone.id"
                        class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3.5"
                    >
                        <!-- Milestone Header -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start space-x-3">
                                <button
                                    @click="completeMilestone(milestone, $event)"
                                    :class="milestone.status === 'completed' ? 'bg-emerald-600 text-white' : 'border-2 border-slate-300 bg-white hover:border-emerald-600'"
                                    class="w-6 h-6 rounded-lg flex items-center justify-center mt-0.5 transition-all shrink-0 cursor-pointer"
                                    title="Tandai Milestone Selesai (DoD Terpenuhi)"
                                >
                                    <AppIcon v-if="milestone.status === 'completed'" name="check" :size="13" />
                                </button>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h4
                                            :class="milestone.status === 'completed' ? 'line-through text-slate-400 font-medium' : 'text-slate-900 font-bold'"
                                            class="text-xs sm:text-sm"
                                        >
                                            {{ milestone.title }}
                                        </h4>
                                        <span
                                            v-if="milestone.status === 'completed'"
                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800"
                                        >
                                            Verified
                                        </span>
                                    </div>
                                    <p v-if="milestone.definition_of_done" class="text-xs text-slate-500 mt-1">
                                        <span class="font-semibold text-slate-600">Definition of Done (DoD):</span>
                                        {{ milestone.definition_of_done }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2 shrink-0 text-xs">
                                <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                    {{ milestone.due_date ? new Date(milestone.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : 'No deadline' }}
                                </span>
                                <button
                                    @click="emit('openCreateTask', milestone.id)"
                                    class="px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors"
                                >
                                    + Tugas
                                </button>
                                <button
                                    @click="emit('editMilestone', milestone)"
                                    class="text-slate-400 hover:text-emerald-700 p-1 rounded"
                                    title="Edit milestone"
                                >
                                    <AppIcon name="file-text" :size="13" />
                                </button>
                                <button
                                    @click="emit('deleteMilestone', milestone)"
                                    class="text-slate-300 hover:text-rose-600 p-1 rounded"
                                    title="Hapus milestone"
                                >
                                    <AppIcon name="trash" :size="13" />
                                </button>
                            </div>
                        </div>

                        <!-- Tasks Under This Milestone -->
                        <div class="pl-4 sm:pl-9 space-y-2 border-t border-slate-100 pt-3">
                            <div
                                v-if="(project.tasks || []).filter(t => t.milestone_id === milestone.id).length === 0"
                                class="text-xs text-slate-400 py-3 text-center border border-dashed border-slate-200 rounded-xl bg-slate-50/50"
                            >
                                Belum ada tugas di milestone ini.
                                <button
                                    @click="emit('openCreateTask', milestone.id)"
                                    class="text-emerald-600 font-bold hover:underline ml-1"
                                >
                                    Tambah tugas sekarang
                                </button>
                            </div>

                            <div
                                v-for="task in (project.tasks || []).filter(t => t.milestone_id === milestone.id)"
                                :key="task.id"
                                @click="emit('openTaskDetail', task)"
                                class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/70 hover:border-emerald-400 hover:bg-white hover:shadow-sm transition-all cursor-pointer group gap-2"
                            >
                                <!-- Left: Checkbox + Title + Badges -->
                                <div class="flex items-center space-x-3 flex-1 min-w-0 pr-2">
                                    <button
                                        @click="toggleTask(task, $event)"
                                        :class="task.status === 'done' ? 'bg-emerald-600 text-white' : 'border-2 border-slate-300 bg-white hover:border-emerald-600'"
                                        class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 transition-all cursor-pointer active:scale-90"
                                        title="Ubah status tugas"
                                    >
                                        <AppIcon v-if="task.status === 'done'" name="check" :size="12" />
                                    </button>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                :class="task.status === 'done' ? 'line-through text-slate-400 font-normal' : 'text-slate-900 font-semibold'"
                                                class="text-xs sm:text-sm truncate"
                                            >
                                                {{ task.title }}
                                            </span>

                                            <!-- PIC Pill -->
                                            <span
                                                v-if="task.assigned_user"
                                                class="hidden sm:inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200"
                                                title="Penanggung Jawab"
                                            >
                                                PIC: {{ task.assigned_user.name.split(' ')[0] }}
                                            </span>
                                        </div>

                                        <!-- Target Deliverable Summary line -->
                                        <p v-if="task.output_deliverable" class="text-[11px] text-emerald-800/80 truncate mt-0.5 flex items-center space-x-1">
                                            <span class="font-semibold text-emerald-900">Luaran:</span>
                                            <span>{{ task.output_deliverable }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Right: Operational Badges & Actions -->
                                <div class="flex items-center space-x-2 shrink-0 self-end sm:self-center text-xs" @click.stop>
                                    <!-- Checklist Badge -->
                                    <span
                                        v-if="task.checklists && task.checklists.length > 0"
                                        :class="task.checklists.every(c => c.is_completed) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'"
                                        class="text-[10px] font-bold px-2 py-0.5 rounded-md flex items-center space-x-1"
                                        title="Kriteria selesai terpenuhi"
                                    >
                                        <span>✓</span>
                                        <span>{{ task.checklists.filter(c => c.is_completed).length }}/{{ task.checklists.length }}</span>
                                    </span>

                                    <!-- Deliverables / Attachments Badge -->
                                    <span
                                        v-if="task.attachments && task.attachments.length > 0"
                                        class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 flex items-center space-x-1"
                                        title="Lampiran berkas"
                                    >
                                        <AppIcon name="paperclip" :size="10" />
                                        <span>{{ task.attachments.length }}</span>
                                    </span>

                                    <!-- Priority Badge -->
                                    <span
                                        :class="{
                                            'bg-rose-50 text-rose-700 border-rose-200': task.priority === 'high',
                                            'bg-amber-50 text-amber-700 border-amber-200': task.priority === 'medium',
                                            'bg-slate-100 text-slate-600 border-slate-200': task.priority === 'low'
                                        }"
                                        class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md border"
                                    >
                                        {{ task.priority }}
                                    </span>

                                    <!-- Clickable Detail Arrow -->
                                    <button
                                        @click="emit('openTaskDetail', task)"
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-all shadow-2xs"
                                        title="Buka Halaman Detail Tugas"
                                    >
                                        Buka Detail &rarr;
                                    </button>

                                    <button
                                        @click="emit('deleteTask', task)"
                                        class="opacity-0 group-hover:opacity-100 text-slate-300 hover:text-rose-600 p-1 rounded transition-opacity"
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
    </div>
</template>


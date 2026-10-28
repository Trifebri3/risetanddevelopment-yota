<script setup>
import { ref, watch, computed } from 'vue';
import AppIcon from '@/Components/AppIcon.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    isOpen: Boolean,
    taskId: [Number, String],
    project: Object,
});

const emit = defineEmits(['close', 'taskUpdated']);

const activeTab = ref('instructions'); // 'instructions', 'checklists', 'attachments', 'extension', 'reviews', 'comments'
const isLoading = ref(false);
const taskData = ref(null);
const isEditingInfo = ref(false);

// Edit Task Form State
const editForm = ref({
    title: '',
    description: '',
    instructions: '',
    output_deliverable: '',
    assigned_to: '',
    priority: 'medium',
    status: 'todo',
    start_date: '',
    due_date: '',
    weight: 1,
    stage_gate_status: 'none',
    extension_fields: {},
});

// New Checklist Item
const newChecklistText = ref('');
const isSubmittingChecklist = ref(false);

// New Attachment Form
const attachmentForm = ref({
    title: '',
    attachment_type: 'deliverable', // 'reference' or 'deliverable'
    link_url: '',
    file: null,
});
const fileInputRef = ref(null);
const isSubmittingAttachment = ref(false);

// New Review Form
const reviewForm = ref({
    status: 'approved',
    comment: '',
    rating: 5,
});
const isSubmittingReview = ref(false);

// New Comment Form
const newComment = ref('');
const isSubmittingComment = ref(false);

// Fetch Task Detail
const fetchTaskDetail = async () => {
    if (!props.taskId) return;
    isLoading.value = true;
    try {
        const response = await window.axios.get(route('tasks.detail.show', props.taskId));
        taskData.value = response.data.task;

        // Populate edit form
        editForm.value = {
            title: taskData.value.title || '',
            description: taskData.value.description || '',
            instructions: taskData.value.instructions || '',
            output_deliverable: taskData.value.output_deliverable || '',
            assigned_to: taskData.value.assigned_to || '',
            priority: taskData.value.priority || 'medium',
            status: taskData.value.status || 'todo',
            start_date: taskData.value.start_date || '',
            due_date: taskData.value.due_date || '',
            weight: taskData.value.weight || 1,
            stage_gate_status: taskData.value.stage_gate_status || 'none',
            extension_fields: taskData.value.extension_fields || {},
        };
    } catch (error) {
        console.error('Error fetching task details:', error);
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.taskId, (newVal) => {
    if (newVal && props.isOpen) {
        fetchTaskDetail();
    }
}, { immediate: true });

watch(() => props.isOpen, (newVal) => {
    if (newVal && props.taskId) {
        fetchTaskDetail();
    }
});

// Update Task Info
const saveTaskInfo = async () => {
    if (!props.taskId) return;
    isLoading.value = true;
    try {
        await window.axios.put(route('tasks.detail.update', props.taskId), editForm.value);
        isEditingInfo.value = false;
        await fetchTaskDetail();
        emit('taskUpdated');
    } catch (error) {
        alert('Gagal menyimpan perubahan tugas.');
    } finally {
        isLoading.value = false;
    }
};

// Toggle Task Status
const updateTaskStatus = async (newStatus) => {
    editForm.value.status = newStatus;
    await saveTaskInfo();
};

// Checklists Management
const addChecklistItem = async () => {
    if (!newChecklistText.value.trim() || !props.taskId) return;
    isSubmittingChecklist.value = true;
    try {
        await window.axios.post(route('tasks.checklists.store', props.taskId), {
            item: newChecklistText.value.trim(),
        });
        newChecklistText.value = '';
        await fetchTaskDetail();
        emit('taskUpdated');
    } catch (error) {
        alert('Gagal menambahkan kriteria selesai.');
    } finally {
        isSubmittingChecklist.value = false;
    }
};

const toggleChecklistItem = async (item) => {
    try {
        item.is_completed = !item.is_completed;
        await window.axios.post(route('checklists.toggle', item.id));
        await fetchTaskDetail();
        emit('taskUpdated');
    } catch (error) {
        item.is_completed = !item.is_completed;
        alert('Gagal memperbarui status checklist.');
    }
};

const deleteChecklistItem = async (item) => {
    if (!confirm('Hapus kriteria ini?')) return;
    try {
        await window.axios.delete(route('checklists.destroy', item.id));
        await fetchTaskDetail();
        emit('taskUpdated');
    } catch (error) {
        alert('Gagal menghapus kriteria.');
    }
};

// Attachment Management
const handleFileUpload = (e) => {
    attachmentForm.value.file = e.target.files[0] || null;
};

const submitAttachment = async () => {
    if (!attachmentForm.value.title.trim()) {
        alert('Judul berkas wajib diisi.');
        return;
    }
    if (!attachmentForm.value.file && !attachmentForm.value.link_url.trim()) {
        alert('Wajib mengunggah file atau mencantumkan tautan URL.');
        return;
    }

    isSubmittingAttachment.value = true;
    const formData = new FormData();
    formData.append('title', attachmentForm.value.title);
    formData.append('attachment_type', attachmentForm.value.attachment_type);
    if (attachmentForm.value.link_url) formData.append('link_url', attachmentForm.value.link_url);
    if (attachmentForm.value.file) formData.append('file', attachmentForm.value.file);

    try {
        await window.axios.post(route('tasks.attachments.store', props.taskId), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        attachmentForm.value = {
            title: '',
            attachment_type: 'deliverable',
            link_url: '',
            file: null,
        };
        if (fileInputRef.value) fileInputRef.value.value = '';
        await fetchTaskDetail();
        emit('taskUpdated');
    } catch (error) {
        alert('Gagal mengunggah lampiran.');
    } finally {
        isSubmittingAttachment.value = false;
    }
};

const deleteAttachment = async (att) => {
    if (!confirm(`Hapus lampiran "${att.title}"?`)) return;
    try {
        await window.axios.delete(route('attachments.destroy', att.id));
        await fetchTaskDetail();
        emit('taskUpdated');
    } catch (error) {
        alert('Gagal menghapus berkas.');
    }
};

// Review / Approval Management
const submitReview = async () => {
    if (!props.taskId) return;
    isSubmittingReview.value = true;
    try {
        await window.axios.post(route('tasks.reviews.store', props.taskId), reviewForm.value);
        reviewForm.value.comment = '';
        await fetchTaskDetail();
        emit('taskUpdated');
    } catch (error) {
        alert('Gagal mencatat review verifikasi.');
    } finally {
        isSubmittingReview.value = false;
    }
};

// Comment Management
const submitComment = async () => {
    if (!newComment.value.trim() || !props.taskId) return;
    isSubmittingComment.value = true;
    try {
        await window.axios.post(route('tasks.comments.store', props.taskId), {
            comment: newComment.value.trim(),
        });
        newComment.value = '';
        await fetchTaskDetail();
        emit('taskUpdated');
    } catch (error) {
        alert('Gagal mengirim komentar.');
    } finally {
        isSubmittingComment.value = false;
    }
};

// Computed Helpers
const references = computed(() => {
    return taskData.value?.attachments?.filter(a => a.attachment_type === 'reference') || [];
});

const deliverables = computed(() => {
    return taskData.value?.attachments?.filter(a => a.attachment_type === 'deliverable') || [];
});

const checklistProgress = computed(() => {
    const list = taskData.value?.checklists || [];
    const total = list.length;
    const completed = list.filter(i => i.is_completed).length;
    const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
    return { total, completed, percent };
});

const teamMembers = computed(() => {
    const list = [];
    if (props.project?.user) {
        list.push({ id: props.project.user.id, name: props.project.user.name + ' (Owner)' });
    }
    if (props.project?.members) {
        props.project.members.forEach(m => {
            if (m.user && m.user.id !== props.project.user?.id) {
                list.push({ id: m.user.id, name: m.user.name + ` (${m.role})` });
            }
        });
    }
    return list;
});
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop -->
        <div
            @click="emit('close')"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity animate-fade-in"
        ></div>

        <!-- Slide-over Drawer Panel -->
        <div class="fixed inset-y-0 right-0 max-w-2xl w-full bg-white shadow-2xl flex flex-col z-10 animate-slide-left">
            <!-- Drawer Header -->
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/80 flex items-start justify-between">
                <div class="space-y-1 pr-4 flex-1">
                    <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-700">
                        <span class="px-2 py-0.5 rounded bg-emerald-100/70 border border-emerald-200 uppercase tracking-wider text-[10px]">
                            {{ project?.mode }}
                        </span>
                        <span>&bull;</span>
                        <span class="text-slate-500 truncate max-w-xs">{{ taskData?.milestone?.title || 'Milestone' }}</span>
                    </div>

                    <h2 v-if="!isEditingInfo" class="text-lg font-bold text-slate-900 leading-snug">
                        {{ taskData?.title || 'Memuat tugas...' }}
                    </h2>
                    <input
                        v-else
                        v-model="editForm.title"
                        type="text"
                        class="w-full text-base font-bold rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 px-3 py-1.5"
                    />
                </div>

                <div class="flex items-center space-x-2 shrink-0">
                    <button
                        v-if="!isEditingInfo"
                        @click="isEditingInfo = true"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center space-x-1"
                    >
                        <AppIcon name="file-text" :size="13" />
                        <span>Edit Info</span>
                    </button>
                    <button
                        v-else
                        @click="saveTaskInfo"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs flex items-center space-x-1"
                    >
                        <AppIcon name="check" :size="13" />
                        <span>Simpan</span>
                    </button>

                    <button
                        @click="emit('close')"
                        class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors"
                        title="Tutup Panel"
                    >
                        <AppIcon name="x" :size="18" />
                    </button>
                </div>
            </div>

            <!-- Quick Metadata Bar -->
            <div class="px-6 py-3 bg-white border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <!-- Status Switcher -->
                <div class="flex items-center space-x-2">
                    <span class="font-medium text-slate-500">Status:</span>
                    <div class="inline-flex rounded-lg p-0.5 bg-slate-100 border border-slate-200">
                        <button
                            @click="updateTaskStatus('todo')"
                            :class="taskData?.status === 'todo' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                            class="px-2.5 py-1 rounded-md text-[11px] transition-all"
                        >
                            Todo
                        </button>
                        <button
                            @click="updateTaskStatus('in_progress')"
                            :class="taskData?.status === 'in_progress' ? 'bg-amber-500 text-white font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                            class="px-2.5 py-1 rounded-md text-[11px] transition-all"
                        >
                            In Progress
                        </button>
                        <button
                            @click="updateTaskStatus('done')"
                            :class="taskData?.status === 'done' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                            class="px-2.5 py-1 rounded-md text-[11px] transition-all"
                        >
                            ✓ Done
                        </button>
                    </div>
                </div>

                <!-- Priority & Review Gate -->
                <div class="flex items-center space-x-2">
                    <span
                        :class="{
                            'bg-rose-50 text-rose-700 border-rose-200': taskData?.priority === 'high',
                            'bg-amber-50 text-amber-700 border-amber-200': taskData?.priority === 'medium',
                            'bg-slate-50 text-slate-700 border-slate-200': taskData?.priority === 'low'
                        }"
                        class="px-2.5 py-1 rounded-lg border font-semibold text-[11px] uppercase tracking-wider"
                    >
                        Prioritas {{ taskData?.priority }}
                    </span>

                    <span
                        v-if="taskData?.stage_gate_status && taskData?.stage_gate_status !== 'none'"
                        :class="{
                            'bg-emerald-50 text-emerald-800 border-emerald-200': taskData.stage_gate_status === 'approved',
                            'bg-amber-50 text-amber-800 border-amber-200': taskData.stage_gate_status === 'pending_review',
                            'bg-rose-50 text-rose-800 border-rose-200': taskData.stage_gate_status === 'changes_requested'
                        }"
                        class="px-2.5 py-1 rounded-lg border font-bold text-[11px] flex items-center space-x-1"
                    >
                        <span>Gate:</span>
                        <span class="capitalize">{{ taskData.stage_gate_status.replace('_', ' ') }}</span>
                    </span>
                </div>
            </div>

            <!-- Drawer Tabs Navigation -->
            <div class="px-6 border-b border-slate-200 bg-white flex space-x-1 overflow-x-auto scrollbar-none">
                <button
                    @click="activeTab = 'instructions'"
                    :class="activeTab === 'instructions' ? 'border-emerald-600 text-emerald-700 font-bold bg-emerald-50/50' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3 text-xs border-b-2 font-medium shrink-0 flex items-center space-x-1.5 transition-colors"
                >
                    <AppIcon name="file-text" :size="13" />
                    <span>Instruksi & Luaran</span>
                </button>

                <button
                    @click="activeTab = 'checklists'"
                    :class="activeTab === 'checklists' ? 'border-emerald-600 text-emerald-700 font-bold bg-emerald-50/50' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3 text-xs border-b-2 font-medium shrink-0 flex items-center space-x-1.5 transition-colors"
                >
                    <AppIcon name="check" :size="13" />
                    <span>Kriteria Selesai ({{ checklistProgress.completed }}/{{ checklistProgress.total }})</span>
                </button>

                <button
                    @click="activeTab = 'attachments'"
                    :class="activeTab === 'attachments' ? 'border-emerald-600 text-emerald-700 font-bold bg-emerald-50/50' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3 text-xs border-b-2 font-medium shrink-0 flex items-center space-x-1.5 transition-colors"
                >
                    <AppIcon name="paperclip" :size="13" />
                    <span>Lampiran & Bukti ({{ taskData?.attachments?.length || 0 }})</span>
                </button>

                <button
                    @click="activeTab = 'extension'"
                    :class="activeTab === 'extension' ? 'border-emerald-600 text-emerald-700 font-bold bg-emerald-50/50' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3 text-xs border-b-2 font-medium shrink-0 flex items-center space-x-1.5 transition-colors"
                >
                    <AppIcon name="layers" :size="13" />
                    <span>Konteks Metodologi</span>
                </button>

                <button
                    @click="activeTab = 'reviews'"
                    :class="activeTab === 'reviews' ? 'border-emerald-600 text-emerald-700 font-bold bg-emerald-50/50' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3 text-xs border-b-2 font-medium shrink-0 flex items-center space-x-1.5 transition-colors"
                >
                    <AppIcon name="award" :size="13" />
                    <span>Review & Gate</span>
                </button>

                <button
                    @click="activeTab = 'comments'"
                    :class="activeTab === 'comments' ? 'border-emerald-600 text-emerald-700 font-bold bg-emerald-50/50' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3 text-xs border-b-2 font-medium shrink-0 flex items-center space-x-1.5 transition-colors"
                >
                    <AppIcon name="message-square" :size="13" />
                    <span>Diskusi ({{ taskData?.comments?.length || 0 }})</span>
                </button>
            </div>

            <!-- Drawer Content Body -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 space-y-6">
                <!-- Loading State -->
                <div v-if="isLoading" class="flex items-center justify-center py-12 text-slate-400 space-x-2">
                    <span class="animate-spin text-emerald-600 text-xl font-bold">↻</span>
                    <span class="text-xs">Memuat rincian tugas...</span>
                </div>

                <div v-else>
                    <!-- TAB 1: INSTRUKSI & LUARAN -->
                    <div v-if="activeTab === 'instructions'" class="space-y-5">
                        <!-- Target Luaran (Deliverable Box) -->
                        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 space-y-2">
                            <div class="flex items-center space-x-2 text-xs font-bold text-emerald-900">
                                <AppIcon name="target" :size="15" class="text-emerald-700" />
                                <span>Target Luaran Wajib (Deliverable)</span>
                            </div>
                            <div v-if="!isEditingInfo" class="text-sm text-emerald-950 font-medium bg-white/70 p-3 rounded-xl border border-emerald-100">
                                {{ taskData?.output_deliverable || 'Belum ada spesifikasi luaran wajib yang dicantumkan.' }}
                            </div>
                            <textarea
                                v-else
                                v-model="editForm.output_deliverable"
                                rows="2"
                                placeholder="Tentukan dokumen atau bukti nyata yang wajib dihasilkan..."
                                class="w-full text-xs rounded-xl border-emerald-300 focus:ring-emerald-500 focus:border-emerald-500 p-2.5"
                            ></textarea>
                        </div>

                        <!-- Step-by-Step Instructions -->
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                                    <AppIcon name="book-open" :size="14" class="text-emerald-600" />
                                    <span>Panduan Langkah Demi Langkah</span>
                                </h3>
                                <span class="text-[11px] text-slate-400">Instruksi Operasional Metodologi</span>
                            </div>

                            <div v-if="!isEditingInfo" class="prose prose-xs max-w-none text-slate-700 whitespace-pre-line text-xs sm:text-sm bg-slate-50 p-4 rounded-xl border border-slate-100">
                                {{ taskData?.instructions || 'Belum ada instruksi langkah pengerjaan khusus.' }}
                            </div>
                            <textarea
                                v-else
                                v-model="editForm.instructions"
                                rows="6"
                                placeholder="1. Lakukan pengumpulan data...\n2. Sintesis temuan...\n3. Buat dokumen laporan..."
                                class="w-full text-xs font-mono rounded-xl border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 p-3"
                            ></textarea>
                        </div>

                        <!-- General Description & Assignment -->
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                                Penanggung Jawab & Waktu Pelaksanaan
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div>
                                    <label class="block font-medium text-slate-600 mb-1">PIC (Penanggung Jawab)</label>
                                    <select
                                        v-if="isEditingInfo"
                                        v-model="editForm.assigned_to"
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    >
                                        <option value="">Belum Ditugaskan</option>
                                        <option v-for="user in teamMembers" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <div v-else class="font-semibold text-slate-800 bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center space-x-2">
                                        <AppIcon name="users" :size="13" class="text-emerald-600" />
                                        <span>{{ taskData?.assigned_user?.name || 'Belum Ditugaskan' }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 mb-1">Batas Waktu (Deadline)</label>
                                    <input
                                        v-if="isEditingInfo"
                                        v-model="editForm.due_date"
                                        type="date"
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    />
                                    <div v-else class="font-semibold text-slate-800 bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center space-x-2">
                                        <AppIcon name="calendar" :size="13" class="text-emerald-600" />
                                        <span>{{ taskData?.due_date ? new Date(taskData.due_date).toLocaleDateString('id-ID', { dateStyle: 'long' }) : 'Tidak ada batas waktu' }}</span>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-medium text-slate-600 mb-1">Deskripsi Tambahan</label>
                                    <p v-if="!isEditingInfo" class="text-slate-600 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">
                                        {{ taskData?.description || 'Tidak ada catatan deskripsi tambahan.' }}
                                    </p>
                                    <textarea
                                        v-else
                                        v-model="editForm.description"
                                        rows="2"
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: KRITERIA SELESAI (ACCEPTANCE CRITERIA) -->
                    <div v-if="activeTab === 'checklists'" class="space-y-5">
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                                        Kriteria Penyelesaian (Acceptance Criteria)
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Tugas dinyatakan layak tuntas apabila seluruh kriteria verifikasi terpenuhi.
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    {{ checklistProgress.completed }} / {{ checklistProgress.total }} Terpenuhi
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div
                                    class="bg-emerald-600 h-2.5 rounded-full transition-all duration-300"
                                    :style="{ width: `${checklistProgress.percent}%` }"
                                ></div>
                            </div>

                            <!-- Checklist Items -->
                            <div class="space-y-2 pt-2">
                                <div
                                    v-if="!taskData?.checklists || taskData?.checklists.length === 0"
                                    class="text-xs text-slate-400 text-center py-6 border border-dashed border-slate-200 rounded-xl"
                                >
                                    Belum ada kriteria selesai yang dibuat. Tambahkan kriteria di bawah.
                                </div>

                                <div
                                    v-for="item in taskData?.checklists"
                                    :key="item.id"
                                    class="flex items-center justify-between p-3 rounded-xl bg-slate-50/70 border border-slate-200/80 hover:bg-white transition-all group"
                                >
                                    <div class="flex items-center space-x-3 flex-1">
                                        <input
                                            type="checkbox"
                                            :checked="item.is_completed"
                                            @change="toggleChecklistItem(item)"
                                            class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer"
                                        />
                                        <span
                                            :class="item.is_completed ? 'line-through text-slate-400' : 'text-slate-800 font-medium'"
                                            class="text-xs sm:text-sm"
                                        >
                                            {{ item.item }}
                                        </span>
                                    </div>

                                    <button
                                        @click="deleteChecklistItem(item)"
                                        class="opacity-0 group-hover:opacity-100 p-1 text-slate-300 hover:text-rose-600 rounded transition-opacity"
                                        title="Hapus Kriteria"
                                    >
                                        <AppIcon name="trash" :size="13" />
                                    </button>
                                </div>
                            </div>

                            <!-- Add Checklist Form -->
                            <form @submit.prevent="addChecklistItem" class="flex gap-2 pt-2">
                                <input
                                    v-model="newChecklistText"
                                    type="text"
                                    placeholder="Tambah kriteria selesai baru..."
                                    class="flex-1 text-xs rounded-xl border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                                    :disabled="isSubmittingChecklist"
                                />
                                <button
                                    type="submit"
                                    :disabled="isSubmittingChecklist || !newChecklistText.trim()"
                                    class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 disabled:opacity-50 shadow-xs flex items-center space-x-1"
                                >
                                    <AppIcon name="plus" :size="13" />
                                    <span>Tambah</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 3: LAMPIRAN & BERKAS (Pemisahan Referensi vs Deliverable) -->
                    <div v-if="activeTab === 'attachments'" class="space-y-6">
                        <!-- 1. Lampiran Referensi & Panduan -->
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                                    <AppIcon name="folder" :size="14" class="text-blue-600" />
                                    <span>1. Lampiran Referensi & Template Kerja</span>
                                </h3>
                                <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                                    {{ references.length }} Berkas Panduan
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">
                                Berkas acuan, template dokumen (Excel/Word), atau pedoman metodologi yang disiapkan pengelola tugas.
                            </p>

                            <div v-if="references.length === 0" class="text-xs text-slate-400 py-3 text-center border border-dashed border-slate-200 rounded-xl">
                                Belum ada berkas referensi/template yang dilampirkan.
                            </div>

                            <div v-else class="space-y-2">
                                <div
                                    v-for="refItem in references"
                                    :key="refItem.id"
                                    class="flex items-center justify-between p-3 rounded-xl bg-blue-50/40 border border-blue-100 group"
                                >
                                    <div class="flex items-center space-x-3 truncate">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center shrink-0">
                                            <AppIcon name="file-text" :size="14" />
                                        </div>
                                        <div class="truncate">
                                            <h4 class="text-xs font-bold text-slate-900 truncate">{{ refItem.title }}</h4>
                                            <span class="text-[10px] text-slate-500 uppercase">{{ refItem.file_type || 'link' }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 shrink-0">
                                        <a
                                            :href="refItem.link_url || (refItem.file_path ? `/storage/${refItem.file_path}` : '#')"
                                            target="_blank"
                                            class="px-2.5 py-1 text-xs font-bold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 shadow-xs flex items-center space-x-1"
                                        >
                                            <AppIcon name="external-link" :size="12" />
                                            <span>Buka</span>
                                        </a>
                                        <button
                                            @click="deleteAttachment(refItem)"
                                            class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-rose-600 rounded"
                                        >
                                            <AppIcon name="trash" :size="13" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Bukti Hasil Pengerjaan (Deliverables) -->
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                                    <AppIcon name="check-circle" :size="14" class="text-emerald-600" />
                                    <span>2. Bukti Hasil Pengerjaan (Deliverables)</span>
                                </h3>
                                <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded">
                                    {{ deliverables.length }} Bukti Terunggah
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">
                                Dokumen hasil, dataset, prototipe, atau tautan bukti pengerjaan nyata yang diunggah oleh pelaksana tugas.
                            </p>

                            <div v-if="deliverables.length === 0" class="text-xs text-slate-400 py-3 text-center border border-dashed border-slate-200 rounded-xl">
                                Belum ada berkas hasil pengerjaan yang diunggah pelaksana.
                            </div>

                            <div v-else class="space-y-2">
                                <div
                                    v-for="deliv in deliverables"
                                    :key="deliv.id"
                                    class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/40 border border-emerald-100 group"
                                >
                                    <div class="flex items-center space-x-3 truncate">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                            <AppIcon name="download" :size="14" />
                                        </div>
                                        <div class="truncate">
                                            <h4 class="text-xs font-bold text-slate-900 truncate">{{ deliv.title }}</h4>
                                            <div class="flex items-center space-x-2 text-[10px] text-slate-500">
                                                <span>{{ deliv.user?.name || 'Pelaksana' }}</span>
                                                <span>&bull;</span>
                                                <span>{{ new Date(deliv.created_at).toLocaleDateString('id-ID') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 shrink-0">
                                        <a
                                            :href="deliv.link_url || (deliv.file_path ? `/storage/${deliv.file_path}` : '#')"
                                            target="_blank"
                                            class="px-2.5 py-1 text-xs font-bold text-emerald-800 bg-white border border-emerald-200 rounded-lg hover:bg-emerald-50 shadow-xs flex items-center space-x-1"
                                        >
                                            <AppIcon name="external-link" :size="12" />
                                            <span>Lihat Hasil</span>
                                        </a>
                                        <button
                                            @click="deleteAttachment(deliv)"
                                            class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-rose-600 rounded"
                                        >
                                            <AppIcon name="trash" :size="13" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Unggah Berkas / Tautan Baru -->
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                                Unggah Berkas atau Lampirkan Tautan Baru
                            </h3>

                            <form @submit.prevent="submitAttachment" class="space-y-3 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-medium text-slate-600 mb-1">Kategori Lampiran</label>
                                        <select
                                            v-model="attachmentForm.attachment_type"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                        >
                                            <option value="deliverable">Bukti Hasil Pengerjaan (Deliverable)</option>
                                            <option value="reference">Berkas Referensi / Panduan Kerja</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block font-medium text-slate-600 mb-1">Nama / Judul Berkas</label>
                                        <input
                                            v-model="attachmentForm.title"
                                            type="text"
                                            placeholder="Contoh: Matriks_Literatur_v1.xlsx"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                            required
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-medium text-slate-600 mb-1">Unggah File (Max 20MB)</label>
                                        <input
                                            ref="fileInputRef"
                                            type="file"
                                            @change="handleFileUpload"
                                            class="w-full text-xs rounded-xl border border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                                        />
                                    </div>

                                    <div>
                                        <label class="block font-medium text-slate-600 mb-1">Atau Tautan Eksternal (URL)</label>
                                        <input
                                            v-model="attachmentForm.link_url"
                                            type="url"
                                            placeholder="https://drive.google.com/..."
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        :disabled="isSubmittingAttachment"
                                        class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 disabled:opacity-50 shadow-xs flex items-center space-x-1"
                                    >
                                        <AppIcon name="paperclip" :size="13" />
                                        <span>Simpan Lampiran</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 4: KONTEKS METODOLOGI SPESIFIK (EXTENSION FIELDS) -->
                    <div v-if="activeTab === 'extension'" class="space-y-5">
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                                    <AppIcon name="layers" :size="14" class="text-emerald-600" />
                                    <span>Field Konteks Khusus: Metodologi {{ project?.mode?.toUpperCase() }}</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Parameter unik sesuai framework yang dipilih agar eksekusi tugas memiliki konteks lengkap.
                                </p>
                            </div>

                            <!-- Research Specific -->
                            <div v-if="project?.mode === 'research'" class="space-y-4 text-xs">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Research Question / Pertanyaan Penelitian</label>
                                    <textarea
                                        v-model="editForm.extension_fields.research_question"
                                        rows="2"
                                        placeholder="Pertanyaan ilmiah yang sedang diselidiki pada tugas ini..."
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Hipotesis / Dugaan Awal</label>
                                    <input
                                        v-model="editForm.extension_fields.hypothesis"
                                        type="text"
                                        placeholder="Hipotesis yang diuji..."
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    />
                                </div>
                            </div>

                            <!-- Product Specific -->
                            <div v-else-if="project?.mode === 'product'" class="space-y-4 text-xs">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">User Story</label>
                                    <textarea
                                        v-model="editForm.extension_fields.user_story"
                                        rows="2"
                                        placeholder="Sebagai [pengguna], saya ingin [tindakan] sehingga [manfaat]..."
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    ></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">Target Versi</label>
                                        <input
                                            v-model="editForm.extension_fields.target_version"
                                            type="text"
                                            placeholder="MVP 1 / MVP 2 / Pilot"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">Modul / Komponen</label>
                                        <input
                                            v-model="editForm.extension_fields.module"
                                            type="text"
                                            placeholder="Backend / Frontend / Sensor"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Social Specific -->
                            <div v-else-if="project?.mode === 'social'" class="space-y-4 text-xs">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Kelompok Sasaran Warga</label>
                                    <input
                                        v-model="editForm.extension_fields.target_group"
                                        type="text"
                                        placeholder="Contoh: Petani cabai desa Sukamaju, Ibu PKK RT 04"
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    />
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Aset Komunitas yang Dilibatkan (ABCD)</label>
                                    <input
                                        v-model="editForm.extension_fields.community_asset"
                                        type="text"
                                        placeholder="Modal sosial, balai pertemuan, relawan lokal..."
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    />
                                </div>
                            </div>

                            <!-- Competition Specific -->
                            <div v-else-if="project?.mode === 'competition'" class="space-y-4 text-xs">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Komponen Rubrik Penilaian Terkait</label>
                                    <input
                                        v-model="editForm.extension_fields.rubric_component"
                                        type="text"
                                        placeholder="Contoh: Inovasi Solusi & Diferensiasi (Bobot 30%)"
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">Bobot Rubrik (%)</label>
                                        <input
                                            v-model="editForm.extension_fields.rubric_weight"
                                            type="text"
                                            placeholder="30%"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">Kepatuhan Administrasi</label>
                                        <input
                                            v-model="editForm.extension_fields.compliance_note"
                                            type="text"
                                            placeholder="Sesuai batas margin & halaman"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button
                                    @click="saveTaskInfo"
                                    class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 shadow-xs"
                                >
                                    Simpan Konteks Metodologi
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: REVIEW & STAGE GATE -->
                    <div v-if="activeTab === 'reviews'" class="space-y-5">
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                                    <AppIcon name="award" :size="14" class="text-emerald-600" />
                                    <span>Persetujuan & Verifikasi Hasil (Stage Gate)</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Verifikasi oleh ketua tim atau reviewer untuk memastikan kriteria terpenuhi sebelum tugas ditutup.
                                </p>
                            </div>

                            <!-- Previous Reviews -->
                            <div v-if="taskData?.reviews && taskData?.reviews.length > 0" class="space-y-3">
                                <h4 class="text-xs font-bold text-slate-700">Riwayat Verifikasi:</h4>
                                <div
                                    v-for="rev in taskData.reviews"
                                    :key="rev.id"
                                    class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-bold text-slate-900">{{ rev.user?.name || 'Reviewer' }}</span>
                                            <span
                                                :class="{
                                                    'bg-emerald-100 text-emerald-800': rev.status === 'approved',
                                                    'bg-amber-100 text-amber-800': rev.status === 'pending',
                                                    'bg-rose-100 text-rose-800': rev.status === 'changes_requested'
                                                }"
                                                class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                            >
                                                {{ rev.status.replace('_', ' ') }}
                                            </span>
                                        </div>
                                        <span class="text-slate-400 text-[11px]">{{ new Date(rev.created_at).toLocaleDateString('id-ID') }}</span>
                                    </div>
                                    <p class="text-slate-700">{{ rev.comment || 'Tidak ada catatan.' }}</p>
                                </div>
                            </div>

                            <!-- Submit Review Form -->
                            <form @submit.prevent="submitReview" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3 text-xs">
                                <h4 class="font-bold text-slate-900">Beri Verifikasi / Keputusan Gate:</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-medium text-slate-600 mb-1">Keputusan Status</label>
                                        <select
                                            v-model="reviewForm.status"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                        >
                                            <option value="approved">Disetujui (Approved / Done)</option>
                                            <option value="changes_requested">Perlu Perbaikan (Changes Requested)</option>
                                            <option value="pending">Menunggu Pemeriksaan Lanjutan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-medium text-slate-600 mb-1">Skor Kualitas (1 - 5 Bintang)</label>
                                        <select
                                            v-model="reviewForm.rating"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                        >
                                            <option :value="5">⭐⭐⭐⭐⭐ (Sempurna)</option>
                                            <option :value="4">⭐⭐⭐⭐ (Sangat Baik)</option>
                                            <option :value="3">⭐⭐⭐ (Cukup)</option>
                                            <option :value="2">⭐⭐ (Perlu Banyak Revisi)</option>
                                            <option :value="1">⭐ (Tidak Memenuhi)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 mb-1">Catatan Evaluasi Reviewer</label>
                                    <textarea
                                        v-model="reviewForm.comment"
                                        rows="2"
                                        placeholder="Tuliskan masukan atau alasan persetujuan..."
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                    ></textarea>
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="isSubmittingReview"
                                        class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 disabled:opacity-50 shadow-xs"
                                    >
                                        Kirim Keputusan Review
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 6: DISKUSI & KOMENTAR -->
                    <div v-if="activeTab === 'comments'" class="space-y-5">
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                                Ruang Diskusi & Catatan Progres
                            </h3>

                            <!-- Comments Stream -->
                            <div class="space-y-3">
                                <div
                                    v-if="!taskData?.comments || taskData?.comments.length === 0"
                                    class="text-xs text-slate-400 py-6 text-center border border-dashed border-slate-200 rounded-xl"
                                >
                                    Belum ada diskusi untuk tugas ini.
                                </div>

                                <div
                                    v-for="comm in taskData?.comments"
                                    :key="comm.id"
                                    class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs"
                                >
                                    <div class="flex items-center justify-between text-slate-500 text-[11px]">
                                        <span class="font-bold text-slate-900">{{ comm.user?.name || 'Anggota Tim' }}</span>
                                        <span>{{ new Date(comm.created_at).toLocaleString('id-ID') }}</span>
                                    </div>
                                    <p class="text-slate-800 whitespace-pre-line">{{ comm.comment }}</p>
                                </div>
                            </div>

                            <!-- Comment Input -->
                            <form @submit.prevent="submitComment" class="space-y-2 pt-2">
                                <textarea
                                    v-model="newComment"
                                    rows="2"
                                    placeholder="Tulis catatan atau pertanyaan diskusi..."
                                    class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                                    :disabled="isSubmittingComment"
                                ></textarea>
                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="isSubmittingComment || !newComment.trim()"
                                        class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 disabled:opacity-50 shadow-xs"
                                    >
                                        Kirim Komentar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideLeft {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
.animate-slide-left {
    animation: slideLeft 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}
</style>


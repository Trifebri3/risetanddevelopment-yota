<script setup>
import { ref } from 'vue';
import AppIcon from '@/Components/AppIcon.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    project: Object,
    isOwner: Boolean,
});

const isAddMetricModalOpen = ref(false);
const editingMetric = ref(null);

const metricForm = useForm({
    name: '',
    target_value: '',
    current_value: '',
    unit: '',
    category: 'outcome',
    status: 'in_progress',
    notes: '',
});

const openAddMetricModal = () => {
    editingMetric.value = null;
    metricForm.reset();
    metricForm.category = props.project.mode === 'competition' ? 'rubric' : 'outcome';
    isAddMetricModalOpen.value = true;
};

const openEditMetricModal = (metric) => {
    editingMetric.value = metric;
    metricForm.name = metric.name;
    metricForm.target_value = metric.target_value;
    metricForm.current_value = metric.current_value;
    metricForm.unit = metric.unit || '';
    metricForm.category = metric.category || 'outcome';
    metricForm.status = metric.status || 'in_progress';
    metricForm.notes = metric.notes || '';
    isAddMetricModalOpen.value = true;
};

const submitMetric = () => {
    if (editingMetric.value) {
        metricForm.put(route('metrics.update', editingMetric.value.id), {
            onSuccess: () => {
                isAddMetricModalOpen.value = false;
                metricForm.reset();
            },
        });
    } else {
        metricForm.post(route('projects.metrics.store', props.project.slug || props.project.id), {
            onSuccess: () => {
                isAddMetricModalOpen.value = false;
                metricForm.reset();
            },
        });
    }
};

const deleteMetric = (metric) => {
    if (confirm(`Hapus indikator capaian "${metric.name}"?`)) {
        router.delete(route('metrics.destroy', metric.id), { preserveScroll: true });
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- 3-Tier Progress Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- 1. Task Execution Progress -->
            <div class="p-5 rounded-2xl sm:rounded-3xl bg-white border border-slate-200/90 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">1. Task Progress</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs border border-blue-200">
                        <AppIcon name="check-circle" :size="15" />
                    </span>
                </div>
                <div>
                    <div class="flex items-baseline space-x-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                            {{ project.task_progress_percent ?? project.progress_percent ?? 0 }}%
                        </span>
                        <span class="text-xs text-slate-400">eksekusi tugas</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Berdasarkan bobot tugas terencana yang sudah berstatus selesai.
                    </p>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div
                        class="bg-blue-600 h-2 rounded-full transition-all"
                        :style="{ width: `${project.task_progress_percent ?? project.progress_percent ?? 0}%` }"
                    ></div>
                </div>
            </div>

            <!-- 2. Milestone Gate Progress -->
            <div class="p-5 rounded-2xl sm:rounded-3xl bg-white border border-slate-200/90 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">2. Milestone Progress</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs border border-amber-200">
                        <AppIcon name="award" :size="15" />
                    </span>
                </div>
                <div>
                    <div class="flex items-baseline space-x-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                            {{ project.milestone_progress_percent ?? 0 }}%
                        </span>
                        <span class="text-xs text-slate-400">DoD tervalidasi</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Tahapan yang sudah memenuhi kriteria selesai (*Definition of Done*).
                    </p>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div
                        class="bg-amber-500 h-2 rounded-full transition-all"
                        :style="{ width: `${project.milestone_progress_percent ?? 0}%` }"
                    ></div>
                </div>
            </div>

            <!-- 3. Outcome & Impact Progress -->
            <div class="p-5 rounded-2xl sm:rounded-3xl bg-white border border-slate-200/90 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">3. Outcome Progress</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center font-bold text-xs border border-emerald-200">
                        <AppIcon name="target" :size="15" />
                    </span>
                </div>
                <div>
                    <div class="flex items-baseline space-x-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                            {{ project.outcome_progress_percent ?? 0 }}%
                        </span>
                        <span class="text-xs text-slate-400">capaian nyata</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Bukti keberhasilan riil (akurasi model, dampak warga, lolos seleksi).
                    </p>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div
                        class="bg-emerald-600 h-2 rounded-full transition-all"
                        :style="{ width: `${project.outcome_progress_percent ?? 0}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Outcome Metrics Table / List -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center space-x-2">
                        <AppIcon name="award" :size="16" class="text-emerald-600" />
                        <span>Indikator Capaian Nyata & Bukti Keberhasilan (Outcome Metrics)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Membedakan aktivitas yang sekadar selesai dengan bukti keberhasilan yang tervalidasi.
                    </p>
                </div>

                <button
                    @click="openAddMetricModal"
                    class="px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 shadow-2xs flex items-center space-x-1 self-start sm:self-auto transition-colors"
                >
                    <AppIcon name="plus" :size="13" />
                    <span>+ Tambah Indikator</span>
                </button>
            </div>

            <!-- Empty State -->
            <div
                v-if="!project.metrics || project.metrics.length === 0"
                class="text-xs text-slate-400 py-8 text-center border border-dashed border-slate-200 rounded-2xl"
            >
                Belum ada indikator capaian outcome yang ditambahkan.
                <button @click="openAddMetricModal" class="text-emerald-600 font-bold hover:underline ml-1">
                    Tambah Indikator Sekarang
                </button>
            </div>

            <!-- Metrics Cards Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div
                    v-for="metric in project.metrics"
                    :key="metric.id"
                    class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-white transition-all space-y-3 group"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                {{ metric.category }}
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">
                                {{ metric.name }}
                            </h4>
                        </div>

                        <span
                            :class="{
                                'bg-emerald-100 text-emerald-800': metric.status === 'achieved',
                                'bg-blue-100 text-blue-800': metric.status === 'in_progress',
                                'bg-amber-100 text-amber-800': metric.status === 'at_risk',
                                'bg-slate-200 text-slate-700': metric.status === 'not_started'
                            }"
                            class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase shrink-0"
                        >
                            {{ metric.status.replace('_', ' ') }}
                        </span>
                    </div>

                    <!-- Target vs Actual -->
                    <div class="p-3 rounded-xl bg-white border border-slate-100 space-y-1.5 text-xs">
                        <div class="flex justify-between items-center text-slate-500">
                            <span>Target:</span>
                            <span class="font-bold text-slate-800">{{ metric.target_value }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Capaian Saat Ini:</span>
                            <span class="font-extrabold text-emerald-700">{{ metric.current_value }}</span>
                        </div>
                    </div>

                    <!-- Notes & Actions -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-[11px] text-slate-400 truncate max-w-[150px]">
                            {{ metric.notes || 'Tidak ada catatan' }}
                        </span>

                        <div class="flex items-center space-x-1 shrink-0">
                            <button
                                @click="openEditMetricModal(metric)"
                                class="px-2 py-1 text-slate-400 hover:text-emerald-700 rounded hover:bg-slate-100"
                                title="Edit capaian"
                            >
                                <AppIcon name="file-text" :size="12" />
                            </button>
                            <button
                                @click="deleteMetric(metric)"
                                class="px-2 py-1 text-slate-300 hover:text-rose-600 rounded hover:bg-rose-50"
                                title="Hapus indikator"
                            >
                                <AppIcon name="trash" :size="12" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah / Edit Metric -->
        <div v-if="isAddMetricModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="isAddMetricModalOpen = false" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ editingMetric ? 'Perbarui Indikator Capaian' : 'Tambah Indikator Capaian Baru' }}
                    </h3>
                    <button @click="isAddMetricModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <AppIcon name="x" :size="16" />
                    </button>
                </div>

                <form @submit.prevent="submitMetric" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama Indikator</label>
                        <input
                            v-model="metricForm.name"
                            type="text"
                            placeholder="Contoh: Akurasi Model AI / Responden Warga"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Target Nilai</label>
                            <input
                                v-model="metricForm.target_value"
                                type="text"
                                placeholder="e.g. > 90% / 100 KK"
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                required
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Capaian Saat Ini</label>
                            <input
                                v-model="metricForm.current_value"
                                type="text"
                                placeholder="e.g. 75% / 20 KK"
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-emerald-500"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Kategori</label>
                            <select v-model="metricForm.category" class="w-full text-xs rounded-xl border-slate-300">
                                <option value="outcome">Outcome Utama</option>
                                <option value="technical">Spesifikasi Teknis</option>
                                <option value="scientific">Validitas Ilmiah</option>
                                <option value="usability">Usability / UX</option>
                                <option value="impact">Dampak Warga</option>
                                <option value="rubric">Rubrik Lomba</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Status Capaian</label>
                            <select v-model="metricForm.status" class="w-full text-xs rounded-xl border-slate-300">
                                <option value="in_progress">Dalam Proses (In Progress)</option>
                                <option value="achieved">Tercapai (Achieved)</option>
                                <option value="at_risk">Beresiko / Tertinggal (At Risk)</option>
                                <option value="not_started">Belum Dimulai</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Catatan Tambahan</label>
                        <textarea
                            v-model="metricForm.notes"
                            rows="2"
                            placeholder="Catatan hasil pengujian atau bukti..."
                            class="w-full text-xs rounded-xl border-slate-300"
                        ></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3">
                        <button
                            type="button"
                            @click="isAddMetricModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="metricForm.processing"
                            class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 shadow-xs"
                        >
                            {{ editingMetric ? 'Simpan Perubahan' : 'Tambah Indikator' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>


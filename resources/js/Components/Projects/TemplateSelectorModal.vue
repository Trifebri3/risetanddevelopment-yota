<script setup>
import { ref } from 'vue';
import AppIcon from '@/Components/AppIcon.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    isOpen: Boolean,
    project: Object,
    availableTemplates: Array,
});

const emit = defineEmits(['close']);

const selectedTemplateId = ref(props.availableTemplates?.[0]?.id || null);

const form = useForm({
    workflow_template_id: null,
});

const selectTemplate = (tpl) => {
    selectedTemplateId.value = tpl.id;
};

const applyTemplate = () => {
    if (!selectedTemplateId.value) return;
    if (!confirm('Terapkan template metodologi ini? Tahapan dan daftar tugas berpanduan operasional akan dibuat otomatis di proyek Anda.')) return;

    form.workflow_template_id = selectedTemplateId.value;
    form.post(route('projects.apply_template', props.project.slug || props.project.id), {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div @click="emit('close')" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        <!-- Modal Dialog -->
        <div class="relative bg-white rounded-3xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl z-10 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center space-x-2">
                        <AppIcon name="layers" :size="18" class="text-emerald-600" />
                        <span>Katalog Metodologi & Template Alur Kerja Tera</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pilih framework operasional yang sesuai untuk mengisi roadmap, milestone, instruksi, dan kriteria tugas otomatis.
                    </p>
                </div>

                <button @click="emit('close')" class="p-2 text-slate-400 hover:text-slate-700 rounded-lg">
                    <AppIcon name="x" :size="18" />
                </button>
            </div>

            <!-- Body (Templates List & Preview) -->
            <div class="p-6 overflow-y-auto space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div
                        v-for="tpl in availableTemplates"
                        :key="tpl.id"
                        @click="selectTemplate(tpl)"
                        :class="selectedTemplateId === tpl.id ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                        class="p-4 rounded-2xl border transition-all cursor-pointer space-y-3 flex flex-col justify-between"
                    >
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                    {{ tpl.category }}
                                </span>
                                <span v-if="tpl.is_default" class="text-[10px] text-slate-400 font-medium">Default</span>
                            </div>

                            <h3 class="text-sm font-bold text-slate-900">
                                {{ tpl.name }}
                            </h3>

                            <p class="text-xs text-slate-600 line-clamp-2">
                                {{ tpl.description }}
                            </p>
                        </div>

                        <!-- Stages Preview Chips -->
                        <div class="pt-2 border-t border-slate-100 text-[11px] space-y-1">
                            <span class="font-semibold text-slate-500">{{ tpl.stages?.length || 0 }} Tahap Operasional:</span>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="stg in tpl.stages?.slice(0, 3)"
                                    :key="stg.id"
                                    class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-medium"
                                >
                                    {{ stg.name.split('.')[1] || stg.name }}
                                </span>
                                <span v-if="tpl.stages?.length > 3" class="px-2 py-0.5 text-[10px] text-slate-400">
                                    +{{ tpl.stages.length - 3 }} lainnya
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Framework tidak dikunci secara kaku; Anda bebas memodifikasi tahapan setelah diterapkan.
                </span>

                <div class="flex items-center space-x-2">
                    <button
                        @click="emit('close')"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50"
                    >
                        Batal
                    </button>
                    <button
                        @click="applyTemplate"
                        :disabled="form.processing || !selectedTemplateId"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 shadow-xs flex items-center space-x-1.5"
                    >
                        <AppIcon name="check" :size="13" />
                        <span>Terapkan Metodologi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>


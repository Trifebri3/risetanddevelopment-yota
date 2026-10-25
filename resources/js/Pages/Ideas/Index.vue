<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    ideas: Array,
});

const isModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedFilterMode = ref('all');

const filteredIdeas = computed(() => {
    if (!props.ideas) return [];
    if (selectedFilterMode.value === 'all') return props.ideas;
    return props.ideas.filter(i => i.suggested_mode === selectedFilterMode.value);
});

// Form Tambah Ide Cepat
const form = useForm({
    title: '',
    content: '',
    category_tag: '',
    suggested_mode: 'research',
});

const submitIdea = () => {
    form.post(route('ideas.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

// Form Edit Ide (Update)
const editForm = useForm({
    id: null,
    title: '',
    content: '',
    category_tag: '',
    suggested_mode: 'research',
    status: 'draft',
});

const openEditModal = (idea) => {
    editForm.id = idea.id;
    editForm.title = idea.title;
    editForm.content = idea.content;
    editForm.category_tag = idea.category_tag || '';
    editForm.suggested_mode = idea.suggested_mode || 'research';
    editForm.status = idea.status || 'draft';
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEditIdea = () => {
    editForm.put(route('ideas.update', editForm.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
        },
    });
};

const convertToProject = (idea) => {
    if (confirm(`Konversi ide "${idea.title}" menjadi proyek baru?`)) {
        router.post(route('ideas.convert', idea.id));
    }
};

const deleteIdea = (idea) => {
    if (confirm(`Hapus ide "${idea.title}"?`)) {
        router.delete(route('ideas.destroy', idea.id));
    }
};

// Strict Palette: Emerald + Slate + Amber for Competition
const getModeBadge = (mode) => {
    switch (mode) {
        case 'research':
            return { label: 'Riset', icon: 'research', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
        case 'product':
            return { label: 'Produk', icon: 'product', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
        case 'social':
            return { label: 'Sosial', icon: 'social', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
        case 'competition':
            return { label: 'Lomba', icon: 'competition', color: 'bg-amber-50 text-amber-800 border-amber-200' };
        default:
            return { label: 'Umum', icon: 'idea', color: 'bg-slate-50 text-slate-700 border-slate-200' };
    }
};
</script>

<template>
    <Head title="Taman Ide (Idea Playground)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        <AppIcon name="idea" :size="24" class="text-slate-700" />
                        Taman Ide (Idea Playground)
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Tangkap gagasan bebas, pertanyaan awal, dan sketsa inovasi sebelum diformalkan menjadi proyek.
                    </p>
                </div>
                <button
                    @click="form.clearErrors(); isModalOpen = true"
                    class="inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition-all hover:shadow w-full sm:w-auto active:scale-95"
                >
                    <AppIcon name="plus" :size="16" class="mr-1.5" />
                    Tangkap Ide Cepat
                </button>
            </div>
        </template>

        <div class="py-5 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Filter Tabs -->
                <div class="flex items-center space-x-2 border-b border-slate-200 pb-3 text-xs font-semibold overflow-x-auto">
                    <button
                        @click="selectedFilterMode = 'all'"
                        :class="selectedFilterMode === 'all' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl transition-all whitespace-nowrap active:scale-95"
                    >
                        Semua Ide ({{ ideas.length }})
                    </button>
                    <button
                        @click="selectedFilterMode = 'research'"
                        :class="selectedFilterMode === 'research' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-3.5 py-2 rounded-xl transition-all flex items-center whitespace-nowrap active:scale-95"
                    >
                        <AppIcon name="research" :size="13" class="mr-1.5" />
                        <span>Riset ({{ ideas.filter(i => i.suggested_mode === 'research').length }})</span>
                    </button>
                    <button
                        @click="selectedFilterMode = 'product'"
                        :class="selectedFilterMode === 'product' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-3.5 py-2 rounded-xl transition-all flex items-center whitespace-nowrap active:scale-95"
                    >
                        <AppIcon name="product" :size="13" class="mr-1.5" />
                        <span>Produk ({{ ideas.filter(i => i.suggested_mode === 'product').length }})</span>
                    </button>
                    <button
                        @click="selectedFilterMode = 'social'"
                        :class="selectedFilterMode === 'social' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-3.5 py-2 rounded-xl transition-all flex items-center whitespace-nowrap active:scale-95"
                    >
                        <AppIcon name="social" :size="13" class="mr-1.5" />
                        <span>Sosial ({{ ideas.filter(i => i.suggested_mode === 'social').length }})</span>
                    </button>
                    <button
                        @click="selectedFilterMode = 'competition'"
                        :class="selectedFilterMode === 'competition' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-3.5 py-2 rounded-xl transition-all flex items-center whitespace-nowrap active:scale-95"
                    >
                        <AppIcon name="competition" :size="13" class="mr-1.5" />
                        <span>Lomba ({{ ideas.filter(i => i.suggested_mode === 'competition').length }})</span>
                    </button>
                </div>

                <!-- Zero State -->
                <div v-if="filteredIdeas.length === 0" class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center mb-3">
                        <AppIcon name="idea" :size="32" />
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mt-3">Belum ada ide yang tercatat</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Jangan biarkan ide cemerlang Anda hilang begitu saja. Tulis dalam hitungan detik dan kembangkan sewaktu-waktu.
                    </p>
                    <button
                        @click="form.clearErrors(); isModalOpen = true"
                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 shadow-xs active:scale-95"
                    >
                        <AppIcon name="plus" :size="14" />
                        Catat Ide Sekarang
                    </button>
                </div>

                <!-- Ideas Cards Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div
                        v-for="idea in filteredIdeas"
                        :key="idea.id"
                        class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-300 hover:shadow-md transition-all p-5 sm:p-6 flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between text-xs mb-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                    :class="getModeBadge(idea.suggested_mode).color"
                                >
                                    <AppIcon :name="getModeBadge(idea.suggested_mode).icon" :size="12" class="mr-1.5" />
                                    {{ getModeBadge(idea.suggested_mode).label }}
                                </span>

                                <span v-if="idea.category_tag" class="text-slate-400 font-medium flex items-center gap-1">
                                    <AppIcon name="tag" :size="11" />
                                    {{ idea.category_tag }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-slate-900">{{ idea.title }}</h3>
                            <p class="text-xs text-slate-600 mt-2 leading-relaxed whitespace-pre-line line-clamp-4">
                                {{ idea.content }}
                            </p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span v-if="idea.status === 'converted'" class="text-emerald-800 font-bold flex items-center">
                                <AppIcon name="check" :size="14" class="mr-1 text-emerald-600" />
                                <Link :href="route('projects.show', idea.project?.slug || idea.project_id)" class="hover:underline inline-flex items-center gap-1">
                                    Telah jadi proyek
                                    <AppIcon name="arrow-right" :size="12" />
                                </Link>
                            </span>
                            <button
                                v-else
                                @click="convertToProject(idea)"
                                class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold hover:bg-emerald-100 transition-colors active:scale-95"
                            >
                                <AppIcon name="rocket" :size="13" class="mr-1.5" />
                                Ubah Jadi Proyek
                            </button>

                            <div class="flex items-center space-x-1">
                                <button
                                    @click="openEditModal(idea)"
                                    class="text-slate-400 hover:text-emerald-700 font-medium inline-flex items-center gap-1 p-1.5 rounded-md hover:bg-slate-100 transition-colors"
                                    title="Edit ide"
                                >
                                    <AppIcon name="file-text" :size="13" />
                                    <span>Edit</span>
                                </button>
                                <button
                                    @click="deleteIdea(idea)"
                                    class="text-slate-400 hover:text-rose-600 font-medium inline-flex items-center gap-1 p-1.5 rounded-md hover:bg-rose-50 transition-colors"
                                    title="Hapus ide"
                                >
                                    <AppIcon name="trash" :size="13" />
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Catat Ide Cepat (CREATE) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-5 sm:p-7 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                        <AppIcon name="idea" :size="18" class="text-slate-700" />
                        Catat Ide Cepat
                    </h2>
                    <button @click="isModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>

                <form @submit.prevent="submitIdea" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Gagasan / Pertanyaan <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="Contoh: Drone NDVI Murah untuk Monitoring Padi"
                            class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.title" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Saran Mode Proyek <span class="text-rose-500">*</span></label>
                            <select v-model="form.suggested_mode" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="research">Riset (Research Dev)</option>
                                <option value="product">Produk (Product Dev)</option>
                                <option value="social">Sosial (Social Project)</option>
                                <option value="competition">Lomba / Kompetisi (Competition)</option>
                            </select>
                            <p v-if="form.errors.suggested_mode" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.suggested_mode }}</p>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori / Tag</label>
                            <input
                                v-model="form.category_tag"
                                type="text"
                                placeholder="Contoh: AI, IoT, Edukasi"
                                class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <p v-if="form.errors.category_tag" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.category_tag }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Bebas / Uraian Ide <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="form.content"
                            rows="4"
                            required
                            placeholder="Tuliskan ide awal, hipotesis singkat, atau sketsa rencana Anda..."
                            class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                        <p v-if="form.errors.content" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.content }}</p>
                    </div>

                    <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs transition-colors active:scale-95 disabled:opacity-50">
                            <AppIcon name="check" :size="14" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Ide' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Ide (UPDATE) -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-5 sm:p-7 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                        <AppIcon name="file-text" :size="18" class="text-emerald-700" />
                        Edit Catatan Ide
                    </h2>
                    <button @click="isEditModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <AppIcon name="close" :size="18" />
                    </button>
                </div>

                <form @submit.prevent="submitEditIdea" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Gagasan / Pertanyaan <span class="text-rose-500">*</span></label>
                        <input
                            v-model="editForm.title"
                            type="text"
                            required
                            class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="editForm.errors.title" class="text-xs text-rose-500 mt-1 font-medium">{{ editForm.errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Saran Mode Proyek <span class="text-rose-500">*</span></label>
                            <select v-model="editForm.suggested_mode" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="research">Riset (Research Dev)</option>
                                <option value="product">Produk (Product Dev)</option>
                                <option value="social">Sosial (Social Project)</option>
                                <option value="competition">Lomba / Kompetisi (Competition)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori / Tag</label>
                            <input
                                v-model="editForm.category_tag"
                                type="text"
                                placeholder="Contoh: AI, IoT, Edukasi"
                                class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status Ide</label>
                        <select v-model="editForm.status" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                            <option value="draft">Draft (Belum Dikonversi)</option>
                            <option value="converted">Sudah Dikonversi ke Proyek</option>
                            <option value="archived">Diarsipkan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Bebas / Uraian Ide <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="editForm.content"
                            rows="5"
                            required
                            class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                        <p v-if="editForm.errors.content" class="text-xs text-rose-500 mt-1 font-medium">{{ editForm.errors.content }}</p>
                    </div>

                    <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100">
                        <button type="button" @click="isEditModalOpen = false" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="editForm.processing" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs transition-colors active:scale-95 disabled:opacity-50">
                            <AppIcon name="check" :size="14" />
                            {{ editForm.processing ? 'Menyimpan...' : 'Perbarui Ide' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

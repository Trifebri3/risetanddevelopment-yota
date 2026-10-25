<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    ideas: Array,
});

const isModalOpen = ref(false);

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

// Strict 2-Color Palette: Emerald + Slate
const getModeBadge = (mode) => {
    switch (mode) {
        case 'research':
            return { label: 'Riset', icon: 'research', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
        case 'product':
            return { label: 'Produk', icon: 'product', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
        case 'social':
            return { label: 'Sosial', icon: 'social', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
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
                    @click="isModalOpen = true"
                    class="inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition-all hover:shadow w-full sm:w-auto active:scale-95"
                >
                    <AppIcon name="plus" :size="16" class="mr-1.5" />
                    Tangkap Ide Cepat
                </button>
            </div>
        </template>

        <div class="py-5 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Zero State -->
                <div v-if="ideas.length === 0" class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center mb-3">
                        <AppIcon name="idea" :size="32" />
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mt-3">Belum ada ide yang dicatat</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Jangan biarkan ide cemerlang Anda hilang begitu saja. Tulis dalam hitungan detik dan kembangkan sewaktu-waktu.
                    </p>
                    <button
                        @click="isModalOpen = true"
                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 shadow-xs"
                    >
                        <AppIcon name="plus" :size="14" />
                        Catat Ide Pertama
                    </button>
                </div>

                <!-- Ideas Cards Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div
                        v-for="idea in ideas"
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
                                <Link :href="route('projects.show', idea.project_id)" class="hover:underline inline-flex items-center gap-1">
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

                            <button
                                @click="deleteIdea(idea)"
                                class="text-slate-400 hover:text-slate-700 font-medium inline-flex items-center gap-1 p-1 rounded-md transition-colors"
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

        <!-- Modal Catat Ide Cepat -->
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
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Saran Mode Proyek</label>
                            <select v-model="form.suggested_mode" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="research">Riset (Research Dev)</option>
                                <option value="product">Produk (Product Dev)</option>
                                <option value="social">Sosial (Social Project)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori / Tag</label>
                            <input
                                v-model="form.category_tag"
                                type="text"
                                placeholder="Contoh: AI, IoT, Edukasi"
                                class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
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
                    </div>

                    <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-xs transition-colors active:scale-95">
                            <AppIcon name="check" :size="14" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Ide' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

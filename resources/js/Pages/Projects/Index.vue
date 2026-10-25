<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    projects: Array,
    filters: Object,
});

const isModalOpen = ref(false);
const searchInput = ref(props.filters?.search || '');
const selectedScope = ref(props.filters?.scope || 'all');
const selectedMode = ref(props.filters?.mode || 'all');
const selectedStatus = ref(props.filters?.status || 'all');

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('create') === 'true') {
        isModalOpen.value = true;
    }
});

const form = useForm({
    title: '',
    mode: 'research',
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

const applyFilter = () => {
    router.get(
        route('projects.index'),
        {
            search: searchInput.value || undefined,
            scope: selectedScope.value !== 'all' ? selectedScope.value : undefined,
            mode: selectedMode.value !== 'all' ? selectedMode.value : undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};


// Strict 2-Color Palette: Emerald + Slate
const getModeInfo = (mode) => {
    switch (mode) {
        case 'research':
            return {
                label: 'Research Development',
                badge: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'research',
                desc: 'Pertanyaan riset, metodologi, eksperimen, dataset, & publikasi ilmiah.',
            };
        case 'product':
            return {
                label: 'Product Development',
                badge: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'product',
                desc: 'Validasi masalah, komponen hardware/software, prototipe, & rilis MVP.',
            };
        case 'social':
            return {
                label: 'Social Project Development',
                badge: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'social',
                desc: 'Pohon masalah, intervensi komunitas, penerima manfaat, & KPI dampak.',
            };
        case 'competition':
            return {
                label: 'Lomba & Kompetisi',
                badge: 'bg-amber-50 text-amber-800 border-amber-200',
                icon: 'competition',
                desc: 'Panduan lomba, proposal/pitching, seleksi bertahap, & target kejuaraan.',
            };
        default:
            return {
                label: 'Umum',
                badge: 'bg-slate-50 text-slate-700 border-slate-200',
                icon: 'folder',
                desc: 'Pengembangan proyek.',
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
                        Kelola seluruh inisiatif Anda dengan pemantauan terstruktur dan bukti nyata.
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
                <!-- Scope Tabs (Semua / Milik Saya / Kolaborasi Tim) -->
                <div class="flex items-center space-x-2 border-b border-slate-200 pb-3 text-xs font-semibold overflow-x-auto">
                    <button
                        @click="selectedScope = 'all'; applyFilter()"
                        :class="selectedScope === 'all' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl transition-all whitespace-nowrap active:scale-95"
                    >
                        Semua Proyek
                    </button>
                    <button
                        @click="selectedScope = 'owned'; applyFilter()"
                        :class="selectedScope === 'owned' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl transition-all whitespace-nowrap active:scale-95"
                    >
                        Milik Saya
                    </button>
                    <button
                        @click="selectedScope = 'team'; applyFilter()"
                        :class="selectedScope === 'team' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl transition-all flex items-center whitespace-nowrap active:scale-95"
                    >
                        <AppIcon name="users" :size="13" class="mr-1.5" />
                        <span>Kolaborasi Tim</span>
                    </button>
                </div>

                <!-- Filters & Search Bar (Thumb-friendly on mobile) -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input
                            v-model="searchInput"
                            @keyup.enter="applyFilter"
                            type="text"
                            placeholder="Cari judul proyek, rumusan masalah..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                        />
                        <AppIcon name="search" :size="16" class="text-slate-400 absolute left-3.5 top-3" />
                    </div>

                    <!-- Mode and Status Selection -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold overflow-x-auto max-w-full">
                            <button
                                @click="selectedMode = 'all'; applyFilter()"
                                :class="selectedMode === 'all' ? 'bg-white shadow-xs text-slate-900 font-bold' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap"
                            >
                                Semua
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

                        <select
                            v-model="selectedStatus"
                            @change="applyFilter"
                            class="text-xs font-medium py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="all">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="completed">Selesai</option>
                            <option value="draft">Draf</option>
                            <option value="on_hold">Tertunda (On Hold)</option>
                        </select>
                    </div>
                </div>

                <!-- Projects Cards Grid -->
                <div v-if="projects.length === 0" class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center">
                    <AppIcon name="folder" :size="36" class="text-slate-300 mx-auto" />
                    <h3 class="text-sm sm:text-base font-bold text-slate-800 mt-3">Tidak ada proyek yang sesuai filter</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Coba ubah kata kunci pencarian atau buat proyek baru sesuai kebutuhan Anda.</p>
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
                        class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 hover:border-emerald-300 hover:shadow-md transition-all flex flex-col justify-between p-5 sm:p-6 relative group"
                    >
                        <!-- Top Meta -->
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                    :class="getModeInfo(project.mode).badge"
                                >
                                    <AppIcon :name="getModeInfo(project.mode).icon" :size="12" class="mr-1.5" />
                                    <span>{{ getModeInfo(project.mode).label }}</span>
                                </span>

                                <div class="flex items-center space-x-1.5">
                                    <span v-if="project.user_id !== $page.props.auth.user.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200" title="Proyek Kolaborasi Tim">
                                        <AppIcon name="users" :size="10" class="mr-1 text-slate-500" />
                                        <span>Tim</span>
                                    </span>
                                    <span v-if="project.is_public" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <AppIcon name="share" :size="10" class="mr-1" />
                                        <span>Link Publik</span>
                                    </span>
                                </div>
                            </div>


                            <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-2">
                                <Link :href="route('projects.show', project.slug || project.id)">
                                    {{ project.title }}
                                </Link>
                            </h3>

                            <p class="text-xs text-slate-500 mt-2 line-clamp-3 leading-relaxed">
                                {{ project.problem_statement || project.description }}
                            </p>
                        </div>

                        <!-- Bottom Section -->
                        <div class="mt-6 pt-4 border-t border-slate-100 space-y-4">
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
                                    <span>{{ project.outputs?.length || 0 }} Luaran</span>
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

        <!-- Modal Buat Proyek Baru -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-2xl w-full p-5 sm:p-8 shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto">
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
                    <!-- Mode Proyek Selection (3 Choice Cards) -->
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
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Hipotesis, metode, data, & jurnal.</p>
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
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Kebutuhan teknis, prototipe, & MVP.</p>
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
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Komunitas, lapangan, & KPI dampak.</p>
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
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1">Panduan lomba, pitch deck, & juara.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Judul Proyek -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Proyek <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="Contoh: Pengembangan Model AI Deteksi Dini Penyakit Padi"
                            class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.title" class="text-xs text-rose-500 mt-1">{{ form.errors.title }}</p>
                    </div>

                    <!-- Masalah yang Ingin Diselesaikan (Problem Statement) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Masalah / Latar Belakang (Problem Statement)</label>
                        <textarea
                            v-model="form.problem_statement"
                            rows="2"
                            placeholder="Jelaskan masalah utama yang ingin dijawab atau diselesaikan..."
                            class="w-full text-xs sm:text-sm px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <!-- Target Luaran Utama -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Target Luaran Utama</label>
                        <input
                            v-model="form.target_outcome"
                            type="text"
                            placeholder="Contoh: Manuskrip Scopus Q2, Prototipe Hardware MVP, atau Laporan Dampak Komunitas"
                            class="w-full text-xs sm:text-sm px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                        />
                    </div>

                    <!-- Tanggal Mulai dan Selesai -->
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
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Target Selesai (Opsional)</label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="w-full text-xs sm:text-sm px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Buttons -->
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
    </AuthenticatedLayout>
</template>

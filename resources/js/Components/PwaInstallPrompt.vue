<script setup>
import { onMounted } from 'vue';
import { usePwa } from '@/pwa';
import AppIcon from '@/Components/AppIcon.vue';

const {
    isInstallable,
    isInstalled,
    isIos,
    isDismissed,
    initPwa,
    promptInstall,
    dismissPrompt,
} = usePwa();

onMounted(() => {
    initPwa();
});
</script>

<template>
    <!-- Show only if installable or iOS guide, not already installed, and not dismissed -->
    <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        enter-to-class="translate-y-0 opacity-100 sm:scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="(isInstallable || (isIos && !isDismissed && !isInstalled))"
            class="fixed bottom-20 sm:bottom-6 left-4 right-4 sm:left-auto sm:right-6 sm:max-w-md z-50 bg-white border border-emerald-300/80 rounded-2xl shadow-xl p-4 sm:p-5 backdrop-blur-md"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 border border-emerald-200 mt-0.5">
                        <AppIcon name="smartphone" :size="20" />
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h4 class="text-sm font-bold text-slate-900">
                                Pasang Aplikasi TERA
                            </h4>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800">
                                PWA
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Akses workspace riset lebih cepat, responsif tanpa loading browser, dan siap digunakan secara offline.
                        </p>

                        <!-- iOS Guide -->
                        <div v-if="isIos && !isInstallable" class="mt-2.5 p-2 rounded-lg bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1">
                            <div class="flex items-center space-x-1.5 font-medium text-slate-700">
                                <AppIcon name="share" :size="12" class="text-emerald-700" />
                                <span>Cara Pasang di iPhone/iPad:</span>
                            </div>
                            <p class="pl-4">
                                Ketuk tombol <strong>Bagikan (Share)</strong> di Safari, lalu pilih <strong>"Tambahkan ke Layar Utama"</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dismiss Button -->
                <button
                    @click="dismissPrompt"
                    type="button"
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors shrink-0"
                    title="Tutup pemberitahuan"
                >
                    <AppIcon name="close" :size="16" />
                </button>
            </div>

            <!-- Install Action Button (Android / Desktop Chrome / Edge) -->
            <div v-if="isInstallable" class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button
                    @click="dismissPrompt"
                    type="button"
                    class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors"
                >
                    Nanti Saja
                </button>
                <button
                    @click="promptInstall"
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs hover:shadow transition-all active:scale-95"
                >
                    <AppIcon name="download" :size="14" class="mr-1.5" />
                    <span>Pasang di Perangkat</span>
                </button>
            </div>
        </div>
    </transition>
</template>

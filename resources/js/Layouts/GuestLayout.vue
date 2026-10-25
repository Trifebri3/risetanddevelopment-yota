<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import PlatformInitiators from '@/Components/PlatformInitiators.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 p-4 sm:p-6 text-slate-800 antialiased">
        <div class="mb-6 text-center">
            <Link :href="route('welcome')" class="inline-block transition-transform hover:scale-[1.02]">
                <ApplicationLogo class="h-12 w-auto max-h-14" />
            </Link>
            <p class="text-xs text-slate-500 mt-2 font-medium">Research & Development Workspace</p>
        </div>

        <div class="w-full overflow-hidden bg-white px-6 py-8 shadow-xl sm:max-w-md sm:rounded-3xl border border-slate-200/80">
            <slot />
        </div>

        <div class="mt-6 text-center text-xs text-slate-400">
            <Link :href="route('welcome')" class="hover:text-emerald-700 transition-colors">
                &larr; Kembali ke Halaman Utama
            </Link>
        </div>

        <!-- Inisiator Platform Footer (YOIN & Yota Adiwidya Center) -->
        <div class="mt-8 pt-6 border-t border-slate-200/60 max-w-md w-full">
            <PlatformInitiators :compact="true" />
        </div>
    </div>
</template>

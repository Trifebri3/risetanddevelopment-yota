<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AppIcon from '@/Components/AppIcon.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import PwaInstallPrompt from '@/Components/PwaInstallPrompt.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 antialiased flex flex-col justify-between">
        <div>
            <!-- Top Navigation Bar -->
            <nav class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-md transition-all shadow-xs">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 sm:h-18 justify-between items-center">
                        <div class="flex items-center space-x-4 sm:space-x-8">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="focus:outline-none transition-transform hover:scale-[1.02]">
                                    <ApplicationLogo class="h-8 sm:h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Primary Desktop Links -->
                            <div class="hidden md:-my-px md:flex md:space-x-2">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    class="inline-flex items-center px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-colors"
                                >
                                    <AppIcon name="activity" :size="15" class="mr-2" />
                                    <span>Dashboard</span>
                                </NavLink>

                                <NavLink
                                    :href="route('projects.index')"
                                    :active="route().current('projects.*')"
                                    class="inline-flex items-center px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-colors"
                                >
                                    <AppIcon name="folder" :size="15" class="mr-2" />
                                    <span>Proyek Saya</span>
                                </NavLink>

                                <NavLink
                                    :href="route('ideas.index')"
                                    :active="route().current('ideas.*')"
                                    class="inline-flex items-center px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl transition-colors"
                                >
                                    <AppIcon name="idea" :size="15" class="mr-2" />
                                    <span>Taman Ide</span>
                                </NavLink>
                            </div>
                        </div>

                        <!-- Right Controls -->
                        <div class="hidden sm:flex sm:items-center sm:space-x-3">
                            <Link
                                :href="route('projects.index', { create: 'true' })"
                                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition-all hover:shadow"
                            >
                                <AppIcon name="plus" :size="14" class="mr-1.5" />
                                <span>Buat Proyek</span>
                            </Link>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-2">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-1.5 text-xs sm:text-sm font-medium rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 focus:outline-none transition-colors"
                                        >
                                            <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs mr-2">
                                                {{ $page.props.auth.user.name.charAt(0) }}
                                            </div>
                                            <span class="max-w-[120px] truncate font-semibold">{{ $page.props.auth.user.name }}</span>
                                            <AppIcon name="chevron-right" :size="14" class="ms-1.5 text-slate-400 rotate-90" />
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="px-4 py-2.5 border-b border-slate-100 text-xs text-slate-500">
                                            Login sebagai <br>
                                            <strong class="text-slate-800">{{ $page.props.auth.user.email }}</strong>
                                        </div>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profil Saya
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Keluar (Log Out)
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Mobile Menu Button -->
                        <div class="flex items-center md:hidden space-x-2">
                            <Link
                                :href="route('projects.index', { create: 'true' })"
                                class="inline-flex items-center p-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white"
                                title="Buat Proyek"
                            >
                                <AppIcon name="plus" :size="16" />
                            </Link>

                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-xl p-2.5 text-slate-600 hover:bg-slate-100 focus:outline-none transition-colors"
                                aria-label="Toggle Navigation"
                            >
                                <AppIcon :name="showingNavigationDropdown ? 'close' : 'menu'" :size="20" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Mobile Navigation Menu -->
                <div v-show="showingNavigationDropdown" class="md:hidden border-t border-slate-200 bg-white px-4 py-4 space-y-3 shadow-lg">
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            <div class="flex items-center">
                                <AppIcon name="activity" :size="16" class="mr-2.5" />
                                <span>Dashboard</span>
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('projects.index')" :active="route().current('projects.*')">
                            <div class="flex items-center">
                                <AppIcon name="folder" :size="16" class="mr-2.5" />
                                <span>Proyek Saya</span>
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('ideas.index')" :active="route().current('ideas.*')">
                            <div class="flex items-center">
                                <AppIcon name="idea" :size="16" class="mr-2.5" />
                                <span>Taman Ide</span>
                            </div>
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <div class="text-sm font-bold text-slate-900">{{ $page.props.auth.user.name }}</div>
                        <div class="text-xs text-slate-500">{{ $page.props.auth.user.email }}</div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil Saya
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Keluar (Log Out)
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Flash Alert Notification Toast -->
            <div v-if="page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="flex items-center p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs sm:text-sm shadow-xs">
                    <AppIcon name="check-circle" :size="18" class="text-emerald-600 mr-2.5 shrink-0" />
                    <span class="font-medium">{{ page.props.flash.success }}</span>
                </div>
            </div>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white border-b border-slate-200/60 shadow-2xs">
                <div class="mx-auto max-w-7xl px-4 py-4 sm:py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Main Content -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-8 text-xs text-slate-500 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Initiators Attribution -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/70 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-center sm:text-left">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">
                            Inisiator Platform
                        </span>
                        <span class="text-slate-600 text-xs">Research & Development Workspace Ecosystem</span>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-5 sm:gap-6">
                        <a
                            href="https://yotainovasi.id/"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="PT Yota Inovasi Nusantara (YOIN) | Transformasi Digital, Jasa Website & Ekosistem Teknologi"
                            class="inline-flex items-center transition-transform hover:scale-105"
                        >
                            <img
                                src="https://yotainovasi.id/logotulisan.png"
                                alt="PT Yota Inovasi Nusantara (YOIN)"
                                class="h-6 w-auto object-contain"
                            />
                        </a>

                        <div class="w-px h-5 bg-slate-200"></div>

                        <a
                            href="https://siyota.org/"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="Beranda | Yota Adiwidya Center"
                            class="inline-flex items-center transition-transform hover:scale-105"
                        >
                            <img
                                src="https://siyota.org/image/logo.png"
                                alt="Yota Adiwidya Center"
                                class="h-6 w-auto object-contain"
                            />
                        </a>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-slate-400">
                    <div class="flex items-center space-x-2">
                        <ApplicationLogo class="h-5 w-auto" />
                        <span>&copy; 2026 Research & Development Workspace</span>
                    </div>
                    <span>Simple by default • Evidence-led • Human-centred</span>
                </div>
            </div>
        </footer>

        <!-- PWA Install Prompt Banner -->
        <PwaInstallPrompt />
    </div>
</template>

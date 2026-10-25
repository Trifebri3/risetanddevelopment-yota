<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke Workspace" />

        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900">Masuk ke Workspace</h2>
            <p class="text-xs text-slate-500 mt-1">Gunakan akun Anda untuk mengelola riset dan proyek inovasi.</p>
        </div>

        <div v-if="status" class="mb-4 text-xs font-semibold text-emerald-800 bg-emerald-50 p-3 rounded-xl border border-emerald-200">
            {{ status }}
        </div>

        <!-- Google Login Button -->
        <div class="space-y-4 mb-4">
            <a
                :href="route('auth.google.redirect')"
                class="w-full inline-flex items-center justify-center gap-3 py-3 px-4 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold text-xs sm:text-sm hover:bg-slate-50 transition-all shadow-xs active:scale-95"
            >
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Masuk dengan Akun Google</span>
            </a>

            <div class="relative flex items-center justify-center">
                <div class="border-t border-slate-200 w-full"></div>
                <span class="bg-white px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider absolute">atau email</span>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4 text-xs">
            <div>
                <InputLabel for="email" value="Alamat Email" class="font-semibold text-slate-700" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@institusi.ac.id"
                />

                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi (Password)" class="font-semibold text-slate-700" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center space-x-2">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-emerald-600 focus:ring-emerald-500" />
                    <span class="text-slate-600 font-medium">Ingat Saya</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-emerald-800 hover:underline font-semibold"
                >
                    Lupa password?
                </Link>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold active:scale-95 transition-all"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk ke Workspace
                </PrimaryButton>
            </div>

            <div class="text-center pt-2 text-slate-500 text-xs">
                Belum punya akun?
                <Link :href="route('register')" class="text-emerald-800 font-bold hover:underline ml-1">
                    Daftar Sekarang
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

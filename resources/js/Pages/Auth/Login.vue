<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

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
    <Head title="Masuk" />

    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-4 py-10 sm:px-6">
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-20 h-[28rem] w-[28rem] rounded-full bg-cyan-500/15 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_0%,rgba(30,64,175,0.18),transparent_55%)]"></div>
        </div>

        <div class="relative grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-white/10 bg-white shadow-2xl shadow-blue-950/40 lg:min-h-[620px] lg:grid-cols-[1.05fr_0.95fr]">
            <section class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-blue-700 via-blue-800 to-slate-950 p-10 text-white lg:flex xl:p-14">
                <div class="absolute -right-24 top-28 h-72 w-72 rounded-full border border-white/10"></div>
                <div class="absolute -right-10 top-44 h-44 w-44 rounded-full border border-white/10"></div>
                <div class="absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-cyan-400/10 blur-2xl"></div>

                <div class="relative">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-xl shadow-inner ring-1 ring-white/20">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </span>
                        <div>
                            <p class="text-sm font-extrabold tracking-[0.18em]">CBT SYSTEM</p>
                            <p class="mt-0.5 text-xs text-blue-100/75">Computer Based Test</p>
                        </div>
                    </div>

                    <div class="mt-24 max-w-md">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-semibold text-blue-50">
                            <span class="h-2 w-2 rounded-full bg-emerald-300 shadow-[0_0_12px_rgba(110,231,183,0.8)]"></span>
                            Platform ujian digital
                        </span>
                        <h1 class="mt-6 text-4xl font-black leading-[1.12] tracking-tight xl:text-5xl">Fokus pada ujian.<br /><span class="text-cyan-200">Kami urus sisanya.</span></h1>
                        <p class="mt-5 max-w-sm text-sm leading-7 text-blue-100/75">Masuk ke akun Anda untuk mengikuti ujian, memantau progres, dan melihat hasil dengan mudah.</p>
                    </div>
                </div>

                <div class="relative flex items-center gap-3 border-t border-white/15 pt-6 text-xs text-blue-100/65">
                    <i class="fa-solid fa-shield-halved text-cyan-200"></i>
                    <span>Akses aman untuk peserta dan administrator</span>
                </div>
            </section>

            <section class="flex items-center justify-center px-6 py-10 sm:px-10 lg:px-12 xl:px-16">
                <div class="w-full max-w-sm">
                    <div class="mb-9 lg:hidden">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-xl text-white shadow-lg shadow-blue-200">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <p class="mt-4 text-xs font-extrabold tracking-[0.18em] text-blue-700">CBT SYSTEM</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Selamat datang</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Masuk ke akun</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan email dan password yang sudah terdaftar.</p>
                    </div>

                    <form class="mt-8 space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Alamat email</label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input id="email" v-model="form.email" type="email" required autofocus autocomplete="username" placeholder="nama@email.com" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-blue-500" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input id="password" v-model="form.password" type="password" required autocomplete="current-password" placeholder="Masukkan password" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-blue-500" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                            <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                            <span>Ingat saya di perangkat ini</span>
                        </label>

                        <button type="submit" :disabled="form.processing" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 hover:shadow-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                            <span>{{ form.processing ? 'Memeriksa akun...' : 'Masuk' }}</span>
                            <i v-if="!form.processing" class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                            <i v-else class="fa-solid fa-circle-notch animate-spin text-xs"></i>
                        </button>
                    </form>

                    <p class="mt-8 text-center text-xs leading-5 text-slate-400">Akses hanya tersedia untuk akun yang dibuat oleh administrator.</p>
                </div>
            </section>
        </div>
    </main>
</template>

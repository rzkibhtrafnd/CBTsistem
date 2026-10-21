<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

const user = computed(() => usePage().props.auth?.user ?? {});
const initials = computed(() => user.value.name?.trim()?.charAt(0)?.toUpperCase() ?? 'U');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pengaturan Profil" />

        <template #header>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Pengaturan akun</p>
                <h1 class="mt-1 text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Profil Saya</h1>
            </div>
        </template>

        <Breadcrumb :items="[{ label: 'Profil Saya' }]" />

        <div class="mx-auto space-y-6">
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-blue-950 to-blue-800 p-6 text-white shadow-xl shadow-blue-950/10 sm:p-8">
                <div class="pointer-events-none absolute -right-10 -top-28 h-72 w-72 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute -right-1 top-0 h-48 w-48 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-white/10 text-3xl font-black ring-1 ring-white/20 backdrop-blur">
                        {{ initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-cyan-200">Akun {{ user.role === 'admin' ? 'administrator' : 'peserta' }}</p>
                        <h2 class="mt-1 truncate text-2xl font-black tracking-tight sm:text-3xl">{{ user.name }}</h2>
                        <p class="mt-1 truncate text-sm text-blue-100/75">{{ user.email }}</p>
                    </div>
                    <div class="sm:ml-auto">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1.5 text-xs font-semibold text-emerald-100">
                            <i class="fa-solid fa-shield-halved"></i>
                            Akun terlindungi
                        </span>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <UpdateProfileInformationForm />
                <UpdatePasswordForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

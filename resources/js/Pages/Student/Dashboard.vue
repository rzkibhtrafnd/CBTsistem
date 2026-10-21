<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard Siswa" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Dashboard Siswa</h1>
                <p class="mt-1 text-sm text-slate-500">Selamat datang kembali di CBT Sistem.</p>
            </div>
        </template>

        <Breadcrumb :items="[{ label: 'Dashboard Siswa' }]" />

        <section class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-blue-950 to-blue-700 px-5 py-7 text-white shadow-lg shadow-blue-950/15 sm:rounded-3xl sm:px-10 sm:py-11">
            <div class="pointer-events-none absolute -right-12 -top-20 -z-10 h-72 w-72 rounded-full border-[36px] border-white/5"></div>
            <div class="pointer-events-none absolute bottom-0 right-1/3 -z-10 h-40 w-40 rounded-full bg-blue-400/10 blur-3xl"></div>

            <div class="relative flex min-w-0 flex-col items-start gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0 max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-blue-100">
                    <i class="fa-solid fa-graduation-cap"></i>Ruang belajar siswa
                </span>
                <h2 class="mt-5 break-words text-2xl font-black tracking-tight sm:text-4xl">
                    Halo, {{ page.props.auth?.user?.name || 'Siswa' }}
                </h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-blue-100 sm:text-base">
                    Siap mengerjakan ujian? Buka daftar ujian untuk melihat jadwal yang tersedia, melanjutkan sesi, atau memeriksa hasil yang sudah selesai.
                </p>
                </div>
                <Link :href="route('student.exams.index')" class="w-full shrink-0 sm:w-auto">
                    <PrimaryButton type="button" class="w-full justify-center !rounded-xl !bg-white !px-5 !py-3 !text-blue-800 hover:!bg-blue-50 sm:w-auto">
                        <i class="fa-solid fa-file-circle-check mr-2"></i>Lihat daftar ujian
                    </PrimaryButton>
                </Link>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

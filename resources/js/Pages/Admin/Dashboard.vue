<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const menus = [
    { label: 'Bank Test', description: 'Atur test dan kumpulan soal.', route: 'admin.exams.index', icon: 'fa-clipboard-list', color: 'violet' },
    { label: 'Manajemen Event', description: 'Jadwalkan ujian dan tugaskan peserta.', route: 'admin.events.index', icon: 'fa-calendar-days', color: 'blue' },
    { label: 'Kelola User', description: 'Kelola akun siswa dan administrator.', route: 'admin.users.index', icon: 'fa-users', color: 'emerald' },
    { label: 'Rekap Hasil', description: 'Pantau hasil ujian per event.', route: 'admin.reports.index', icon: 'fa-chart-column', color: 'amber' },
];
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard Admin" />
        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Dashboard Admin</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola ujian CBT dari satu tempat.</p>
            </div>
        </template>
        <Breadcrumb :items="[{ label: 'Dashboard' }]" />

        <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-blue-950 to-blue-800 px-5 py-6 text-white shadow-sm sm:px-8 sm:py-9">
            <p class="text-sm font-semibold text-blue-200">Panel administrator</p>
            <h2 class="mt-2 max-w-2xl text-2xl font-extrabold tracking-tight sm:text-3xl">Selamat datang di sistem CBT</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100 sm:text-base">Pilih menu untuk mengelola test, event ujian, peserta, dan rekap hasil.</p>
        </section>

        <section class="mt-5 grid gap-3 sm:mt-6 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
            <Link v-for="menu in menus" :key="menu.route" :href="route(menu.route)" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md sm:p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-lg text-slate-700 transition group-hover:bg-blue-50 group-hover:text-blue-700">
                    <i class="fa-solid" :class="menu.icon"></i>
                </span>
                <h3 class="mt-4 font-bold text-slate-900">{{ menu.label }}</h3>
                <p class="mt-1 text-sm leading-5 text-slate-500">{{ menu.description }}</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-blue-700">Buka menu <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-1"></i></span>
            </Link>
        </section>
    </AuthenticatedLayout>
</template>

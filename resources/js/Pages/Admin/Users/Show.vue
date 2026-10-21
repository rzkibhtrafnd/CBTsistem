<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

defineProps({ user: { type: Object, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail ${user.name}`" />
        <template #header>
            <div><h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Detail User</h1><p class="mt-1 text-sm font-normal text-slate-500">Informasi akun dan aktivitas ujian user.</p></div>
        </template>
        <Breadcrumb :items="[{ label: 'Kelola User', url: route('admin.users.index') }, { label: user.name }]" />

        <div class="mx-auto space-y-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 bg-gradient-to-r from-white to-slate-50/70 px-4 py-5 sm:px-6 sm:py-6">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-xl font-extrabold text-blue-700">{{ user.name.charAt(0).toUpperCase() }}</span>
                        <div class="min-w-0">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="user.role === 'admin' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700'">{{ user.role === 'admin' ? 'Administrator' : 'Siswa' }}</span>
                            <h2 class="mt-2 break-words text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ user.name }}</h2>
                            <p class="break-all text-sm text-slate-500">{{ user.email }}</p>
                        </div>
                    </div>
                </header>
                <div class="grid divide-y divide-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Event ditugaskan</p><p class="mt-1 font-bold text-slate-900">{{ user.events_count }} event</p></div>
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Sesi ujian</p><p class="mt-1 font-bold text-slate-900">{{ user.exam_sessions_count }} sesi</p></div>
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Terdaftar</p><p class="mt-1 font-bold text-slate-900">{{ formatIndonesiaDateTime(user.created_at) }}</p></div>
                </div>
            </section>

            <section v-if="user.role === 'student'" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 px-4 py-5 sm:px-6"><h2 class="font-bold text-slate-900">Event ditugaskan</h2><p class="mt-1 text-sm text-slate-500">Daftar event yang memberikan akses ujian kepada siswa ini.</p></header>
                <div v-if="user.events.length" class="divide-y divide-slate-100">
                    <article v-for="event in user.events" :key="event.id" class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0"><h3 class="break-words font-semibold text-slate-900">{{ event.title }}</h3><p class="mt-1 text-xs text-slate-500">{{ formatIndonesiaDateTime(event.start_time) }} · {{ event.tests_count }} test</p></div>
                        <span class="inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold" :class="event.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">{{ event.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </article>
                </div>
                <div v-else class="px-6 py-12 text-center text-sm text-slate-500">Belum ada event yang ditugaskan.</div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <Link :href="route('admin.users.index')"><SecondaryButton type="button" class="w-full justify-center sm:w-auto">Kembali</SecondaryButton></Link>
                <Link :href="route('admin.users.edit', user.id)"><PrimaryButton type="button" class="w-full justify-center sm:w-auto">Edit User</PrimaryButton></Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

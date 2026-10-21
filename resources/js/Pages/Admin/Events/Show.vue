<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

defineProps({ event: { type: Object, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail ${event.title}`" />
        <template #header>
            <div><h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Detail Event</h1><p class="mt-1 text-sm font-normal text-slate-500">Jadwal, test, dan peserta yang ditugaskan pada event.</p></div>
        </template>
        <Breadcrumb :items="[{ label: 'Event', url: route('admin.events.index') }, { label: event.title }]" />

        <div class="mx-auto space-y-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 bg-gradient-to-r from-white to-slate-50/70 px-4 py-5 sm:px-6 sm:py-6">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" :class="event.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'"><span class="h-1.5 w-1.5 rounded-full" :class="event.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>{{ event.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <h2 class="mt-3 break-words text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ event.title }}</h2>
                        <p class="mt-1 break-words text-sm text-slate-500">{{ event.description || 'Event ujian' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Dibuat {{ formatIndonesiaDateTime(event.created_at, { dateStyle: 'full' }) }}</p>
                    </div>
                </header>
                <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4">
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Mulai</p><p class="mt-1 font-bold text-slate-900">{{ formatIndonesiaDateTime(event.start_time) }}</p></div>
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Selesai</p><p class="mt-1 font-bold text-slate-900">{{ formatIndonesiaDateTime(event.end_time) }}</p></div>
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Test</p><p class="mt-1 font-bold text-slate-900">{{ event.tests_count }} test</p></div>
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Peserta / Sesi</p><p class="mt-1 font-bold text-slate-900">{{ event.participants_count }} peserta · {{ event.sessions_count }} sesi</p></div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 px-4 py-5 sm:px-6"><h2 class="font-bold text-slate-900">Test dalam event</h2><p class="mt-1 text-sm text-slate-500">{{ event.tests_count }} test ditambahkan ke event ini.</p></header>
                <div v-if="event.tests.length" class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
                    <article v-for="test in event.tests" :key="test.id" class="rounded-xl border border-slate-200 p-4"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-700"><i class="fa-solid fa-file-circle-check"></i></div><h3 class="mt-3 font-bold text-slate-900">{{ test.title }}</h3><p class="mt-1 text-sm text-slate-500">{{ test.questions_count }} soal · {{ test.duration_minutes }} menit</p><Link :href="route('admin.exams.show', test.id)" class="mt-3 inline-flex text-xs font-semibold text-blue-700 hover:text-blue-900">Lihat test <i class="fa-solid fa-arrow-right ml-2"></i></Link></article>
                </div>
                <div v-else class="px-6 py-12 text-center text-sm text-slate-500">Belum ada test yang ditambahkan.</div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 px-4 py-5 sm:px-6"><h2 class="font-bold text-slate-900">Peserta ditugaskan</h2><p class="mt-1 text-sm text-slate-500">{{ event.participants_count }} peserta mendapat akses ke event ini.</p></header>
                <div v-if="event.participants.length" class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
                    <article v-for="person in event.participants" :key="person.id" class="flex items-center gap-3 rounded-xl border border-slate-200 p-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-extrabold text-blue-700">{{ person.name.charAt(0).toUpperCase() }}</span><span class="min-w-0"><span class="block truncate text-sm font-bold text-slate-800">{{ person.name }}</span><span class="block truncate text-xs text-slate-500">{{ person.email }}</span></span></article>
                </div>
                <div v-else class="px-6 py-12 text-center text-sm text-slate-500">Belum ada peserta yang ditugaskan.</div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <Link :href="route('admin.events.index')"><SecondaryButton type="button" class="w-full justify-center sm:w-auto">Kembali</SecondaryButton></Link>
                <Link :href="route('admin.events.edit', event.id)"><PrimaryButton type="button" class="w-full justify-center sm:w-auto">Edit Event</PrimaryButton></Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

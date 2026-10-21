<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({ event: { type: Object, required: true } });
const rows = computed(() => props.event.participants.flatMap((participant) => props.event.tests.map((test) => ({
    participant,
    test,
    session: props.event.sessions.find((session) => Number(session.user_id) === Number(participant.id) && Number(session.test_id) === Number(test.id)),
}))));
const finishedCount = computed(() => props.event.sessions.filter((session) => session.status === 'finished').length);

function formatScore(value) {
    return value === null ? '—' : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Rekap ${event.title}`" />
        <template #header>
            <div><h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Rekap Event</h1><p class="mt-1 text-sm text-slate-500">Ringkasan hasil seluruh peserta dan test dalam event.</p></div>
        </template>
        <Breadcrumb :items="[{ label: 'Rekap Hasil', url: route('admin.reports.index') }, { label: event.title }]" />

        <div class="mx-auto space-y-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 bg-gradient-to-r from-white to-slate-50/70 px-4 py-5 sm:px-6 sm:py-6">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" :class="event.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'"><span class="h-1.5 w-1.5 rounded-full" :class="event.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>{{ event.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    <h2 class="mt-3 break-words text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ event.title }}</h2>
                    <p class="mt-1 break-words text-sm text-slate-500">{{ event.description || 'Event ujian' }}</p>
                    <p class="mt-2 text-sm leading-5 text-slate-500">{{ formatIndonesiaDateTime(event.start_time) }} sampai {{ formatIndonesiaDateTime(event.end_time) }}</p>
                </header>
                <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4">
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Test</p><p class="mt-1 font-bold text-slate-900">{{ event.tests_count }} test</p></div>
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Peserta</p><p class="mt-1 font-bold text-slate-900">{{ event.participants_count }} peserta</p></div>
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Sesi ujian</p><p class="mt-1 font-bold text-slate-900">{{ event.sessions_count }} sesi</p></div>
                    <div class="px-4 py-4 sm:px-6 sm:py-5"><p class="text-xs font-medium text-slate-500">Sesi selesai</p><p class="mt-1 font-bold text-slate-900">{{ finishedCount }} sesi</p></div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 px-5 py-5 sm:px-6"><h2 class="font-bold text-slate-900">Hasil Peserta</h2><p class="mt-1 text-sm text-slate-500">{{ rows.length }} penugasan peserta dan test dalam event ini.</p></header>
                <div class="divide-y divide-slate-100 2xl:hidden">
                    <article v-for="row in rows" :key="`card-${row.participant.id}-${row.test.id}`" class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0"><p class="break-words font-semibold text-slate-900">{{ row.participant.name }}</p><p class="break-all text-xs text-slate-500">{{ row.participant.email }}</p></div>
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold" :class="!row.session ? 'bg-slate-100 text-slate-600' : row.session.status === 'finished' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"><span class="h-1.5 w-1.5 rounded-full" :class="!row.session ? 'bg-slate-400' : row.session.status === 'finished' ? 'bg-emerald-500' : 'bg-amber-500'"></span>{{ !row.session ? 'Belum mulai' : row.session.status === 'finished' ? 'Selesai' : 'Berlangsung' }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3"><p class="break-words text-sm font-semibold text-slate-800">{{ row.test.title }}</p><p class="mt-1 text-xs text-slate-500">{{ row.test.questions_count }} soal</p></div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg border border-slate-100 p-2"><p class="text-[11px] text-slate-500">Jawaban</p><p class="mt-1 text-sm font-bold text-slate-800">{{ row.session ? `${row.session.answered_count} / ${row.test.questions_count}` : '—' }}</p></div>
                            <div class="rounded-lg border border-slate-100 p-2"><p class="text-[11px] text-slate-500">Benar</p><p class="mt-1 text-sm font-bold text-emerald-700">{{ row.session ? row.session.correct_count : '—' }}</p></div>
                            <div class="rounded-lg border border-slate-100 p-2"><p class="text-[11px] text-slate-500">Nilai</p><p class="mt-1 text-sm font-extrabold text-slate-800">{{ row.session?.status === 'finished' ? formatScore(row.session.total_score) : '—' }}</p></div>
                        </div>
                        <Link v-if="row.session" :href="route('admin.reports.session', row.session.id)" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-700"><i class="fa-regular fa-eye"></i>Detail sesi</Link>
                        <p v-else class="text-center text-xs text-slate-400">Belum ada sesi ujian</p>
                    </article>
                    <div v-if="rows.length === 0" class="p-6 text-center text-sm text-slate-500">Event ini belum memiliki daftar test atau peserta.</div>
                </div>
                <div class="hidden overflow-x-auto 2xl:block">
                    <table class="w-full min-w-[1050px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-4">Peserta</th><th class="px-4 py-4">Test</th><th class="px-4 py-4 text-center">Jawaban</th><th class="px-4 py-4 text-center">Benar</th><th class="px-4 py-4 text-center">Nilai</th><th class="px-4 py-4">Status</th><th class="px-5 py-4 text-right">Aksi</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="row in rows" :key="`${row.participant.id}-${row.test.id}`" class="hover:bg-slate-50/70">
                                <td class="px-5 py-4"><p class="font-semibold text-slate-900">{{ row.participant.name }}</p><p class="mt-1 text-xs text-slate-500">{{ row.participant.email }}</p></td>
                                <td class="px-4 py-4"><p class="font-semibold text-slate-800">{{ row.test.title }}</p><p class="mt-1 text-xs text-slate-500">{{ row.test.questions_count }} soal</p></td>
                                <td class="px-4 py-4 text-center"><template v-if="row.session"><span class="font-bold text-slate-700">{{ row.session.answered_count }}</span><span class="text-slate-400"> / {{ row.test.questions_count }}</span></template><span v-else class="text-slate-400">—</span></td>
                                <td class="px-4 py-4 text-center"><span v-if="row.session" class="inline-flex min-w-9 justify-center rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ row.session.correct_count }}</span><span v-else class="text-slate-400">—</span></td>
                                <td class="px-4 py-4 text-center font-extrabold text-slate-800">{{ row.session?.status === 'finished' ? formatScore(row.session.total_score) : '—' }}</td>
                                <td class="px-4 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="!row.session ? 'bg-slate-100 text-slate-600' : row.session.status === 'finished' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"><span class="h-1.5 w-1.5 rounded-full" :class="!row.session ? 'bg-slate-400' : row.session.status === 'finished' ? 'bg-emerald-500' : 'bg-amber-500'"></span>{{ !row.session ? 'Belum mulai' : row.session.status === 'finished' ? 'Selesai' : 'Berlangsung' }}</span></td>
                                <td class="px-5 py-4 text-right"><Link v-if="row.session" :href="route('admin.reports.session', row.session.id)" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"><i class="fa-regular fa-eye"></i><span>Detail</span></Link><span v-else class="text-xs text-slate-300">Belum ada detail</span></td>
                            </tr>
                            <tr v-if="rows.length === 0"><td colspan="7" class="px-5 py-14 text-center text-sm text-slate-500">Event ini belum memiliki daftar test atau peserta.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <Link :href="route('admin.reports.index')"><SecondaryButton type="button" class="w-full justify-center sm:w-auto">Kembali</SecondaryButton></Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

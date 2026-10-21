<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({
    exam: { type: Object, required: true },
    session: { type: Object, required: true },
    summary: { type: Object, required: true },
    event: { type: Object, default: null },
});

const scoreLabel = computed(() => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(props.summary.score));
const maxScoreLabel = computed(() => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(props.summary.max_score));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Hasil ${exam.title}`" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Hasil Ujian</h1>
                <p class="mt-1 text-sm text-slate-500">Ringkasan pengerjaan ujian Anda.</p>
            </div>
        </template>

        <Breadcrumb :items="[
            { label: 'Daftar Ujian', url: route('student.exams.index') },
            { label: event?.title ?? 'Hasil Ujian' },
        ]" />

        <div class="mx-auto min-w-0 space-y-4 sm:space-y-5">
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-blue-950 to-blue-700 px-4 py-6 text-white shadow-lg shadow-blue-950/15 sm:rounded-3xl sm:px-9 sm:py-10">
                <div class="pointer-events-none absolute -right-12 -top-20 h-64 w-64 rounded-full border-[32px] border-white/5"></div>
                <div class="relative flex flex-col gap-8 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0 max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1.5 text-xs font-bold text-emerald-100">
                            <i class="fa-solid fa-circle-check"></i>Ujian telah diselesaikan
                        </span>
                        <h2 class="mt-4 break-words text-2xl font-extrabold tracking-tight sm:mt-5 sm:text-3xl">{{ exam.title }}</h2>
                        <p class="mt-2 break-words text-xs text-blue-100 sm:text-sm">Diselesaikan pada {{ formatIndonesiaDateTime(session.updated_at, { dateStyle: 'full', timeStyle: 'short' }) }}</p>
                    </div>
                    <div class="flex min-w-0 shrink-0 items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur-sm sm:gap-4 sm:px-5 sm:py-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 text-xl text-amber-200"><i class="fa-solid fa-award"></i></span>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-blue-100">Nilai akhir</p>
                            <p class="mt-0.5 break-words text-xl font-black sm:text-2xl">{{ scoreLabel }}<span class="text-sm font-bold text-blue-100"> / {{ maxScoreLabel }}</span></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Benar</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-check"></i></span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-emerald-700">{{ summary.correct }}</p>
                    <p class="mt-1 text-xs text-slate-400">jawaban benar</p>
                </article>
                <article class="rounded-2xl border border-red-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Salah</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-500"><i class="fa-solid fa-xmark"></i></span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-red-600">{{ summary.incorrect }}</p>
                    <p class="mt-1 text-xs text-slate-400">jawaban keliru</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Kosong</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500"><i class="fa-regular fa-circle"></i></span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-slate-700">{{ summary.unanswered }}</p>
                    <p class="mt-1 text-xs text-slate-400">belum dijawab</p>
                </article>
                <article class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Terjawab</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fa-solid fa-list-check"></i></span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-blue-700">{{ summary.answered }}<span class="text-base font-bold text-slate-400"> / {{ summary.total_questions }}</span></p>
                    <p class="mt-1 text-xs text-slate-400">soal terisi</p>
                </article>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <span class="font-bold text-slate-800">Pencapaian nilai</span>
                            <span class="font-extrabold text-blue-700">{{ summary.percentage }}%</span>
                        </div>
                        <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-emerald-500 transition-all" :style="{ width: `${Math.min(summary.percentage, 100)}%` }"></div>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Perbandingan nilai yang diperoleh dengan total bobot seluruh soal.</p>
                    </div>
                    <div class="w-full sm:w-auto">
                        <Link :href="route('student.exams.index')" class="block w-full sm:w-auto"><PrimaryButton type="button" class="w-full justify-center sm:w-auto"><i class="fa-solid fa-list mr-2"></i>Kembali ke Daftar Ujian</PrimaryButton></Link>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

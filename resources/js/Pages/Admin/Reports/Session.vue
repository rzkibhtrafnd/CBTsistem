<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({
    session: { type: Object, required: true },
});

const answers = computed(() => props.session.answers ?? []);
const questions = computed(() => props.session.test.questions ?? []);
const answeredCount = computed(() => answers.value.filter((answer) => answer.choice_id !== null).length);
const correctCount = computed(() => answers.value.filter((answer) => answer.choice?.is_correct).length);
const unansweredCount = computed(() => answers.value.length - answeredCount.value);
const incorrectCount = computed(() => answeredCount.value - correctCount.value);
const scoreLabel = computed(() => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(props.session.total_score ?? 0)));
const maxScore = computed(() => questions.value.reduce((total, question) => total + Number(question.score_weight), 0));

function answerFor(question) {
    return answers.value.find((answer) => answer.question_id === question.id);
}

function correctChoice(question) {
    return question.choices.find((choice) => choice.is_correct);
}

function questionStatus(question) {
    const answer = answerFor(question);
    if (!answer?.choice_id) return 'Belum dijawab';
    return answer.choice?.is_correct ? 'Benar' : 'Salah';
}

function formatScore(value) {
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Rekap ${session.test.title} - ${session.user.name}`" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Detail Rekap Peserta</h1>
                <p class="mt-1 text-sm text-slate-500">Hasil sesi dan rincian jawaban peserta.</p>
            </div>
        </template>

        <Breadcrumb :items="[
            { label: 'Rekap Hasil', url: route('admin.reports.index') },
            { label: session.event.title, url: route('admin.reports.show', session.event.id) },
            { label: 'Detail Peserta' },
        ]" />

        <div class="mx-auto space-y-5">
            <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-blue-950 to-blue-700 px-4 py-6 text-white shadow-lg shadow-blue-950/15 sm:px-9 sm:py-9">
                <div class="pointer-events-none absolute -right-12 -top-20 -z-10 h-64 w-64 rounded-full border-[34px] border-white/5"></div>
                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold" :class="session.status === 'finished' ? 'border-emerald-200/20 bg-emerald-300/10 text-emerald-100' : 'border-amber-200/20 bg-amber-300/10 text-amber-100'">
                            <i :class="session.status === 'finished' ? 'fa-solid fa-circle-check' : 'fa-solid fa-hourglass-half'"></i>
                            {{ session.status === 'finished' ? 'Ujian selesai' : 'Sedang dikerjakan' }}
                        </span>
                        <h2 class="mt-4 break-words text-2xl font-black tracking-tight sm:text-3xl">{{ session.user.name }}</h2>
                        <p class="break-all text-sm text-blue-100">{{ session.user.email }}</p>
                        <p class="mt-4 flex items-start gap-2 break-words text-sm font-semibold text-blue-50"><i class="fa-solid fa-file-circle-check mt-0.5 shrink-0 text-blue-200"></i><span>{{ session.event.title }} · {{ session.test.title }}</span></p>
                    </div>
                    <div class="shrink-0 rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur-sm">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-blue-100">Nilai akhir</p>
                        <p class="mt-1 text-3xl font-black">{{ session.status === 'finished' ? scoreLabel : '—' }}<span v-if="session.status === 'finished'" class="ml-1 text-sm font-bold text-blue-100">/ {{ formatScore(maxScore) }}</span></p>
                        <p class="mt-1 text-xs text-blue-200">{{ session.status === 'finished' ? 'Bobot nilai seluruh soal' : 'Nilai tersedia setelah ujian selesai' }}</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wide text-slate-500">Dijawab</span><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fa-solid fa-list-check"></i></span></div>
                    <p class="mt-4 text-3xl font-black text-blue-700">{{ answeredCount }}<span class="text-base font-bold text-slate-400"> / {{ questions.length }}</span></p>
                </article>
                <article class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wide text-slate-500">Benar</span><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-check"></i></span></div>
                    <p class="mt-4 text-3xl font-black text-emerald-700">{{ correctCount }}</p>
                </article>
                <article class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wide text-slate-500">Salah</span><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-500"><i class="fa-solid fa-xmark"></i></span></div>
                    <p class="mt-4 text-3xl font-black text-red-600">{{ incorrectCount }}</p>
                </article>
                <article class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wide text-slate-500">Kosong</span><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-regular fa-circle"></i></span></div>
                    <p class="mt-4 text-3xl font-black text-amber-600">{{ unansweredCount }}</p>
                </article>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="font-extrabold text-slate-900">Rincian Jawaban</h2>
                        <p class="mt-1 text-sm text-slate-500">Jawaban peserta dibandingkan dengan kunci jawaban.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="rounded-lg bg-slate-100 px-3 py-2 font-semibold text-slate-600"><i class="fa-regular fa-clock mr-1.5"></i>Mulai {{ formatIndonesiaDateTime(session.start_time, { dateStyle: 'medium', timeStyle: 'short' }) }}</span>
                        <span v-if="session.status === 'finished'" class="rounded-lg bg-emerald-50 px-3 py-2 font-semibold text-emerald-700"><i class="fa-solid fa-flag-checkered mr-1.5"></i>Selesai {{ formatIndonesiaDateTime(session.updated_at, { dateStyle: 'medium', timeStyle: 'short' }) }}</span>
                    </div>
                </header>

                <div v-if="questions.length" class="space-y-4 p-5 sm:p-6">
                    <article v-for="(question, index) in questions" :key="question.id" class="overflow-hidden rounded-2xl border border-slate-200">
                        <header class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/70 px-4 py-4 sm:flex-row sm:items-start sm:justify-between sm:px-5">
                            <div class="flex gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-sm font-extrabold text-slate-600 shadow-sm">{{ index + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="whitespace-pre-line font-semibold leading-6 text-slate-900">{{ question.question_text }}</p>
                                    <p class="mt-1 text-xs text-slate-400">Bobot {{ question.score_weight }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2 pl-11 sm:pl-0">
                                <span v-if="answerFor(question)?.is_doubtful" class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700"><i class="fa-solid fa-flag mr-1"></i>Ragu</span>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold" :class="questionStatus(question) === 'Benar' ? 'bg-emerald-50 text-emerald-700' : questionStatus(question) === 'Salah' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600'">{{ questionStatus(question) }}</span>
                            </div>
                        </header>

                        <div class="space-y-2 p-4 sm:p-5">
                            <div v-for="(choice, choiceIndex) in question.choices" :key="choice.id" class="flex items-start gap-3 rounded-xl border px-3.5 py-3" :class="[
                                choice.is_correct ? 'border-emerald-200 bg-emerald-50/60' : answerFor(question)?.choice_id === choice.id ? 'border-red-200 bg-red-50/60' : 'border-slate-100 bg-white',
                            ]">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[11px] font-extrabold" :class="choice.is_correct ? 'bg-emerald-100 text-emerald-700' : answerFor(question)?.choice_id === choice.id ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500'">{{ String.fromCharCode(65 + choiceIndex) }}</span>
                                <span class="min-w-0 flex-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ choice.choice_text }}</span>
                                <span v-if="choice.is_correct" class="shrink-0 text-[10px] font-extrabold uppercase tracking-wide text-emerald-700"><i class="fa-solid fa-key mr-1"></i>Kunci</span>
                                <span v-if="answerFor(question)?.choice_id === choice.id" class="shrink-0 text-[10px] font-extrabold uppercase tracking-wide" :class="choice.is_correct ? 'text-emerald-700' : 'text-red-600'"><i class="fa-solid fa-user-check mr-1"></i>Dipilih</span>
                            </div>
                            <p v-if="!answerFor(question)?.choice_id" class="pt-1 text-xs font-medium text-slate-400">Peserta tidak memilih jawaban untuk soal ini.</p>
                        </div>
                    </article>
                </div>

                <div v-else class="px-6 py-14 text-center text-sm text-slate-500">Ujian ini belum memiliki soal.</div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <Link :href="route('admin.reports.show', session.event.id)"><SecondaryButton type="button" class="w-full justify-center sm:w-auto">Kembali</SecondaryButton></Link>
                <Link :href="route('admin.exams.show', session.test.id)"><PrimaryButton type="button" class="w-full justify-center sm:w-auto">Lihat Test</PrimaryButton></Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

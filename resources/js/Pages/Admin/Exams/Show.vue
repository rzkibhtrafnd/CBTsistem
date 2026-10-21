<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({
    exam: { type: Object, required: true },
});

const statusLabel = computed(() => {
    if (!props.exam.is_active) return 'Nonaktif';
    return 'Siap dipakai';
});

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail ${exam.title}`" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Detail Test</h1>
                <p class="mt-1 text-sm font-normal text-slate-500">Informasi test dan ringkasan bank soal.</p>
            </div>
        </template>

        <Breadcrumb :items="[
            { label: 'Test', url: route('admin.exams.index') },
            { label: exam.title },
        ]" />

        <div class="mx-auto space-y-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-5 border-b border-slate-100 bg-gradient-to-r from-white to-slate-50/70 px-6 py-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" :class="exam.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="exam.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                            {{ statusLabel }}
                        </span>
                        <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ exam.title }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ exam.description || 'Bank soal test' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Dibuat {{ formatIndonesiaDateTime(exam.created_at, { dateStyle: 'full' }) }}</p>
                    </div>
                </header>

                <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div class="flex items-center gap-4 px-6 py-5">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fa-regular fa-clock"></i></span>
                        <div><p class="text-xs font-medium text-slate-500">Durasi</p><p class="mt-1 font-bold text-slate-900">{{ exam.duration_minutes }} menit</p></div>
                    </div>
                    <div class="flex items-center gap-4 px-6 py-5">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fa-regular fa-file-lines"></i></span>
                        <div><p class="text-xs font-medium text-slate-500">Jumlah soal</p><p class="mt-1 font-bold text-slate-900">{{ exam.questions_count }} soal</p></div>
                    </div>
                    <div class="flex items-center gap-4 px-6 py-5">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-users"></i></span>
                        <div><p class="text-xs font-medium text-slate-500">Event terkait</p><p class="mt-1 font-bold text-slate-900">{{ exam.events_count }} event</p></div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><i class="fa-regular fa-calendar"></i></span>
                    <div>
                        <h2 class="font-bold text-slate-900">Event Terkait</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Jadwal dan penugasan peserta diatur pada event.</p>
                    </div>
                </div>

                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Event</dt>
                        <dd class="mt-2 font-semibold text-slate-800">{{ exam.events_count ?? 0 }} event</dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status Test</dt>
                        <dd class="mt-2 font-semibold text-slate-800">{{ exam.is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                    </div>
                </dl>

            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900">Daftar Soal</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ exam.questions_count }} soal dalam test ini.</p>
                    </div>
                </header>

                <div v-if="exam.questions.length" class="divide-y divide-slate-100">
                    <article v-for="(question, questionIndex) in exam.questions" :key="question.id" class="px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-xs font-bold text-violet-700">{{ questionIndex + 1 }}</span>
                                <div>
                                    <p class="whitespace-pre-line font-semibold leading-6 text-slate-900">{{ question.question_text }}</p>
                                    <p class="mt-1 text-xs text-slate-400">Bobot {{ question.score_weight }} · {{ question.answers_count }} jawaban peserta</p>
                                </div>
                            </div>
                        </div>

                        <ol class="mt-4 grid gap-2 sm:ml-11 sm:grid-cols-2">
                            <li v-for="(choice, choiceIndex) in question.choices" :key="choice.id" class="flex items-start gap-2.5 rounded-lg border px-3 py-2.5" :class="choice.is_correct ? 'border-emerald-200 bg-emerald-50/60' : 'border-slate-100 bg-slate-50/60'">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[11px] font-bold" :class="choice.is_correct ? 'bg-emerald-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200'">{{ String.fromCharCode(65 + choiceIndex) }}</span>
                                <span class="whitespace-pre-line flex-1 pt-0.5 text-sm" :class="choice.is_correct ? 'font-medium text-emerald-900' : 'text-slate-700'">{{ choice.choice_text }}</span>
                                <i v-if="choice.is_correct" class="fa-solid fa-check mt-1 text-xs text-emerald-600" title="Jawaban benar"></i>
                            </li>
                        </ol>
                    </article>
                </div>
                <div v-else class="px-6 py-12 text-center">
                    <i class="fa-regular fa-circle-question text-2xl text-slate-300"></i>
                    <p class="mt-3 font-semibold text-slate-700">Belum ada soal</p>
                    <p class="mt-1 text-sm text-slate-500">Tambahkan soal melalui menu edit ujian.</p>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <Link :href="route('admin.exams.index')"><SecondaryButton type="button" class="w-full justify-center sm:w-auto">Kembali</SecondaryButton></Link>
                <Link :href="route('admin.exams.edit', exam.id)"><PrimaryButton type="button" class="w-full justify-center sm:w-auto">Edit</PrimaryButton></Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

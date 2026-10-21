<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({
    exam: { type: Object, required: true },
    session: { type: Object, required: true },
});

const answers = reactive(Object.fromEntries(props.session.answers.map((answer) => [answer.question_id, {
    choice_id: answer.choice_id,
    is_doubtful: answer.is_doubtful,
    saving: false,
    saved: Boolean(answer.choice_id) || answer.is_doubtful,
    saveVersion: 0,
    pendingPayload: null,
}])));
const currentQuestionIndex = ref(0);
const remainingSeconds = ref(0);
const finishForm = useForm({});
let timer;
let finishRequested = false;

const currentQuestion = computed(() => props.exam.questions[currentQuestionIndex.value]);
const currentAnswer = computed(() => answers[currentQuestion.value?.id]);
const answeredCount = computed(() => Object.values(answers).filter((answer) => answer.choice_id !== null).length);
const doubtfulCount = computed(() => Object.values(answers).filter((answer) => answer.is_doubtful).length);
const timeLabel = computed(() => {
    const minutes = Math.floor(remainingSeconds.value / 60);
    const seconds = remainingSeconds.value % 60;
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

function questionStatus(question) {
    const answer = answers[question.id];
    if (answer.is_doubtful) return 'doubtful';
    if (answer.choice_id !== null) return 'answered';
    return 'unanswered';
}

function statusClass(question) {
    return {
        answered: 'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700',
        doubtful: 'border-amber-400 bg-amber-300 text-amber-950 hover:bg-amber-400',
        unanswered: 'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:bg-blue-50',
    }[questionStatus(question)];
}

function saveAnswer(question) {
    const answer = answers[question.id];
    answer.saveVersion++;
    answer.pendingPayload = {
        choice_id: answer.choice_id,
        is_doubtful: answer.is_doubtful,
    };
    answer.saved = false;
    if (!answer.saving) sendPendingAnswer(question, answer);
}

function sendPendingAnswer(question, answer) {
    const version = answer.saveVersion;
    const payload = answer.pendingPayload;
    answer.pendingPayload = null;
    answer.saving = true;

    router.patch(route('student.exams.answers.update', { event: props.session.event_id, exam: props.exam.id, question: question.id }), {
        ...payload,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (answer.saveVersion === version) answer.saved = true;
        },
        onFinish: () => {
            if (answer.pendingPayload) {
                sendPendingAnswer(question, answer);
                return;
            }

            answer.saving = false;
        },
    });
}

function setChoice(question, choiceId) {
    const answer = answers[question.id];
    if (answer.choice_id === choiceId) return;

    answer.choice_id = choiceId;
    saveAnswer(question);
}

function setDoubtful(question, value) {
    const answer = answers[question.id];
    if (answer.is_doubtful === value) return;

    answer.is_doubtful = value;
    saveAnswer(question);
}

function updateTimer() {
    remainingSeconds.value = Math.max(0, Math.floor((new Date(props.session.end_time).getTime() - Date.now()) / 1000));
    if (remainingSeconds.value === 0) finishExam(true);
}

function finishExam(expired = false) {
    if (finishRequested || finishForm.processing) return;
    if (!expired && !window.confirm('Akhiri ujian sekarang?')) return;

    finishRequested = true;
    finishForm.post(route('student.exams.finish', { event: props.session.event_id, exam: props.exam.id }), {
        onFinish: () => { finishRequested = false; },
    });
}

onMounted(() => {
    updateTimer();
    timer = window.setInterval(updateTimer, 1000);
});
onUnmounted(() => window.clearInterval(timer));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Mengerjakan ${exam.title}`" />
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Mengerjakan Ujian</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ exam.title }}</p>
                </div>
            </div>
        </template>

        <Breadcrumb :items="[
            { label: 'Daftar Ujian', url: route('student.exams.index') },
            { label: exam.title, url: route('student.exams.lobby', { event: session.event_id, exam: exam.id }) },
            { label: 'Pengerjaan' },
        ]" />

        <div class="mx-auto w-full max-w-none space-y-4 sm:space-y-5">
            <section class="grid gap-2.5 sm:grid-cols-3 sm:gap-3">
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Soal terjawab</p>
                    <p class="mt-1 text-lg font-extrabold text-emerald-700">{{ answeredCount }} <span class="text-sm font-medium text-slate-400">/ {{ exam.questions.length }}</span></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Ditandai ragu</p>
                    <p class="mt-1 text-lg font-extrabold text-amber-600">{{ doubtfulCount }} <span class="text-sm font-medium text-slate-400">soal</span></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Batas sesi</p>
                    <p class="mt-1 text-sm font-bold text-slate-700">{{ formatIndonesiaDateTime(session.end_time, { dateStyle: 'medium' }) }}</p>
                </div>
            </section>

            <div class="sticky top-16 z-[5] flex items-center justify-between gap-3 rounded-xl border border-blue-200 bg-blue-950 px-4 py-3 text-white shadow-lg shadow-blue-950/10 xl:hidden">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Sisa waktu</p>
                    <p class="font-mono text-xl font-extrabold tabular-nums">{{ timeLabel }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Progres</p>
                    <p class="text-sm font-bold">{{ answeredCount }} / {{ exam.questions.length }} soal</p>
                </div>
            </div>

            <div class="grid w-full min-w-0 items-start gap-4 xl:grid-cols-[minmax(0,1fr)_320px] xl:gap-6">
                <main v-if="currentQuestion" class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-7">
                        <span class="rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-extrabold text-blue-700">Soal {{ currentQuestionIndex + 1 }}</span>
                        <span class="text-xs font-medium text-slate-400">Bobot {{ currentQuestion.score_weight }}</span>
                    </header>

                    <div class="px-4 py-5 sm:px-7 sm:py-8">
                        <h2 class="whitespace-pre-line text-base font-semibold leading-7 text-slate-900 sm:text-lg">{{ currentQuestion.question_text }}</h2>

                        <fieldset class="mt-6 space-y-3">
                            <legend class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Pilih satu jawaban</legend>
                            <label v-for="(choice, choiceIndex) in currentQuestion.choices" :key="choice.id" class="flex cursor-pointer items-start gap-2.5 rounded-xl border px-3 py-3.5 transition sm:gap-3 sm:px-4" :class="currentAnswer.choice_id === choice.id ? 'border-blue-300 bg-blue-50/70 ring-1 ring-blue-100' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                                <input :checked="currentAnswer.choice_id === choice.id" type="radio" :name="`question-${currentQuestion.id}`" class="mt-1 border-slate-300 text-blue-600 focus:ring-blue-500" @change="setChoice(currentQuestion, choice.id)" />
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-extrabold text-slate-600">{{ String.fromCharCode(65 + choiceIndex) }}</span>
                                <span class="min-w-0 flex-1 break-words whitespace-pre-line pt-0.5 text-sm leading-6 text-slate-700">{{ choice.choice_text }}</span>
                            </label>
                        </fieldset>

                        <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-600">
                                <input :checked="currentAnswer.is_doubtful" type="checkbox" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500" @change="setDoubtful(currentQuestion, $event.target.checked)" />
                                Tandai ragu-ragu
                            </label>
                            <span v-if="currentAnswer.saving" class="text-xs font-semibold text-blue-600"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Menyimpan jawaban...</span>
                            <span v-else-if="currentAnswer.saved" class="text-xs font-semibold text-emerald-700"><i class="fa-solid fa-check mr-1"></i>Jawaban tersimpan otomatis</span>
                            <span v-else class="text-xs text-slate-400">Pilih jawaban untuk menyimpan otomatis</span>
                        </div>
                    </div>

                    <footer class="flex items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/70 px-3 py-4 sm:px-7">
                        <SecondaryButton type="button" class="min-w-0 flex-1 justify-center !px-2 !text-[10px] sm:flex-none sm:!px-4 sm:!text-xs" :disabled="currentQuestionIndex === 0" @click="currentQuestionIndex--">
                            <i class="fa-solid fa-arrow-left mr-2"></i>Sebelumnya
                        </SecondaryButton>
                        <span class="text-xs font-semibold text-slate-400">{{ currentQuestionIndex + 1 }} dari {{ exam.questions.length }}</span>
                        <PrimaryButton type="button" class="min-w-0 flex-1 justify-center !px-2 !text-[10px] sm:flex-none sm:!px-4 sm:!text-xs" :disabled="currentQuestionIndex === exam.questions.length - 1" @click="currentQuestionIndex++">
                            Selanjutnya<i class="fa-solid fa-arrow-right ml-2"></i>
                        </PrimaryButton>
                    </footer>
                </main>

                <aside class="min-w-0 space-y-4 xl:sticky xl:top-5">
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-200/60">
                        <header class="bg-gradient-to-br from-slate-900 via-blue-900 to-blue-700 px-5 py-5 text-white">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-200">Lembar jawaban</p>
                                    <h2 class="mt-1 text-lg font-extrabold tracking-tight">Navigasi soal</h2>
                                </div>
                                <span class="flex h-12 min-w-12 items-center justify-center rounded-xl border border-white/15 bg-white/10 px-2 font-mono text-lg font-extrabold shadow-inner">{{ timeLabel }}</span>
                            </div>
                            <div class="mt-4 flex items-center justify-between rounded-xl border border-white/10 bg-white/10 px-3 py-2.5">
                                <span class="text-xs font-medium text-blue-100">Progres jawaban</span>
                                <span class="text-sm font-extrabold">{{ answeredCount }}<span class="font-medium text-blue-200"> / {{ exam.questions.length }}</span></span>
                            </div>
                        </header>

                        <div class="p-5">
                            <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Pilih nomor soal</p>
                            <div class="grid grid-cols-5 gap-2 sm:grid-cols-7 xl:grid-cols-5">
                                <button v-for="(question, index) in exam.questions" :key="question.id" type="button" class="flex aspect-square min-h-10 items-center justify-center rounded-xl border text-xs font-extrabold shadow-sm transition duration-150 hover:-translate-y-0.5 hover:shadow-md sm:text-sm" :class="[
                                    statusClass(question),
                                    currentQuestionIndex === index ? 'ring-2 ring-blue-500 ring-offset-2 ring-offset-white' : '',
                                ]" :aria-label="`Buka soal ${index + 1}`" :aria-current="currentQuestionIndex === index ? 'step' : undefined" @click="currentQuestionIndex = index">
                                    {{ index + 1 }}
                                </button>
                            </div>

                            <div class="mt-5 space-y-2.5 rounded-xl bg-slate-50 p-3.5 text-xs font-semibold text-slate-600">
                                <div class="flex items-center gap-2.5"><span class="h-4 w-4 rounded-md border border-emerald-600 bg-emerald-500 shadow-sm shadow-emerald-200"></span>Sudah dijawab</div>
                                <div class="flex items-center gap-2.5"><span class="h-4 w-4 rounded-md border border-amber-400 bg-amber-300 shadow-sm shadow-amber-200"></span>Ditandai ragu</div>
                                <div class="flex items-center gap-2.5"><span class="h-4 w-4 rounded-md border border-slate-200 bg-white shadow-sm"></span>Belum dijawab</div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
                        <h2 class="font-bold text-slate-900">Selesai mengerjakan?</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Jawaban dan penanda ragu disimpan otomatis saat dipilih.
                        </p>

                        <div class="mt-4 space-y-2">
                            <PrimaryButton 
                                type="button" 
                                class="w-full justify-center bg-red-600 py-2.5 text-xs font-semibold uppercase tracking-widest text-white hover:bg-red-700 focus:bg-red-700 active:bg-red-800" 
                                :disabled="finishForm.processing" 
                                @click="finishExam()"
                            >
                                {{ finishForm.processing ? 'Mengakhiri...' : 'Akhiri Ujian' }}
                            </PrimaryButton>

                            <Link 
                                :href="route('student.exams.index')" 
                                class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                            >
                                Keluar Halaman
                            </Link>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

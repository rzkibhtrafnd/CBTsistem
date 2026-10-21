<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    exam: { type: Object, default: null },
});

const isEditing = computed(() => props.exam !== null);

function blankQuestion() {
    return {
        question_text: '',
        score_weight: 1,
        choices: [
            { choice_text: '', is_correct: true },
            { choice_text: '', is_correct: false },
            { choice_text: '', is_correct: false },
            { choice_text: '', is_correct: false },
        ],
    };
}

const form = useForm({
    title: props.exam?.title ?? '',
    duration_minutes: props.exam?.duration_minutes ?? 60,
    is_active: props.exam?.is_active ?? false,
    description: props.exam?.description ?? '',
    questions: props.exam?.questions?.map((question) => ({
        id: question.id,
        question_text: question.question_text,
        score_weight: question.score_weight,
        has_answers: question.answers_count > 0,
        choices: question.choices.map((choice) => ({
            id: choice.id,
            choice_text: choice.choice_text,
            is_correct: Boolean(choice.is_correct),
            has_answers: choice.answers_count > 0,
        })),
    })) ?? [blankQuestion()],
});

function addQuestion() {
    if (form.questions.length < 100) form.questions.push(blankQuestion());
}

function removeQuestion(index) {
    const question = form.questions[index];
    if (question.has_answers) return;
    if (form.questions.length > 1) form.questions.splice(index, 1);
}

function addChoice(question) {
    if (question.choices.length < 8) question.choices.push({ choice_text: '', is_correct: false });
}

function removeChoice(question, index) {
    const choice = question.choices[index];
    if (choice.has_answers || question.choices.length <= 2) return;
    question.choices.splice(index, 1);
}

function setCorrectChoice(question, index) {
    question.choices.forEach((choice, choiceIndex) => {
        choice.is_correct = choiceIndex === index;
    });
}

function questionError(questionIndex, field) {
    return form.errors[`questions.${questionIndex}.${field}`];
}

function choiceError(questionIndex, choiceIndex) {
    return form.errors[`questions.${questionIndex}.choices.${choiceIndex}.choice_text`];
}

function submit() {
    if (isEditing.value) {
        form.put(route('admin.exams.update', props.exam.id));
        return;
    }

    form.post(route('admin.exams.store'));
}
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-6 py-5">
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="font-bold text-slate-900">{{ isEditing ? 'Pengaturan Test' : 'Informasi Test Baru' }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Susun bank soal di test; jadwal dan penugasan peserta diatur melalui event.</p>
                </div>

            </div>
        </header>

        <form class="p-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <InputLabel for="title" value="Nama test" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1.5 block w-full"
                        required
                        maxlength="255"
                        autofocus
                        placeholder="Contoh: Matematika Dasar"
                    />
                    <InputError class="mt-1.5" :message="form.errors.title" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="description" value="Deskripsi (opsional)" />
                    <textarea id="description" v-model="form.description" rows="3" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <InputError class="mt-1.5" :message="form.errors.description" />
                </div>

                <div>
                    <InputLabel for="duration_minutes" value="Durasi pengerjaan" />
                    <div class="relative mt-1.5">
                        <TextInput
                            id="duration_minutes"
                            v-model="form.duration_minutes"
                            type="number"
                            class="block w-full pr-20"
                            required
                            min="1"
                            max="1440"
                            placeholder="60"
                        />
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-slate-400">menit</span>
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.duration_minutes" />
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                        <label for="is_active" class="text-sm font-semibold text-slate-800">Test aktif</label>
                        <p class="mt-0.5 text-xs text-slate-500">Test aktif dapat dipilih saat membuat event.</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input id="is_active" v-model="form.is_active" type="checkbox" class="peer sr-only" />
                        <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-100"></span>
                        <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </label>
                    <InputError :message="form.errors.is_active" />
                </div>

            </div>

            <section class="mt-8 border-t border-slate-100 pt-7">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <i class="fa-solid fa-list-ol"></i>
                        </span>
                        <div>
                            <h2 class="font-bold text-slate-900">Daftar Soal</h2>
                            <p class="mt-0.5 text-sm text-slate-500">{{ form.questions.length }} soal · setiap soal memiliki pilihan dan satu kunci jawaban.</p>
                        </div>
                    </div>
                    <button type="button" :disabled="form.questions.length >= 100" class="inline-flex items-center justify-center gap-2 self-start rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-50" @click="addQuestion">
                        <i class="fa-solid fa-plus"></i>Tambah soal
                    </button>
                </div>

                <InputError class="mb-4" :message="form.errors.questions" />

                <div class="space-y-5">
                    <article v-for="(question, questionIndex) in form.questions" :key="question.id ?? `new-question-${questionIndex}`" class="overflow-hidden rounded-xl border border-slate-200">
                        <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-sm font-bold text-violet-700">{{ questionIndex + 1 }}</span>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Soal {{ questionIndex + 1 }}</h3>
                                    <p class="text-xs text-slate-500">Pertanyaan dan pilihan jawaban</p>
                                </div>
                            </div>
                            <button v-if="form.questions.length > 1" type="button" :disabled="question.has_answers" class="inline-flex h-8 items-center gap-2 rounded-lg px-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:text-slate-300" :title="question.has_answers ? 'Soal sudah memiliki jawaban peserta dan tidak dapat dihapus' : 'Hapus soal'" @click="removeQuestion(questionIndex)">
                                <i class="fa-regular fa-trash-can"></i><span class="hidden sm:inline">Hapus soal</span>
                            </button>
                        </header>

                        <div class="space-y-5 p-4 sm:p-5">
                            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_150px]">
                                <div>
                                    <InputLabel :for="`question-${questionIndex}`" value="Teks pertanyaan" />
                                    <textarea :id="`question-${questionIndex}`" v-model="question.question_text" rows="3" required maxlength="10000" placeholder="Tuliskan pertanyaan..." class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    <InputError class="mt-1.5" :message="questionError(questionIndex, 'question_text')" />
                                </div>
                                <div>
                                    <InputLabel :for="`weight-${questionIndex}`" value="Bobot nilai" />
                                    <TextInput :id="`weight-${questionIndex}`" v-model="question.score_weight" type="number" min="1" max="100000" required class="mt-1.5 block w-full" />
                                    <InputError class="mt-1.5" :message="questionError(questionIndex, 'score_weight')" />
                                </div>
                            </div>

                            <div>
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-wide text-slate-700">Pilihan jawaban</h4>
                                        <p class="mt-1 text-xs text-slate-400">Klik huruf untuk menandai kunci jawaban.</p>
                                    </div>
                                    <button type="button" :disabled="question.choices.length >= 8" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50 disabled:opacity-40" @click="addChoice(question)">
                                        <i class="fa-solid fa-plus"></i>Tambah pilihan
                                    </button>
                                </div>

                                <div class="grid gap-3 lg:grid-cols-2">
                                    <div v-for="(choice, choiceIndex) in question.choices" :key="choice.id ?? `new-${questionIndex}-${choiceIndex}`" class="flex items-start gap-2.5 rounded-lg border p-2.5" :class="choice.is_correct ? 'border-emerald-200 bg-emerald-50/50' : 'border-slate-200 bg-white'">
                                        <button type="button" class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-md border text-xs font-bold transition" :class="choice.is_correct ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-emerald-300'" :aria-label="`Tandai opsi ${choiceIndex + 1} sebagai benar`" @click="setCorrectChoice(question, choiceIndex)">{{ String.fromCharCode(65 + choiceIndex) }}</button>
                                        <div class="min-w-0 flex-1">
                                            <label :for="`choice-${questionIndex}-${choiceIndex}`" class="mb-1 block text-[11px] font-semibold" :class="choice.is_correct ? 'text-emerald-700' : 'text-slate-500'">{{ choice.is_correct ? 'Kunci jawaban' : `Pilihan ${String.fromCharCode(65 + choiceIndex)}` }}</label>
                                            <textarea :id="`choice-${questionIndex}-${choiceIndex}`" v-model="choice.choice_text" rows="2" required maxlength="5000" placeholder="Isi pilihan..." class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                            <InputError class="mt-1" :message="choiceError(questionIndex, choiceIndex)" />
                                        </div>
                                        <button v-if="question.choices.length > 2" type="button" :disabled="choice.has_answers" class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:text-slate-200" :title="choice.has_answers ? 'Pilihan ini sudah dipilih peserta' : 'Hapus pilihan'" @click="removeChoice(question, choiceIndex)">
                                            <i class="fa-regular fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors[`questions.${questionIndex}.choices`]" />
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <Link :href="route('admin.exams.index')">
                    <SecondaryButton type="button" class="w-full justify-center sm:w-auto">Batal</SecondaryButton>
                </Link>
                <PrimaryButton :disabled="form.processing" class="w-full justify-center sm:w-auto">
                    {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : `Simpan Test & ${form.questions.length} Soal`) }}
                </PrimaryButton>
            </div>
        </form>
    </section>
</template>

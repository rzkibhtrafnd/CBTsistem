<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({
    exam: { type: Object, required: true },
    event: { type: Object, required: true },
    session: { type: Object, default: null },
});

const form = useForm({});
const isOngoing = computed(() => props.session?.status === 'ongoing');

function startExam() {
    form.post(route('student.exams.start', { event: props.event.id, exam: props.exam.id }));
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Persiapan ${exam.title}`" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Persiapan Ujian</h1>
                <p class="mt-1 text-sm text-slate-500">Periksa informasi dan petunjuk sebelum masuk ke ruang ujian.</p>
            </div>
        </template>

        <Breadcrumb :items="[
            { label: 'Daftar Ujian', url: route('student.exams.index') },
            { label: event.title },
        ]" />

        <div class="mx-auto min-w-0 space-y-4 sm:space-y-5">
            <section class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-blue-950 to-blue-700 px-4 py-6 text-white shadow-lg shadow-blue-950/15 sm:rounded-3xl sm:px-9 sm:py-9">
                <div class="pointer-events-none absolute -right-14 -top-24 -z-10 h-72 w-72 rounded-full border-[36px] border-white/5"></div>
                <div class="pointer-events-none absolute bottom-0 right-24 -z-10 h-40 w-40 rounded-full bg-blue-400/10 blur-3xl"></div>
                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-blue-100 backdrop-blur-sm">
                            <i class="fa-solid fa-shield-halved"></i>
                            {{ isOngoing ? 'Sesi ujian berlangsung' : 'Ruang persiapan ujian' }}
                        </span>
                        <h2 class="mt-4 break-words text-2xl font-black tracking-tight sm:mt-5 sm:text-3xl">{{ exam.title }}</h2>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-blue-100">Siapkan diri dan perangkat Anda. Baca petunjuk berikut sebelum memulai pengerjaan.</p>
                    </div>
                    <div class="flex min-w-0 items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-3 py-3 backdrop-blur-sm sm:px-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-lg text-blue-100"><i class="fa-regular fa-calendar-check"></i></span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Jadwal ditutup</p>
                            <p class="mt-1 break-words text-xs font-bold sm:text-sm">{{ formatIndonesiaDateTime(event.end_time, { dateStyle: 'medium' }) }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-3 sm:grid-cols-3">
                <article class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fa-regular fa-clock text-lg"></i></span>
                    <div><p class="text-xs font-semibold text-slate-400">Waktu pengerjaan</p><p class="mt-1 font-extrabold text-slate-900">{{ exam.duration_minutes }} menit</p></div>
                </article>
                <article class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fa-regular fa-circle-question text-lg"></i></span>
                    <div><p class="text-xs font-semibold text-slate-400">Jumlah pertanyaan</p><p class="mt-1 font-extrabold text-slate-900">{{ exam.questions_count }} soal</p></div>
                </article>
                <article class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-floppy-disk"></i></span>
                    <div><p class="text-xs font-semibold text-slate-400">Penyimpanan jawaban</p><p class="mt-1 font-extrabold text-slate-900">Otomatis</p></div>
                </article>
            </section>

            <div class="grid min-w-0 items-start gap-4 sm:gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <header class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-list-check"></i></span>
                        <div>
                            <h2 class="font-extrabold text-slate-900">Petunjuk pengerjaan</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Luangkan waktu sebentar untuk membaca.</p>
                        </div>
                    </header>
                    <div class="space-y-4 p-5 sm:p-6">
                        <article class="flex gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-extrabold text-blue-700">1</span>
                            <div><h3 class="text-sm font-bold text-slate-800">Pastikan perangkat siap</h3><p class="mt-1 text-sm leading-6 text-slate-500">Gunakan koneksi internet yang stabil dan hindari menutup halaman selama ujian.</p></div>
                        </article>
                        <article class="flex gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-extrabold text-blue-700">2</span>
                            <div><h3 class="text-sm font-bold text-slate-800">Perhatikan hitung mundur</h3><p class="mt-1 text-sm leading-6 text-slate-500">Waktu mulai berjalan saat Anda menekan tombol masuk ujian dan berakhir otomatis saat habis.</p></div>
                        </article>
                        <article class="flex gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-extrabold text-blue-700">3</span>
                            <div><h3 class="text-sm font-bold text-slate-800">Jawaban tersimpan otomatis</h3><p class="mt-1 text-sm leading-6 text-slate-500">Pilih jawaban dan tandai soal ragu-ragu. Perubahan langsung dikirim dan disimpan.</p></div>
                        </article>
                        <article class="flex gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-extrabold text-blue-700">4</span>
                            <div><h3 class="text-sm font-bold text-slate-800">Kerjakan dengan mandiri</h3><p class="mt-1 text-sm leading-6 text-slate-500">Ujian hanya dapat dimulai satu kali. Jika keluar sebelum selesai, Anda dapat melanjutkan selama waktu masih tersedia.</p></div>
                        </article>
                    </div>
                </section>

                <aside class="space-y-4 lg:sticky lg:top-5">
                    <section v-if="isOngoing" class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                        <div class="border-b border-emerald-100 bg-emerald-50 px-5 py-4">
                            <div class="flex items-center gap-2 text-emerald-700"><i class="fa-solid fa-circle-check"></i><h2 class="font-extrabold">Ujian sedang berlangsung</h2></div>
                            <p class="mt-1 text-xs leading-5 text-emerald-800">Sesi Anda sudah dimulai. Lanjutkan sebelum batas waktu berakhir.</p>
                        </div>
                        <div class="space-y-3 p-5">
                            <div class="rounded-xl bg-slate-50 px-3.5 py-3 text-sm">
                                <p class="text-xs text-slate-400">Mulai pada</p>
                                <p class="mt-1 font-bold text-slate-800">{{ formatIndonesiaDateTime(session.start_time, { dateStyle: 'medium', timeStyle: 'short' }) }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3.5 py-3 text-sm">
                                <p class="text-xs text-slate-400">Batas waktu sesi</p>
                                <p class="mt-1 font-bold text-slate-800">{{ formatIndonesiaDateTime(session.end_time, { dateStyle: 'medium', timeStyle: 'short' }) }}</p>
                            </div>
                            <Link :href="route('student.exams.session', { event: event.id, exam: exam.id })" class="block">
                                <PrimaryButton type="button" class="w-full justify-center"><i class="fa-solid fa-arrow-right mr-2"></i>Lanjutkan Ujian</PrimaryButton>
                            </Link>
                        </div>
                    </section>

                    <section v-else class="overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-md shadow-blue-950/5">
                        <div class="border-b border-blue-100 bg-blue-50/70 px-5 py-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Sebelum masuk</p>
                            <h2 class="mt-1 font-extrabold text-slate-900">Anda sudah siap?</h2>
                            <p class="mt-1 text-xs leading-5 text-slate-500">Pastikan semua informasi ujian sudah dipahami.</p>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start gap-2.5 rounded-xl border border-amber-100 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                                <i class="fa-solid fa-circle-info mt-0.5 text-amber-600"></i>
                                <p>Setelah dimulai, waktu ujian langsung berjalan dan sesi tidak dapat diulang.</p>
                            </div>
                            <PrimaryButton :disabled="form.processing" class="mt-4 w-full justify-center py-2.5" @click="startExam">
                                <i class="fa-solid fa-play mr-2"></i>{{ form.processing ? 'Menyiapkan ujian...' : 'Mulai Ujian' }}
                            </PrimaryButton>
                            <Link :href="route('student.exams.index')" class="mt-3 block text-center text-xs font-bold text-slate-400 hover:text-slate-600">Kembali ke daftar ujian</Link>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({
    exams: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const filterForm = useForm({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    per_page: Number(props.filters.per_page ?? props.exams.per_page ?? 10),
});

const hasFilters = computed(() => Boolean(filterForm.search || filterForm.status));

function applyFilters() {
    router.get(route('student.exams.index'), {
        search: filterForm.search,
        status: filterForm.status,
        per_page: filterForm.per_page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function resetFilters() {
    filterForm.search = '';
    filterForm.status = '';
    filterForm.per_page = 10;
    applyFilters();
}

function hasSessionStatus(exam, status) {
    return exam.status === status;
}

function examActionRoute(exam) {
    if (hasSessionStatus(exam, 'finished')) return 'student.exams.result';
    if (hasSessionStatus(exam, 'ongoing')) return 'student.exams.session';
    return 'student.exams.lobby';
}

function testOf(item) { return item.test; }
function examRouteParams(item) { return { event: item.event_id, exam: item.test_id }; }

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Daftar Ujian" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Daftar Ujian</h1>
                <p class="mt-1 text-sm text-slate-500">Pilih ujian yang tersedia atau buka hasil ujian yang sudah selesai.</p>
            </div>
        </template>

        <Breadcrumb :items="[{ label: 'Daftar Ujian' }]" />

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Ujian</h2>
                    <p class="mt-1 text-sm text-slate-500">Cari ujian, lanjutkan sesi, atau periksa hasil yang sudah selesai.</p>
                </div>
            </header>

            <form class="flex min-w-0 flex-col gap-3 border-b border-slate-100 bg-slate-50/60 p-4 sm:flex-row sm:items-center" @submit.prevent="applyFilters">
                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">Cari nama ujian</span>
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input v-model="filterForm.search" type="search" placeholder="Cari nama ujian..." class="w-full rounded-xl border-slate-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500" />
                </label>

                <select v-model="filterForm.status" class="rounded-xl border-slate-200 bg-white py-2 pl-3 pr-9 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-48" aria-label="Filter status ujian">
                    <option value="">Semua ujian</option>
                    <option value="available">Tersedia</option>
                    <option value="ongoing">Sedang dikerjakan</option>
                    <option value="finished">Sudah dikerjakan</option>
                </select>

                <div class="flex gap-2">
                    <PrimaryButton type="submit" :disabled="filterForm.processing"><i class="fa-solid fa-filter mr-2"></i>Filter</PrimaryButton>
                    <SecondaryButton v-if="hasFilters" type="button" @click="resetFilters">Reset</SecondaryButton>
                </div>
            </form>

            <div v-if="exams.data.length" class="grid min-w-0 gap-4 p-4 sm:gap-5 sm:p-5 md:grid-cols-2 xl:grid-cols-3">
                <article v-for="exam in exams.data" :key="exam.id" class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between border-b border-slate-100 bg-slate-50/50 px-5 py-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i class="fa-solid fa-file-circle-check text-lg"></i>
                        </span>
                        <span v-if="hasSessionStatus(exam, 'finished')" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>Sudah dikerjakan
                        </span>
                        <span v-else-if="hasSessionStatus(exam, 'ongoing')" class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Sedang dikerjakan
                        </span>
                        <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Tersedia
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col px-5 py-5">
                        <h3 class="break-words text-base font-extrabold leading-6 text-slate-900">{{ testOf(exam).title }}</h3>
                        <p class="mt-1 break-words text-xs font-semibold text-blue-700">{{ exam.event.title }}</p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Durasi</p>
                                <p class="mt-1 text-sm font-bold text-slate-800"><i class="fa-regular fa-clock mr-1.5 text-slate-400"></i>{{ testOf(exam).duration_minutes }} menit</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Jumlah soal</p>
                                <p class="mt-1 text-sm font-bold text-slate-800"><i class="fa-regular fa-circle-question mr-1.5 text-slate-400"></i>{{ testOf(exam).questions_count }} soal</p>
                            </div>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-slate-500">
                            <i class="fa-regular fa-calendar mr-1.5 text-slate-400"></i>
                            Berakhir {{ formatIndonesiaDateTime(exam.event.end_time) }}
                        </p>

                        <Link :href="route(examActionRoute(exam), examRouteParams(exam))" class="mt-5 block">
                            <PrimaryButton type="button" class="w-full justify-center">
                                <i :class="hasSessionStatus(exam, 'finished') ? 'fa-solid fa-chart-column' : hasSessionStatus(exam, 'ongoing') ? 'fa-solid fa-arrow-right' : 'fa-solid fa-play'" class="mr-2"></i>
                                {{ hasSessionStatus(exam, 'finished') ? 'Lihat Hasil' : hasSessionStatus(exam, 'ongoing') ? 'Lanjutkan Ujian' : 'Kerjakan' }}
                            </PrimaryButton>
                        </Link>
                    </div>
                </article>
            </div>

            <div v-else class="m-4 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center sm:m-5 sm:px-6 sm:py-16">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <i class="fa-regular fa-calendar-xmark text-2xl"></i>
                </span>
                <h3 class="mt-4 font-bold text-slate-800">{{ hasFilters ? 'Ujian tidak ditemukan' : 'Belum ada ujian' }}</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ hasFilters ? 'Coba ubah kata kunci atau filter status.' : 'Ujian akan tampil di sini saat jadwalnya dibuka oleh administrator.' }}</p>
            </div>

            <PaginationControls
                v-if="exams.data.length"
                :links="exams.links"
                :from="exams.from ?? 0"
                :to="exams.to ?? 0"
                :total="exams.total"
                label="ujian"
                v-model:per-page="filterForm.per_page"
                @change="applyFilters"
            />
        </section>
    </AuthenticatedLayout>
</template>

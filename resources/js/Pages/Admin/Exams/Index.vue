<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({
    tests: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const filterForm = useForm({
    search: props.filters.search ?? '',
    is_active: props.filters.is_active ?? '',
    per_page: Number(props.filters.per_page ?? props.tests.per_page ?? 10),
});

const hasFilters = () => Boolean(filterForm.search || filterForm.is_active !== '');

function applyFilters() {
    router.get(route('admin.exams.index'), {
        search: filterForm.search,
        is_active: filterForm.is_active,
        per_page: filterForm.per_page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function resetFilters() {
    filterForm.search = '';
    filterForm.is_active = '';
    filterForm.per_page = 10;
    applyFilters();
}

function deleteExam(exam) {
    if (!window.confirm(`Hapus test "${exam.title}"?`)) return;

    router.delete(route('admin.exams.destroy', exam.id), { preserveScroll: true });
}

function rowNumber(index) {
    return (props.tests.current_page - 1) * props.tests.per_page + index + 1;
}

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Bank Test" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">
                    Bank Test
                </h1>
                <p class="mt-1 text-sm font-normal text-slate-500">
                    Kelola test dan bank soal. Atur jadwal serta peserta melalui menu event.
                </p>
            </div>
        </template>

        <Breadcrumb :items="[{ label: 'Test' }]" />

        <div class="mx-auto">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Daftar Test</h2>
                        <p class="mt-1 text-sm text-slate-500">Cari dan kelola test yang tersedia di sistem.</p>
                    </div>
                    <Link :href="route('admin.exams.create')" class="shrink-0">
                        <PrimaryButton type="button">
                            <i class="fa-solid fa-plus mr-2"></i>Tambah Test
                        </PrimaryButton>
                    </Link>
                </header>

                <form class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 p-4 sm:flex-row sm:items-center" @submit.prevent="applyFilters">
                    <label class="relative min-w-0 flex-1">
                        <span class="sr-only">Cari test</span>
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input
                            v-model="filterForm.search"
                            type="search"
                            name="search"
                            placeholder="Cari nama test..."
                            class="w-full rounded-xl border-slate-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </label>

                    <select
                        v-model="filterForm.is_active"
                        name="is_active"
                        class="rounded-xl border-slate-200 bg-white py-2 pl-3 pr-9 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-44"
                    >
                        <option value="">Semua status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>

                    <div class="flex gap-2">
                        <PrimaryButton type="submit">
                            <i class="fa-solid fa-filter mr-2"></i>Filter
                        </PrimaryButton>
                        <SecondaryButton v-if="hasFilters" type="button" @click="resetFilters">
                            Reset
                        </SecondaryButton>
                    </div>
                </form>

                <div v-if="tests.data.length" class="divide-y divide-slate-100 2xl:hidden">
                    <article v-for="(exam, index) in tests.data" :key="`card-${exam.id}`" class="space-y-4 p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0"><p class="break-words font-semibold text-slate-900">{{ exam.title }}</p><p class="mt-1 text-xs text-slate-500">{{ rowNumber(index) }} · Dibuat {{ formatIndonesiaDateTime(exam.created_at) }}</p></div>
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="exam.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'"><span class="h-1.5 w-1.5 rounded-full" :class="exam.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>{{ exam.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-center">
                            <div><p class="text-xs text-slate-500">Durasi</p><p class="mt-1 text-sm font-bold text-slate-800">{{ exam.duration_minutes }} mnt</p></div>
                            <div><p class="text-xs text-slate-500">Soal</p><p class="mt-1 text-sm font-bold text-violet-700">{{ exam.questions_count }}</p></div>
                            <div><p class="text-xs text-slate-500">Event</p><p class="mt-1 text-sm font-bold text-blue-700">{{ exam.events_count }}</p></div>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <Link :href="route('admin.exams.show', exam.id)" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-2 text-xs font-semibold text-slate-600"><i class="fa-regular fa-eye"></i>Detail</Link>
                            <Link :href="route('admin.exams.edit', exam.id)" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50 px-2 text-xs font-semibold text-blue-700"><i class="fa-regular fa-pen-to-square"></i>Edit</Link>
                            <button type="button" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-2 text-xs font-semibold text-red-600" @click="deleteExam(exam)"><i class="fa-regular fa-trash-can"></i>Hapus</button>
                        </div>
                    </article>
                </div>
                <div class="hidden overflow-x-auto 2xl:block">
                    <table class="w-full min-w-[1000px] text-left text-sm">
                        <thead class="bg-white text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-14 px-5 py-4">No</th>
                                <th class="px-4 py-4">Nama Test</th>
                                <th class="px-4 py-4 text-center">Durasi</th>
                                <th class="px-4 py-4 text-center">Soal</th>
                                <th class="px-4 py-4 text-center">Event</th>
                                <th class="px-4 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(exam, index) in tests.data" :key="exam.id" class="transition-colors hover:bg-slate-50/70">
                                <td class="px-5 py-4 font-mono text-xs text-slate-400">{{ rowNumber(index) }}</td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900">{{ exam.title }}</p>
                                    <p class="mt-1 text-xs text-slate-400">Dibuat {{ formatIndonesiaDateTime(exam.created_at) }}</p>
                                </td>
                                <td class="px-4 py-4 text-center text-slate-700">{{ exam.duration_minutes }} menit</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">{{ exam.questions_count }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ exam.events_count }} event</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="exam.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="exam.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                        {{ exam.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="route('admin.exams.show', exam.id)" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100" title="Detail">
                                            <i class="fa-regular fa-eye"></i><span>Detail</span>
                                        </Link>
                                        <Link :href="route('admin.exams.edit', exam.id)" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50 px-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100" title="Edit">
                                            <i class="fa-regular fa-pen-to-square"></i><span>Edit</span>
                                        </Link>
                                        <button type="button" class="inline-flex h-8 items-center gap-1.5 justify-center rounded-lg border border-red-100 bg-red-50 px-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-100" title="Hapus" @click="deleteExam(exam)">
                                            <i class="fa-regular fa-trash-can"></i><span>Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="tests.data.length === 0">
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <i class="fa-regular fa-folder-open text-xl"></i>
                                    </span>
                                    <p class="mt-3 font-semibold text-slate-700">{{ hasFilters() ? 'Test tidak ditemukan' : 'Belum ada test' }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ hasFilters() ? 'Coba ubah kata kunci atau filter.' : 'Tambahkan ujian untuk mulai mengelola evaluasi.' }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <PaginationControls
                    :links="tests.links"
                    :from="tests.from ?? 0"
                    :to="tests.to ?? 0"
                    :total="tests.total"
                    label="test"
                    v-model:per-page="filterForm.per_page"
                    @change="applyFilters"
                />
            </section>
        </div>
    </AuthenticatedLayout>
</template>

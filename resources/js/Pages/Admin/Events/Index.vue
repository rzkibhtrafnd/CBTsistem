<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({ events: { type: Object, required: true }, filters: { type: Object, default: () => ({}) } });
const filterForm = useForm({ search: props.filters.search ?? '', is_active: props.filters.is_active ?? '', per_page: Number(props.filters.per_page ?? props.events.per_page ?? 10) });
const hasFilters = () => Boolean(filterForm.search || filterForm.is_active !== '');

function applyFilters() {
    router.get(route('admin.events.index'), { search: filterForm.search, is_active: filterForm.is_active, per_page: filterForm.per_page }, { preserveState: true, preserveScroll: true, replace: true });
}

function resetFilters() {
    filterForm.search = '';
    filterForm.is_active = '';
    filterForm.per_page = 10;
    applyFilters();
}

function deleteEvent(event) {
    if (window.confirm(`Hapus event "${event.title}"?`)) router.delete(route('admin.events.destroy', event.id), { preserveScroll: true });
}

function rowNumber(index) {
    return (props.events.current_page - 1) * props.events.per_page + index + 1;
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Event" />
        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Manajemen Event</h1>
                <p class="mt-1 text-sm font-normal text-slate-500">Atur jadwal, gabungkan beberapa test, dan tugaskan peserta.</p>
            </div>
        </template>

        <Breadcrumb :items="[{ label: 'Event' }]" />

        <div class="mx-auto">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Daftar Event</h2>
                        <p class="mt-1 text-sm text-slate-500">Cari dan kelola event yang tersedia di sistem.</p>
                    </div>
                    <Link :href="route('admin.events.create')" class="shrink-0">
                        <PrimaryButton type="button"><i class="fa-solid fa-plus mr-2"></i>Tambah Event</PrimaryButton>
                    </Link>
                </header>

                <form class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 p-4 sm:flex-row sm:items-center" @submit.prevent="applyFilters">
                    <label class="relative min-w-0 flex-1">
                        <span class="sr-only">Cari event</span>
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input v-model="filterForm.search" type="search" name="search" placeholder="Cari nama event..." class="w-full rounded-xl border-slate-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500" />
                    </label>
                    <select v-model="filterForm.is_active" name="is_active" class="rounded-xl border-slate-200 bg-white py-2 pl-3 pr-9 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-44">
                        <option value="">Semua status</option><option value="1">Aktif</option><option value="0">Nonaktif</option>
                    </select>
                    <div class="flex gap-2">
                        <PrimaryButton type="submit"><i class="fa-solid fa-filter mr-2"></i>Filter</PrimaryButton>
                        <SecondaryButton v-if="hasFilters()" type="button" @click="resetFilters">Reset</SecondaryButton>
                    </div>
                </form>

                <div v-if="events.data.length" class="divide-y divide-slate-100 2xl:hidden">
                    <article v-for="(event, index) in events.data" :key="`card-${event.id}`" class="space-y-4 p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0"><p class="break-words font-semibold text-slate-900">{{ event.title }}</p><p class="mt-1 text-xs text-slate-500">{{ rowNumber(index) }} · {{ formatIndonesiaDateTime(event.created_at) }}</p></div>
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="event.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'"><span class="h-1.5 w-1.5 rounded-full" :class="event.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>{{ event.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold text-slate-700">{{ formatIndonesiaDateTime(event.start_time) }}</p><p class="mt-1 text-xs text-slate-500">sampai {{ formatIndonesiaDateTime(event.end_time) }}</p></div>
                        <div class="grid grid-cols-3 gap-2 rounded-xl border border-slate-100 p-3 text-center">
                            <div><p class="text-xs text-slate-500">Test</p><p class="mt-1 font-bold text-violet-700">{{ event.tests_count }}</p></div>
                            <div><p class="text-xs text-slate-500">Peserta</p><p class="mt-1 font-bold text-blue-700">{{ event.participants_count }}</p></div>
                            <div><p class="text-xs text-slate-500">Sesi</p><p class="mt-1 font-bold text-slate-800">{{ event.sessions_count }}</p></div>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <Link :href="route('admin.events.show', event.id)" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-2 text-xs font-semibold text-slate-600"><i class="fa-regular fa-eye"></i>Detail</Link>
                            <Link :href="route('admin.events.edit', event.id)" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50 px-2 text-xs font-semibold text-blue-700"><i class="fa-regular fa-pen-to-square"></i>Edit</Link>
                            <button type="button" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-2 text-xs font-semibold text-red-600" @click="deleteEvent(event)"><i class="fa-regular fa-trash-can"></i>Hapus</button>
                        </div>
                    </article>
                </div>
                <div class="hidden overflow-x-auto 2xl:block">
                    <table class="w-full min-w-[1000px] text-left text-sm">
                        <thead class="bg-white text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-14 px-5 py-4">No</th><th class="px-4 py-4">Nama Event</th><th class="px-4 py-4">Jadwal</th>
                                <th class="px-4 py-4 text-center">Test</th><th class="px-4 py-4 text-center">Peserta</th><th class="px-4 py-4 text-center">Sesi</th><th class="px-4 py-4">Status</th><th class="px-5 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(event, index) in events.data" :key="event.id" class="transition-colors hover:bg-slate-50/70">
                                <td class="px-5 py-4 font-mono text-xs text-slate-400">{{ rowNumber(index) }}</td>
                                <td class="px-4 py-4"><p class="font-semibold text-slate-900">{{ event.title }}</p><p class="mt-1 text-xs text-slate-400">Dibuat {{ formatIndonesiaDateTime(event.created_at) }}</p></td>
                                <td class="px-4 py-4"><p class="text-xs font-medium text-slate-700">{{ formatIndonesiaDateTime(event.start_time) }}</p><p class="mt-1 text-xs text-slate-400">sampai {{ formatIndonesiaDateTime(event.end_time) }}</p></td>
                                <td class="px-4 py-4 text-center"><span class="rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">{{ event.tests_count }}</span></td>
                                <td class="px-4 py-4 text-center"><span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ event.participants_count }}</span></td>
                                <td class="px-4 py-4 text-center text-slate-700">{{ event.sessions_count }}</td>
                                <td class="px-4 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="event.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'"><span class="h-1.5 w-1.5 rounded-full" :class="event.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>{{ event.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('admin.events.show', event.id)" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"><i class="fa-regular fa-eye"></i><span>Detail</span></Link>
                                    <Link :href="route('admin.events.edit', event.id)" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50 px-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"><i class="fa-regular fa-pen-to-square"></i><span>Edit</span></Link>
                                    <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-100" @click="deleteEvent(event)"><i class="fa-regular fa-trash-can"></i><span>Hapus</span></button>
                                </div></td>
                            </tr>
                            <tr v-if="events.data.length === 0">
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fa-regular fa-folder-open text-xl"></i></span>
                                    <p class="mt-3 font-semibold text-slate-700">{{ hasFilters() ? 'Event tidak ditemukan' : 'Belum ada event' }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ hasFilters() ? 'Coba ubah kata kunci atau filter.' : 'Tambahkan event untuk mulai menugaskan test kepada peserta.' }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <PaginationControls :links="events.links" :from="events.from ?? 0" :to="events.to ?? 0" :total="events.total" label="event" v-model:per-page="filterForm.per_page" @change="applyFilters" />
            </section>
        </div>
    </AuthenticatedLayout>
</template>

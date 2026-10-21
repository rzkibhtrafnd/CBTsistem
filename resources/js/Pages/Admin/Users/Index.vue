<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatIndonesiaDateTime } from '@/utils/date';

const props = defineProps({ users: { type: Object, required: true }, filters: { type: Object, default: () => ({}) } });
const filterForm = useForm({ search: props.filters.search ?? '', role: props.filters.role ?? '', per_page: Number(props.filters.per_page ?? props.users.per_page ?? 10) });
const hasFilters = () => Boolean(filterForm.search || filterForm.role);

function applyFilters() {
    router.get(route('admin.users.index'), { search: filterForm.search, role: filterForm.role, per_page: filterForm.per_page }, { preserveState: true, preserveScroll: true, replace: true });
}

function resetFilters() {
    filterForm.search = '';
    filterForm.role = '';
    filterForm.per_page = 10;
    applyFilters();
}

function deleteUser(user) {
    if (window.confirm(`Hapus user "${user.name}"?`)) router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
}

function rowNumber(index) {
    return (props.users.current_page - 1) * props.users.per_page + index + 1;
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Kelola User" />
        <template #header>
            <div><h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Kelola User</h1><p class="mt-1 text-sm font-normal text-slate-500">Kelola akun siswa dan administrator yang terdaftar.</p></div>
        </template>
        <Breadcrumb :items="[{ label: 'Kelola User' }]" />

        <div class="mx-auto">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div><h2 class="text-base font-bold text-slate-900">Daftar User</h2><p class="mt-1 text-sm text-slate-500">Cari, lihat, dan kelola akun sistem.</p></div>
                    <Link :href="route('admin.users.create')" class="shrink-0"><PrimaryButton type="button"><i class="fa-solid fa-plus mr-2"></i>Tambah User</PrimaryButton></Link>
                </header>

                <form class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 p-4 sm:flex-row sm:items-center" @submit.prevent="applyFilters">
                    <label class="relative min-w-0 flex-1">
                        <span class="sr-only">Cari user</span><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input v-model="filterForm.search" type="search" name="search" placeholder="Cari nama atau email..." class="w-full rounded-xl border-slate-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500" />
                    </label>
                    <select v-model="filterForm.role" name="role" class="rounded-xl border-slate-200 bg-white py-2 pl-3 pr-9 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-48">
                        <option value="">Semua peran</option><option value="student">Siswa</option><option value="admin">Administrator</option>
                    </select>
                    <div class="flex gap-2"><PrimaryButton type="submit"><i class="fa-solid fa-filter mr-2"></i>Filter</PrimaryButton><SecondaryButton v-if="hasFilters()" type="button" @click="resetFilters">Reset</SecondaryButton></div>
                </form>

                <div v-if="users.data.length" class="divide-y divide-slate-100 2xl:hidden">
                    <article v-for="(user, index) in users.data" :key="`card-${user.id}`" class="space-y-4 p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 font-bold text-blue-700">{{ user.name?.slice(0, 1)?.toUpperCase() }}</span>
                            <div class="min-w-0 flex-1"><p class="break-words font-semibold text-slate-900">{{ user.name }}</p><p class="break-all text-xs text-slate-500">{{ user.email }}</p><p class="mt-1 text-xs text-slate-400">{{ rowNumber(index) }} · {{ formatIndonesiaDateTime(user.created_at) }}</p></div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="user.role === 'admin' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700'">{{ user.role === 'admin' ? 'Admin' : 'Siswa' }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-50 p-3 text-center">
                            <div><p class="text-xs text-slate-500">Event</p><p class="mt-1 font-bold text-slate-800">{{ user.events_count }}</p></div>
                            <div><p class="text-xs text-slate-500">Sesi ujian</p><p class="mt-1 font-bold text-slate-800">{{ user.exam_sessions_count }}</p></div>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <Link :href="route('admin.users.show', user.id)" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-2 text-xs font-semibold text-slate-600"><i class="fa-regular fa-eye"></i>Detail</Link>
                            <Link :href="route('admin.users.edit', user.id)" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50 px-2 text-xs font-semibold text-blue-700"><i class="fa-regular fa-pen-to-square"></i>Edit</Link>
                            <button type="button" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-2 text-xs font-semibold text-red-600" @click="deleteUser(user)"><i class="fa-regular fa-trash-can"></i>Hapus</button>
                        </div>
                    </article>
                </div>
                <div class="hidden overflow-x-auto 2xl:block">
                    <table class="w-full min-w-[950px] text-left text-sm">
                        <thead class="bg-white text-xs font-semibold uppercase tracking-wider text-slate-500"><tr><th class="w-14 px-5 py-4">No</th><th class="px-4 py-4">Nama / Email</th><th class="px-4 py-4">Peran</th><th class="px-4 py-4 text-center">Event</th><th class="px-4 py-4 text-center">Sesi Ujian</th><th class="px-4 py-4">Terdaftar</th><th class="px-5 py-4 text-right">Aksi</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(user, index) in users.data" :key="user.id" class="transition-colors hover:bg-slate-50/70">
                                <td class="px-5 py-4 font-mono text-xs text-slate-400">{{ rowNumber(index) }}</td>
                                <td class="px-4 py-4"><p class="font-semibold text-slate-900">{{ user.name }}</p><p class="mt-1 text-xs text-slate-500">{{ user.email }}</p></td>
                                <td class="px-4 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="user.role === 'admin' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700'">{{ user.role === 'admin' ? 'Administrator' : 'Siswa' }}</span></td>
                                <td class="px-4 py-4 text-center text-slate-700">{{ user.events_count }}</td><td class="px-4 py-4 text-center text-slate-700">{{ user.exam_sessions_count }}</td>
                                <td class="px-4 py-4 text-xs text-slate-600">{{ formatIndonesiaDateTime(user.created_at) }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('admin.users.show', user.id)" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"><i class="fa-regular fa-eye"></i><span>Detail</span></Link>
                                    <Link :href="route('admin.users.edit', user.id)" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50 px-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"><i class="fa-regular fa-pen-to-square"></i><span>Edit</span></Link>
                                    <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-100" @click="deleteUser(user)"><i class="fa-regular fa-trash-can"></i><span>Hapus</span></button>
                                </div></td>
                            </tr>
                            <tr v-if="users.data.length === 0"><td colspan="7" class="px-5 py-16 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fa-solid fa-users text-xl"></i></span><p class="mt-3 font-semibold text-slate-700">{{ hasFilters() ? 'User tidak ditemukan' : 'Belum ada user' }}</p><p class="mt-1 text-sm text-slate-400">{{ hasFilters() ? 'Coba ubah kata kunci atau filter.' : 'Tambahkan akun untuk mulai mengelola pengguna.' }}</p></td></tr>
                        </tbody>
                    </table>
                </div>
                <PaginationControls :links="users.links" :from="users.from ?? 0" :to="users.to ?? 0" :total="users.total" label="user" v-model:per-page="filterForm.per_page" @change="applyFilters" />
            </section>
        </div>
    </AuthenticatedLayout>
</template>

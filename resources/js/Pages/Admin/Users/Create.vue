<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import UserForm from './UserForm.vue';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah User" />
        <template #header>
            <div><h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Tambah User</h1><p class="mt-1 text-sm font-normal text-slate-500">Buat akun siswa atau administrator.</p></div>
        </template>
        <Breadcrumb :items="[{ label: 'Kelola User', url: route('admin.users.index') }, { label: 'Tambah User' }]" />
        <UserForm />
    </AuthenticatedLayout>
</template>

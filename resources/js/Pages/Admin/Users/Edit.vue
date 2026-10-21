<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import UserForm from './UserForm.vue';

defineProps({ user: { type: Object, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit ${user.name}`" />
        <template #header>
            <div><h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Edit User</h1><p class="mt-1 text-sm font-normal text-slate-500">Perbarui informasi akun dan peran user.</p></div>
        </template>
        <Breadcrumb :items="[{ label: 'Kelola User', url: route('admin.users.index') }, { label: user.name, url: route('admin.users.show', user.id) }, { label: 'Edit' }]" />
        <UserForm :user="user" />
    </AuthenticatedLayout>
</template>

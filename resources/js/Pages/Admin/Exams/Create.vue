<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import ExamForm from './ExamForm.vue';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Buat Test" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Buat Test</h1>
                <p class="mt-1 text-sm font-normal text-slate-500">Buat test dan susun bank soalnya.</p>
            </div>
        </template>

        <Breadcrumb :items="[
            { label: 'Test', url: route('admin.exams.index') },
            { label: 'Buat Test' },
        ]" />

        <div class="mx-auto">
            <ExamForm />
        </div>
    </AuthenticatedLayout>
</template>

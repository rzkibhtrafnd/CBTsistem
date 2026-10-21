<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import ExamForm from './ExamForm.vue';

defineProps({
    exam: { type: Object, required: true },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit ${exam.title}`" />

        <template #header>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Edit Test</h1>
                <p class="mt-1 text-sm font-normal text-slate-500">Perbarui pengaturan dan bank soal test.</p>
            </div>
        </template>

        <Breadcrumb :items="[
            { label: 'Test', url: route('admin.exams.index') },
            { label: exam.title, url: route('admin.exams.show', exam.id) },
            { label: 'Edit' },
        ]" />

        <div class="mx-auto">
            <ExamForm :exam="exam" />
        </div>
    </AuthenticatedLayout>
</template>

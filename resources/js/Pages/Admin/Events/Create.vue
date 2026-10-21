<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import EventForm from './EventForm.vue';

defineProps({ tests: { type: Array, required: true }, participants: { type: Array, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Buat Event" />
        <template #header>
            <div><h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Buat Event</h1><p class="mt-1 text-sm font-normal text-slate-500">Atur jadwal, pilih beberapa test, dan tambahkan peserta.</p></div>
        </template>
        <Breadcrumb :items="[{ label: 'Event', url: route('admin.events.index') }, { label: 'Buat Event' }]" />
        <EventForm :tests="tests" :participants="participants" />
    </AuthenticatedLayout>
</template>

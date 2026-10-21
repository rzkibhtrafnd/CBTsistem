<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import EventForm from './EventForm.vue';

defineProps({ event: { type: Object, required: true }, tests: { type: Array, required: true }, participants: { type: Array, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit ${event.title}`" />
        <template #header>
            <div><h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Edit Event</h1><p class="mt-1 text-sm font-normal text-slate-500">Perbarui jadwal, test, dan peserta yang ditugaskan.</p></div>
        </template>
        <Breadcrumb :items="[{ label: 'Event', url: route('admin.events.index') }, { label: event.title, url: route('admin.events.show', event.id) }, { label: 'Edit' }]" />
        <EventForm :event="event" :tests="tests" :participants="participants" />
    </AuthenticatedLayout>
</template>

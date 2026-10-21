<script setup>
import { Link } from '@inertiajs/vue3';

const emit = defineEmits(['update:perPage', 'change']);

defineProps({
    links: {
        type: Array,
        required: true,
    },
    from: {
        type: Number,
        default: 0,
    },
    to: {
        type: Number,
        default: 0,
    },
    total: {
        type: Number,
        default: 0,
    },
    label: {
        type: String,
        default: 'data',
    },
    perPage: {
        type: Number,
        default: 10,
    },
});

function changePerPage(event) {
    emit('update:perPage', Number(event.target.value));
    emit('change');
}
</script>

<template>
    <div class="flex flex-col gap-4 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500">
            Menampilkan <span class="font-semibold text-slate-700">{{ from }}–{{ to }}</span>
            dari <span class="font-semibold text-slate-700">{{ total }}</span> {{ label }}
        </p>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <label class="flex items-center gap-2 text-xs text-slate-500">
                <span>Tampilkan</span>
                <select
                    :value="perPage"
                    class="rounded-lg border-slate-200 bg-white py-1.5 pl-2.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    aria-label="Jumlah data per halaman"
                    @change="changePerPage"
                >
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                </select>
                <span>per halaman</span>
            </label>

            <nav v-if="links.length > 3" aria-label="Navigasi halaman" class="flex items-center justify-end gap-1">
                <Link
                    v-for="(link, index) in links"
                    :key="index"
                    :href="link.url || '#'"
                    preserve-scroll
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-semibold transition"
                    :class="link.active
                        ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-200'
                        : link.url
                            ? 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700'
                            : 'pointer-events-none border-slate-100 bg-slate-50 text-slate-300'"
                    :aria-current="link.active ? 'page' : undefined"
                    v-html="link.label"
                />
            </nav>
        </div>
    </div>
</template>

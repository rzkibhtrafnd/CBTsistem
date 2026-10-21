<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="h-9 inline-flex items-center justify-center whitespace-nowrap rounded-md border border-transparent bg-blue-600 px-3 py-0 text-[11px] font-bold uppercase tracking-normal text-white shadow-sm transition duration-150 ease-in-out hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-800 sm:px-4 sm:text-xs"
    >
        <slot />
    </button>
</template>

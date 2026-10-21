<script setup>
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const dashboardRoute = page.props.auth?.user?.role === 'admin'
    ? 'admin.dashboard'
    : 'student.dashboard';
const dashboardHref = route().has(dashboardRoute) ? route(dashboardRoute) : route('dashboard');
</script>

<template>
    <nav
        v-if="items.length"
        class="mb-4 min-w-0 overflow-x-auto rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 text-slate-700 shadow-sm backdrop-blur-sm sm:mb-5 sm:px-4"
        aria-label="Breadcrumb"
    >
        <ol class="inline-flex min-w-max items-center space-x-1 text-xs font-medium md:space-x-2">
            <li class="inline-flex items-center">
                <Link
                    :href="dashboardHref"
                    class="inline-flex items-center text-slate-500 transition-colors hover:text-blue-600"
                >
                    <i class="fa-solid fa-house mr-2 text-[11px] text-slate-400"></i>
                    <span>Dashboard</span>
                </Link>
            </li>

            <li v-for="(item, index) in items" :key="`${item.label}-${index}`">
                <div class="flex items-center">
                    <i class="fa-solid fa-chevron-right mx-2 text-[9px] text-slate-300"></i>
                    <Link
                        v-if="item.url && index !== items.length - 1"
                        :href="item.url"
                        class="text-slate-500 transition-colors hover:text-blue-600"
                    >
                        {{ item.label }}
                    </Link>
                    <span
                        v-else
                        class="max-w-[55vw] truncate rounded-md bg-slate-100 px-2 py-0.5 font-bold text-slate-900 sm:max-w-[24rem]"
                        aria-current="page"
                    >
                        {{ item.label }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</template>

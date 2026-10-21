<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

// State Sidebar & Profile
const sidebarOpen = ref(false);
const sidebarMinimized = ref(false);

// Akses data global dari Inertia ($page.props)
const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const flash = computed(() => page.props.flash || {});
watch(() => page.url, () => { sidebarOpen.value = false; });

// Role dari model User diserialisasi sebagai string enum oleh Laravel.
const role = computed(() => {
    const value = user.value.role;

    return typeof value === 'string' ? value : value?.value || '';
});
const isAdmin = computed(() => role.value === 'admin');
const isStudent = computed(() => role.value === 'student');
const roleLabel = computed(() => ({
    admin: 'Administrator',
    student: 'Siswa',
}[role.value] || 'Pengguna'));
const dashboardUrl = computed(() => {
    const roleDashboard = role.value ? `${role.value}.dashboard` : null;

    if (roleDashboard && route().has(roleDashboard)) {
        return route(roleDashboard);
    }

    return route('dashboard');
});
const hasProfileRoute = computed(() => route().has('profile.edit'));

// Helper Route Check untuk Active State Menu
const isRouteActive = (pattern) => {
    return route().current(pattern);
};
</script>

<template>
    <div class="relative flex min-h-screen w-full bg-slate-50 font-sans text-slate-900 antialiased">

        <!-- Overlay Mobile Sidebar -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/50 transition-opacity lg:hidden"
        ></div>

        <!-- Sidebar Navigation -->
        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarMinimized ? 'lg:w-20' : 'lg:w-64'
            ]"
            class="fixed inset-y-0 left-0 z-30 flex h-dvh w-64 flex-col transform bg-slate-900 shadow-xl transition-all duration-300 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:translate-x-0 lg:shadow-none"
        >
            <!-- Sidebar Header / Logo -->
            <div
                class="flex items-center py-4 border-b h-16 bg-slate-950 border-slate-800"
                :class="sidebarMinimized ? 'justify-center px-0' : 'justify-between px-6'"
            >
                <a :href="dashboardUrl" class="flex items-center text-white" :class="sidebarMinimized ? 'space-x-0' : 'space-x-3'">
                    <ApplicationLogo class="w-auto h-8 text-blue-500 fill-current shrink-0" />
                    <div v-show="!sidebarMinimized" class="flex flex-col leading-tight overflow-hidden">
                        <span class="text-sm font-black tracking-wider text-white whitespace-nowrap">CBT SISTEM</span>
                        <span class="text-[10px] font-bold text-blue-400 tracking-widest uppercase">
                            {{ roleLabel }}
                        </span>
                    </div>
                </a>
                <button
                    @click="sidebarOpen = false"
                    class="text-gray-400 focus:outline-none lg:hidden"
                    v-show="!sidebarMinimized"
                >
                    <i class="text-xl fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Area Menu Navigation -->
            <nav
                class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto overflow-x-hidden"
                :class="sidebarMinimized ? 'px-2' : 'px-4'"
            >
                <!-- Dashboard Link -->
                <Link
                    :href="dashboardUrl"
                    title="Dashboard"
                    class="flex items-center py-3 text-sm font-bold rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('dashboard') || isRouteActive('*.dashboard')
                            ? 'border border-transparent bg-blue-600 text-white shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i
                        class="fa-solid fa-chart-pie transition-colors"
                        :class="[
                            isRouteActive('dashboard') || isRouteActive('*.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Dashboard</span>
                </Link>

                <Link
                    v-if="isAdmin && route().has('admin.exams.index')"
                    :href="route('admin.exams.index')"
                    title="Bank Test"
                    class="flex items-center py-3 text-sm font-bold rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('admin.exams.*')
                            ? 'border border-transparent bg-blue-600 text-white shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i
                        class="fa-solid fa-file-circle-check transition-colors"
                        :class="[
                            isRouteActive('admin.exams.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Bank Test</span>
                </Link>

                <Link
                    v-if="isAdmin && route().has('admin.events.index')"
                    :href="route('admin.events.index')"
                    title="Manajemen Event"
                    class="flex items-center rounded-xl py-3 text-sm font-bold transition-colors duration-200 group"
                    :class="[isRouteActive('admin.events.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white', sidebarMinimized ? 'justify-center px-0' : 'px-4']"
                >
                    <i class="fa-solid fa-calendar-days" :class="[sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg', isRouteActive('admin.events.*') ? 'text-white' : 'text-slate-400 group-hover:text-white']"></i>
                    <span v-show="!sidebarMinimized">Manajemen Event</span>
                </Link>

                <Link
                    v-if="isAdmin && route().has('admin.users.index')"
                    :href="route('admin.users.index')"
                    title="Kelola User"
                    class="flex items-center rounded-xl py-3 text-sm font-bold transition-colors duration-200 group"
                    :class="[isRouteActive('admin.users.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white', sidebarMinimized ? 'justify-center px-0' : 'px-4']"
                >
                    <i class="fa-solid fa-users" :class="[sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg', isRouteActive('admin.users.*') ? 'text-white' : 'text-slate-400 group-hover:text-white']"></i>
                    <span v-show="!sidebarMinimized">Kelola User</span>
                </Link>

                <Link
                    v-if="isAdmin && route().has('admin.reports.index')"
                    :href="route('admin.reports.index')"
                    title="Rekap Hasil"
                    class="flex items-center py-3 text-sm font-bold rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('admin.reports.*')
                            ? 'border border-transparent bg-blue-600 text-white shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i
                        class="fa-solid fa-chart-column transition-colors"
                        :class="[
                            isRouteActive('admin.reports.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Rekap Hasil</span>
                </Link>

                <Link
                    v-if="isStudent && route().has('student.exams.index')"
                    :href="route('student.exams.index')"
                    title="Daftar Ujian"
                    class="flex items-center py-3 text-sm font-bold rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('student.exams.*')
                            ? 'border border-transparent bg-blue-600 text-white shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i
                        class="fa-solid fa-file-circle-check transition-colors"
                        :class="[
                            isRouteActive('student.exams.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Daftar Ujian</span>
                </Link>

                <!-- Pengaturan akun -->
                <div class="pt-3 pb-1">
                    <p v-show="!sidebarMinimized" class="px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Pengaturan</p>
                    <div v-show="sidebarMinimized" class="border-t border-slate-800 my-2"></div>
                </div>

                <Link
                    v-if="hasProfileRoute"
                    :href="route('profile.edit')"
                    title="Profile"
                    class="flex items-center py-3 text-sm font-bold rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('profile.*')
                            ? 'border border-transparent bg-blue-600 text-white shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i class="fa-solid fa-user-gear transition-colors" :class="[
                        isRouteActive('profile.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                        sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                    ]"></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Profile</span>
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    title="Logout"
                    class="flex w-full items-center py-3 text-sm font-bold text-slate-300 rounded-xl transition-colors duration-200 group hover:bg-red-500 hover:text-white"
                    :class="sidebarMinimized ? 'justify-center px-0' : 'px-4 text-left'"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket text-slate-400 transition-colors group-hover:text-white" :class="sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'"></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Logout</span>
                </Link>
            </nav>

            <!-- Sidebar Footer Profile -->
            <div
                class="flex items-center p-4 border-t bg-slate-950 border-slate-800 text-slate-400 overflow-hidden"
                :class="sidebarMinimized ? 'justify-center space-x-0' : 'space-x-3'"
            >
                <div class="flex items-center justify-center w-8 h-8 font-bold text-white bg-blue-600 border border-blue-500 rounded-full shrink-0 shadow-[0_0_15px_-3px_rgba(59,130,246,0.5)]">
                    {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                </div>
                <div v-show="!sidebarMinimized" class="flex-1 truncate">
                    <p class="text-xs font-bold leading-none text-white truncate">{{ user.name }}</p>
                    <span class="text-[9px] text-gray-500 font-mono block mt-1 uppercase tracking-wider">
                        Role: <span :class="isAdmin ? 'text-amber-400' : 'text-blue-400'" class="font-bold">{{ roleLabel }}</span>
                    </span>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex min-h-screen min-w-0 flex-1 flex-col">

            <!-- Header Topbar -->
            <header class="sticky top-0 z-10 flex min-h-16 items-center justify-between gap-3 border-b border-slate-100 bg-white px-4 shadow-sm sm:px-6">
                <div class="flex min-w-0 flex-1 items-center">

                    <!-- Tombol Mobile Sidebar (Off-canvas) -->
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="mr-3 shrink-0 text-slate-500 transition-colors hover:text-blue-600 focus:outline-none sm:mr-4 lg:hidden"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Tombol Desktop Sidebar (Minimize) -->
                    <button
                        @click="sidebarMinimized = !sidebarMinimized"
                        class="mr-4 hidden shrink-0 text-slate-500 transition-colors hover:text-blue-600 focus:outline-none lg:block"
                        title="Toggle Sidebar"
                    >
                        <i
                            class="fa-solid fa-bars-staggered text-lg transition-transform duration-300"
                            :class="sidebarMinimized ? 'rotate-180' : ''"
                        ></i>
                    </button>

                    <!-- Page Title / Header Slot -->
                    <div v-if="$slots.header" class="min-w-0 flex-1">
                        <slot name="header" />
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 text-sm font-bold text-slate-600">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-xs font-extrabold text-blue-700 sm:hidden">{{ user.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
                    <i class="fa-regular fa-circle-user hidden text-lg sm:inline"></i>
                    <span class="hidden max-w-40 truncate sm:inline">{{ user.name }}</span>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="min-w-0 flex-1 bg-slate-50 px-4 py-4 sm:p-6">
                <!-- Flash Message Success -->
                <div v-if="flash.success" class="mb-4 overflow-hidden bg-white border border-slate-100 shadow-sm rounded-xl">
                    <div class="p-4 text-green-800 border-l-4 border-green-200 bg-green-50 font-medium">
                        {{ flash.success }}
                    </div>
                </div>

                <!-- Flash Message Error -->
                <div v-if="flash.error" class="mb-4 overflow-hidden bg-white border border-red-100 shadow-sm rounded-xl" role="alert">
                    <div class="flex items-start gap-3 p-4 text-red-800 border-l-4 border-red-400 bg-red-50 font-medium">
                        <span>{{ flash.error }}</span>
                    </div>
                </div>

                <div v-if="flash.info" class="mb-4 overflow-hidden bg-white border border-blue-100 shadow-sm rounded-xl" role="status">
                    <div class="flex items-start gap-3 p-4 text-blue-800 border-l-4 border-blue-400 bg-blue-50 font-medium">
                        <span>{{ flash.info }}</span>
                    </div>
                </div>

                <!-- Slot Konten Halaman -->
                <slot />
            </main>

            <!-- Footer -->
            <footer class="mt-auto bg-white border-t border-slate-100">
                <div class="mx-auto flex items-center justify-center px-4 py-3 sm:px-6">
                    <p class="text-xs text-center text-slate-500">&copy; {{ new Date().getFullYear() }} CBT Sistem. All rights reserved.</p>
                </div>
            </footer>
        </div>

    </div>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ current_password: '', password: '', password_confirmation: '' });

function updatePassword() {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) form.reset('password', 'password_confirmation');
            if (form.errors.current_password) form.reset('current_password');
        },
    });
}
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
            <div>
                <h2 class="text-base font-bold text-slate-900">Keamanan password</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Gunakan password yang kuat dan tidak digunakan di tempat lain.</p>
            </div>
        </header>

        <form class="space-y-5 p-5 sm:p-6" @submit.prevent="updatePassword">
            <div>
                <label for="current-password" class="mb-2 block text-sm font-semibold text-slate-700">Password saat ini</label>
                <TextInput id="current-password" v-model="form.current_password" type="password" autocomplete="current-password" required class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:bg-white focus:ring-blue-500" />
                <InputError class="mt-2" :message="form.errors.current_password" />
            </div>

            <div>
                <label for="new-password" class="mb-2 block text-sm font-semibold text-slate-700">Password baru</label>
                <TextInput id="new-password" v-model="form.password" type="password" autocomplete="new-password" required class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:bg-white focus:ring-blue-500" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <label for="confirm-password" class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi password baru</label>
                <TextInput id="confirm-password" v-model="form.password_confirmation" type="password" autocomplete="new-password" required class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:bg-white focus:ring-blue-500" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
                <p v-if="form.recentlySuccessful" class="text-xs font-medium text-emerald-600"><i class="fa-solid fa-circle-check mr-1"></i>Password diperbarui</p>
                <span v-else class="text-xs text-slate-400">Konfirmasi password diperlukan.</span>
                <PrimaryButton :disabled="form.processing">
                    Perbarui password
                </PrimaryButton>
            </div>
        </form>
    </section>
</template>

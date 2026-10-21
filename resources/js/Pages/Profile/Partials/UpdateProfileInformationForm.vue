<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
const form = useForm({ name: user.name, email: user.email });

function saveProfile() {
    form.patch(route('profile.update'), { preserveScroll: true });
}
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
            <div>
                <h2 class="text-base font-bold text-slate-900">Informasi profil</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Perbarui nama dan alamat email akun Anda.</p>
            </div>
        </header>

        <form class="space-y-5 p-5 sm:p-6" @submit.prevent="saveProfile">
            <div>
                <label for="profile-name" class="mb-2 block text-sm font-semibold text-slate-700">Nama lengkap</label>
                <TextInput id="profile-name" v-model="form.name" type="text" autocomplete="name" required class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:bg-white focus:ring-blue-500" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <label for="profile-email" class="mb-2 block text-sm font-semibold text-slate-700">Alamat email</label>
                <TextInput id="profile-email" v-model="form.email" type="email" autocomplete="email" required class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:bg-white focus:ring-blue-500" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
                <p v-if="form.recentlySuccessful" class="text-xs font-medium text-emerald-600"><i class="fa-solid fa-circle-check mr-1"></i>Perubahan tersimpan</p>
                <span v-else class="text-xs text-slate-400">Pastikan alamat email dapat diakses.</span>
                <PrimaryButton :disabled="form.processing">
                    Simpan profil
                </PrimaryButton>
            </div>
        </form>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({ user: { type: Object, default: null } });
const editing = computed(() => Boolean(props.user));

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    role: props.user?.role ?? 'student',
    password: '',
    password_confirmation: '',
});

function submit() {
    editing.value ? form.put(route('admin.users.update', props.user.id)) : form.post(route('admin.users.store'));
}
</script>

<template>
    <form class="mx-auto" @submit.prevent="submit">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-6 py-5">
                <h2 class="font-bold text-slate-900">{{ editing ? 'Informasi User' : 'Informasi User Baru' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Lengkapi nama, email, peran, dan kata sandi user.</p>
            </header>

            <div class="space-y-5 p-6">
                <div>
                    <InputLabel for="name" value="Nama lengkap" />
                    <TextInput id="name" v-model="form.name" class="mt-1.5 block w-full" required maxlength="255" autocomplete="name" placeholder="Nama user" />
                    <InputError class="mt-1.5" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="email" value="Alamat email" />
                    <TextInput id="email" v-model="form.email" type="email" class="mt-1.5 block w-full" required maxlength="255" autocomplete="email" placeholder="nama@email.com" />
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>
                <div>
                    <InputLabel for="role" value="Peran user" />
                    <select id="role" v-model="form.role" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="student">Siswa</option>
                        <option value="admin">Administrator</option>
                    </select>
                    <InputError class="mt-1.5" :message="form.errors.role" />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <InputLabel for="password" :value="editing ? 'Kata sandi baru (opsional)' : 'Kata sandi'" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1.5 block w-full" :required="!editing" autocomplete="new-password" />
                        <InputError class="mt-1.5" :message="form.errors.password" />
                        <p v-if="editing" class="mt-1.5 text-xs text-slate-500">Kosongkan jika tidak ingin mengganti kata sandi.</p>
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" value="Konfirmasi kata sandi" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1.5 block w-full" :required="Boolean(form.password) || !editing" autocomplete="new-password" />
                        <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <Link :href="route('admin.users.index')"><SecondaryButton type="button" class="w-full justify-center sm:w-auto">Batal</SecondaryButton></Link>
                    <PrimaryButton :disabled="form.processing" class="w-full justify-center sm:w-auto">{{ form.processing ? 'Menyimpan...' : (editing ? 'Simpan Perubahan' : 'Simpan User') }}</PrimaryButton>
                </div>
            </div>
        </section>
    </form>
</template>

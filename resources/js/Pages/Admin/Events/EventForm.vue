<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { toJakartaDateTimeLocal } from '@/utils/date';

const props = defineProps({ event: { type: Object, default: null }, tests: { type: Array, required: true }, participants: { type: Array, required: true } });
const editing = computed(() => Boolean(props.event));
const selectedTestId = ref('');
const testSearch = ref('');
const selectedParticipantId = ref('');
const participantSearch = ref('');

const form = useForm({
    title: props.event?.title ?? '',
    description: props.event?.description ?? '',
    start_time: toJakartaDateTimeLocal(props.event?.start_time),
    end_time: toJakartaDateTimeLocal(props.event?.end_time),
    is_active: props.event?.is_active ?? true,
    test_ids: props.event?.tests?.map((test) => test.id) ?? [],
    participant_ids: props.event?.participants?.map((person) => person.id) ?? [],
});

const availableTests = computed(() => {
    const search = testSearch.value.trim().toLocaleLowerCase('id-ID');
    return props.tests.filter((test) => test.is_active && !form.test_ids.some((id) => Number(id) === Number(test.id))
        && (!search || test.title.toLocaleLowerCase('id-ID').includes(search)));
});
const selectedTests = computed(() => form.test_ids.map((id) => props.tests.find((test) => Number(test.id) === Number(id))).filter(Boolean));
const availableParticipants = computed(() => {
    const search = participantSearch.value.trim().toLocaleLowerCase('id-ID');
    return props.participants.filter((person) => !form.participant_ids.some((id) => Number(id) === Number(person.id))
        && (!search || `${person.name} ${person.email}`.toLocaleLowerCase('id-ID').includes(search)));
});
const selectedParticipants = computed(() => form.participant_ids.map((id) => props.participants.find((person) => Number(person.id) === Number(id))).filter(Boolean));

function addTest() {
    const id = Number(selectedTestId.value);
    if (!id || form.test_ids.some((testId) => Number(testId) === id)) return;
    form.test_ids.push(id);
    selectedTestId.value = '';
    testSearch.value = '';
}

function removeTest(id) {
    form.test_ids = form.test_ids.filter((testId) => testId !== id);
}

function addParticipant() {
    const id = Number(selectedParticipantId.value);
    if (!id || form.participant_ids.some((personId) => Number(personId) === id)) return;
    form.participant_ids.push(id);
    selectedParticipantId.value = '';
    participantSearch.value = '';
}

function removeParticipant(id) {
    form.participant_ids = form.participant_ids.filter((personId) => personId !== id);
}

function submit() {
    editing.value ? form.put(route('admin.events.update', props.event.id)) : form.post(route('admin.events.store'));
}
</script>

<template>
    <form class="mx-auto" @submit.prevent="submit">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-6 py-5">
                <h2 class="font-bold text-slate-900">{{ editing ? 'Pengaturan Event' : 'Informasi Event Baru' }}</h2>
                <p class="mt-0.5 text-sm text-slate-500">Isi informasi event, tambahkan test dan peserta dari pilihan di bawah.</p>
            </header>

            <div class="p-6">
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <InputLabel for="title" value="Nama event" />
                        <TextInput id="title" v-model="form.title" class="mt-1.5 block w-full" required maxlength="255" placeholder="Contoh: Ujian Akhir Semester" />
                        <InputError class="mt-1.5" :message="form.errors.title" />
                    </div>
                    <div class="md:col-span-2">
                        <InputLabel for="description" value="Deskripsi (opsional)" />
                        <textarea id="description" v-model="form.description" rows="3" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        <InputError class="mt-1.5" :message="form.errors.description" />
                    </div>
                    <div>
                        <InputLabel for="start_time" value="Waktu mulai" />
                        <TextInput id="start_time" v-model="form.start_time" type="datetime-local" class="mt-1.5 block w-full" required />
                        <InputError class="mt-1.5" :message="form.errors.start_time" />
                    </div>
                    <div>
                        <InputLabel for="end_time" value="Waktu selesai" />
                        <TextInput id="end_time" v-model="form.end_time" type="datetime-local" class="mt-1.5 block w-full" required />
                        <InputError class="mt-1.5" :message="form.errors.end_time" />
                    </div>
                    <label class="md:col-span-2 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <span><span class="block text-sm font-semibold text-slate-800">Event aktif</span><span class="mt-0.5 block text-xs text-slate-500">Peserta dapat mengakses test saat jadwal event berlangsung.</span></span>
                        <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-blue-600" />
                    </label>
                    <InputError class="md:col-span-2" :message="form.errors.is_active" />
                </div>

                <section class="mt-8 border-t border-slate-100 pt-7">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div><h2 class="font-bold text-slate-900">Test dalam event</h2><p class="mt-0.5 text-sm text-slate-500">Cari dan tambahkan satu atau lebih test.</p></div>
                        <span class="shrink-0 rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">{{ form.test_ids.length }} test dipilih</span>
                    </div>

                    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                        <div>
                            <InputLabel for="test-select" value="Pilih test" />
                            <select id="test-select" v-model="selectedTestId" class="mt-1.5 block w-full rounded-xl border-slate-300 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ availableTests.length ? 'Pilih test...' : 'Tidak ada hasil test' }}</option>
                                <option v-for="test in availableTests" :key="test.id" :value="String(test.id)">{{ test.title }} · {{ test.duration_minutes }} menit</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="test-search" value="Cari test" />
                            <div class="relative mt-1.5"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i><input id="test-search" v-model="testSearch" type="search" placeholder="Ketik judul test..." class="block w-full rounded-xl border-slate-300 py-2 pl-9 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" /></div>
                        </div>
                        <PrimaryButton type="button" class="justify-center" :disabled="!selectedTestId" @click="addTest"><i class="fa-solid fa-plus mr-2"></i>Tambahkan</PrimaryButton>
                    </div>
                    <p v-if="!tests.some((test) => test.is_active)" class="mt-3 text-sm text-slate-500">Belum ada test aktif. Buat test melalui menu Bank Test.</p>
                    <InputError class="mt-2" :message="form.errors.test_ids" />

                    <div class="mt-5">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500">Detail test terpilih</h3>
                        <div v-if="selectedTests.length" class="grid gap-3 md:grid-cols-2">
                            <article v-for="test in selectedTests" :key="test.id" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3"><div class="min-w-0"><h4 class="truncate font-bold text-slate-900">{{ test.title }}</h4><p class="mt-1 text-xs text-slate-500">Durasi pengerjaan {{ test.duration_minutes }} menit</p></div><button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600" :aria-label="`Hapus test ${test.title}`" @click="removeTest(test.id)"><i class="fa-regular fa-trash-can"></i></button></div>
                            </article>
                        </div>
                        <div v-else class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center"><i class="fa-regular fa-folder-open text-xl text-slate-300"></i><p class="mt-2 text-sm font-semibold text-slate-600">Belum ada test ditambahkan</p><p class="mt-1 text-xs text-slate-400">Pilih test dari dropdown, lalu tekan Tambahkan.</p></div>
                    </div>
                </section>

                <section class="mt-8 border-t border-slate-100 pt-7">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div><h2 class="font-bold text-slate-900">Peserta ditugaskan</h2><p class="mt-0.5 text-sm text-slate-500">Cari peserta berdasarkan nama atau email, lalu tambahkan.</p></div>
                        <span class="shrink-0 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ form.participant_ids.length }} peserta dipilih</span>
                    </div>

                    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                        <div>
                            <InputLabel for="participant-select" value="Pilih peserta" />
                            <select id="participant-select" v-model="selectedParticipantId" class="mt-1.5 block w-full rounded-xl border-slate-300 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ availableParticipants.length ? 'Pilih peserta...' : 'Tidak ada hasil peserta' }}</option>
                                <option v-for="person in availableParticipants" :key="person.id" :value="String(person.id)">{{ person.name }} · {{ person.email }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="participant-search" value="Cari peserta" />
                            <div class="relative mt-1.5"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i><input id="participant-search" v-model="participantSearch" type="search" placeholder="Ketik nama atau email..." class="block w-full rounded-xl border-slate-300 py-2 pl-9 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" /></div>
                        </div>
                        <PrimaryButton type="button" class="justify-center" :disabled="!selectedParticipantId" @click="addParticipant"><i class="fa-solid fa-user-plus mr-2"></i>Tambahkan</PrimaryButton>
                    </div>
                    <InputError class="mt-2" :message="form.errors.participant_ids" />

                    <div class="mt-5">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500">Detail peserta terpilih</h3>
                        <div v-if="selectedParticipants.length" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <article v-for="person in selectedParticipants" :key="person.id" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-extrabold text-blue-700">{{ person.name.charAt(0).toUpperCase() }}</span>
                                <span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold text-slate-800">{{ person.name }}</span><span class="block truncate text-xs text-slate-500">{{ person.email }}</span></span>
                                <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600" :aria-label="`Hapus peserta ${person.name}`" @click="removeParticipant(person.id)"><i class="fa-regular fa-trash-can"></i></button>
                            </article>
                        </div>
                        <div v-else class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center"><i class="fa-solid fa-users text-xl text-slate-300"></i><p class="mt-2 text-sm font-semibold text-slate-600">Belum ada peserta ditambahkan</p><p class="mt-1 text-xs text-slate-400">Cari peserta, pilih dari dropdown, lalu tekan Tambahkan.</p></div>
                    </div>
                </section>

                <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <Link :href="route('admin.events.index')"><SecondaryButton type="button" class="w-full justify-center sm:w-auto">Batal</SecondaryButton></Link>
                    <PrimaryButton :disabled="form.processing" class="w-full justify-center sm:w-auto">{{ form.processing ? 'Menyimpan...' : (editing ? 'Simpan Perubahan' : 'Simpan Event') }}</PrimaryButton>
                </div>
            </div>
        </section>
    </form>
</template>

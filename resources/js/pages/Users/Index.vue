<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Plus, Pencil, Trash2, Users } from 'lucide-vue-next';

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const showForm = ref(false);
const editMode = ref(false);
const selectedUserId = ref(null);
const search = ref('');
const errors = ref({});

const form = ref({
    name: '',
    email: '',
    password: '',
});

const filteredUsers = computed(() => {
    if (!search.value) return props.users;

    const keyword = search.value.toLowerCase();

    return props.users.filter((user) => {
        return (
            user.name.toLowerCase().includes(keyword) ||
            user.email.toLowerCase().includes(keyword)
        );
    });
});

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        password: '',
    };

    selectedUserId.value = null;
    editMode.value = false;
    errors.value = {};
};

const openCreate = () => {
    resetForm();
    showForm.value = true;
};

const openEdit = (user) => {
    editMode.value = true;
    selectedUserId.value = user.id;
    showForm.value = true;
    errors.value = {};

    form.value = {
        name: user.name,
        email: user.email,
        password: '',
    };
};

const closeForm = () => {
    showForm.value = false;
    resetForm();
};

const submitForm = () => {
    errors.value = {};

    if (editMode.value) {
        router.put(`/data-master/users/${selectedUserId.value}`, form.value, {
            preserveScroll: true,
            onSuccess: () => closeForm(),
            onError: (err) => {
                errors.value = err;
            },
        });

        return;
    }

    router.post('/data-master/users', form.value, {
        preserveScroll: true,
        onSuccess: () => closeForm(),
        onError: (err) => {
            errors.value = err;
        },
    });
};

const deleteUser = (user) => {
    const confirmed = confirm(`Hapus user ${user.name}?`);

    if (!confirmed) return;

    router.delete(`/data-master/users/${user.id}`, {
        preserveScroll: true,
        onError: (err) => {
            errors.value = err;
        },
    });
};

const formatDate = (date) => {
    if (!date) return '-';

    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Data Master - Users" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">
                    Data Master User
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola akun user biasa yang bisa login ke aplikasi.
                </p>
            </div>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                @click="openCreate"
            >
                <Plus class="h-4 w-4" />
                Tambah User
            </button>
        </div>

        <div
            v-if="errors.user"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ errors.user }}
        </div>

        <div class="rounded-xl border bg-card p-4 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-2">
                    <Users class="h-5 w-5 text-blue-500" />
                    <h2 class="font-semibold">
                        Daftar User
                    </h2>
                </div>

                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama atau email..."
                    class="w-full rounded-lg border px-3 py-2 text-sm md:w-72"
                />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] border-collapse text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Dibuat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(user, index) in filteredUsers"
                            :key="user.id"
                            class="border-b"
                        >
                            <td class="px-4 py-3">
                                {{ index + 1 }}
                            </td>

                            <td class="px-4 py-3 font-medium">
                                {{ user.name }}
                            </td>

                            <td class="px-4 py-3">
                                {{ user.email }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                    User
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                {{ formatDate(user.created_at) }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg border px-3 py-1.5 text-xs hover:bg-muted"
                                        @click="openEdit(user)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs text-red-600 hover:bg-red-50"
                                        @click="deleteUser(user)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                Tidak ada user ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div class="w-full max-w-lg rounded-xl bg-background p-6 shadow-lg">
                <h2 class="text-xl font-semibold">
                    {{ editMode ? 'Edit User' : 'Tambah User' }}
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    {{ editMode ? 'Update data user biasa.' : 'Buat akun login user baru.' }}
                </p>

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Nama
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border px-3 py-2"
                            placeholder="Nama user"
                        />

                        <p v-if="errors.name" class="mt-1 text-sm text-red-500">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Email
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border px-3 py-2"
                            placeholder="email@example.com"
                        />

                        <p v-if="errors.email" class="mt-1 text-sm text-red-500">
                            {{ errors.email }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Password
                        </label>

                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full rounded-lg border px-3 py-2"
                            :placeholder="editMode ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'"
                        />

                        <p v-if="errors.password" class="mt-1 text-sm text-red-500">
                            {{ errors.password }}
                        </p>
                    </div>

                    <div class="rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-700">
                        Akun yang dibuat dari halaman ini otomatis menjadi
                        <strong>user biasa</strong>. Admin hanya dibuat dari seeder dan hanya ada 1 akun.
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                        @click="closeForm"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        @click="submitForm"
                    >
                        {{ editMode ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

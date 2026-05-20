<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import {
    Plus,
    Pencil,
    Trash2,
    Users,
    X,
    Save,
    Search,
    UserPlus,
} from 'lucide-vue-next';

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
const processing = ref(false);

const showDeleteModal = ref(false);
const selectedDeleteUser = ref(null);
const deleteProcessing = ref(false);

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
    processing.value = false;
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
    processing.value = false;

    form.value = {
        name: user.name,
        email: user.email,
        password: '',
    };
};

const closeForm = () => {
    if (processing.value) return;

    showForm.value = false;
    resetForm();
};

const submitForm = () => {
    errors.value = {};
    processing.value = true;

    const payload = {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
    };

    const options = {
        preserveScroll: true,
        onSuccess: () => closeForm(),
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (editMode.value) {
        router.put(
            `/data-master/users/${selectedUserId.value}`,
            payload,
            options,
        );
        return;
    }

    router.post('/data-master/users', payload, options);
};

const openDeleteModal = (user) => {
    selectedDeleteUser.value = user;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    if (deleteProcessing.value) return;

    selectedDeleteUser.value = null;
    showDeleteModal.value = false;
};

const confirmDeleteUser = () => {
    if (!selectedDeleteUser.value) return;

    deleteProcessing.value = true;

    router.delete(`/data-master/users/${selectedDeleteUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            deleteProcessing.value = false;
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
        <!-- Header -->
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold">Data Master User</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola akun user biasa yang bisa login ke aplikasi.
                </p>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                @click="openCreate"
            >
                <Plus class="h-4 w-4" />
                Tambah User
            </button>
        </div>

        <div
            v-if="errors.user"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-200"
        >
            {{ errors.user }}
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Total User</p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ users.length }}
                        </p>
                    </div>

                    <Users class="h-8 w-8 text-blue-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Hasil Pencarian
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ filteredUsers.length }}
                        </p>
                    </div>

                    <Search class="h-8 w-8 text-indigo-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Role Default
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-500">
                            User
                        </p>
                    </div>

                    <UserPlus class="h-8 w-8 text-emerald-500" />
                </div>
            </div>
        </div>

        <!-- User List -->
        <div class="rounded-xl border bg-card p-4 shadow-sm">
            <div
                class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-2">
                    <Users class="h-5 w-5 text-blue-500" />
                    <div>
                        <h2 class="font-semibold">Daftar User</h2>
                        <p class="text-sm text-muted-foreground">
                            User yang dibuat di halaman ini otomatis mendapat
                            kategori default.
                        </p>
                    </div>
                </div>

                <div class="relative w-full md:w-80">
                    <Search
                        class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground"
                    />

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama atau email..."
                        class="w-full rounded-lg border bg-background py-2 pr-3 pl-9 text-sm"
                    />
                </div>
            </div>

            <EmptyState
                v-if="props.users.length === 0"
                :icon="Users"
                title="Belum ada user"
                description="Tambahkan user biasa agar bisa mulai menggunakan aplikasi."
                action-label="Tambah User"
                button-type="button"
                @action="openCreate"
            />

            <EmptyState
                v-else-if="filteredUsers.length === 0"
                :icon="Search"
                title="User tidak ditemukan"
                description="Tidak ada user yang cocok dengan kata kunci pencarian."
            />

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse text-sm">
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
                            class="border-b transition hover:bg-muted/40"
                        >
                            <td class="px-4 py-3">
                                {{ index + 1 }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                    >
                                        {{
                                            user.name
                                                ?.charAt(0)
                                                ?.toUpperCase() || '?'
                                        }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate font-medium">
                                            {{ user.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            User biasa
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                {{ user.email }}
                            </td>

                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
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
                                        class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-xs transition hover:bg-muted"
                                        @click="openEdit(user)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-3 py-2 text-xs text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                                        @click="openDeleteModal(user)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div class="w-full max-w-lg rounded-xl bg-background p-6 shadow-lg">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold">
                            {{ editMode ? 'Edit User' : 'Tambah User' }}
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            {{
                                editMode
                                    ? 'Update data user biasa.'
                                    : 'Buat akun login user baru.'
                            }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="processing"
                        @click="closeForm"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="submitForm">
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Nama
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border bg-background px-3 py-2"
                            placeholder="Nama user"
                            :disabled="processing"
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
                            class="w-full rounded-lg border bg-background px-3 py-2"
                            placeholder="email@example.com"
                            :disabled="processing"
                        />

                        <p
                            v-if="errors.email"
                            class="mt-1 text-sm text-red-500"
                        >
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
                            class="w-full rounded-lg border bg-background px-3 py-2"
                            :placeholder="
                                editMode
                                    ? 'Kosongkan jika tidak diubah'
                                    : 'Minimal 8 karakter'
                            "
                            :disabled="processing"
                        />

                        <p
                            v-if="errors.password"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.password }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200"
                    >
                        Akun yang dibuat dari halaman ini otomatis menjadi
                        <strong>user biasa</strong> dan akan dibuatkan kategori
                        default. Admin hanya dibuat dari seeder dan hanya ada 1
                        akun.
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            class="rounded-lg border px-4 py-2 text-sm transition hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="processing"
                            @click="closeForm"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            :disabled="processing"
                        >
                            <Save class="h-4 w-4" />
                            {{
                                processing
                                    ? 'Menyimpan...'
                                    : editMode
                                      ? 'Update User'
                                      : 'Simpan User'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus User?"
            description="User ini akan dihapus permanen beserta data yang terkait dengan akun tersebut."
            :item-name="selectedDeleteUser?.name"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="confirmDeleteUser"
        />
    </div>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { User, Upload, Lock, Trash2, Eye, EyeOff } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Settings',
                href: '/settings/profile',
            },
        ],
    },
});

const page = usePage();

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = computed(() => page.props.auth.user);

const formData = ref({
    name: user.value?.name || '',
    email: user.value?.email || '',
    photo: null,
});

const passwordData = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirm = ref(false);
const photoPreview = ref(user.value?.photo_url || null);
const processingProfile = ref(false);
const processingPassword = ref(false);
const errors = ref({});
const success = ref('');

const handlePhotoSelect = (e) => {
    const file = e.target.files?.[0];

    if (!file) return;

    formData.value.photo = file;

    const reader = new FileReader();

    reader.onload = (event) => {
        photoPreview.value = event.target.result;
    };

    reader.readAsDataURL(file);
};

const updateProfile = () => {
    processingProfile.value = true;
    errors.value = {};
    success.value = '';

    const form = new FormData();

    form.append('name', formData.value.name);
    form.append('email', formData.value.email);

    if (formData.value.photo) {
        form.append('photo', formData.value.photo);
    }

    router.post('/settings/profile', form, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            success.value = 'Profile updated successfully!';
            formData.value.photo = null;

            setTimeout(() => {
                success.value = '';
            }, 3000);
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processingProfile.value = false;
        },
    });
};

const updatePassword = () => {
    processingPassword.value = true;
    errors.value = {};
    success.value = '';

    router.post('/settings/password', passwordData.value, {
        preserveScroll: true,
        onSuccess: () => {
            success.value = 'Password updated successfully!';

            passwordData.value = {
                current_password: '',
                password: '',
                password_confirmation: '',
            };

            setTimeout(() => {
                success.value = '';
            }, 3000);
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processingPassword.value = false;
        },
    });
};

const removePhoto = () => {
    photoPreview.value = user.value?.photo_url || null;
    formData.value.photo = null;
};

const deleteAccount = () => {
    const confirmed = confirm(
        'Are you sure you want to delete your account? This action cannot be undone.',
    );

    if (!confirmed) return;

    router.delete('/settings/profile', {
        preserveScroll: true,
        onError: (err) => {
            errors.value = err;
        },
    });
};
</script>

<template>
    <Head title="Profile Settings" />

    <div class="w-full">
        <div class="max-w-4xl space-y-6">
            <div
                v-if="success"
                class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700"
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"
                    />
                </svg>
                {{ success }}
            </div>

            <div
                v-if="mustVerifyEmail && user?.email_verified_at === null"
                class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-700"
            >
                <p class="text-sm">
                    Please verify your email address to continue.
                </p>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="h-32 bg-gradient-to-r from-blue-500 to-indigo-600"
                ></div>

                <div class="px-6 pb-6">
                    <div
                        class="relative z-10 -mt-16 flex flex-col items-start gap-6 md:flex-row md:items-center"
                    >
                        <div
                            class="h-32 w-32 overflow-hidden rounded-full border-4 border-white bg-slate-200 shadow-lg"
                        >
                            <img
                                v-if="photoPreview"
                                :src="photoPreview"
                                :alt="formData.name"
                                class="h-full w-full object-cover"
                                @error="photoPreview = null"
                            />

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-400 to-indigo-600"
                            >
                                <User class="h-16 w-16 text-white" />
                            </div>
                        </div>

                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-slate-900">
                                {{ formData.name }}
                            </h1>

                            <p class="text-sm text-slate-500">
                                {{ formData.email }}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
                                >
                                    <Upload class="h-4 w-4" />
                                    <span>Upload Photo</span>

                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg"
                                        class="hidden"
                                        @change="handlePhotoSelect"
                                    />
                                </label>

                                <button
                                    v-if="photoPreview && formData.photo"
                                    type="button"
                                    class="rounded-lg bg-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-300"
                                    @click="removePhoto"
                                >
                                    Remove
                                </button>
                            </div>

                            <p class="mt-2 text-xs text-slate-500">
                                JPG, PNG only. Max 5MB.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-6 flex items-center gap-2">
                    <User class="h-5 w-5 text-blue-500" />

                    <h2 class="text-lg font-semibold text-slate-900">
                        Profile Information
                    </h2>
                </div>

                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Full Name
                        </label>

                        <input
                            v-model="formData.name"
                            type="text"
                            placeholder="Your full name"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                        />

                        <div
                            v-if="errors.name"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.name }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Email Address
                        </label>

                        <input
                            v-model="formData.email"
                            type="email"
                            placeholder="your@email.com"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                        />

                        <div
                            v-if="errors.email"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.email }}
                        </div>

                        <p
                            v-if="mustVerifyEmail && user?.email_verified_at === null"
                            class="mt-1 text-sm text-yellow-600"
                        >
                            Your email is not verified
                        </p>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <button
                            type="button"
                            :disabled="processingProfile"
                            class="rounded-lg bg-blue-500 px-6 py-2 font-medium text-white transition hover:bg-blue-600 disabled:bg-slate-300"
                            @click="updateProfile"
                        >
                            {{
                                processingProfile
                                    ? 'Saving...'
                                    : 'Save Changes'
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-6 flex items-center gap-2">
                    <Lock class="h-5 w-5 text-blue-500" />

                    <h2 class="text-lg font-semibold text-slate-900">
                        Change Password
                    </h2>
                </div>

                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Current Password
                        </label>

                        <input
                            v-model="passwordData.current_password"
                            type="password"
                            placeholder="Enter current password"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                        />

                        <div
                            v-if="errors.current_password"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.current_password }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            New Password
                        </label>

                        <div class="relative">
                            <input
                                v-model="passwordData.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Enter new password (min. 8 characters)"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2 pr-12 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                            />

                            <button
                                type="button"
                                class="absolute top-2.5 right-3 text-slate-500 hover:text-slate-700"
                                @click="showPassword = !showPassword"
                            >
                                <Eye v-if="showPassword" class="h-5 w-5" />
                                <EyeOff v-else class="h-5 w-5" />
                            </button>
                        </div>

                        <div
                            v-if="errors.password"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.password }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Confirm Password
                        </label>

                        <div class="relative">
                            <input
                                v-model="passwordData.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                placeholder="Confirm new password"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2 pr-12 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                            />

                            <button
                                type="button"
                                class="absolute top-2.5 right-3 text-slate-500 hover:text-slate-700"
                                @click="showConfirm = !showConfirm"
                            >
                                <Eye v-if="showConfirm" class="h-5 w-5" />
                                <EyeOff v-else class="h-5 w-5" />
                            </button>
                        </div>

                        <div
                            v-if="errors.password_confirmation"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.password_confirmation }}
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <button
                            type="button"
                            :disabled="processingPassword"
                            class="rounded-lg bg-blue-500 px-6 py-2 font-medium text-white transition hover:bg-blue-600 disabled:bg-slate-300"
                            @click="updatePassword"
                        >
                            {{
                                processingPassword
                                    ? 'Updating...'
                                    : 'Update Password'
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-red-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-6 flex items-center gap-2">
                    <Trash2 class="h-5 w-5 text-red-500" />

                    <h2 class="text-lg font-semibold text-slate-900">
                        Danger Zone
                    </h2>
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <p class="mb-4 text-sm text-red-700">
                        Deleting your account is permanent and cannot be undone.
                        All your data including accounts, transactions, and
                        notes will be deleted.
                    </p>

                    <button
                        type="button"
                        class="rounded-lg bg-red-500 px-6 py-2 font-medium text-white transition hover:bg-red-600"
                        @click="deleteAccount"
                    >
                        Delete My Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

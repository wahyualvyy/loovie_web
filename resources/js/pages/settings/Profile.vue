<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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
const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = computed(() => page.props.auth.user);

// Form state
const formData = ref({
    name: user.value.name || '',
    email: user.value.email || '',
    photo: null,
});

const passwordData = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirm = ref(false);
const photoPreview = ref(user.value.photo_url || null);
const processing = ref(false);
const errors = ref({});
const success = ref('');

// Handle photo selection
const handlePhotoSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
        formData.value.photo = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            photoPreview.value = event.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Update profile
const updateProfile = async () => {
    processing.value = true;
    errors.value = {};
    success.value = '';

    const form = new FormData();
    form.append('name', formData.value.name);
    form.append('email', formData.value.email);
    if (formData.value.photo) {
        form.append('photo', formData.value.photo);
    }

    try {
        router.post(route('profile.update'), form, {
            onSuccess: () => {
                success.value = 'Profile updated successfully!';
                setTimeout(() => (success.value = ''), 3000);
                processing.value = false;
            },
            onError: (err) => {
                errors.value = err;
                processing.value = false;
            },
        });
    } catch (err) {
        console.error(err);
        processing.value = false;
    }
};

// Update password
const updatePassword = async () => {
    processing.value = true;
    errors.value = {};
    success.value = '';

    try {
        router.post(route('password.update'), passwordData.value, {
            onSuccess: () => {
                success.value = 'Password updated successfully!';
                passwordData.value = {
                    current_password: '',
                    password: '',
                    password_confirmation: '',
                };
                setTimeout(() => (success.value = ''), 3000);
                processing.value = false;
            },
            onError: (err) => {
                errors.value = err;
                processing.value = false;
            },
        });
    } catch (err) {
        console.error(err);
        processing.value = false;
    }
};

// Remove photo
const removePhoto = () => {
    photoPreview.value = user.value.photo_url || null;
    formData.value.photo = null;
};

// Delete account
const deleteAccount = () => {
    if (
        confirm(
            'Are you sure you want to delete your account? This action cannot be undone.',
        )
    ) {
        router.delete(route('profile.destroy'), {
            onError: (err) => {
                errors.value = err;
            },
        });
    }
};
</script>

<template>
    <Head title="Profile Settings" />

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 md:p-8"
    >
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Success Message -->
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

            <!-- Email Verification Notice -->
            <div
                v-if="mustVerifyEmail && user.email_verified_at === null"
                class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-700"
            >
                <p class="text-sm">
                    Please verify your email address to continue.
                </p>
            </div>

            <!-- Profile Photo Section -->
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
                            <div class="mt-4 flex gap-2">
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
                                >
                                    <Upload class="h-4 w-4" />
                                    <span>Upload Photo</span>
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg"
                                        @change="handlePhotoSelect"
                                        class="hidden"
                                    />
                                </label>
                                <button
                                    v-if="photoPreview && formData.photo"
                                    @click="removePhoto"
                                    class="rounded-lg bg-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-300"
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

            <!-- Profile Information Section -->
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
                    <!-- Name Field -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                            >Full Name</label
                        >
                        <input
                            v-model="formData.name"
                            type="text"
                            placeholder="Your full name"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                        />
                        <div
                            v-if="errors.name"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.name }}
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                            >Email Address</label
                        >
                        <input
                            v-model="formData.email"
                            type="email"
                            placeholder="your@email.com"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                        />
                        <div
                            v-if="errors.email"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.email }}
                        </div>
                        <p
                            v-if="
                                mustVerifyEmail &&
                                user.email_verified_at === null
                            "
                            class="mt-1 text-sm text-yellow-600"
                        >
                            Your email is not verified
                        </p>
                    </div>

                    <!-- Save Button -->
                    <div class="flex gap-2 pt-4">
                        <button
                            @click="updateProfile"
                            :disabled="processing"
                            class="rounded-lg bg-blue-500 px-6 py-2 font-medium text-white transition hover:bg-blue-600 disabled:bg-slate-300"
                        >
                            {{ processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Password Section -->
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
                    <!-- Current Password -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                            >Current Password</label
                        >
                        <input
                            v-model="passwordData.current_password"
                            type="password"
                            placeholder="Enter current password"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                        />
                        <div
                            v-if="errors.current_password"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.current_password }}
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                            >New Password</label
                        >
                        <div class="relative">
                            <input
                                v-model="passwordData.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Enter new password (min. 8 characters)"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                            />
                            <button
                                @click="showPassword = !showPassword"
                                type="button"
                                class="absolute top-2.5 right-3 text-slate-500 hover:text-slate-700"
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

                    <!-- Confirm Password -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                            >Confirm Password</label
                        >
                        <div class="relative">
                            <input
                                v-model="passwordData.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                placeholder="Confirm new password"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-blue-500"
                            />
                            <button
                                @click="showConfirm = !showConfirm"
                                type="button"
                                class="absolute top-2.5 right-3 text-slate-500 hover:text-slate-700"
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

                    <!-- Password Update Button -->
                    <div class="flex gap-2 pt-4">
                        <button
                            @click="updatePassword"
                            :disabled="processing"
                            class="rounded-lg bg-blue-500 px-6 py-2 font-medium text-white transition hover:bg-blue-600 disabled:bg-slate-300"
                        >
                            {{ processing ? 'Updating...' : 'Update Password' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
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
                        @click="deleteAccount"
                        class="rounded-lg bg-red-500 px-6 py-2 font-medium text-white transition hover:bg-red-600"
                    >
                        Delete My Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

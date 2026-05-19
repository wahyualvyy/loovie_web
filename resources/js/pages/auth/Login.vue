<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { request } from '@/routes/password';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: GuestLayout,
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const processing = ref(false);
const showPassword = ref(false);

const submit = () => {
    processing.value = true;
    form.post('/login', {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Login - Loovie Apps" />

    <!-- Background Gradient -->
    <div class="fixed inset-0 -z-10">
        <div
            class="absolute inset-0 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 opacity-10"
        ></div>
        <div
            class="animate-blob absolute top-0 right-0 h-96 w-96 rounded-full bg-blue-400 opacity-20 mix-blend-multiply blur-3xl filter"
        ></div>
        <div
            class="animate-blob animation-delay-2000 absolute bottom-0 left-0 h-96 w-96 rounded-full bg-purple-400 opacity-20 mix-blend-multiply blur-3xl filter"
        ></div>
    </div>

    <div
        class="flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8"
    >
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="mb-8 text-center">
                <h1
                    class="mb-2 bg-gradient-to-r from-indigo-600 to-pink-600 bg-clip-text text-3xl font-bold text-transparent sm:text-4xl"
                >
                    Loovie Apps
                </h1>
                <p
                    class="text-sm text-gray-600 sm:text-base dark:text-gray-400"
                >
                    Daily Finance Management
                </p>
            </div>

            <!-- Card -->
            <div
                class="bg-opacity-95 dark:bg-opacity-95 rounded-xl border border-white/20 bg-white p-6 shadow-xl backdrop-blur-md sm:p-8 dark:border-gray-800/50 dark:bg-gray-900"
            >
                <!-- Status Message -->
                <div
                    v-if="status"
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <h2
                    class="mb-6 text-xl font-bold text-gray-900 sm:text-2xl dark:text-white"
                >
                    Welcome Back
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <Label
                            for="email"
                            class="font-medium text-gray-700 dark:text-gray-300"
                        >
                            Email Address
                        </Label>
                        <div class="relative">
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="h-11 rounded-lg border-gray-200 bg-gray-50 pl-10 transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                            />
                            <svg
                                class="absolute top-3 left-3 h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <InputError
                            :message="form.errors.email"
                            class="text-sm"
                        />
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label
                                for="password"
                                class="font-medium text-gray-700 dark:text-gray-300"
                            >
                                Password
                            </Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm text-indigo-600 transition-colors hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                Forgot?
                            </TextLink>
                        </div>
                        <div class="relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="h-11 rounded-lg border-gray-200 bg-gray-50 pr-10 pl-10 transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                            />
                            <svg
                                class="absolute top-3 left-3 h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute top-3 right-3 text-gray-400 transition-colors hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg
                                    v-if="!showPassword"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m4.753-4.753L3.3 3.3m11.4 11.4l4.2 4.2M9.25 9.25l4.5 4.5m0-4.5l-4.5 4.5"
                                    />
                                </svg>
                            </button>
                        </div>
                        <InputError
                            :message="form.errors.password"
                            class="text-sm"
                        />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center space-x-2">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            name="remember"
                            class="h-4 w-4 cursor-pointer rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label
                            for="remember"
                            class="cursor-pointer text-sm text-gray-700 dark:text-gray-300"
                        >
                            Remember me
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="mt-6 flex h-11 w-full transform items-center justify-center space-x-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 font-semibold text-white transition-all hover:scale-105 hover:from-indigo-700 hover:to-purple-700 disabled:transform-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Spinner v-if="processing" class="h-4 w-4" />
                        <span>{{
                            processing ? 'Signing in...' : 'Sign In'
                        }}</span>
                    </Button>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div
                                class="w-full border-t border-gray-300 dark:border-gray-700"
                            ></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span
                                class="bg-white px-2 text-gray-500 dark:bg-gray-900 dark:text-gray-400"
                                >or</span
                            >
                        </div>
                    </div>

                    <!-- Sign Up Link -->
                    <p
                        class="text-center text-sm text-gray-600 dark:text-gray-400"
                    >
                        Don't have an account?
                        <TextLink
                            href="/register"
                            class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            Create one
                        </TextLink>
                    </p>
                </form>
            </div>

            <!-- Footer -->
            <div
                class="mt-8 text-center text-xs text-gray-500 sm:text-sm dark:text-gray-400"
            >
                <p>© 2026 Loovie Apps. All rights reserved.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}
</style>

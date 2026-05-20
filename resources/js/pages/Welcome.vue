<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    WalletCards,
    BarChart3,
    PiggyBank,
    NotebookText,
    ShieldCheck,
    ArrowRight,
    LogIn,
    Sparkles,
    ReceiptText,
} from 'lucide-vue-next';
</script>

<template>
    <Head title="Welcome - Loovie Apps" />

    <div
        class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-white"
    >
        <!-- Navbar -->
        <header
            class="sticky top-0 z-30 border-b bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/80"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 text-white shadow-lg"
                    >
                        <WalletCards class="h-6 w-6" />
                    </div>

                    <div>
                        <div class="flex items-center gap-1">
                            <span class="text-lg font-bold tracking-tight">
                                Loovie
                            </span>
                            <span
                                class="text-lg font-bold tracking-tight text-indigo-500"
                            >
                                Apps
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Manajemen Keuangan
                        </p>
                    </div>
                </div>

                <nav class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700"
                    >
                        Dashboard
                        <ArrowRight class="h-4 w-4" />
                    </Link>

                    <Link
                        v-else
                        href="/login"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700"
                    >
                        <LogIn class="h-4 w-4" />
                        Login
                    </Link>
                </nav>
            </div>
        </header>

        <main>
            <!-- Hero -->
            <section class="relative overflow-hidden">
                <div class="absolute inset-0 -z-10">
                    <div
                        class="absolute top-0 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-indigo-300/30 blur-3xl dark:bg-indigo-700/20"
                    ></div>
                    <div
                        class="absolute top-40 right-10 h-72 w-72 rounded-full bg-purple-300/30 blur-3xl dark:bg-purple-700/20"
                    ></div>
                    <div
                        class="absolute bottom-0 left-10 h-72 w-72 rounded-full bg-blue-300/30 blur-3xl dark:bg-blue-700/20"
                    ></div>
                </div>

                <div
                    class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-24"
                >
                    <div>
                        <div
                            class="mb-5 inline-flex items-center gap-2 rounded-full border bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <Sparkles class="h-4 w-4 text-indigo-500" />
                            Kelola keuangan lebih mudah dan rapi
                        </div>

                        <h1
                            class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl"
                        >
                            Catat, atur, dan pantau
                            <span
                                class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent"
                            >
                                keuanganmu
                            </span>
                            dalam satu aplikasi.
                        </h1>

                        <p
                            class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg dark:text-slate-300"
                        >
                            Loovie Apps membantu kamu mencatat transaksi,
                            mengelola akun keuangan, membuat budget bulanan,
                            menyimpan catatan, dan melihat laporan keuangan
                            dengan tampilan yang sederhana.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <Link
                                v-if="$page.props.auth.user"
                                href="/dashboard"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-700"
                            >
                                Buka Dashboard
                                <ArrowRight class="h-4 w-4" />
                            </Link>

                            <Link
                                v-else
                                href="/login"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-700"
                            >
                                Login Sekarang
                                <ArrowRight class="h-4 w-4" />
                            </Link>

                            <a
                                href="#fitur"
                                class="inline-flex items-center justify-center rounded-xl border bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                Lihat Fitur
                            </a>
                        </div>

                        <p
                            class="mt-4 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Registrasi publik dinonaktifkan. Akun user dibuat
                            oleh admin melalui Data Master.
                        </p>
                    </div>

                    <!-- Hero Card -->
                    <div class="relative">
                        <div
                            class="rounded-3xl border bg-white p-5 shadow-2xl shadow-slate-200/80 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none"
                        >
                            <div class="mb-5 flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Total Saldo
                                    </p>
                                    <p class="mt-1 text-3xl font-bold">
                                        Rp 12.500.000
                                    </p>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300"
                                >
                                    <WalletCards class="h-6 w-6" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    class="rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-950/30"
                                >
                                    <p
                                        class="text-xs text-emerald-700 dark:text-emerald-300"
                                    >
                                        Pemasukan
                                    </p>
                                    <p
                                        class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400"
                                    >
                                        Rp 4.200.000
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl bg-red-50 p-4 dark:bg-red-950/30"
                                >
                                    <p
                                        class="text-xs text-red-700 dark:text-red-300"
                                    >
                                        Pengeluaran
                                    </p>
                                    <p
                                        class="mt-1 text-lg font-bold text-red-600 dark:text-red-400"
                                    >
                                        Rp 1.850.000
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-5 rounded-2xl border p-4 dark:border-slate-800"
                            >
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <p class="text-sm font-semibold">
                                        Budget Bulanan
                                    </p>
                                    <span
                                        class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                                    >
                                        Aman
                                    </span>
                                </div>

                                <div
                                    class="mb-2 flex justify-between text-xs text-slate-500 dark:text-slate-400"
                                >
                                    <span>Terpakai</span>
                                    <span>62%</span>
                                </div>

                                <div
                                    class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                >
                                    <div
                                        class="h-full w-[62%] rounded-full bg-emerald-500"
                                    ></div>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                <div
                                    class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 dark:bg-slate-800"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-9 w-9 rounded-full bg-blue-500"
                                        ></div>
                                        <div>
                                            <p class="text-sm font-medium">
                                                Gaji Bulanan
                                            </p>
                                            <p
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Bank BCA
                                            </p>
                                        </div>
                                    </div>
                                    <p
                                        class="text-sm font-bold text-emerald-500"
                                    >
                                        +Rp 4.000.000
                                    </p>
                                </div>

                                <div
                                    class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 dark:bg-slate-800"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-9 w-9 rounded-full bg-red-500"
                                        ></div>
                                        <div>
                                            <p class="text-sm font-medium">
                                                Makan
                                            </p>
                                            <p
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Cash
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-red-500">
                                        -Rp 75.000
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section
                id="fitur"
                class="border-y bg-white py-16 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2
                            class="text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Fitur utama Loovie Apps
                        </h2>
                        <p class="mt-4 text-slate-600 dark:text-slate-300">
                            Semua kebutuhan dasar manajemen keuangan personal
                            tersedia dalam satu tempat.
                        </p>
                    </div>

                    <div
                        class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            class="rounded-2xl border bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-300"
                            >
                                <ReceiptText class="h-6 w-6" />
                            </div>
                            <h3 class="mt-4 font-semibold">Transaksi</h3>
                            <p
                                class="mt-2 text-sm text-slate-600 dark:text-slate-400"
                            >
                                Catat pemasukan dan pengeluaran berdasarkan akun
                                dan kategori.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300"
                            >
                                <PiggyBank class="h-6 w-6" />
                            </div>
                            <h3 class="mt-4 font-semibold">Budget</h3>
                            <p
                                class="mt-2 text-sm text-slate-600 dark:text-slate-400"
                            >
                                Buat budget bulanan agar pengeluaran lebih
                                terkendali.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300"
                            >
                                <BarChart3 class="h-6 w-6" />
                            </div>
                            <h3 class="mt-4 font-semibold">Laporan</h3>
                            <p
                                class="mt-2 text-sm text-slate-600 dark:text-slate-400"
                            >
                                Pantau ringkasan bulanan, kategori, dan
                                transaksi terbesar.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-100 text-yellow-600 dark:bg-yellow-950 dark:text-yellow-300"
                            >
                                <NotebookText class="h-6 w-6" />
                            </div>
                            <h3 class="mt-4 font-semibold">Catatan</h3>
                            <p
                                class="mt-2 text-sm text-slate-600 dark:text-slate-400"
                            >
                                Simpan pengingat, rencana tabungan, atau catatan
                                penting.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Admin note -->
            <section class="py-16">
                <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div
                        class="rounded-3xl border bg-gradient-to-br from-indigo-600 to-purple-700 p-8 text-white shadow-xl"
                    >
                        <div
                            class="grid grid-cols-1 gap-6 md:grid-cols-[auto_1fr_auto] md:items-center"
                        >
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15"
                            >
                                <ShieldCheck class="h-7 w-7" />
                            </div>

                            <div>
                                <h2 class="text-2xl font-bold">
                                    Akses user dikelola oleh admin
                                </h2>
                                <p class="mt-2 text-sm text-indigo-100">
                                    Tidak ada pendaftaran publik. Admin membuat
                                    akun user melalui menu Data Master agar
                                    akses aplikasi lebih terkontrol.
                                </p>
                            </div>

                            <Link
                                v-if="$page.props.auth.user"
                                href="/dashboard"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50"
                            >
                                Dashboard
                                <ArrowRight class="h-4 w-4" />
                            </Link>

                            <Link
                                v-else
                                href="/login"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50"
                            >
                                Login
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer
            class="border-t bg-white py-6 dark:border-slate-800 dark:bg-slate-950"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col gap-2 px-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8 dark:text-slate-400"
            >
                <p>© {{ new Date().getFullYear() }} Loovie Apps.</p>

                <p>Personal finance management.</p>
            </div>
        </footer>
    </div>
</template>

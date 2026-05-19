<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountExportController extends Controller
{
    public function exportCSV(Request $request): StreamedResponse
    {
        $user = $request->user();

        $accounts = $user->financialAccounts()
            ->orderBy('name')
            ->get();

        $totalAccounts = $accounts->count();
        $totalInitialBalance = $accounts->sum('initial_balance');
        $totalCurrentBalance = $accounts->sum('current_balance');

        $fileName = 'laporan-akun-keuangan-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use (
            $accounts,
            $user,
            $totalAccounts,
            $totalInitialBalance,
            $totalCurrentBalance
        ) {
            $handle = fopen('php://output', 'w');

            // BOM agar Excel Windows membaca UTF-8 dengan benar
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Gunakan delimiter titik koma agar lebih aman untuk Excel regional Indonesia
            $delimiter = ';';

            // Header laporan
            fputcsv($handle, ['LAPORAN AKUN KEUANGAN'], $delimiter);
            fputcsv($handle, ['Nama User', $user->name], $delimiter);
            fputcsv($handle, ['Email', $user->email], $delimiter);
            fputcsv($handle, ['Tanggal Export', now()->format('d-m-Y H:i:s')], $delimiter);
            fputcsv($handle, [], $delimiter);

            // Ringkasan
            fputcsv($handle, ['RINGKASAN'], $delimiter);
            fputcsv($handle, ['Total Akun', $totalAccounts], $delimiter);
            fputcsv($handle, ['Total Saldo Awal', $totalInitialBalance], $delimiter);
            fputcsv($handle, ['Total Saldo Sekarang', $totalCurrentBalance], $delimiter);
            fputcsv($handle, ['Selisih Saldo', $totalCurrentBalance - $totalInitialBalance], $delimiter);
            fputcsv($handle, [], $delimiter);

            // Header tabel
            fputcsv($handle, [
                'No',
                'Nama Akun',
                'Tipe Akun',
                'Saldo Awal',
                'Saldo Sekarang',
                'Selisih Saldo',
                'Status',
                'Deskripsi',
                'Tanggal Dibuat',
                'Tanggal Diperbarui',
            ], $delimiter);

            foreach ($accounts as $index => $account) {
                $initialBalance = (float) ($account->initial_balance ?? 0);
                $currentBalance = (float) ($account->current_balance ?? 0);
                $balanceDifference = $currentBalance - $initialBalance;

                fputcsv($handle, [
                    $index + 1,
                    $account->name ?? '-',
                    $account->type ?? '-',
                    $initialBalance,
                    $currentBalance,
                    $balanceDifference,
                    $account->is_active ? 'Aktif' : 'Nonaktif',
                    $account->description ?? '-',
                    optional($account->created_at)->format('d-m-Y H:i:s'),
                    optional($account->updated_at)->format('d-m-Y H:i:s'),
                ], $delimiter);
            }

            // Footer total
            fputcsv($handle, [], $delimiter);
            fputcsv($handle, [
                '',
                'TOTAL',
                '',
                $totalInitialBalance,
                $totalCurrentBalance,
                $totalCurrentBalance - $totalInitialBalance,
                '',
                '',
                '',
                '',
            ], $delimiter);

            fclose($handle);
        }, $fileName, $headers);
    }

    public function printReport(Request $request)
    {
        $user = $request->user();

        $accounts = $user->financialAccounts()
            ->orderBy('name')
            ->get();

        $totalBalance = $accounts->sum('current_balance');
        $totalInitialBalance = $accounts->sum('initial_balance');

        return view('accounts.print', compact(
            'accounts',
            'totalBalance',
            'totalInitialBalance'
        ));
    }
}

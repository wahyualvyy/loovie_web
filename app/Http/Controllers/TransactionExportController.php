<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionExportController extends Controller
{
    /**
     * Export transactions to CSV.
     */
    public function exportCSV(Request $request): StreamedResponse
    {
        $user = Auth::user();

        $filters = $request->only([
            'account_id',
            'category_id',
            'type',
            'date_from',
            'date_to',
            'month',
        ]);

        $transactions = $this->getFilteredTransactions($user, $filters);

        $totalIncome = $transactions
            ->where('type', 'income')
            ->sum(fn ($transaction) => (float) $transaction->amount);

        $totalExpense = $transactions
            ->where('type', 'expense')
            ->sum(fn ($transaction) => (float) $transaction->amount);

        $net = $totalIncome - $totalExpense;

        $fileName = 'laporan-transaksi-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use (
            $transactions,
            $user,
            $totalIncome,
            $totalExpense,
            $net
        ) {
            $file = fopen('php://output', 'w');

            // BOM agar Excel Windows membaca UTF-8 dengan benar
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Delimiter ; lebih aman untuk Excel regional Indonesia
            $delimiter = ';';

            // Header laporan
            fputcsv($file, ['LAPORAN TRANSAKSI KEUANGAN'], $delimiter);
            fputcsv($file, ['Nama User', $user->name], $delimiter);
            fputcsv($file, ['Email', $user->email], $delimiter);
            fputcsv($file, ['Tanggal Export', now()->format('d-m-Y H:i:s')], $delimiter);
            fputcsv($file, [], $delimiter);

            // Ringkasan
            fputcsv($file, ['RINGKASAN'], $delimiter);
            fputcsv($file, ['Total Transaksi', $transactions->count()], $delimiter);
            fputcsv($file, ['Total Pemasukan', $totalIncome], $delimiter);
            fputcsv($file, ['Total Pengeluaran', $totalExpense], $delimiter);
            fputcsv($file, ['Saldo Bersih', $net], $delimiter);
            fputcsv($file, [], $delimiter);

            // Header tabel
            fputcsv($file, [
                'No',
                'Tanggal',
                'Akun',
                'Kategori',
                'Tipe',
                'Nominal',
                'Deskripsi',
                'Tanggal Dibuat',
                'Tanggal Diperbarui',
            ], $delimiter);

            foreach ($transactions as $index => $transaction) {
                fputcsv($file, [
                    $index + 1,
                    optional($transaction->transaction_date)->format('d-m-Y'),
                    $transaction->account->name ?? '-',
                    $transaction->category->name ?? '-',
                    $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                    (float) $transaction->amount,
                    $transaction->description ?? '-',
                    optional($transaction->created_at)->format('d-m-Y H:i:s'),
                    optional($transaction->updated_at)->format('d-m-Y H:i:s'),
                ], $delimiter);
            }

            // Footer total
            fputcsv($file, [], $delimiter);

            fputcsv($file, [
                '',
                '',
                '',
                'TOTAL PEMASUKAN',
                '',
                $totalIncome,
                '',
                '',
                '',
            ], $delimiter);

            fputcsv($file, [
                '',
                '',
                '',
                'TOTAL PENGELUARAN',
                '',
                $totalExpense,
                '',
                '',
                '',
            ], $delimiter);

            fputcsv($file, [
                '',
                '',
                '',
                'SALDO BERSIH',
                '',
                $net,
                '',
                '',
                '',
            ], $delimiter);

            fclose($file);
        }, $fileName, $headers);
    }

    /**
     * Show print-friendly transaction report.
     */
    public function printReport(Request $request)
    {
        $user = Auth::user();

        $filters = $request->only([
            'account_id',
            'category_id',
            'type',
            'date_from',
            'date_to',
            'month',
        ]);

        $transactions = $this->getFilteredTransactions($user, $filters);

        $totalIncome = $transactions
            ->where('type', 'income')
            ->sum(fn ($transaction) => (float) $transaction->amount);

        $totalExpense = $transactions
            ->where('type', 'expense')
            ->sum(fn ($transaction) => (float) $transaction->amount);

        $net = $totalIncome - $totalExpense;

        return view('transactions.print', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'net',
            'filters'
        ));
    }

    /**
     * Get filtered transactions.
     */
    protected function getFilteredTransactions($user, array $filters = [])
    {
        $query = Transaction::where('user_id', $user->id)
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc');

        if (!empty($filters['account_id'])) {
            $query->where('financial_account_id', $filters['account_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['month'])) {
            if (strlen($filters['month']) === 7) {
                $query->whereYear('transaction_date', substr($filters['month'], 0, 4))
                    ->whereMonth('transaction_date', substr($filters['month'], 5, 2));
            } else {
                $query->whereMonth('transaction_date', $filters['month']);
            }
        }

        return $query->get();
    }
}

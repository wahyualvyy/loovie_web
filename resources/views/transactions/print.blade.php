<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Transaksi</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #111827;
            background: #ffffff;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .print-button {
            padding: 10px 16px;
            background: #2563eb;
            color: #ffffff;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .back-button {
            padding: 10px 16px;
            background: #f3f4f6;
            color: #111827;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: bold;
        }

        .header p {
            margin: 6px 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            background: #f9fafb;
        }

        .summary-card .label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
        }

        .income {
            color: #059669;
            font-weight: bold;
        }

        .expense {
            color: #dc2626;
            font-weight: bold;
        }

        .net {
            color: #2563eb;
            font-weight: bold;
        }

        .filters {
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            font-size: 13px;
        }

        .filters strong {
            display: block;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 9px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        tfoot th {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-income {
            background: #dcfce7;
            color: #166534;
        }

        .badge-expense {
            background: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 24px;
            text-align: right;
            color: #6b7280;
            font-size: 12px;
        }

        @media print {
            body {
                margin: 0;
            }

            .toolbar {
                display: none;
            }

            .summary-card,
            .filters,
            table {
                page-break-inside: avoid;
            }
        }

        @media screen and (max-width: 900px) {
            .summary {
                grid-template-columns: repeat(2, 1fr);
            }

            table {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="/transactions" class="back-button">
            Kembali
        </a>

        <button class="print-button" onclick="window.print()">
            Cetak Laporan
        </button>
    </div>

    <div class="header">
        <h1>Laporan Transaksi Keuangan</h1>
        <p>Dicetak pada {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Total Transaksi</div>
            <div class="value">
                {{ $transactions->count() }}
            </div>
        </div>

        <div class="summary-card">
            <div class="label">Total Pemasukan</div>
            <div class="value income">
                Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="summary-card">
            <div class="label">Total Pengeluaran</div>
            <div class="value expense">
                Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="summary-card">
            <div class="label">Saldo Bersih</div>
            <div class="value net">
                Rp {{ number_format($net ?? 0, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="filters">
        <strong>Filter Laporan</strong>

        <div>
            Tipe:
            {{ !empty($filters['type']) ? ($filters['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran') : 'Semua' }}
        </div>

        <div>
            Tanggal Dari:
            {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d-m-Y') : '-' }}
        </div>

        <div>
            Tanggal Sampai:
            {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d-m-Y') : '-' }}
        </div>

        <div>
            Bulan:
            {{ !empty($filters['month']) ? $filters['month'] : '-' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Tanggal</th>
                <th>Akun</th>
                <th>Kategori</th>
                <th>Tipe</th>
                <th class="text-right">Nominal</th>
                <th>Deskripsi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($transactions as $index => $transaction)
                @php
                    $accountName = $transaction->account->name ?? '-';
                    $categoryName = $transaction->category->name ?? '-';
                    $isIncome = $transaction->type === 'income';
                @endphp

                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') : '-' }}
                    </td>

                    <td>
                        {{ $accountName }}
                    </td>

                    <td>
                        {{ $categoryName }}
                    </td>

                    <td>
                        <span class="badge {{ $isIncome ? 'badge-income' : 'badge-expense' }}">
                            {{ $isIncome ? 'Pemasukan' : 'Pengeluaran' }}
                        </span>
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}
                    </td>

                    <td>
                        {{ $transaction->description ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">
                        Tidak ada transaksi yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <th colspan="5" class="text-right">
                    Total Pemasukan
                </th>
                <th class="text-right income">
                    Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
                </th>
                <th></th>
            </tr>

            <tr>
                <th colspan="5" class="text-right">
                    Total Pengeluaran
                </th>
                <th class="text-right expense">
                    Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}
                </th>
                <th></th>
            </tr>

            <tr>
                <th colspan="5" class="text-right">
                    Saldo Bersih
                </th>
                <th class="text-right net">
                    Rp {{ number_format($net ?? 0, 0, ',', '.') }}
                </th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Printed from Laravel Finance App
    </div>
</body>
</html>

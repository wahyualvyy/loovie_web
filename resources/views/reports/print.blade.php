<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #111827;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            cursor: pointer;
            text-decoration: none;
            color: #111827;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        .header p {
            margin: 6px 0;
            color: #6b7280;
            font-size: 13px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            background: #f9fafb;
        }

        .card .label {
            font-size: 12px;
            color: #6b7280;
        }

        .card .value {
            margin-top: 6px;
            font-size: 18px;
            font-weight: bold;
        }

        .income {
            color: #059669;
        }

        .expense {
            color: #dc2626;
        }

        .blue {
            color: #2563eb;
        }

        h2 {
            margin-top: 28px;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .text-right {
            text-align: right;
        }

        @media print {
            .toolbar {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="/reports?month={{ $month }}" class="btn">
            Kembali
        </a>

        <button class="btn btn-primary" onclick="window.print()">
            Cetak
        </button>
    </div>

    <div class="header">
        <h1>Laporan Bulanan</h1>
        <p>{{ $report['monthLabel'] }}</p>
        <p>{{ $user->name }} - {{ $user->email }}</p>
        <p>Dicetak pada {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="card">
            <div class="label">Pemasukan</div>
            <div class="value income">
                Rp {{ number_format($report['summary']['totalIncome'], 0, ',', '.') }}
            </div>
        </div>

        <div class="card">
            <div class="label">Pengeluaran</div>
            <div class="value expense">
                Rp {{ number_format($report['summary']['totalExpense'], 0, ',', '.') }}
            </div>
        </div>

        <div class="card">
            <div class="label">Saldo Bersih</div>
            <div class="value blue">
                Rp {{ number_format($report['summary']['netBalance'], 0, ',', '.') }}
            </div>
        </div>

        <div class="card">
            <div class="label">Total Transaksi</div>
            <div class="value">
                {{ $report['summary']['transactionCount'] }}
            </div>
        </div>
    </div>

    <h2>Budget Bulanan</h2>
    <table>
        <tr>
            <th>Total Budget</th>
            <th>Terpakai</th>
            <th>Sisa</th>
            <th>Progress</th>
        </tr>
        <tr>
            <td>Rp {{ number_format($report['budget']['totalBudget'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($report['budget']['totalUsed'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($report['budget']['totalRemaining'], 0, ',', '.') }}</td>
            <td>{{ $report['budget']['totalPercentage'] }}%</td>
        </tr>
    </table>

    <h2>Pengeluaran per Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th>Jumlah Transaksi</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['expenseByCategory'] as $index => $category)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $category['name'] }}</td>
                    <td>{{ $category['count'] }}</td>
                    <td class="text-right">
                        Rp {{ number_format($category['total'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada pengeluaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Pemasukan per Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th>Jumlah Transaksi</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['incomeByCategory'] as $index => $category)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $category['name'] }}</td>
                    <td>{{ $category['count'] }}</td>
                    <td class="text-right">
                        Rp {{ number_format($category['total'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada pemasukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Transaksi Terbesar</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Akun</th>
                <th>Tipe</th>
                <th class="text-right">Nominal</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['largestTransactions'] as $index => $transaction)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $transaction['date'] }}</td>
                    <td>{{ $transaction['category_name'] }}</td>
                    <td>{{ $transaction['account_name'] }}</td>
                    <td>{{ $transaction['type'] }}</td>
                    <td class="text-right">
                        Rp {{ number_format($transaction['amount'], 0, ',', '.') }}
                    </td>
                    <td>{{ $transaction['description'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Tidak ada transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

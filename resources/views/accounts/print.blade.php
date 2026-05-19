<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Accounts Report</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 24px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 6px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .summary {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }

        .summary-card {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
            background: #f9fafb;
        }

        .summary-card .label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .summary-card .value {
            font-size: 20px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .status-active {
            color: #059669;
            font-weight: bold;
        }

        .status-inactive {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: #6b7280;
            text-align: right;
        }

        .print-button {
            margin-bottom: 20px;
            padding: 10px 16px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        Print Report
    </button>

    <div class="header">
        <h1>Financial Accounts Report</h1>
        <p>Generated at {{ now()->format('d M Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Total Initial Balance</div>
            <div class="value">
                Rp {{ number_format($totalInitialBalance, 0, ',', '.') }}
            </div>
        </div>

        <div class="summary-card">
            <div class="label">Total Current Balance</div>
            <div class="value">
                Rp {{ number_format($totalBalance, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Account Name</th>
                <th>Type</th>
                <th class="text-right">Initial Balance</th>
                <th class="text-right">Current Balance</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($accounts as $index => $account)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $account->name }}</td>
                    <td>{{ $account->type ?? '-' }}</td>
                    <td class="text-right">
                        Rp {{ number_format($account->initial_balance ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($account->current_balance ?? 0, 0, ',', '.') }}
                    </td>
                    <td>
                        @if ($account->is_active)
                            <span class="status-active">Active</span>
                        @else
                            <span class="status-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $account->description ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">
                        No financial accounts found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Printed from Laravel Finance App
    </div>
</body>
</html>

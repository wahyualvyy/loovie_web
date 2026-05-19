<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Catatan</title>

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
            grid-template-columns: repeat(2, 1fr);
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

        .note-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .note-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .note-title {
            margin: 0;
            font-size: 17px;
            font-weight: bold;
        }

        .note-date {
            color: #6b7280;
            font-size: 12px;
            white-space: nowrap;
        }

        .label-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 11px;
            font-weight: bold;
        }

        .note-content {
            font-size: 14px;
            line-height: 1.7;
            white-space: pre-line;
        }

        .empty {
            padding: 28px;
            text-align: center;
            color: #6b7280;
            border: 1px dashed #d1d5db;
            border-radius: 10px;
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
            .note-card {
                page-break-inside: avoid;
            }
        }

        @media screen and (max-width: 700px) {
            .summary {
                grid-template-columns: 1fr;
            }

            .note-header {
                flex-direction: column;
                gap: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="/notes" class="back-button">
            Kembali
        </a>

        <button class="print-button" onclick="window.print()">
            Cetak Laporan
        </button>
    </div>

    <div class="header">
        <h1>Laporan Catatan</h1>
        <p>Dicetak pada {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Nama User</div>
            <div class="value">
                {{ $user->name ?? '-' }}
            </div>
        </div>

        <div class="summary-card">
            <div class="label">Total Catatan</div>
            <div class="value">
                {{ $notes->count() }}
            </div>
        </div>
    </div>

    <div class="filters">
        <strong>Filter Laporan</strong>

        <div>
            Label:
            {{ !empty($filters['label']) ? $filters['label'] : 'Semua' }}
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

    @forelse ($notes as $note)
        <div class="note-card">
            <div class="note-header">
                <div>
                    <h2 class="note-title">
                        {{ $note->title ?? 'Tanpa Judul' }}
                    </h2>

                    @if (!empty($note->label))
                        <span class="label-badge">
                            {{ $note->label }}
                        </span>
                    @endif
                </div>

                <div class="note-date">
                    {{ $note->note_date ? \Carbon\Carbon::parse($note->note_date)->format('d-m-Y') : '-' }}
                </div>
            </div>

            <div class="note-content">
                {{ $note->content ?? '-' }}
            </div>
        </div>
    @empty
        <div class="empty">
            Tidak ada catatan yang ditemukan.
        </div>
    @endforelse

    <div class="footer">
        Printed from Laravel Finance App
    </div>
</body>
</html>

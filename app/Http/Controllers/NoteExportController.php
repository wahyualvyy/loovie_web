<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NoteExportController extends Controller
{
    /**
     * Export notes to CSV.
     */
    public function exportCSV(Request $request): StreamedResponse
    {
        $user = Auth::user();

        $filters = $request->only([
            'label',
            'date_from',
            'date_to',
            'month',
        ]);

        $notes = $this->getFilteredNotes($user, $filters);

        $fileName = 'laporan-catatan-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($notes, $user, $filters) {
            $file = fopen('php://output', 'w');

            // BOM agar Excel Windows membaca UTF-8 dengan benar
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Delimiter ; lebih aman untuk Excel regional Indonesia
            $delimiter = ';';

            // Header laporan
            fputcsv($file, ['LAPORAN CATATAN'], $delimiter);
            fputcsv($file, ['Nama User', $user->name], $delimiter);
            fputcsv($file, ['Email', $user->email], $delimiter);
            fputcsv($file, ['Tanggal Export', now()->format('d-m-Y H:i:s')], $delimiter);
            fputcsv($file, [], $delimiter);

            // Ringkasan
            fputcsv($file, ['RINGKASAN'], $delimiter);
            fputcsv($file, ['Total Catatan', $notes->count()], $delimiter);
            fputcsv($file, ['Filter Label', $filters['label'] ?? 'Semua'], $delimiter);
            fputcsv($file, ['Filter Tanggal Dari', $filters['date_from'] ?? '-'], $delimiter);
            fputcsv($file, ['Filter Tanggal Sampai', $filters['date_to'] ?? '-'], $delimiter);
            fputcsv($file, ['Filter Bulan', $filters['month'] ?? '-'], $delimiter);
            fputcsv($file, [], $delimiter);

            // Header tabel
            fputcsv($file, [
                'No',
                'Tanggal Catatan',
                'Judul',
                'Isi Catatan',
                'Label',
                'Tanggal Dibuat',
                'Tanggal Diperbarui',
            ], $delimiter);

            foreach ($notes as $index => $note) {
                fputcsv($file, [
                    $index + 1,
                    optional($note->note_date)->format('d-m-Y'),
                    $note->title ?? '-',
                    $note->content ?? '-',
                    $note->label ?? '-',
                    optional($note->created_at)->format('d-m-Y H:i:s'),
                    optional($note->updated_at)->format('d-m-Y H:i:s'),
                ], $delimiter);
            }

            fclose($file);
        }, $fileName, $headers);
    }

    /**
     * Show print-friendly notes report.
     */
    public function printReport(Request $request)
    {
        $user = Auth::user();

        $filters = $request->only([
            'label',
            'date_from',
            'date_to',
            'month',
        ]);

        $notes = $this->getFilteredNotes($user, $filters);

        return view('notes.print', compact(
            'notes',
            'filters',
            'user'
        ));
    }

    /**
     * Get filtered notes.
     */
    protected function getFilteredNotes($user, array $filters = [])
    {
        $query = Note::where('user_id', $user->id)
            ->orderBy('note_date', 'desc');

        if (!empty($filters['label'])) {
            $query->where('label', $filters['label']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('note_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('note_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['month'])) {
            if (strlen($filters['month']) === 7) {
                $query->whereYear('note_date', substr($filters['month'], 0, 4))
                    ->whereMonth('note_date', substr($filters['month'], 5, 2));
            } else {
                $query->whereMonth('note_date', $filters['month']);
            }
        }

        return $query->get();
    }
}

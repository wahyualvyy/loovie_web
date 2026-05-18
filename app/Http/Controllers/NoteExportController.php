<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NoteExportController extends Controller
{
    /**
     * Export notes to CSV
     */
    public function exportCSV(Request $request): StreamedResponse
    {
        $user = Auth::user();
        
        $filters = $request->only(['label', 'date_from', 'date_to', 'month']);
        $notes = $this->getFilteredNotes($user, $filters);

        $callback = function () use ($notes) {
            $file = fopen('php://output', 'w');
            
            // Header row with BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'Date',
                'Title',
                'Content',
                'Label',
            ]);

            // Data rows
            foreach ($notes as $note) {
                fputcsv($file, [
                    $note->note_date->format('Y-m-d'),
                    $note->title,
                    $note->content,
                    $note->label ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'notes_' . now()->format('Y-m-d_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="notes.csv"',
        ]);
    }

    /**
     * Show print-friendly notes report
     */
    public function printReport(Request $request)
    {
        $user = Auth::user();
        
        $filters = $request->only(['label', 'date_from', 'date_to', 'month']);
        $notes = $this->getFilteredNotes($user, $filters);

        return view('notes.print', compact('notes'));
    }

    /**
     * Get filtered notes
     */
    protected function getFilteredNotes($user, array $filters = [])
    {
        $query = Note::where('user_id', $user->id)
            ->orderBy('note_date', 'desc');

        // Apply filters
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
            $query->whereMonth('note_date', $filters['month']);
        }

        return $query->get();
    }
}

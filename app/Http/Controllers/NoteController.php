<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $notes = $user->notes()
            ->when(request('search'), function ($query) {
                $search = request('search');
                return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->when(request('label'), function ($query) {
                return $query->where('label', request('label'));
            })
            ->when(request('date_from'), function ($query) {
                return $query->whereDate('note_date', '>=', request('date_from'));
            })
            ->when(request('date_to'), function ($query) {
                return $query->whereDate('note_date', '<=', request('date_to'));
            })
            ->when(request('month'), function ($query) {
                $month = request('month');
                [$year, $monthNum] = explode('-', $month);
                return $query->whereYear('note_date', $year)
                    ->whereMonth('note_date', $monthNum);
            })
            ->orderBy('note_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $labels = $user->notes()
            ->select('label')
            ->distinct()
            ->whereNotNull('label')
            ->pluck('label');

        return Inertia::render('NotesIndex', [
            'notes' => $notes,
            'labels' => $labels,
            'filters' => request()->only(['search', 'label', 'date_from', 'date_to', 'month']),
        ]);
    }

    public function create()
    {
        return Inertia::render('CreateNote');
    }

    public function store(StoreNoteRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        Note::create($validated);

        return redirect()->route('notes.index')
            ->with('success', 'Note created successfully!');
    }

    public function edit(Note $note)
    {
        $this->authorizeUser($note);

        return Inertia::render('EditNote', [
            'note' => $note,
        ]);
    }

    public function update(UpdateNoteRequest $request, Note $note)
    {
        $this->authorizeUser($note);

        $validated = $request->validated();
        $note->update($validated);

        return redirect()->route('notes.index')
            ->with('success', 'Note updated successfully!');
    }

    public function destroy(Note $note)
    {
        $this->authorizeUser($note);

        $note->delete();

        return redirect()->route('notes.index')
            ->with('success', 'Note deleted successfully!');
    }

    private function authorizeUser(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}

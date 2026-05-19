<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notes = $user->notes()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('label'), function ($query) use ($request) {
                $query->where('label', $request->label);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('note_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('note_date', '<=', $request->date_to);
            })
            ->when($request->filled('month'), function ($query) use ($request) {
                $month = $request->month;

                if (strlen($month) === 7) {
                    [$year, $monthNum] = explode('-', $month);

                    $query->whereYear('note_date', $year)
                        ->whereMonth('note_date', $monthNum);
                }
            })
            ->orderBy('note_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $labels = $user->notes()
            ->select('label')
            ->distinct()
            ->whereNotNull('label')
            ->where('label', '!=', '')
            ->orderBy('label')
            ->pluck('label');

        return Inertia::render('NotesIndex', [
            'notes' => $notes,
            'labels' => $labels,
            'filters' => $request->only([
                'search',
                'label',
                'date_from',
                'date_to',
                'month',
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('CreateNote');
    }

    public function store(StoreNoteRequest $request)
    {
        $validated = $request->validated();

        $request->user()->notes()->create($validated);

        return redirect()
            ->route('notes.index')
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

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note updated successfully!');
    }

    public function destroy(Note $note)
    {
        $this->authorizeUser($note);

        $note->delete();

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note deleted successfully!');
    }

    private function authorizeUser(Note $note): void
    {
        if ((int) $note->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}

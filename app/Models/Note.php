<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'title', 'content', 'note_date', 'label'])]
class Note extends Model
{
    use HasFactory;

    protected $table = 'notes';

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'note_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

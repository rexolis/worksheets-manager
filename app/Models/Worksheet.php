<?php

namespace App\Models;

use Database\Factories\WorksheetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $number
 * @property int $subject_id
 * @property string|null $subtopic
 * @property string $title
 * @property int $worksheet_class_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'number',
    'subject_id',
    'subtopic',
    'title',
    'worksheet_class_id',
    'created_by',
    'updated_by',
])]
class Worksheet extends Model
{
    /** @use HasFactory<WorksheetFactory> */
    use HasFactory;

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function worksheetClass(): BelongsTo
    {
        return $this->belongsTo(WorksheetClass::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

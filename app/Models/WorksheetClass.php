<?php

namespace App\Models;

use Database\Factories\WorksheetClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name'])]
class WorksheetClass extends Model
{
    /** @use HasFactory<WorksheetClassFactory> */
    use HasFactory;

    public function worksheets(): HasMany
    {
        return $this->hasMany(Worksheet::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'worksheets')
            ->distinct();
    }
}

<?php

namespace App\Models;

use App\Enums\SectionStatusId;
use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $section_type
 * @property int $worksheet_class_id
 * @property string $class_code
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property SectionStatusId $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'section_type',
    'worksheet_class_id',
    'class_code',
    'date_start',
    'date_end',
    'status',
])]
class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => SectionStatusId::Active->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_start' => 'date',
            'date_end' => 'date',
            'status' => SectionStatusId::class,
        ];
    }

    /**
     * @param  Builder<Section>  $query
     * @return Builder<Section>
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('status', '!=', SectionStatusId::Deleted);
    }

    public function isArchived(): bool
    {
        return $this->status === SectionStatusId::Archived;
    }

    public function isDeleted(): bool
    {
        return $this->status === SectionStatusId::Deleted;
    }

    public function worksheetClass(): BelongsTo
    {
        return $this->belongsTo(WorksheetClass::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_masters')
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use App\Enums\StudentClassStatus;
use Database\Factories\StudentClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $section_id
 * @property StudentClassStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'section_id',
    'status',
])]
class StudentClass extends Model
{
    /** @use HasFactory<StudentClassFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'student_class';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => StudentClassStatus::Pending->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StudentClassStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}

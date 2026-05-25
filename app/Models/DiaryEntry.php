<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DiaryEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Fillable([
    'user_id',
    'title',
    'content',
    'mood',
    'weather',
    'location',
    'entry_date',
    'tags',
    'is_favorite',
    'is_draft',
    'attachments',
])]
class DiaryEntry extends Model
{
    /** @use HasFactory<DiaryEntryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'tags' => 'array',
            'attachments' => 'array',
            'is_favorite' => 'boolean',
            'is_draft' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $field ??= $this->getRouteKeyName();
        $userId = Auth::id();

        if ($userId === null) {
            return null;
        }

        return $this->where($field, $value)
            ->where('user_id', $userId)
            ->first();
    }
}

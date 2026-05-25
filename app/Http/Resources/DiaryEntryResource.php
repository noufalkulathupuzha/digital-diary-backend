<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DiaryEntry
 */
class DiaryEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'mood' => $this->mood,
            'weather' => $this->weather,
            'location' => $this->location,
            'entry_date' => $this->entry_date?->toDateString(),
            'tags' => $this->tags ?? [],
            'is_favorite' => $this->is_favorite,
            'is_draft' => $this->is_draft,
            'attachments' => $this->attachments ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

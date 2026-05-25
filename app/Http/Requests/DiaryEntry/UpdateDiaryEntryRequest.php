<?php

declare(strict_types=1);

namespace App\Http\Requests\DiaryEntry;

use App\Enums\Mood;
use App\Enums\Weather;
use App\Models\DiaryEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiaryEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var DiaryEntry|null $diaryEntry */
        $diaryEntry = $this->route('diary_entry');

        return $diaryEntry !== null && ($this->user()?->can('update', $diaryEntry) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'nullable', 'string', 'max:50000'],
            'mood' => ['sometimes', 'nullable', 'string', Rule::in(Mood::values())],
            'weather' => ['sometimes', 'nullable', 'string', Rule::in(Weather::values())],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'entry_date' => ['sometimes', 'required', 'date'],
            'tags' => ['sometimes', 'nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
            'is_favorite' => ['sometimes', 'boolean'],
            'is_draft' => ['sometimes', 'boolean'],
            'attachments' => ['sometimes', 'nullable', 'array', 'max:20'],
            'attachments.*.type' => ['required_with:attachments', 'string', Rule::in(['image', 'audio'])],
            'attachments.*.name' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.url' => ['nullable', 'string', 'max:2048'],
        ];
    }
}

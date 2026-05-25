<?php

declare(strict_types=1);

namespace App\Http\Requests\DiaryEntry;

use App\Enums\Mood;
use App\Enums\Weather;
use App\Models\DiaryEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiaryEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DiaryEntry::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isDraft = $this->boolean('is_draft');

        return [
            'title' => [$isDraft ? 'nullable' : 'required', 'string', 'max:255'],
            'content' => [$isDraft ? 'nullable' : 'required', 'string', 'max:50000'],
            'mood' => ['nullable', 'string', Rule::in(Mood::values())],
            'weather' => ['nullable', 'string', Rule::in(Weather::values())],
            'location' => ['nullable', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
            'is_favorite' => ['sometimes', 'boolean'],
            'is_draft' => ['sometimes', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:20'],
            'attachments.*.type' => ['required_with:attachments', 'string', Rule::in(['image', 'audio'])],
            'attachments.*.name' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.url' => ['nullable', 'string', 'max:2048'],
        ];
    }
}

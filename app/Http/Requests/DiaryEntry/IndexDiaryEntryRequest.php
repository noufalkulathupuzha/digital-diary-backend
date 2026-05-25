<?php

declare(strict_types=1);

namespace App\Http\Requests\DiaryEntry;

use App\Enums\Mood;
use App\Models\DiaryEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDiaryEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', DiaryEntry::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('calendar')) {
            $merge['calendar'] = $this->boolean('calendar');
        }

        if ($this->has('drafts')) {
            $merge['drafts'] = $this->boolean('drafts');
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'mood' => ['sometimes', 'string', Rule::in(Mood::values())],
            'search' => ['sometimes', 'string', 'max:255'],
            'from' => [
                'sometimes',
                'date',
                Rule::requiredIf(fn (): bool => $this->boolean('calendar')),
            ],
            'to' => [
                'sometimes',
                'date',
                'after_or_equal:from',
                Rule::requiredIf(fn (): bool => $this->boolean('calendar')),
            ],
            'drafts' => ['sometimes', 'boolean'],
            'calendar' => ['sometimes', 'boolean'],
        ];
    }
}

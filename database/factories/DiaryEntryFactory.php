<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Mood;
use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiaryEntry>
 */
class DiaryEntryFactory extends Factory
{
    protected $model = DiaryEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'mood' => fake()->randomElement(Mood::values()),
            'entry_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'tags' => fake()->optional()->randomElements(['personal', 'work', 'health', 'travel', 'goals'], 2),
        ];
    }
}

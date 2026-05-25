<?php

declare(strict_types=1);

namespace App\Enums;

enum Mood: string
{
    case Calm = 'calm';
    case Happy = 'happy';
    case Inspired = 'inspired';
    case Reflective = 'reflective';
    case Grateful = 'grateful';
    case Anxious = 'anxious';
    case Tired = 'tired';
    case Energized = 'energized';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

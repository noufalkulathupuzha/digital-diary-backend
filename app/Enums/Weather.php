<?php

declare(strict_types=1);

namespace App\Enums;

enum Weather: string
{
    case Sunny = 'sunny';
    case Cloudy = 'cloudy';
    case Rainy = 'rainy';
    case Stormy = 'stormy';
    case Snowy = 'snowy';
    case Windy = 'windy';
    case Foggy = 'foggy';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

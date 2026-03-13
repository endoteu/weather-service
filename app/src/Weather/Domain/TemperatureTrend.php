<?php

declare(strict_types=1);

namespace App\Weather\Domain;

enum TemperatureTrend: string
{
    case Positive = 'positive';
    case Negative = 'negative';
    case Static = 'static';

    public function suffix(): string
    {
        return match ($this) {
            self::Positive => '🥵',
            self::Negative => '🥶',
            self::Static => '-',
        };
    }
}

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

    public static function fromTemperatures(float $currentTemperature, float $averageTemperature): self
    {
        $difference = round($currentTemperature - $averageTemperature, 2);

        if ($difference > 0.0) {
            return self::Positive;
        }

        if ($difference < 0.0) {
            return self::Negative;
        }

        return self::Static;
    }
}

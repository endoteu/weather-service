<?php

declare(strict_types=1);

namespace App\Weather\Domain;

final class TemperatureTrendResolver
{
    public function resolve(float $currentTemperature, float $averageTemperature): TemperatureTrend
    {
        $difference = round($currentTemperature - $averageTemperature, 2);

        if ($difference > 0.0) {
            return TemperatureTrend::Positive;
        }

        if ($difference < 0.0) {
            return TemperatureTrend::Negative;
        }

        return TemperatureTrend::Static;
    }
}

<?php

declare(strict_types=1);

namespace App\Weather\Application\Query;

use App\Weather\Application\WeatherReport;
use App\Weather\Domain\TemperatureTrend;
use App\Weather\Domain\WeatherSnapshot;

final class WeatherReportFactory
{
    public function fromSnapshot(WeatherSnapshot $snapshot): WeatherReport
    {
        return new WeatherReport(
            city: ucfirst($snapshot->city()),
            temperature: (int) round($snapshot->temperature()),
            trend: TemperatureTrend::fromTemperatures(
                $snapshot->temperature(),
                $snapshot->averageLast10Days(),
            ),
            fetchedAt: $snapshot->fetchedAt(),
        );
    }
}

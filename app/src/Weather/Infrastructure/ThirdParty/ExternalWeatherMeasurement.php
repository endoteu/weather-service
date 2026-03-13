<?php

declare(strict_types=1);

namespace App\Weather\Infrastructure\ThirdParty;

final readonly class ExternalWeatherMeasurement
{
    public function __construct(
        public float $currentTemperature,
        public float $averageLast10Days,
        public \DateTimeImmutable $fetchedAt,
    ) {
    }
}

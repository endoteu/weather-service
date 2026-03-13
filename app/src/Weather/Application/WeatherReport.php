<?php

declare(strict_types=1);

namespace App\Weather\Application;

use App\Weather\Domain\TemperatureTrend;

final readonly class WeatherReport
{
    public function __construct(
        public string $city,
        public int $temperature,
        public TemperatureTrend $trend,
        public \DateTimeImmutable $fetchedAt
    ) {
    }

    public function formattedTemperature(): string
    {
        return sprintf('%d %s', $this->temperature, $this->trend->suffix());
    }

    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'temperature' => $this->formattedTemperature(),
            'value' => $this->temperature,
            'trend' => $this->trend->value,
            'trend_suffix' => $this->trend->suffix(),
            'fetched_at' => $this->fetchedAt->format(DATE_ATOM),
        ];
    }
}

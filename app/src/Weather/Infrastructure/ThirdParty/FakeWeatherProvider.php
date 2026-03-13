<?php

declare(strict_types=1);

namespace App\Weather\Infrastructure\ThirdParty;

use App\Weather\Application\Exception\CityNotFound;

final class FakeWeatherProvider implements WeatherProviderInterface
{
    private array $fixtures = [];
    private array $calls = [];

    public function seed(string $city, float $currentTemperature, float $averageLast10Days): void
    {
        $this->fixtures[mb_strtolower(trim($city))] = [
            'current' => $currentTemperature,
            'average' => $averageLast10Days,
        ];
    }

    public function reset(): void
    {
        $this->fixtures = [];
        $this->calls = [];
    }

    public function callsFor(string $city): int
    {
        return $this->calls[mb_strtolower(trim($city))] ?? 0;
    }

    public function fetch(string $city): ExternalWeatherMeasurement
    {
        $city = mb_strtolower(trim($city));
        $this->calls[$city] = ($this->calls[$city] ?? 0) + 1;

        if (!isset($this->fixtures[$city])) {
            throw new CityNotFound(sprintf('City "%s" was not found.', $city));
        }

        return new ExternalWeatherMeasurement(
            currentTemperature: $this->fixtures[$city]['current'],
            averageLast10Days: $this->fixtures[$city]['average'],
            fetchedAt: new \DateTimeImmutable(),
        );
    }
}

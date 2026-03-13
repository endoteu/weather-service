<?php

declare(strict_types=1);

namespace App\Weather\Infrastructure\ThirdParty;

use App\Weather\Application\Exception\CityNotFound;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenMeteoWeatherProvider implements WeatherProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function fetch(string $city): ExternalWeatherMeasurement
    {
        $coordinates = $this->resolveCoordinates($city);

        $currentTemperature = $this->fetchCurrentTemperature($coordinates['latitude'], $coordinates['longitude']);
        $averageLast10Days = $this->fetchAverageLast10Days($coordinates['latitude'], $coordinates['longitude']);

        return new ExternalWeatherMeasurement(
            currentTemperature: $currentTemperature,
            averageLast10Days: $averageLast10Days,
            fetchedAt: new \DateTimeImmutable(),
        );
    }

    private function resolveCoordinates(string $city): array
    {
        $response = $this->httpClient->request('GET', 'https://geocoding-api.open-meteo.com/v1/search', [
            'query' => [
                'name' => $city,
                'count' => 1,
                'language' => 'en',
                'format' => 'json',
            ],
        ]);

        $data = $response->toArray();

        if (empty($data['results'][0])) {
            throw new CityNotFound(sprintf('City "%s" was not found.', $city));
        }

        return [
            'latitude' => (float) $data['results'][0]['latitude'],
            'longitude' => (float) $data['results'][0]['longitude'],
        ];
    }

    private function fetchCurrentTemperature(float $latitude, float $longitude): float
    {
        $response = $this->httpClient->request('GET', 'https://api.open-meteo.com/v1/forecast', [
            'query' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m',
                'timezone' => 'Europe/Sofia',
            ],
        ]);

        $data = $response->toArray();

        return (float) ($data['current']['temperature_2m'] ?? 0.0);
    }

    private function fetchAverageLast10Days(float $latitude, float $longitude): float
    {
        $endDate = (new \DateTimeImmutable('yesterday'))->format('Y-m-d');
        $startDate = (new \DateTimeImmutable('-10 days'))->format('Y-m-d');

        $response = $this->httpClient->request('GET', 'https://archive-api.open-meteo.com/v1/archive', [
            'query' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'daily' => 'temperature_2m_mean',
                'timezone' => 'Europe/Sofia',
            ],
        ]);

        $data = $response->toArray();
        $values = $data['daily']['temperature_2m_mean'] ?? [];

        if ($values === []) {
            return 0.0;
        }

        $sum = array_sum(array_map('floatval', $values));

        return round($sum / count($values), 2);
    }
}

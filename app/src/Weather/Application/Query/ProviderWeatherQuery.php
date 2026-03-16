<?php

declare(strict_types=1);

namespace App\Weather\Application\Query;

use App\Weather\Application\Contract\WeatherQueryInterface;
use App\Weather\Application\WeatherReport;
use App\Weather\Domain\WeatherSnapshot;
use App\Weather\Domain\WeatherSnapshotRepositoryInterface;
use App\Weather\Infrastructure\ThirdParty\WeatherProviderInterface;

final class ProviderWeatherQuery implements WeatherQueryInterface
{
    public function __construct(
        private readonly WeatherProviderInterface $provider,
        private readonly WeatherSnapshotRepositoryInterface $repository,
        private readonly WeatherReportFactory $reportFactory,
        private readonly int $weatherCacheTtl,
    ) {
    }

    public function getByCity(string $city): WeatherReport
    {
        $normalizedCity = mb_strtolower(trim($city));

        $snapshot = $this->repository->findFreshByCity(
            $normalizedCity,
            new \DateTimeImmutable(sprintf('-%d seconds', $this->weatherCacheTtl)),
        );

        if ($snapshot !== null) {
            return $this->reportFactory->fromSnapshot($snapshot);
        }

        $measurement = $this->provider->fetch($normalizedCity);

        $snapshot = WeatherSnapshot::capture(
            city: $normalizedCity,
            temperature: $measurement->currentTemperature,
            averageLast10Days: $measurement->averageLast10Days,
            fetchedAt: $measurement->fetchedAt,
        );

        $this->repository->save($snapshot);

        return $this->reportFactory->fromSnapshot($snapshot);
    }
}

<?php

declare(strict_types=1);

namespace App\Weather\Application\Query;

use App\Weather\Application\Contract\WeatherQueryInterface;
use App\Weather\Application\WeatherReport;
use App\Weather\Domain\WeatherSnapshotRepositoryInterface;

final class SnapshotWeatherQuery implements WeatherQueryInterface
{
    public function __construct(
        private readonly WeatherSnapshotRepositoryInterface $repository,
        private readonly WeatherReportFactory $reportFactory,
        private readonly WeatherQueryInterface $inner,
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

        return $this->inner->getByCity($normalizedCity);
    }
}

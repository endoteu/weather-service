<?php

declare(strict_types=1);

namespace App\Weather\Domain;

interface WeatherSnapshotRepositoryInterface
{
    public function findFreshByCity(string $city, \DateTimeImmutable $freshAfter): ?WeatherSnapshot;

    public function save(WeatherSnapshot $snapshot): void;

    public function deleteAll(): void;
}

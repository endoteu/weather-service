<?php

declare(strict_types=1);

namespace App\Weather\Application;

use App\Weather\Domain\TemperatureTrendResolver;
use App\Weather\Domain\WeatherSnapshot;
use App\Weather\Domain\WeatherSnapshotRepositoryInterface;
use App\Weather\Infrastructure\ThirdParty\WeatherProviderInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class GetWeatherByCity
{
    public function __construct(
        private readonly WeatherSnapshotRepositoryInterface $repository,
        private readonly WeatherProviderInterface $provider,
        private readonly TemperatureTrendResolver $trendResolver,
        private readonly CacheInterface $cache,
        private readonly LockFactory $lockFactory,
        private readonly int $weatherCacheTtl,
    ) {
    }

    public function handle(string $city): WeatherReport
    {
        $normalizedCity = mb_strtolower(trim($city));
        $cacheKey = 'weather_report_' . md5($normalizedCity);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($normalizedCity) {
            $item->expiresAfter($this->weatherCacheTtl);

            $freshAfter = new \DateTimeImmutable(sprintf('-%d seconds', $this->weatherCacheTtl));
            $snapshot = $this->repository->findFreshByCity($normalizedCity, $freshAfter);

            $lock = null;

            try {
                if (!$snapshot) {
                    $lock = $this->lockFactory->createLock('weather_fetch_' . md5($normalizedCity), 10.0);
                    $lock->acquire(true);

                    $snapshot = $this->repository->findFreshByCity($normalizedCity, $freshAfter);

                    if (!$snapshot) {
                        $measurement = $this->provider->fetch($normalizedCity);

                        $snapshot = WeatherSnapshot::capture(
                            city: $normalizedCity,
                            temperature: $measurement->currentTemperature,
                            averageLast10Days: $measurement->averageLast10Days,
                            fetchedAt: $measurement->fetchedAt,
                        );

                        $this->repository->save($snapshot);
                    }
                }

                $trend = $this->trendResolver->resolve(
                    $snapshot->temperature(),
                    $snapshot->averageLast10Days(),
                );

                return new WeatherReport(
                    city: ucfirst($snapshot->city()),
                    temperature: (int) round($snapshot->temperature()),
                    trend: $trend,
                    fetchedAt: $snapshot->fetchedAt(),
                );
            } finally {
                $lock?->release();
            }
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Weather\Application\Query;

use App\Weather\Application\Contract\WeatherQueryInterface;
use App\Weather\Application\WeatherReport;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedWeatherQuery implements WeatherQueryInterface
{
    public function __construct(
        private readonly WeatherQueryInterface $inner,
        private readonly CacheInterface $cache,
        private readonly int $weatherCacheTtl,
    ) {
    }

    public function getByCity(string $city): WeatherReport
    {
        $normalizedCity = mb_strtolower(trim($city));
        $cacheKey = 'weather_report_' . md5($normalizedCity);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($normalizedCity) {
                $item->expiresAfter($this->weatherCacheTtl);

                return $this->inner->getByCity($normalizedCity);
            });
        } catch (\Throwable) {
            return $this->inner->getByCity($normalizedCity);
        }
    }
}

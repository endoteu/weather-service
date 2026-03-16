<?php

declare(strict_types=1);

namespace App\Weather\Application\Query;

use App\Weather\Application\Contract\WeatherQueryInterface;
use App\Weather\Application\WeatherReport;
use Symfony\Component\Lock\LockFactory;

final class LockedWeatherQuery implements WeatherQueryInterface
{
    public function __construct(
        private readonly WeatherQueryInterface $inner,
        private readonly LockFactory $lockFactory,
    ) {
    }

    public function getByCity(string $city): WeatherReport
    {
        $lock = $this->lockFactory->createLock(
            'weather_fetch_' . md5(mb_strtolower(trim($city))),
            10.0
        );

        try {
            $lock->acquire(true);

            return $this->inner->getByCity($city);
        } finally {
            $lock->release();
        }
    }
}

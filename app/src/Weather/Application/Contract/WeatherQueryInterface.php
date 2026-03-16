<?php

declare(strict_types=1);

namespace App\Weather\Application\Contract;

use App\Weather\Application\WeatherReport;

interface WeatherQueryInterface
{
    public function getByCity(string $city): WeatherReport;
}

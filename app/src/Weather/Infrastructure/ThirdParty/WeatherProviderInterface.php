<?php

declare(strict_types=1);

namespace App\Weather\Infrastructure\ThirdParty;

interface WeatherProviderInterface
{
    public function fetch(string $city): ExternalWeatherMeasurement;
}

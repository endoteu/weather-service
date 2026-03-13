<?php

declare(strict_types=1);

namespace App\Features\Bootstrap;

use App\Weather\Application\GetWeatherByCity;
use App\Weather\Application\WeatherReport;
use App\Weather\Domain\WeatherSnapshotRepositoryInterface;
use App\Weather\Infrastructure\ThirdParty\FakeWeatherProvider;
use Behat\Behat\Context\Context;
use Symfony\Contracts\Cache\CacheInterface;

final class FeatureContext implements Context
{
    private ?WeatherReport $report = null;

    public function __construct(
        private readonly GetWeatherByCity $getWeatherByCity,
        private readonly FakeWeatherProvider $fakeWeatherProvider,
        private readonly WeatherSnapshotRepositoryInterface $repository,
        private readonly CacheInterface $cache,
    ) {
    }

    /** @BeforeScenario */
    public function resetState(): void
    {
        $this->fakeWeatherProvider->reset();
        $this->repository->deleteAll();
        $this->cache->clear();
        $this->report = null;
    }

    /**
     * @Given the provider has weather data for :city with current :current and average :average
     */
    public function theProviderHasWeatherDataForWithCurrentAndAverage(string $city, float $current, float $average): void
    {
        $this->fakeWeatherProvider->seed($city, $current, $average);
    }

    /**
     * @When I request the weather for :city
     */
    public function iRequestTheWeatherFor(string $city): void
    {
        $this->report = $this->getWeatherByCity->handle($city);
    }

    /**
     * @When I request the weather for :city twice
     */
    public function iRequestTheWeatherForTwice(string $city): void
    {
        $this->report = $this->getWeatherByCity->handle($city);
        $this->report = $this->getWeatherByCity->handle($city);
    }

    /**
     * @Then the returned temperature should be :temperature
     */
    public function theReturnedTemperatureShouldBe(int $temperature): void
    {
        if (!$this->report || $this->report->temperature !== $temperature) {
            throw new \RuntimeException(sprintf('Expected temperature %d.', $temperature));
        }
    }

    /**
     * @Then the returned suffix should be :suffix
     */
    public function theReturnedSuffixShouldBe(string $suffix): void
    {
        if (!$this->report || $this->report->trend->suffix() !== $suffix) {
            throw new \RuntimeException(sprintf('Expected suffix %s.', $suffix));
        }
    }

    /**
     * @Then the provider should be called :count time for :city
     * @Then the provider should be called :count times for :city
     */
    public function theProviderShouldBeCalledTimeFor(int $count, string $city): void
    {
        $actual = $this->fakeWeatherProvider->callsFor($city);

        if ($actual !== $count) {
            throw new \RuntimeException(sprintf('Expected %d calls, got %d.', $count, $actual));
        }
    }
}

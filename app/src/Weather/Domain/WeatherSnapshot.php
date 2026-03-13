<?php

declare(strict_types=1);

namespace App\Weather\Domain;

use App\Weather\Infrastructure\Persistence\DoctrineWeatherSnapshotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineWeatherSnapshotRepository::class)]
#[ORM\Table(name: 'weather_snapshot')]
#[ORM\Index(columns: ['city', 'fetched_at'], name: 'idx_city_fetched_at')]
class WeatherSnapshot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $city;

    #[ORM\Column(type: 'float')]
    private float $temperature;

    #[ORM\Column(name: 'average_last_10_days', type: 'float')]
    private float $averageLast10Days;

    #[ORM\Column(name: 'fetched_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $fetchedAt;

    public function __construct(
        string $city,
        float $temperature,
        float $averageLast10Days,
        \DateTimeImmutable $fetchedAt
    ) {
        $this->city = mb_strtolower(trim($city));
        $this->temperature = $temperature;
        $this->averageLast10Days = $averageLast10Days;
        $this->fetchedAt = $fetchedAt;
    }

    public static function capture(
        string $city,
        float $temperature,
        float $averageLast10Days,
        \DateTimeImmutable $fetchedAt
    ): self {
        return new self($city, $temperature, $averageLast10Days, $fetchedAt);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function temperature(): float
    {
        return $this->temperature;
    }

    public function averageLast10Days(): float
    {
        return $this->averageLast10Days;
    }

    public function fetchedAt(): \DateTimeImmutable
    {
        return $this->fetchedAt;
    }
}

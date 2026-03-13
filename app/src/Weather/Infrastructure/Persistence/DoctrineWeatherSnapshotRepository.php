<?php

declare(strict_types=1);

namespace App\Weather\Infrastructure\Persistence;

use App\Weather\Domain\WeatherSnapshot;
use App\Weather\Domain\WeatherSnapshotRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineWeatherSnapshotRepository extends ServiceEntityRepository implements WeatherSnapshotRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherSnapshot::class);
    }

    public function findFreshByCity(string $city, \DateTimeImmutable $freshAfter): ?WeatherSnapshot
    {
        return $this->createQueryBuilder('ws')
            ->andWhere('ws.city = :city')
            ->andWhere('ws.fetchedAt >= :freshAfter')
            ->setParameter('city', mb_strtolower(trim($city)))
            ->setParameter('freshAfter', $freshAfter)
            ->orderBy('ws.fetchedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(WeatherSnapshot $snapshot): void
    {
        $em = $this->getEntityManager();
        $em->persist($snapshot);
        $em->flush();
    }

    public function deleteAll(): void
    {
        $this->getEntityManager()->createQuery('DELETE FROM App\Weather\Domain\WeatherSnapshot ws')->execute();
    }
}

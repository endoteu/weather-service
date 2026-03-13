<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create weather_snapshot table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE weather_snapshot (
                id INT AUTO_INCREMENT NOT NULL,
                city VARCHAR(120) NOT NULL,
                temperature DOUBLE PRECISION NOT NULL,
                average_last_10_days DOUBLE PRECISION NOT NULL,
                fetched_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX idx_city_fetched_at (city, fetched_at),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE weather_snapshot');
    }
}

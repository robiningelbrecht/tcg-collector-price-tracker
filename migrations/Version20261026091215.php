<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026091215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE CardPrice (language VARCHAR(255) NOT NULL, cardId VARCHAR(255) NOT NULL, variantId VARCHAR(255) NOT NULL, source VARCHAR(255) NOT NULL, date DATE NOT NULL, variantType VARCHAR(255) DEFAULT NULL, variantSubtype VARCHAR(255) DEFAULT NULL, variantSize VARCHAR(255) DEFAULT NULL, variantStamps VARCHAR(255) DEFAULT NULL, currency VARCHAR(255) NOT NULL, low DOUBLE PRECISION DEFAULT NULL, mid DOUBLE PRECISION DEFAULT NULL, high DOUBLE PRECISION DEFAULT NULL, market DOUBLE PRECISION DEFAULT NULL, avg DOUBLE PRECISION DEFAULT NULL, trend DOUBLE PRECISION DEFAULT NULL, avg1 DOUBLE PRECISION DEFAULT NULL, avg7 DOUBLE PRECISION DEFAULT NULL, avg30 DOUBLE PRECISION DEFAULT NULL, sourceUpdatedOn DATETIME DEFAULT NULL, data CLOB NOT NULL, PRIMARY KEY (language, cardId, variantId, source, date))');
        $this->addSql('CREATE INDEX CardPrice_date ON CardPrice (date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE CardPrice');
    }
}
